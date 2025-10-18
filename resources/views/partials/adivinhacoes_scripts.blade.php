<script>
  $(document).ready(function() {
    $('.verComentarios').on('click', async function() {
      const adivinhacaoId = $(this).data('id');
      const route = $(this).data('route');
      const $box = $(`#comentarios-${adivinhacaoId}`);
      const $list = $box.find('.comentarios-list');

      if ($box.hasClass('d-none')) {
        $box.removeClass('d-none animate__fadeOut').addClass('animate__fadeIn');

        try {
          const res = await fetch(`${route}?limit=5&offset=0`, {
            headers: {
              'Accept': 'application/json'
            }
          });
          const data = await res.json();

          if (data.comments && data.comments.length > 0) {
            let html = '';
            data.comments.forEach(c => {
              html += adicionarComentario(c);
            });
            $list.html(html);

            // Show "Ver Mais" button if there are more comments
            if (data.has_more) {
              $box.find('.load-more-comments').removeClass('d-none');
            }
          } else {
            // $list.html('<p class="text-muted">Nenhum comentário ainda. Seja o primeiro!</p>');
          }
        } catch (e) {
          $list.html('<p class="text-danger">Erro ao carregar comentários.</p>');
        }

      } else {
        $box.removeClass('animate__fadeIn').addClass('animate__fadeOut');
        setTimeout(() => $box.addClass('d-none'), 300);
      }
    });

    function adicionarComentario(comentario) {
      const foto = comentario.user_photo ?
        comentario.user_photo :
        `https://ui-avatars.com/api/?name=${encodeURIComponent(comentario.usuario)}&background=random`;

      return `
        <div class="d-flex align-items-start gap-2 mb-3 p-3 rounded-3 bg-white shadow-sm">
            <img src="${foto}" 
                 alt="${comentario.usuario}" 
                 class="rounded-circle shadow-sm" 
                 width="48" height="48" 
                 style="object-fit: cover;"
                 
                 >

            <div>
                <div class="fw-bold abrir-perfil" data-username="${comentario.usuario}">${comentario.usuario}</div>
                <div class="text-muted">${comentario.body}</div>
            </div>
        </div>
    `;
    }

    $('.sendComment').on('click', async function() {
      const adivinhacaoId = $(this).data('id');
      const route = $(this).data('route');
      const $input = $(`#comentario-input-${adivinhacaoId}`);
      const body = $input.val().trim();

      if (!body) return;

      $(this).attr('disabled', true)

      try {
        const res = await fetch(route, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            body
          })
        });

        $input.val('');
      } catch (e) {
        alert('Erro ao enviar comentário');
      } finally {
        $(this).attr('disabled', false)
      }
    });

    $('.sendResposta').on('click', async function() {
      const $btn = $(this);
      const $body = $btn.closest('.col-md-7');
      const id = $body.find('[name="adivinhacao_id"]').val();
      const $input = $(`#resposta-${id}`);
      const resposta = $input.val().trim();

      $body.find('.resposta-enviada, .text-danger').remove();

      if (!resposta) {
        $(`<div class="mt-2 text-danger fw-bold">Preencha seu palpite primeiro!</div>`).insertAfter($input);
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
        } else if (json.info) {
          $msg.addClass('text-warning').text(json.info);
          $btn.attr('disabled', false);
        } else {
          let codigoResposta = json.reply_code ? `<br><small class="text-white">Seu código de palpite: <strong>${json.reply_code}</strong></small>` : '';

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
                  : 'Você não possui mais palpites. Se quiser, pode <a href="{{ route('tentativas.comprar') }}" class="btn btn-sm btn-primary ms-2">comprar mais</a> 😞'
              }${codigoResposta}`);

            $('#palpites_adivinhacao_' + id).html(json.trys)
            $btn.attr('disabled', false);
          }
        }

        $msg.insertAfter($input);

      } catch (error) {
        Swal.fire('Erro', 'Erro ao enviar o palpite. Tente novamente!', 'error');
        $btn.attr('disabled', false);
      }
    });

    $('#btnCopiarLink').on('click', function() {
      const $btn = $(this);
      const $input = $('#linkIndicacao');
      $input[0].select();
      $input[0].setSelectionRange(0, 99999);

      navigator.clipboard.writeText($input.val()).then(() => {
        $btn.text('Copiado!').removeClass('btn-primary').addClass('btn-primary');
        setTimeout(() => {
          $btn.text('Copiar link').removeClass('btn-primary').addClass('btn-primary');
        }, 2000);
      }).catch(() => {
        Swal.fire('Erro', 'Não foi possível copiar o link. Por favor, tente manualmente.', 'error');
      });
    });
  });

  @auth
  $(document).on('click', '.verRespostas', async function() {
    const adivinhacaoId = $(this).attr('adivinhacao_id');

    const res = await fetch("{{ route('adivinhacoes.respostas_do_usuario') }}", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        adivinhacao_id: adivinhacaoId
      })
    });

    const respostas = await res.json();

    const tbody = $('#tbodyRespostas');
    tbody.empty();

    if (!respostas.length) {
      tbody.append(`<tr><td class="text-muted text-center">Nenhum palpite enviado ainda.</td></tr>`);
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


  document.querySelectorAll('.btn-like').forEach(button => {
      button.addEventListener('click', function() {
          const adivinhacaoId = this.dataset.id;
          const btn = this;
          const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

          fetch(`/adivinhacoes/${adivinhacaoId}/toggle-like`, {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': token
              },
              body: JSON.stringify({})
          })
          .then(res => res.json())
          .then(data => {
              // Atualiza count
              btn.querySelector('.likes-count').textContent = data.likes_count;

              // Alterna ícone e cor do botão
              const icon = btn.querySelector('i');
              if (data.liked) {
                  btn.classList.remove('btn-outline-primary');
                  btn.classList.add('btn-danger');
                  icon.classList.remove('bi-hand-thumbs-up');
                  icon.classList.add('bi-hand-thumbs-up-fill');
              } else {
                  btn.classList.remove('btn-danger');
                  btn.classList.add('btn-outline-primary');
                  icon.classList.remove('bi-hand-thumbs-up-fill');
                  icon.classList.add('bi-hand-thumbs-up');
              }
          })
          .catch(err => console.error(err));
      });
  });

  // Load more comments functionality
  $(document).on('click', '.load-more-comments', async function() {
    const $btn = $(this);
    const adivinhacaoId = $btn.data('id');
    const route = $btn.data('route');
    const offset = parseInt($btn.data('offset'));
    const $list = $(`#comentarios-${adivinhacaoId} .comentarios-list`);

    $btn.prop('disabled', true).text('Carregando...');

    try {
      const res = await fetch(`${route}?limit=5&offset=${offset}`, {
        headers: {
          'Accept': 'application/json'
        }
      });
      const data = await res.json();

      if (data.comments && data.comments.length > 0) {
        let html = '';
        data.comments.forEach(c => {
          html += adicionarComentario(c);
        });
        $list.append(html);

        // Update offset for next load
        $btn.data('offset', offset + 5);

        // Hide button if no more comments
        if (!data.has_more) {
          $btn.addClass('d-none');
        }
      }
    } catch (e) {
      console.error('Erro ao carregar mais comentários:', e);
    } finally {
      $btn.prop('disabled', false).text('Ver Mais Comentários');
    }
  });


</script>
