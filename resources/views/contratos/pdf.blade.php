<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Contrato {{ $contrato->codigo_contrato }} - PDF</title>
    <style>
        body {
            background: #525659;
            font-family: "Times New Roman", Times, serif;
            margin: 0;
            padding: 30px 0;
            color: #111;
        }
        .barra-acoes {
            max-width: 210mm;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            font-family: Arial, sans-serif;
        }
        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-imprimir { background: #28a745; color: #fff; }
        .btn-voltar { background: #f8f9fa; color: #333; }
        .folha-a4 {
            width: 210mm;
            min-height: 297mm;
            padding: 22mm 20mm;
            margin: 0 auto;
            background: #fff;
            box-sizing: border-box;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
        }
        .cabecalho {
            border-bottom: 2px solid #222;
            padding-bottom: 12px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cabecalho h1 { margin: 0; font-size: 22px; text-transform: uppercase; }
        .texto-contrato {
            white-space: pre-wrap;
            line-height: 1.65;
            font-size: 14px;
            text-align: justify;
        }
        .bloco-assinaturas {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            gap: 30px;
            page-break-inside: avoid;
        }
        .caixa-assinatura {
            flex: 1;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 10px;
            margin-top: 60px;
        }
        .img-assinatura {
            height: 75px;
            display: block;
            margin: -65px auto 5px auto;
        }
        .rodape-hash {
            margin-top: 40px;
            padding: 10px;
            border: 1px dashed #666;
            font-family: monospace;
            font-size: 11px;
            background: #f9f9f9;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .barra-acoes { display: none !important; }
            .folha-a4 { box-shadow: none; width: 100%; padding: 10mm; }
        }
    </style>
</head>
<body>
    <div class="barra-acoes">
        <a href="{{ route('contratos.show', $contrato) }}" class="btn btn-voltar">&larr; Voltar ao Sistema</a>
        <button onclick="window.print()" class="btn btn-imprimir">&#128424; Imprimir / Salvar como PDF</button>
    </div>

    <div class="folha-a4">
        <div class="cabecalho">
            <div>
                <h1>Softcom Gestão Imobiliária</h1>
                <small>Instrumento Contratual Nº <strong>{{ $contrato->codigo_contrato }}</strong></small>
            </div>
            <div style="text-align: right; font-size: 12px;">
                <div><strong>Imóvel:</strong> {{ $contrato->imovel->codigo_referencia }}</div>
                <div><strong>Vigência:</strong> {{ $contrato->vigencia_inicio->format('d/m/Y') }}</div>
                <div><strong>Reajuste:</strong> {{ $contrato->indice_reajuste }}</div>
            </div>
        </div>

        <div class="texto-contrato">{{ $contrato->termos_contrato }}</div>

        <div class="bloco-assinaturas">
            <div class="caixa-assinatura">
                @php
                    $assinaturaCliente = $contrato->assinatura_cliente ?? ($contrato->proposta->assinatura_cliente ?? null);
                @endphp
                @if ($assinaturaCliente)
                    <img src="{{ $assinaturaCliente }}" class="img-assinatura" alt="Assinatura Cliente">
                @endif
                <strong>{{ $contrato->cliente->nome_razao_social }}</strong><br>
                <small>Contratante / Cliente ({{ $contrato->cliente->documento }})</small><br>
                @if ($contrato->assinado_cliente_em)
                    <small style="color: green;">Assinado digitalmente em {{ $contrato->assinado_cliente_em->format('d/m/Y H:i') }}</small>
                @endif
            </div>

            <div class="caixa-assinatura">
                @if ($contrato->assinatura_proprietario)
                    <img src="{{ $contrato->assinatura_proprietario }}" class="img-assinatura" alt="Assinatura Proprietário">
                @endif
                <strong>{{ $contrato->imovel->proprietarios->pluck('nome_razao_social')->join(', ') }}</strong><br>
                <small>Proprietário(s) / Outorgante(s)</small><br>
                @if ($contrato->assinado_proprietario_em)
                    <small style="color: green;">Assinado digitalmente em {{ $contrato->assinado_proprietario_em->format('d/m/Y H:i') }}</small>
                @endif
            </div>
        </div>

        <div class="rodape-hash">
            <strong>AUTENTICIDADE ELETRÔNICA ({{ $contrato->plataforma_assinatura }}):</strong><br>
            Hash SHA-256: {{ $contrato->hash_documento }}<br>
            Status do Instrumento: {{ strtoupper(str_replace('_', ' ', $contrato->status)) }}
        </div>
    </div>
</body>
</html>
