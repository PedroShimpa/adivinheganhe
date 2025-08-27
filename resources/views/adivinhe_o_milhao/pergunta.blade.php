@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container  ">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">

            {{-- Contador de tempo restante --}}
            <div class="alert alert-warning text-center fw-bold fs-5 shadow-sm mb-4 rounded-pill">
                ⏳ Tempo restante: <span id="contador">
                    {{ str_pad(intval($tempoRestante / 60), 2, '0', STR_PAD_LEFT) }}:{{ str_pad($tempoRestante % 60, 2, '0', STR_PAD_LEFT) }}
                </span>
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
                        <img src="{{ str_starts_with($pergunta['arquivo'], 'http') ? $pergunta['arquivo'] :  asset('/storage/' . $pergunta['arquivo'])  }}"
                            class="img-fluid rounded shadow"
                            alt="Imagem da pergunta">
                        @elseif(in_array(strtolower($ext), ['mp3','wav','ogg']))
                        <audio controls class="w-100 mt-2">
                            <source src="{{ str_starts_with($pergunta['arquivo'], 'http') ? $pergunta['arquivo'] :  asset('/storage/' . $pergunta['arquivo'])  }}" type="audio/{{ $ext }}">
                            Seu navegador não suporta áudio.
                        </audio>
                        @elseif(in_array(strtolower($ext), ['mp4','webm','mov']))
                        <video controls class="w-100 rounded mt-2">
                            <source src="{{ str_starts_with($pergunta['arquivo'], 'http') ? $pergunta['arquivo'] :  asset('/storage/' . $pergunta['arquivo'])  }}" type="video/{{ $ext }}">
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
                                placeholder="Sua resposta aqui..." required autofocus
                                onpaste="return false" oncopy="return false" oncut="return false">

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
    (function() {
        const tempoInicial = Math.max(0, Math.min(600, Number(@json((int) $tempoRestante))));
        const contador = document.getElementById('contador');
        if (!contador) return;

        const target = Date.now() + tempoInicial * 1000;

        function formatar(seg) {
            const m = String(Math.floor(seg / 60)).padStart(2, '0');
            const s = String(seg % 60).padStart(2, '0');
            return `${m}:${s}`;
        }

        function tick() {
            const restante = Math.max(0, Math.floor((target - Date.now()) / 1000));
            contador.textContent = formatar(restante);

            if (restante <= 0) {
                clearInterval(timer);
                alert("⏰ O tempo acabou!");
                window.location.replace("{{ route('home') }}");
            }
        }

        contador.textContent = formatar(tempoInicial);
        const timer = setInterval(tick, 1000);

        const respostaInput = document.getElementById('resposta');
        if (respostaInput) {
            respostaInput.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'v') {
                    e.preventDefault();
                    alert('Colar não é permitido!');
                }
            });
            respostaInput.addEventListener('paste', function(e) {
                e.preventDefault();
                alert('Colar não é permitido!');
            });
            respostaInput.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                alert('Colar não é permitido!');
            });
        }
    })();
</script>

@endsection