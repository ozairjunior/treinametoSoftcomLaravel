@extends('adminlte::page')

@section('title', 'Dashboard Imobiliário')

@section('content_header')
    <h1><i class="fas fa-chart-line mr-2"></i>Painel de Gestão Imobiliária</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ \App\Models\Imovel::where('status', 'disponivel')->count() }}</h3>
                    <p>Imóveis Disponíveis</p>
                </div>
                <div class="icon"><i class="fas fa-building"></i></div>
                <a href="{{ route('imoveis.index') }}" class="small-box-footer">Ver Imóveis <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ \App\Models\Cliente::whereNull('anonimizado_em')->count() }}</h3>
                    <p>Clientes no CRM</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('clientes.index') }}" class="small-box-footer">Abrir CRM <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ \App\Models\Visita::whereIn('status', ['agendada', 'confirmada'])->count() }}</h3>
                    <p>Visitas Pendentes</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-check"></i></div>
                <a href="{{ route('visitas.index') }}" class="small-box-footer">Ver Agenda <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ \App\Models\Contrato::where('status', 'vigente_formalizado')->count() }}</h3>
                    <p>Contratos Formalizados</p>
                </div>
                <div class="icon"><i class="fas fa-file-contract"></i></div>
                <a href="{{ route('contratos.index') }}" class="small-box-footer">Ver Contratos <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
@stop