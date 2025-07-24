@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">🛒 Comprar Tentativas</h4>
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

                        <div class="mb-3">
                            <label for="quantidade" class="form-label fw-semibold">Quantidade de tentativas</label>
                            <input type="number" name="quantidade" id="quantidade" class="form-control" min="{{ env('MIN_ATTEMPT_BUY', 10) }}" step="1" value="{{ env('MIN_ATTEMPT_BUY', 10)}}" required>
                            <div class="form-text">Mínimo de 10 tentativas. Cada uma custa R$ {{ env('PRICE_PER_ATTEMPT', 0.25)}}.</div>
                             <div class="form-text">
                                Tentivas compradas são cumulativas e nunca expiram.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Valor total</label>
                            <input type="text" class="form-control bg-light" id="valorTotal" value="R$ {{ env('PRICE_PER_ATTEMPT', 0.25) * env('MIN_ATTEMPT_BUY', 10)}}" readonly>
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

                        <button type="submit" id="form-checkout__submit" class="btn btn-success w-100 py-2 fs-5">
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

<script>
    const quantidadeInput = document.getElementById('quantidade');
    const valorTotalInput = document.getElementById('valorTotal');

    function calcularValorTotal() {
        const qtd = parseInt(quantidadeInput.value) ||"{{env('MIN_ATTEMPT_BUY', 10)}}";
        const valor = (qtd * "{{ env('PRICE_PER_ATTEMPT', 0.25)}}").toFixed(2).toString();
        valorTotalInput.value = `R$ ${valor.replace('.', ',')}`;
        return valor;
    }

    let valorAtual = calcularValorTotal();

    quantidadeInput.addEventListener('input', () => {
        valorAtual = calcularValorTotal();
        if (cardForm) {
              cardForm.update('amount', valorAtual);
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
                    }).finally(() =>   $('#form-checkout__submit').attr('disabled', false));

            },
            onFetching: (resource) => {
              
        
            }
        },
    });
</script>

@endpush