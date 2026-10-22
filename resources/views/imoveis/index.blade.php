@extends('adminlte::page')

@section('title', 'Gestão de Imóveis')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-building mr-2"></i>Carteira de Imóveis</h1>
        <a href="{{ route('imoveis.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Novo Imóvel
        </a>
    </div>
@stop

@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <form method="GET" action="{{ route('imoveis.index') }}" class="row">
                <div class="col-md-3 mb-2">
                    <input type="text" name="busca" value="{{ request('busca') }}" class="form-control" placeholder="Código, título ou rua...">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="text" name="bairro" value="{{ request('bairro') }}" class="form-control" placeholder="Bairro...">
                </div>
                <div class="col-md-2 mb-2">
                    <select name="tipo" class="form-control">
                        <option value="">Todos os Tipos</option>
                        @foreach (['apartamento' => 'Apartamento', 'casa' => 'Casa', 'terreno' => 'Terreno', 'comercial' => 'Comercial'] as $k => $v)
                            <option value="{{ $k }}" {{ request('tipo') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="status" class="form-control">
                        <option value="">Todos os Status</option>
                        @foreach (['rascunho', 'disponivel', 'reservado', 'vendido', 'alugado', 'inativo'] as $st)
                            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <input type="number" name="preco_max" value="{{ request('preco_max') }}" class="form-control" placeholder="Preço Máx (R$)">
                </div>
                <div class="col-md-1 mb-2">
                    <button type="submit" class="btn btn-secondary btn-block"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Ref. / Imóvel</th>
                        <th>Tipo / Finalidade</th>
                        <th>Localização</th>
                        <th>Características</th>
                        <th>Valores</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($imoveis as $imovel)
                        <tr>
                            <td>
                                <code>{{ $imovel->codigo_referencia }}</code><br>
                                <a href="{{ route('imoveis.show', $imovel) }}"><strong>{{ $imovel->titulo }}</strong></a>
                            </td>
                            <td>
                                <span class="badge badge-dark text-uppercase">{{ $imovel->tipo }}</span>
                                <span class="badge badge-info text-uppercase">{{ $imovel->finalidade }}</span>
                            </td>
                            <td>
                                {{ $imovel->bairro }} — {{ $imovel->cidade }}/{{ $imovel->uf }}<br>
                                <small class="text-muted">{{ $imovel->logradouro }}, {{ $imovel->numero }}</small>
                            </td>
                            <td>
                                <small>
                                    <i class="fas fa-bed"></i> {{ $imovel->quartos }} ({{ $imovel->suites }} suítes) |
                                    <i class="fas fa-car"></i> {{ $imovel->vagas_garagem }} |
                                    <i class="fas fa-ruler-combined"></i> {{ $imovel->area_util ?? 0 }} m²
                                </small>
                            </td>
                            <td>
                                @if ($imovel->valor_venda)
                                    <div><strong>Venda:</strong> R$ {{ number_format($imovel->valor_venda, 2, ',', '.') }}</div>
                                @endif
                                @if ($imovel->valor_aluguel)
                                    <div><strong>Aluguel:</strong> R$ {{ number_format($imovel->valor_aluguel, 2, ',', '.') }}</div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $cores = [
                                        'rascunho' => 'secondary',
                                        'disponivel' => 'success',
                                        'reservado' => 'warning',
                                        'vendido' => 'primary',
                                        'alugado' => 'info',
                                        'inativo' => 'danger',
                                    ];
                                @endphp
                                <span class="badge badge-{{ $cores[$imovel->status] ?? 'secondary' }} text-uppercase">
                                    {{ $imovel->status }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('imoveis.show', $imovel) }}" class="btn btn-sm btn-primary" title="Fotos, Plantas e Detalhes">
                                    <i class="fas fa-images"></i> Gerenciar
                                </a>
                                <a href="{{ route('imoveis.edit', $imovel) }}" class="btn btn-sm btn-info" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Nenhum imóvel encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $imoveis->withQueryString()->links() }}
        </div>
    </div>
@stop
