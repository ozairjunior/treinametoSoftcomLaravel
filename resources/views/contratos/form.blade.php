@extends('adminlte::page')

@section('title', 'Gerar Contrato')

@section('content_header')
    <h1><i class="fas fa-file-signature mr-2"></i>Gerar Contrato a partir da Proposta #{{ $proposta->id }}</h1>
@stop

@section('content')
    <div class="card card-outline card-success">
        <form method="POST" action="{{ route('contratos.store') }}">
            @csrf
            <input type="hidden" name="proposta_id" value="{{ $proposta->id }}">

            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-4">
                        <label>Tipo de Contrato *</label>
                        <select name="tipo_contrato" class="form-control" required>
                            <option value="compra_venda" {{ $tipoSugerido === 'compra_venda' ? 'selected' : '' }}>Promessa de Compra e Venda</option>
                            <option value="locacao" {{ $tipoSugerido === 'locacao' ? 'selected' : '' }}>Locação Residencial / Comercial</option>
                            <option value="intermediacao">Contrato de Intermediação</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Valor Final Negociado (R$) *</label>
                        <input type="number" step="0.01" name="valor_final" class="form-control" value="{{ $proposta->valor_oferecido }}" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Índice de Reajuste Anual *</label>
                        <select name="indice_reajuste" class="form-control" required>
                            <option value="IPCA">IPCA</option>
                            <option value="IGP-M">IGP-M</option>
                            <option value="INPC">INPC</option>
                            <option value="NENHUM">Sem Reajuste</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-3">
                        <label>Início da Vigência *</label>
                        <input type="date" name="vigencia_inicio" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Fim da Vigência</label>
                        <input type="date" name="vigencia_fim" class="form-control" value="{{ now()->addMonths(30)->format('Y-m-d') }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Dia Vencimento</label>
                        <input type="number" name="dia_vencimento" class="form-control" value="10">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Garantia</label>
                        <input type="text" name="garantia_locaticia" class="form-control" value="{{ $proposta->garantia_locaticia }}" readonly>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Nº Apólice / Caução</label>
                        <input type="text" name="apolice_seguro_fianca" class="form-control" placeholder="Opcional">
                    </div>
                </div>

                <div class="form-group">
                    <label>Minuta do Contrato *</label>
                    <textarea name="termos_contrato" class="form-control" rows="12" required>{{ $textoInicial }}</textarea>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('propostas.show', $proposta) }}" class="btn btn-default">Voltar</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-check mr-1"></i> Emitir Contrato para Assinatura</button>
            </div>
        </form>
    </div>
@stop
