<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ env("APP_NAME") }} | Login</title>
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('logos/logonfenotas.png') }}">

<link rel="apple-touch-icon" href="{{ asset('logos/logonfenotas.png') }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        nfe: {
                            blue: '#11426f',
                            green: '#61bd4f',
                            slate: '#64748b'
                        }
                    },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }
                }
            }
        }
    </script>

    <style>
        body { 
            background: #f1f5f9;
            background-image: radial-gradient(at 0% 0%, rgba(97, 189, 79, 0.05) 0px, transparent 50%), radial-gradient(at 100% 100%, rgba(17, 66, 111, 0.05) 0px, transparent 50%);
            min-height: 100vh;
        }
        .premium-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .input-group:focus-within { border-color: #61bd4f; transform: translateY(-2px); }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-ui { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
    </style>
</head>

<body class="flex items-center justify-center p-4">

    <div class="w-full max-w-md animate-ui">
        
        <div class="flex flex-col items-center mb-10 group cursor-default">
            <div class="w-16 h-16 bg-nfe-blue rounded-[1.5rem] flex items-center justify-center shadow-2xl shadow-nfe-blue/20 group-hover:scale-110 group-hover:rotate-3 transition-all duration-500 mb-4">
                <i class='bx bxs-zap text-nfe-green text-4xl'></i>
            </div>
            <div class="text-center">
                <h1 class="text-4xl font-black text-nfe-blue leading-none tracking-tighter">
                    NFe<span class="text-nfe-green">Notas</span>
                </h1>
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-[4px] leading-none mt-2 block">
                    Gestão Inteligente
                </span>
            </div>
        </div>

        @if(session()->has('flash_sucesso'))
        <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-r-xl flex items-center shadow-sm">
            <i class='bx bxs-check-circle text-2xl mr-3'></i>
            <span class="text-sm font-bold">{{ session()->get('flash_sucesso') }}</span>
        </div>
        @endif

        @if(session()->has('flash_erro'))
        <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-r-xl flex items-center shadow-sm">
            <i class='bx bxs-error-circle text-2xl mr-3'></i>
            <span class="text-sm font-bold">{{ session()->get('flash_erro') }}</span>
        </div>
        @endif

        <div class="premium-card rounded-[2.5rem] p-1 shadow-2xl">
            <div class="bg-white/60 rounded-[2.3rem] p-8 md:p-10">
                
                <div id="form-login-container">
                    <div class="mb-8 text-center">
                        <h2 class="text-2xl font-bold text-nfe-blue">Painel de Acesso</h2>
                        <p class="text-slate-500 text-sm font-medium">Bem-vindo à sua gestão empresarial</p>
                    </div>

                    <form action="{{ route('login.request') }}" method="POST" class="space-y-5">
                        @csrf
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-nfe-blue/60 uppercase tracking-widest ml-1">Usuário</label>
                            <div class="input-group flex items-center bg-white border-2 border-slate-100 rounded-2xl transition-all duration-300">
                                <span class="pl-4 text-slate-400"><i class='bx bx-user text-xl'></i></span>
                                <input type="text" name="login" value="{{ session('login') ?? ($loginCookie ?? '') }}" required
                                    class="w-full px-4 py-3.5 bg-transparent outline-none font-semibold text-nfe-blue" placeholder="Seu usuário">
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-nfe-blue/60 uppercase tracking-widest ml-1">Senha</label>
                            <div class="input-group flex items-center bg-white border-2 border-slate-100 rounded-2xl transition-all duration-300">
                                <span class="pl-4 text-slate-400"><i class='bx bx-lock-alt text-xl'></i></span>
                                <input type="password" name="senha" id="senha" value="{{ $senhaCookie ?? '' }}" required
                                    class="w-full px-4 py-3.5 bg-transparent outline-none font-semibold text-nfe-blue" placeholder="••••••••">
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] px-1">
                            <label class="flex items-center cursor-pointer group">
                                <input type="checkbox" name="lembrar" class="hidden peer" @if(isset($lembrarCookie) && $lembrarCookie) checked @endif>
                                <div class="w-8 h-4 bg-slate-200 rounded-full peer-checked:bg-nfe-green transition-all relative">
                                    <div class="absolute top-0.5 left-0.5 bg-white w-3 h-3 rounded-full transition-all peer-checked:translate-x-4"></div>
                                </div>
                                <span class="ml-2 font-bold text-slate-500">Lembrar-me</span>
                            </label>
                            <button type="button" id="btn-forget" class="font-extrabold text-nfe-blue hover:text-nfe-green transition-colors">Esqueceu a senha?</button>
                        </div>

                        <button type="submit" class="w-full bg-nfe-blue text-white font-bold py-4 rounded-2xl shadow-lg hover:bg-slate-800 hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                            ENTRAR NO SISTEMA <i class='bx bx-right-arrow-alt text-xl'></i>
                        </button>
                    </form>
                </div>

                <div id="form-forget-container" class="hidden">
                    <div class="mb-8 text-center">
                        <div class="w-16 h-16 bg-green-50 text-nfe-green rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                            <i class='bx bx-mail-send'></i>
                        </div>
                        <h2 class="text-xl font-bold text-nfe-blue">Recuperar Senha</h2>
                        <p class="text-slate-500 text-sm">Enviaremos um link de recuperação.</p>
                    </div>

                    <form action="{{ route('recuperarSenha') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="input-group flex items-center bg-white border-2 border-slate-100 rounded-2xl">
                            <span class="pl-4 text-slate-400"><i class='bx bx-envelope text-xl'></i></span>
                            <input type="email" name="email" required class="w-full px-4 py-4 bg-transparent outline-none font-semibold text-nfe-blue" placeholder="E-mail cadastrado">
                        </div>
                        
                        <button type="submit" class="w-full bg-nfe-green text-white font-bold py-4 rounded-2xl shadow-lg hover:opacity-90 transition-all">
                            SOLICITAR NOVA SENHA
                        </button>
                        
                        <button type="button" id="btn-back" class="w-full text-slate-400 font-bold py-2 text-xs uppercase tracking-widest hover:text-nfe-blue transition-colors">
                            Voltar ao login
                        </button>
                    </form>
                </div>

            </div>
        </div>

        <div class="mt-8 space-y-4">
            <a href="/cadastro/plano" class="block w-full text-center py-4 bg-white/50 border-2 border-dashed border-nfe-green/30 text-nfe-green font-bold rounded-2xl hover:bg-nfe-green hover:text-white transition-all">
                Ainda não tem conta? <span class="underline">Cadastre sua empresa</span>
            </a>

            @if(env("APP_ENV") == "demo")
            <div class="bg-white/80 p-6 rounded-[2rem] shadow-sm">
                <p class="text-[10px] font-black text-center text-slate-400 uppercase tracking-widest mb-4">Acesso Rápido Demo</p>
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="fillLogin('usuario', '123')" class="py-2 px-3 text-xs font-bold border-2 border-slate-100 rounded-xl text-nfe-blue hover:border-nfe-blue transition-all">Super Admin</button>
                    <button onclick="fillLogin('mateus', '123456')" class="py-2 px-3 text-xs font-bold border-2 border-slate-100 rounded-xl text-nfe-blue hover:border-nfe-blue transition-all">Admin</button>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Alternar para Esqueci Senha
            $('#btn-forget').click(function() {
                $('#form-login-container').fadeOut(200, function() {
                    $('#form-forget-container').removeClass('hidden').fadeIn(200);
                });
            });

            // Voltar para Login
            $('#btn-back').click(function() {
                $('#form-forget-container').fadeOut(200, function() {
                    $('#form-login-container').fadeIn(200);
                });
            });
        });

        function fillLogin(user, pass) {
            $('input[name="login"]').val(user);
            $('input[name="senha"]').val(pass);
            // Pequeno delay para o usuário ver o preenchimento antes do submit
            setTimeout(() => {
                $('form[action*="login.request"]').submit();
            }, 300);
        }
    </script>
</body>
</html>