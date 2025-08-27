@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container  ">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header text-center p-4" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">
                    <h2 class="text-white fw-bold mb-0">📌 Criar Nova Pergunta</h2>
                </div>

                <div class="card-body p-4">

                    @if(session('success'))
                        <div class="alert alert-success text-center">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('adivinhe_o_milhao.store_pergunta') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Descrição --}}
                        <div class="mb-3">
                            <label for="descricao" class="form-label fw-bold">Descrição da Pergunta</label>
                            <textarea name="descricao" id="descricao" class="form-control" rows="3" placeholder="Digite a pergunta aqui..." required>{{ old('descricao') }}</textarea>
                            @error('descricao')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Resposta --}}
                        <div class="mb-3">
                            <label for="resposta" class="form-label fw-bold">Resposta Correta</label>
                            <input type="text" name="resposta" id="resposta" class="form-control" placeholder="Digite a resposta correta" required value="{{ old('resposta') }}">
                            @error('resposta')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Arquivo --}}
                        <div class="mb-4">
                            <label for="arquivo" class="form-label fw-bold">Arquivo (opcional: imagem, áudio ou vídeo)</label>
                            <input type="file" name="arquivo" id="arquivo" class="form-control">
                            @error('arquivo')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Botão enviar --}}
                        <div class="text-center">
                            <button type="submit" class="btn btn-lg btn-success px-5 py-2 rounded-pill shadow fw-bold">
                                ✅ Criar Pergunta
                            </button>
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </div>
</div>
@endsection
