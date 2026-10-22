@extends('adminlte::page')

@section('title', 'Visita #' . $visita->id)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-clipboard-check mr-2"></i>Detalhes e Check-in da Visita #{{ $visita->id }}</h1>
        <a href="{{ route('visitas.index') }}" class="btn btn-default">Voltar para Agenda</a>
    </div>
@stop

@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row">
        <div class="col-md-5">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">Dados do Agendamento</h3></div>
                <div class="card-body">
                    <p><strong>Horário:</strong> {{ $visita->data_hora_inicio->format('d/m/Y H:i') }} às {{ $visita->data_hora_fim->format('H:i') }}</p>
                    <p><strong>Imóvel:</strong> <a href="{{ route('imoveis.show', $visita->imovel) }}">{{ $visita->imovel->codigo_referencia }} — {{ $visita->imovel->titulo }}</a></p>
                    <p><strong>Endereço:</strong> {{ $visita->imovel->logradouro }}, {{ $visita->imovel->numero }} — {{ $visita->imovel->bairro }}</p>
                    <p><strong>Cliente Interessado:</strong> <a href="{{ route('clientes.show', $visita->cliente) }}">{{ $visita->cliente->nome_razao_social }}</a> ({{ $visita->cliente->telefone }})</p>
                    <p><strong>Corretor:</strong> {{ $visita->corretor->name }}</p>
                    <p><strong>Notificou Proprietário?</strong> {{ $visita->notificar_proprietario ? 'Sim' : 'Não' }}</p>
                    <p><strong>Observações:</strong> {{ $visita->observacoes_agendamento ?? '-' }}</p>
                    @if ($visita->checkin_em)
                        <p class="text-success"><strong><i class="fas fa-check-circle"></i> Check-in realizado em:</strong> {{ $visita->checkin_em->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card card-outline card-success">
                <div class="card-header"><h3 class="card-title">Status, Check-in e Feedback Pós-Visita</h3></div>
                <form method="POST" action="{{ route('visitas.feedback', $visita) }}">
                    @csrf
                    @method('PATCH')
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Estado da Visita *</label>
                                <select name="status" class="form-control" required>
                                    @foreach ([
                                        'agendada' => 'Agendada',
                                        'confirmada' => 'Confirmada pelo Cliente',
                                        'realizada' => 'Realizada (Fazer Check-in)',
                                        'cancelada' => 'Cancelada',
                                        'nao_compareceu' => 'Cliente Não Compareceu',
                                    ] as $k => $v)
                                        <option value="{{ $k }}" {{ $visita->status === $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Nível de Interesse do Cliente</label>
                                <select name="nivel_interesse" class="form-control">
                                    <option value="">-- Avaliar após visita --</option>
                                    <option value="alto" {{ $visita->nivel_interesse === 'alto' ? 'selected' : '' }}>Alto (Gostou muito)</option>
                                    <option value="medio" {{ $visita->nivel_interesse === 'medio' ? 'selected' : '' }}>Médio (Em dúvida)</option>
                                    <option value="baixo" {{ $visita->nivel_interesse === 'baixo' ? 'selected' : '' }}>Baixo (Descartou)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Feedback Pós-Visita / Objeções Levantadas pelo Cliente</label>
                            <textarea name="feedback_objecoes" class="form-control" rows="4" placeholder="Relate o que o cliente achou do imóvel, pontos positivos, objeções de preço/acabamento...">{{ old('feedback_objecoes', $visita->feedback_objecoes) }}</textarea>
                        </div>

                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="intencao_proposta" name="intencao_proposta" value="1" {{ old('intencao_proposta', $visita->intencao_proposta) ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold text-success" for="intencao_proposta">
                                Cliente demonstrou intenção de enviar Proposta!
                            </label>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Salvar Status e Feedback</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
