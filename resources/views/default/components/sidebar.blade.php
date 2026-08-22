<style>
    /* Marca NF-e Notas na sidebar */
    .sidebar-header {
        padding: 0 12px;
    }

    .nfe-sidebar-brand {
        width: 100%;
        min-width: 0;
        height: 48px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 5px 7px;
        border-radius: 14px;
        text-decoration: none !important;
        overflow: hidden;
        transition: background-color .2s ease, transform .2s ease;
    }

    .nfe-sidebar-brand:hover,
    .nfe-sidebar-brand:focus {
        background: rgba(13, 110, 253, .07);
        transform: translateY(-1px);
    }

    .nfe-brand-symbol {
        position: relative;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        color: #fff;
        background: linear-gradient(145deg, #1677ff 0%, #0d47a1 100%);
        box-shadow: 0 7px 18px rgba(13, 71, 161, .24);
    }

    .nfe-brand-symbol > i {
        font-size: 22px;
        line-height: 1;
    }

    .nfe-brand-check {
        position: absolute;
        right: -3px;
        bottom: -3px;
        width: 16px;
        height: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        border-radius: 50%;
        background: #16a34a;
        color: #fff;
        box-shadow: 0 2px 6px rgba(22, 163, 74, .28);
    }

    .nfe-brand-check i {
        font-size: 11px;
        font-weight: 700;
    }

    .nfe-brand-copy {
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        line-height: 1.05;
        overflow: hidden;
        white-space: nowrap;
    }

    .nfe-brand-name {
        display: block;
        color: #172033;
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -.45px;
    }

    .nfe-brand-name strong {
        color: #0d6efd;
        font-weight: 800;
    }

    .nfe-brand-subtitle {
        display: block;
        margin-top: 4px;
        color: #8a94a6;
        font-size: 9px;
        font-weight: 600;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    /* Sidebar recolhida: mantém apenas o símbolo da marca */
    @media screen and (min-width: 1025px) {
        .wrapper.toggled:not(.sidebar-hovered) .sidebar-header {
            padding: 0;
        }

        .wrapper.toggled:not(.sidebar-hovered) .nfe-sidebar-brand {
            justify-content: center;
            width: 70px;
            padding: 5px 0;
            gap: 0;
            transform: none;
        }

        .wrapper.toggled:not(.sidebar-hovered) .nfe-brand-copy {
            display: none;
        }
    }

    /* Compatibilidade com sidebar escura */
    html.semi-dark .nfe-sidebar-brand:hover,
    html.semi-dark .nfe-sidebar-brand:focus {
        background: rgba(255, 255, 255, .08);
    }

    html.semi-dark .nfe-brand-name {
        color: #f8fafc;
    }

    html.semi-dark .nfe-brand-name strong {
        color: #60a5fa;
    }

    html.semi-dark .nfe-brand-subtitle {
        color: #94a3b8;
    }

    html.semi-dark .nfe-brand-check {
        border-color: #171717;
    }
</style>

<div class="sidebar-wrapper" data-simplebar="true">

    <div class="sidebar-header">
        <a href="{{ isSuper(session('user_logged')['super']) ? route('dashboard.index') : route('graficos.index') }}"
           class="nfe-sidebar-brand"
           title="NF-e Notas"
           aria-label="{{ isSuper(session('user_logged')['super']) ? 'Ir para o Dashboard SaaS do NF-e Notas' : 'Ir para o início do NF-e Notas' }}">

            <span class="nfe-brand-symbol" aria-hidden="true">
                <i class='bx bx-receipt'></i>
                <span class="nfe-brand-check">
                    <i class='bx bx-check'></i>
                </span>
            </span>

            <span class="nfe-brand-copy">
                <span class="nfe-brand-name">NF-e <strong>Notas</strong></span>
                <span class="nfe-brand-subtitle">Gestão fiscal inteligente</span>
            </span>
        </a>
    </div>

    <ul class="metismenu" id="menu">

        @if(isSuper(session('user_logged')['super']))
        <li>
            <a href="javascript:;" class="has-arrow bg-dark">
                <div class="menu-title text-white">Gestão SaaS</div>
            </a>
            <ul>
                <li><a href="{{ route('dashboard.index') }}"><i class="bx bx-right-arrow-alt"></i>Visão Geral do SaaS</a></li>
                <li><a href="/empresas"><i class="bx bx-right-arrow-alt"></i>Clientes e Empresas</a></li>
                <li><a href="/planos"><i class="bx bx-right-arrow-alt"></i>Planos e Assinaturas</a></li>
                <li><a href="/planosPendentes"><i class="bx bx-right-arrow-alt"></i>Assinaturas Pendentes</a></li>
                <li><a href="/leads"><i class="bx bx-right-arrow-alt"></i>Leads e Oportunidades</a></li>
                <li><a href="/emails"><i class="bx bx-right-arrow-alt"></i>Central de E-mails</a></li>
                <li><a href="/ticketsSuper"><i class="bx bx-right-arrow-alt"></i>Central de Suporte</a></li>
                <li><a href="/relatorioSuper"><i class="bx bx-right-arrow-alt"></i>Relatórios Gerenciais</a></li>
                <li><a href="/pesquisa"><i class="bx bx-right-arrow-alt"></i>Satisfação de Clientes</a></li>
                <li><a href="/alertas"><i class="bx bx-right-arrow-alt"></i>Avisos aos Clientes</a></li>
                <li><a href="/representantes"><i class="bx bx-right-arrow-alt"></i>Parceiros e Representantes</a></li>
                <li><a href="/consulta-multa"><i class="bx bx-right-arrow-alt"></i>Multas e Boletos</a></li>
                <li><a href="/consultar-veiculo"><i class="bx bx-right-arrow-alt"></i>Consulta Veicular</a></li>
                <li><a href="/veiculo"><i class="bx bx-right-arrow-alt"></i>Relatórios Veiculares</a></li>
                <li><a href="/ibpt"><i class="bx bx-right-arrow-alt"></i>Tabela IBPT</a></li>
                <li><a href="/cidades"><i class="bx bx-right-arrow-alt"></i>Cadastro de Cidades</a></li>
                <li><a href="/etiquetas"><i class="bx bx-right-arrow-alt"></i>Gestão de Etiquetas</a></li>
                <li><a href="/videos"><i class="bx bx-right-arrow-alt"></i>Conteúdos e Tutoriais</a></li>
                <li><a href="/errosLog"><i class="bx bx-right-arrow-alt"></i>Logs e Erros</a></li>
            </ul>
        </li>
        @endif

        @include('default/menu')

        <li class="group">
            <a href="/videos/video" 
               class="flex items-center px-4 py-3 mx-3 my-1 rounded-xl text-gray-500 hover:bg-blue-50 hover:text-blue-700 transition-all duration-300 border border-transparent hover:border-blue-100 shadow-sm hover:shadow-md custom-helper"
               data-titulo="Central de Ajuda"
               data-icone="bx bx-help-circle"
               data-help="Precisa de uma mãozinha? Assista aos nossos tutoriais em vídeo para dominar todas as funções.">
                <div class="flex items-center justify-center min-w-[40px] h-10 rounded-lg bg-gray-100 group-hover:bg-blue-600 group-hover:text-white group-hover:rotate-12 transition-all duration-300 mr-3">
                    <i class='bx bx-help-circle text-xl'></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-bold tracking-tight">Central de Ajuda</span>
                    <span class="text-[10px] text-gray-400 group-hover:text-blue-500 leading-none transition-colors">Tutoriais e suporte</span>
                </div>
            </a>
        </li>

        <li class="group mt-1">
            <a href="/payment/finish" 
               class="flex items-center px-4 py-3 mx-3 my-1 rounded-xl text-gray-500 hover:bg-emerald-50 hover:text-emerald-700 transition-all duration-300 border border-transparent hover:border-emerald-100 shadow-sm hover:shadow-md custom-helper"
               data-titulo="Plano e Assinatura"
               data-icone="bx bx-credit-card-front"
               data-help="Gerencie seu plano, verifique faturas e atualize seus dados de pagamento aqui.">
                <div class="flex items-center justify-center min-w-[40px] h-10 rounded-lg bg-gray-100 group-hover:bg-emerald-600 group-hover:text-white group-hover:-rotate-12 transition-all duration-300 mr-3">
                    <i class='bx bx-credit-card-front text-xl'></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-bold tracking-tight">Plano e Assinatura</span>
                    <span class="text-[10px] text-gray-400 group-hover:text-emerald-500 leading-none transition-colors">Plano, cobrança e faturas</span>
                </div>
            </a>
        </li>

    </ul>

</div>