<div class="card mb-4 p-3 shadow-5 rounded-4 overflow-hidden">
    <div class="row g-0 flex-wrap">
        {{-- Coluna do arquivo --}}
        @include('partials.adivinhacao_media')

        {{-- Coluna de conteúdo --}}
        <div class="col-12 col-md-7 p-4 d-flex flex-column justify-content-between">
            <div>
                @include('partials.adivinhacao_header')
                @include('partials.adivinhacao_dica')
                @include('partials.adivinhacao_actions')
                @include('partials.adivinhacao_social')
            </div>

            @include('partials.adivinhacao_premio')
        </div>
    </div>
</div>
