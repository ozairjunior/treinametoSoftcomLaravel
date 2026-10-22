@extends('adminlte::page')

@section('title', 'CRM de Clientes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-address-book mr-2"></i>CRM de Clientes</h1>
        <a href="{{ route('clientes.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus mr-1"></i> Novo Cliente
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
            <form method="GET" action="{{ route('clientes.index') }}" class="row">
                <div class="col-md-5">
                    <input type="text" name="busca" value="{{ request('busca') }}" class="form-control" placeholder="Buscar por nome, razão social, e-mail ou CPF/CNPJ exato...">
                </div>
                <div class="col-md-2">
                    <select name="tipo_pessoa" class="form-control">
                        <option value="">PF e PJ</option>
                        <option value="PF" {{ request('tipo_pessoa') === 'PF' ? 'selected' : '' }}>Pessoa Física (PF)</option>
                        <option value="PJ" {{ request('tipo_pessoa') === 'PJ' ? 'selected' : '' }}>Pessoa Jurídica (PJ)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="papel" class="form-control">
                        <option value="">Todos os Papéis</option>
                        <option value="proprietario" {{ request('papel') === 'proprietario' ? 'selected' : '' }}>Proprietário</option>
                        <option value="interessado" {{ request('papel') === 'interessado' ? 'selected' : '' }}>Interessado / Lead</option>
                        <option value="locatario_comprador" {{ request('papel') === 'locatario_comprador' ? 'selected' : '' }}>Locatário / Comprador</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary btn-block"><i class="fas fa-search"></i> Filtrar</button>
                </div>
            </form>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Cliente / Razão Social</th>
                        <th>Tipo / Documento</th>
                        <th>Papéis</th>
                        <th>Documentação</th>
                        <th>Corretor Resp.</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clientes as $c)
                        <tr>
                            <td>
                                <a href="{{ route('clientes.show', $c) }}"><strong>{{ $c->nome_razao_social }}</strong></a><br>
                                <small class="text-muted">{{ $c->telefone ?? '-' }} | {{ $c->email ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge badge-dark">{{ $c->tipo_pessoa }}</span>
                                <code>{{ $c->documento }}</code>
                            </td>
                            <td>
                                @if ($c->is_proprietario)
                                    <span class="badge badge-primary">Proprietário</span>
                                @endif
                                @if ($c->is_interessado)
                                    <span class="badge badge-info">Interessado/Lead</span>
                                @endif
                                @if ($c->is_locatario_comprador)
                                    <span class="badge badge-success">Locatário/Comprador</span>
                                @endif
                            </td>
                            <td>
                                @if ($c->documentacao_validada)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Validada</span>
                                @else
                                    <span class="badge badge-secondary">Pendente</span>
                                @endif
                            </td>
                            <td>{{ $c->corretor->name ?? 'Sem corretor' }}</td>
                            <td class="text-right">
                                <a href="{{ route('clientes.show', $c) }}" class="btn btn-sm btn-primary" title="Linha do Tempo e Detalhes">
                                    <i class="fas fa-stream"></i> CRM
                                </a>
                                <a href="{{ route('clientes.edit', $c) }}" class="btn btn-sm btn-info" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Nenhum cliente cadastrado ainda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $clientes->withQueryString()->links() }}
        </div>
    </div>
@stop
