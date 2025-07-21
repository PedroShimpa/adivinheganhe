@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">🛒 Comprar Tentativas</h4>
                </div>
                <div class="card-body">
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
                    <style>
                        #form-checkout {
                            display: flex;
                            flex-direction: column;
                            max-width: 600px;
                        }

                        .container {
                            height: 18px;
                            display: inline-block;
                            border: 1px solid rgb(118, 118, 118);
                            border-radius: 2px;
                            padding: 1px 2px;
                        }
                    </style>
                    <form id="form-checkout">
                        @csrf

                        <div class="mb-3">
                            <label for="quantidade" class="form-label">Quantidade de tentativas</label>
                            <input type="number" name="quantidade" id="quantidade" class="form-control" min="10" step="1" value="10" required>
                            <div class="form-text">Mínimo de 10 tentativas. Cada uma custa R$ 0,10.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Valor total</label>
                            <input type="text" class="form-control" id="valorTotal" value="R$ 1,00" readonly>
                        </div>

                        <div id="form-checkout__cardNumber" class="container"></div>
                        <div id="form-checkout__expirationDate" class="container"></div>
                        <div id="form-checkout__securityCode" class="container"></div>
                        <input type="text" id="form-checkout__cardholderName" />
                        <select id="form-checkout__issuer"></select>
                        <select id="form-checkout__installments"></select>
                        <select id="form-checkout__identificationType"></select>
                        <input type="text" id="form-checkout__identificationNumber" />
                        <input type="email" id="form-checkout__cardholderEmail" />
                        <button type="submit" id="form-checkout__submit" class="btn btn-success w-100 py-2 fs-5">
                            💳 Confirmar Compra
                        </button>
                        <button type="submit" id="form-checkout__submit">Pagar</button>
                        <progress value="0" class="progress-bar">Carregando...</progress>
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
    const quantidadeInput = document.getElementById('quantidade');
    const valorTotalInput = document.getElementById('valorTotal');

    function calcularValorTotal() {
        const qtd = parseInt(quantidadeInput.value) || 10;
        const valor = (qtd * 0.10).toFixed(2);
        valorTotalInput.value = `R$ ${valor.replace('.', ',')}`;
        return valor;
    }

    let valorAtual = calcularValorTotal();

    quantidadeInput.addEventListener('input', () => {
        valorAtual = calcularValorTotal();
        if (cardFormInstance) {
            cardFormInstance.update({
                amount: valorAtual
            });
        }
    });

    const mp = new MercadoPago('{{ env("MERCADO_PAGO_PUBLIC_KEY") }}');

    const cardForm = mp.cardForm({
        amount: valorAtual,
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

                fetch("{{ route('tentativas.comprar') }}", {
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
                            description: "Compra de tentativas",
                            payer: {
                                email,
                                identification: {
                                    type: identificationType,
                                    number: identificationNumber,
                                },
                            },
                            quantidade: document.getElementById("quantidade").value
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Pagamento aprovado!',
                                text: 'Suas tentativas foram creditadas com sucesso.',
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
                    });

            },
            onFetching: (resource) => {
                console.log("Fetching resource: ", resource);

                // Animate progress bar
                const progressBar = document.querySelector(".progress-bar");
                progressBar.removeAttribute("value");

                return () => {
                    progressBar.setAttribute("value", "0");
                };
            }
        },
    });
</script>

@endpush