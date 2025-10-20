{{-- Dica --}}
@if(!empty($adivinhacao->dica) && ($adivinhacao->resolvida != 'S'))
    @if($adivinhacao->dica_paga == 'S')
        @if(!$adivinhacao->buyed)
            <div class="alert alert-warning d-flex align-items-center justify-content-between">
                <div>
                    <strong>Dica disponível:</strong> Esta dica custa <strong>R${{ number_format($adivinhacao->dica_valor, 2, ',', '.') }}</strong>
                </div>
                <a href="{{ route('dicas.index_buy', $adivinhacao->uuid) }}" class="btn btn-sm btn-primary rounded-pill">Comprar dica</a>
            </div>
        @else
            <div class="alert alert-info rounded-3">
                <strong>Dica:</strong> {{ $adivinhacao->dica }}
            </div>
        @endif
    @else
        <div class="alert alert-info rounded-3">
            <strong>Dica:</strong> {{ $adivinhacao->dica }}
        </div>
    @endif
@endif
