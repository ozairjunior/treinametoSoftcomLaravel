@extends('adminlte::page')

@section('title', 'Agenda de Visitas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-calendar-check mr-2"></i>Calendário de Visitas</h1>
        <a href="{{ route('visitas.create') }}" class="btn btn-primary">
            <i class="fas fa-calendar-plus mr-1"></i> Agendar Visita
        </a>
    </div>
@stop

@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <form method="GET" action="{{ route('visitas.index') }}" class="row">
                <div class="col-md-3 mb-2">
                    <label class="small mb-0">Corretor</label>
                    <select name="corretor_id" class="form-control">
                        <option value="">Todos os Corretores</option>
                        @foreach ($corretores as $c)
                            <option value="{{ $c->id }}" {{ (int) request('corretor_id') === $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small mb-0">Status</label>
                    <select name="status" class="form-control">
                        <option value="">Todos os Status</option>
                        @foreach (['agendada' => 'Agendada', 'confirmada' => 'Confirmada', 'realizada' => 'Realizada', 'cancelada' => 'Cancelada', 'nao_compareceu' => 'Não Compareceu'] as $k => $v)
                            <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small mb-0">De</label>
                    <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="form-control">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small mb-0">Até</label>
                    <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="form-control">
                </div>
                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary btn-block"><i class="fas fa-filter"></i> Filtrar</button>
                </div>
            </form>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Data e Horário</th>
                        <th>Imóvel</th>
                        <th>Cliente Interessado</th>
                        <th>Corretor</th>
                        <th>Status</th>
                        <th>Interesse / Proposta</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visitas as $v)
                        <tr>
                            <td>
                                <strong>{{ $v->data_hora_inicio->format('d/m/Y') }}</strong><br>
                                <span class="badge badge-light border">
                                    <i class="far fa-clock"></i> {{ $v->data_hora_inicio->format('H:i') }} às {{ $v->data_hora_fim->format('H:i') }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('imoveis.show', $v->imovel) }}">
                                    <code>{{ $v->imovel->codigo_referencia }}</code> — {{ $v->imovel->titulo }}
                                </a><br>
                                <small class="text-muted">{{ $v->imovel->bairro }}, {{ $v->imovel->cidade }}</small>
                            </td>
                            <td>
                                <a href="{{ route('clientes.show', $v->cliente) }}"><strong>{{ $v->cliente->nome_razao_social }}</strong></a><br>
                                <small class="text-muted">{{ $v->cliente->telefone }}</small>
                            </td>
                            <td>{{ $v->corretor->name }}</td>
                            <td>
                                @php
                                    $cor = [
                                        'agendada' => 'info',
                                        'confirmada' => 'primary',
                                        'realizada' => 'success',
                                        'cancelada' => 'secondary',
                                        'nao_compareceu' => 'danger',
                                    ][$v->status] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $cor }} text-uppercase">{{ str_replace('_', ' ', $v->status) }}</span>
                            </td>
                            <td>
                                @if ($v->nivel_interesse)
                                    <span class="badge badge-dark text-uppercase">Interesse: {{ $v->nivel_interesse }}</span>
                                @endif
                                @if ($v->intencao_proposta)
                                    <span class="badge badge-success"><i class="fas fa-file-signature"></i> Quer Proposta</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('visitas.show', $v) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-clipboard-check"></i> Check-in / Feedback
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Nenhuma visita agendada para os filtros selecionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $visitas->withQueryString()->links() }}
        </div>
    </div>
@stop
