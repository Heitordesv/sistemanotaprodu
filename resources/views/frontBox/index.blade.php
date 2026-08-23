@php
    $temaAtual = $theme ?? null;

    $classesTema = collect([
        data_get($temaAtual, 'tema'),
        data_get($temaAtual, 'cabecalho'),
        data_get($temaAtual, 'plano_fundo')
            ? 'color-sidebar ' . data_get($temaAtual, 'plano_fundo')
            : null,
    ])->filter()->implode(' ');
@endphp

<!doctype html>
<html lang="pt-BR" class="{{ $classesTema }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#5932b9">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Frente de Caixa')</title>

    <link href="{{ asset('assets/css/simplebar.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/perfect-scrollbar.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/tagsinput.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vectormap/jquery-jvectormap-2.0.2.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/highcharts.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/metisMenu.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/pace.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/pace.min.js') }}"></script>

    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/bootstrap-extended.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/dark-theme.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/semi-dark.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/header-colors.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/select2-bootstrap4.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/toastr.min.css') }}" rel="stylesheet">

    <style>
        :root {
            --pdv-bg: #eef2f7;
            --pdv-card: #ffffff;
            --pdv-soft: #f7f9fc;
            --pdv-border: #dfe6ef;
            --pdv-text: #172033;
            --pdv-muted: #6b778c;
            --pdv-primary: #5932b9;
            --pdv-primary-dark: #452394;
            --pdv-blue: #1683d8;
            --pdv-green: #1f9d6d;
            --pdv-orange: #f59e0b;
            --pdv-danger: #dc3545;
            --pdv-radius: 18px;
            --pdv-shadow: 0 12px 35px rgba(31, 42, 68, .08);
            --pdv-transition: 160ms ease;
        }

        * { box-sizing: border-box; }
        html, body { min-height: 100%; }

        body {
            margin: 0;
            overflow-x: hidden;
            background:
                radial-gradient(circle at top left, rgba(89, 50, 185, .07), transparent 420px),
                var(--pdv-bg);
            color: var(--pdv-text);
            font-family: Inter, sans-serif;
            font-size: 14px;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        button, input, select, textarea { font: inherit; }
        button, a, input, select, textarea {
            transition: border-color var(--pdv-transition), box-shadow var(--pdv-transition),
                background-color var(--pdv-transition), color var(--pdv-transition),
                transform var(--pdv-transition), opacity var(--pdv-transition);
        }

        button:focus-visible, a:focus-visible, input:focus-visible,
        select:focus-visible, textarea:focus-visible {
            outline: 3px solid rgba(89, 50, 185, .2);
            outline-offset: 2px;
        }

        .wrapper {
            width: 100%;
            max-width: 1920px;
            min-height: 100vh;
            margin: 0 auto;
            padding: 10px;
        }

        #form-pdv { min-height: calc(100vh - 20px); }
        .pdv-workspace { min-height: inherit; }

        .pdv-command-bar {
            position: relative;
            z-index: 30;
            display: flex;
            min-height: 72px;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border: 1px solid var(--pdv-border);
            border-radius: var(--pdv-radius);
            background: rgba(255, 255, 255, .95);
            box-shadow: var(--pdv-shadow);
            backdrop-filter: blur(12px);
        }

        .pdv-command-status {
            display: flex;
            min-width: 230px;
            align-items: center;
            gap: 10px;
        }

        .pdv-command-icon {
            display: grid;
            width: 43px;
            height: 43px;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 12px;
            background: #f0ebff;
            color: var(--pdv-primary);
            font-size: 1.45rem;
        }

        .pdv-command-copy { min-width: 0; }
        .pdv-command-title { display: flex; align-items: center; gap: 7px; }
        .pdv-command-title strong { font-size: .92rem; font-weight: 800; white-space: nowrap; }
        .pdv-command-copy small {
            display: block;
            max-width: 250px;
            margin-top: 3px;
            overflow: hidden;
            color: var(--pdv-muted);
            font-size: .66rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .pdv-badge {
            padding: 4px 7px;
            border-radius: 7px;
            font-size: .59rem;
            font-weight: 800;
            letter-spacing: .02em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .pdv-badge-success { background: #eafaf2; color: #16714c; }
        .pdv-badge-info { background: #eaf5ff; color: #176ca9; }

        .pdv-icon-action {
            display: grid;
            width: 35px;
            height: 35px;
            place-items: center;
            flex: 0 0 auto;
            border: 1px solid var(--pdv-border);
            border-radius: 9px;
            background: #fff;
            color: var(--pdv-primary);
        }

        .pdv-comanda-label {
            margin: 0;
            color: var(--pdv-primary);
            font-size: .75rem;
            font-weight: 800;
        }

        .pdv-command-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
        }

        .pdv-command-button {
            display: inline-flex;
            min-height: 41px;
            align-items: center;
            gap: 7px;
            padding: 8px 11px;
            border: 0;
            border-radius: 11px;
            color: #fff;
            font-size: .72rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }
        .pdv-command-button:hover { color: #fff; filter: brightness(.96); transform: translateY(-1px); }
        .pdv-command-button i { font-size: 1.15rem; }
        .pdv-command-dark { background: #252a35; }
        .pdv-command-info { background: var(--pdv-blue); }
        .pdv-command-primary { background: var(--pdv-primary); }
        .pdv-command-warning { background: #f6a817; }
        .pdv-command-success { background: #09836e; }

        .pdv-command-menus { display: flex; align-items: center; gap: 6px; }
        .pdv-menu-button, .pdv-exit-button {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 10px;
            border: 1px solid var(--pdv-border);
            border-radius: 10px;
            background: #fff;
            color: #536174;
            font-size: .75rem;
            font-weight: 700;
            text-decoration: none;
        }
        .pdv-exit-button { color: var(--pdv-danger); }
        .pdv-menu-button:hover, .pdv-exit-button:hover { background: #f7f9fc; }

        .pdv-command-bar .dropdown-menu {
            min-width: 235px;
            padding: 7px;
            border: 1px solid var(--pdv-border);
            border-radius: 12px;
            box-shadow: 0 18px 44px rgba(31, 42, 68, .16);
        }
        .pdv-command-bar .dropdown-item {
            display: flex;
            min-height: 40px;
            align-items: center;
            gap: 9px;
            padding: 8px 10px;
            border: 0;
            border-radius: 8px;
            color: #455268;
            background: transparent;
            font-size: .78rem;
            font-weight: 600;
        }
        .pdv-command-bar .dropdown-item:hover { background: #f3f0ff; color: var(--pdv-primary); }
        .pdv-command-bar .dropdown-item i { width: 18px; font-size: 1.1rem; text-align: center; }

        .pdv-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(350px, 405px);
            gap: 10px;
            margin-top: 10px;
        }
        .pdv-products, .pdv-checkout { min-width: 0; }

        .pdv-scanner {
            position: relative;
            display: flex;
            min-height: 66px;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            overflow: hidden;
            border: 1px solid #bad4ef;
            border-radius: 15px;
            background: linear-gradient(135deg, #eef6ff, #f8fbff);
            color: #2465a7;
            cursor: text;
        }
        .pdv-scanner > i { flex: 0 0 auto; font-size: 1.8rem; }
        .mousetrap {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            padding: 0;
            border: 0;
            outline: 0;
            background: transparent;
            opacity: .01;
            cursor: text;
        }
        .pdv-scanner:focus-within { border-color: #5295d5; box-shadow: 0 0 0 4px rgba(22, 131, 216, .11); }
        #mousetrapTitle { display: flex; min-width: 0; flex-direction: column; color: #2465a7; }
        #mousetrapTitle strong {
            overflow: hidden;
            font-size: .8rem;
            font-weight: 800;
            text-overflow: ellipsis;
            text-transform: uppercase;
            white-space: nowrap;
        }
        #mousetrapTitle small { margin-top: 2px; color: #6b88a8; font-size: .66rem; }
        .pdv-scanner-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-left: auto;
            padding: 6px 8px;
            border-radius: 8px;
            background: #fff;
            color: #57718e;
            font-size: .63rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .pdv-product-form {
            display: grid;
            grid-template-columns: minmax(260px, 1fr) 120px 145px auto;
            gap: 8px;
            align-items: end;
            margin-top: 9px;
            padding: 10px;
            border: 1px solid var(--pdv-border);
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 7px 20px rgba(31, 42, 68, .04);
        }

        .pdv-product-form label, .pdv-payment-section label, .pdv-price-list label {
            display: block;
            margin-bottom: 5px;
            color: #59677a;
            font-size: .71rem;
            font-weight: 700;
        }

        .pdv-product-form .form-control, .pdv-product-form .form-select,
        .pdv-payment-section .form-control, .pdv-payment-section .form-select,
        .pdv-price-list .form-select,
        .pdv-product-form .select2-container .select2-selection--single,
        .pdv-payment-section .select2-container .select2-selection--single {
            min-height: 46px;
            border: 1px solid var(--pdv-border);
            border-radius: 11px;
            background: #fff;
            box-shadow: none;
        }

        .pdv-product-form .form-control:focus, .pdv-product-form .form-select:focus,
        .pdv-payment-section .form-control:focus, .pdv-payment-section .form-select:focus {
            border-color: #8e73d5;
            box-shadow: 0 0 0 4px rgba(89, 50, 185, .09);
        }

        .select2-container { width: 100% !important; }
        .select2-container .select2-selection--single .select2-selection__rendered {
            padding-left: 12px;
            padding-right: 34px;
            line-height: 44px;
        }
        .select2-container .select2-selection--single .select2-selection__arrow { height: 44px; }

        .pdv-add-field .btn {
            display: inline-flex;
            min-height: 46px;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            border: 0;
            border-radius: 11px;
            background: var(--pdv-primary);
            font-size: .75rem;
            font-weight: 800;
        }
        .pdv-add-field .btn:hover { background: var(--pdv-primary-dark); }

        .pdv-items-card {
            margin-top: 9px;
            overflow: hidden;
            border: 1px solid var(--pdv-border);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 7px 20px rgba(31, 42, 68, .04);
        }
        .pdv-items-heading {
            display: flex;
            min-height: 50px;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            border-bottom: 1px solid var(--pdv-border);
            background: #fbfcfe;
        }
        .pdv-items-heading h2, .pdv-section-heading h2 { margin: 0; font-size: .84rem; font-weight: 800; }
        .pdv-items-heading small, .pdv-section-heading small {
            display: block;
            margin-top: 2px;
            color: var(--pdv-muted);
            font-size: .62rem;
        }
        .pdv-items-count {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 8px;
            border-radius: 8px;
            background: #f2edff;
            color: var(--pdv-primary);
            font-size: .65rem;
            font-weight: 700;
        }
        .pdv-items-scroll {
            position: relative;
            height: clamp(270px, calc(100vh - 420px), 470px);
            overflow: auto;
        }
        .pdv-items-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
        .pdv-items-scroll::-webkit-scrollbar-thumb { border-radius: 999px; background: #cbd5e1; }
        .pdv-items-scroll .table { min-width: 650px; margin: 0; }
        .pdv-items-scroll thead { position: sticky; top: 0; z-index: 3; background: #f6f8fb; }
        .pdv-items-scroll th {
            padding: 10px 11px;
            border-bottom: 1px solid var(--pdv-border);
            color: #687589;
            font-size: .65rem;
            font-weight: 800;
            letter-spacing: .03em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .pdv-items-scroll td { padding: 7px 8px; vertical-align: middle; border-color: #edf1f5; }
        .pdv-items-scroll td:first-child { width: 48%; }
        .pdv-items-scroll input.form-control {
            min-height: 36px;
            padding: 6px 8px;
            border-color: transparent;
            background: transparent;
            color: #364258;
            font-size: .76rem;
            font-weight: 600;
        }
        .pdv-items-scroll tr:hover td { background: rgba(89, 50, 185, .025); }

        .pdv-empty-state {
            position: absolute;
            top: 50%;
            left: 50%;
            display: flex;
            width: min(300px, 90%);
            flex-direction: column;
            align-items: center;
            color: var(--pdv-muted);
            text-align: center;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }
        .pdv-empty-state i { margin-bottom: 7px; color: #cad3df; font-size: 2.7rem; }
        .pdv-empty-state strong { color: #536174; font-size: .8rem; }
        .pdv-empty-state span { margin-top: 3px; font-size: .66rem; }

        .pdv-adjustments {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr minmax(220px, 1.25fr);
            gap: 8px;
            margin-top: 9px;
        }
        .pdv-adjustment-card, .pdv-price-list, .pdv-location {
            min-height: 65px;
            border: 1px solid var(--pdv-border);
            border-radius: 14px;
            background: #fff;
        }
        .pdv-adjustment-card {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 9px;
            color: inherit;
            text-align: left;
        }
        .pdv-adjustment-card:hover { border-color: #d3c7f1; box-shadow: 0 6px 18px rgba(89, 50, 185, .07); }
        .pdv-adjustment-icon {
            display: grid;
            width: 37px;
            height: 37px;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 10px;
            background: #fff5df;
            color: #e19300;
            font-size: 1.1rem;
        }
        .pdv-adjustment-copy { display: flex; min-width: 0; flex-direction: column; }
        .pdv-adjustment-copy small { color: var(--pdv-muted); font-size: .63rem; }
        .pdv-adjustment-copy strong { margin-top: 2px; color: var(--pdv-text); font-size: .82rem; }
        .pdv-adjustment-edit { margin-left: auto; color: #9a6a0a; }
        .pdv-price-list { padding: 8px 10px; }
        .pdv-price-list .form-select { min-height: 39px; }
        .pdv-location {
            display: flex;
            grid-column: 1 / -1;
            min-height: 44px;
            align-items: center;
            gap: 7px;
            padding: 8px 11px;
            color: var(--pdv-muted);
            font-size: .71rem;
        }
        .pdv-location strong { margin-left: auto; color: var(--pdv-text); }

        .pdv-checkout {
            position: sticky;
            top: 10px;
            align-self: start;
            padding: 10px;
            border: 1px solid var(--pdv-border);
            border-radius: var(--pdv-radius);
            background: #fff;
            box-shadow: var(--pdv-shadow);
        }
        .pdv-checkout-actions { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 7px; }
        .pdv-quick-action {
            display: flex;
            min-height: 66px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 7px 5px;
            border: 0;
            border-radius: 13px;
            color: #fff;
            line-height: 1.1;
        }
        .pdv-quick-action:hover { color: #fff; filter: brightness(.97); transform: translateY(-1px); }
        .pdv-quick-action i { margin-bottom: 4px; font-size: 1.3rem; }
        .pdv-quick-action span { font-size: .7rem; font-weight: 800; }
        .pdv-quick-action small { margin-top: 2px; font-size: .56rem; opacity: .84; }
        .pdv-client-action { background: linear-gradient(135deg, #0f8ee9, #1477c2); }
        .pdv-multi-action { background: linear-gradient(135deg, #6738c6, #4e26a0); }
        .pdv-note-action { background: linear-gradient(135deg, #ffb01c, #e99400); }

        .pdv-total-card {
            margin-top: 9px;
            padding: 13px 14px;
            border: 1px solid #bde8ce;
            border-radius: 15px;
            background: linear-gradient(135deg, #f1fff6, #e6f9ed);
        }
        .pdv-total-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #237249;
            font-size: .7rem;
            font-weight: 800;
            text-transform: uppercase;
        }
        .pdv-total-value { display: flex; align-items: baseline; gap: 7px; margin-top: 5px; color: #125f39; }
        .pdv-total-value small { font-size: .83rem; font-weight: 800; }
        .pdv-total-value strong {
            min-width: 0;
            overflow: hidden;
            font-size: clamp(2rem, 4vw, 3.15rem);
            font-weight: 800;
            line-height: 1;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .pdv-payment-section { margin-top: 10px; }
        .pdv-section-heading { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
        .pdv-section-heading > i {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border-radius: 9px;
            background: #f2edff;
            color: var(--pdv-primary);
        }
        .pdv-payment-field, .pdv-received-field { margin-top: 8px; }
        .pdv-installments {
            margin-top: 8px;
            padding: 9px;
            border: 1px dashed #c8d3e2;
            border-radius: 12px;
            background: #fafcff;
        }
        .pdv-installment-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .pdv-installment-preview { max-height: 130px; margin-top: 7px; overflow: auto; }
        .pdv-installment-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 5px 2px;
            color: var(--pdv-muted);
            font-size: .67rem;
        }
        .pdv-installment-line strong { color: var(--pdv-text); }

        .pdv-credit-panel {
            margin-top: 8px;
            padding: 9px 10px;
            border: 1px solid transparent;
            border-radius: 11px;
            font-size: .68rem;
            line-height: 1.45;
        }
        .pdv-credit-panel strong { display: block; margin-bottom: 2px; font-size: .72rem; }
        .pdv-credit-panel.is-info { border-color: #b6ddf5; background: #eef9ff; color: #17658f; }
        .pdv-credit-panel.is-warning { border-color: #f4d38c; background: #fff9eb; color: #8a6012; }
        .pdv-credit-panel.is-success { border-color: #bde8ce; background: #effbf4; color: #16633f; }
        .pdv-credit-panel.is-danger { border-color: #f0b8be; background: #fff1f2; color: #9e2935; }

        .pdv-money-input {
            display: flex;
            align-items: center;
            overflow: hidden;
            border: 1px solid var(--pdv-border);
            border-radius: 11px;
            background: #fff;
        }
        .pdv-money-input:focus-within { border-color: #8e73d5; box-shadow: 0 0 0 4px rgba(89, 50, 185, .09); }
        .pdv-money-input span { padding-left: 12px; color: #687589; font-size: .76rem; font-weight: 700; }
        .pdv-money-input .form-control { border: 0; box-shadow: none; }

        .pdv-change-card {
            display: flex;
            min-height: 64px;
            align-items: center;
            justify-content: space-between;
            margin-top: 9px;
            padding: 9px 12px;
            border: 1px solid #efd5b0;
            border-radius: 13px;
            background: #fff9f1;
        }
        .pdv-change-card div { display: flex; flex-direction: column; }
        .pdv-change-card small { color: #8a6a3b; font-size: .65rem; font-weight: 700; text-transform: uppercase; }
        .pdv-change-card strong { margin-top: 2px; color: #4f3d24; font-size: 1.15rem; }
        .pdv-change-card > i { color: #d18a17; font-size: 1.5rem; }

        .pdv-finish-button {
            display: flex;
            width: 100%;
            min-height: 63px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-top: 10px;
            border: 0;
            border-radius: 14px;
            background: var(--pdv-green);
            font-weight: 800;
            box-shadow: 0 10px 22px rgba(31, 157, 109, .17);
        }
        .pdv-finish-button span { display: flex; align-items: center; gap: 6px; }
        .pdv-finish-button small { margin-top: 3px; font-size: .6rem; font-weight: 500; opacity: .84; }
        .pdv-finish-button:disabled { background: #d8dee7; color: #8793a5; box-shadow: none; opacity: 1; cursor: not-allowed; }

        .pdv-pix-box { padding: 12px; border: 1px solid var(--pdv-border); border-radius: 13px; background: #f8fafc; }
        #qr-code-img {
            width: 100%;
            max-width: 290px;
            min-height: 220px;
            object-fit: contain;
            border: 1px solid var(--pdv-border);
            border-radius: 12px;
            background: #fff;
        }

        .modal-content { overflow: hidden; border: 1px solid var(--pdv-border); border-radius: 16px; box-shadow: 0 26px 65px rgba(31, 42, 68, .22); }
        .modal-header, .modal-footer { background: #f8fafc; border-color: var(--pdv-border); }

        .modal-loading {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: none;
            background: rgba(23, 32, 51, .58);
            backdrop-filter: blur(4px);
        }
        .modal-loading.show, .modal-loading.active, .modal-loading.ativo { display: block !important; }

        #toast-container > div {
            width: min(360px, calc(100vw - 28px));
            border: 0;
            border-radius: 12px;
            box-shadow: 0 18px 42px rgba(31, 42, 68, .18);
            font-family: Inter, sans-serif;
            opacity: 1;
        }

        html.dark-theme {
            --pdv-bg: #07111f;
            --pdv-card: #0f1b2d;
            --pdv-soft: #132238;
            --pdv-border: #26384f;
            --pdv-text: #f8fafc;
            --pdv-muted: #94a3b8;
        }
        html.dark-theme body { background: #07111f; }
        html.dark-theme .pdv-command-bar, html.dark-theme .pdv-product-form,
        html.dark-theme .pdv-items-card, html.dark-theme .pdv-adjustment-card,
        html.dark-theme .pdv-price-list, html.dark-theme .pdv-location,
        html.dark-theme .pdv-checkout, html.dark-theme .pdv-menu-button,
        html.dark-theme .pdv-exit-button, html.dark-theme .pdv-icon-action {
            background: var(--pdv-card);
            border-color: var(--pdv-border);
        }
        html.dark-theme .pdv-items-heading, html.dark-theme .pdv-items-scroll thead,
        html.dark-theme .modal-header, html.dark-theme .modal-footer {
            background: var(--pdv-soft);
            border-color: var(--pdv-border);
        }
        html.dark-theme .pdv-product-form .form-control,
        html.dark-theme .pdv-product-form .form-select,
        html.dark-theme .pdv-payment-section .form-control,
        html.dark-theme .pdv-payment-section .form-select,
        html.dark-theme .pdv-price-list .form-select,
        html.dark-theme .pdv-money-input,
        html.dark-theme .select2-container .select2-selection--single {
            color: #e2e8f0;
            background: #0b1627;
            border-color: #334155;
        }
        html.dark-theme .select2-container .select2-selection__rendered { color: #e2e8f0; }
        html.dark-theme .pdv-items-scroll input.form-control { color: #e2e8f0; }
        html.dark-theme .pdv-installments { background: #111f32; border-color: #3a4f69; }
        html.dark-theme .pdv-adjustment-copy strong, html.dark-theme .pdv-location strong,
        html.dark-theme .pdv-installment-line strong { color: #f8fafc; }

        @media (max-width: 1320px) {
            .wrapper { padding: 7px; }
            .pdv-command-bar { gap: 7px; padding: 8px 9px; }
            .pdv-command-status { min-width: 200px; }
            .pdv-command-copy small { display: none; }
            .pdv-command-actions { gap: 4px; }
            .pdv-command-button { padding: 8px 9px; font-size: .67rem; }
            .pdv-layout { grid-template-columns: minmax(0, 1fr) 350px; gap: 8px; margin-top: 8px; }
            .pdv-product-form { grid-template-columns: minmax(220px, 1fr) 105px 130px auto; gap: 7px; }
            .pdv-adjustments { grid-template-columns: 1fr 1fr; gap: 7px; }
        }

        @media (max-width: 1100px) {
            .pdv-command-button span { display: none; }
            .pdv-command-button { width: 41px; justify-content: center; padding: 8px; }
            .pdv-command-button i { margin: 0; font-size: 1.2rem; }
        }

        @media (max-width: 991.98px) {
            body { overflow-y: auto; }
            .pdv-layout { display: flex; flex-direction: column; }
            .pdv-checkout { position: static; width: 100%; }
            .pdv-items-scroll { height: 340px; }
            .pdv-adjustments { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 767.98px) {
            .wrapper { padding: 5px; }
            #form-pdv { min-height: auto; }
            .pdv-command-bar { min-height: 62px; border-radius: 13px; }
            .pdv-command-status { min-width: 0; flex: 1; }
            .pdv-command-icon { width: 38px; height: 38px; }
            .pdv-badge { display: none; }
            .pdv-command-actions { display: none; }
            .pdv-command-menus { margin-left: auto; }
            .pdv-scanner { min-height: 58px; }
            .pdv-scanner-status { display: none; }
            .pdv-product-form { grid-template-columns: 1fr 1fr; }
            .pdv-product-field, .pdv-add-field { grid-column: 1 / -1; }
            .pdv-add-field .btn { width: 100%; justify-content: center; }
            .pdv-items-scroll { height: 300px; }
            .pdv-adjustments { grid-template-columns: 1fr 1fr; }
            .pdv-price-list { grid-column: 1 / -1; }
        }

        @media (max-width: 480px) {
            .wrapper { padding: 0; }
            .pdv-command-bar { border-left: 0; border-right: 0; border-radius: 0; }
            .pdv-command-copy small, .pdv-exit-button span { display: none; }
            .pdv-menu-button, .pdv-exit-button { min-height: 36px; padding: 7px 8px; }
            .pdv-layout { padding: 5px; margin-top: 2px; }
            #mousetrapTitle strong { font-size: .7rem; }
            #mousetrapTitle small { display: none; }
            .pdv-product-form { grid-template-columns: 1fr; }
            .pdv-product-form > * { grid-column: 1; }
            .pdv-items-scroll { height: 255px; }
            .pdv-adjustments { grid-template-columns: 1fr; }
            .pdv-adjustments > * { grid-column: 1; }
            .pdv-checkout { padding: 7px; border-radius: 14px; }
            .pdv-quick-action { min-height: 60px; }
            .pdv-quick-action small { display: none; }
            .pdv-total-value strong { font-size: 2.35rem; }
            .pdv-installment-grid { grid-template-columns: 1fr; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
            }
        }
    </style>

    @yield('css')
</head>
<body>
    <input type="hidden" value="{{ data_get(session('user_logged'), 'empresa') }}" id="empresa_id">
    <input type="hidden" value="{{ data_get(session('user_logged'), 'id') }}" id="usuario_id">

    @if(isset($config))
        <input type="hidden" id="pass" value="{{ $config->senha_remover }}">
    @endif

    <input
        type="hidden"
        value="{{ data_get($config ?? null, 'percentual_max_desconto', 0) }}"
        id="percentual_max_desconto"
    >

    <div class="wrapper">
        {!! Form::open()->post()->route('frenteCaixa.store')->id('form-pdv') !!}
            @include('frontBox._forms')
        {!! Form::close() !!}
    </div>

    <div class="page">
        <div class="row g-0">
            @yield('modal')
        </div>
    </div>

    <div class="modal-loading" aria-hidden="true"></div>

    @include('modals.frontBox._fluxo_diario', ['not_submit' => true])
    @include('modals.frontBox._lista_pre_venda', ['not_submit' => true])
    @include('modals.frontBox._suprimento_caixa', ['not_submit' => true])
    @include('modals.frontBox._comanda_pdv', ['not_submit' => true])
    @include('modals.frontBox._sangria_caixa', ['not_submit' => true])
    @include('modals._abrir_caixa')

    <script>
        var casas_decimais = @json($casasDecimais ?? 2);
        const path_url = window.location.protocol + '//' + window.location.host + '/';
        window.pdvProdutoEndpoints = {
            pesquisa: @json(route('frenteCaixa.produtos.pesquisa')),
            find: @json(url('/frenteCaixa/produtos/find')),
            findByBarcode: @json(route('frenteCaixa.produtos.findByBarcode')),
            findByBarcodeReference: @json(route('frenteCaixa.produtos.findByBarcodeReference'))
        };
    </script>

    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script>
        const pdvEmpresaHash = @json(data_get(session('user_logged'), 'hash_empresa'));

        if (pdvEmpresaHash) {
            $.ajaxSetup({
                headers: {
                    'X-Empresa-Hash': pdvEmpresaHash
                }
            });
        }
    </script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src="{{ asset('js/jquery.mask.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}?v=10"></script>
    <script src="{{ asset('js/frontBox.js') }}?v=10"></script>
    <script src="{{ asset('js/theme.js') }}?v=1"></script>
    <script src="{{ asset('assets/js/toastr.min.js') }}"></script>

    <script>
        (function ($) {
            'use strict';

            if (!$) {
                return;
            }

            var creditoClientePdv = null;
            var carregandoCreditoPdv = false;
            var requisicaoCreditoPdv = null;
            var consultaCreditoSequencia = 0;

            function moedaParaFloat(valor) {
                valor = String(valor || '').replace(/\s/g, '').replace('R$', '');

                if (!valor) {
                    return 0;
                }

                if (valor.indexOf(',') !== -1) {
                    valor = valor.replace(/\./g, '').replace(',', '.');
                }

                return parseFloat(valor.replace(/[^0-9.-]/g, '')) || 0;
            }

            function moeda(valor) {
                return Number(valor || 0).toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });
            }

            function mostrarAviso(titulo, texto, icone) {
                if (typeof window.swal === 'function') {
                    window.swal(titulo, texto, icone || 'warning');
                    return;
                }

                window.alert(texto);
            }

            function valorCrediarioSolicitado() {
                var tiposMistos = $('[name="tipo_pagamento_row[]"]');
                var valoresMistos = $('[name="valor_integral_row[]"]');
                var totalMisto = 0;

                tiposMistos.each(function (indice) {
                    if (String($(this).val()) === '06') {
                        totalMisto += moedaParaFloat(valoresMistos.eq(indice).val());
                    }
                });

                if (tiposMistos.length > 0) {
                    return totalMisto;
                }

                if (String($('#inp-tipo_pagamento').val()) === '06') {
                    return moedaParaFloat($('.total-venda').first().text());
                }

                return 0;
            }

            function painelCredito() {
                var painel = $('#credito-cliente-pdv');

                if (!painel.length) {
                    painel = $('<div id="credito-cliente-pdv" class="pdv-credit-panel d-none" role="alert"></div>');
                    $('#inp-tipo_pagamento').closest('.pdv-payment-field').after(painel);
                }

                return painel;
            }

            function aplicarBloqueioCredito(bloquear) {
                var botao = $('#salvar_venda');

                if (!botao.length) {
                    return;
                }

                if (bloquear) {
                    botao.attr('data-bloqueio-credito', '1').prop('disabled', true);
                    return;
                }

                if (botao.attr('data-bloqueio-credito') === '1') {
                    botao.removeAttr('data-bloqueio-credito');

                    if (typeof window.validateButtonSave === 'function') {
                        window.validateButtonSave();
                    }
                }
            }

            function renderizarCredito(solicitado) {
                var painel = painelCredito();

                painel.removeClass('is-info is-warning is-success is-danger');

                if (solicitado <= 0) {
                    painel.addClass('d-none').empty();
                    aplicarBloqueioCredito(false);
                    return;
                }

                if (carregandoCreditoPdv) {
                    painel.removeClass('d-none').addClass('is-info').html(
                        '<strong>Consultando limite de crédito</strong>' +
                        'Aguarde enquanto os dados do cliente são atualizados.'
                    );
                    aplicarBloqueioCredito(true);
                    return;
                }

                if (!creditoClientePdv) {
                    painel.removeClass('d-none').addClass('is-warning').html(
                        '<strong>Cliente necessário</strong>' +
                        'Selecione um cliente para consultar o limite do crediário.'
                    );
                    aplicarBloqueioCredito(true);
                    return;
                }

                var credito = creditoClientePdv.credito || {};
                var limite = Number(credito.limite || 0);
                var utilizado = Number(credito.utilizado || 0);
                var disponivel = Number(credito.disponivel || 0);
                var autorizado = limite > 0 && solicitado <= (disponivel + 0.009);

                painel.removeClass('d-none')
                    .addClass(autorizado ? 'is-success' : 'is-danger')
                    .html(
                        '<strong>' + (autorizado
                            ? 'Crédito disponível para esta venda'
                            : 'Venda no crediário bloqueada') + '</strong>' +
                        'Limite: ' + moeda(limite) +
                        ' &nbsp;•&nbsp; Utilizado: ' + moeda(utilizado) +
                        ' &nbsp;•&nbsp; Disponível: ' + moeda(disponivel) +
                        ' &nbsp;•&nbsp; Venda: ' + moeda(solicitado)
                    );

                aplicarBloqueioCredito(!autorizado);
            }

            function carregarCreditoCliente(callback) {
                var clienteId = parseInt($('#inp-cliente_id').val(), 10) || 0;
                var empresaId = parseInt($('#empresa_id').val(), 10) || 0;
                var sequenciaAtual = ++consultaCreditoSequencia;

                if (requisicaoCreditoPdv && requisicaoCreditoPdv.readyState !== 4) {
                    requisicaoCreditoPdv.abort();
                }

                if (!clienteId || !empresaId) {
                    carregandoCreditoPdv = false;
                    creditoClientePdv = null;
                    renderizarCredito(valorCrediarioSolicitado());

                    if (typeof callback === 'function') {
                        callback(false);
                    }
                    return;
                }

                carregandoCreditoPdv = true;
                renderizarCredito(valorCrediarioSolicitado());

                requisicaoCreditoPdv = $.get(
                    path_url + 'api/cliente/find/' + clienteId,
                    { empresa_id: empresaId }
                );

                requisicaoCreditoPdv
                    .done(function (cliente) {
                        if (sequenciaAtual !== consultaCreditoSequencia) {
                            return;
                        }

                        if ((parseInt($('#inp-cliente_id').val(), 10) || 0) !== clienteId) {
                            return;
                        }

                        creditoClientePdv = {
                            clienteId: clienteId,
                            credito: cliente.credito || {
                                limite: 0,
                                utilizado: 0,
                                disponivel: 0
                            }
                        };

                        if (typeof callback === 'function') {
                            callback(true);
                        }
                    })
                    .fail(function (xhr, status) {
                        if (status === 'abort' || sequenciaAtual !== consultaCreditoSequencia) {
                            return;
                        }

                        creditoClientePdv = null;

                        if (typeof callback === 'function') {
                            callback(false);
                        }
                    })
                    .always(function () {
                        if (sequenciaAtual !== consultaCreditoSequencia) {
                            return;
                        }

                        carregandoCreditoPdv = false;
                        renderizarCredito(valorCrediarioSolicitado());
                    });
            }

            function validarLimiteCredito(mostrarMensagem) {
                var solicitado = valorCrediarioSolicitado();
                var clienteId = parseInt($('#inp-cliente_id').val(), 10) || 0;

                renderizarCredito(solicitado);

                if (solicitado <= 0) {
                    return true;
                }

                if (!clienteId) {
                    if (mostrarMensagem) {
                        mostrarAviso(
                            'Cliente obrigatório',
                            'Selecione um cliente para utilizar o crediário.',
                            'warning'
                        );
                    }
                    return false;
                }

                if (carregandoCreditoPdv) {
                    if (mostrarMensagem) {
                        mostrarAviso(
                            'Consultando crédito',
                            'Aguarde a consulta do limite do cliente.',
                            'info'
                        );
                    }
                    return false;
                }

                if (!creditoClientePdv || creditoClientePdv.clienteId !== clienteId) {
                    carregarCreditoCliente();

                    if (mostrarMensagem) {
                        mostrarAviso(
                            'Consultando crédito',
                            'O limite está sendo consultado. Tente finalizar novamente após a consulta.',
                            'info'
                        );
                    }
                    return false;
                }

                var credito = creditoClientePdv.credito || {};
                var limite = Number(credito.limite || 0);
                var disponivel = Number(credito.disponivel || 0);

                if (limite <= 0) {
                    if (mostrarMensagem) {
                        mostrarAviso(
                            'Crediário não autorizado',
                            'Este cliente não possui limite de crédito cadastrado.',
                            'error'
                        );
                    }
                    return false;
                }

                if (solicitado > (disponivel + 0.009)) {
                    if (mostrarMensagem) {
                        mostrarAviso(
                            'Limite de crédito excedido',
                            'Disponível: ' + moeda(disponivel) +
                            '. Valor solicitado: ' + moeda(solicitado) + '.',
                            'error'
                        );
                    }
                    return false;
                }

                return true;
            }

            $(document).on('change select2:select select2:clear', '#inp-cliente_id', function () {
                creditoClientePdv = null;
                carregarCreditoCliente();
            });

            $(document).on(
                'change input',
                '#inp-tipo_pagamento, [name="tipo_pagamento_row[]"], ' +
                '[name="valor_integral_row[]"], .subtotal-item',
                function () {
                    validarLimiteCredito(false);
                }
            );

            $(document).on('click', '#btn-seleciona-cliente', function () {
                creditoClientePdv = null;
                window.setTimeout(carregarCreditoCliente, 150);
            });

            var formularioPdv = document.getElementById('form-pdv');
            if (formularioPdv) {
                formularioPdv.addEventListener('submit', function (event) {
                    if (!validarLimiteCredito(true)) {
                        event.preventDefault();
                        event.stopImmediatePropagation();
                    }
                }, true);
            }

            var totalCreditoElement = document.querySelector('.total-venda');
            if (totalCreditoElement && window.MutationObserver) {
                new MutationObserver(function () {
                    validarLimiteCredito(false);
                }).observe(totalCreditoElement, {
                    childList: true,
                    characterData: true,
                    subtree: true
                });
            }

            window.atualizarCreditoClientePdv = function (credito) {
                if (!creditoClientePdv) {
                    return;
                }

                creditoClientePdv.credito = credito;
                renderizarCredito(valorCrediarioSolicitado());
            };

            if ((parseInt($('#inp-cliente_id').val(), 10) || 0) > 0) {
                carregarCreditoCliente();
            }
        })(window.jQuery);
    </script>

    <script>
        toastr.options = {
            closeButton: true,
            newestOnTop: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            preventDuplicates: true,
            showDuration: 300,
            hideDuration: 700,
            timeOut: 7000,
            extendedTimeOut: 1500,
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut'
        };

        @if(session()->has('flash_sucesso'))
            toastr.success(@json(session()->get('flash_sucesso')), 'Operação realizada');
        @endif

        @if(session()->has('flash_erro'))
            toastr.error(@json(session()->get('flash_erro')), 'Não foi possível concluir');
        @endif

        @if(session()->has('flash_warning'))
            toastr.warning(@json(session()->get('flash_warning')), 'Atenção');
        @endif
    </script>

    @yield('js')
</body>
</html>
