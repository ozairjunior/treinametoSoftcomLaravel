<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Cliente;
use App\Models\ClienteInteracao;
use App\Models\Imovel;
use App\Models\Visita;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VisitaController extends Controller
{
    public function index(Request $request)
    {
        $query = Visita::with(['imovel', 'cliente', 'corretor'])
            ->orderBy('data_hora_inicio');

        if ($request->filled('corretor_id')) {
            $query->where('corretor_id', $request->corretor_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_hora_inicio', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_hora_inicio', '<=', $request->data_fim);
        }

        $visitas = $query->paginate(20);
        $corretores = User::where('status', 'ativo')->orderBy('name')->get();

        return view('visitas.index', compact('visitas', 'corretores'));
    }

    public function create(Request $request)
    {
        $imoveis = Imovel::where('status', 'disponivel')->orderBy('codigo_referencia')->get();
        $clientes = Cliente::whereNull('anonimizado_em')->orderBy('nome_razao_social')->get();
        $corretores = User::where('status', 'ativo')->orderBy('name')->get();

        $visita = new Visita([
            'imovel_id' => $request->imovel_id,
            'cliente_id' => $request->cliente_id,
            'corretor_id' => auth()->id(),
            'data_hora_inicio' => now()->addDay()->setHour(10)->setMinute(0),
            'data_hora_fim' => now()->addDay()->setHour(11)->setMinute(0),
        ]);

        return view('visitas.form', compact('visita', 'imoveis', 'clientes', 'corretores'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'imovel_id' => ['required', 'exists:imoveis,id'],
            'cliente_id' => ['required', 'exists:clientes,id'],
            'corretor_id' => ['required', 'exists:users,id'],
            'data_hora_inicio' => ['required', 'date'],
            'data_hora_fim' => ['required', 'date', 'after:data_hora_inicio'],
            'observacoes_agendamento' => ['nullable', 'string', 'max:1000'],
        ]);

        $imovel = Imovel::findOrFail($dados['imovel_id']);
        $inicio = Carbon::parse($dados['data_hora_inicio']);
        $fim = Carbon::parse($dados['data_hora_fim']);

        if ($imovel->status !== 'disponivel') {
            return back()->withInput()->withErrors([
                'imovel_id' => "Este imóvel está com status [{$imovel->status}] e não permite novos agendamentos de visita.",
            ]);
        }

        if ($conflitoCorretor = Visita::corretorTemConflito((int) $dados['corretor_id'], $inicio, $fim)) {
            $horarioConflito = $conflitoCorretor->data_hora_inicio->format('d/m/Y H:i') . ' às ' . $conflitoCorretor->data_hora_fim->format('H:i');
            return back()->withInput()->withErrors([
                'data_hora_inicio' => "Conflito de agenda: O corretor já possui visita agendada ({$horarioConflito}). É exigido intervalo mínimo de 30 minutos para deslocamento entre visitas.",
            ]);
        }

        if (!$request->boolean('permitir_simultaneo')) {
            if ($conflitoImovel = Visita::imovelTemConflito((int) $dados['imovel_id'], $inicio, $fim)) {
                $horarioOcupado = $conflitoImovel->data_hora_inicio->format('d/m/Y H:i') . ' às ' . $conflitoImovel->data_hora_fim->format('H:i');
                return back()->withInput()->withErrors([
                    'imovel_id' => "Ocupação simultânea: Este imóvel já possui visita agendada no horário ({$horarioOcupado}).",
                ]);
            }
        }

        $dados['status'] = 'agendada';
        $dados['notificar_proprietario'] = $request->boolean('notificar_proprietario') || $imovel->imovel_habitado;

        $visita = Visita::create($dados);

        $msgNotificacao = $dados['notificar_proprietario']
            ? ' (Notificação enviada ao cliente, corretor e proprietário - imóvel habitado).'
            : ' (Notificação enviada ao cliente e corretor).';

        ClienteInteracao::create([
            'cliente_id' => $visita->cliente_id,
            'user_id' => auth()->id(),
            'tipo' => 'visita',
            'descricao' => "Visita agendada ao imóvel {$imovel->codigo_referencia} para {$inicio->format('d/m/Y H:i')}{$msgNotificacao}",
        ]);

        AuditLog::registrar('visita_agendada', "Agendou visita #{$visita->id} no imóvel {$imovel->codigo_referencia}");

        return redirect()->route('visitas.show', $visita)
            ->with('status', 'Visita agendada com sucesso!' . $msgNotificacao);
    }

    public function show(Visita $visita)
    {
        $visita->load(['imovel.proprietarios', 'cliente', 'corretor']);
        return view('visitas.show', compact('visita'));
    }

    public function registrarFeedback(Request $request, Visita $visita)
    {
        $dados = $request->validate([
            'status' => ['required', Rule::in(['agendada', 'confirmada', 'realizada', 'cancelada', 'nao_compareceu'])],
            'nivel_interesse' => ['nullable', Rule::in(['baixo', 'medio', 'alto'])],
            'feedback_objecoes' => ['nullable', 'string', 'max:2000'],
        ]);

        $dados['intencao_proposta'] = $request->boolean('intencao_proposta');

        if ($dados['status'] === 'realizada' && !$visita->checkin_em) {
            $dados['checkin_em'] = now();
        }

        $visita->update($dados);

        ClienteInteracao::create([
            'cliente_id' => $visita->cliente_id,
            'user_id' => auth()->id(),
            'tipo' => 'visita',
            'descricao' => "Visita #{$visita->id} ao imóvel {$visita->imovel->codigo_referencia} atualizada para [" . strtoupper($dados['status']) . "]. "
                . ($dados['nivel_interesse'] ? "Interesse: " . strtoupper($dados['nivel_interesse']) . ". " : "")
                . ($dados['feedback_objecoes'] ? "Feedback: {$dados['feedback_objecoes']}" : ""),
        ]);

        AuditLog::registrar('visita_feedback', "Atualizou status/feedback da visita #{$visita->id} para {$dados['status']}");

        return back()->with('status', 'Status e feedback da visita registrados com sucesso!');
    }
}
