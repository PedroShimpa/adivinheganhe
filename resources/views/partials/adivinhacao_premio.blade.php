{{-- Botão de prêmio se for link --}}
@php $isLink = filter_var($adivinhacao->premio, FILTER_VALIDATE_URL); @endphp
@if($isLink)
    <div class="mt-3 text-end">
        <a href="{{ $adivinhacao->premio }}" class="btn btn-primary btn-sm rounded-pill" target="_blank">🎁 Ver prêmio</a>
    </div>
@endif
