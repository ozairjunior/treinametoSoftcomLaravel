<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $imovel->titulo }} — Catálogo Softcom Imobiliária</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .foto-principal {
            width: 100%;
            height: 380px;
            object-fit: cover;
            border-radius: 6px;
        }
        .foto-miniatura {
            width: 100%;
            height: 90px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .foto-miniatura:hover {
            border-color: #007bff;
        }
        .placeholder-detalhe {
            width: 100%;
            height: 320px;
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
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
            <div class="ml-auto">
                <a href="{{ route('catalogo.index') }}" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar ao Catálogo
                </a>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @if (session('status'))
            <div class="alert alert-success shadow-sm">
                <i class="fas fa-check-circle mr-1"></i> {{ session('status') }}
            </div>
        @endif

        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="badge badge-dark text-uppercase">{{ $imovel->tipo }}</span>
                                <span class="badge badge-info text-uppercase">
                                    {{ $imovel->finalidade === 'ambos' ? 'Venda e Locação' : ($imovel->finalidade === 'venda' ? 'Venda' : 'Locação') }}
                                </span>
                            </div>
                            <span class="text-muted small">Ref: <code>{{ $imovel->codigo_referencia }}</code></span>
                        </div>

                        <h2 class="font-weight-bold mb-1">{{ $imovel->titulo }}</h2>
                        <p class="text-muted mb-3">
                            <i class="fas fa-map-marker-alt text-primary mr-1"></i>
                            {{ $imovel->bairro }} — {{ $imovel->cidade }}/{{ $imovel->uf }}
                        </p>

                        @if ($imovel->fotos->isNotEmpty())
                            @php
                                $fotoInicial = $imovel->fotoCapa ?? $imovel->fotos->first();
                            @endphp
                            <div class="mb-3">
                                <img id="imagem-destaque" src="{{ asset('storage/' . $fotoInicial->arquivo_path) }}" alt="{{ $imovel->titulo }}" class="foto-principal">
                            </div>
                            @if ($imovel->fotos->count() > 1)
                                <div class="row">
                                    @foreach ($imovel->fotos as $foto)
                                        <div class="col-3 mb-2">
                                            <img src="{{ asset('storage/' . $foto->arquivo_path) }}"
                                                 alt="{{ $foto->titulo }}"
                                                 class="foto-miniatura"
                                                 onclick="document.getElementById('imagem-destaque').src = this.src;">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <div class="placeholder-detalhe mb-3">
                                <div class="text-center">
                                    <i class="fas fa-camera fa-4x mb-2"></i>
                                    <div>Sem fotos públicas cadastradas</div>
                                </div>
                            </div>
                        @endif

                        <div class="row bg-light rounded p-3 my-3 text-center">
                            <div class="col-4 col-md-2 mb-2">
                                <small class="text-muted d-block">Área Útil</small>
                                <strong>{{ $imovel->area_util ?? 0 }} m²</strong>
                            </div>
                            <div class="col-4 col-md-2 mb-2">
                                <small class="text-muted d-block">Área Total</small>
                                <strong>{{ $imovel->area_total ?? 0 }} m²</strong>
                            </div>
                            <div class="col-4 col-md-2 mb-2">
                                <small class="text-muted d-block">Quartos</small>
                                <strong>{{ $imovel->quartos }}</strong>
                            </div>
                            <div class="col-4 col-md-2 mb-2">
                                <small class="text-muted d-block">Suítes</small>
                                <strong>{{ $imovel->suites }}</strong>
                            </div>
                            <div class="col-4 col-md-2 mb-2">
                                <small class="text-muted d-block">Banheiros</small>
                                <strong>{{ $imovel->banheiros }}</strong>
                            </div>
                            <div class="col-4 col-md-2 mb-2">
                                <small class="text-muted d-block">Vagas</small>
                                <strong>{{ $imovel->vagas_garagem }}</strong>
                            </div>
                        </div>

                        <h5 class="font-weight-bold border-bottom pb-2">Valores</h5>
                        <div class="row mb-3">
                            @if ($imovel->valor_venda)
                                <div class="col-md-6 mb-2">
                                    <div class="p-3 border rounded bg-white">
                                        <small class="text-muted d-block">Valor de Venda</small>
                                        <span class="h4 font-weight-bold text-primary">R$ {{ number_format($imovel->valor_venda, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endif
                            @if ($imovel->valor_aluguel)
                                <div class="col-md-6 mb-2">
                                    <div class="p-3 border rounded bg-white">
                                        <small class="text-muted d-block">Valor de Locação Mensal</small>
                                        <span class="h4 font-weight-bold text-success">R$ {{ number_format($imovel->valor_aluguel, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="row mb-3 small text-muted">
                            <div class="col-md-4">
                                <strong>Condomínio:</strong> R$ {{ number_format($imovel->valor_condominio ?? 0, 2, ',', '.') }}
                            </div>
                            <div class="col-md-4">
                                <strong>IPTU:</strong> R$ {{ number_format($imovel->valor_iptu ?? 0, 2, ',', '.') }}
                            </div>
                            <div class="col-md-4">
                                <strong>Mobília:</strong>
                                @if ($imovel->mobiliado === 'mobiliado')
                                    Mobiliado
                                @elseif ($imovel->mobiliado === 'semimobiliado')
                                    Semimobiliado
                                @else
                                    Não Mobiliado
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <span class="badge {{ $imovel->aceita_pets ? 'badge-success' : 'badge-secondary' }} p-2">
                                <i class="fas fa-paw mr-1"></i> {{ $imovel->aceita_pets ? 'Aceita Pets' : 'Não Aceita Pets' }}
                            </span>
                        </div>

                        <h5 class="font-weight-bold border-bottom pb-2 mt-4">Descrição do Imóvel</h5>
                        <p class="text-secondary" style="white-space: pre-line;">{{ $imovel->descricao ?: 'Entre em contato para mais detalhes sobre este imóvel.' }}</p>

                        @if ($imovel->tour_virtual_url || $imovel->video_url)
                            <div class="mt-3">
                                @if ($imovel->tour_virtual_url)
                                    <a href="{{ $imovel->tour_virtual_url }}" target="_blank" class="btn btn-outline-info btn-sm mr-2">
                                        <i class="fas fa-vr-cardboard mr-1"></i> Tour Virtual 360°
                                    </a>
                                @endif
                                @if ($imovel->video_url)
                                    <a href="{{ $imovel->video_url }}" target="_blank" class="btn btn-outline-danger btn-sm">
                                        <i class="fab fa-youtube mr-1"></i> Assistir Vídeo
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-paper-plane text-primary mr-1"></i> Demonstre Interesse neste Imóvel
                        </h3>
                    </div>
                    <form method="POST" action="{{ route('catalogo.interesse', $imovel) }}">
                        @csrf
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0 pl-3">
                                        @foreach ($errors->all() as $erro)
                                            <li>{{ $erro }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <p class="text-muted small">
                                Preencha os seus dados abaixo para solicitar atendimento ou enviar uma intenção de proposta ao corretor responsável.
                            </p>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Tipo de Pessoa *</label>
                                    <select name="tipo_pessoa" class="form-control" required>
                                        <option value="PF" {{ old('tipo_pessoa', 'PF') === 'PF' ? 'selected' : '' }}>Pessoa Física (PF)</option>
                                        <option value="PJ" {{ old('tipo_pessoa') === 'PJ' ? 'selected' : '' }}>Pessoa Jurídica (PJ)</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-8">
                                    <label>CPF / CNPJ *</label>
                                    <input type="text" name="documento" value="{{ old('documento') }}" class="form-control" placeholder="000.000.000-00" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Nome Completo / Razão Social *</label>
                                <input type="text" name="nome" value="{{ old('nome') }}" class="form-control" placeholder="Informe seu nome completo" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Telefone / WhatsApp *</label>
                                    <input type="text" name="telefone" value="{{ old('telefone') }}" class="form-control" placeholder="(00) 00000-0000" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>E-mail *</label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="seu@email.com" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Valor Proposto (Opcional — R$)</label>
                                <input type="number" step="0.01" min="0" name="valor_proposto" value="{{ old('valor_proposto') }}" class="form-control" placeholder="Deixe em branco caso deseje apenas contato ou visita">
                                <small class="form-text text-muted">
                                    Se informado, seu interesse será classificado como intenção de <strong>proposta</strong> para análise do corretor.
                                </small>
                            </div>

                            <div class="form-group">
                                <label>Mensagem *</label>
                                <textarea name="mensagem" rows="3" class="form-control" required placeholder="Olá, tenho interesse neste imóvel e gostaria de mais informações ou agendar uma visita...">{{ old('mensagem') }}</textarea>
                            </div>

                            <div class="form-group bg-light p-2 rounded border">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="consentimento_lgpd" name="consentimento_lgpd" value="1" {{ old('consentimento_lgpd') ? 'checked' : '' }} required>
                                    <label class="custom-control-label small text-dark" for="consentimento_lgpd">
                                        Li e concordo com o tratamento dos meus dados para que a imobiliária entre em contato comigo. *
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-block btn-lg">
                                <i class="fas fa-paper-plane mr-1"></i> Enviar Interesse
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white border-top py-4 mt-5">
        <div class="container text-center text-muted small">
            <strong>Softcom Imobiliária</strong> — Catálogo Público de Imóveis
        </div>
    </footer>
</body>
</html>
