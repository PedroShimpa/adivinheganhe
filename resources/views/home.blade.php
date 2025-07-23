{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-4">

    @if(Auth::check())
    <div class="mb-4 p-3 bg-light rounded shadow-sm text-center">
        <h5 class="mb-2 text-primary fw-semibold">
            Indique e ganhe <strong>5 tentativas por novo Adivinhador registrado no seu link!</strong>
        </h5>
        <div class="input-group mb-3 mx-auto" style="max-width: 100%;">
            <input type="text" id="linkIndicacao" class="form-control text-truncate" style="max-width: 400px;"
                value="{{ route('register', ['ib' => auth()->user()->uuid]) }}" readonly>
            <button class="btn btn-outline-primary" id="btnCopiarLink">Copiar link</button>
        </div>
        <p class="mb-1"><strong id="tentativas-restantes">Restam {{ $trys }}</strong> tentativas. 
            <a href="{{ route('tentativas.comprar') }}" class="btn btn-sm btn-primary ms-2">Comprar mais</a>
        </p>
        <p class="small">Você tem 10 tentativas (não acumulativas) gratuitas todos os dias!</p>
    </div>
    @endif

    @forelse($adivinhacoes as $adivinhacao)
    <div class="card mb-4 shadow-sm">
        <div class="row g-0 flex-wrap">
            <div class="col-12 col-md-5 d-flex align-items-center justify-content-center bg-light p-2">
                <img src="{{ asset('storage/' . $adivinhacao->imagem) }}" class="img-fluid w-100 rounded" alt="Imagem da adivinhação">
            </div>
            <div class="col-12 col-md-7 p-3 d-flex flex-column justify-content-between">
                <div>
                    <h5 class="text-primary fw-bold mb-2">{{ $adivinhacao->titulo }}</h5>

                    <button class="btn btn-outline-info btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#modalDescricao-{{ $adivinhacao->id }}">
                        ➕ Informações
                    </button>

                    <div class="modal fade" id="modalDescricao-{{ $adivinhacao->id }}" tabindex="-1" aria-labelledby="modalLabel-{{ $adivinhacao->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalLabel-{{ $adivinhacao->id }}">{{ $adivinhacao->titulo }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">{!! $adivinhacao->descricao !!}</div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small mt-2" id="count-respostas-{{ $adivinhacao->id}}">
                        👥 {{ $adivinhacao->count_respostas ?: 'Ninguém tentou adivinhar ainda!' }}
                    </p>

                    @auth
                        @if($limitExceded)
                        <div class="alert alert-warning p-2 small">Você atingiu o limite de tentativas hoje!</div>
                        @else
                        <div class="mb-2">
                            <input type="text" id="resposta-{{ $adivinhacao->id }}" class="form-control border-primary fs-6 fw-semibold" name="resposta" placeholder="💬 Digite sua resposta">
                        </div>
                        <input type="hidden" name="adivinhacao_id" value="{{ $adivinhacao->id }}">
                        <button class="btn btn-success w-100" id="btn-resposta-{{ $adivinhacao->id }}">Enviar resposta</button>
                        @endif
                    @else
                    <div class="alert alert-warning small">
                        Você precisa <a href="{{ route('login') }}">entrar</a> para responder. É <span class="text-success">grátis</span>!
                    </div>
                    @endauth
                </div>

                @php $isLink = filter_var($adivinhacao->premio, FILTER_VALIDATE_URL); @endphp
                @if($isLink)
                <div class="mt-3 text-end">
                    <a href="{{ $adivinhacao->premio }}" class="btn btn-outline-primary btn-sm" target="_blank">🎁 Ver prêmio</a>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="text-center">
        <h5 class="text-muted">Nenhuma adivinhação disponível no momento.</h5>
    </div>
    @endforelse

    @if($premios->isNotEmpty())
    <hr class="my-4">

    <h5 class="mb-3">🎉 Prêmios conquistados</h5>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark small">
                <tr>
                    <th>Título</th>
                    <th>Resposta</th>
                    <th>Ações</th>
                    <th>Usuário</th>
                    <th>Enviado?</th>
                </tr>
            </thead>
            <tbody>
                @foreach($premios as $premio)
                <tr>
                    <td>{{ $premio->titulo }}</td>
                    <td>{{ $premio->resposta }}</td>
                    <td>
                        @php $isLink = filter_var($premio->premio, FILTER_VALIDATE_URL); @endphp
                        @if($isLink)
                        <a href="{{ $premio->premio }}" target="_blank" class="btn btn-sm btn-outline-primary">Ver prêmio</a>
                        @endif
                      <button class="btn btn-sm btn-outline-primary btn-ver-tentativas"
                            data-uuid="{{ $premio->uuid }}">
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
  <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 90vw; max-height: 90vh;">
    <div class="modal-content" style="height: 90vh;">
      <div class="modal-header">
        <h5 class="modal-title" id="modalRespostasLabel">Respostas da Adivinhação</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0" style="height: calc(100% - 56px);">
        <iframe id="iframeRespostas" src="" style="border:none; width:100%; height:100%;"></iframe>
      </div>
    </div>
  </div>
</div>

@endsection
@push('head-scripts')
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ env('GOOGLE_ADSENSE_TAG')}}"
        crossorigin="anonymous"></script>
