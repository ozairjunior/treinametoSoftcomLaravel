@extends('adminlte::page')

@section('title', 'Auditoria de Acesso')

@section('content_header')
    <h1><i class="fas fa-shield-alt mr-2"></i>Auditoria de Acesso e Ações do Sistema</h1>
@stop

@section('content')
    <div class="card card-outline card-dark">
        <div class="card-body table-responsive p-0">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Data / Hora</th>
                        <th>Usuário</th>
                        <th>Evento</th>
                        <th>Descrição</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $log->user->name ?? 'Visitante / Não autenticado' }}</td>
                            <td><span class="badge badge-secondary">{{ $log->evento }}</span></td>
                            <td>{{ $log->descricao }}</td>
                            <td><code>{{ $log->ip_address }}</code></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhum registro de auditoria ainda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
    </div>
@stop
