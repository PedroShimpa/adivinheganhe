@extends('layouts.app')

@section('content')
<div class="container  ">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">🛒 Comprar Dica para - {{ $adivinhacao->titulo}}</h4>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form id="form-checkout" class="needs-validation" novalidate>
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Valor total</label>
                            <input type="text" class="form-control bg-light" id="valorTotal" value="{{ $adivinhacao->dica_valor}}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Número do Cartão</label>
                            <div id="form-checkout__cardNumber" class="form-control p-2"></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Validade</label>
                                <div id="form-checkout__expirationDate" class="form-control p-2"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Código de Segurança</label>
                                <div id="form-checkout__securityCode" class="form-control p-2"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nome no Cartão</label>
                            <input type="text" id="form-checkout__cardholderName" class="form-control" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-semibold">Bandeira</label>
                                <select id="form-checkout__issuer" class="form-select"></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Parcelas</label>
                                <select id="form-checkout__installments" class="form-select"></select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-semibold">Tipo de Documento</label>
                                <select id="form-checkout__identificationType" class="form-select"></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Número do Documento</label>
                                <input type="text" id="form-checkout__identificationNumber" class="form-control">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" id="form-checkout__cardholderEmail" class="form-control">
                        </div>

                        <button type="submit" id="form-checkout__submit" class="btn btn-primary w-100 py-2 fs-5">
                            💳 Confirmar Compra
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script src="https://sdk.mercadopago.com/js/v2"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const mp = new MercadoPago('{{ env("MERCADO_PAGO_PUBLIC_KEY") }}');

    const cardForm = mp.cardForm({
        amount: "{{ $adivinhacao->dica_valor}}",
        iframe: true,
        form: {
            id: "form-checkout",
            cardNumber: {
                id: "form-checkout__cardNumber",
                placeholder: "Número do cartão",
            },
            expirationDate: {
                id: "form-checkout__expirationDate",
                placeholder: "MM/YY",
            },
            securityCode: {
                id: "form-checkout__securityCode",
                placeholder: "Código de segurança",
            },
            cardholderName: {
                id: "form-checkout__cardholderName",
                placeholder: "Titular do cartão",
            },
            issuer: {
                id: "form-checkout__issuer",
                placeholder: "Banco emissor",
            },
            installments: {
                id: "form-checkout__installments",
                placeholder: "Parcelas",
            },
            identificationType: {
                id: "form-checkout__identificationType",
                placeholder: "Tipo de documento",
            },
            identificationNumber: {
                id: "form-checkout__identificationNumber",
                placeholder: "Número do documento",
            },
            cardholderEmail: {
                id: "form-checkout__cardholderEmail",
                placeholder: "E-mail",
            },
        },
        callbacks: {
            onFormMounted: error => {
                if (error) return console.warn("Form Mounted handling error: ", error);
                console.log("Form mounted");
            },
            onSubmit: event => {
                event.preventDefault();
                $('#form-checkout__submit').attr('disabled', true)
                const {

                    paymentMethodId: payment_method_id,
                    issuerId: issuer_id,
                    cardholderEmail: email,
                    amount,
                    token,
                    installments,
                    identificationNumber,
                    identificationType,
                } = cardForm.getCardFormData();

                fetch("{{ route('dicas.comprar', $adivinhacao->uuid) }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            token,
                            issuer_id,
                            payment_method_id,
                            transaction_amount: Number(amount),
                            installments: Number(installments),
                            description: "Compra de dica",
                            payer: {
                                email,
                                identification: {
                                    type: identificationType,
                                    number: identificationNumber,
                                },
                            },
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Pagamento aprovado!',
                                text: 'Sua dica foi adicionada, você será redirecionado para o inicio.',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = "{{ route('home') }}";
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
                    }).finally(() => $('#form-checkout__submit').attr('disabled', false));

            },
            onFetching: (resource) => {
                console.log("Fetching resource: ", resource);
            }
        },
    });
</script>

@endpush