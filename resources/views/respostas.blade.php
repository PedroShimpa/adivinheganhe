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
            <div class="mt-3">
                {{ $respostas->links() }}
            </div>
        </div>

    </div>
</div>