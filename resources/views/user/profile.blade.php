@extends('layouts.app', ['enable_adsense' => false])

@section('content')
<div class="container py-5" style="max-width: 700px;">
    <h1 class="mb-4 fw-bold">Editar Perfil</h1>

    <form action="{{ route('profile.update') }}" method="POST" class="card shadow-sm p-4">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <label for="name" class="form-label fw-semibold">Nome</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror"
                id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
            @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="username" class="form-label fw-semibold">Usuário</label>
            <input type="text" class="form-control @error('username') is-invalid @enderror"
                id="username" name="username" value="{{ old('username', auth()->user()->username) }}" required>
            @error('username')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
            @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="whatsapp" class="form-label fw-semibold">WhatsApp</label>
            <input type="text" class="form-control @error('whatsapp') is-invalid @enderror"
                id="whatsapp" name="whatsapp" value="{{ old('whatsapp', auth()->user()->whatsapp) }}"
                placeholder="(99) 99999-9999">
            <small class="form-text text-muted">
                Nós usamos o WhatsApp para coletar informações de entrega de prêmios como chave Pix e outros.
            </small>
            @error('whatsapp')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-primary px-4">
                Salvar Alterações
            </button>

            <button type="button" onclick="confirmDelete()" class="btn btn-danger px-4">
                Excluir Conta
            </button>
        </div>
    </form>

    {{-- Formulário oculto para exclusão --}}
    <form id="deleteForm" action="{{ route('profile.destroy') }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
</div>

@endsection
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js" integrity="sha512-F5Ul1uuyFlGnIT1dk2c4kB4DBdi5wnBJjVhL7gQlGh46Xn0VhvD8kgxLtjdZ5YN83gybk/aASUAlpdoWUjRR3g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function() {
        $('#whatsapp').inputmask('(99) 99999-9999');


        function confirmDelete() {
            if (confirm('Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.')) {
                document.getElementById('deleteForm').submit();
            }
        }
    });
</script>
@endpush