    @if(Auth::check())
    <div class="mb-4 p-3 bg-light rounded shadow-sm text-center">
        <h5 class="mb-2 text-primary fw-semibold">
            Indique e ganhe <strong>5 tentativas por novo Adivinhador registrado no seu link!</strong>
        </h5>
        <div class="input-group mb-3 mx-auto " style="max-width: 100%;">
            <input type="text" id="linkIndicacao" class="form-control text-truncate" style="max-width: 400px;"
                value="{{ route('register', ['ib' => auth()->user()->uuid]) }}" readonly>
            <button class="btn btn-outline-primary" id="btnCopiarLink">Copiar link</button>
        </div>
        <p class="mb-1"><strong id="tentativas-restantes">Restam {{ $trys }}</strong> tentativas.
            <a href="{{ route('tentativas.comprar') }}" class="btn btn-sm btn-primary ms-2">Comprar mais</a>
        </p>
        <p class="small">Você tem 10 tentativas (não acumulativas) gratuitas todos os dias!</p>
    </div>
    @endif

    <div class="alert alert-success mt-4 d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 shadow-sm border-start border-4 border-success">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-whatsapp fs-4 text-success"></i>
            <span class="fw-semibold">
                Entre em nossa comunidade do WhatsApp para saber quando novos jogos surgirem e os gabaritos dos jogos passados.
            </span>
        </div>
        <a href="{{ env('WHATSAPP_COMUNITY_URL') }}" target="_blank" class="btn btn-success btn-sm px-3">
            Participar agora
        </a>
    </div>