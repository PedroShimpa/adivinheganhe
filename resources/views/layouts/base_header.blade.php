@if(!Auth::check())
<div class="alert alert-warning text-center rounded-0 mb-3 animate__animated animate__fadeInDown">
    <strong>🎯 Você precisa se registrar para jogar.</strong>
    <a href="{{ route('register') }}" class="text-decoration-underline text-primary">Clique aqui para registrar-se</a>
</div>
@endif

@if(Auth::check() && !auth()->user()->whatsapp)
<div class="alert alert-warning text-center rounded-0 mb-3 animate__animated animate__fadeInDown">
    <strong>📱 Você ainda não cadastrou seu WhatsApp!</strong><br>
    Você precisa dele cadastrado para receber os prêmios em caso de acerto.<br>
    Cadastre agora mesmo:
    <a href="{{ route('profile.edit') }}" class="text-decoration-underline fw-bold">
        {{ auth()->user()->name }} → Perfil
    </a>
</div>
@endif


@if(Auth::check())
<div class="mb-4 p-4 card text-center animate__animated animate__fadeInUp">
    <h5 class="mb-3 ">
        🎯 Indique e ganhe <strong>{{ env('INDICATION_ADICIONAL')}} palpites</strong>
    </h5>

    <div class="input-group mb-3 mx-auto" style="max-width: 500px;">
        <input type="text" id="linkIndicacao" class="form-control rounded-start" value="{{ route('register', ['ib' => auth()->user()->uuid]) }}" readonly>
        <button class="btn btn-primary text-white" id="btnCopiarLink">Copiar link</button>
    </div>

    <p class="mb-2">
        <strong>🎮 Você tem <span id="trysRestantes">{{ $trys }} </span> </strong> palpites.
        <a href="{{ route('tentativas.comprar') }}" class="btn btn-sm btn-primary text-white ms-2">Comprar mais</a>
    </p>
    <p class="small text-dark">🕓 Você recebe {{ env('MAX_ADIVINHATIONS')}} palpites gratuitos todos os dias (não acumulativos).</p>
</div>
@endif

<div class="alert alert-success mt-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 p-3 shadow-sm border-start border-4 border-success rounded-4 animate__animated animate__fadeInUp">
    <div class="d-flex align-items-center gap-3 text-center text-md-start">
        <i class="bi bi-whatsapp fs-4 text-success"></i>
        <span class="fw-semibold">
            Entre na nossa <strong>comunidade do WhatsApp!</strong>
        </span>
    </div>
    <a href="{{ env('WHATSAPP_COMUNITY_URL') }}" target="_blank" class="btn btn-primary btn-sm rounded-pill px-4">
        Participar agora
    </a>
</div>