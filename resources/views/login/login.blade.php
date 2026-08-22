<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
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
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        body { 
            background: #f1f5f9;
            background-image: 
                radial-gradient(at 0% 0%, rgba(97, 189, 79, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(17, 66, 111, 0.05) 0px, transparent 50%);
            min-height: 100vh;
        }

        /* Logo em HTML Refinada */
        .logo-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .icon-canvas {
            position: relative;
            width: 75px;
            height: 85px;
        }

        .paper-element {
            position: absolute;
            width: 50px;
            height: 65px;
            background: #11426f;
            border-radius: 6px 18px 6px 6px;
            left: 0;
            top: 0;
            box-shadow: 0 10px 15px -3px rgba(17, 66, 111, 0.2);
        }

        .paper-element::before {
            content: '';
            position: absolute;
            top: 15px; left: 10px;
            width: 30px; height: 3px;
            background: rgba(255,255,255,0.15);
            box-shadow: 0 10px 0 rgba(255,255,255,0.15), 0 20px 0 rgba(255,255,255,0.15);
        }

        .check-element {
            position: absolute;
            width: 42px;
            height: 42px;
            background: #61bd4f;
            bottom: 5px;
            right: 0;
            border-radius: 12px;
            border: 4px solid #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 16px rgba(97, 189, 79, 0.3);
            transform: rotate(-3deg);
        }

        /* Efeito de Vidro no Card */
        .premium-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(17, 66, 111, 0.12);
        }

        .input-group:focus-within {
            border-color: #61bd4f;
            transform: translateY(-2px);
        }

        /* Animação de Entrada */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-ui { animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1); }

        /* Botão com Brilho */
        .btn-shine {
            position: relative;
            overflow: hidden;
        }
        .btn-shine::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            transition: 0.5s;
        }
        .btn-shine:hover::after {
            left: 100%;
        }
    </style>
    
    <title>NFE NOTAS | Gestão Empresarial</title>
</head>

