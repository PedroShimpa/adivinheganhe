<!-- Contador de usuários online -->
<div class="mb-4 p-3 bg-white border rounded-4 shadow-sm text-center">
    <span class="d-inline-flex align-items-center justify-content-center gap-2 text-secondary">
        <i class="bi bi-people-fill text-primary fs-5"></i>
        <span class="fw-semibold">
            <span id="online-count" class="text-primary fs-5">0</span> jogadores online agora
        </span>
    </span>
</div>

@if(Auth::check())
<div class="mb-4 p-4 bg-light rounded-4 shadow-sm text-center">
    <h5 class="mb-3 text-primary fw-semibold">
        🎯 Indique e ganhe <strong>5 tentativas</strong> por novo Adivinhador registrado no seu link!
    </h5>

    <div class="d-flex justify-content-center">
        <div class="input-group mb-3" style="max-width: 100%; width: 100%; max-width: 500px;">
            <input type="text" id="linkIndicacao" class="form-control text-truncate rounded-start" value="{{ route('register', ['ib' => auth()->user()->uuid]) }}" readonly>
            <button class="btn btn-outline-primary" id="btnCopiarLink">Copiar link</button>
        </div>
    </div>

    <p class="mb-2">
        <strong id="tentativas-restantes">🎮 Você tem {{ $trys }}</strong> tentativas.
        <a href="{{ route('tentativas.comprar') }}" class="btn btn-sm btn-primary ms-2 rounded-pill">Comprar mais</a>
    </p>
    <p class="small text-muted">🕓 Você recebe 10 tentativas gratuitas todos os dias (não acumulativas).</p>
</div>
@endif

<div class="alert alert-success mt-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 p-3 shadow-sm border-start border-4 border-success rounded-4">
    <div class="d-flex align-items-center gap-3 text-center text-md-start">
        <i class="bi bi-whatsapp fs-4 text-success"></i>
        <span class="fw-semibold">
            Entre na nossa <strong>comunidade do WhatsApp</strong> para ser avisado de novos jogos e gabaritos anteriores.
        </span>
    </div>
    <a href="{{ env('WHATSAPP_COMUNITY_URL') }}" target="_blank" class="btn btn-success btn-sm rounded-pill px-4">
        Participar agora
    </a>
</div>
