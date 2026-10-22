@extends('adminlte::page')

@section('title', 'Ficha do Cliente - ' . $cliente->nome_razao_social)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user mr-2"></i>{{ $cliente->nome_razao_social }} <small class="badge badge-dark">{{ $cliente->tipo_pessoa }}</small></h1>
        <div>
            <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-info"><i class="fas fa-edit mr-1"></i> Editar</a>
            <form action="{{ route('clientes.anonimizar', $cliente) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja anonimizar os dados sensíveis deste cliente?');">
                @csrf
                <button type="submit" class="btn btn-danger"><i class="fas fa-user-secret mr-1"></i> Anonimizar Dados</button>
            </form>
        </div>
    </div>
@stop

@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">Dados e Papéis</h3></div>
                <div class="card-body">
                    <p><strong>CPF/CNPJ:</strong> <code>{{ $cliente->documento }}</code></p>
                    <p><strong>Telefone:</strong> {{ $cliente->telefone ?? '-' }}</p>
                    <p><strong>E-mail:</strong> {{ $cliente->email ?? '-' }}</p>
                    <p><strong>Corretor:</strong> {{ $cliente->corretor->name ?? '-' }}</p>
                    <hr>
                    <p><strong>Papéis Ativos:</strong><br>
                        @if ($cliente->is_proprietario) <span class="badge badge-primary">Proprietário</span> @endif
                        @if ($cliente->is_interessado) <span class="badge badge-info">Interessado/Lead</span> @endif
                        @if ($cliente->is_locatario_comprador) <span class="badge badge-success">Locatário/Comprador</span> @endif
                    </p>
                    <p><strong>Apto a ser Proprietário:</strong>
                        @if ($cliente->podeSerProprietario())
                            <span class="badge badge-success">Sim (Doc. Validada)</span>
                        @else
                            <span class="badge badge-warning">Não (Requer Papel Proprietário + Doc. Validada)</span>
                        @endif
                    </p>
                    @if ($cliente->pref_bairro || $cliente->pref_tipo_imovel || $cliente->pref_preco_max)
                        <hr>
                        <h6><strong>Preferências de Busca:</strong></h6>
                        <small>
                            <strong>Tipo:</strong> {{ ucfirst($cliente->pref_tipo_imovel ?? 'Qualquer') }} ({{ $cliente->pref_finalidade ?? 'Ambos' }})<br>
                            <strong>Bairro:</strong> {{ $cliente->pref_bairro ?? 'Qualquer' }}<br>
                            <strong>Faixa:</strong> R$ {{ number_format($cliente->pref_preco_min ?? 0, 2, ',', '.') }} até R$ {{ number_format($cliente->pref_preco_max ?? 0, 2, ',', '.') }}
                        </small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-comments mr-1"></i>Registrar Nova Interação</h3></div>
                <form method="POST" action="{{ route('clientes.interacoes.store', $cliente) }}">
                    @csrf
                    <div class="card-body row">
                        <div class="form-group col-md-4 mb-2">
                            <select name="tipo" class="form-control" required>
                                <option value="whatsapp">Contato via WhatsApp</option>
                                <option value="telefone">Ligação Telefônica</option>
                                <option value="email">E-mail Enviado</option>
                                <option value="visita">Feedback de Visita</option>
                                <option value="proposta">Negociação / Proposta</option>
                                <option value="observacao">Observação Interna</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6 mb-2">
                            <input type="text" name="descricao" class="form-control" placeholder="Descreva o que foi conversado com o cliente..." required>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="submit" class="btn btn-info btn-block">Registrar</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="timeline">
                @foreach ($cliente->interacoes as $interacao)
                    <div>
                        <i class="fas fa-comment bg-primary"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> {{ $interacao->created_at->format('d/m/Y H:i') }}</span>
                            <h3 class="timeline-header">
                                <strong>{{ strtoupper($interacao->tipo) }}</strong> — registrado por {{ $interacao->autor->name ?? 'Sistema' }}
                            </h3>
                            <div class="timeline-body">
                                {{ $interacao->descricao }}
                            </div>
                        </div>
                    </div>
                @endforeach
                <div><i class="fas fa-clock bg-gray"></i></div>
            </div>
        </div>
    </div>
@stop
