@extends('layouts.app')

@section('content')
<div class="container mb-5 mt-2">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            {{-- Login com Google destacado --}}
            <div class="text-center mb-4">
                <a href="{{ route('login.google') }}" class="btn btn-light bg-white border d-flex align-items-center justify-content-center gap-2 shadow-sm py-2 rounded-3">
                    <i class="bi bi-google fs-5 text-danger"></i>
                    <span class="fw-semibold text-dark">Registrar com Google</span>
                </a>
            </div>

            <div class="card shadow rounded-4">
                <div class="card-body p-5">
                    <h2 class="mb-4 text-center text-primary fw-bold">Criar Conta</h2>

                    @error('fingerprint')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <input name="indicated_by" type="hidden">

                        <div class="mb-3">
                            <label for="name" class="form-label">Nome</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                name="name" value="{{ old('name') }}" required autofocus>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Nome de Usuário</label>
                            <input id="username" type="text" class="form-control @error('username') is-invalid @enderror"
                                name="username" value="{{ old('username') }}" required>
                            @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                name="email" value="{{ old('email') }}" required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Senha</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                name="password" required autocomplete="new-password">
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                  
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary w-100 py-2 mb-2">
                                Registrar
                            </button>
                            <a href="{{ route('login') }}" class="d-block small text-decoration-none">
                                Já tem uma conta? <strong>Entrar</strong>
                            </a>
                            <small class="d-block mt-3 text-muted">
                                Ao se cadastrar você concorda com nossos <a href="#" class="text-decoration-underline">termos</a>.
                            </small>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://openfpcdn.io/fingerprintjs/v4"></script>

<script>
    $(document).ready(function() {

        const urlParams = new URLSearchParams(window.location.search);
        const ib = urlParams.get('ib');
        if (ib) {
            $('input[name="indicated_by"]').val(ib);
        }

        const googleBtn = document.getElementById('google-register-btn');
        if (googleBtn) {
            const baseHref = googleBtn.getAttribute('href').split('?')[0];
            googleBtn.setAttribute('href', `${baseHref}?ib=${encodeURIComponent(ib)}`);
        }

        $('form').on('submit', function() {
            $(this).find('button[type="submit"]').attr('disabled', true).text('Registrando...');
        });

        const fpPromise = import('https://openfpcdn.io/fingerprintjs/v4')
            .then(FingerprintJS => FingerprintJS.load())

        fpPromise.then(fp => fp.get()).then(result => {
            const visitorId = result.visitorId;
            fetch("{{ route('salvar_fingerprint') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    fingerprint: visitorId
                })
            });
        });
    });
</script>
@endpush