<body class="flex items-center justify-center p-6 text-slate-800">

    <div class="w-full max-w-md animate-ui">
        
        <div class="logo-box mb-10">
            <div class="icon-canvas">
                <div class="paper-element"></div>
                <div class="check-element">
                    <i class='bx bx-check text-white text-2xl font-bold'></i>
                </div>
            </div>
            <div class="text-center">
                <h1 class="text-4xl font-[900] tracking-tighter flex gap-1 items-baseline">
                    <span class="text-nfe-blue">NFE</span>
                    <span class="text-nfe-green">NOTAS</span>
                </h1>
                <p class="text-[11px] font-extrabold text-nfe-blue/60 uppercase tracking-[4px] mt-1">
                    Software e Gestão Empresarial
                </p>
            </div>
        </div>

        <div class="premium-card rounded-[2.5rem] p-1 shadow-sm">
            <div class="bg-white/40 rounded-[2.3rem] p-8 md:p-10">
                
                <div id="form-login">
                    <div class="mb-10 text-center">
                        <h2 class="text-2xl font-bold text-nfe-blue tracking-tight">Painel de Acesso</h2>
                        <p class="text-slate-500 text-sm mt-2 font-medium">Bem-vindo ao futuro da sua gestão.</p>
                    </div>

                    <form action="{{ route('login.request') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-nfe-blue/70 uppercase tracking-widest ml-1">Usuário</label>
                            <div class="input-group relative flex items-center bg-white border-2 border-slate-100 rounded-2xl transition-all duration-300">
                                <span class="pl-5 text-slate-400">
                                    <i class='bx bx-user-circle text-2xl'></i>
                                </span>
                                <input type="text" name="login" required
                                    class="w-full px-4 py-4 bg-transparent outline-none font-semibold text-nfe-blue placeholder:text-slate-300"
                                    placeholder="Seu nome de usuário">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-nfe-blue/70 uppercase tracking-widest ml-1">Senha</label>
                            <div class="input-group relative flex items-center bg-white border-2 border-slate-100 rounded-2xl transition-all duration-300" id="show_hide_password">
                                <span class="pl-5 text-slate-400">
                                    <i class='bx bx-lock-alt text-2xl'></i>
                                </span>
                                <input type="password" name="senha" id="senha" required
                                    class="w-full pl-4 pr-12 py-4 bg-transparent outline-none font-semibold text-nfe-blue placeholder:text-slate-300"
                                    placeholder="••••••••">
                                <button type="button" class="absolute right-4 text-slate-400 hover:text-nfe-blue">
                                    <i class='bx bx-hide text-xl'></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between px-1">
                            <label class="flex items-center cursor-pointer group">
                                <input type="checkbox" name="lembrar" class="hidden peer">
                                <div class="w-10 h-5 bg-slate-200 rounded-full peer-checked:bg-nfe-green transition-all relative">
                                    <div class="absolute top-1 left-1 bg-white w-3 h-3 rounded-full transition-all peer-checked:translate-x-5"></div>
                                </div>
                                <span class="ml-3 text-xs font-bold text-slate-500 group-hover:text-nfe-blue transition-colors">Salvar acesso</span>
                            </label>
                            <button type="button" id="forget-password" class="text-xs font-extrabold text-nfe-blue hover:text-nfe-green transition-all border-b-2 border-transparent hover:border-nfe-green">
                                Esqueceu a senha?
                            </button>
                        </div>

                        <button type="submit" class="btn-shine w-full bg-nfe-blue text-white font-bold py-5 rounded-2xl shadow-xl shadow-blue-900/20 transition-all hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-3">
                            ENTRAR AGORA <i class='bx bx-right-arrow-alt text-2xl'></i>
                        </button>
                    </form>
                </div>

                <div id="forget-form" class="hidden text-center">
                    <div class="mb-8">
                        <div class="w-20 h-20 bg-green-50 text-nfe-green rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class='bx bx-mail-send text-4xl'></i>
                        </div>
                        <h3 class="text-xl font-bold text-nfe-blue">Recuperar Senha</h3>
                        <p class="text-sm text-slate-400 mt-2">Você receberá um link em seu e-mail.</p>
                    </div>
                    <form action="#" class="space-y-4">
                        <input type="email" placeholder="Digite seu e-mail" class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl outline-none focus:border-nfe-green">
                        <button class="w-full bg-nfe-green text-white font-bold py-4 rounded-2xl">ENVIAR INSTRUÇÕES</button>
                        <button type="button" id="back-btn" class="w-full text-slate-400 font-bold py-2 text-xs uppercase tracking-widest hover:text-nfe-blue">
                             Voltar ao login
                        </button>
                    </form>
                </div>

            </div>
        </div>
        
        <div class="mt-12 flex flex-col items-center gap-8">
            <div class="flex gap-6">
                <a href="https://wa.me/5551992846772" target="_blank" class="group relative flex items-center justify-center w-14 h-14 bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 text-slate-400 hover:text-green-500">
                    <i class='bx bxl-whatsapp text-3xl'></i>
                    <span class="absolute -top-10 scale-0 group-hover:scale-100 transition-all bg-nfe-blue text-white text-[10px] py-1 px-3 rounded-lg font-bold uppercase tracking-widest">WhatsApp</span>
                </a>
                <a href="https://www.instagram.com/nfenotas/" target="_blank" class="group relative flex items-center justify-center w-14 h-14 bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 text-slate-400 hover:text-pink-500">
                    <i class='bx bxl-instagram text-3xl'></i>
                    <span class="absolute -top-10 scale-0 group-hover:scale-100 transition-all bg-nfe-blue text-white text-[10px] py-1 px-3 rounded-lg font-bold uppercase tracking-widest">Instagram</span>
                </a>
            </div>
            
            <div class="text-center space-y-1">
                <p class="text-[10px] font-black text-nfe-blue/40 tracking-[5px] uppercase">
                    © 2026 NFE NOTAS
                </p>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[2px]">
                    Excelência em Gestão Empresarial
                </p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle Password
            $("#show_hide_password button").on('click', function() {
                let input = $('#senha');
                let icon = $(this).find('i');
                if (input.attr("type") === "password") {
                    input.attr('type', 'text');
                    icon.removeClass("bx-hide").addClass("bx-show");
                } else {
                    input.attr('type', 'password');
                    icon.removeClass("bx-show").addClass("bx-hide");
                }
            });

            // Navigation
            $('#forget-password').click(function() {
                $('#form-login').fadeOut(200, () => $('#forget-form').removeClass('hidden').fadeIn(200));
            });
            $('#back-btn').click(function() {
                $('#forget-form').fadeOut(200, () => $('#form-login').fadeIn(200));
            });
        });
    </script>
</body>
</html>