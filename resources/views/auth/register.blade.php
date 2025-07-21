{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow rounded-4">
                <div class="card-body p-5">
                    <h2 class="mb-4 text-center text-primary fw-bold">Criar Conta</h2>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <input name="indicated_by" hidden>

                        <!-- Nome -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                name="name" value="{{ old('name') }}" required autofocus>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nome de Usuário -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Nome de Usuário</label>
                            <input id="username" type="text" class="form-control @error('username') is-invalid @enderror"
                                name="username" value="{{ old('username') }}" required>
                            @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                name="email" value="{{ old('email') }}" required>
                            @error('email')
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
                                name="whatsapp" value="{{ old('whatsapp') }}">
                            @error('whatsapp')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Este campo é solicitado para notificar você sobre envio de prêmios e solicitar informações adicionais, se necessário.
                            </div>
                        </div>

                        <!-- Senha -->
                        <div class="mb-4">
                            <label for="password" class="form-label">Senha</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                name="password" required autocomplete="new-password">
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Botão -->
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('login') }}" class="text-decoration-none small text-muted">
                                Já tem uma conta?
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                Registrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js" integrity="sha512-F5Ul1uuyFlGnIT1dk2c4kB4DBdi5wnBJjVhL7gQlGh46Xn0VhvD8kgxLtjdZ5YN83gybk/aASUAlpdoWUjRR3g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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
    });
</script>
@endpush