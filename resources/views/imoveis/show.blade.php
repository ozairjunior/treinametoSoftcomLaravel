@extends('adminlte::page')

@section('title', $imovel->codigo_referencia . ' - ' . $imovel->titulo)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <code>{{ $imovel->codigo_referencia }}</code> — {{ $imovel->titulo }}
        </h1>
        <div>
            <a href="{{ route('imoveis.edit', $imovel) }}" class="btn btn-info"><i class="fas fa-edit mr-1"></i> Editar Dados</a>
            <a href="{{ route('imoveis.index') }}" class="btn btn-default">Voltar</a>
        </div>
    </div>
@stop

@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">Status e Informações do Imóvel</h3></div>
                <div class="card-body">
                    <form action="{{ route('imoveis.status', $imovel) }}" method="POST" class="d-flex">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="form-control mr-2">
                            @foreach (['rascunho' => 'Rascunho', 'disponivel' => 'Disponível', 'reservado' => 'Reservado / Em Proposta', 'vendido' => 'Vendido', 'alugado' => 'Alugado', 'inativo' => 'Inativo'] as $k => $v)
                                <option value="{{ $k }}" {{ $imovel->status === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary">Atualizar</button>
                    </form>
                    <hr>
                    <p><strong>Endereço:</strong> {{ $imovel->logradouro }}, {{ $imovel->numero }} {{ $imovel->complemento }} — {{ $imovel->bairro }}, {{ $imovel->cidade }}/{{ $imovel->uf }} ({{ $imovel->cep }})</p>
                    <p><strong>Características:</strong> {{ $imovel->quartos }} quartos ({{ $imovel->suites }} suítes), {{ $imovel->banheiros }} banheiros, {{ $imovel->vagas_garagem }} vagas, {{ $imovel->area_util }} m² útil.</p>
                    <p><strong>Venda:</strong> R$ {{ number_format($imovel->valor_venda ?? 0, 2, ',', '.') }} | <strong>Aluguel:</strong> R$ {{ number_format($imovel->valor_aluguel ?? 0, 2, ',', '.') }}</p>
                    <p><strong>Proprietário(s):</strong><br>
                        @foreach ($imovel->proprietarios as $prop)
                            <a href="{{ route('clientes.show', $prop) }}" class="badge badge-primary">{{ $prop->nome_razao_social }}</a>
                        @endforeach
                    </p>
                    @if ($imovel->tour_virtual_url)
                        <a href="{{ $imovel->tour_virtual_url }}" target="_blank" class="btn btn-sm btn-outline-info btn-block"><i class="fas fa-vr-cardboard mr-1"></i> Abrir Tour Virtual 360°</a>
                    @endif
                    @if ($imovel->video_url)
                        <a href="{{ $imovel->video_url }}" target="_blank" class="btn btn-sm btn-outline-danger btn-block"><i class="fab fa-youtube mr-1"></i> Assistir Vídeo do Imóvel</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-cloud-upload-alt mr-1"></i>Enviar Fotos, Plantas Baixas ou Documentos</h3></div>
                <form action="{{ route('imoveis.midias.upload', $imovel) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body row align-items-end">
                        <div class="form-group col-md-3 mb-2">
                            <label>Categoria *</label>
                            <select name="categoria" class="form-control" required>
                                <option value="foto">Foto do Imóvel</option>
                                <option value="planta">Planta Baixa</option>
                                <option value="documento">Matrícula / Certidão (Restrito)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4 mb-2">
                            <label>Título / Legenda</label>
                            <input type="text" name="titulo" class="form-control" placeholder="Ex: Sala de Estar / Matrícula Atualizada">
                        </div>
                        <div class="form-group col-md-3 mb-2">
                            <label>Arquivo(s) *</label>
                            <input type="file" name="arquivos[]" class="form-control-file" multiple required>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="submit" class="btn btn-info btn-block"><i class="fas fa-upload"></i> Enviar</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Galeria e Documentos do Imóvel</h3></div>
                <div class="card-body">
                    <div class="row">
                        @forelse ($imovel->midias as $midia)
                            @if (!$midia->acesso_restrito || auth()->user()->role !== 'cliente')
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 {{ $midia->is_capa ? 'border-primary' : '' }}">
                                        @if ($midia->categoria === 'foto' || $midia->categoria === 'planta')
                                            <img src="{{ asset('storage/' . $midia->arquivo_path) }}" class="card-img-top" style="height: 160px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 160px;">
                                                <i class="fas fa-file-pdf fa-4x text-danger"></i>
                                            </div>
                                        @endif
                                        <div class="card-body p-2">
                                            <span class="badge badge-dark text-uppercase">{{ $midia->categoria }}</span>
                                            @if ($midia->is_capa)
                                                <span class="badge badge-primary">CAPA</span>
                                            @endif
                                            @if ($midia->acesso_restrito)
                                                <span class="badge badge-danger"><i class="fas fa-lock"></i> Restrito</span>
                                            @endif
                                            <p class="small mb-2 mt-1 text-truncate">{{ $midia->titulo }}</p>
                                            <div class="d-flex justify-content-between">
                                                <a href="{{ asset('storage/' . $midia->arquivo_path) }}" target="_blank" class="btn btn-xs btn-default">Abrir</a>
                                                @if ($midia->categoria === 'foto' && !$midia->is_capa)
                                                    <form action="{{ route('imoveis.midias.capa', [$imovel, $midia]) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-xs btn-outline-primary">Definir Capa</button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('imoveis.midias.remover', [$imovel, $midia]) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="col-12 text-center text-muted py-4">Nenhuma foto, planta ou documento enviado para este imóvel.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
