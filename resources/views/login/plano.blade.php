<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ env("APP_NAME") }} | Selecione seu Plano</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        nfe: {
                            blue: '#11426f',
                            green: '#61bd4f',
                            dark: '#020617'
                        }
                    },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }
                }
            }
        }
    </script>

    <style>
        body { 
            background-color: #020617;
            background-image: 
                radial-gradient(circle at 0% 0%, rgba(97, 189, 79, 0.12) 0%, transparent 40%),
                radial-gradient(circle at 100% 100%, rgba(17, 66, 111, 0.2) 0%, transparent 40%);
            min-height: 100vh;
        }

        .premium-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        }

        .premium-card:hover {
            border-color: rgba(97, 189, 79, 0.4);
            background: rgba(255, 255, 255, 0.04);
            transform: translateY(-12px) scale(1.01);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .animated-border {
            position: relative;
            background: rgba(2, 6, 23, 0.8) !important;
        }
        .animated-border::before {
            content: "";
            position: absolute;
            inset: -1px;
            background: linear-gradient(90deg, transparent, #61bd4f, transparent);
            z-index: -1;
            border-radius: 3rem;
            animation: moveBorder 3s linear infinite;
        }

        @keyframes moveBorder {
            0% { filter: hue-rotate(0deg); background-position: 0% 50%; }
            100% { filter: hue-rotate(360deg); background-position: 200% 50%; }
        }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #020617; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #61bd4f; }
    </style>
</head>

<body class="antialiased text-slate-200 overflow-x-hidden">

    <div class="container mx-auto px-6 py-12">
        
        <div class="flex justify-center mb-16" data-aos="fade-down">
            <div class="flex items-center gap-3 group cursor-pointer">
                <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-2xl group-hover:bg-nfe-green transition-all duration-500 group-hover:rotate-6">
                    <i class='bx bxs-zap text-nfe-blue text-2xl group-hover:text-white'></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-2xl font-black text-white leading-none tracking-tighter">
                        NFe<span class="text-nfe-green">Notas</span>
                    </span>
                    <span class="text-[9px] font-extrabold text-slate-500 uppercase tracking-[3px] leading-none mt-1">
                        SaaS Edition
                    </span>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto text-center mb-24" data-aos="fade-up" data-aos-delay="100">
            <div class="inline-flex items-center gap-2 py-1 px-3 rounded-full bg-nfe-green/10 border border-nfe-green/20 mb-6">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-nfe-green opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-nfe-green"></span>
                </span>
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-nfe-green">Soluções em Nuvem</span>
            </div>
            
            <h1 class="text-6xl md:text-7xl font-black mb-10 tracking-tighter leading-none text-white">
                {!! env("TITULOPLANO", "Escolha o poder da sua gestão") !!}
            </h1>
            
            <p class="text-slate-400 text-lg max-w-2xl mx-auto font-medium leading-relaxed">
                Potencialize sua operação com ferramentas de alta performance. 
                @if(env("PLANOAUTOMATICODIAS") > 0)
                    Comece hoje e ganhe <span class="text-white font-bold">{{env("PLANOAUTOMATICODIAS")}} dias</span> de acesso total.
                @endif
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
            @foreach($planos as $p)
            <div class="premium-card group relative rounded-[3rem] p-12 flex flex-col {{ $loop->iteration == 2 ? 'animated-border' : '' }}" 
                 data-aos="fade-up" 
                 data-aos-delay="{{ $loop->iteration * 150 }}">
                
                @if($loop->iteration == 2)
                <div class="absolute -top-4 left-12 px-4 py-1.5 bg-nfe-green rounded-full shadow-lg shadow-nfe-green/20 z-20">
                    <span class="text-nfe-dark font-black text-[10px] uppercase tracking-tighter">Mais escolhido</span>
                </div>
                @endif

                <div class="mb-12">
                    <span class="text-nfe-green font-bold text-sm uppercase tracking-widest">{{$p->nome}}</span>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-5xl font-black text-white tracking-tighter">
                            R$ {{ number_format($p->valor, 0, ',', '.') }}
                        </span>
                        <span class="text-slate-500 font-semibold text-sm">/mês</span>
                    </div>
                </div>

                <div class="flex-grow mb-12">
                    <div class="space-y-5">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-6 italic">Funcionalidades:</p>
                        <div class="text-slate-300 font-medium text-sm leading-relaxed prose prose-invert prose-p:my-2">
                            {!! $p->descricao !!}
                        </div>
                    </div>
                </div>

                <a href="/cadastro?plano={{$p->id}}" 
                   class="relative overflow-hidden group/btn flex items-center justify-center w-full py-5 bg-white rounded-2xl transition-all duration-500 hover:scale-105 active:scale-95">
                    <span class="relative z-10 text-nfe-dark font-black uppercase tracking-widest text-xs transition-colors duration-500">
                        Ativar Plano
                    </span>
                    <div class="absolute inset-0 bg-nfe-green translate-y-full group-hover/btn:translate-y-0 transition-transform duration-500"></div>
                </a>
            </div>
            @endforeach
        </div>

        <div class="mt-32 pt-12 border-t border-white/5 flex flex-col md:flex-row items-center justify-between gap-8 opacity-60" data-aos="fade-up">
            <div class="flex items-center gap-6">
                <i class='bx bxs-shield-quarter text-4xl text-nfe-green'></i>
                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-widest">Ambiente Blindado</h4>
                    <p class="text-xs text-slate-500">Sua assinatura processada com segurança bancária.</p>
                </div>
            </div>
            <div class="flex gap-8 items-center grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition-all duration-500">
                <i class='bx bxl-visa text-5xl'></i>
                <i class='bx bxl-mastercard text-5xl'></i>
                <i class='bx bxs-bank text-4xl'></i>
                <span class="font-black text-xl italic tracking-tighter">PIX</span>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            easing: 'ease-out-quart'
        });
    </script>
</body>
</html>