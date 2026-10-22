<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ColaboradorController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('name', 'like', "%{$busca}%")
                  ->orWhere('email', 'like', "%{$busca}%")
                  ->orWhere('cpf', 'like', "%{$busca}%")
                  ->orWhere('creci', 'like', "%{$busca}%");
            });
        }

        $colaboradores = $query->orderBy('name')->paginate(15);

        return view('colaboradores.index', compact('colaboradores'));
    }

    public function create()
    {
        $colaborador = new User();
        return view('colaboradores.form', compact('colaborador'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'corretor', 'financeiro', 'cliente'])],
            'status' => ['required', Rule::in(['ativo', 'suspenso', 'inativo'])],
            'cpf' => ['nullable', 'string', 'max:14', 'unique:users,cpf'],
            'creci' => ['nullable', 'string', 'max:30'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'comissao_padrao' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        $dados['password'] = Hash::make($dados['password']);
        $dados['comissao_padrao'] = $dados['comissao_padrao'] ?? 0;

        if ($request->hasFile('foto')) {
            $dados['foto'] = $request->file('foto')->store('colaboradores', 'public');
        }

        $colaborador = User::create($dados);

        AuditLog::registrar(
            'colaborador_criado',
            "Cadastrou o usuário {$colaborador->name} ({$colaborador->role})",
            auth()->id(),
            ['colaborador_id' => $colaborador->id, 'role' => $colaborador->role]
        );

        return redirect()->route('colaboradores.index')
            ->with('status', 'Colaborador cadastrado com sucesso!');
    }

    public function edit(User $colaboradore)
    {
        return view('colaboradores.form', ['colaborador' => $colaboradore]);
    }

    public function update(Request $request, User $colaboradore)
    {
        $dados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($colaboradore->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'corretor', 'financeiro', 'cliente'])],
            'status' => ['required', Rule::in(['ativo', 'suspenso', 'inativo'])],
            'cpf' => ['nullable', 'string', 'max:14', Rule::unique('users', 'cpf')->ignore($colaboradore->id)],
            'creci' => ['nullable', 'string', 'max:30'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'comissao_padrao' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        if (!empty($dados['password'])) {
            $dados['password'] = Hash::make($dados['password']);
        } else {
            unset($dados['password']);
        }

        if ($request->hasFile('foto')) {
            if ($colaboradore->foto) {
                Storage::disk('public')->delete($colaboradore->foto);
            }
            $dados['foto'] = $request->file('foto')->store('colaboradores', 'public');
        }

        $statusAnterior = $colaboradore->status;
        $colaboradore->update($dados);

        AuditLog::registrar(
            'colaborador_atualizado',
            "Atualizou cadastro de {$colaboradore->name} (Status: {$statusAnterior} -> {$colaboradore->status})",
            auth()->id(),
            ['colaborador_id' => $colaboradore->id, 'status_novo' => $colaboradore->status]
        );

        return redirect()->route('colaboradores.index')
            ->with('status', 'Colaborador atualizado com sucesso!');
    }

    public function alterarStatus(Request $request, User $colaboradore)
    {
        $request->validate([
            'status' => ['required', Rule::in(['ativo', 'suspenso', 'inativo'])],
        ]);

        if ($colaboradore->id === auth()->id() && $request->status !== 'ativo') {
            return back()->withErrors(['status' => 'Você não pode suspender/inativar sua própria conta logada.']);
        }

        $statusAnterior = $colaboradore->status;
        $colaboradore->update(['status' => $request->status]);

        AuditLog::registrar(
            'status_conta_alterado',
            "Alterou status de {$colaboradore->name} de [{$statusAnterior}] para [{$request->status}]",
            auth()->id(),
            ['colaborador_id' => $colaboradore->id, 'de' => $statusAnterior, 'para' => $request->status]
        );

        return back()->with('status', "Status de {$colaboradore->name} alterado para {$request->status}!");
    }

    public function auditoria(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('evento')) {
            $query->where('evento', $request->evento);
        }

        $logs = $query->paginate(25);

        return view('auditoria.index', compact('logs'));
    }
}
