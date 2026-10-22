@extends('adminlte::page')

@section('title', 'Contrato ' . $contrato->codigo_contrato)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-contract mr-2"></i>Contrato <code>{{ $contrato->codigo_contrato }}</code></h1>
        <a href="{{ route('contratos.index') }}" class="btn btn-default">Voltar</a>
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
                    <p>
                        <strong>1. Assinatura do Cliente ({{ $contrato->cliente->nome_razao_social }}):</strong><br>
                        @if ($contrato->assinado_cliente_em)
                            <span class="badge badge-success"><i class="fas fa-check"></i> Assinado em {{ $contrato->assinado_cliente_em->format('d/m/Y H:i') }}</span>
                        @else
                            <form method="POST" action="{{ route('contratos.assinar', $contrato) }}" class="mt-1">
                                @csrf
                                <input type="hidden" name="parte" value="cliente">
                                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-pen-nib mr-1"></i> Coletar Assinatura do Cliente</button>
                            </form>
                        @endif
                    </p>

                    <p>
                        <strong>2. Assinatura do Proprietário:</strong><br>
                        @if ($contrato->assinado_proprietario_em)
                            <span class="badge badge-success"><i class="fas fa-check"></i> Assinado em {{ $contrato->assinado_proprietario_em->format('d/m/Y H:i') }}</span>
                        @else
                            <form method="POST" action="{{ route('contratos.assinar', $contrato) }}" class="mt-1">
                                @csrf
                                <input type="hidden" name="parte" value="proprietario">
                                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-pen-nib mr-1"></i> Coletar Assinatura do Proprietário</button>
                            </form>
                        @endif
                    </p>
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
