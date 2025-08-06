<script>

  $(document).ready(function () {
    $('.sendResposta').on('click', async function () {
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

      $btn.attr('disabled', true);

      try {
        const res = await fetch("{{ route('resposta.enviar') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
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
        } 
        else if(json.info) {
          $msg.addClass('text-warning').text(json.info);
          $btn.attr('disabled', false);  
        }
        else {
          let codigoResposta = json.reply_code ? `<br><small class="text-white">Seu código de resposta: <strong>${json.reply_code}</strong></small>` : '';

          if (json.message === 'acertou') {
            $msg
              .removeClass('text-danger')
              .addClass('text-success')
              .html(`🎉 Você acertou! Em breve notificaremos o envio do prêmio.${codigoResposta}`);
            $input.prop('disabled', true);
            $btn.prop('disabled', true);
          } else {
            $msg
              .removeClass('text-success fw-bold')
              .addClass('text-warning fw-bold')
              .html(`Que pena, você errou! ${
                json.trys > 0
                  ? 'Mas ainda possui ' + json.trys + ' tentativa' + (json.trys === 1 ? '' : 's')
                  : 'Você não possui mais tentativas. Se quiser, pode <a href="{{ route('tentativas.comprar') }}" class="btn btn-sm btn-primary ms-2">comprar mais</a> 😞'
              }${codigoResposta}`);
            $btn.attr('disabled', false);
          }
        }

        $msg.insertAfter($input);

      } catch (error) {
        Swal.fire('Erro', 'Erro ao enviar a resposta. Tente novamente!', 'error');
        $btn.attr('disabled', false);
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

  @auth
  $(document).on('click', '.verRespostas', async function () {
      const adivinhacaoId = $(this).attr('adivinhacao_id');

      const res = await fetch("{{ route('adivinhacoes.respostas_do_usuario') }}", {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
              'Accept': 'application/json'
          },
          body: JSON.stringify({ adivinhacao_id: adivinhacaoId })
      });

      const respostas = await res.json();

      const tbody = $('#tbodyRespostas');
      tbody.empty(); // limpa antes

      if (!respostas.length) {
          tbody.append(`<tr><td class="text-muted text-center">Nenhuma resposta enviada ainda.</td></tr>`);
      } else {
          respostas.forEach(resposta => {
              tbody.append(`<tr><td>${resposta.resposta}</td></tr>`);
          });
      }
  });
  @endauth



  $('.abrirModalInformacoes').click(function() {
    $('#modalLabel').html($(this).attr('titulo'));
    $('#modalDescricao').html($(this).attr('descricao'));
  });
</script>
