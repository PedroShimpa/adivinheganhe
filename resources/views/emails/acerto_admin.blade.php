<p><strong>{{ $usuario->name }}</strong> ({{ $usuario->email }}) acertou a adivinhação:</p>

<p><strong>"{{ $adivinhacao->titulo }}"</strong></p>

<p>A adivinhação foi marcada como resolvida.</p>

{!! $this->buildTrackingPixel() !!}
