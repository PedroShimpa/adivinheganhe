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
        <div class="col-md-5 d-flex align-items-center justify-content-center bg-light p-3">
            <img src="{{ asset('storage/' . $adivinhacao->imagem) }}" class="img-fluid rounded" alt="Imagem da adivinhação">
        </div>
        <div class="col-md-7 p-4 d-flex flex-column justify-content-between">
            <div>
                <h2 class="text-primary fw-bold mb-3">{{ $adivinhacao->titulo }}</h2>

                <!-- Botão que abre o modal com a descrição -->
                <button class="btn btn-outline-info mb-2" data-bs-toggle="modal" data-bs-target="#modalDescricao-{{ $adivinhacao->id }}">
                    ➕ Informações
                </button>

                <!-- Modal de descrição -->
                <div class="modal fade" id="modalDescricao-{{ $adivinhacao->id }}" tabindex="-1" aria-labelledby="modalLabel-{{ $adivinhacao->id }}" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel-{{ $adivinhacao->id }}">{{ $adivinhacao->titulo }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                      </div>
                      <div class="modal-body">
                        {!! $adivinhacao->descricao !!}
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                      </div>
                    </div>
                  </div>
                </div>

                @if($adivinhacao->count_respostas != 0)
                <p class="text-muted small mt-2 mb-2">👥 {{ $adivinhacao->count_respostas }} tentativas até agora</p>
                @else
                <p class="text-muted small mt-2 mb-2">👥 Nínguem tentou adivinhar ainda!</p>

                @endif

                @auth
                    @if($limitExceded)
                    <div class="alert alert-warning">Você atingiu seu limite de tentativas de hoje!</div>
                    @else
                    <div class="mb-3">
<input type="text" id="resposta-{{ $adivinhacao->id }}" class="form-control border-2 border-primary fs-5 fw-semibold" name="resposta" placeholder="💬 Digite sua resposta aqui">

                    </div>
                    <input type="hidden" name="adivinhacao_id" value="{{ $adivinhacao->id }}">
                    <button class="btn btn-success w-100">Enviar resposta</button>
                    @endif
                @else
                <div class="alert alert-warning">
                    Você precisa <a href="{{ route('login') }}">entrar</a> para responder. Pode vir é <span class="text-success">grátis</span>
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
                    <th>Resposta</th>
                    <th>Ações</th>
                    <th>Usuário</th>
                    <th>Prêmio Enviado?</th>
                </tr>
            </thead>
            <tbody>
                @foreach($premios as $premio)
                <tr>
                    <td>{{ $premio->titulo }}</td>
                    <td>{{ $premio->resposta }}</td>
                    <td class="d-flex gap-2 flex-wrap">
                        @php
                            $isLink = filter_var($premio->premio, FILTER_VALIDATE_URL);
                        @endphp
                        @if($isLink)
                        <a href="{{ $premio->premio }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            Ver prêmio
                        </a>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-primary btn-ver-tentativas"
        data-uuid="{{ $premio->uuid }}"
        data-bs-toggle="modal" data-bs-target="#modalRespostas">
    Ver Tentativas
</button>

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

<div class="modal fade" id="modalRespostas" tabindex="-1" aria-labelledby="modalRespostasLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalRespostasLabel">Respostas da Adivinhação</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body p-0" id="modalRespostasBody">
        <div class="text-center p-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-3 text-muted">Carregando respostas...</p>
        </div>
      </div>
    </div>
  </div>
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
    const body = btn.closest('.col-md-7');
    const id = body.querySelector('[name="adivinhacao_id"]').value;
    const input = body.querySelector(`#resposta-${id}`);
    const resposta = input.value;

    body.querySelectorAll('.resposta-enviada, .text-danger').forEach(el => el.remove());

    if (!resposta.trim()) {
      const msg = document.createElement('div');
      msg.className = 'mt-2 text-danger fw-bold';
      msg.textContent = 'Preencha a resposta primeiro!';
      input.insertAdjacentElement('afterend', msg);
      return;
    }

    if (tentativas <= 0) {
      Swal.fire('Sem tentativas!', 'Você não possui mais tentativas 😞', 'warning');
      return;
    }

    tentativas--;
    if (tentativasEl) {
      tentativasEl.textContent = 'Restam ' + tentativas + ' tentativa' + (tentativas === 1 ? '' : 's');
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

      const msg = document.createElement('div');
      msg.className = 'mt-2 fw-semibold resposta-enviada';

      if(json.error) {
        msg.classList.add('text-danger');
        msg.textContent = `Que pena, você errou! ${tentativas > 0 ? 'Mas ainda possui ' + tentativas + ' tentativa' + (tentativas === 1 ? '' : 's') : 'Você não possui mais tentativas 😞'}`;
      } else {
        msg.classList.add('text-success');
        msg.textContent = '🎉 Você acertou! Em breve notificaremos o envio do prêmio.';
        input.disabled = true;
        btn.disabled = true;
      }

      input.insertAdjacentElement('afterend', msg);

    } catch (error) {
      Swal.fire('Erro', 'Erro ao enviar a resposta. Tente novamente!', 'error');
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

      document.querySelectorAll('.btn-ver-tentativas').forEach(btn => {
    btn.addEventListener('click', () => {
      const uuid = btn.dataset.uuid;
      const modalBody = document.getElementById('modalRespostasBody');

      modalBody.innerHTML = `
        <div class="text-center p-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-3 text-muted">Carregando respostas...</p>
        </div>
      `;

      fetch(`/adivinhacoes/${uuid}/respostas`)
        .then(res => {
          if (!res.ok) throw new Error("Erro ao carregar respostas");
          return res.text();
        })
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const content = doc.querySelector('.container');
          if (content) modalBody.innerHTML = content.innerHTML;
          else throw new Error('Conteúdo inválido');
        })
        .catch(() => {
          modalBody.innerHTML = '<div class="p-4 text-danger">Erro ao carregar respostas. Tente novamente mais tarde.</div>';
        });
    });
  })

  </script>
@endpush
