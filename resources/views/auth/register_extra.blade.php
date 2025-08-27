@extends('layouts.app')

@section('content')
<div class="container  ">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow rounded-4">
                <div class="card-body p-5">
                    <h2 class="mb-4 text-center text-primary fw-bold">Finalizar Cadastro</h2>
                    @error('fingerprint')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <form method="POST" action="{{ route('register.extra') }}">
                        @csrf

                        <!-- Campos ocultos do Socialite -->
                        <input type="hidden" name="name" value="{{ old('name', $user['name']) }}">
                        <input type="hidden" name="email" value="{{ old('email', $user['email']) }}">
                        <input type="hidden" name="username" value="{{ old('username', $user['username'] ?? explode('@', $user['email'])[0]) }}">

                        <div class="mb-3">
                            <label for="username" class="form-label">Nome de Usuário</label>
                            <input id="username" type="text" class="form-control @error('username') is-invalid @enderror"
                                name="username" value="{{ old('username') }}" required>
                            @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- CPF -->
                        <div class="mb-3">
                            <label for="cpf" class="form-label">CPF</label>
                            <input id="cpf" type="text" class="form-control @error('cpf') is-invalid @enderror"
                                name="cpf" value="{{ old('cpf') }}" required>
                            @error('cpf')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Só pedimos seu CPF para controle maior de contas, ele não será exibido em nenhum lugar do site e nenhum dos nossos colaboradores tem acesso a ele!
                            </div>
                        </div>

                        <!-- WhatsApp -->
                        <div class="mb-3">
                            <label for="whatsapp" class="form-label">WhatsApp</label>
                            <input id="whatsapp" type="text" class="form-control @error('whatsapp') is-invalid @enderror"
                                name="whatsapp" value="{{ old('whatsapp') }}" required>
                            @error('whatsapp')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Este campo é solicitado para notificar você sobre envio de prêmios e solicitar informações adicionais, se necessário.
                            </div>
                        </div>

                        <!-- Botão -->
                        <div class="align-items-center">
                            <button type="submit" class="btn btn-primary px-4">
                                Finalizar Cadastro
                            </button><br />
                            <small>Ao se cadastrar você concorda com nossos termos e condições!</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js" integrity="sha512-F5Ul1uuyFlGnIT1dk2c4kB4DBdi5wnBJjVhL7gQlGh46Xn0VhvD8kgxLtjdZ5YN83gybk/aASUAlpdoWUjRR3g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://openfpcdn.io/fingerprintjs/v4"></script>

<script>
    $(document).ready(function() {
        // Máscaras existentes
        $('#cpf').inputmask('999.999.999-99');
        $('#whatsapp').inputmask('(99) 99999-9999');
        $('#cep').inputmask('99999-999');

        // Preencher campo indicado_by da query string
        const urlParams = new URLSearchParams(window.location.search);
        const ib = urlParams.get('ib');
        if (ib) {
            $('input[name="indicated_by"]').val(ib);
        }

        // Bloquear botão submit ao enviar o formulário
        $('form').on('submit', function() {
            // Desabilita o botão dentro deste form para evitar múltiplos cliques
            $(this).find('button[type="submit"]').attr('disabled', true).text('Registrando...');
        });

        const fpPromise = import('https://openfpcdn.io/fingerprintjs/v4')
            .then(FingerprintJS => FingerprintJS.load())

        // Get the visitor identifier when you need it.
        fpPromise
            .then(fp => fp.get())
            .then(result => {
                // This is the visitor identifier:
                const visitorId = result.visitorId
                fetch("{{ route('salvar_fingerprint')}}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        fingerprint: visitorId
                    })
                });
            })
    });
</script>
@endpush