<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ env('GOOGLE_ADSENSE_TAG')}}"
        crossorigin="anonymous"></script>
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.11.1/dist/echo.iife.js"></script>

<script>
  let tentativas = parseInt($('#tentativas-restantes').text().replace(/\D/g, ''));

  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const EchoCtor = window.Echo;

  window.Echo = new EchoCtor({
    broadcaster: 'pusher',
    key: '{{ env("REVERB_APP_KEY") }}',
    wsHost: '{{ env("VITE_REVERB_HOST", "localhost") }}',
    wsPort: '{{ env("VITE_REVERB_PORT", 8080) }}',
    forceTLS: false,
    disableStats: true,
    authEndpoint: '/broadcasting/auth-mixed', // <- NOVO ENDPOINT
    auth: {
      headers: { 'X-CSRF-TOKEN': csrfToken }
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
      const id = e.adivinhacaoId;
      if(e.contagem && e.contagem >0) {
        
      }
      $(`#count-respostas-${id}`).html(e.contagem);
    })
    .listen('.alerta.global', e => {
      Swal.fire(e.titulo, e.msg, e.tipo)
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

      $btn.attr('disabled', true)
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
        $btn.attr('disabled', false);
      } else {
        let codigoResposta = json.code ? `<br><small class="text-muted">Seu código de resposta: <strong>${json.code}</strong></small>` : '';

        if (json.status === 'acertou') {
          $msg
            .removeClass('text-danger')
            .addClass('text-success')
            .html(`🎉 Você acertou! Em breve notificaremos o envio do prêmio.${codigoResposta}`);
          $input.prop('disabled', true);
          $btn.prop('disabled', true);
        } else {
          $msg
            .removeClass('text-success')
            .addClass('text-danger')
            .html(`Que pena, você errou! ${
              tentativas > 0
                ? 'Mas ainda possui ' + tentativas + ' tentativa' + (tentativas === 1 ? '' : 's')
                : 'Você não possui mais tentativas. Se quiser, pode <a href="{{ route('tentativas.comprar') }}" class="btn btn-sm btn-primary ms-2">comprar mais</a> 😞'
            }${codigoResposta}`);
          $btn.attr('disabled', false);
        }
      }
      $msg.insertAfter($input);

      } catch (error) {
        Swal.fire('Erro', 'Erro ao enviar a resposta. Tente novamente!', 'error');
            $btn.attr('disabled', false)

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
  });

    window.Echo.join('presence') .here(users => updateCount(users.length))
    .joining(user => updateCount(online + 1))
    .leaving(user => updateCount(Math.max(online - 1, 0)))
</script>