<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Logic for Comprovante Modal Action
    var comprovanteModal = document.getElementById('comprovanteModal');
    if(comprovanteModal) {
        comprovanteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var contaId = button.getAttribute('data-id');
            var form = document.getElementById('formComprovante');
            form.action = '{{ url("/contasPagar") }}/' + contaId + '/comprovante'; 
        });
    }

    // SweetAlert Delete
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function () {
            let form = this.closest('.form-delete');
            Swal.fire({
                title: 'Excluir conta?',
                text: "Esta ação é irreversível!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    });
});
document.addEventListener('DOMContentLoaded', function () {
    const modalVisualizar = new bootstrap.Modal(document.getElementById('modalVisualizarComprovante'));
    const imgWrapper = document.getElementById('wrapper-img');
    const pdfWrapper = document.getElementById('wrapper-pdf');
    const imgPreview = document.getElementById('img-preview');
    const pdfPreview = document.getElementById('pdf-preview');
    const btnDownload = document.getElementById('btnDownloadComp');
    const loader = document.getElementById('loader-comprovante');

    document.querySelectorAll('.btn-view-file').forEach(button => {
        button.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            const type = this.getAttribute('data-type');

            // Resetar estados
            imgWrapper.classList.add('d-none');
            pdfWrapper.classList.add('d-none');
            loader.classList.remove('d-none');
            
            // Configurar download
            btnDownload.href = url;

            if (type === 'pdf') {
                pdfPreview.src = url;
                pdfWrapper.classList.remove('d-none');
            } else {
                imgPreview.src = url;
                imgWrapper.classList.remove('d-none');
            }

            // Abrir o modal global
            modalVisualizar.show();

            // Esconder loader após pequeno delay para o iframe/img carregar
            setTimeout(() => loader.classList.add('d-none'), 600);
        });
    });
});
</script>
<script>
function abrirPreview(url, isPdf) {
    let img = document.getElementById('previewImage');
    let pdf = document.getElementById('previewPdf');

    if (isPdf) {
        pdf.src = url;
        pdf.classList.remove('d-none');
        img.classList.add('d-none');
    } else {
        img.src = url;
        img.classList.remove('d-none');
        pdf.classList.add('d-none');
    }

    let modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
}
</script><style>
/* Estilo das Badges Modernas (Soft Labels) */
.bg-label-success {
    background-color: rgba(40, 199, 111, 0.12) !important;
    color: #28c76f !important;
}

.bg-label-danger {
    background-color: rgba(234, 84, 85, 0.12) !important;
    color: #ea5455 !important;
}

.bg-label-warning {
    background-color: rgba(255, 159, 67, 0.12) !important;
    color: #ff9f43 !important;
}

.bg-label-secondary {
    background-color: rgba(130, 134, 139, 0.12) !important;
    color: #82868b !important;
}.preview-container {
    height: 180px;
    overflow: hidden;
    border-radius: 12px 12px 0 0;
    cursor: pointer;
}

.preview-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .3s ease;
}

.comprovante-card:hover .preview-img {
    transform: scale(1.08);
}

.comprovante-card {
    border-radius: 12px;
    transition: all .2s ease;
}

.comprovante-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}@media (max-width: 768px) {

    table thead {
        display: none;
    }

    table tbody tr {
        display: block;
        margin-bottom: 12px;
        background: #fff;
        border-radius: 12px;
        padding: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    table tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        border: none;
        font-size: 13px;
    }
}</style>