@extends('layouts.app', ['enable_adsense' => false])

@section('content')
<div class="container mb-5 mt-2" style="max-width: 700px;">
    <h1 class="mb-4 fw-bold">Editar Perfil</h1>
    @if(session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('status') }}
        @if(session('image_upload_notice'))
        <br><small class="text-muted">{{ session('image_upload_notice') }}</small>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="card shadow-sm p-4">
        @csrf
        @method('PATCH')

        {{-- Nome --}}
        <div class="mb-3">
            <label for="name" class="form-label fw-semibold">Nome</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror"
                id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Usuário --}}
        <div class="mb-3">
            <label for="username" class="form-label fw-semibold">Usuário</label>
            <input type="text" class="form-control @error('username') is-invalid @enderror"
                id="username" name="username" value="{{ old('username', auth()->user()->username) }}" required>
            @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- WhatsApp --}}
        <div class="mb-3">
            <label for="whatsapp" class="form-label fw-semibold">WhatsApp</label>
            <input type="text" class="form-control @error('whatsapp') is-invalid @enderror"
                id="whatsapp" name="whatsapp" value="{{ old('whatsapp', auth()->user()->whatsapp) }}"
                placeholder="(99) 99999-9999">
        <small class="form-text text-muted">
                Usamos o WhatsApp para contato de prêmios (Pix, entrega, etc).
            </small>
            @error('whatsapp') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Perfil Privado --}}
        <div class="mb-3">
            <label for="perfil_privado" class="form-label fw-semibold">Perfil Privado</label>
            <select id="perfil_privado" name="perfil_privado" class="form-select @error('perfil_privado') is-invalid @enderror">
                <option value="N" {{ old('perfil_privado', auth()->user()->perfil_privado) === 'N' ? 'selected' : '' }}>Não</option>
                <option value="S" {{ old('perfil_privado', auth()->user()->perfil_privado) === 'S' ? 'selected' : '' }}>Sim</option>
            </select>
            @error('perfil_privado') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Bio --}}
        <div class="mb-3">
            <label for="bio" class="form-label fw-semibold">Bio</label>
            <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="3">{{ old('bio', auth()->user()->bio) }}</textarea>
            @error('bio') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Status VIP --}}
        @if(auth()->user()->isVip())
        <div class="mb-3">
            <div class="alert alert-success">
                <h6 class="alert-heading">⭐ Você é um membro VIP!</h6>
                <p class="mb-0">Sua assinatura expira em {{ auth()->user()->membership_expires_at->format('d/m/Y') }}</p>
            </div>
        </div>
        @endif

        {{-- Imagem --}}
        <div class="mb-3">
            <label for="image" class="form-label fw-semibold">Foto de Perfil</label>
            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
            @if(auth()->user()->image)
            <div class="mt-2">
                <img src="{{ auth()->user()->image }}" alt="Foto de perfil" class="rounded-circle" width="80" height="80">
            </div>
            @endif
            @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Botões --}}
        <div class="d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-primary px-4">Salvar Alterações</button>
            <button type="button" onclick="confirmDelete()" class="btn btn-danger px-4">Excluir Conta</button>
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