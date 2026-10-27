@extends('adminlte::page')

@section('title', 'Proposta #' . $proposta->id)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Proposta #{{ $proposta->id }} — Imóvel <code>{{ $proposta->imovel->codigo_referencia }}</code></h1>
        <div>
            @if ($proposta->status === 'aceita' && !$proposta->contrato)
                <a href="{{ route('contratos.create.proposta', $proposta) }}" class="btn btn-success">
                    <i class="fas fa-file-contract mr-1"></i> Gerar Contrato
                </a>
            @elseif ($proposta->contrato)
                <a href="{{ route('contratos.show', $proposta->contrato) }}" class="btn btn-info">
                    <i class="fas fa-file-signature mr-1"></i> Ver Contrato {{ $proposta->contrato->codigo_contrato }}
                </a>
            @endif
            <a href="{{ route('propostas.index') }}" class="btn btn-default">Voltar</a>
        </div>
    </div>
@stop

@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row">
        <div class="col-md-5">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">Resumo da Negociação</h3></div>
                <div class="card-body">
                    <p><strong>Status Atual:</strong> <span class="badge badge-dark text-uppercase">{{ str_replace('_', ' ', $proposta->status) }}</span></p>
                    <p><strong>Valor na {{ $proposta->rodada_atual }}ª Rodada:</strong> <span class="text-success font-weight-bold">R$ {{ number_format($proposta->valor_oferecido, 2, ',', '.') }}</span></p>
                    <p><strong>Pagamento / Garantia:</strong> {{ str_replace('_', ' ', $proposta->forma_pagamento) }} | Garantia: {{ str_replace('_', ' ', $proposta->garantia_locaticia) }}</p>
                    <p><strong>Válida até:</strong> {{ $proposta->valida_ate->format('d/m/Y H:i') }}</p>
                    <p><strong>Cliente:</strong> {{ $proposta->cliente->nome_razao_social }}</p>
                    <p><strong>Status Atual do Imóvel:</strong> <span class="badge badge-warning text-uppercase">{{ $proposta->imovel->status }}</span></p>

                    @if ($proposta->status === 'em_analise')
                        <hr>
                        <h6><strong>Decisão do Proprietário:</strong></h6>
                        <form method="POST" action="{{ route('propostas.decidir', $proposta) }}" class="d-flex">
                            @csrf
                            @method('PATCH')
                            <button type="submit" name="decisao" value="aceita" class="btn btn-success mr-2">
                                <i class="fas fa-check mr-1"></i> Aceitar e Reservar Imóvel
                            </button>
                            <button type="submit" name="decisao" value="recusada" class="btn btn-danger">
                                <i class="fas fa-times mr-1"></i> Recusar
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card card-outline card-dark">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-signature mr-1"></i>Assinatura Digital na Proposta</h3>
                </div>
                <div class="card-body text-center">
                    @if ($proposta->assinatura_cliente)
                        <p class="text-success mb-2">
                            <i class="fas fa-check-circle"></i> Assinada em {{ $proposta->assinado_em ? $proposta->assinado_em->format('d/m/Y H:i') : '' }}
                        </p>
                        <div class="border rounded bg-white p-2 mb-2">
                            <img src="{{ $proposta->assinatura_cliente }}" alt="Assinatura Digital" style="max-width: 100%; height: 120px; object-fit: contain;">
                        </div>
                        <small class="text-muted d-block">Deseja substituir a assinatura? Desenhe novamente abaixo:</small>
                    @else
                        <p class="text-muted small mb-2">Assine no quadro abaixo segurando o botão esquerdo do mouse:</p>
                    @endif

                    <form method="POST" action="{{ route('propostas.assinar', $proposta) }}" id="form-assinatura">
                        @csrf
                        <input type="hidden" name="assinatura_cliente" id="input-assinatura">
                        <div class="border rounded bg-light mb-2" style="display: inline-block;">
                            <canvas id="canvas-assinatura" width="360" height="150" style="cursor: crosshair; touch-action: none; background: #fff;"></canvas>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limparAssinatura()">
                                <i class="fas fa-eraser mr-1"></i> Limpar Quadro
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary" onclick="return prepararEnvioAssinatura()">
                                <i class="fas fa-pen-nib mr-1"></i> Salvar Assinatura Digital
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            @if ($proposta->status === 'em_analise')
                <div class="card card-outline card-warning">
                    <div class="card-header"><h3 class="card-title">Registrar Contraproposta / Nova Rodada</h3></div>
                    <form method="POST" action="{{ route('propostas.contraproposta', $proposta) }}">
                        @csrf
                        <div class="card-body row">
                            <div class="form-group col-md-4">
                                <label>Origem</label>
                                <select name="autor_tipo" class="form-control">
                                    <option value="proprietario">Contraproposta do Proprietário</option>
                                    <option value="cliente">Nova Oferta do Cliente</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Novo Valor Proposto (R$)</label>
                                <input type="number" step="0.01" name="valor_proposto" class="form-control" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Justificativa / Condição</label>
                                <input type="text" name="justificativa" class="form-control" required>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-warning"><i class="fas fa-sync-alt mr-1"></i> Registrar Contraproposta</button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="card">
                <div class="card-header"><h3 class="card-title">Histórico de Rodadas de Negociação</h3></div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Rodada</th>
                                <th>Autor</th>
                                <th>Valor</th>
                                <th>Observação</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($proposta->rodadas as $r)
                                <tr>
                                    <td><strong>#{{ $r->numero_rodada }}</strong></td>
                                    <td><span class="badge badge-info text-uppercase">{{ $r->autor_tipo }}</span></td>
                                    <td>R$ {{ number_format($r->valor_proposto, 2, ',', '.') }}</td>
                                    <td>{{ $r->justificativa }}</td>
                                    <td>{{ $r->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    var canvas = document.getElementById('canvas-assinatura');
    var ctx = canvas.getContext('2d');
    var desenhando = false;
    var temTraco = false;

    ctx.strokeStyle = '#0b2e59';
    ctx.lineWidth = 2.2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    function posicaoMouse(e) {
        var rect = canvas.getBoundingClientRect();
        var clientX = e.touches ? e.touches[0].clientX : e.clientX;
        var clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    }

    function iniciarDesenho(e) {
        e.preventDefault();
        desenhando = true;
        var pos = posicaoMouse(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }

    function desenhar(e) {
        if (!desenhando) return;
        e.preventDefault();
        temTraco = true;
        var pos = posicaoMouse(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    }

    function pararDesenho() {
        desenhando = false;
    }

    canvas.addEventListener('mousedown', iniciarDesenho);
    canvas.addEventListener('mousemove', desenhar);
    window.addEventListener('mouseup', pararDesenho);

    canvas.addEventListener('touchstart', iniciarDesenho, { passive: false });
    canvas.addEventListener('touchmove', desenhar, { passive: false });
    window.addEventListener('touchend', pararDesenho);

    function limparAssinatura() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        temTraco = false;
    }

    function prepararEnvioAssinatura() {
        if (!temTraco) {
            alert('Por favor, desenhe sua assinatura com o mouse no quadro branco antes de salvar!');
            return false;
        }
        document.getElementById('input-assinatura').value = canvas.toDataURL('image/png');
        return true;
    }
</script>
@stop