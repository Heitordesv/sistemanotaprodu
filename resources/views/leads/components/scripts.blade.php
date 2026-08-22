<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('modalLead');
    const hasValidationErrors = @json($errors->any());

    if (hasValidationErrors && modalElement && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }

    const formNovoLead = document.getElementById('formNovoLead');
    if (formNovoLead) {
        formNovoLead.addEventListener('submit', function () {
            if (formNovoLead.dataset.submitting === '1') {
                return false;
            }

            formNovoLead.dataset.submitting = '1';
            const submitButton = document.getElementById('btnSalvarLead');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Salvando...';
            }
        });
    }

    const formImportar = document.getElementById('formImportar');
    const loadingImport = document.getElementById('loading-import');

    if (formImportar && loadingImport) {
        formImportar.addEventListener('submit', function () {
            loadingImport.style.display = 'flex';
        });
    }

    window.addEventListener('pageshow', function () {
        if (loadingImport) {
            loadingImport.style.display = 'none';
        }
    });

    if (window.jQuery && jQuery.fn.mask) {
        const applyWhatsappMask = function () {
            jQuery('.whatsapp-mask').mask('(00) 00000-0000');
        };

        applyWhatsappMask();

        if (modalElement) {
            modalElement.addEventListener('shown.bs.modal', applyWhatsappMask);
        }
    }
});
</script>