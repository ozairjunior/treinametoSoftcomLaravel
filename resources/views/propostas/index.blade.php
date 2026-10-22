@extends('adminlte::page')

@section('title', 'Propostas Comerciais')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-handshake mr-2"></i>Propostas e Negociações</h1>
        <a href="{{ route('propostas.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Nova Proposta
        </a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th># / Imóvel</th>
                        <th>Cliente Interessado</th>
                        <th>Valor Atual / Pagamento</th>
                        <th>Rodada</th>
                        <th>Validade</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($propostas as $p)
                        <tr>
                            <td>
                                <strong>#{{ $p->id }}</strong> —
                                <a href="{{ route('imoveis.show', $p->imovel) }}"><code>{{ $p->imovel->codigo_referencia }}</code></a><br>
                                <small class="text-muted">{{ $p->imovel->titulo }}</small>
                            </td>
                            <td>{{ $p->cliente->nome_razao_social }}</td>
                            <td>
                                <strong>R$ {{ number_format($p->valor_oferecido, 2, ',', '.') }}</strong><br>
                                <small class="text-uppercase text-muted">{{ str_replace('_', ' ', $p->forma_pagamento) }}</small>
                            </td>
                            <td><span class="badge badge-dark">{{ $p->rodada_atual }}ª Rodada</span></td>
                            <td>{{ $p->valida_ate->format('d/m/Y H:i') }}</td>
                            <td>
                                @php
                                    $cor = [
                                        'em_analise' => 'warning',
                                        'aceita' => 'success',
                                        'recusada' => 'danger',
                                        'expirada' => 'secondary',
                                    ][$p->status] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $cor }} text-uppercase">{{ str_replace('_', ' ', $p->status) }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('propostas.show', $p) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-comments-dollar"></i> Negociar / Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Nenhuma proposta registrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
