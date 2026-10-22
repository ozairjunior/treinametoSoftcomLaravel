@extends('adminlte::page')

@section('title', 'Colaboradores e Usuários')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-users-cog mr-2"></i>Gestão de Colaboradores e Usuários</h1>
        <a href="{{ route('colaboradores.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Novo Colaborador
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

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <form method="GET" action="{{ route('colaboradores.index') }}" class="row">
                <div class="col-md-4">
                    <input type="text" name="busca" value="{{ request('busca') }}" class="form-control" placeholder="Buscar por nome, e-mail, CPF ou CRECI...">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-control">
                        <option value="">Todos os Perfis</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrador</option>
                        <option value="corretor" {{ request('role') === 'corretor' ? 'selected' : '' }}>Corretor</option>
                        <option value="financeiro" {{ request('role') === 'financeiro' ? 'selected' : '' }}>Financeiro / Jurídico</option>
                        <option value="cliente" {{ request('role') === 'cliente' ? 'selected' : '' }}>Cliente</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">Todos os Status</option>
                        <option value="ativo" {{ request('status') === 'ativo' ? 'selected' : '' }}>Ativo</option>
                        <option value="suspenso" {{ request('status') === 'suspenso' ? 'selected' : '' }}>Suspenso</option>
                        <option value="inativo" {{ request('status') === 'inativo' ? 'selected' : '' }}>Inativo</option>
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
                        <th>Nome</th>
                        <th>Perfil</th>
                        <th>CPF / CRECI</th>
                        <th>Telefone</th>
                        <th>Comissão</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($colaboradores as $c)
                        <tr>
                            <td>
                                <strong>{{ $c->name }}</strong><br>
                                <small class="text-muted">{{ $c->email }}</small>
                            </td>
                            <td>
                                <span class="badge badge-info text-uppercase">{{ $c->role }}</span>
                            </td>
                            <td>
                                <div>{{ $c->cpf ?? '-' }}</div>
                                <small class="text-primary">{{ $c->creci ?? '' }}</small>
                            </td>
                            <td>{{ $c->telefone ?? '-' }}</td>
                            <td>{{ number_format($c->comissao_padrao, 2, ',', '.') }}%</td>
                            <td>
                                @php
                                    $corStatus = ['ativo' => 'success', 'suspenso' => 'warning', 'inativo' => 'danger'][$c->status] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $corStatus }} text-uppercase">{{ $c->status }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('colaboradores.edit', $c) }}" class="btn btn-sm btn-info" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('colaboradores.status', $c) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    @if ($c->status !== 'ativo')
                                        <button type="submit" name="status" value="ativo" class="btn btn-sm btn-success" title="Ativar Conta">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                    @if ($c->status !== 'suspenso')
                                        <button type="submit" name="status" value="suspenso" class="btn btn-sm btn-warning" title="Suspender Conta">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                    @endif
                                    @if ($c->status !== 'inativo')
                                        <button type="submit" name="status" value="inativo" class="btn btn-sm btn-danger" title="Inativar Conta">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Nenhum usuário encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $colaboradores->withQueryString()->links() }}
        </div>
    </div>
@stop
