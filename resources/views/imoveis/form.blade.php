@extends('adminlte::page')

@section('title', $imovel->exists ? 'Editar Imóvel' : 'Novo Imóvel')

@section('content_header')
    <h1>{{ $imovel->exists ? 'Editar Imóvel: ' . $imovel->codigo_referencia : 'Cadastrar Novo Imóvel' }}</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <form method="POST" action="{{ $imovel->exists ? route('imoveis.update', $imovel) : route('imoveis.store') }}">
            @csrf
            @if ($imovel->exists)
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

                <h5 class="text-primary border-bottom pb-2"><i class="fas fa-tag mr-1"></i>1. Identificação e Status</h5>
                <div class="row">
                    <div class="form-group col-md-2">
                        <label>Código Ref. *</label>
                        <input type="text" name="codigo_referencia" class="form-control" value="{{ old('codigo_referencia', $imovel->codigo_referencia) }}" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Título do Anúncio *</label>
                        <input type="text" name="titulo" class="form-control" placeholder="Ex: Apartamento 3 Quartos em Manaíra" value="{{ old('titulo', $imovel->titulo) }}" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Tipo *</label>
                        <select name="tipo" class="form-control" required>
                            @foreach (['apartamento' => 'Apartamento', 'casa' => 'Casa', 'terreno' => 'Terreno', 'comercial' => 'Comercial'] as $k => $v)
                                <option value="{{ $k }}" {{ old('tipo', $imovel->tipo) === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Finalidade *</label>
                        <select name="finalidade" class="form-control" required>
                            @foreach (['venda' => 'Venda', 'locacao' => 'Locação', 'ambos' => 'Venda e Locação'] as $k => $v)
                                <option value="{{ $k }}" {{ old('finalidade', $imovel->finalidade) === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Status *</label>
                        <select name="status" class="form-control" required>
                            @foreach (['rascunho' => 'Rascunho', 'disponivel' => 'Disponível', 'reservado' => 'Reservado', 'vendido' => 'Vendido', 'alugado' => 'Alugado', 'inativo' => 'Inativo'] as $k => $v)
                                <option value="{{ $k }}" {{ old('status', $imovel->status) === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Proprietário(s) do Imóvel *</label>
                        @php
                            $selecionados = old('proprietarios', $imovel->exists ? $imovel->proprietarios->pluck('id')->toArray() : []);
                        @endphp
                        <select name="proprietarios[]" class="form-control" multiple required size="3">
                            @forelse ($proprietariosAptos as $prop)
                                <option value="{{ $prop->id }}" {{ in_array($prop->id, $selecionados) ? 'selected' : '' }}>
                                    {{ $prop->nome_razao_social }} ({{ $prop->documento }})
                                </option>
                            @empty
                                <option value="" disabled>Nenhum cliente Proprietário com documentação validada! Cadastre no CRM primeiro.</option>
                            @endforelse
                        </select>
                        <small class="text-muted">Segure Ctrl para selecionar mais de um proprietário.</small>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Corretor Captador / Responsável</label>
                        <select name="corretor_id" class="form-control">
                            <option value="">-- Selecione --</option>
                            @foreach ($corretores as $corretor)
                                <option value="{{ $corretor->id }}" {{ (int) old('corretor_id', $imovel->corretor_id ?? auth()->id()) === $corretor->id ? 'selected' : '' }}>
                                    {{ $corretor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Descrição</label>
                        <textarea name="descricao" class="form-control" rows="3">{{ old('descricao', $imovel->descricao) }}</textarea>
                    </div>
                </div>

                <h5 class="text-primary border-bottom pb-2 mt-3"><i class="fas fa-map-marker-alt mr-1"></i>2. Localização</h5>
                <div class="row">
                    <div class="form-group col-md-2">
                        <label>CEP *</label>
                        <input type="text" name="cep" id="cep" class="form-control" placeholder="00000-000" value="{{ old('cep', $imovel->cep) }}" required onblur="buscarCep(this.value)">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Logradouro *</label>
                        <input type="text" name="logradouro" id="logradouro" class="form-control" value="{{ old('logradouro', $imovel->logradouro) }}" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Número *</label>
                        <input type="text" name="numero" class="form-control" value="{{ old('numero', $imovel->numero) }}" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Complemento</label>
                        <input type="text" name="complemento" class="form-control" value="{{ old('complemento', $imovel->complemento) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-4">
                        <label>Bairro *</label>
                        <input type="text" name="bairro" id="bairro" class="form-control" value="{{ old('bairro', $imovel->bairro) }}" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Cidade *</label>
                        <input type="text" name="cidade" id="cidade" class="form-control" value="{{ old('cidade', $imovel->cidade) }}" required>
                    </div>
                    <div class="form-group col-md-1">
                        <label>UF *</label>
                        <input type="text" name="uf" id="uf" maxlength="2" class="form-control" value="{{ old('uf', $imovel->uf) }}" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Latitude</label>
                        <input type="text" name="latitude" class="form-control" value="{{ old('latitude', $imovel->latitude) }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Longitude</label>
                        <input type="text" name="longitude" class="form-control" value="{{ old('longitude', $imovel->longitude) }}">
                    </div>
                </div>

                <h5 class="text-primary border-bottom pb-2 mt-3"><i class="fas fa-ruler-combined mr-1"></i>3. Características Físicas</h5>
                <div class="row">
                    <div class="form-group col-md-2">
                        <label>Área Útil (m²)</label>
                        <input type="number" step="0.01" name="area_util" class="form-control" value="{{ old('area_util', $imovel->area_util) }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Área Total (m²)</label>
                        <input type="number" step="0.01" name="area_total" class="form-control" value="{{ old('area_total', $imovel->area_total) }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Quartos *</label>
                        <input type="number" name="quartos" class="form-control" value="{{ old('quartos', $imovel->quartos ?? 0) }}" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Suítes *</label>
                        <input type="number" name="suites" class="form-control" value="{{ old('suites', $imovel->suites ?? 0) }}" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Banheiros *</label>
                        <input type="number" name="banheiros" class="form-control" value="{{ old('banheiros', $imovel->banheiros ?? 0) }}" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Vagas Garagem *</label>
                        <input type="number" name="vagas_garagem" class="form-control" value="{{ old('vagas_garagem', $imovel->vagas_garagem ?? 0) }}" required>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="form-group col-md-3">
                        <label>Mobília</label>
                        <select name="mobiliado" class="form-control">
                            <option value="nao" {{ old('mobiliado', $imovel->mobiliado) === 'nao' ? 'selected' : '' }}>Não Mobiliado</option>
                            <option value="semimobiliado" {{ old('mobiliado', $imovel->mobiliado) === 'semimobiliado' ? 'selected' : '' }}>Semimobiliado</option>
                            <option value="mobiliado" {{ old('mobiliado', $imovel->mobiliado) === 'mobiliado' ? 'selected' : '' }}>Mobiliado</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3 pt-4">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="aceita_pets" name="aceita_pets" value="1" {{ old('aceita_pets', $imovel->aceita_pets) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="aceita_pets">Aceita Pets</label>
                        </div>
                    </div>
                    <div class="form-group col-md-3 pt-4">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="imovel_habitado" name="imovel_habitado" value="1" {{ old('imovel_habitado', $imovel->imovel_habitado) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="imovel_habitado">Imóvel Habitado</label>
                        </div>
                    </div>
                </div>

                <h5 class="text-primary border-bottom pb-2 mt-3"><i class="fas fa-dollar-sign mr-1"></i>4. Valores e Links Multimídia</h5>
                <div class="row">
                    <div class="form-group col-md-3">
                        <label>Valor de Venda (R$)</label>
                        <input type="number" step="0.01" name="valor_venda" class="form-control" value="{{ old('valor_venda', $imovel->valor_venda) }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Valor de Aluguel (R$)</label>
                        <input type="number" step="0.01" name="valor_aluguel" class="form-control" value="{{ old('valor_aluguel', $imovel->valor_aluguel) }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Condomínio (R$)</label>
                        <input type="number" step="0.01" name="valor_condominio" class="form-control" value="{{ old('valor_condominio', $imovel->valor_condominio ?? 0) }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label>IPTU (R$)</label>
                        <input type="number" step="0.01" name="valor_iptu" class="form-control" value="{{ old('valor_iptu', $imovel->valor_iptu ?? 0) }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Taxa Adm. (%)</label>
                        <input type="number" step="0.01" name="taxa_administracao" class="form-control" value="{{ old('taxa_administracao', $imovel->taxa_administracao ?? 10) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Link Tour Virtual 360°</label>
                        <input type="url" name="tour_virtual_url" class="form-control" placeholder="https://..." value="{{ old('tour_virtual_url', $imovel->tour_virtual_url) }}">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Link Vídeo YouTube / Vimeo</label>
                        <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/..." value="{{ old('video_url', $imovel->video_url) }}">
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('imoveis.index') }}" class="btn btn-default">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Salvar Imóvel</button>
            </div>
        </form>
    </div>
@stop

@section('js')
<script>
    function buscarCep(valor) {
        var cep = valor.replace(/\D/g, '');
        if (cep.length !== 8) return;

        fetch('https://viacep.com.br/ws/' + cep + '/json/')
            .then(function (r) { return r.json(); })
            .then(function (dados) {
                if (!dados.erro) {
                    document.getElementById('logradouro').value = dados.logradouro || '';
                    document.getElementById('bairro').value = dados.bairro || '';
                    document.getElementById('cidade').value = dados.localidade || '';
                    document.getElementById('uf').value = dados.uf || '';
                }
            });
    }
</script>
@stop
