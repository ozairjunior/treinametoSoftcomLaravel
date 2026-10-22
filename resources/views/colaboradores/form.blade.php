@extends('adminlte::page')

@section('title', $colaborador->exists ? 'Editar Colaborador' : 'Novo Colaborador')

@section('content_header')
    <h1>{{ $colaborador->exists ? 'Editar Colaborador: ' . $colaborador->name : 'Novo Colaborador' }}</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <form method="POST" enctype="multipart/form-data"
              action="{{ $colaborador->exists ? route('colaboradores.update', $colaborador) : route('colaboradores.store') }}">
            @csrf
            @if ($colaborador->exists)
                @method('PUT')
            @endif

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $erro)
                                <li>{{ $erro }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Nome Completo *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $colaborador->name) }}" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>E-mail *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $colaborador->email) }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-4">
                        <label>Perfil de Acesso *</label>
                        <select name="role" class="form-control" required>
                            @foreach (['corretor' => 'Corretor', 'financeiro' => 'Financeiro / Jurídico', 'admin' => 'Administrador', 'cliente' => 'Cliente (Portal)'] as $val => $label)
                                <option value="{{ $val }}" {{ old('role', $colaborador->role ?? 'corretor') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Status da Conta *</label>
                        <select name="status" class="form-control" required>
                            @foreach (['ativo' => 'Ativo', 'suspenso' => 'Suspenso', 'inativo' => 'Inativo'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $colaborador->status ?? 'ativo') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Comissão Padrão (%)</label>
                        <input type="number" step="0.01" name="comissao_padrao" class="form-control" value="{{ old('comissao_padrao', $colaborador->comissao_padrao ?? '5.00') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-4">
                        <label>CPF</label>
                        <input type="text" name="cpf" class="form-control" placeholder="000.000.000-00" value="{{ old('cpf', $colaborador->cpf) }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>CRECI (Para Corretores)</label>
                        <input type="text" name="creci" class="form-control" placeholder="Ex: CRECI-PB 12345-F" value="{{ old('creci', $colaborador->creci) }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Telefone / WhatsApp</label>
                        <input type="text" name="telefone" class="form-control" placeholder="(00) 90000-0000" value="{{ old('telefone', $colaborador->telefone) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-4">
                        <label>Senha {{ $colaborador->exists ? '(deixe em branco para manter)' : '*' }}</label>
                        <input type="password" name="password" class="form-control" {{ $colaborador->exists ? '' : 'required' }}>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Confirmar Senha</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Foto de Perfil</label>
                        <input type="file" name="foto" class="form-control-file" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('colaboradores.index') }}" class="btn btn-default">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Salvar</button>
            </div>
        </form>
    </div>
@stop
