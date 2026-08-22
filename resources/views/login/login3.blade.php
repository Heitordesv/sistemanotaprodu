<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="/assets/login3/bootstrap.css" type="text/css">
    <link href="/assets/css/bootstrap-extended.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/login3/login.css" type="text/css" />
    <link href="/assets/css/icons.css" rel="stylesheet">
    
</head>

<body class="login">
    <!-- BEGIN LOGO -->
    <div class="logo">
        <img src="logos/logonfenotas.png" alt="">
    </div>
    <!-- END LOGO -->
    <!-- BEGIN LOGIN -->
    <div class="content">
        @if(session()->has('flash_login'))
        <div class="mt-2 mb-0" style="width: 98%; margin-left: 10px; margin-top: 0px;">
            <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show mb-0 py-1">
                <div class="d-flex align-items-center">
                    <div class="font-35 text-white"><i class="bx bxs-message-square-x"></i>
                    </div>
                    <div class="ms-3">

                        <div class="text-white">{{ session()->get('flash_login') }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <form action="{{ route('login.request') }}" class="" name="form-login" id="form-login" method="post">
            <h4 class="">Entre com seus dados de acesso</h4>
            @csrf
            <div class="input-group">
                <div class="input-icon-container">
                    <input autocomplete="off" type="text" class="form-control rounded-right" id="login" placeholder="Login" autofocus @if(session('login') !=null) value="{{ session('login') }}" @else @if(isset($loginCookie)) value="{{$loginCookie}}" @endif @endif name="login" />
                </div>
            </div>
            <div class="input-group">
                <div class="input-icon-container" id="show_hide_password">
                    <input type="password" class="form-control rounded-right" id="senha" name="senha" placeholder="Senha" autocomplete="off" @if(isset($senhaCookie)) value="{{$senhaCookie}}" @endif>
                    <a class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                </div>
            </div>
            <div class="form-actions">
                <label class="rememberme mt-checkbox mt-checkbox-outline">
                </label>
                <button type="submit" class="btn btn-success btn-lg pull-right px-5" form="form-login"> <i class="bx bx-check"></i> Entrar </button>
            </div>

            <div class="create-account">
                <p><a href="javascript:;" id="forget-password" class="btn btn-primary btn-lg"> <i class="bx bx-key"></i> Recuperar senha de acesso </a></p>
            </div>
            <div class="create-account" style="margin-top: 20px; width: 100%">
                <p><a href="/cadastro/plano" style="text-align:center" type="submit" class="btn btn-info"><i class="bx bxs-layer-plus"></i>Quero cadastrar minha empresa</a></p>
            </div>
        </form>

        <!-- END LOGIN FORM -->
        <div class="div-recuperar-senha-sicok hide">
            <form method="post" id="forget-form" action="{{ route('recuperarSenha') }}">
                @csrf
                <h3>Esqueceu a senha?</h3>
                <p>Receba uma nova senha em seu e-mail cadastrado.</p>
                <div class="form-group">
                    <input class="form-control placeholder-no-fix input-email-recuperar-senha-sicok" type="text" autocomplete="off" placeholder="E-mail cadastrado" name="email" />
                </div>
                <div class="form-actions">
                    <button type="button" id="back-btn" class="btn btn-primary btn-lg">Tela de login</button>
                    <button type="submit" class="btn btn-success btn-lg pull-right btn-repuerar-senha-sicok"> <i class="bx bx-check"></i> Solicitar nova senha </button>
                </div>
            </form>
        </div>
    </div>
    <!-- END LOGIN -->
    <!-- BEGIN COPYRIGHT -->
    <div class="copyright"> &copy; Slym - Gestão Empresarial Online </div>
    <!-- JS // PLUGINS -->
    <script src="assets/login3/js/jquery-3.3.1.min.js" type="text/javascript"></script>
    <script src="assets/login3/js/jquery.backstretch.min.js" type="text/javascript"></script>
    <script src="assets/login3/js/login.js" type="text/javascript"></script>
    <script>
        $(document).ready(function() {
            $("#show_hide_password a").on('click', function(event) {
                event.preventDefault();
                if ($('#show_hide_password input').attr("type") == "text") {
                    $('#show_hide_password input').attr('type', 'password');
                    $('#show_hide_password i').addClass("bx-hide");
                    $('#show_hide_password i').removeClass("bx-show");
                } else if ($('#show_hide_password input').attr("type") == "password") {
                    $('#show_hide_password input').attr('type', 'text');
                    $('#show_hide_password i').removeClass("bx-hide");
                    $('#show_hide_password i').addClass("bx-show");
                }
            });
        });

    </script>
    <!--app JS-->
</body>
</html>