@endpush
@push('scripts')
<script src="{{ asset('js/pusher.min.js')}}"></script>
<script src="{{ asset('js/echo.iife.min.js')}}"></script>

<script>
  let tentativas = parseInt($('#tentativas-restantes').text().replace(/\D/g, ''));

  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const EchoCtor = window.Echo;

  window.Echo = new EchoCtor({
    broadcaster: 'pusher',
    key: '{{ env("REVERB_APP_KEY") }}',
    wsHost: '{{ env("REVERB_HOST", "localhost") }}',
    wsPort: '{{ env("REVERB_PORT", 8080) }}',
    forceTLS: false,
    disableStats: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
      headers: {
        'X-CSRF-TOKEN': csrfToken
      }
    }
  });

  window.Echo.channel('adivinhacoes')
    .listen('.resposta.aprovada', e => {
      $('input[name="resposta"], .btn-success').prop('disabled', true);
      Swal.fire('Adivinhação encerrada', e.mensagem, 'info');

      const id = e.adivinhacaoId;
      $(`#resposta-${id}`).prop('disabled', true);
      $(`#btn-resposta-${id}`).prop('disabled', true);
    })
    .listen('.resposta.contagem', e => {
      $('input[name="resposta"], .btn-success').prop('disabled', true);

      const id = e.adivinhacaoId;
      $(`#count-respostas-${id}`).html(e.contagem);
    });

  @auth
  window.Echo.private(`user.{{ Auth::id() }}`)
    .listen('.resposta.sucesso', e => {
      Swal.fire(e.title ?? 'Parabéns!', e.mensagem, 'success').then(() => {
        const id = e.adivinhacaoId;
        $(`#resposta-${id}`).prop('disabled', true);
        $(`#btn-resposta-${id}`).prop('disabled', true);
      });
    });
  @endauth

  $(document).ready(function () {
    $('.btn-success').on('click', async function () {
      const $btn = $(this);
      const $body = $btn.closest('.col-md-7');
      const id = $body.find('[name="adivinhacao_id"]').val();
      const $input = $(`#resposta-${id}`);
      const resposta = $input.val().trim();

      $body.find('.resposta-enviada, .text-danger').remove();

      if (!resposta) {
        $(`<div class="mt-2 text-danger fw-bold">Preencha a resposta primeiro!</div>`).insertAfter($input);
        return;
      }

      if (tentativas <= 0) {
        Swal.fire('Sem tentativas!', 'Você não possui mais tentativas 😞', 'warning');
        return;
      }

      tentativas--;
      $('#tentativas-restantes').text(`Restam ${tentativas} tentativa${tentativas === 1 ? '' : 's'}`);

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
        $input.val('');

        const $msg = $('<div class="mt-2 fw-semibold resposta-enviada"></div>');

        if (json.error) {
          $msg.addClass('text-danger').text(json.error);
        } else {
          if (json.status === 'acertou') {
            $msg.addClass('text-success').text('🎉 Você acertou! Em breve notificaremos o envio do prêmio.');
            $input.prop('disabled', true);
            $btn.prop('disabled', true);
          } else {
            $msg.text(`Que pena, você errou! ${tentativas > 0 ? 'Mas ainda possui ' + tentativas + ' tentativa' + (tentativas === 1 ? '' : 's') : 'Você não possui mais tentativas 😞'}`);
          }
        }

        $msg.insertAfter($input);
      } catch (error) {
        Swal.fire('Erro', 'Erro ao enviar a resposta. Tente novamente!', 'error');
      }
    });

    $('#btnCopiarLink').on('click', function () {
      const $btn = $(this);
      const $input = $('#linkIndicacao');
      $input[0].select();
      $input[0].setSelectionRange(0, 99999);

      navigator.clipboard.writeText($input.val()).then(() => {
        $btn.text('Copiado!').removeClass('btn-outline-primary').addClass('btn-success');
        setTimeout(() => {
          $btn.text('Copiar link').removeClass('btn-success').addClass('btn-outline-primary');
        }, 2000);
      }).catch(() => {
        Swal.fire('Erro', 'Não foi possível copiar o link. Por favor, tente manualmente.', 'error');
      });
    });

    $('.btn-ver-tentativas').on('click', function () {
      const uuid = $(this).data('uuid');
      $('#iframeRespostas').attr('src', `/adivinhacoes/${uuid}/respostas-iframe`);

      const modal = new bootstrap.Modal($('#modalRespostas')[0]);
      modal.show();
    });
  });
</script>
@endpush
