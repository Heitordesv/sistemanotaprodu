<div class="modal fade" id="modalLead" tabindex="-1" aria-labelledby="modalLeadLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white border-0">
                <div>
                    <h5 class="modal-title fw-bold" id="modalLeadLabel">
                        <i class="fas fa-user-plus me-2"></i>Novo Lead
                    </h5>
                    <small class="text-white-50">Cadastre o contato e acompanhe pelo funil comercial.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <form action="{{ route('leads.store') }}" method="POST" id="formNovoLead" novalidate autocomplete="off">
                @csrf

                <div class="modal-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger d-flex align-items-start" role="alert">
                            <i class="fas fa-exclamation-circle me-2 mt-1"></i>
                            <div>
                                <strong>N&atilde;o foi poss&iacute;vel salvar o lead.</strong>
                                <div class="small">Revise os campos destacados abaixo.</div>
                            </div>
                        </div>
                    @endif

                    @include('leads.components.form')
                </div>

                <div class="modal-footer bg-light border-0 px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success px-4" id="btnSalvarLead">
                        <i class="fas fa-save me-1"></i> Salvar Lead
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>