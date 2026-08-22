(function carregarRecursosPdv() {
    'use strict';

    if (window.__frontBoxRecursosCarregados) {
        return;
    }

    if (!window.jQuery || typeof window.calcTotalPayment !== 'function') {
        window.setTimeout(carregarRecursosPdv, 50);
        return;
    }

    window.__frontBoxRecursosCarregados = true;

    function carregar(src) {
        var script = document.createElement('script');
        script.src = src;
        script.async = false;
        document.body.appendChild(script);
    }

    carregar('/js/frontBoxParcelasCore.js?v=1');
    carregar('/js/frontBoxUx.js?v=1');
})();