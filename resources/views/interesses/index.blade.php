@extends('adminlte::page')

@section('title', 'Interesses Recebidos do Catálogo')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-bullhorn mr-2"></i>Interesses do Catálogo Público</h1>
        <a href="{{ route('catalogo.index') }}" target="_blank" class="btn btn-outline-primary">
            <i class="fas fa-external-link-alt mr-1"></i> Abrir Catálogo Público
        </a>
    </div>
@stop

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <form method="GET" action="{{ route('interesses.index') }}" class="row">
                <div class="col-md-5 mb-2">
                    <input type="text" name="busca" value="{{ request('busca') }}" class="form-control" placeholder="Buscar por cliente, telefone, e-mail ou código do imóvel...">
                </div>
                <div class="col-md-3 mb-2">
                    <select name="tipo" class="form-control">
                        <option value="">Todos os Tipos</option>
                        <option value="contato" {{ request('tipo') === 'contato' ? 'selected' : '' }}>Contato / Dúvida</option>
                        <option value="proposta" {{ request('tipo') === 'proposta' ? 'selected' : '' }}>Intenção de Proposta</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="status" class="form-control">
                        <option value="">Todos os Status</option>
                        @foreach ([
                            'novo' => 'Novo',
                            'em_atendimento' => 'Em Atendimento',
                            'contatado' => 'Contatado',
                            'convertido' => 'Convertido',
                            'encerrado' => 'Encerrado'
                        ] as $k => $v)
                            <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-secondary btn-block"><i class="fas fa-search"></i> Filtrar</button>
                </div>
            </form>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Cliente</th>
                        <th>Imóvel</th>
                        <th>Corretor Responsável</th>
                        <th>Tipo / Valor Proposto</th>
                        <th>Mensagem</th>
                        <th>Status</th>
                        <th class="text-right">Atualizar Status / Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($interesses as $interesse)
                        <tr>
                            <td class="text-nowrap">
                                <small>{{ $interesse->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                @if ($interesse->cliente)
                                    <a href="{{ route('clientes.show', $interesse->cliente) }}">
                                        <strong>{{ $interesse->cliente->nome_razao_social }}</strong>
                                    </a>
                                    @if ($interesse->cliente->cadastro_status === 'pre_cadastro')
                                        <span class="badge badge-warning">Pré-cadastro</span>
                                    @else
                                        <span class="badge badge-success">Completo</span>
                                    @endif
                                    <br>
                                    <small class="text-muted">
                                        {{ $interesse->cliente->telefone ?? '-' }} | {{ $interesse->cliente->email ?? '-' }}
                                    </small>
                                @else
                                    <span class="text-muted">Cliente removido</span>
                                @endif
                            </td>
                            <td>
                                @if ($interesse->imovel)
                                    <code>{{ $interesse->imovel->codigo_referencia }}</code><br>
                                    <a href="{{ route('imoveis.show', $interesse->imovel) }}">
                                        {{ $interesse->imovel->titulo }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($interesse->corretor)
                                    {{ $interesse->corretor->name }}
                                @else
                                    <span class="badge badge-secondary">Aguardando Distribuição</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if ($interesse->tipo === 'proposta')
                                    <span class="badge badge-primary">PROPOSTA</span><br>
                                    <strong>R$ {{ number_format($interesse->valor_proposto, 2, ',', '.') }}</strong>
                                @else
                                    <span class="badge badge-info">CONTATO</span><br>
                                    <small class="text-muted">Sem valor sugerido</small>
                                @endif
                            </td>
                            <td style="max-width: 260px;">
                                <small>{{ $interesse->mensagem }}</small>
                            </td>
                            <td class="text-nowrap">
                                @php
                                    $coresStatus = [
                                        'novo' => 'danger',
                                        'em_atendimento' => 'warning',
                                        'contatado' => 'info',
                                        'convertido' => 'success',
                                        'encerrado' => 'secondary',
                                    ];
                                    $nomesStatus = [
                                        'novo' => 'Novo',
                                        'em_atendimento' => 'Em Atendimento',
                                        'contatado' => 'Contatado',
                                        'convertido' => 'Convertido',
                                        'encerrado' => 'Encerrado',
                                    ];
                                @endphp
                                <span class="badge badge-{{ $coresStatus[$interesse->status] ?? 'secondary' }}">
                                    {{ $nomesStatus[$interesse->status] ?? strtoupper($interesse->status) }}
                                </span>
                            </td>
                            <td class="text-right text-nowrap">
                                <form method="POST" action="{{ route('interesses.status', $interesse) }}" class="d-inline-flex align-items-center">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-control form-control-sm mr-1" style="width: 145px;">
                                        @foreach ($nomesStatus as $chave => $rotulo)
                                            <option value="{{ $chave }}" {{ $interesse->status === $chave ? 'selected' : '' }}>
                                                {{ $rotulo }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary" title="Salvar Status">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </form>
                                @if ($interesse->cliente)
                                    <a href="{{ route('clientes.show', $interesse->cliente) }}" class="btn btn-sm btn-outline-info ml-1" title="Abrir Ficha do Cliente">
                                        <i class="fas fa-user"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Nenhum interesse registrado pelo catálogo público até o momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $interesses->withQueryString()->links() }}
        </div>
    </div>
@stop
