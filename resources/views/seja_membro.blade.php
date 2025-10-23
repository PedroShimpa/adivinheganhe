@extends('layouts.app')

@section('title', 'Seja Membro VIP')

@section('content')
<div class="container-fluid py-5 mb-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-white mb-3">Seja Membro VIP</h1>
                <p class="lead text-light">Desbloqueie recursos exclusivos e tenha uma experiência premium</p>
            </div>

            <div class="card bg-dark border-secondary shadow-lg">
                <div class="card-header bg-gradient text-white text-center py-4">
                    <h2 class="h3 mb-2">Plano VIP Mensal</h2>
                    <div class="display-4 fw-bold mb-2">R$ {{ config('app.membership_value') }}</div>
                    <p class="text-light">por mês</p>
                </div>

                <div class="card-body p-4">
                    <div class="mb-4">
                        <h3 class="h4 text-white mb-4">Benefícios VIP</h3>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex align-items-center text-light">
                                    <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                                    <span>7 tentativas por adivinhação (vs 3 para não-VIPs)</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center text-light">
                                    <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                                    <span>Acesso antecipado a adivinhações VIP</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center text-light">
                                    <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                                    <span>Badge VIP no perfil e chats</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center text-light">
                                    <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                                    <span>Adivinhações exclusivas para membros</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center text-light">
                                    <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                                    <span>Suporte prioritário via whatsapp</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center text-light">
                                    <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                                    <span>Grupo excluisvo de VIPS no whatsapp </span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center text-light">
                                    <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                                    <span>Prioridade no recebimento de prêmios (dentro do horário comercial)</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center text-light">
                                    <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                                    <span>Sorteios mensais exclusivos para membros</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center text-light">
                                    <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                                    <span>Acesso ao adivinhe o milhão para sempre</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center text-light">
                                    <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                                    <span>Sem anúncios</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(auth()->check() && auth()->user()->isVip())
                        <div class="alert alert-success border-success" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-3 fs-4"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">Você já é um membro VIP!</h5>
                                    <p class="mb-0">Sua assinatura expira em {{ auth()->user()->membership_expires_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center mb-4">
                            <button id="checkout-button" class="btn btn-primary btn-lg px-5 py-3 fw-bold me-3 mb-2">
                                <i class="bi bi-credit-card me-2"></i>
                                Cartão de Crédito - R$ {{ config('app.membership_value') }}
                            </button>
                            <button id="mercadopago-button" class="btn btn-success btn-lg px-5 py-3 fw-bold mb-2">
                                <i class="bi bi-wallet me-2"></i>
                                PIX - R$ {{ config('app.membership_value') }}
                            </button>
                            <p class="text-white mt-3 small">Pagamento seguro • Cancele a qualquer momento</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(!auth()->check() || !auth()->user()->isVip())
<script src="https://js.stripe.com/v3/"></script>
<script src="https://sdk.mercadopago.com/js/v2"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Stripe integration
    const stripe = Stripe('{{ config("services.stripe.key") }}');
    const checkoutButton = document.getElementById('checkout-button');

    checkoutButton.addEventListener('click', function() {
        checkoutButton.disabled = true;
        checkoutButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processando...';

        fetch('{{ route("membership.checkout") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({})
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta do servidor: ' + response.status);
            }
            return response.json();
        })
        .then(session => {
            if (session.error) {
                throw new Error(session.error);
            }
            return stripe.redirectToCheckout({ sessionId: session.id });
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocorreu um erro ao processar o pagamento: ' + error.message + '. Tente novamente.');
            checkoutButton.disabled = false;
            checkoutButton.innerHTML = '<i class="bi bi-credit-card me-2"></i>Stripe - R$ {{ config("app.membership_value") }}/mês';
        });
    });

    // Mercado Pago PIX integration
    const mercadopagoButton = document.getElementById('mercadopago-button');

    mercadopagoButton.addEventListener('click', function() {
        mercadopagoButton.disabled = true;
        mercadopagoButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processando...';

        fetch("{{ route('membership.buy_vip') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({}),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show QR code modal
                Swal.fire({
                    title: 'Pague com PIX',
                    html: `
                        <div class="text-center">
                            <p>Escaneie o QR code abaixo para pagar:</p>
                            <img src="data:image/png;base64,${data.qr_code_base64}" alt="QR Code PIX" class="img-fluid mb-3" style="max-width: 200px;">
                            <p class="small text-muted">Ou copie o código PIX:</p>
                            <textarea class="form-control" readonly rows="3">${data.qr_code}</textarea>
                        </div>
                    `,
                    showConfirmButton: false,
                    showCloseButton: true,
                    allowOutsideClick: false,
                    didOpen: () => {
                        // Check payment status every 5 seconds
                        const checkInterval = setInterval(() => {
                            fetch("{{ route('membership.check_payment_status') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                },
                                body: JSON.stringify({ payment_id: data.payment_id }),
                            })
                            .then(response => response.json())
                            .then(statusData => {
                                if (statusData.status === 'approved') {
                                    clearInterval(checkInterval);
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Pagamento aprovado!',
                                        text: 'Bem-vindo ao clube VIP!',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                }
                            })
                            .catch(error => {
                                console.error("Erro ao verificar status:", error);
                            });
                        }, 5000);
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro no pagamento',
                    text: 'Não foi possível processar seu pagamento. Tente novamente mais tarde.',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error("Erro ao processar pagamento:", error);
            Swal.fire({
                icon: 'error',
                title: 'Erro inesperado',
                text: 'Tente novamente mais tarde.',
                confirmButtonText: 'OK'
            });
        }).finally(() => {
            mercadopagoButton.disabled = false;
            mercadopagoButton.innerHTML = '<i class="bi bi-wallet me-2"></i>PIX - R$ {{ config("app.membership_value") }}/mês';
        });
    });
});
</script>
@endif
@endsection
