{{-- MODAL PIX MERCADO PAGO --}}
<div class="modal fade" id="pixModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div>
                    <small class="text-primary text-uppercase fw-bold">Mercado Pago</small>
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="bx bx-qr me-2"></i>Pagamento via PIX
                    </h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center p-4">
                <div class="bg-light p-3 rounded-3 mb-3 d-inline-block">
                    <img id="pixQrCodeImg" src="" class="img-fluid" style="max-width:220px;" alt="QR Code PIX">
                </div>

                <label class="form-label fw-semibold">PIX Copia e Cola</label>
                <div class="input-group mb-3">
                    <input type="text" id="pixCodigo" class="form-control" readonly>
                    <button type="button" class="btn btn-outline-primary" onclick="copiarChavePix()" title="Copiar PIX">
                        <i class="bx bx-copy"></i>
                    </button>
                </div>

                <div class="rounded-3 border p-3 text-start mb-3">
                    <div class="d-flex justify-content-between gap-3">
                        <span class="text-muted small">Payment ID</span>
                        <strong id="pixPaymentId" class="small text-break"></strong>
                    </div>
                </div>

                <p id="pixStatus" class="fw-bold text-warning mb-0">Aguardando pagamento...</p>
                <small class="text-muted d-block mt-1">A situação é consultada automaticamente enquanto esta página estiver aberta.</small>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>