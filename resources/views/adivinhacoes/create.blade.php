@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Cadastrar Adivinhação') }}</div>

                <div class="card-body">
                    <form action="{{ route('adivinhacoes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="titulo" class="form-label">{{ __('Título') }}</label>
                            <input type="text" class="form-control @error('titulo') is-invalid @enderror" id="titulo" name="titulo" value="{{ old('titulo') }}" required>
                            @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="descricao" class="form-label">{{ __('Descrição') }}</label>
                            <textarea class="form-control @error('descricao') is-invalid @enderror" id="descricao" name="descricao" rows="3">{!! old('descricao') !!}</textarea>
                            @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="premio" class="form-label">{{ __('Prêmio') }}</label>
                            <input type="text" class="form-control @error('premio') is-invalid @enderror" id="premio" name="premio" value="{{ old('premio') }}" required>
                            @error('premio')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="resposta" class="form-label">{{ __('Resposta') }}</label>
                            <input type="text" class="form-control @error('resposta') is-invalid @enderror" id="resposta" name="resposta" value="{{ old('resposta') }}" required>
                            @error('resposta')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="imagem" class="form-label">{{ __('Imagem') }}</label>
                            <input class="form-control @error('imagem') is-invalid @enderror" type="file" id="imagem" name="imagem" required>
                            @error('imagem')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('Salvar Adivinhação') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.tiny.cloud/1/ai1aa4q1itml9cxyksgdamr2r0v61fis91gwfmb5bdt9n6df/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        tinymce.init({
            selector: '#descricao',
            menubar: false,
            height: 300,
            plugins: 'link lists code',
            toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
            language: 'pt_BR'
        });
    });
</script>
@endpush