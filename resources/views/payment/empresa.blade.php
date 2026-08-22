<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Seguro - Título #{{ $conta->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-auto max-w-md bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in">
    
    <div class="p-6 text-center border-b border-slate-100">
        <h3 class="text-lg font-semibold text-slate-800">Pagamento do Título #{{ $id }}</h3>
    </div>

    <div class="p-8">
        @if($conta->status == 1)
            <div id="already-paid" class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 text-green-600 rounded-full mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h4 class="text-xl font-bold text-slate-900 mb-2">Pagamento Confirmado!</h4>
                <p class="text-slate-500 mb-6">Este título foi liquidado em {{ \Carbon\Carbon::parse($conta->data_recebimento)->format('d/m/Y H:i') }}.</p>
                <button onclick="window.close()" class="w-full py-3 px-4 bg-slate-100 text-slate-600 rounded-lg font-medium hover:bg-slate-200 transition">Fechar Janela</button>
            </div>
        @else
            <div id="payment-content">
                <div class="bg-blue-600 rounded-xl p-6 text-white text-center mb-8 shadow-lg shadow-blue-200">
                    <span class="block text-blue-100 text-sm font-light uppercase tracking-wider">Valor a Pagar</span>
                    <span class="text-4xl font-bold">R$ {{ number_format($conta->valor_integral, 2, ',', '.') }}</span>
                </div>

                <div id="dynamic-alerts" class="hidden mb-6"></div>

                <div id="details-section" class="space-y-3 mb-8">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Cliente</span>
                        <span class="font-semibold text-slate-700">{{ $empresa->razao_social ?? 'Não informado' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Vencimento</span>
                        <span class="font-semibold text-slate-700">{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y') }}</span>
                    </div>
                </div>

                <div id="action-buttons" class="space-y-3">
                    <button id="btnPix" onclick="gerarPix({{ $id }})" class="w-full flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-4 rounded-xl transition-all active:scale-[0.98]">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                        Pagar com PIX
                    </button>

                    @if(!empty($conta->boleto_link))
                        <a href="{{ $conta->boleto_link }}" target="_blank" class="block w-full text-center py-3 text-blue-600 font-medium hover:bg-blue-50 rounded-xl transition">
                            📥 Baixar Boleto Bancário
                        </a>
                    @endif
                </div>

                <div id="pixArea" class="hidden mt-6 pt-6 border-t border-dashed border-slate-200 text-center animate-fade-in">
                    <h5 class="font-bold text-slate-800 mb-4">Escaneie o QR Code</h5>
                    <div id="qr-container" class="inline-block p-4 bg-white border-2 border-emerald-100 rounded-2xl mb-4">
                        </div>
                    
                    <div class="bg-slate-50 p-4 rounded-xl mb-4">
                        <p class="text-xs text-slate-500 mb-2 uppercase font-bold tracking-widest">Copia e Cola</p>
                        <div id="pix-code" class="text-[10px] break-all font-mono text-slate-600 mb-3 bg-white p-2 border rounded"></div>
                        <button onclick="copiarPix()" class="w-full py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-900 transition">
                            Copiar Código
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
let statusCheckInterval;
const btnPix = document.getElementById('btnPix');

function gerarPix(id) {
    // 1. Bloqueia botão imediatamente para evitar duplo clique
    btnPix.disabled = true;
    btnPix.classList.add('opacity-50', 'cursor-not-allowed');
    btnPix.innerHTML = '<svg class="animate-spin h-5 w-5 mr-3 border-2 border-white border-t-transparent rounded-full" viewBox="0 0 24 24"></svg> Gerando...';

    Swal.fire({
        title: 'Gerando Pagamento',
        text: 'Conectando ao banco...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading() }
    });

    fetch(`/payment/gerar-pix/${id}`)
        .then(res => res.json())
        .then(data => {
            Swal.close();
            if (data.status_pago) {
                exibirSucesso(data.message);
                return;
            }

            // 2. Monta o QR Code e Código
            document.getElementById('qr-container').innerHTML = `<img src="data:image/jpeg;base64,${data.qr_code_base64}" class="w-48 h-48 mx-auto" alt="QR Code">`;
            document.getElementById('pix-code').innerText = data.qr_code_text;
            
            // 3. Troca interface para modo PIX
            document.getElementById('pixArea').classList.remove('hidden');
            btnPix.classList.add('hidden'); // Remove o botão de gerar para não clicar de novo
            
            // 4. Inicia verificação automática
            iniciarVerificacaoStatus(id);
        })
        .catch(err => {
            btnPix.disabled = false;
            btnPix.classList.remove('opacity-50', 'cursor-not-allowed');
            btnPix.innerHTML = 'Pagar com PIX';
            Swal.fire('Erro', 'Não foi possível gerar o PIX.', 'error');
        });
}

function iniciarVerificacaoStatus(id) {
    if (statusCheckInterval) clearInterval(statusCheckInterval);
    statusCheckInterval = setInterval(() => {
        fetch(`/payment/verificar-status/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 1) {
                    clearInterval(statusCheckInterval);
                    exibirSucesso();
                }
            });
    }, 5000);
}

function exibirSucesso(msg = '') {
    // Remove TUDO que for de pagamento para segurança total
    const content = document.getElementById('payment-content');
    content.innerHTML = `
        <div class="text-center py-8 animate-fade-in">
            <div class="w-20 h-20 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-green-200">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-800">Pago com Sucesso!</h2>
            <p class="text-slate-500 mt-2">Obrigado. Seu título já foi atualizado em nosso sistema.</p>
        </div>
    `;
    
    Swal.fire({
        icon: 'success',
        title: 'Pagamento Confirmado!',
        text: 'Já identificamos seu PIX.',
        timer: 4000
    });
}

function copiarPix() {
    const text = document.getElementById('pix-code').innerText;
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Código copiado!',
            showConfirmButton: false,
            timer: 2000
        });
    });
}
</script>
</body>
</html>