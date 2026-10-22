@extends('adminlte::page')

@section('title', 'Nova Proposta')

@section('content_header')
    <h1><i class="fas fa-file-invoice-dollar mr-2"></i>Emitir Proposta Formal</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <form method="POST" action="{{ route('propostas.store') }}">
            @csrf
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <div class="row">
                    <div class="form-group col-md-4">
                        <label>Imóvel *</label>
                        <select name="imovel_id" class="form-control" required>
                            <option value="">-- Selecione --</option>
                            @foreach ($imoveis as $imovel)
                                <option value="{{ $imovel->id }}">
                                    [{{ $imovel->codigo_referencia }}] {{ $imovel->titulo }} (Venda: R$ {{ number_format($imovel->valor_venda ?? 0, 2, ',', '.') }} | Aluguel: R$ {{ number_format($imovel->valor_aluguel ?? 0, 2, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Cliente Proponente *</label>
                        <select name="cliente_id" class="form-control" required>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->nome_razao_social }} ({{ $cliente->documento }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Corretor Responsável *</label>
                        <select name="corretor_id" class="form-control" required>
                            @foreach ($corretores as $corretor)
                                <option value="{{ $corretor->id }}" {{ auth()->id() === $corretor->id ? 'selected' : '' }}>{{ $corretor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-3">
                        <label>Valor Oferecido (R$) *</label>
                        <input type="number" step="0.01" name="valor_oferecido" class="form-control" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Forma de Pagamento *</label>
                        <select name="forma_pagamento" class="form-control" required>
                            <option value="a_vista">À Vista</option>
                            <option value="financiamento">Financiamento Bancário</option>
                            <option value="parcelamento_direto">Parcelamento Direto</option>
                            <option value="aluguel_mensal">Aluguel Mensal (Locação)</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Garantia Locatícia *</label>
                        <select name="garantia_locaticia" class="form-control" required>
                            <option value="nenhuma">Nenhuma (Compra e Venda)</option>
                            <option value="caucao">Caução (Até 3 aluguéis)</option>
                            <option value="fiador">Fiador Solidário</option>
                            <option value="seguro_fianca">Seguro Fiança</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Prazo de Validade *</label>
                        <input type="datetime-local" name="valida_ate" class="form-control" value="{{ $validaAte->format('Y-m-d\TH:i') }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Condições da Proposta / Detalhes de Entrada ou Permuta</label>
                    <textarea name="condicoes_observacoes" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('propostas.index') }}" class="btn btn-default">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane mr-1"></i> Enviar Proposta</button>
            </div>
        </form>
    </div>
@stop
