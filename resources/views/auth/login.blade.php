@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@section('auth_header', 'Entre com suas credenciais para iniciar a sessão')

@section('auth_body')
    <form action="{{ route('login') }}" method="post">
        @csrf

        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="E-mail" autofocus required>

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>

            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="input-group mb-3">
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   placeholder="Senha" required>

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>

            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="row">
            <div class="col-7">
                <div class="icheck-primary" title="Manter conectado neste dispositivo">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">Lembrar-me</label>
                </div>
            </div>

            <div class="col-5">
                <button type="submit" class="btn btn-block btn-flat btn-primary">
                    <span class="fas fa-sign-in-alt mr-1"></span> Entrar
                </button>
            </div>
        </div>
    </form>
@stop

@section('auth_footer')
    <div class="text-center">
        <a href="{{ route('catalogo.index') }}" class="btn btn-outline-primary btn-block mb-2">
            <i class="fas fa-building mr-1"></i> Ver Catálogo Público de Imóveis
        </a>
        @if (Route::has('password.request'))
            <p class="my-1 small">
                <a href="{{ route('password.request') }}">Esqueci minha senha</a>
            </p>
        @endif
    </div>
@stop
