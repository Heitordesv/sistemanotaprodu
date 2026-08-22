<style>
    .config-ecommerce-nav {
        position: sticky;
        top: 90px;
    }

    .config-ecommerce-nav .list-group-item {
        border: 0;
        border-radius: 8px !important;
        margin-bottom: 4px;
        color: #6c757d;
        font-weight: 600;
        transition: .2s ease;
    }

    .config-ecommerce-nav .list-group-item:hover,
    .config-ecommerce-nav .list-group-item.active-section {
        background: rgba(13, 110, 253, .08);
        color: #0d6efd;
    }

    .config-ecommerce-nav .list-group-item i {
        width: 24px;
        font-size: 18px;
        vertical-align: middle;
    }

    #secao-identidade,
    #secao-endereco,
    #secao-entrega,
    #secao-pagamentos,
    #secao-aparencia,
    #secao-conteudo,
    #secao-api {
        scroll-margin-top: 100px;
    }

    #template1,
    #template2 {
        cursor: pointer;
        transition: .2s ease;
    }

    #template1:hover,
    #template2:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08);
    }

    .config-save-bar {
        position: sticky;
        bottom: 12px;
        z-index: 20;
    }

    @media (max-width: 991.98px) {
        .config-ecommerce-nav {
            position: static;
        }

        .config-save-bar {
            position: static;
        }
    }
</style>

<div class="row g-4">
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm config-ecommerce-nav">
            <div class="card-body p-3">
                <div class="mb-3 px-2">
                    <small class="text-uppercase text-muted fw-bold">Configuração da Loja</small>
                    <div class="small text-muted mt-1">Escolha uma seção para editar.</div>
                </div>

                <div class="list-group list-group-flush" id="config-section-menu">
                    <a href="#secao-identidade" class="list-group-item list-group-item-action active-section">
                        <i class="bx bx-store-alt me-2"></i> Dados da Loja
                    </a>
                    <a href="#secao-endereco" class="list-group-item list-group-item-action">
                        <i class="bx bx-map me-2"></i> Endereço e Contato
                    </a>
                    <a href="#secao-entrega" class="list-group-item list-group-item-action">
                        <i class="bx bx-package me-2"></i> Entrega e Correios
                    </a>
                    <a href="#secao-pagamentos" class="list-group-item list-group-item-action">
                        <i class="bx bx-credit-card me-2"></i> Pagamentos
                    </a>
                    <a href="#secao-aparencia" class="list-group-item list-group-item-action">
                        <i class="bx bx-palette me-2"></i> Aparência
                    </a>
                    <a href="#secao-conteudo" class="list-group-item list-group-item-action">
                        <i class="bx bx-file me-2"></i> Conteúdo e Mapa
                    </a>
                    <a href="#secao-api" class="list-group-item list-group-item-action">
                        <i class="bx bx-code-alt me-2"></i> API Avançada
                    </a>
                </div>

                @if(isset($item) && $item)
                    <hr>
                    <div class="px-2 small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Correios</span>
                            @if(!empty($item->correios_usuario) && !empty($item->correios_senha) && !empty($item->correios_cartao))
                                <span class="badge bg-success">Configurado</span>
                            @else
                                <span class="badge bg-warning text-dark">Pendente</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Mercado Pago</span>
                            @if(!empty($item->mercadopago_public_key) && !empty($item->mercadopago_access_token))
                                <span class="badge bg-success">Configurado</span>
                            @else
                                <span class="badge bg-warning text-dark">Pendente</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        @include('config_ecommerce.partials._identidade')
        @include('config_ecommerce.partials._endereco')
        @include('config_ecommerce.partials._entrega')
        @include('config_ecommerce.partials._pagamentos')
        @include('config_ecommerce.partials._aparencia')
        @include('config_ecommerce.partials._conteudo')
        @include('config_ecommerce.partials._api')

        <div class="card border-0 shadow-lg config-save-bar mb-4">
            <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 py-3">
                <div>
                    <strong>Pronto para salvar?</strong>
                    <div class="small text-muted">As alterações serão aplicadas à configuração da Loja Online.</div>
                </div>
                <div class="d-flex gap-2">
                    @if(isset($item) && $item)
                        <a href="{{ route('configEcommerce.verSite') }}" target="_blank" class="btn btn-light border">
                            <i class="bx bx-show me-1"></i> Ver Loja
                        </a>
                    @endif

                    @isset($not_submit)
                        <button type="button" class="btn btn-primary px-4">
                            <i class="bx bx-save me-1"></i> Salvar Configurações
                        </button>
                    @else
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bx bx-save me-1"></i> Salvar Configurações
                        </button>
                    @endisset
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
    $(function () {
        setTimeout(() => {
            usarApi();
        }, 100);

        $('#config-section-menu a').on('click', function () {
            $('#config-section-menu a').removeClass('active-section');
            $(this).addClass('active-section');
        });
    });

    function selectTemplate(id) {
        $('#template1, #template2').removeClass('border-primary bg-light-primary');

        if (id == 1) {
            $('#template1').addClass('border-primary bg-light-primary');
            $('.cor').show();
            $('#tema_ecommerce').val('ecommerce');
        } else if (id == 2) {
            $('#template2').addClass('border-primary bg-light-primary');
            $('.cor').hide();
            $('#tema_ecommerce').val('ecommerce_one_tech');
        }
    }

    $('#inp-usar_api').change(() => {
        usarApi();
    });

    function usarApi() {
        let usar = $('#inp-usar_api').val();

        if (usar == 1) {
            $('.div-usarApi').removeClass('d-none');
            $('.div-tema').addClass('d-none');
        } else {
            $('.div-usarApi').addClass('d-none');
            $('.div-tema').removeClass('d-none');
        }
    }

    $('#btn_token').click(() => {
        let token = generate_token(25);

        swal({
            title: 'Atenção',
            text: 'Ao gerar um novo token, aplicações externas que usam o token atual precisarão ser atualizadas.',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((confirmed) => {
            if (confirmed) {
                $('#api_token').val(token);
            }
        });
    });

    function generate_token(length) {
        const caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'.split('');
        const resultado = [];

        for (let i = 0; i < length; i++) {
            const indice = Math.floor(Math.random() * caracteres.length);
            resultado.push(caracteres[indice]);
        }

        return resultado.join('');
    }
</script>

<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
@endsection