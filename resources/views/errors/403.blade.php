<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Acesso Restrito - NfeNotas</title>
    <style>
        .float-anim { animation: float 5s ease-in-out infinite alternate; }
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            100% { transform: translateY(20px) rotate(3deg); }
        }
        .delay-1 { animation-delay: 0.7s; }
        .delay-2 { animation-delay: 1.4s; }
        .delay-3 { animation-delay: 2.1s; }
    </style>
</head>
<body class="bg-[#0f172a] min-h-screen flex flex-col items-center justify-center p-6 antialiased font-sans">

    <!-- Logo NfeNotas -->
    <nav class="absolute top-8 left-8 flex items-center gap-2 opacity-80 hover:opacity-100 transition-opacity">
        <div class="bg-red-500 p-1.5 rounded-lg shadow-lg shadow-red-500/20">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
        </div>
        <span class="text-lg font-bold text-white tracking-tight">Nfe<span class="text-red-400">Notas</span></span>
    </nav>

    <div class="max-w-5xl w-full flex flex-col md:flex-row items-center justify-center gap-12 md:gap-24">
        
        <!-- Lado Esquerdo: SVG com tons de alerta/vermelho para 403 -->
        <div class="w-full md:w-1/2 flex justify-center order-2 md:order-1">
            <svg class="w-full max-w-[380px] filter drop-shadow-[0_20px_50px_rgba(239,68,68,0.1)]" viewBox="0 0 837 1045" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Vermelho Alerta -->
                <path class="float-anim" d="M353,9 L626.6,170 L626.6,487 L353,642 L79.3,487 L79.3,170 L353,9 Z" stroke="#ef4444" stroke-width="15" stroke-linecap="round" />
                <!-- Laranja/Amber -->
                <path class="float-anim delay-1" d="M78.5,529 L147,569.1 L147,648.3 L78.5,687 L10,648.3 L10,569.1 L78.5,529 Z" stroke="#f59e0b" stroke-width="12" />
                <!-- Slate -->
                <path class="float-anim delay-2" d="M773,186 L827,217.5 L827,279.6 L773,310 L719,279.6 L719,217.5 L773,186 Z" stroke="#94a3b8" stroke-width="12" />
                <!-- Rosa Profundo -->
                <path class="float-anim delay-3" d="M639,529 L773,607.8 L773,763.1 L639,839 L505,763.1 L505,607.8 L639,529 Z" stroke="#be123c" stroke-width="12" />
            </svg>
        </div>

        <!-- Lado Direito: Conteúdo -->
        <div class="w-full md:w-1/2 text-center md:text-left order-1 md:order-2">
            <h1 class="text-7xl md:text-8xl font-black mb-2 text-white opacity-20 italic">403</h1>
            <h2 class="text-3xl md:text-4xl font-bold mb-4 text-white leading-tight">
                Área restrita. <br><span class="text-red-400 text-2xl md:text-3xl">Acesso não autorizado.</span>
            </h2>
            <p class="text-slate-400 text-lg mb-8 leading-relaxed">
                Parece que você tentou acessar um lugar que exige permissões especiais. Se você acha que isso é um erro, tente falar com o suporte.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                <!-- Lógica de retorno -->
                <button onclick="window.history.length > 1 ? history.back() : window.location.href='/'" 
                    class="px-8 py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-white transition-all duration-300 font-medium">
                    Voltar de onde vim
                </button>
                
                <a href="{{ route('graficos.index') }}" 
                    class="px-8 py-3 rounded-xl bg-red-500 hover:bg-red-400 text-white font-bold transition-all duration-300 shadow-lg shadow-red-500/20">
                    Ir para a Home
                </a>
            </div>
        </div>
    </div>

    <!-- Rodapé -->
    <footer class="absolute bottom-8 text-slate-500 text-xs">
        &copy; 2026 NfeNotas. Segurança em primeiro lugar.
    </footer>

</body>
</html>