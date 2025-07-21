@extends('layouts.app')

@section('content')
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
@endsection
@push('scripts')
<script>document.addEventListener('DOMContentLoaded', function() {
    let page = 1;
    let loading = false;
    const container = document.getElementById('respostas-container');

    // Função nomeada para poder remover depois
    function handleScroll() {
        if (loading) return;

        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 100) {
            page++;
            loadMore(page);
        }
    }

    window.addEventListener('scroll', handleScroll);

    function loadMore(page) {
        loading = true;

        const url = `{{ route('adivinhacoes.respostas', $adivinhacao->uuid) }}?page=${page}`;

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
                // Nenhum dado novo, para de escutar scroll
                window.removeEventListener('scroll', handleScroll);
                return;
            }
            container.insertAdjacentHTML('beforeend', data);
            loading = false;
        })
        .catch(error => {
            console.error(error);
            loading = false;
        });
    }
});

</script>
@endpush
