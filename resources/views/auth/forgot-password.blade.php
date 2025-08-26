@extends('layouts.app')

@section('content')
<div class="container  d-flex justify-content-center">
    <div class="card shadow-4 w-100" style="max-width: 500px;">
        <div class="card-body p-4">

            <h4 class="mb-4 text-center">{{ __('Redefinir Senha') }}</h4>

            <div class="alert alert-info small" role="alert">
                {{ __('Esqueceu sua senha? Sem problemas. Informe seu endereço de e-mail e enviaremos um link para redefinição de senha.') }}
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="alert alert-success mb-3">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div class="form-outline mb-4">
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus />
                    <label class="form-label" for="email">{{ __('Email') }}</label>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Enviar link de redefinição') }}
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
