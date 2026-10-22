<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Cliente;
use App\Models\ClienteInteracao;
use App\Models\Imovel;
use App\Models\Proposta;
use App\Models\PropostaRodada;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PropostaController extends Controller
{
    public function index(Request $request)
    {
        Proposta::expirarPropostasVencidas();

        $query = Proposta::with(['imovel', 'cliente', 'corretor', 'contrato'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $propostas = $query->paginate(15);

        return view('propostas.index', compact('propostas'));
    }

    public function create(Request $request)
    {
        $imoveis = Imovel::whereIn('status', ['disponivel', 'reservado'])->orderBy('codigo_referencia')->get();
        $clientes = Cliente::whereNull('anonimizado_em')->orderBy('nome_razao_social')->get();
        $corretores = User::where('status', 'ativo')->orderBy('name')->get();

        $validaAte = now()->addWeekdays(2);

        return view('propostas.form', compact('imoveis', 'clientes', 'corretores', 'validaAte'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'imovel_id' => ['required', 'exists:imoveis,id'],
            'cliente_id' => ['required', 'exists:clientes,id'],
            'corretor_id' => ['required', 'exists:users,id'],
            'valor_oferecido' => ['required', 'numeric', 'min:1'],
            'forma_pagamento' => ['required', Rule::in(['a_vista', 'financiamento', 'parcelamento_direto', 'aluguel_mensal'])],
            'garantia_locaticia' => ['required', Rule::in(['nenhuma', 'caucao', 'fiador', 'seguro_fianca'])],
            'valida_ate' => ['required', 'date', 'after:now'],
            'condicoes_observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        $dados['status'] = 'em_analise';
        $dados['rodada_atual'] = 1;

        $proposta = Proposta::create($dados);

        PropostaRodada::create([
            'proposta_id' => $proposta->id,
            'user_id' => auth()->id(),
            'numero_rodada' => 1,
            'autor_tipo' => 'cliente',
            'valor_proposto' => $proposta->valor_oferecido,
            'justificativa' => $proposta->condicoes_observacoes ?: 'Proposta inicial emitida pelo cliente.',
        ]);

        ClienteInteracao::create([
            'cliente_id' => $proposta->cliente_id,
            'user_id' => auth()->id(),
            'tipo' => 'proposta',
            'descricao' => "Emitiu proposta #{$proposta->id} no valor de R$ " . number_format($proposta->valor_oferecido, 2, ',', '.') . " para o imóvel {$proposta->imovel->codigo_referencia}.",
        ]);

        AuditLog::registrar('proposta_emitida', "Proposta #{$proposta->id} registrada para o imóvel {$proposta->imovel->codigo_referencia}");

        return redirect()->route('propostas.show', $proposta)
            ->with('status', 'Proposta registrada com sucesso!');
    }

    public function show(Proposta $proposta)
    {
        Proposta::expirarPropostasVencidas();
        $proposta->refresh()->load(['imovel.proprietarios', 'cliente', 'corretor', 'rodadas.usuario', 'contrato']);

        return view('propostas.show', compact('proposta'));
    }

    public function contraproposta(Request $request, Proposta $proposta)
    {
        if ($proposta->status !== 'em_analise') {
            return back()->withErrors(['status' => 'Apenas propostas Em Análise podem receber contrapropostas.']);
        }

        $dados = $request->validate([
            'autor_tipo' => ['required', Rule::in(['proprietario', 'cliente', 'corretor'])],
            'valor_proposto' => ['required', 'numeric', 'min:1'],
            'justificativa' => ['required', 'string', 'max:1000'],
        ]);

        $novaRodada = $proposta->rodada_atual + 1;

        PropostaRodada::create([
            'proposta_id' => $proposta->id,
            'user_id' => auth()->id(),
            'numero_rodada' => $novaRodada,
            'autor_tipo' => $dados['autor_tipo'],
            'valor_proposto' => $dados['valor_proposto'],
            'justificativa' => $dados['justificativa'],
        ]);

        $proposta->update([
            'valor_oferecido' => $dados['valor_proposto'],
            'rodada_atual' => $novaRodada,
            'valida_ate' => now()->addWeekdays(2),
        ]);

        return back()->with('status', "Rodada #{$novaRodada} (Contraproposta) registrada e prazo renovado por 48h úteis!");
    }

    public function decidir(Request $request, Proposta $proposta)
    {
        $request->validate([
            'decisao' => ['required', Rule::in(['aceita', 'recusada'])],
        ]);

        $proposta->update(['status' => $request->decisao]);

        if ($request->decisao === 'aceita') {
            $reservaAte = now()->addWeekdays(5);
            $proposta->imovel->update([
                'status' => 'reservado',
                'reservado_ate' => $reservaAte,
            ]);

            ClienteInteracao::create([
                'cliente_id' => $proposta->cliente_id,
                'user_id' => auth()->id(),
                'tipo' => 'proposta',
                'descricao' => "Proposta #{$proposta->id} ACEITA pelo proprietário! Imóvel {$proposta->imovel->codigo_referencia} reservado automaticamente até {$reservaAte->format('d/m/Y')}.",
            ]);

            AuditLog::registrar('proposta_aceita', "Proposta #{$proposta->id} aceita. Imóvel {$proposta->imovel->codigo_referencia} reservado por 5 dias úteis.");

            return back()->with('status', 'Proposta ACEITA! O imóvel foi alterado automaticamente para RESERVADO por 5 dias úteis.');
        }

        AuditLog::registrar('proposta_recusada', "Proposta #{$proposta->id} recusada.");

        return back()->with('status', 'Proposta marcada como RECUSADA.');
    }
}
