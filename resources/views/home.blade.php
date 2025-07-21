{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-5">

    @if(Auth::check())
    <div class="mb-5 p-4 bg-light rounded shadow-sm text-center">
        <h4 class="mb-3 text-primary fw-semibold">
            Indique e ganhe <strong>5 novas tentativas a cada novo Adivinhador registrado!</strong>
        </h4>
        <div class="input-group justify-content-center mb-3">
            <input type="text" id="linkIndicacao" class="form-control w-auto text-truncate" style="max-width: 400px;"
                value="{{ route('register', ['ib' => auth()->user()->uuid]) }}" readonly>
            <button class="btn btn-outline-primary" id="btnCopiarLink" type="button">Copiar link</button>
        </div>
        <p><strong id="tentativas-restantes">Restam {{ $trys }}</strong> tentativas para você. Se quiser você pode <a href="{{ route('tentativas.comprar') }}" class="btn btn-primary" >comprar mais</a></p>
        <p>Você recebe 10 tentativas gratuitamente todos os dias!</p>
    </div>
    @endif

    @forelse($adivinhacoes as $adivinhacao)
    <div class="card mb-5 shadow-sm">
        <div class="row g-0 flex-column flex-md-row">
            <div class="col-md-5 text-center bg-light p-2">
                <img src="{{ asset('storage/' . $adivinhacao->imagem) }}" class="img-fluid rounded" alt="Imagem da adivinhação">
            </div>
            <div class="col-md-7 p-4 d-flex flex-column justify-content-between">
                <div>
                    <h2 class="text-primary fw-bold mb-3">{{ $adivinhacao->titulo }}</h2>
                    <p>{!! $adivinhacao->descricao !!}</p>
                    <p class="text-muted small">👥 {{ $adivinhacao->count_respostas }} respostas até agora</p>

                    @auth
                        @if($limitExceded)
                        <div class="alert alert-warning">Você atingiu seu limite de resposta de hoje!</div>
                        @else
                        <div class="mb-3">
                            <input type="text" id="resposta-{{ $adivinhacao->id }}" class="form-control"
                                name="resposta" placeholder="O que você acha que é?">
                        </div>
                        <input type="hidden" name="adivinhacao_id" value="{{ $adivinhacao->id }}">
                        <button class="btn btn-success w-100">Enviar resposta</button>
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
                <div class="mt-3 text-end">
                    <a href="{{ $adivinhacao->premio }}" class="btn btn-outline-primary" target="_blank">🎁 Ver prêmio</a>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="text-center">
        <h4 class="text-muted">Nenhuma adivinhação disponível no momento.</h4>
    </div>
    @endforelse

    @if($premios->isNotEmpty())
    <hr class="my-5">

    <h2 class="mb-3">🎉 Prêmios conquistados</h2>

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
                    <td class="d-flex gap-2 flex-wrap">
                        @php
                            $isLink = filter_var($premio->premio, FILTER_VALIDATE_URL);
                        @endphp
                        @if($isLink)
                        <a href="{{ $premio->premio }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            Ver prêmio
                        </a>
                        @endif
                        <a href="{{ route('adivinhacoes.respostas', $premio->uuid) }}" target="_blank"
                            class="btn btn-sm btn-outline-primary">
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
    let tentativas = parseInt(document.getElementById('tentativas-restantes').textContent.replace(/\D/g, ''));

    // Habilita log detalhado
    Pusher.logToConsole = true;

    const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
    const EchoCtor = window.Echo;


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


    // Canal público: adivinhacoes
    window.Echo.channel('adivinhacoes')
      .listen('.resposta.aprovada', e => {

        document.querySelectorAll('input[name="resposta"], .btn-success')
                .forEach(el => el.disabled = true);

        Swal.fire('Adivinhação encerrada', e.mensagem, 'info');
      });

    // Canal privado do usuário
    @auth
    window.Echo.private(`user.{{ Auth::id() }}`)
      .listen('.resposta.sucesso', e => {

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
  const tentativasEl = document.getElementById('tentativas-restantes');
 tentativas = tentativasEl ? parseInt(tentativasEl.textContent.replace(/\D/g, '')) : 0;

document.querySelectorAll('.btn-success').forEach(btn => {
  btn.addEventListener('click', async () => {
    const body = btn.closest('.card-body');
    const id = body.querySelector('[name="adivinhacao_id"]').value;
    const input = body.querySelector(`#resposta-${id}`);
    const resposta = input.value;

    if (!resposta) {
      const msg = document.createElement('div');
      msg.className = 'mt-2 text-danger';
      msg.textContent = 'Preencha a resposta primeiro!';
      input.insertAdjacentElement('afterend', msg);
      return;
    }

    // Atualiza o número de tentativas antes do fetch
    if (tentativas > 0) {
      tentativas--;
      if (tentativasEl) {
        tentativasEl.textContent = 'Restam ' + tentativas;
      }
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

      input.value = '';

      body.querySelectorAll('.resposta-enviada').forEach(el => el.remove());
      const msg = document.createElement('div');
      if(json.error) {
        msg.className = 'mt-2 text-danger';
        msg.textContent = json.error;
      } else {
        msg.className = 'mt-2 text-success resposta-enviada';
        msg.textContent = json.status === 'acertou'
          ? 'Você acertou! Em breve notificaremos o envio do prêmio.'
          : 'Que pena! Tente novamente (se ainda tiver tentativas)!';
      }
      input.insertAdjacentElement('afterend', msg);

    } catch (error) {
      // Se quiser pode aqui reverter a decrementação em caso de erro na requisição.
    }
  });
});

  document.getElementById('btnCopiarLink').addEventListener('click', function() {
        const input = document.getElementById('linkIndicacao');
        input.select();
        input.setSelectionRange(0, 99999); // Para dispositivos móveis

        navigator.clipboard.writeText(input.value).then(() => {
            // Feedback com Bootstrap Toast ou alert simples
            this.textContent = 'Copiado!';
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-success');
            setTimeout(() => {
                this.textContent = 'Copiar link';
                this.classList.remove('btn-success');
                this.classList.add('btn-outline-primary');
            }, 2000);
        }).catch(() => {
            alert('Não foi possível copiar o link. Por favor, copie manualmente.');
        });
    });

  </script>
@endpush
