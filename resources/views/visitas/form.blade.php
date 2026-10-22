@extends('adminlte::page')

@section('title', 'Agendar Visita')

@section('content_header')
    <h1><i class="fas fa-calendar-plus mr-2"></i>Solicitação de Agendamento de Visita</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <form method="POST" action="{{ route('visitas.store') }}">
            @csrf
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <h5><i class="icon fas fa-ban"></i> Conflito ou Erro de Validação!</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $erro)
                                <li>{{ $erro }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Atenção:</strong> O sistema exige um intervalo mínimo de <strong>30 minutos de deslocamento</strong> entre visitas do mesmo corretor e bloqueia agendamentos simultâneos para o mesmo imóvel.
                </div>

                <div class="row">
                    <div class="form-group col-md-4">
                        <label>Imóvel Disponível *</label>
                        <select name="imovel_id" class="form-control" required>
                            <option value="">-- Selecione o Imóvel --</option>
                            @foreach ($imoveis as $imovel)
                                <option value="{{ $imovel->id }}" {{ (int) old('imovel_id', $visita->imovel_id) === $imovel->id ? 'selected' : '' }}>
                                    [{{ $imovel->codigo_referencia }}] {{ $imovel->titulo }} ({{ $imovel->bairro }}) {{ $imovel->imovel_habitado ? '⚠️ Habitado' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Cliente Interessado *</label>
                        <select name="cliente_id" class="form-control" required>
                            <option value="">-- Selecione o Cliente --</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ (int) old('cliente_id', $visita->cliente_id) === $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nome_razao_social }} ({{ $cliente->telefone ?? $cliente->documento }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Corretor Responsável *</label>
                        <select name="corretor_id" class="form-control" required>
                            @foreach ($corretores as $corretor)
                                <option value="{{ $corretor->id }}" {{ (int) old('corretor_id', $visita->corretor_id) === $corretor->id ? 'selected' : '' }}>
                                    {{ $corretor->name }} ({{ $corretor->creci ?? $corretor->role }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-4">
                        <label>Data e Hora de Início *</label>
                        <input type="datetime-local" name="data_hora_inicio" class="form-control"
                               value="{{ old('data_hora_inicio', $visita->data_hora_inicio->format('Y-m-d\TH:i')) }}" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Data e Hora de Término *</label>
                        <input type="datetime-local" name="data_hora_fim" class="form-control"
                               value="{{ old('data_hora_fim', $visita->data_hora_fim->format('Y-m-d\TH:i')) }}" required>
                    </div>
                    <div class="form-group col-md-4 pt-4">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="notificar_proprietario" name="notificar_proprietario" value="1" {{ old('notificar_proprietario') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="notificar_proprietario">Notificar Proprietário (Imóvel Habitado)</label>
                        </div>
                        <div class="custom-control custom-switch mt-2">
                            <input type="checkbox" class="custom-control-input" id="permitir_simultaneo" name="permitir_simultaneo" value="1" {{ old('permitir_simultaneo') ? 'checked' : '' }}>
                            <label class="custom-control-label text-muted" for="permitir_simultaneo">Permitir visita simultânea no imóvel</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Observações para a Visita / Ponto de Encontro</label>
                    <textarea name="observacoes_agendamento" class="form-control" rows="2">{{ old('observacoes_agendamento') }}</textarea>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('visitas.index') }}" class="btn btn-default">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-check mr-1"></i> Confirmar Agendamento</button>
            </div>
        </form>
    </div>
@stop
