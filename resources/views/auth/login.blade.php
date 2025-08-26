@extends('layouts.app')

@section('content')
<div class="container ">

    <!-- Seção de convite para registro -->
    <div class="text-center mb-5">
        <h3 class="fw-bold text-white">Ainda não tem uma conta?</h3>
        <p class="text-white">Junte-se a nós gratuitamente e comece agora mesmo!</p>
        <a href="{{ route('register') }}" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
            Criar minha conta
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow rounded-4">
                <div class="card-body p-5">

                    <h2 class="mb-4 text-center text-primary fw-bold">Entrar</h2>

                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                name="email" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Senha -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Senha</label>
                            <input id="password" type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Lembrar -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
                            <label class="form-check-label" for="remember_me">Lembrar de mim</label>
                        </div>

                        <!-- Botões -->
                        <div class="d-flex flex-column gap-3">

                            <button type="submit" class="btn btn-primary w-100">
                                Entrar
                            </button>

                            <a href="{{ route('login.google') }}" class="btn btn-light border w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-google fs-5"></i>
                                Entrar com Google
                            </a>

                            @if (Route::has('password.request'))
                                <a class="text-decoration-none small text-center mt-2" href="{{ route('password.request') }}">
                                    Esqueceu a senha?
                                </a>
                            @endif

                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
