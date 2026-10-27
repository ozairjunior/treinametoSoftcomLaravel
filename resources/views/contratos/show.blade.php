@extends('adminlte::page')

@section('title', 'Contrato ' . $contrato->codigo_contrato)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-contract mr-2"></i>Contrato <code>{{ $contrato->codigo_contrato }}</code></h1>
        <div>
            <a href="{{ route('contratos.pdf', $contrato) }}" target="_blank" class="btn btn-danger mr-1">
                <i class="fas fa-file-pdf mr-1"></i> Visualizar PDF / Imprimir
            </a>
            <a href="{{ route('contratos.index') }}" class="btn btn-default">Voltar</a>
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
        <div class="col-md-4">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">Assinatura Eletrônica</h3></div>
                <div class="card-body">
                    <p><strong>Plataforma:</strong> {{ $contrato->plataforma_assinatura }}</p>
                    <p><strong>Hash SHA-256:</strong><br><code class="small">{{ $contrato->hash_documento }}</code></p>
                    <hr>

                    <div class="mb-3">
                        <strong>1. Assinatura do Cliente ({{ $contrato->cliente->nome_razao_social }}):</strong><br>
                        @if ($contrato->assinado_cliente_em)
                            <span class="badge badge-success mb-1"><i class="fas fa-check"></i> Assinado em {{ $contrato->assinado_cliente_em->format('d/m/Y H:i') }}</span>
                            @if ($contrato->assinatura_cliente)
                                <div class="border rounded bg-white p-1 text-center">
                                    <img src="{{ $contrato->assinatura_cliente }}" alt="Assinatura Cliente" style="max-width: 100%; height: 70px; object-fit: contain;">
                                </div>
                            @endif
                        @else
                            <span class="badge badge-warning">Aguardando Assinatura</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <strong>2. Assinatura do Proprietário:</strong><br>
                        @if ($contrato->assinado_proprietario_em)
                            <span class="badge badge-success mb-1"><i class="fas fa-check"></i> Assinado em {{ $contrato->assinado_proprietario_em->format('d/m/Y H:i') }}</span>
                            @if ($contrato->assinatura_proprietario)
                                <div class="border rounded bg-white p-1 text-center">
                                    <img src="{{ $contrato->assinatura_proprietario }}" alt="Assinatura Proprietário" style="max-width: 100%; height: 70px; object-fit: contain;">
                                </div>
                            @endif
                        @else
                            <span class="badge badge-warning">Aguardando Assinatura</span>
                        @endif
                    </div>

                    @if (!$contrato->assinado_cliente_em || !$contrato->assinado_proprietario_em)
                        <hr>
                        <h6 class="font-weight-bold text-primary"><i class="fas fa-signature mr-1"></i>Desenhar Assinatura com o Mouse:</h6>
                        <form method="POST" action="{{ route('contratos.assinar', $contrato) }}" id="form-assinar-contrato">
                            @csrf
                            <input type="hidden" name="assinatura_base64" id="input-assinatura-contrato">

                            <div class="form-group mb-2">
                                <label class="small mb-1">Quem está assinando agora?</label>
                                <select name="parte" class="form-control form-control-sm">
                                    @if (!$contrato->assinado_cliente_em)
                                        <option value="cliente">1. Cliente ({{ $contrato->cliente->nome_razao_social }})</option>
                                    @endif
                                    @if (!$contrato->assinado_proprietario_em)
                                        <option value="proprietario">2. Proprietário do Imóvel</option>
                                    @endif
                                </select>
                            </div>

                            <div class="border rounded bg-light text-center mb-2">
                                <canvas id="canvas-contrato" width="320" height="140" style="cursor: crosshair; touch-action: none; background: #fff; max-width: 100%;"></canvas>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limparCanvasContrato()">
                                    <i class="fas fa-eraser mr-1"></i> Limpar
                                </button>
                                <button type="submit" class="btn btn-sm btn-primary" onclick="return enviarAssinaturaContrato()">
                                    <i class="fas fa-pen-nib mr-1"></i> Coletar Assinatura
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-outline {{ $contrato->possuiAssinatura() ? 'card-danger' : 'card-info' }}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        Instrumento Contratual
                        @if ($contrato->possuiAssinatura())
                            <span class="badge badge-danger ml-2"><i class="fas fa-lock"></i> CONTRATO IMUTÁVEL</span>
                        @endif
                    </h3>
                </div>
                <form method="POST" action="{{ route('contratos.update.termos', $contrato) }}">
                    @csrf
                    @method('PATCH')
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Índice de Reajuste</label>
                                <select name="indice_reajuste" class="form-control" {{ $contrato->possuiAssinatura() ? 'disabled' : '' }}>
                                    @foreach (['IPCA', 'IGP-M', 'INPC', 'NENHUM'] as $ind)
                                        <option value="{{ $ind }}" {{ $contrato->indice_reajuste === $ind ? 'selected' : '' }}>{{ $ind }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Dia de Vencimento</label>
                                <input type="number" name="dia_vencimento" class="form-control" value="{{ $contrato->dia_vencimento }}" {{ $contrato->possuiAssinatura() ? 'readonly' : '' }}>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Cláusulas e Termos do Contrato</label>
                            <textarea name="termos_contrato" class="form-control" rows="13" {{ $contrato->possuiAssinatura() ? 'readonly' : '' }}>{{ $contrato->termos_contrato }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-info" {{ $contrato->possuiAssinatura() ? 'disabled' : '' }}>
                            <i class="fas fa-save mr-1"></i> Salvar Alterações na Minuta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    var canvasContrato = document.getElementById('canvas-contrato');
    if (canvasContrato) {
        var ctxContrato = canvasContrato.getContext('2d');
        var desenhandoContrato = false;
        var temTracoContrato = false;

        ctxContrato.strokeStyle = '#0b2e59';
        ctxContrato.lineWidth = 2.2;
        ctxContrato.lineCap = 'round';
        ctxContrato.lineJoin = 'round';

        function posContrato(e) {
            var rect = canvasContrato.getBoundingClientRect();
            var cx = e.touches ? e.touches[0].clientX : e.clientX;
            var cy = e.touches ? e.touches[0].clientY : e.clientY;
            return { x: cx - rect.left, y: cy - rect.top };
        }

        function iniciarContrato(e) {
            e.preventDefault();
            desenhandoContrato = true;
            var p = posContrato(e);
            ctxContrato.beginPath();
            ctxContrato.moveTo(p.x, p.y);
        }

        function riscarContrato(e) {
            if (!desenhandoContrato) return;
            e.preventDefault();
            temTracoContrato = true;
            var p = posContrato(e);
            ctxContrato.lineTo(p.x, p.y);
            ctxContrato.stroke();
        }

        function pararContrato() {
            desenhandoContrato = false;
        }

        canvasContrato.addEventListener('mousedown', iniciarContrato);
        canvasContrato.addEventListener('mousemove', riscarContrato);
        window.addEventListener('mouseup', pararContrato);

        canvasContrato.addEventListener('touchstart', iniciarContrato, { passive: false });
        canvasContrato.addEventListener('touchmove', riscarContrato, { passive: false });
        window.addEventListener('touchend', pararContrato);

        window.limparCanvasContrato = function () {
            ctxContrato.clearRect(0, 0, canvasContrato.width, canvasContrato.height);
            temTracoContrato = false;
        };

        window.enviarAssinaturaContrato = function () {
            if (!temTracoContrato) {
                alert('Desenhe a assinatura com o mouse no quadro branco antes de clicar em Coletar Assinatura!');
                return false;
            }
            document.getElementById('input-assinatura-contrato').value = canvasContrato.toDataURL('image/png');
            return true;
        };
    }
</script>
@stop
