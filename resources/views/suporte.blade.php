@extends('layouts.app')

@section('content')
<div class="container mb-5 mt-2">
    <h2 class="mb-4">🛠️ Nós vamos te ajudar!</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('suporte.store') }}" method="POST">
        @csrf

        @guest
        <div class="mb-3">
            <label for="nome" class="form-label text-white">Nome *</label>
            <input type="text" name="nome" class="form-control" value="{{ old('nome') }}">
            @error('nome') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label text-white">Email *</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
        </div>
        @endguest

        <div class="mb-3">
            <label for="categoria_id" class="form-label text-white">Categoria *</label>
            <select name="categoria_id" class="form-select">
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->descricao }}
                    </option>
                @endforeach
            </select>
            @error('categoria_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label text-white">Descrição *</label>
            <textarea name="descricao" rows="4" class="form-control">{{ old('descricao') }}</textarea>
            @error('descricao') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Enviar chamado</button>
    </form>
</div>
@endsection
