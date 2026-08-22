<style>
.network-alert {
    position: fixed;
    top: 0;
    width: 100%;
    padding: 10px;
    text-align: center;
    color: #fff;
    z-index: 9999;
    transform: translateY(-100%);
    transition: .3s;
}

.network-alert.show {
    transform: translateY(0);
}

.offline { background: #dc3545; }
.online { background: #28a745; }
</style>

<div id="offlineAlert" class="network-alert offline">
    🚨 Sem conexão com a internet
</div>

<div id="onlineAlert" class="network-alert online">
    ✅ Conexão restaurada
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const offline = document.getElementById('offlineAlert');
    const online = document.getElementById('onlineAlert');

    function show(el) {
        el.classList.add('show');
    }

    function hide(el) {
        el.classList.remove('show');
    }

    if (!navigator.onLine) show(offline);

    window.addEventListener('offline', () => {
        show(offline);
        hide(online);
    });

    window.addEventListener('online', () => {
        show(online);
        hide(offline);

        setTimeout(() => hide(online), 3000);
    });

});
</script>