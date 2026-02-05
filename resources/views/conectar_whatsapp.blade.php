@extends('layouts.app')

@section('title', 'Conectar WhatsApp')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow border-0">
                <div class="card-header bg-dark text-white text-center">
                    <h3 class="mb-0">Conectar WhatsApp</h3>
                </div>
                <div class="card-body">
                    <p class="mb-4 text-secondary text-center">Conecte seu WhatsApp à plataforma. Se já estiver conectado, você verá o status abaixo.</p>
                    <div id="status-section" class="mb-4 text-center"></div>
                    <form id="whatsapp-connect-form" class="d-none">
                        <button type="submit" class="btn btn-success w-100 fw-bold">Gerar QR Code</button>
                    </form>
                    <div id="qrcode-section" class="mt-4 d-none text-center">
                        <h5 class="mb-3 text-dark">QR Code</h5>
                        <img id="qrcode-img" src="" alt="QR Code de conexão" class="border p-2 bg-light mx-auto">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', async function() {
        const base = '{{ env('NOTIFICACAO_API_BASE') }}';
        const tokenPath = '{{ env('NOTIFICACAO_API_TOKEN_PATH') }}';
        const statusSection = document.getElementById('status-section');
        const form = document.getElementById('whatsapp-connect-form');
        const qrcodeSection = document.getElementById('qrcode-section');
        let token = '';
        // 1. Gerar token
        if (window.location.protocol === 'https:' && base.startsWith('http://')) {
            statusSection.innerHTML = `<div class='alert alert-warning'>Atenção: Seu navegador pode bloquear requisições para API HTTP a partir de um site HTTPS. Considere usar HTTPS na API ou habilitar CORS e HTTPS no backend.</div>`;
        }
        try {
            const tokenRes = await fetch(`${base}${tokenPath}`, { method: 'POST' });
            const tokenData = await tokenRes.json();
            if (!tokenData.token) throw new Error('Token não recebido.');
            token = tokenData.token;
        } catch (err) {
            statusSection.innerHTML = `<div class='alert alert-danger'>Erro ao gerar token: ${err.message}</div>`;
            return;
        }
        // 2. Checar conexão
        try {
            const checkRes = await fetch(`${base}/check-connection-session`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const checkData = await checkRes.json();
            if (checkData.status === true || checkData.connected === true) {
                statusSection.innerHTML = `<div class='alert alert-success'>WhatsApp já está conectado!</div>`;
                form.classList.add('d-none');
                qrcodeSection.classList.add('d-none');
            } else {
                statusSection.innerHTML = `<div class='alert alert-warning'>WhatsApp não está conectado. Clique para gerar o QR Code.</div>`;
                form.classList.remove('d-none');
            }
        } catch (err) {
            statusSection.innerHTML = `<div class='alert alert-danger'>Erro ao checar conexão: ${err.message}</div>`;
        }
    });

    document.getElementById('whatsapp-connect-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const base = '{{ env('NOTIFICACAO_API_BASE') }}';
        const tokenPath = '{{ env('NOTIFICACAO_API_TOKEN_PATH') }}';
        const statusSection = document.getElementById('status-section');
        let token = '';
        // 1. Gerar token
        try {
            const tokenRes = await fetch(`${base}${tokenPath}`, { method: 'POST' });
            const tokenData = await tokenRes.json();
            if (!tokenData.token) throw new Error('Token não recebido.');
            token = tokenData.token;
        } catch (err) {
            statusSection.innerHTML = `<div class='alert alert-danger'>Erro ao gerar token: ${err.message}</div>`;
            return;
        }
        // 2. Logout sessão antes de gerar QR Code
        try {
            await fetch(`${base}/logout-session`, {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}` }
            });
            // Aguarda 1 segundo para garantir que a sessão foi deslogada
            await new Promise(resolve => setTimeout(resolve, 1000));
        } catch (err) {
            // Mesmo se falhar, tenta gerar o QR Code
        }
        // 3. Iniciar sessão e obter QR Code
        try {
            const startRes = await fetch(`${base}/start-session`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ waitQrCode: true })
            });
            if (!startRes.ok) throw new Error('Erro HTTP ao iniciar sessão');
            // Espera resposta JSON com qrcode
            const data = await startRes.json();
            if (!data.qrcode && !data.qrCode && !data.qr) throw new Error('QR Code não recebido.');
            let qr = data.qrcode || data.qrCode || data.qr;
            // Se vier base64, monta data url
            if (!qr.startsWith('data:image')) {
                qr = 'data:image/png;base64,' + qr;
            }
            document.getElementById('qrcode-img').src = qr;
            document.getElementById('qrcode-section').classList.remove('d-none');
            statusSection.innerHTML = `<div class='alert alert-success'>QR Code gerado com sucesso!</div>`;
        } catch (err) {
            statusSection.innerHTML = `<div class='alert alert-danger'>Erro ao obter QR Code: ${err.message}</div>`;
        }
    });
</script>
@endsection
