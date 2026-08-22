<style>
    .pix-safe{max-width:780px;margin:36px auto 70px;padding:0 16px;color:#0f172a}
    .pix-safe-card{background:#fff;border:1px solid #e5e7eb;border-radius:22px;padding:28px;text-align:center;box-shadow:0 10px 30px rgba(15,23,42,.07)}
    .pix-safe-headline{font-size:27px;margin:9px 0 4px;font-weight:900;color:#111827}
    .pix-safe-sub{color:#64748b;line-height:1.55;margin:0 auto;max-width:560px}
    .pix-safe-total{font-size:32px;font-weight:900;margin:12px 0;color:#111827}
    .pix-safe-qr-wrap{width:290px;max-width:90%;margin:22px auto;padding:16px;border-radius:18px;background:#f8fafc;border:1px solid #e2e8f0}
    .pix-safe-qr{width:100%;display:block;border-radius:10px}
    .pix-safe-btn{border:0;border-radius:12px;padding:14px 18px;background:var(--main-color);color:#fff!important;font-weight:900;cursor:pointer;width:100%;max-width:440px;text-decoration:none!important;display:inline-flex;align-items:center;justify-content:center;gap:8px;transition:.2s ease}
    .pix-safe-btn:hover{filter:brightness(.93)}
    .pix-safe-btn.secondary{background:#fff;color:#334155!important;border:1px solid #cbd5e1}
    .pix-safe-status{margin:18px auto 0;max-width:560px;padding:13px 14px;border-radius:12px;background:#fff7ed;color:#9a3412;border:1px solid #fed7aa;font-weight:700;font-size:13px;line-height:1.45}
    .pix-safe-help{margin-top:14px;color:#64748b;font-size:12px;line-height:1.5}
    .pix-safe-actions{display:flex;justify-content:center;flex-wrap:wrap;gap:10px;margin-top:20px}
    .pix-safe-actions .pix-safe-btn{max-width:260px}
    .pix-safe-manual{display:none;margin-top:12px}
    .pix-safe-kicker{font-size:12px;text-transform:uppercase;letter-spacing:.12em;color:#64748b;font-weight:900}
    @media(max-width:640px){.pix-safe-card{padding:22px}.pix-safe-headline{font-size:24px}.pix-safe-actions .pix-safe-btn{max-width:none}.pix-safe-qr-wrap{width:260px}}
</style>

<div class="pix-safe">
    <div class="pix-safe-card">
        <div class="pix-safe-kicker">Pedido #{{ $pedido->id }}</div>
        <h2 class="pix-safe-headline">Pague com PIX</h2>
        <div class="pix-safe-total">R$ {{ number_format((float)$pedido->valor_total, 2, ',', '.') }}</div>
        <p class="pix-safe-sub">Abra o aplicativo do seu banco, escaneie o QR Code ou copie o código PIX. A confirmação será identificada automaticamente.</p>

        @if($pedido_pix->qr_code_base64)
            <div class="pix-safe-qr-wrap">
                <img class="pix-safe-qr" src="data:image/png;base64,{{ $pedido_pix->qr_code_base64 }}" alt="QR Code PIX do pedido #{{ $pedido->id }}">
            </div>
            <input type="hidden" id="pix-safe-code" value="{{ $pedido_pix->qr_code }}">
            <button type="button" id="pix-safe-copy" class="pix-safe-btn">Copiar código PIX</button>
        @else
            <div class="pix-safe-status" style="background:#fef2f2;color:#991b1b;border-color:#fecaca">O Mercado Pago não retornou o QR Code. Volte ao pagamento e gere uma nova tentativa.</div>
        @endif

        <div id="pix-safe-status" class="pix-safe-status" aria-live="polite">Aguardando confirmação do pagamento...</div>
        <div id="pix-safe-manual" class="pix-safe-manual">
            <button type="button" id="pix-safe-refresh" class="pix-safe-btn secondary">Atualizar status</button>
            <div class="pix-safe-help">A atualização automática foi pausada para economizar recursos. Você pode verificar o pagamento manualmente.</div>
        </div>

        <div class="pix-safe-actions">
            <a href="{{ $rota }}/pedido_detalhe/{{ $pedido->id }}" class="pix-safe-btn secondary">Ver meu pedido</a>
            <a href="{{ $rota }}/pagamento" class="pix-safe-btn secondary">Outra forma de pagamento</a>
        </div>
    </div>
</div>

<script>
(function(){
    const copyButton = document.getElementById('pix-safe-copy');
    const code = document.getElementById('pix-safe-code');
    const statusBox = document.getElementById('pix-safe-status');
    const manualBox = document.getElementById('pix-safe-manual');
    const refreshButton = document.getElementById('pix-safe-refresh');
    const statusUrl = @json(route('ecommerce.secure.status', ['link' => $link, 'pedidoId' => $pedido->id]));
    const finalUrl = @json(route('ecommerce.secure.finalizado', ['link' => $link, 'hash' => $pedido->hash]));
    let attempts = 0;
    let checking = false;
    let timer = null;

    if(copyButton && code){
        copyButton.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(code.value);
                copyButton.textContent = 'Código copiado!';
                setTimeout(() => copyButton.textContent = 'Copiar código PIX', 2200);
            } catch(e) {
                code.type = 'text';
                code.select();
                document.execCommand('copy');
                code.type = 'hidden';
                copyButton.textContent = 'Código copiado!';
                setTimeout(() => copyButton.textContent = 'Copiar código PIX', 2200);
            }
        });
    }

    function setStatus(type, text){
        statusBox.textContent = text;
        if(type === 'ok'){
            statusBox.style.background = '#ecfdf5';
            statusBox.style.color = '#065f46';
            statusBox.style.borderColor = '#a7f3d0';
        }else if(type === 'bad'){
            statusBox.style.background = '#fef2f2';
            statusBox.style.color = '#991b1b';
            statusBox.style.borderColor = '#fecaca';
        }else{
            statusBox.style.background = '#fff7ed';
            statusBox.style.color = '#9a3412';
            statusBox.style.borderColor = '#fed7aa';
        }
    }

    async function checkStatus(manual = false){
        if(checking) return;
        checking = true;
        if(refreshButton && manual){ refreshButton.disabled = true; refreshButton.textContent = 'Atualizando...'; }
        try {
            const response = await fetch(statusUrl, {headers:{'Accept':'application/json'}});
            const data = await response.json();
            const status = String(data.status || '').toLowerCase();

            if(status === 'approved'){
                if(timer) clearInterval(timer);
                setStatus('ok', 'Pagamento aprovado! Abrindo o acompanhamento do seu pedido...');
                setTimeout(() => window.location.href = finalUrl, 900);
                return;
            }

            if(['rejected','cancelled'].includes(status)){
                if(timer) clearInterval(timer);
                setStatus('bad', 'Pagamento não aprovado. Escolha outra forma de pagamento para continuar.');
                if(manualBox) manualBox.style.display = 'none';
                return;
            }

            if(manual) setStatus('wait', 'O pagamento ainda não foi confirmado. Se você acabou de pagar, aguarde alguns instantes e tente novamente.');
        } catch(e) {
            if(manual) setStatus('bad', 'Não foi possível consultar o pagamento agora. Tente novamente em alguns instantes.');
        } finally {
            checking = false;
            if(refreshButton && manual){ refreshButton.disabled = false; refreshButton.textContent = 'Atualizar status'; }
        }
    }

    if(refreshButton) refreshButton.addEventListener('click', () => checkStatus(true));

    timer = setInterval(async () => {
        attempts++;
        await checkStatus(false);
        if(attempts >= 60){
            clearInterval(timer);
            timer = null;
            setStatus('wait', 'Ainda aguardando o pagamento. Use o botão abaixo para atualizar quando desejar.');
            if(manualBox) manualBox.style.display = 'block';
        }
    }, 5000);
})();
</script>