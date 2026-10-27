<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Cliente;
use App\Models\ClienteInteracao;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        /*O ClienteController faz a parte do CRUD de clientes a função index é responsável por listar os clientes. 
        filtrando por tipo de pessoa, qual o papel do cliente, se é proprietario ou locatario... email e documento.
        a função create fica responsavel para preparar o formulario ja com valores padrão para o tipo e papel 
        deixando tambem corretores ativos amostra.
        a função do store faz uma valicação aplica regras para caso o  cliente seja propietario para ser informado a 
        documentacao e caso tenha comprovante de renda, com esses dados é criado o cliente e faz um registro de log.
        o show carrega os dados do cliente com eager loading, com isso faz com que o Eloquent tenha mesmo queries em execução.
        O edit é igual ao create, só que já com os dados preenchidos.
        o update é responsável por atualizar os dados do cliente, utiliza mesmo fluxo do store, com diferenças no comando de update
        o storeInteracao faz o registro de uma nova interação de um corretor com o cliente.
        o anonimizar apaga dados pessoais do cliente pro conta da LGPD, para ficar um 
        pouco ratreavel é registrado no log o antigo nome do cliente
        ja o validarDados ele é usado para fazer a validação dos dados do cliente e não ficar repetitivo essas validações, 
        tipo se ao cadastrar um cliente e o documento ja tiver no banco de dados ele faz essa validação*/
        $query = Cliente::with('corretor')->whereNull('anonimizado_em');

        if ($request->filled('tipo_pessoa')) {
            $query->where('tipo_pessoa', $request->tipo_pessoa);
        }

        if ($request->filled('papel')) {
            if ($request->papel === 'proprietario') {
                $query->where('is_proprietario', true);
            } elseif ($request->papel === 'interessado') {
                $query->where('is_interessado', true);
            } elseif ($request->papel === 'locatario_comprador') {
                $query->where('is_locatario_comprador', true);
            }
        }

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $hashBusca = Cliente::gerarHashDocumento($busca);
            $query->where(function ($q) use ($busca, $hashBusca) {
                $q->where('nome_razao_social', 'like', "%{$busca}%")
                  ->orWhere('email', 'like', "%{$busca}%")
                  ->orWhere('documento_hash', $hashBusca);
            });
        }

        $clientes = $query->orderBy('nome_razao_social')->paginate(15);

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        $cliente = new Cliente(['tipo_pessoa' => 'PF', 'is_interessado' => true]);
        $corretores = User::where('status', 'ativo')->orderBy('name')->get();
        return view('clientes.form', compact('cliente', 'corretores'));
    }

    public function store(Request $request)
    {
        $dados = $this->validarDados($request);

        if (!empty($dados['is_proprietario']) && empty($dados['documentacao_validada'])) {
            return back()->withInput()->withErrors([
                'documentacao_validada' => 'Para classificar o cliente como Proprietário, é obrigatório marcar a validação prévia de documentação.',
            ]);
        }

        if ($request->hasFile('comprovante_renda')) {
            $dados['comprovante_renda_path'] = $request->file('comprovante_renda')->store('clientes/comprovantes', 'public');
        }

        $cliente = Cliente::create($dados);

        ClienteInteracao::create([
            'cliente_id' => $cliente->id,
            'user_id' => auth()->id(),
            'tipo' => 'cadastro',
            'descricao' => 'Cliente cadastrado no CRM.',
        ]);

        AuditLog::registrar('cliente_criado', "Cadastrou o cliente {$cliente->nome_razao_social} ({$cliente->tipo_pessoa})");

        return redirect()->route('clientes.show', $cliente)
            ->with('status', 'Cliente cadastrado com sucesso!');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['corretor', 'interacoes.autor']);
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        $corretores = User::where('status', 'ativo')->orderBy('name')->get();
        return view('clientes.form', compact('cliente', 'corretores'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $dados = $this->validarDados($request, $cliente->id);

        if (!empty($dados['is_proprietario']) && empty($dados['documentacao_validada'])) {
            return back()->withInput()->withErrors([
                'documentacao_validada' => 'Para habilitar o papel de Proprietário, a documentação precisa estar validada.',
            ]);
        }

        if ($request->hasFile('comprovante_renda')) {
            $dados['comprovante_renda_path'] = $request->file('comprovante_renda')->store('clientes/comprovantes', 'public');
        }

        $cliente->update($dados);

        ClienteInteracao::create([
            'cliente_id' => $cliente->id,
            'user_id' => auth()->id(),
            'tipo' => 'atualizacao',
            'descricao' => 'Dados cadastrais / preferências do cliente atualizados.',
        ]);

        AuditLog::registrar('cliente_atualizado', "Atualizou o cliente {$cliente->nome_razao_social}");

        return redirect()->route('clientes.show', $cliente)
            ->with('status', 'Cliente atualizado com sucesso!');
    }

    public function storeInteracao(Request $request, Cliente $cliente)
    {
        $dados = $request->validate([
            'tipo' => ['required', 'string', 'max:40'],
            'descricao' => ['required', 'string', 'max:2000'],
        ]);

        $cliente->interacoes()->create([
            'user_id' => auth()->id(),
            'tipo' => $dados['tipo'],
            'descricao' => $dados['descricao'],
        ]);

        return back()->with('status', 'Interação registrada na linha do tempo!');
    }

    public function anonimizar(Cliente $cliente)
    {
        $nomeAntigo = $cliente->nome_razao_social;
        $cliente->anonimizarDados();

        AuditLog::registrar('cliente_anonimizado', "Anonimizou os dados pessoais do cliente #{$cliente->id} ({$nomeAntigo})");

        return redirect()->route('clientes.index')
            ->with('status', "Dados sensíveis do cliente #{$cliente->id} foram anonimizados com sucesso.");
    }

    private function validarDados(Request $request, ?int $ignorarId = null): array
    {
        $documentoHash = Cliente::gerarHashDocumento((string) $request->documento);
        $request->merge(['documento_hash' => $documentoHash]);

        $dados = $request->validate([
            'corretor_id' => ['nullable', 'exists:users,id'],
            'tipo_pessoa' => ['required', Rule::in(['PF', 'PJ'])],
            'nome_razao_social' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'documento' => ['required', 'string', 'max:25'],
            'documento_hash' => ['required', Rule::unique('clientes', 'documento_hash')->ignore($ignorarId)],
            'rg' => ['nullable', 'string', 'max:30'],
            'profissao' => ['nullable', 'string', 'max:100'],
            'estado_civil' => ['nullable', 'string', 'max:40'],
            'renda_comprovada' => ['nullable', 'numeric', 'min:0'],
            'comprovante_renda' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'inscricao_estadual' => ['nullable', 'string', 'max:40'],
            'representante_legal' => ['nullable', 'string', 'max:255'],
            'pref_finalidade' => ['nullable', Rule::in(['venda', 'locacao', 'ambos'])],
            'pref_tipo_imovel' => ['nullable', 'string', 'max:50'],
            'pref_bairro' => ['nullable', 'string', 'max:120'],
            'pref_preco_min' => ['nullable', 'numeric', 'min:0'],
            'pref_preco_max' => ['nullable', 'numeric', 'min:0'],
        ], [
            'documento_hash.unique' => 'Já existe um cliente cadastrado com este CPF/CNPJ.',
        ]);

        $dados['is_proprietario'] = $request->boolean('is_proprietario');
        $dados['is_interessado'] = $request->boolean('is_interessado');
        $dados['is_locatario_comprador'] = $request->boolean('is_locatario_comprador');
        $dados['documentacao_validada'] = $request->boolean('documentacao_validada');

        return $dados;
    }
}
