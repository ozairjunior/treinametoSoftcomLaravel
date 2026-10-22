@extends('adminlte::page')

@section('title', 'Contratos e Pós-Contrato')

@section('content_header')
    <h1><i class="fas fa-file-contract mr-2"></i>Gestão de Contratos e Assinaturas</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Imóvel / Cliente</th>
                        <th>Valor / Reajuste</th>
                        <th>Imutabilidade</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contratos as $c)
                        <tr>
                            <td><code>{{ $c->codigo_contrato }}</code></td>
                            <td><span class="badge badge-dark text-uppercase">{{ str_replace('_', ' ', $c->tipo_contrato) }}</span></td>
                            <td>
                                <strong>{{ $c->imovel->codigo_referencia }}</strong> — {{ $c->cliente->nome_razao_social }}
                            </td>
                            <td>
                                R$ {{ number_format($c->valor_final, 2, ',', '.') }}<br>
                                <small class="text-muted">Reajuste: {{ $c->indice_reajuste }} | Vigência: {{ $c->vigencia_inicio->format('d/m/Y') }}</small>
                            </td>
                            <td>
                                @if ($c->possuiAssinatura())
                                    <span class="badge badge-danger"><i class="fas fa-lock"></i> Bloqueado / Imutável</span>
                                @else
                                    <span class="badge badge-secondary"><i class="fas fa-unlock"></i> Editável</span>
                                @endif
                            </td>
                            <td><span class="badge badge-info text-uppercase">{{ str_replace('_', ' ', $c->status) }}</span></td>
                            <td class="text-right">
                                <a href="{{ route('contratos.show', $c) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-file-signature"></i> Abrir Contrato
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Nenhum contrato gerado ainda. Aceite uma proposta em "Propostas" para gerar um contrato!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
