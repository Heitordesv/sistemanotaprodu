<!doctype html>
    <html lang="pt-BR" class="{{ $theme != null ? $theme->tema : ''}} {{ $theme != null ? $theme->cabecalho : ''}} {{ $theme != null ? 'color-sidebar ' . $theme->plano_fundo : ''}}">

   <head>
    @include('default.components.head')

    @if(!request()->routeIs('tickets.show') && !request()->routeIs('ticketsSuper.show'))
    <style>
        .chat-wrapper,
        .chat-toggle-btn,
        .chat-toggle-btn-mobile {
            display: none !important;
        }
    </style>
    @endif
</head>

    <body>

<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/serviceworker.js')
        .then(() => console.log('Service Worker registrado!'))
        .catch(err => console.error('Erro ao registrar SW:', err));
    }
</script>
        <div class="wrapper">
        @include('default.components.sidebar')

        @include('default.components.header')

<div class="page-wrapper">
   @include('default.components.footer')

</div>

@include('default.components.switcher')
@include('default.components.network-alert')
@include('default.components.scripts')


</body>
</html>