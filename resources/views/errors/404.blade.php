<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Página não encontrada - NfeNotas</title>
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

    <nav class="absolute top-8 left-8 flex items-center gap-2 opacity-80 hover:opacity-100 transition-opacity">
        <div class="bg-cyan-600 p-1.5 rounded-lg">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
        </div>
        <span class="text-lg font-bold text-white tracking-tight">Nfe<span class="text-cyan-400">Notas</span></span>
    </nav>

    <div class="max-w-5xl w-full flex flex-col md:flex-row items-center justify-center gap-12 md:gap-24">
        
        <div class="w-full md:w-1/2 flex justify-center order-2 md:order-1">
            <svg class="w-full max-w-[380px] filter drop-shadow-2xl" viewBox="0 0 837 1045" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path class="float-anim" d="M353,9 L626.6,170 L626.6,487 L353,642 L79.3,487 L79.3,170 L353,9 Z" stroke="#0ea5e9" stroke-width="15" stroke-linecap="round" />
                <path class="float-anim delay-1" d="M78.5,529 L147,569.1 L147,648.3 L78.5,687 L10,648.3 L10,569.1 L78.5,529 Z" stroke="#22d3ee" stroke-width="12" />
                <path class="float-anim delay-2" d="M773,186 L827,217.5 L827,279.6 L773,310 L719,279.6 L719,217.5 L773,186 Z" stroke="#6366f1" stroke-width="12" />
                <path class="float-anim delay-3" d="M639,529 L773,607.8 L773,763.1 L639,839 L505,763.1 L505,607.8 L639,529 Z" stroke="#38bdf8" stroke-width="12" />
            </svg>
        </div>

        <div class="w-full md:w-1/2 text-center md:text-left order-1 md:order-2">
            <h1 class="text-7xl md:text-8xl font-black mb-2 text-white opacity-20">404</h1>
            <h2 class="text-3xl md:text-4xl font-bold mb-4 text-white">
                Ih, parece que nos perdemos.
            </h2>
            <p class="text-slate-400 text-lg mb-8 leading-relaxed">
                Não conseguimos encontrar a página que você digitou. Pode ser que o link esteja quebrado ou a página tenha mudado de lugar.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                <button onclick="window.history.length > 1 ? history.back() : window.location.href='/'" 
                    class="px-8 py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-white transition-all duration-300 font-medium">
                    Voltar para onde estava
                </button>
                
                <a href="/" 
                    class="px-8 py-3 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-[#0f172a] font-bold transition-all duration-300 shadow-lg shadow-cyan-500/20">
                    Ir para o Início
                </a>
            </div>
        </div>
    </div>

    <footer class="absolute bottom-8 text-slate-500 text-xs">
        &copy; 2026 NfeNotas. Todos os direitos reservados.
    </footer>

</body>
</html>