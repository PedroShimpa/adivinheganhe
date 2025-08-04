@if(!Auth::check())
<div class="alert alert-warning text-center rounded-0 mb-3 animate__animated animate__fadeInDown">
    <strong>🎯 Você precisa se registrar para jogar.</strong>
    <a href="{{ route('register') }}" class="text-decoration-underline text-primary">Clique aqui para registrar-se</a>
</div>
@endif

<div class="mb-4 p-3 glass text-center animate__animated animate__fadeInDown">
    <span class="d-inline-flex align-items-center justify-content-center gap-2 text-glow fs-5">
        <i class="bi bi-people-fill fs-4"></i>
        <span id="online-count">0</span> jogadores online agora
    </span>
</div>

@if(Auth::check())
<div class="mb-4 p-4 glass text-center animate__animated animate__fadeInUp">
    <h5 class="mb-3 text-glow">
        🎯 Indique e ganhe <strong>{{ env('INDICATION_ADICIONAL')}} tentativas</strong>
    </h5>

    <div class="input-group mb-3 mx-auto" style="max-width: 500px;">
        <input type="text" id="linkIndicacao" class="form-control rounded-start" value="{{ route('register', ['ib' => auth()->user()->uuid]) }}" readonly>
        <button class="btn btn-success text-dark" id="btnCopiarLink">Copiar link</button>
    </div>

    <p class="mb-2">
        <strong>🎮 Você tem {{ $trys }}</strong> tentativas.
        <a href="{{ route('tentativas.comprar') }}" class="btn btn-sm btn-success text-dark ms-2">Comprar mais</a>
    </p>
    <p class="small text-dark">🕓 Você recebe {{ env('MAX_ADIVINHATIONS')}} tentativas gratuitas todos os dias (não acumulativas).</p>
</div>
@endif

<div class="alert alert-success mt-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 p-3 shadow-sm border-start border-4 border-success rounded-4 animate__animated animate__fadeInUp">
    <div class="d-flex align-items-center gap-3 text-center text-md-start">
        <i class="bi bi-whatsapp fs-4 text-success"></i>
        <span class="fw-semibold">
            Entre na nossa <strong>comunidade do WhatsApp</strong> para ser avisado de novos jogos e votar nos nos prêmios.
        </span>
    </div>
    <a href="{{ env('WHATSAPP_COMUNITY_URL') }}" target="_blank" class="btn btn-success btn-sm rounded-pill px-4">
        Participar agora
    </a>
</div>

