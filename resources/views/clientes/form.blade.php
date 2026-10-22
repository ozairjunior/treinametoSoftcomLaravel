@extends('adminlte::page')

@section('title', $cliente->exists ? 'Editar Cliente' : 'Novo Cliente')

@section('content_header')
    <h1>{{ $cliente->exists ? 'Editar Cliente: ' . $cliente->nome_razao_social : 'Novo Cliente' }}</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <form method="POST" enctype="multipart/form-data"
              action="{{ $cliente->exists ? route('clientes.update', $cliente) : route('clientes.store') }}">
            @csrf
            @if ($cliente->exists)
                @method('PUT')
            @endif

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $erro)
                                <li>{{ $erro }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <h5 class="text-primary border-bottom pb-2"><i class="fas fa-id-card mr-1"></i>1. Identificação</h5>
                <div class="row">
                    <div class="form-group col-md-2">
                        <label>Tipo Pessoa *</label>
                        <select name="tipo_pessoa" id="tipo_pessoa" class="form-control" required onchange="alternarCamposPfPj()">
                            <option value="PF" {{ old('tipo_pessoa', $cliente->tipo_pessoa) === 'PF' ? 'selected' : '' }}>Pessoa Física (PF)</option>
                            <option value="PJ" {{ old('tipo_pessoa', $cliente->tipo_pessoa) === 'PJ' ? 'selected' : '' }}>Pessoa Jurídica (PJ)</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Nome Completo / Razão Social *</label>
                        <input type="text" name="nome_razao_social" class="form-control" value="{{ old('nome_razao_social', $cliente->nome_razao_social) }}" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>CPF / CNPJ *</label>
                        <input type="text" name="documento" class="form-control" value="{{ old('documento', $cliente->documento) }}" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Corretor Responsável</label>
                        <select name="corretor_id" class="form-control">
                            <option value="">-- Selecione --</option>
                            @foreach ($corretores as $corretor)
                                <option value="{{ $corretor->id }}" {{ (int) old('corretor_id', $cliente->corretor_id ?? auth()->id()) === $corretor->id ? 'selected' : '' }}>
                                    {{ $corretor->name }} ({{ $corretor->role }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-4">
                        <label>E-mail</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $cliente->email) }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Telefone / WhatsApp</label>
                        <input type="text" name="telefone" class="form-control" value="{{ old('telefone', $cliente->telefone) }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Comprovante de Renda / Documento</label>
                        <input type="file" name="comprovante_renda" class="form-control-file">
                    </div>
                </div>

                <div id="bloco_pf" class="row bg-light p-2 rounded mb-3">
                    <div class="form-group col-md-3 mb-0">
                        <label>RG (PF)</label>
                        <input type="text" name="rg" class="form-control" value="{{ old('rg', $cliente->rg) }}">
                    </div>
                    <div class="form-group col-md-3 mb-0">
                        <label>Profissão (PF)</label>
                        <input type="text" name="profissao" class="form-control" value="{{ old('profissao', $cliente->profissao) }}">
                    </div>
                    <div class="form-group col-md-3 mb-0">
                        <label>Estado Civil (PF)</label>
                        <input type="text" name="estado_civil" class="form-control" placeholder="Solteiro, Casado..." value="{{ old('estado_civil', $cliente->estado_civil) }}">
                    </div>
                    <div class="form-group col-md-3 mb-0">
                        <label>Renda Comprovada R$ (PF)</label>
                        <input type="number" step="0.01" name="renda_comprovada" class="form-control" value="{{ old('renda_comprovada', $cliente->renda_comprovada) }}">
                    </div>
                </div>

                <div id="bloco_pj" class="row bg-light p-2 rounded mb-3" style="display:none;">
                    <div class="form-group col-md-6 mb-0">
                        <label>Inscrição Estadual (PJ)</label>
                        <input type="text" name="inscricao_estadual" class="form-control" value="{{ old('inscricao_estadual', $cliente->inscricao_estadual) }}">
                    </div>
                    <div class="form-group col-md-6 mb-0">
                        <label>Representante Legal (PJ)</label>
                        <input type="text" name="representante_legal" class="form-control" value="{{ old('representante_legal', $cliente->representante_legal) }}">
                    </div>
                </div>

                <h5 class="text-primary border-bottom pb-2 mt-4"><i class="fas fa-user-tag mr-1"></i>2. Papéis do Cliente e Validação Documental</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_interessado" name="is_interessado" value="1" {{ old('is_interessado', $cliente->is_interessado) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_interessado">Interessado / Lead</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_proprietario" name="is_proprietario" value="1" {{ old('is_proprietario', $cliente->is_proprietario) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_proprietario">Proprietário de Imóvel</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_locatario_comprador" name="is_locatario_comprador" value="1" {{ old('is_locatario_comprador', $cliente->is_locatario_comprador) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_locatario_comprador">Locatário / Comprador</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="documentacao_validada" name="documentacao_validada" value="1" {{ old('documentacao_validada', $cliente->documentacao_validada) ? 'checked' : '' }}>
                            <label class="custom-control-label text-success font-weight-bold" for="documentacao_validada">Documentação Validada</label>
                        </div>
                    </div>
                </div>

                <h5 class="text-primary border-bottom pb-2 mt-4"><i class="fas fa-search-dollar mr-1"></i>3. Preferências de Busca do Lead</h5>
                <div class="row">
                    <div class="form-group col-md-2">
                        <label>Finalidade</label>
                        <select name="pref_finalidade" class="form-control">
                            <option value="">Indiferente</option>
                            <option value="venda" {{ old('pref_finalidade', $cliente->pref_finalidade) === 'venda' ? 'selected' : '' }}>Compra</option>
                            <option value="locacao" {{ old('pref_finalidade', $cliente->pref_finalidade) === 'locacao' ? 'selected' : '' }}>Locação</option>
                            <option value="ambos" {{ old('pref_finalidade', $cliente->pref_finalidade) === 'ambos' ? 'selected' : '' }}>Ambos</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Tipologia Desejada</label>
                        <select name="pref_tipo_imovel" class="form-control">
                            <option value="">Qualquer Tipo</option>
                            @foreach (['apartamento' => 'Apartamento', 'casa' => 'Casa', 'terreno' => 'Terreno', 'comercial' => 'Comercial'] as $k => $v)
                                <option value="{{ $k }}" {{ old('pref_tipo_imovel', $cliente->pref_tipo_imovel) === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Bairro de Preferência</label>
                        <input type="text" name="pref_bairro" class="form-control" value="{{ old('pref_bairro', $cliente->pref_bairro) }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Preço Mínimo (R$)</label>
                        <input type="number" step="0.01" name="pref_preco_min" class="form-control" value="{{ old('pref_preco_min', $cliente->pref_preco_min) }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Preço Máximo (R$)</label>
                        <input type="number" step="0.01" name="pref_preco_max" class="form-control" value="{{ old('pref_preco_max', $cliente->pref_preco_max) }}">
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('clientes.index') }}" class="btn btn-default">Voltar</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Salvar Cliente</button>
            </div>
        </form>
    </div>
@stop

@section('js')
<script>
    function alternarCamposPfPj() {
        var tipo = document.getElementById('tipo_pessoa').value;
        document.getElementById('bloco_pf').style.display = (tipo === 'PF') ? 'flex' : 'none';
        document.getElementById('bloco_pj').style.display = (tipo === 'PJ') ? 'flex' : 'none';
    }
    document.addEventListener('DOMContentLoaded', alternarCamposPfPj);
</script>
@stop
