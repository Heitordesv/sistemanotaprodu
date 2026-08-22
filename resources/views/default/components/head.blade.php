<!doctype html>
    <html lang="pt-BR" class="{{ $theme != null ? $theme->tema : ''}} {{ $theme != null ? $theme->cabecalho : ''}} {{ $theme != null ? 'color-sidebar ' . $theme->plano_fundo : ''}}">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!--favicon-->

        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="bearer_token_api_brasil" content="{{ env('BEARER_TOKEN_LOGIN_API_BRASIL') ?? ''}}">
                <meta name="profile_id" content="{{ env('profile_id') ?? ''}}">
<link rel="manifest" href="{{ asset('manifest.json') }}">

<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('logos/logonfenotas.png') }}">

<link rel="apple-touch-icon" href="{{ asset('logos/logonfenotas.png') }}">

<meta name="mobile-web-app-capable" content="yes"> 

<meta name="apple-mobile-web-app-capable" content="yes"> 

<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="theme-color" content="#0d6efd">
        <link href="/assets/css/simplebar.css" rel="stylesheet" />
        <link href="/assets/css/tagsinput.css" rel="stylesheet" />
        <link href="/assets/css/perfect-scrollbar.css" rel="stylesheet" />
        <link href="/assets/css/highcharts.css" rel="stylesheet" />
        <link href="/assets/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet" />
        <link href="/assets/css/metisMenu.min.css" rel="stylesheet" />
        <!-- loader-->
        <link href="/assets/css/pace.min.css" rel="stylesheet" />
        <script src="/assets/js/pace.min.js"></script>
        <!-- Bootstrap CSS -->
        <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
        <link href="/assets/css/bootstrap-extended.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
        <link href="/assets/css/app.css" rel="stylesheet">
        <link href="/assets/css/icons.css" rel="stylesheet">
        <!-- Theme Style CSS -->
        <link rel="stylesheet" href="/assets/css/dark-theme.css" />
        <link rel="stylesheet" href="/assets/css/semi-dark.css" />
        <link rel="stylesheet" href="/assets/css/header-colors.css" />
        <link href="/assets/css/select2.min.css" rel="stylesheet" />
        <link href="/assets/css/select2-bootstrap4.css" rel="stylesheet" />
        <link href="/assets/css/style.css" rel="stylesheet" />
        <link rel="stylesheet" type="text/css" href="/assets/css/toastr.min.css">
        <title>{{$title}}</title>
<style>/* ---------------------------
      CORES E BOTÕES
--------------------------- */
.btn-primary {
    color: #fff;
    background-color: #dc3545;
    border-color: #dc3545;
}

.toggle-icon {
    font-size: 22px;
    cursor: pointer;
    color: #dc3545;
}

/* Hover Menu */
.sidebar-wrapper .metismenu a:hover,
.sidebar-wrapper .metismenu a:focus,
.sidebar-wrapper .metismenu a:active,
.sidebar-wrapper .metismenu .mm-active > a {
    color: #dc3545;
    text-decoration: none;
    background: rgba(136, 51, 255, 0.12);
}

/* ---------------------------
         LOGO PADRÃO
--------------------------- */
.logo-nfe-notas-icone-style {
    font-family: "Poppins", sans-serif;
    font-weight: 900;
    font-size: 22px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    color: #0d47a1;
    padding: 6px 2px;
    white-space: nowrap;
    transition: all 0.30s ease-in-out;
}

/* BLOCO NFE */
.logo-nfe-notas-icone-style .logo-monograma {
    background: #ffffff;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 20px;
    color: #0d47a1;
    font-weight: 900;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    line-height: 1;
    transition: all 0.30s ease-in-out;
}

/* BLOCO NOTAS */
.logo-nfe-notas-icone-style .destaque-icone {
    background-color: #4caf50;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    line-height: 1;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    transition: all 0.30s ease-in-out;
}

/* ---------------------------
   QUANDO O MENU FECHA
   Template usa: .wrapper.toggled
--------------------------- */
.wrapper.toggled .logo-nfe-notas-icone-style {
    gap: 0;
    justify-content: center;
}

/* Oculta a palavra "NOTAS" */
.wrapper.toggled .logo-nfe-notas-icone-style .texto-completo {
    opacity: 0;
    visibility: hidden;
    width: 0;
    overflow: hidden;
    padding: 0;
    margin: 0;
}

/* Ajusta somente NFE (modo ícone) */
.wrapper.toggled .logo-nfe-notas-icone-style .logo-monograma {
    padding: 10px 12px;
    font-size: 18px;
    border-radius: 10px;
}

/* ================================
        RESPONSIVO PARA TABLET
================================ */
@media (max-width: 768px) {
    .logo-nfe-notas-icone-style {
        font-size: 20px;
        gap: 4px;
    }

    .logo-nfe-notas-icone-style .logo-monograma {
        font-size: 18px;
        padding: 5px 10px;
    }

    .logo-nfe-notas-icone-style .destaque-icone {
        font-size: 16px;
        padding: 5px 10px;
    }

    /* menu fechado no tablet */
    .wrapper.toggled .logo-nfe-notas-icone-style .logo-monograma {
        font-size: 17px;
        padding: 8px 10px;
    }
}

/* ================================
        RESPONSIVO PARA CELULAR
================================ */
@media (max-width: 480px) {
    .logo-nfe-notas-icone-style {
        font-size: 18px;
        gap: 3px;
        padding: 4px 0;
    }

    .logo-nfe-notas-icone-style .logo-monograma {
        font-size: 16px;
        padding: 4px 8px;
    }

    .logo-nfe-notas-icone-style .destaque-icone {
        font-size: 14px;
        padding: 4px 8px;
    }

    /* Ícone NFE no celular (menu fechado) */
    .wrapper.toggled .logo-nfe-notas-icone-style .logo-monograma {
        font-size: 16px;
        padding: 7px 9px;
        border-radius: 8px;
    }
}

</style>
        @yield('css')

        @if($colorDefault != '')
        <style type="text/css">
            :root {
                --color-default: {{$colorDefault}};
            }
            
        </style>
        <link href="/assets/css/extend.css" rel="stylesheet" />
        @endif
    </head>
