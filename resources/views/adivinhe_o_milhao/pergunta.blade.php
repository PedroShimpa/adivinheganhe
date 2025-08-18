@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">

            {{-- Contador de tempo restante --}}
            <div class="alert alert-warning text-center fw-bold fs-5 shadow-sm mb-4 rounded-pill">
                ⏳ Tempo restante: <span id="contador">{{ $tempoRestante }}</span> segundos
            </div>

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-body p-4">

                    {{-- Pergunta --}}
                    <h4 class="fw-bold mb-3 text-center">
                        {{ $pergunta['descricao'] }}
                    </h4>

                    {{-- Exibir arquivo se existir --}}
                    @if(!empty($pergunta['arquivo']))
                        <div class="text-center mb-4">
                            @php
                                $ext = pathinfo($pergunta['arquivo'], PATHINFO_EXTENSION);
                            @endphp

                            @if(in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp']))
                                <img src="{{ asset('storage/'.$pergunta['arquivo']) }}" 
                                     class="img-fluid rounded shadow" 
                                     alt="Imagem da pergunta">
                            @elseif(in_array(strtolower($ext), ['mp3','wav','ogg']))
                                <audio controls class="w-100 mt-2">
                                    <source src="{{ asset('storage/'.$pergunta['arquivo']) }}" type="audio/{{ $ext }}">
                                    Seu navegador não suporta áudio.
                                </audio>
                            @elseif(in_array(strtolower($ext), ['mp4','webm','mov']))
                                <video controls class="w-100 rounded mt-2">
                                    <source src="{{ asset('storage/'.$pergunta['arquivo']) }}" type="video/{{ $ext }}">
                                    Seu navegador não suporta vídeo.
                                </video>
                            @endif
                        </div>
                    @endif

                    {{-- Formulário de resposta --}}
                    <form action="{{ route('adivinhe_o_milhao.responder') }}" method="POST">
                        @csrf
                        <input type="hidden" name="pergunta_id" value="{{ $pergunta['id'] }}">

                        <div class="mb-4">
                            <label for="resposta" class="form-label fw-bold">Digite sua resposta:</label>
                            <input type="text" name="resposta" id="resposta" 
                                   class="form-control form-control-lg rounded-pill text-center"
                                   placeholder="Sua resposta aqui..." required autofocus>
                        </div>

                        <div class="text-center">
                            <button type="submit" 
                                    class="btn btn-lg btn-success px-5 py-2 rounded-pill shadow fw-bold">
                                ✅ Responder
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    let tempo = "{{ $tempoRestante }}";
    const contador = document.getElementById('contador');

    const interval = setInterval(() => {
        tempo--;
        contador.textContent = tempo;

        if (tempo <= 0) {
            clearInterval(interval);
            alert("⏰ O tempo acabou!");
            window.location.href = "{{ route('home') }}";
        }
    }, 1000);
</script>
@endsection
