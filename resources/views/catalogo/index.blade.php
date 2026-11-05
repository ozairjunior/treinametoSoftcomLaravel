<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catálogo de Imóveis — Softcom Imobiliária</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .hero-catalogo {
            background: linear-gradient(135deg, #001f3f 0%, #007bff 100%);
            color: #fff;
            padding: 2.5rem 0;
        }
        .card-imovel {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        }
        .card-imovel:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }
        .capa-imovel {
            height: 210px;
            object-fit: cover;
            width: 100%;
        }
        .capa-placeholder {
            height: 210px;
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-navy shadow-sm">
        <div class="container">
            <a class="navbar-brand font-weight-bold" href="{{ route('catalogo.index') }}">
                <i class="fas fa-building mr-2 text-primary"></i>Softcom Imobiliária
            </a>
            <div class="ml-auto d-flex align-items-center">
                @auth
                    <a href="{{ route('home') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-tachometer-alt mr-1"></i> Painel Administrativo
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-user-lock mr-1"></i> Área do Corretor
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <section class="hero-catalogo">
        <div class="container text-center">
            <h1 class="font-weight-bold mb-2">Encontre o Imóvel Ideal para Você</h1>
            <p class="lead mb-0 text-white-50">Confira nossa seleção de imóveis disponíveis para compra e locação</p>
        </div>
    </section>

    <main class="container py-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('catalogo.index') }}">
                    <div class="form-row">
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Busca</label>
                            <input type="text" name="busca" value="{{ request('busca') }}" class="form-control" placeholder="Título, código ou região...">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Finalidade</label>
                            <select name="finalidade" class="form-control">
                                <option value="">Compra e Locação</option>
                                <option value="venda" {{ request('finalidade') === 'venda' ? 'selected' : '' }}>Comprar</option>
                                <option value="locacao" {{ request('finalidade') === 'locacao' ? 'selected' : '' }}>Alugar</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Tipo de Imóvel</label>
                            <select name="tipo" class="form-control">
                                <option value="">Todos os Tipos</option>
                                @foreach (['apartamento' => 'Apartamento', 'casa' => 'Casa', 'terreno' => 'Terreno', 'comercial' => 'Comercial'] as $k => $v)
                                    <option value="{{ $k }}" {{ request('tipo') === $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Bairro</label>
                            <input type="text" name="bairro" value="{{ request('bairro') }}" class="form-control" placeholder="Ex: Cabo Branco">
                        </div>
                        <div class="col-md-1 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Quartos</label>
                            <select name="quartos" class="form-control">
                                <option value="">Todos</option>
                                <option value="1" {{ request('quartos') == '1' ? 'selected' : '' }}>1+</option>
                                <option value="2" {{ request('quartos') == '2' ? 'selected' : '' }}>2+</option>
                                <option value="3" {{ request('quartos') == '3' ? 'selected' : '' }}>3+</option>
                                <option value="4" {{ request('quartos') == '4' ? 'selected' : '' }}>4+</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Valor Máximo (R$)</label>
                            <input type="number" name="preco_max" value="{{ request('preco_max') }}" class="form-control" placeholder="Ex: 1500000">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-2">
                        <a href="{{ route('catalogo.index') }}" class="btn btn-default btn-sm mr-2">Limpar Filtros</a>
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            <i class="fas fa-search mr-1"></i> Buscar Imóveis
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            @forelse ($imoveis as $imovel)
                <div class="col-md-4 mb-4">
                    <div class="card card-imovel h-100 d-flex flex-column">
                        @if ($imovel->fotoCapa && !$imovel->fotoCapa->acesso_restrito)
                            <img src="{{ asset('storage/' . $imovel->fotoCapa->arquivo_path) }}" alt="{{ $imovel->titulo }}" class="capa-imovel card-img-top">
                        @else
                            <div class="capa-placeholder card-img-top">
                                <div class="text-center">
                                    <i class="fas fa-home fa-3x mb-2"></i>
                                    <div class="small font-weight-bold">{{ strtoupper($imovel->tipo) }}</div>
                                </div>
                            </div>
                        @endif

                        <div class="card-body d-flex flex-column flex-grow-1">
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge badge-dark text-uppercase">{{ $imovel->tipo }}</span>
                                    <span class="badge badge-info text-uppercase">
                                        {{ $imovel->finalidade === 'ambos' ? 'Venda e Locação' : ($imovel->finalidade === 'venda' ? 'Venda' : 'Locação') }}
                                    </span>
                                </div>
                                <small class="text-muted"><code>{{ $imovel->codigo_referencia }}</code></small>
                            </div>

                            <h5 class="card-title font-weight-bold mb-1">
                                <a href="{{ route('catalogo.show', $imovel) }}" class="text-dark">
                                    {{ $imovel->titulo }}
                                </a>
                            </h5>

                            <p class="text-muted small mb-2">
                                <i class="fas fa-map-marker-alt text-primary mr-1"></i>
                                {{ $imovel->bairro }} — {{ $imovel->cidade }}/{{ $imovel->uf }}
                            </p>

                            <p class="small text-secondary mb-3">
                                {{ \Illuminate\Support\Str::limit($imovel->descricao, 95) }}
                            </p>

                            <div class="bg-light rounded p-2 mb-3 small d-flex justify-content-around text-center">
                                <div>
                                    <i class="fas fa-ruler-combined text-primary d-block mb-1"></i>
                                    <strong>{{ $imovel->area_util ?? $imovel->area_total ?? 0 }}</strong> m²
                                </div>
                                <div>
                                    <i class="fas fa-bed text-primary d-block mb-1"></i>
                                    <strong>{{ $imovel->quartos }}</strong> Qtos
                                </div>
                                <div>
                                    <i class="fas fa-bath text-primary d-block mb-1"></i>
                                    <strong>{{ $imovel->banheiros }}</strong> Banh
                                </div>
                                <div>
                                    <i class="fas fa-car text-primary d-block mb-1"></i>
                                    <strong>{{ $imovel->vagas_garagem }}</strong> Vagas
                                </div>
                            </div>

                            <div class="mt-auto">
                                <div class="mb-3">
                                    @if ($imovel->valor_venda)
                                        <div class="h5 font-weight-bold text-primary mb-0">
                                            R$ {{ number_format($imovel->valor_venda, 2, ',', '.') }}
                                            <small class="text-muted font-weight-normal">(Venda)</small>
                                        </div>
                                    @endif
                                    @if ($imovel->valor_aluguel)
                                        <div class="h6 font-weight-bold text-success mb-0 mt-1">
                                            R$ {{ number_format($imovel->valor_aluguel, 2, ',', '.') }}/mês
                                            <small class="text-muted font-weight-normal">(Locação)</small>
                                        </div>
                                    @endif
                                </div>

                                <a href="{{ route('catalogo.show', $imovel) }}" class="btn btn-primary btn-block">
                                    <i class="fas fa-eye mr-1"></i> Ver Detalhes / Tenho Interesse
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum imóvel disponível encontrado com os filtros selecionados.</h5>
                            <a href="{{ route('catalogo.index') }}" class="btn btn-outline-primary mt-2">Ver Todos os Imóveis Disponíveis</a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $imoveis->withQueryString()->links() }}
        </div>
    </main>

    <footer class="bg-white border-top py-4 mt-5">
        <div class="container text-center text-muted small">
            <strong>Softcom Imobiliária</strong> — Catálogo Público de Imóveis
        </div>
    </footer>
</body>
</html>
