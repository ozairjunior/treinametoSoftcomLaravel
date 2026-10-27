<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ClienteInteracao;
use App\Models\Contrato;
use App\Models\Proposta;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContratoController extends Controller
{
    public function index()
    {
        $contratos = Contrato::with(['imovel', 'cliente', 'corretor'])->latest()->paginate(15);
        return view('contratos.index', compact('contratos'));
    }

    public function createFromProposta(Proposta $proposta)
    {
        $proposta->load(['imovel.proprietarios', 'cliente', 'corretor']);

        $tipoSugerido = $proposta->imovel->finalidade === 'locacao' ? 'locacao' : 'compra_venda';

        $textoInicial = Contrato::gerarTextoPorTemplate(
            $tipoSugerido,
            $proposta->imovel,
            $proposta->cliente,
            (float) $proposta->valor_oferecido,
            'IPCA',
            10,
            $proposta->garantia_locaticia
        );

        return view('contratos.form', compact('proposta', 'tipoSugerido', 'textoInicial'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'proposta_id' => ['required', 'exists:propostas,id'],
            'tipo_contrato' => ['required', Rule::in(['intermediacao', 'compra_venda', 'locacao'])],
            'valor_final' => ['required', 'numeric', 'min:1'],
            'vigencia_inicio' => ['required', 'date'],
            'vigencia_fim' => ['nullable', 'date', 'after:vigencia_inicio'],
            'indice_reajuste' => ['required', Rule::in(['IPCA', 'IGP-M', 'INPC', 'NENHUM'])],
            'dia_vencimento' => ['nullable', 'integer', 'min:1', 'max:31'],
            'garantia_locaticia' => ['required', Rule::in(['nenhuma', 'caucao', 'fiador', 'seguro_fianca'])],
            'valor_caucao' => ['nullable', 'numeric', 'min:0'],
            'apolice_seguro_fianca' => ['nullable', 'string', 'max:80'],
            'termos_contrato' => ['required', 'string'],
        ]);

        $proposta = Proposta::findOrFail($dados['proposta_id']);

        $dados['codigo_contrato'] = 'CTR-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));
        $dados['imovel_id'] = $proposta->imovel_id;
        $dados['cliente_id'] = $proposta->cliente_id;
        $dados['corretor_id'] = $proposta->corretor_id;
        $dados['status'] = 'aguardando_assinaturas';
        $dados['hash_documento'] = hash('sha256', $dados['termos_contrato']);
        $dados['assinatura_cliente'] = $proposta->assinatura_cliente;
        $dados['assinado_cliente_em'] = $proposta->assinado_em;

        $contrato = Contrato::create($dados);

        AuditLog::registrar('contrato_gerado', "Gerou o contrato {$contrato->codigo_contrato} ({$contrato->tipo_contrato})");

        return redirect()->route('contratos.show', $contrato)
            ->with('status', 'Contrato gerado com sucesso e pronto para coleta de assinaturas digitais!');
    }

    public function show(Contrato $contrato)
    {
        $contrato->load(['imovel.proprietarios', 'cliente', 'corretor', 'proposta']);
        return view('contratos.show', compact('contrato'));
    }

    public function updateTermos(Request $request, Contrato $contrato)
    {
        if ($contrato->possuiAssinatura()) {
            return back()->withErrors([
                'termos_contrato' => 'Este contrato já possui assinatura digital coletada e seus termos não podem mais ser alterados. Cancele-o e gere um aditivo ou novo instrumento.',
            ]);
        }

        $dados = $request->validate([
            'termos_contrato' => ['required', 'string'],
            'indice_reajuste' => ['required', Rule::in(['IPCA', 'IGP-M', 'INPC', 'NENHUM'])],
            'dia_vencimento' => ['nullable', 'integer', 'min:1', 'max:31'],
        ]);

        $dados['hash_documento'] = hash('sha256', $dados['termos_contrato']);
        $contrato->update($dados);

        AuditLog::registrar('contrato_editado', "Atualizou cláusulas do contrato {$contrato->codigo_contrato} antes da assinatura.");

        return back()->with('status', 'Termos do contrato e Hash SHA-256 atualizados!');
    }

    public function assinar(Request $request, Contrato $contrato)
    {
        $request->validate([
            'parte' => ['required', Rule::in(['cliente', 'proprietario'])],
            'assinatura_base64' => ['required', 'string'],
        ], [
            'assinatura_base64.required' => 'Desenhe a assinatura com o mouse no quadro antes de confirmar.',
        ]);

        if ($contrato->status === 'cancelado') {
            return back()->withErrors(['status' => 'Contrato cancelado não pode receber assinaturas.']);
        }

        if ($request->parte === 'cliente') {
            $contrato->assinatura_cliente = $request->assinatura_base64;
            $contrato->assinado_cliente_em = now();
        }

        if ($request->parte === 'proprietario') {
            $contrato->assinatura_proprietario = $request->assinatura_base64;
            $contrato->assinado_proprietario_em = now();
        }

        if ($contrato->assinado_cliente_em && $contrato->assinado_proprietario_em) {
            $contrato->status = 'vigente_formalizado';

            $novoStatusImovel = $contrato->tipo_contrato === 'locacao' ? 'alugado' : 'vendido';
            $contrato->imovel->update([
                'status' => $novoStatusImovel,
                'reservado_ate' => null,
            ]);

            $contrato->cliente->update(['is_locatario_comprador' => true]);

            ClienteInteracao::create([
                'cliente_id' => $contrato->cliente_id,
                'user_id' => auth()->id(),
                'tipo' => 'contrato',
                'descricao' => "Contrato {$contrato->codigo_contrato} 100% assinado e formalizado! Imóvel marcado como " . strtoupper($novoStatusImovel) . ".",
            ]);
        } else {
            $contrato->status = 'parcialmente_assinado';
        }

        $contrato->save();

        AuditLog::registrar(
            'contrato_assinatura_digital',
            "Assinatura digital ({$request->parte}) registrada no contrato {$contrato->codigo_contrato} (Hash: {$contrato->hash_documento})"
        );

        return back()->with('status', "Assinatura eletrônica ({$request->parte}) desenhada e registrada! A partir de agora o contrato é imutável.");
    }

    public function pdf(Contrato $contrato)
    {
        $contrato->load(['imovel.proprietarios', 'cliente', 'corretor', 'proposta']);
        return view('contratos.pdf', compact('contrato'));
    }
}
