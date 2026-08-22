<!-- Conteúdo Principal -->
<main class="row">
    @yield('content')
</main>

<!-- Componentes de UI -->
<div class="overlay toggle-icon"></div>

<a href="javaScript:;" class="back-to-top" aria-label="Voltar ao topo">
    <i class='bx bxs-up-arrow-alt'></i>
</a>

@if(!isset($not_loading))
    <div class="modal-loading loading-class" aria-hidden="true"></div>
@endif

<!-- Rodapé -->
<footer
    class="page-footer d-flex align-items-center justify-content-between px-4 flex-wrap"
    style="position: static !important; left: auto !important; right: auto !important; bottom: auto !important; width: 100%; min-height: 44px; margin-top: 18px; gap: 8px;"
>
    <p class="mb-0 text-muted">
        Copyright © {{ date('Y') }} <strong>{{ env("APP_NAME") }}</strong>. Todos os direitos reservados.
    </p>

    @if($ultimoAcesso != null)
        <div class="last-access small">
            <span class="text-muted">Último acesso:</span>
            <span class="text-primary fw-bold">
                {{ \Carbon\Carbon::parse($ultimoAcesso->created_at)->format('d/m/Y H:i') }}
            </span>
        </div>
    @endif
</footer>