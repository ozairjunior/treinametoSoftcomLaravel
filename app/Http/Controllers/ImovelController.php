<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Cliente;
use App\Models\Imovel;
use App\Models\ImovelMidia;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ImovelController extends Controller
{
    public function index(Request $request)
    {
        $query = Imovel::with(['corretor', 'proprietarios', 'fotoCapa']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('finalidade')) {
            $query->where('finalidade', $request->finalidade);
        }

        if ($request->filled('bairro')) {
            $query->where('bairro', 'like', "%{$request->bairro}%");
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
                  ->orWhere('logradouro', 'like', "%{$busca}%");
            });
        }

        $imoveis = $query->latest()->paginate(12);

        return view('imoveis.index', compact('imoveis'));
    }

    public function create()
    {
        $codigoSugerido = 'IMO-' . strtoupper(substr(uniqid(), -5));
        $imovel = new Imovel([
            'codigo_referencia' => $codigoSugerido,
            'status' => 'rascunho',
            'tipo' => 'apartamento',
            'finalidade' => 'venda',
            'mobiliado' => 'nao',
            'taxa_administracao' => 10.00,
        ]);

        $proprietariosAptos = Cliente::where('is_proprietario', true)
            ->where('documentacao_validada', true)
            ->whereNull('anonimizado_em')
            ->orderBy('nome_razao_social')
            ->get();

        $corretores = User::where('status', 'ativo')->orderBy('name')->get();

        return view('imoveis.form', compact('imovel', 'proprietariosAptos', 'corretores'));
    }

    public function store(Request $request)
    {
        $dados = $this->validarImovel($request);

        if (!$this->validarProprietarios($request->proprietarios)) {
            return back()->withInput()->withErrors([
                'proprietarios' => 'Todos os proprietários vinculados devem ter o papel Proprietário e Documentação Validada.',
            ]);
        }

        $imovel = Imovel::create($dados);
        $imovel->proprietarios()->sync($request->proprietarios);

        AuditLog::registrar('imovel_criado', "Cadastrou o imóvel {$imovel->codigo_referencia} - {$imovel->titulo}");

        return redirect()->route('imoveis.show', $imovel)
            ->with('status', 'Imóvel cadastrado com sucesso! Agora você pode adicionar fotos, plantas e documentos.');
    }

    public function show(Imovel $imovei)
    {
        $imovei->load(['corretor', 'proprietarios', 'midias']);
        return view('imoveis.show', ['imovel' => $imovei]);
    }

    public function edit(Imovel $imovei)
    {
        $imovei->load('proprietarios');

        $proprietariosAptos = Cliente::where('is_proprietario', true)
            ->where('documentacao_validada', true)
            ->whereNull('anonimizado_em')
            ->orderBy('nome_razao_social')
            ->get();

        $corretores = User::where('status', 'ativo')->orderBy('name')->get();

        return view('imoveis.form', [
            'imovel' => $imovei,
            'proprietariosAptos' => $proprietariosAptos,
            'corretores' => $corretores,
        ]);
    }

    public function update(Request $request, Imovel $imovei)
    {
        $dados = $this->validarImovel($request, $imovei->id);

        if (!$this->validarProprietarios($request->proprietarios)) {
            return back()->withInput()->withErrors([
                'proprietarios' => 'Todos os proprietários vinculados devem ter o papel Proprietário e Documentação Validada.',
            ]);
        }

        $imovei->update($dados);
        $imovei->proprietarios()->sync($request->proprietarios);

        AuditLog::registrar('imovel_atualizado', "Atualizou o imóvel {$imovei->codigo_referencia}");

        return redirect()->route('imoveis.show', $imovei)
            ->with('status', 'Imóvel atualizado com sucesso!');
    }

    public function alterarStatus(Request $request, Imovel $imovei)
    {
        $request->validate([
            'status' => ['required', Rule::in(['rascunho', 'disponivel', 'reservado', 'vendido', 'alugado', 'inativo'])],
        ]);

        $statusAnterior = $imovei->status;
        $imovei->update(['status' => $request->status]);

        AuditLog::registrar(
            'imovel_status_alterado',
            "Alterou status do imóvel {$imovei->codigo_referencia} de [{$statusAnterior}] para [{$request->status}]"
        );

        return back()->with('status', "Status do imóvel alterado para " . strtoupper($request->status) . "!");
    }

    public function uploadMidia(Request $request, Imovel $imovei)
    {
        $request->validate([
            'categoria' => ['required', Rule::in(['foto', 'planta', 'documento'])],
            'titulo' => ['nullable', 'string', 'max:150'],
            'arquivos.*' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'],
        ]);

        $maiorOrdem = (int) $imovei->midias()->max('ordem');
        $temCapa = $imovei->fotos()->where('is_capa', true)->exists();

        if ($request->hasFile('arquivos')) {
            foreach ($request->file('arquivos') as $arquivo) {
                $maiorOrdem++;
                $path = $arquivo->store("imoveis/{$imovei->id}/{$request->categoria}", 'public');

                $definirComoCapa = ($request->categoria === 'foto' && !$temCapa);
                if ($definirComoCapa) {
                    $temCapa = true;
                }

                ImovelMidia::create([
                    'imovel_id' => $imovei->id,
                    'categoria' => $request->categoria,
                    'titulo' => $request->titulo ?: $arquivo->getClientOriginalName(),
                    'arquivo_path' => $path,
                    'is_capa' => $definirComoCapa,
                    'ordem' => $maiorOrdem,
                    'acesso_restrito' => $request->categoria === 'documento' || $request->boolean('acesso_restrito'),
                ]);
            }
        }

        return back()->with('status', 'Arquivos enviados com sucesso!');
    }

    public function definirCapa(Imovel $imovei, ImovelMidia $midia)
    {
        $imovei->midias()->where('categoria', 'foto')->update(['is_capa' => false]);
        $midia->update(['is_capa' => true]);

        return back()->with('status', 'Imagem de capa definida com sucesso!');
    }

    public function removerMidia(Imovel $imovei, ImovelMidia $midia)
    {
        Storage::disk('public')->delete($midia->arquivo_path);
        $midia->delete();

        return back()->with('status', 'Arquivo removido com sucesso!');
    }

    private function validarImovel(Request $request, ?int $ignorarId = null): array
    {
        $dados = $request->validate([
            'corretor_id' => ['nullable', 'exists:users,id'],
            'codigo_referencia' => ['required', 'string', 'max:30', Rule::unique('imoveis', 'codigo_referencia')->ignore($ignorarId)],
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'tipo' => ['required', Rule::in(['casa', 'apartamento', 'terreno', 'comercial'])],
            'finalidade' => ['required', Rule::in(['venda', 'locacao', 'ambos'])],
            'status' => ['required', Rule::in(['rascunho', 'disponivel', 'reservado', 'vendido', 'alugado', 'inativo'])],
            'cep' => ['required', 'string', 'max:10'],
            'logradouro' => ['required', 'string', 'max:255'],
            'numero' => ['required', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:100'],
            'bairro' => ['required', 'string', 'max:120'],
            'cidade' => ['required', 'string', 'max:120'],
            'uf' => ['required', 'string', 'size:2'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'area_util' => ['nullable', 'numeric', 'min:0'],
            'area_total' => ['nullable', 'numeric', 'min:0'],
            'quartos' => ['required', 'integer', 'min:0'],
            'suites' => ['required', 'integer', 'min:0'],
            'banheiros' => ['required', 'integer', 'min:0'],
            'vagas_garagem' => ['required', 'integer', 'min:0'],
            'mobiliado' => ['required', Rule::in(['nao', 'semimobiliado', 'mobiliado'])],
            'valor_venda' => ['nullable', 'numeric', 'min:0'],
            'valor_aluguel' => ['nullable', 'numeric', 'min:0'],
            'valor_condominio' => ['nullable', 'numeric', 'min:0'],
            'valor_iptu' => ['nullable', 'numeric', 'min:0'],
            'taxa_administracao' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tour_virtual_url' => ['nullable', 'url', 'max:255'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'proprietarios' => ['required', 'array', 'min:1'],
            'proprietarios.*' => ['exists:clientes,id'],
        ], [
            'proprietarios.required' => 'É obrigatório vincular pelo menos um cliente Proprietário ao imóvel.',
        ]);

        $dados['aceita_pets'] = $request->boolean('aceita_pets');
        $dados['imovel_habitado'] = $request->boolean('imovel_habitado');
        $dados['valor_condominio'] = $dados['valor_condominio'] ?? 0;
        $dados['valor_iptu'] = $dados['valor_iptu'] ?? 0;
        $dados['taxa_administracao'] = $dados['taxa_administracao'] ?? 10;

        return $dados;
    }

    private function validarProprietarios(array $idsProprietarios): bool
    {
        $qtdValidos = Cliente::whereIn('id', $idsProprietarios)
            ->where('is_proprietario', true)
            ->where('documentacao_validada', true)
            ->count();

        return $qtdValidos === count(array_unique($idsProprietarios));
    }
}
