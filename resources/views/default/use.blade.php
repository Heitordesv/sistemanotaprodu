@extends('default.layout')

@section('content')

{{-- ======================================
     ESTILOS E ICONES
====================================== --}}
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet"
      href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

@php
    // Saudação conforme horário
    $hora = date('H');
    if ($hora < 12) {
        $saudacao = "Bom dia";
        $emoji = "🌅";
    } elseif ($hora < 18) {
        $saudacao = "Boa tarde";
        $emoji = "🌤️";
    } else {
        $saudacao = "Boa noite";
        $emoji = "🌙";
    }
@endphp


{{-- ======================================
 🎉 ANIMAÇÃO DE ANIVERSÁRIO
====================================== --}}
@if(!empty($aniversarioHoje) && !empty($funcionario))

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<style>
    /* Balões flutuando */
    @keyframes float {
        0%   { transform: translateY(0px); }
        50%  { transform: translateY(-12px); }
        100% { transform: translateY(0px); }
    }
    .float { animation: float 3s ease-in-out infinite; }

    /* Banner fixo centralizado */
    .banner-aniversario {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: 95%;
        max-width: 900px;
        z-index: 9999;
    }

    /* Efeito brilho */
    .shine {
        position: absolute;
        top: 0;
        left: -150%;
        width: 50%;
        height: 100%;
        background: rgba(255,255,255,0.25);
        transform: skewX(-20deg);
        animation: shine 3s infinite;
    }

    @keyframes shine {
        0%   { left: -150%; }
        60%  { left: 150%; }
        100% { left: 150%; }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
    // Solta confetes ao carregar página
    window.onload = () => {
        confetti({
            particleCount: 200,
            spread: 120,
            origin: { y: 0.4 }
        });
    };
</script>

<div class="banner-aniversario animate__animated animate__fadeInDown">
    <div class="relative bg-gradient-to-r from-purple-600 via-pink-500 to-rose-500
                text-white p-6 rounded-2xl shadow-2xl border border-white/20 text-center overflow-hidden">

        <div class="shine"></div>

        <h2 class="text-3xl font-extrabold mb-1 drop-shadow">
            🎉 Feliz Aniversário, {{ $funcionario->nome }}! 🎂
        </h2>

        <p class="text-md opacity-90 mb-3 font-medium">
            Hoje celebramos você! Aproveite seu dia especial! ✨
        </p>

        <div class="flex justify-center space-x-4">
            <span class="text-4xl float">🎈</span>
            <span class="text-4xl float" style="animation-delay: .4s">🎈</span>
            <span class="text-4xl float" style="animation-delay: .8s">🎈</span>
        </div>
    </div>
</div>

@endif


{{-- ======================================
 ⭐ CONTEÚDO PRINCIPAL
====================================== --}}
<div class="flex justify-center items-center py-24 px-12 bg-gray-50 min-h-screen">
    <div class="w-full max-w-12xl">

        <div class="bg-white p-12 rounded-3xl shadow-xl border border-gray-100 text-center
                    hover:shadow-2xl hover:border-indigo-100 transition duration-300">

            {{-- FOTO DO USUÁRIO --}}
            <div class="flex justify-center mb-8 relative">

                @php
                    $imgSrc = $usuario->img
                        ? "/uploads/usuarios/{$usuario->img}"
                        : "/logos/user.png";
                @endphp

                <img src="{{ $imgSrc }}" width="140" height="140"
                     class="rounded-full object-cover border-4 border-white ring-4 ring-indigo-200 shadow-xl"
                     alt="Foto do Usuário">

                <div class="absolute bottom-4 right-4 bg-green-500 p-2 rounded-full
                            border-4 border-white shadow-lg animate-pulse">
                    <i class='bx bx-check text-white text-xl'></i>
                </div>
            </div>

            {{-- TÍTULO --}}
            <h2 class="text-4xl font-black mb-1 text-gray-800 flex items-center justify-center">
                <i class='bx bx-check-shield text-green-500 mr-3 text-4xl'></i>
                Autenticação Concluída
            </h2>

            {{-- SAUDAÇÃO --}}
            <p class="text-2xl font-bold mb-6 text-indigo-700">
                {{ $saudacao }}, {{ $usuario->nome }} {{ $emoji }}
            </p>

            <hr class="my-8 border-gray-100">

            {{-- DADOS EMPRESA --}}
            <div class="bg-indigo-50 p-6 rounded-xl border border-indigo-200 shadow-inner">
                <p class="text-sm uppercase tracking-widest font-extrabold text-indigo-500 mb-1">
                    Acesso da Empresa
                </p>

                <p class="text-3xl font-extrabold text-indigo-900">
                    {{ $usuario->empresa->razao_social ?? 'Empresa não encontrada' }}
                </p>
            </div>

            <p class="text-lg mt-8 mb-8 text-gray-600">
                Sua sessão foi iniciada com sucesso. Escolha uma opção abaixo.
            </p>

            {{-- BOTÃO PRINCIPAL --}}
            @if(session('user_logged')['adm'])
                <a href="{{ url('/graficos') }}"
                   class="w-full sm:w-2/3 md:w-1/2 lg:w-2/5 mx-auto px-8 py-4 mb-6
                          rounded-full font-bold shadow-lg bg-indigo-600 text-white
                          hover:bg-indigo-700 transition flex items-center justify-center">
                    <i class='bx bx-trending-up text-2xl mr-3'></i>
                    Acessar Painel Principal
                </a>
            @else
                <a href="{{ url('/produtos') }}"
                   class="w-full sm:w-2/3 md:w-1/2 lg:w-2/5 mx-auto px-8 py-4 mb-6
                          rounded-full font-bold shadow-lg bg-indigo-600 text-white
                          hover:bg-indigo-700 transition flex items-center justify-center">
                    <i class='bx bx-grid-alt text-2xl mr-3'></i>
                    Área do Usuário
                </a>
            @endif

            <hr class="my-6 border-gray-100">

            {{-- AÇÕES SECUNDÁRIAS --}}
            <div class="flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-4 mt-4">

                <a href="{{ route('usuarios.edit', $usuario) }}"
                   class="flex items-center justify-center px-4 py-2 rounded-full bg-amber-100 text-amber-700
                          hover:bg-amber-200 transition shadow-sm font-medium">
                    <i class='bx bx-cog text-xl mr-2'></i> Configurar Perfil
                </a>

                <a href="{{ route('usuarios.historico', $usuario) }}"
                   class="flex items-center justify-center px-4 py-2 rounded-full bg-indigo-100 text-indigo-700
                          hover:bg-indigo-200 transition shadow-sm font-medium">
                    <i class='bx bx-time text-xl mr-2'></i> Acessos Recentes
                </a>
            </div>

         

        </div>
    </div>
</div>

@endsection
