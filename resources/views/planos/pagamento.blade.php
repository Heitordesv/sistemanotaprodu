<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escolha seu Plano</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        /* Estilo para garantir que a imagem do PIX no modal seja fácil de ler */
        .qrCodeImg-modal {
            max-width: 250px;
            height: auto;
            border: 1px solid #eee;
            padding: 5px;
            display: block; /* Garante que 'mx-auto' funcione */
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>

<div class="container my-5">
    <h2 class="mb-4 text-center">Escolha seu Plano - {{ $empresa->nome_fantasia ?? $empresa->razao_social }}</h2>

    <div class="row">
        @foreach($planos as $plano)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm p-3 d-flex flex-column">
                    @if($plano->img)
                        <img src="{{ asset('storage/planos/' . $plano->img) }}" class="card-img-top" alt="{{ $plano->nome }}">
                    @endif
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $plano->nome }}</h5>
                        <p class="card-text">{{ $plano->descricao }}</p>
                        <p class="card-text"><strong>Valor:</strong> R$ {{ number_format($plano->valor, 2, ',', '.') }}</p>
                        <p class="card-text"><strong>Duração:</strong> {{ $plano->intervalo_dias }} dias</p>

                        <button class="btn btn-primary mt-auto gerarPixBtn"
                                data-url="{{ route('empresa.plano.gerarPix', ['empresaId' => $empresa->id, 'planoId' => $plano->id]) }}"
                                data-plano-nome="{{ $plano->nome }}">
                            Gerar PIX
                        </button>
                        
                        </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="modal fade" id="pixModal" tabindex="-1" aria-labelledby="pixModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pixModalLabel">PIX para o Plano: <span id="planoNomeModal"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p class="text-success fw-bold">Escaneie o QR Code para pagar:</p>
        <img class="qrCodeImg-modal mb-3" id="qrCodeImgModal" src="" alt="QR Code PIX">
        <p class="mt-3">Ou use o código Copia e Cola:</p>
        <div class="input-group">
          <input type="text" class="form-control qrCodeText" id="qrCodeTextModal" readonly>
          <button class="btn btn-outline-primary btn-copy" type="button" onclick="copiarPixModal()">Copiar</button>
        </div>
        <p class="mt-3 text-muted" id="statusPagamentoText">Aguardando pagamento...</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

{{-- JS / Bibliotecas --}}
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script>
    // Variável para armazenar o intervalo de verificação (globalmente)
    let checkInterval;
    let currentPlanoEmpresaId = null;

    // Função de cópia específica para o input do Modal
    function copiarPixModal() {
        const input = document.getElementById('qrCodeTextModal');
        input.select();
        input.setSelectionRange(0, 99999);
        document.execCommand('copy');
        
        const btn = document.querySelector('#pixModal .btn-copy');
        btn.innerText = 'Copiado!';
        setTimeout(() => { btn.innerText = 'Copiar'; }, 1500);
    }
    
    // Instancia o modal do Bootstrap
    const pixModal = new bootstrap.Modal(document.getElementById('pixModal'));

    document.addEventListener('DOMContentLoaded', function() {
        
        // Limpar o intervalo de verificação ao fechar o modal
        document.getElementById('pixModal').addEventListener('hide.bs.modal', function () {
            if (checkInterval) {
                clearInterval(checkInterval);
            }
        });

        document.querySelectorAll('.gerarPixBtn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const url = this.dataset.url;
                const planoNome = this.dataset.planoNome;
                const originalText = this.innerText;

                // Desabilitar o botão enquanto processa
                btn.disabled = true;
                btn.innerText = 'Gerando PIX...';
                
                // Limpar qualquer verificação anterior
                if (checkInterval) {
                    clearInterval(checkInterval);
                }

                // Requisição AJAX ao backend Laravel
                axios.get(url)
                    .then(res => {
                        if(res.data.status === 'success') {
                            // 1. Popula o Modal com os dados do PIX
                            document.getElementById('planoNomeModal').innerText = planoNome;
                            document.getElementById('qrCodeImgModal').src = 'data:image/png;base64,' + res.data.qr_code_base64;
                            document.getElementById('qrCodeTextModal').value = res.data.qr_code_text;
                            document.getElementById('statusPagamentoText').innerText = 'Aguardando pagamento...';
                            document.getElementById('statusPagamentoText').classList.remove('text-success', 'text-danger');
                            document.getElementById('statusPagamentoText').classList.add('text-muted');

                            // 2. Exibe o Modal
                            pixModal.show();
                            
                            // 3. Atualiza o botão do Card para indicar PIX gerado
                            btn.innerText = 'PIX Gerado';
                            btn.classList.remove('btn-primary', 'btn-success');
                            btn.classList.add('btn-warning'); 
                            btn.disabled = false; // Permite gerar um novo PIX

                            // 4. Inicia a verificação automática
                            currentPlanoEmpresaId = res.data.external_reference;
                            
                            checkInterval = setInterval(() => {
                                axios.get('/planos/verificar-status/' + currentPlanoEmpresaId)
                                    .then(statusRes => {
                                        if(statusRes.data.status === 'approved') {
                                            clearInterval(checkInterval);
                                            pixModal.hide(); // Fecha o modal se estiver aberto
                                            
                                            // Atualiza a interface do Card
                                            btn.innerText = 'Pagamento Confirmado!';
                                            btn.classList.remove('btn-warning');
                                            btn.classList.add('btn-success');
                                            
                                            // Alerta de sucesso (SweetAlert2)
                                            Swal.fire({
                                                title: 'Sucesso! 🥳',
                                                text: `Pagamento do plano "${planoNome}" aprovado e ativado!`,
                                                icon: 'success',
                                                showConfirmButton: true,
                                            });
                                        } 
                                    })
                                    .catch(err => {
                                        // Apenas loga o erro de verificação para não incomodar o usuário
                                        console.error("Erro na verificação de status:", err);
                                    });
                            }, 5000); 

                            // 5. Timeout para a verificação (opcional)
                            setTimeout(() => {
                                if (checkInterval) {
                                    clearInterval(checkInterval);
                                    // Se o modal ainda estiver visível, atualiza a mensagem
                                    if (document.getElementById('pixModal').classList.contains('show')) {
                                        document.getElementById('statusPagamentoText').innerText = 'Tempo de espera excedido. Gere um novo PIX.';
                                        document.getElementById('statusPagamentoText').classList.remove('text-muted');
                                        document.getElementById('statusPagamentoText').classList.add('text-danger');
                                    }
                                }
                            }, 900000); // 15 minutos

                        }
                    })
                    .catch(err => {
                        console.error(err);
                        const errorMessage = err.response?.data?.erro || 'Erro ao gerar PIX. Tente novamente.';
                        
                        // Alerta de erro (SweetAlert2)
                        Swal.fire({
                            title: 'Erro',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'Ok'
                        });
                        
                        // Restaura o botão
                        btn.disabled = false;
                        btn.innerText = originalText;
                        btn.classList.remove('btn-warning');
                        btn.classList.add('btn-primary');
                    });
            });
        });
    });
</script>

</body>
</html>