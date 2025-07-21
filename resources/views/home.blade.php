{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-5">
    @forelse($adivinhacoes as $adivinhacao)
    <div class="card mb-4 shadow-sm">
        <div class="row g-0">
            <div class="col-md-5">
                <img src="{{ asset('storage/' . $adivinhacao->imagem) }}"
                     class="img-fluid"
                     alt="Imagem da adivinhação">
            </div>
            <div class="col-md-7 d-flex flex-column justify-content-between">
                <div class="card-body">
                  <h1 class="card-title display-5 fw-bold text-primary">{{ $adivinhacao->titulo }}</h1>
                    <p class="">{!! $adivinhacao->descricao !!}</p>
                    <p class="text-muted">Quantidade de respostas até agora: {{ $adivinhacao->count_respostas }}</p>
                    @auth
                    @if($limitExceded)
                     <div class="alert alert-warning">
                            Você atingiu seu limite de resposta de hoje!
                        </div>
                    @else
                        <div class="mb-2">
                            <input
                                type="text"
                                id="resposta-{{ $adivinhacao->id }}"
                                class="form-control"
                                name="resposta"
                                placeholder="Digite sua resposta">
                        </div>
                        <input type="hidden"
                               name="adivinhacao_id"
                               value="{{ $adivinhacao->id }}">
                        <button class="btn btn-success">Enviar resposta</button>
                        @endif
                    @else
                        <div class="alert alert-warning">
                            Você precisa <a href="{{ route('login') }}">entrar</a> para responder.
                        </div>
                    @endauth
                </div>
                  @php
                        $isLink = filter_var($adivinhacao->premio, FILTER_VALIDATE_URL);
                    @endphp

                @if($isLink)
                <div class="card-footer bg-transparent border-0">
                    <a href="{{ $adivinhacao->premio }}"
                       class="btn btn-outline-primary"
                       target="_blank">Ver prêmio</a>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="text-center">
      <h1>Não há adivinhações disponíveis por enquanto, em breve novas serão adicionadas!</h1>
    </div>
    @endforelse

@if($premios->isNotEmpty())
<hr class="my-5">

<h2 class="mb-3">🎁 Prêmios já conquistados</h2>

<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>Título</th>
                <th>Ações</th>
                <th>Usuário</th>
                <th>Prêmio Enviado?</th>
            </tr>
        </thead>
        <tbody>
            @foreach($premios as $premio)
                <tr>
                    <td>{{ $premio->titulo }}</td>
                    <td>
                    @php
                        $isLink = filter_var($premio->premio, FILTER_VALIDATE_URL);
                    @endphp

                    @if($isLink)
                        <a href="{{ $premio->premio }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            Ver prêmio
                        </a>
                    @endif

                        <a href="{{ route('adivinhacoes.respostas', $premio->uuid)}}" target="_blank" class="btn btn-sm btn-outline-primary">
                            Ver respostas
                        </a>
                    </td>
                    <td>{{ $premio->username }}</td>
                    <td>
                        @if($premio->premio_enviado === 'S')
                            <span class="badge bg-success">Sim</span>
                        @else
                            <span class="badge bg-warning text-dark">Não</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

</div>
@endsection
@push('scripts')
  <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.11.1/dist/echo.iife.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    // Habilita log detalhado
    Pusher.logToConsole = true;

    const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
    const EchoCtor = window.Echo;

    console.log('[Echo] Inicializando com chave:', '{{ env("REVERB_APP_KEY") }}');

    window.Echo = new EchoCtor({
      broadcaster: 'pusher',
      key: '{{ env("REVERB_APP_KEY") }}',
      wsHost: '{{ env("REVERB_HOST", "localhost") }}',
      wsPort: {{ env("REVERB_PORT", 8080) }},
      forceTLS: false,
      disableStats: true,
      authEndpoint: '/broadcasting/auth',
      auth: {
        headers: {
          'X-CSRF-TOKEN': csrfToken
        }
      }
    });

    console.log('[Echo] Echo instanciado:', window.Echo);

    // Canal público: adivinhacoes
    console.log('[Echo] Tentando se inscrever no canal público: adivinhacoes');
    window.Echo.channel('adivinhacoes')
      .listen('.resposta.aprovada', e => {
        console.log('[Broadcast] Evento recebido: resposta.aprovada', e);

        document.querySelectorAll('input[name="resposta"], .btn-success')
                .forEach(el => el.disabled = true);

        Swal.fire('Adivinhação encerrada', e.mensagem, 'info');
      });

    // Canal privado do usuário
    @auth
    console.log('[Echo] Tentando se inscrever no canal privado: user.{{ Auth::id() }}');
    window.Echo.private(`user.{{ Auth::id() }}`)
      .listen('.resposta.sucesso', e => {
        console.log('[Broadcast] Evento privado recebido: resposta.sucesso', e);

        Swal.fire('Parabéns!', e.mensagem, 'success')
          .then(() => {
            const container = document
              .querySelector(`input[name="adivinhacao_id"][value="${e.adivinhacaoId}"]`)
              .closest('.card-body');

            container.querySelector(`#resposta-${e.adivinhacaoId}`).disabled = true;
            container.querySelector('.btn-success').disabled = true;
          });
      });
    @endauth

    // Envio da resposta
    document.querySelectorAll('.btn-success').forEach(btn => {
      btn.addEventListener('click', async () => {
        const body = btn.closest('.card-body');
        const id = body.querySelector('[name="adivinhacao_id"]').value;
        const input = body.querySelector(`#resposta-${id}`);
        const resposta = input.value;


        if(!resposta) {
                const msg = document.createElement('div');
                msg.className = 'mt-2 text-danger';
          msg.textContent =  'Preencha a resposta primeiro!'

        
          input.insertAdjacentElement('afterend', msg);
          return;
        }
        try {
          const res = await fetch("{{ route('resposta.enviar') }}", {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              resposta: resposta,
              adivinhacao_id: id
            })
          });

          const json = await res.json();
          console.log('[Resposta] Resposta da API:', json);

          input.value = '';

          body.querySelectorAll('.resposta-enviada').forEach(el => el.remove());
          const msg = document.createElement('div');
          if(json.error) {
                msg.className = 'mt-2 text-danger';
          msg.textContent = json.error
          } else {
              msg.className = 'mt-2 text-success resposta-enviada';
          msg.textContent = json.status === 'acertou'
            ? 'Você acertou! Em breve notificaremos o envio do prêmio.'
            : 'Eroooooou! Tente novamente!';
          }
        
          input.insertAdjacentElement('afterend', msg);
        } catch (error) {
          console.error('[Resposta] Erro ao enviar:', error);
        }
      });
    });
  </script>
@endpush
