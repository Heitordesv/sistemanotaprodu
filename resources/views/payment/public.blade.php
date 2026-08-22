<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Seguro - Título #{{ $id }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --primary-color: #0d6efd; 
            --success-color: #198754; 
            --background-color: #f8f9fa; 
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--background-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .payment-card {
            max-width: 500px;
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            background-color: white;
        }
        .value-box {
            background-color: var(--primary-color);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 102, 255, 0.3);
        }
        .value-box .label {
            font-size: 1rem;
            opacity: 0.8;
            font-weight: 300;
        }
        .value-box .amount {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
        }
        .detail-item strong {
            font-weight: 500;
            color: #333;
        }
        .detail-item span {
            color: #6c757d;
        }
        /* Estilo para a área do PIX */
        #pixArea {
            border: 2px solid var(--success-color);
            background-color: #e9f7ee;
        }
        .pix-qr-code {
            max-width: 180px;
            border: 6px solid white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .copy-paste-container {
            border: 1px dashed #ced4da;
            background-color: white;
            cursor: pointer;
            transition: background-color 0.2s;
        }
    </style>
</head>
<body>

<div class="payment-card p-4 p-md-5" id="payment-page">

    <div id="error-area" class="alert alert-danger text-center fw-bold" style="display:none;"></div>

    <div id="content-area" style="display:none;">

        <h2 class="mb-4 text-center fw-bold text-dark" id="page-title">Pagamento do Título...</h2>

        <div class="value-box">
            <small class="d-block mb-2 label">Valor a Pagar</small>
            <span class="amount" id="conta-valor">R$ 0,00</span>
        </div>

        <h5 class="mb-3 text-secondary border-bottom pb-2 pt-3">Detalhes da Cobrança</h5>
        
        <ul class="list-group list-group-flush mb-4">
            <li class="list-group-item detail-item d-flex justify-content-between align-items-center px-0">
                <strong>Cliente:</strong> <span id="cliente-nome"></span>
            </li>
            <li class="list-group-item detail-item d-flex justify-content-between align-items-center px-0">
                <strong>Referência:</strong> <span id="conta-referencia"></span>
            </li>
            <li class="list-group-item detail-item d-flex justify-content-between align-items-center px-0">
                <strong>Observação:</strong> <span id="conta-observacao"></span>
            </li>
        </ul>

        <div class="d-grid mb-4" id="paymentButtons" style="display:none;">
            <button class="btn btn-primary btn-lg fw-bold d-flex align-items-center justify-content-center" id="btnPix" onclick="gerarPix({{ $id }})">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-qr-code-scan me-2" viewBox="0 0 16 16">
                    <path d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5M.5 12a.5.5 0 0 1 .5.5v3h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5M4 4h1v1H4zm1 1h1v1H5zM4 6h1v1H4zm6-2h1v1h-1zm1 1h1v1h-1zM9 4h1v1H9zm-5 7h1v1H4zm1 1h1v1H5zM4 13h1v1H4zM9 11h1v1H9zm1 1h1v1h-1zM9 13h1v1H9zm6-8v2h-2v-2zM4 9h2v2H4zm5-4h2v2H9zm-4 5h2v2H4zm5-4h2v2H9z"/>
                </svg>
                Pagar com PIX
            </button>
        </div>

        <div id="pixArea" class="text-center mt-5 p-4 rounded-3" style="display:none;">
            <p class="fw-bold mb-3 fs-5 text-success">💰 Escaneie para Pagar</p>
            
            <img id="pixQrCode" src="" alt="QR Code PIX" class="pix-qr-code rounded-3">
            
            <p class="mt-4 fw-bold mb-2">Ou use o Código Copia e Cola:</p>
            
            <div class="d-flex align-items-stretch justify-content-center">
                <span class="d-block text-break fw-bold small text-start p-2 rounded-start copy-paste-container" 
                      id="pix-copy-paste" 
                      style="user-select: all; max-width: 75%; flex-grow: 1;">
                </span>
                <button class="btn btn-outline-dark fw-bold btn-sm rounded-end" 
                        id="btnCopyPix" 
                        onclick="copiarPix()"
                        style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                    Copiar
                </button>
            </div>
            <small class="d-block mt-2 text-muted">Aguardando confirmação automática...</small>

        </div>

        <div id="successArea" class="text-center mt-5 p-4 rounded-3 bg-success text-white fw-bold" style="display:none;">
            <h4 class="mb-2">✅ Pagamento Aprovado!</h4>
            <p>Obrigado! Seu título foi liquidado com sucesso.</p>
            <small id="infoExtra" class="d-block opacity-75"></small>
        </div>

        <div id="expiredArea" class="text-center mt-5 p-4 rounded-3 bg-warning text-dark fw-bold" style="display:none;">
            <h4>⚠️ Título Expirado</h4>
            <p>O prazo para pagamento deste título foi encerrado. Por favor, entre em contato.</p>
        </div>

    </div>
</div>
<script>
    const CONTA_ID = {{ $id }};
    let intervaloStatus = null;
    const API_BASE_URL = "{{ url('/') }}"; 
    const APROVADO_TEXTO = "Pagamento aprovado via Mercado Pago"; 

    const DOM = {
        errorArea: document.getElementById('error-area'),
        contentArea: document.getElementById('content-area'),
        pageTitle: document.getElementById('page-title'),
        clienteNome: document.getElementById('cliente-nome'),
        contaValor: document.getElementById('conta-valor'),
        contaReferencia: document.getElementById('conta-referencia'),
        contaObservacao: document.getElementById('conta-observacao'),
        paymentButtons: document.getElementById('paymentButtons'),
        btnPix: document.getElementById('btnPix'),
        pixArea: document.getElementById('pixArea'),
        pixQrCode: document.getElementById('pixQrCode'),
        pixCopyPaste: document.getElementById('pix-copy-paste'),
        successArea: document.getElementById('successArea'),
        infoExtra: document.getElementById('infoExtra'),
        expiredArea: document.getElementById('expiredArea'),
    };

    document.addEventListener('DOMContentLoaded', () => {
        if (!CONTA_ID) {
            mostrarErro('ID da conta não fornecido.');
            return;
        }

        esconderAreasPagamento(); 

        carregarDados(CONTA_ID);
    });
    
    function esconderAreasPagamento() {
        DOM.paymentButtons.style.display = 'none';
        DOM.pixArea.style.display = 'none';
        DOM.successArea.style.display = 'none';
        DOM.expiredArea.style.display = 'none';
    }

    function mostrarErro(msg) {
        DOM.errorArea.innerHTML = msg;
        DOM.errorArea.style.display = 'block';
        DOM.contentArea.style.display = 'none'; 
    }
    
    function formatarMoeda(valor) {
        return 'R$ ' + parseFloat(valor).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function carregarDados(id) {
        const apiUrl = `${API_BASE_URL}/api/pg/${id}`;

        fetch(apiUrl)
            .then(r => r.json())
            .then(data => {
                if (data.erro) { mostrarErro(data.erro); return; }

                const conta = data.conta;
                const cliente = data.cliente;

                DOM.pageTitle.textContent = `Pagamento do Título #${conta.id}`;
                DOM.clienteNome.textContent = cliente.razao_social ?? cliente.nome_fantasia ?? 'Não informado';
                DOM.contaValor.textContent = formatarMoeda(conta.valor_integral);
                DOM.contaReferencia.textContent = conta.referencia ?? 'N/A';
                DOM.contaObservacao.textContent = conta.observacao ?? 'Nenhuma observação';

                DOM.contentArea.style.display = 'block';

                if (conta.observacao && conta.observacao.includes(APROVADO_TEXTO)) {
                    esconderAreasPagamento(); 
                    DOM.successArea.style.display = 'block'; 
                    DOM.infoExtra.textContent = `Status: ${conta.observacao}`;
                } else {
                    verificarStatusInicial(id);
                }
            })
            .catch(err => {
                console.error(err);
                mostrarErro('Erro ao carregar dados do título. Verifique a conexão.');
            });
    }

    function verificarStatusInicial(id) {
        fetch(`${API_BASE_URL}/payment/verificar-status/${id}`)
            .then(r => r.json())
            .then(res => {
                
                esconderAreasPagamento(); 

                if (res.status === 1 || res.status === 'approved') {
                    DOM.successArea.style.display = 'block';
                    DOM.infoExtra.textContent = `Recebido em ${res.data_recebimento ?? 'agora mesmo'}.`;
                } else if (res.status === 2 || res.status === 'expired') {
                    DOM.expiredArea.style.display = 'block';
                } else {
                    DOM.paymentButtons.style.display = 'grid';
                }
            })
            .catch(e => console.error('Erro ao verificar status inicial:', e));
    }

    function gerarPix(id) {
        Swal.fire({ 
            title: 'Processando...', 
            text: 'Verificando status do título e preparando o PIX.', 
            icon: 'info', 
            allowOutsideClick: false, 
            didOpen: () => Swal.showLoading() 
        });
        
        fetch(`${API_BASE_URL}/payment/gerar-pix/${id}`)
            .then(r => r.json())
            .then(res => {
                Swal.close();
                
                if (res.status_pago) {
                    esconderAreasPagamento(); 
                    DOM.successArea.style.display = 'block';
                    DOM.infoExtra.textContent = res.message;
                    
                    Swal.fire({
                        icon: 'warning',
                        title: 'Já Aprovado!',
                        text: res.message,
                        confirmButtonText: 'OK'
                    });
                    return; 
                }
                
                if (res.erro) {
                    Swal.fire('Erro na Geração', res.erro, 'error');
                    return;
                }

                if (res.qr_code_base64 && res.qr_code_text) {
                    esconderAreasPagamento(); 
                    DOM.pixArea.style.display = 'block'; 
                    
                    DOM.pixQrCode.src = 'data:image/png;base64,' + res.qr_code_base64;
                    DOM.pixCopyPaste.textContent = res.qr_code_text;

                    iniciarVerificacaoStatus(id);
                } else {
                    Swal.fire('Erro', 'Falha ao gerar PIX: O servidor não retornou os dados de QR Code esperados.', 'error');
                }
            })
            .catch(() => {
                Swal.close();
                Swal.fire('Erro de Comunicação', 'Não foi possível conectar ao servidor para gerar o PIX.', 'error');
            });
    }

    function iniciarVerificacaoStatus(id) {
        if (intervaloStatus) clearInterval(intervaloStatus);
        
        intervaloStatus = setInterval(() => {
            fetch(`${API_BASE_URL}/payment/verificar-status/${id}`)
                .then(r => r.json())
                .then(res => {
                    if (res.status === 1 || res.status === 'approved') {
                        clearInterval(intervaloStatus);
                        esconderAreasPagamento(); 
                        
                        DOM.successArea.style.display = 'block';
                        DOM.infoExtra.textContent = `Recebido em ${res.data_recebimento ?? 'agora mesmo'}.`;
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Pagamento confirmado!',
                            text: 'Seu pagamento foi aprovado com sucesso. Você pode fechar esta página.',
                            confirmButtonText: 'Feito'
                        });
                    }
                })
                .catch(e => console.error('Erro ao verificar status:', e));
        }, 10000); 
    }

    function copiarPix() {
        const pixCode = DOM.pixCopyPaste.textContent;
        if (!pixCode) { 
            Swal.fire('Atenção','Código PIX não disponível.','warning'); 
            return; 
        }
        navigator.clipboard.writeText(pixCode)
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Copiado!',
                    text: 'Código PIX copiado para a área de transferência.',
                    showConfirmButton: false,
                    timer: 1500
                });
                DOM.btnCopyPix.textContent = 'Copiado!';
                setTimeout(() => DOM.btnCopyPix.textContent = 'Copiar', 1500);
            })
            .catch(() => Swal.fire('success','Código PIX copiado para a área de transferência.','success'));
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>