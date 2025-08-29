@extends('layouts.app', ['enable_adsense' => false])

@section('content')
<div class="container mb-5 mt-2" style="max-width: 900px;">

    <h3 class="fw-bold mb-4 text-white">Pedidos de amizade</h3>

    @if($pendingRequests->isEmpty())
        <div class="alert alert-secondary text-center">
            Nenhum pedido de amizade pendente.
        </div>
    @else
        <div class="row g-3">
            @foreach($pendingRequests as $request)
            <div class="col-12 col-sm-6">
                <div class="card shadow-sm bg-dark border-0 rounded-4 p-3 d-flex flex-column flex-sm-row align-items-center justify-content-between hover-glow">
                    
                    <div class="d-flex align-items-center gap-3 mb-3 mb-sm-0 w-100">
                        <img src="{{ $request->sender->image ?? 'https://ui-avatars.com/api/?name='.urlencode($request->sender->username).'&background=random' }}" 
                             alt="{{ $request->sender->name }}" 
                             class="rounded-circle border border-2 border-white" 
                             width="60" height="60" style="object-fit: cover;">
                        <div class="text-truncate">
                            <h6 class="mb-1 text-white text-truncate">{{ $request->sender->name }}</h6>
                            <p class="text-white mb-0 text-truncate">{{ '@'.$request->sender->username }}</p>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-sm-end w-100">
                        <button class="btn btn-sm btn-success acceptRequest" data-id="{{ $request->sender->id }}">
                            <i class="bi bi-check2"></i> Aceitar
                        </button>
                        <button class="btn btn-sm btn-danger recuseRequest" data-id="{{ $request->sender->id }}">
                            <i class="bi bi-x-lg"></i> Recusar
                        </button>
                    </div>

                </div>
            </div>
            @endforeach
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {

    async function handleRequest(btnClass, urlBase) {
        $('.' + btnClass).on('click', async function() {
            const $btn = $(this);
            const userId = $btn.data('id');

            $btn.prop('disabled', true);
            $btn.siblings('button').prop('disabled', true);

            try {
                const res = await fetch(`${urlBase}/${userId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                });

                if (res.ok) {
                    $btn.closest('.card').fadeOut(300, function() { $(this).remove(); });
                } else {
                    alert('Erro ao processar pedido.');
                    $btn.prop('disabled', false);
                    $btn.siblings('button').prop('disabled', false);
                }
            } catch (e) {
                console.error(e);
                alert('Erro ao processar pedido.');
                $btn.prop('disabled', false);
                $btn.siblings('button').prop('disabled', false);
            }
        });
    }

    handleRequest('acceptRequest', '/users/friend-request/accept');
    handleRequest('recuseRequest', '/users/friend-request/recuse');

});
</script>
@endpush
