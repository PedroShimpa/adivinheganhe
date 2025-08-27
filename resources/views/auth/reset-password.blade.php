@extends('layouts.app')

@section('content')
<div class="container   d-flex justify-content-center">
    <div class="card shadow-4 w-100" style="max-width: 500px;">
        <div class="card-body p-4">

            <h4 class="mb-4 text-center">{{ __('Redefinir Senha') }}</h4>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Token de redefinição -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email -->
                <div class="form-outline mb-4">
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" />
                    <label class="form-label" for="email">{{ __('Email') }}</label>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nova senha -->
                <div class="form-outline mb-4">
                    <input type="password" id="password" name="password"
                        class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password" />
                    <label class="form-label" for="password">{{ __('Nova Senha') }}</label>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirmar senha -->
                <div class="form-outline mb-4">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="form-control @error('password_confirmation') is-invalid @enderror" required autocomplete="new-password" />
                    <label class="form-label" for="password_confirmation">{{ __('Confirmar Senha') }}</label>
                    @error('password_confirmation')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Redefinir Senha') }}
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
