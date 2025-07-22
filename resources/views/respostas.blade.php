<div class="container py-5">
    <div class="text-center mb-4">
        <h1 class="fw-bold text-primary">Respostas da Adivinhação: {{ $adivinhacao->titulo }}</h1>
        <p class="text-muted">Confira abaixo quem respondeu e quando</p>
        <hr class="w-25 mx-auto">
    </div>

    <div class="card shadow rounded-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-primary ">
                        <tr>
                            <th class="text-center">Código</th>
                            <th>Usuário</th>
                            <th>Resposta</th>
                            <th class="text-center">Hora</th>
                        </tr>
                    </thead>
                    <tbody id="respostas-container">
                        @include('partials.respostas_table_rows', ['respostas' => $respostas])
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let page = 1;
    let loading = false;

    function setupScrollInfinite(modalBody, uuid) {
        const container = modalBody.querySelector('#respostas-container');
        if (!container) return;

        const scrollable = modalBody;
        if (!scrollable) return;

        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !loading) {
                page++;
                loadMore(page, container, uuid);
            }
        }, {
            root: scrollable,
            threshold: 1.0
        });

        const sentinel = document.createElement('div');
        sentinel.id = 'sentinel-scroll';
        container.after(sentinel);
        observer.observe(sentinel);
    }

    function loadMore(page, container, uuid) {
        loading = true;
        const url = `/adivinhacoes/${uuid}/respostas?page=${page}`;

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Erro ao carregar página');
            return response.text();
        })
        .then(data => {
            if (data.trim().length === 0) {
                return; // Sem mais dados
            }
            container.insertAdjacentHTML('beforeend', data);
            loading = false;
        })
        .catch(error => {
            console.error(error);
            loading = false;
        });
    }

    // Trigger AJAX load + scroll setup ao abrir modal
    document.querySelectorAll('.btn-ver-tentativas').forEach(btn => {
        btn.addEventListener('click', () => {
            const uuid = btn.dataset.uuid;
            const modalBody = document.getElementById('modalRespostasBody');
            page = 1;
            loading = false;

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
                    if (!content) throw new Error("Conteúdo inválido");

                    modalBody.innerHTML = content.innerHTML;
                    setupScrollInfinite(modalBody, uuid);
                })
                .catch(() => {
                    modalBody.innerHTML = '<div class="p-4 text-danger">Erro ao carregar respostas. Tente novamente mais tarde.</div>';
                });
        });
    });
});
</script>
