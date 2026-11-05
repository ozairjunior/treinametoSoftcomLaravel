<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Cliente;
use App\Models\ClienteInteracao;
use App\Models\Imovel;
use App\Models\InteresseImovel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $query = Imovel::with(['fotoCapa'])
            ->where('publicar_catalogo', true)
            ->where('status', 'disponivel');

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('finalidade')) {
            if ($request->finalidade === 'venda') {
                $query->whereIn('finalidade', ['venda', 'ambos']);
            } elseif ($request->finalidade === 'locacao') {
                $query->whereIn('finalidade', ['locacao', 'ambos']);
            } else {
                $query->where('finalidade', $request->finalidade);
            }
        }

        if ($request->filled('bairro')) {
            $query->where('bairro', 'like', "%{$request->bairro}%");
        }

        if ($request->filled('cidade')) {
            $query->where('cidade', 'like', "%{$request->cidade}%");
        }

        if ($request->filled('quartos')) {
            $query->where('quartos', '>=', (int) $request->quartos);
        }

        if ($request->filled('preco_max')) {
            $precoMax = (float) $request->preco_max;
            $query->where(function ($q) use ($precoMax) {
                $q->where('valor_venda', '<=', $precoMax)
                  ->orWhere('valor_aluguel', '<=', $precoMax);
            });
        }

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('codigo_referencia', 'like', "%{$busca}%")
                  ->orWhere('titulo', 'like', "%{$busca}%")
                  ->orWhere('bairro', 'like', "%{$busca}%")
                  ->orWhere('cidade', 'like', "%{$busca}%");
            });
        }

        $imoveis = $query->latest()->paginate(9);

        return view('catalogo.index', compact('imoveis'));
    }

    public function show(Imovel $imovel)
    {
        if (!$imovel->publicar_catalogo || $imovel->status !== 'disponivel') {
            abort(404);
        }

        $imovel->load([
            'fotoCapa',
            'fotos' => function ($query) {
                $query->where('acesso_restrito', false);
            },
        ]);

        return view('catalogo.show', compact('imovel'));
    }

    public function registrarInteresse(Request $request, Imovel $imovel)
    {
        if (!$imovel->publicar_catalogo || $imovel->status !== 'disponivel') {
            abort(404);
        }

        $dados = $request->validate([
            'tipo_pessoa' => ['required', Rule::in(['PF', 'PJ'])],
            'nome' => ['required', 'string', 'max:255'],
            'documento' => ['required', 'string', 'max:25'],
            'telefone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'valor_proposto' => ['nullable', 'numeric', 'min:0'],
            'mensagem' => ['required', 'string', 'max:2000'],
            'consentimento_lgpd' => ['accepted'],
        ], [
            'tipo_pessoa.required' => 'Selecione o tipo de pessoa (PF ou PJ).',
            'nome.required' => 'Informe o seu nome completo ou razão social.',
            'documento.required' => 'Informe o seu CPF ou CNPJ.',
            'telefone.required' => 'Informe um telefone ou WhatsApp para contato.',
            'email.required' => 'Informe o seu e-mail.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'mensagem.required' => 'Escreva uma mensagem sobre o seu interesse no imóvel.',
            'consentimento_lgpd.accepted' => 'É obrigatório aceitar o termo de consentimento LGPD para prosseguir.',
        ]);

        $documentoHash = Cliente::gerarHashDocumento($dados['documento']);
        $cliente = Cliente::where('documento_hash', $documentoHash)->first();

        if ($cliente) {
            $atualizacoes = [
                'is_interessado' => true,
                'consentimento_lgpd_at' => now(),
            ];

            if (!$cliente->corretor_id && $imovel->corretor_id) {
                $atualizacoes['corretor_id'] = $imovel->corretor_id;
            }

            $cliente->update($atualizacoes);
        } else {
            $cliente = Cliente::create([
                'corretor_id' => $imovel->corretor_id,
                'tipo_pessoa' => $dados['tipo_pessoa'],
                'nome_razao_social' => $dados['nome'],
                'documento' => $dados['documento'],
                'documento_hash' => $documentoHash,
                'telefone' => $dados['telefone'],
                'email' => $dados['email'],
                'is_proprietario' => false,
                'is_interessado' => true,
                'is_locatario_comprador' => false,
                'documentacao_validada' => false,
                'origem' => 'catalogo',
                'cadastro_status' => 'pre_cadastro',
                'consentimento_lgpd_at' => now(),
            ]);
        }

        $temValorProposto = isset($dados['valor_proposto']) && $dados['valor_proposto'] !== '' && (float) $dados['valor_proposto'] > 0;
        $tipoInteresse = $temValorProposto ? 'proposta' : 'contato';

        $interesse = InteresseImovel::create([
            'imovel_id' => $imovel->id,
            'cliente_id' => $cliente->id,
            'corretor_id' => $imovel->corretor_id,
            'tipo' => $tipoInteresse,
            'status' => 'novo',
            'valor_proposto' => $temValorProposto ? $dados['valor_proposto'] : null,
            'mensagem' => $dados['mensagem'],
        ]);

        $resumoValor = $temValorProposto
            ? ' | Valor sugerido: R$ ' . number_format((float) $dados['valor_proposto'], 2, ',', '.')
            : '';

        ClienteInteracao::create([
            'cliente_id' => $cliente->id,
            'user_id' => $imovel->corretor_id,
            'tipo' => 'catalogo',
            'descricao' => "Demonstrou interesse pelo Catálogo Público no imóvel {$imovel->codigo_referencia} ({$imovel->titulo}){$resumoValor}. Mensagem: {$dados['mensagem']}",
        ]);

        AuditLog::registrar(
            'interesse_publico_criado',
            "Interesse público #{$interesse->id} ({$tipoInteresse}) recebido para o imóvel {$imovel->codigo_referencia} — Cliente: {$cliente->nome_razao_social}",
            $imovel->corretor_id,
            [
                'interesse_id' => $interesse->id,
                'imovel_id' => $imovel->id,
                'cliente_id' => $cliente->id,
                'tipo' => $tipoInteresse,
            ]
        );

        return redirect()->route('catalogo.show', $imovel)
            ->with('status', 'Sua manifestação de interesse foi enviada com sucesso! Nosso corretor entrará em contato em breve.');
    }
}
