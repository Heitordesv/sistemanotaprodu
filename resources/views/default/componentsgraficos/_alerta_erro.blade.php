<div class="alert alert-danger d-flex align-items-center p-4 rounded-3 shadow-sm" role="alert">
    <i class="bx bx-error-circle display-5 me-3"></i>
    <div>
        <h5 class="alert-heading fw-bold">Acesso Negado</h5>
        <p class="mb-0">Redirecionando...</p>
    </div>
</div>
<script>setTimeout(() => { window.location.href = "{{ route('bemvindo') }}"; }, 500);</script>