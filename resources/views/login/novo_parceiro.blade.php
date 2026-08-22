<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{env("APP_NAME")}} - Seja nosso Parceiro</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/assets/css/select2.min.css" rel="stylesheet" />
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .glass-effect { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .gradient-text { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .select2-container--default .select2-selection--single { border: 1px solid #e2e8f0 !important; height: 50px !important; padding: 10px !important; border-radius: 0.75rem !important; }
    </style>
</head>

<body class="antialiased text-slate-900">

    <div class="min-h-screen flex flex-col lg:flex-row">
        
        <div class="lg:w-1/2 p-8 lg:p-20 flex flex-col justify-center bg-white">
            <div class="max-w-xl mx-auto lg:mx-0">
                <img width="160" src="/assets/images/logo1.jpg" alt="Logo" class="mb-10">
                
                <h1 class="text-4xl lg:text-6xl font-extrabold tracking-tight mb-6 leading-tight">
                    Sua empresa no <br><span class="gradient-text">pr&oacute;ximo n&iacute;vel.</span>
                </h1>
                
                <p class="text-lg text-slate-600 mb-10 leading-relaxed">
                    Junte-se &agrave; nossa rede de parceiros e tenha acesso a uma plataforma completa de gest&atilde;o e escala. O cadastro leva menos de 2 minutos.
                </p>

                <div class="space-y-4 mb-10">
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="w-12 h-12 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                            <i class='bx bx-rocket text-2xl'></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800">Ativa&ccedil;&atilde;o Imediata</h4>
                            <p class="text-sm text-slate-500">Cadastre e comece a usar no mesmo instante.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500 flex items-center justify-center text-white shadow-lg">
                            <i class='bx bx-shield-quarter text-2xl'></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800">Seguran&ccedil;a de Dados</h4>
                            <p class="text-sm text-slate-500">Criptografia de ponta a ponta em todas as opera&ccedil;&otilde;es.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-slate-400 text-sm font-semibold uppercase tracking-widest">
                    <span class="flex -space-x-2 mr-2">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 border-2 border-white"></div>
                        <div class="w-8 h-8 rounded-full bg-indigo-200 border-2 border-white"></div>
                        <div class="w-8 h-8 rounded-full bg-indigo-300 border-2 border-white"></div>
                    </span>
                    +500 parceiros ativos
                </div>
            </div>
        </div>

        <div class="lg:w-1/2 bg-slate-50 p-6 lg:p-20 flex items-center justify-center relative overflow-hidden">
            <div class="w-full max-w-lg relative z-10">
                <div class="glass-effect p-8 lg:p-10 rounded-[2.5rem] shadow-2xl">
                    <div class="text-center lg:text-left mb-8">
                        <h3 class="text-2xl font-bold text-slate-800">Comece agora</h3>
                        <p class="text-slate-500 text-sm">Preencha os dados da sua empresa abaixo.</p>
                    </div>

                    {!!Form::open()->post()->route('cadastro.storeEmpresa')->attrs(['class' => 'space-y-5'])!!}
                        <input type="hidden" name="contador" value="1">

                        <div class="grid grid-cols-12 gap-3">
                            <div class="col-span-9 lg:col-span-10">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1 block">Documento (CPF/CNPJ)</label>
                                {!!Form::tel('cpf_cnpj', '')->attrs(['class' => 'cpf_cnpj w-full px-5 py-3 rounded-2xl border border-slate-200 focus:border-indigo-500 outline-none font-bold text-slate-700', 'placeholder' => '00.000.000/0000-00'])!!}
                            </div>
                            <div class="col-span-3 lg:col-span-2">
                                <label class="block mb-1">&nbsp;</label>
                                <button type="button" id="btn-consulta" class="w-full h-[50px] bg-slate-900 text-white rounded-2xl hover:bg-indigo-600 transition-all flex items-center justify-center">
                                    <i class="bx bx-search text-xl"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1 block">Raz&atilde;o Social</label>
                            {!!Form::text('razao_social', '')->attrs(['class' => 'w-full px-5 py-3 rounded-2xl border border-slate-200 outline-none font-bold text-slate-700'])!!}
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1 block">WhatsApp</label>
                                {!!Form::tel('telefone', '')->attrs(['class' => 'fone w-full px-5 py-3 rounded-2xl border border-slate-200 outline-none font-bold text-slate-700'])!!}
                            </div>
                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1 block">Cidade Sede</label>
                                {!!Form::select('cidade_id', '')->attrs(['class' => 'select2 w-full'])!!}
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1 block">Usu&aacute;rio de Acesso</label>
                                {!!Form::text('login', '')->attrs(['class' => 'w-full px-5 py-3 rounded-2xl border border-slate-200 outline-none font-bold text-slate-700'])!!}
                            </div>
                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1 block">Senha</label>
                                <input type="password" name="senha" class="w-full px-5 py-3 rounded-2xl border border-slate-200 outline-none font-bold text-slate-700">
                            </div>
                        </div>

                        <div class="pt-6 text-center">
                            <button type="submit" class="w-full py-5 bg-indigo-600 text-white rounded-2xl font-black uppercase tracking-widest text-sm shadow-xl hover:bg-indigo-700 hover:-translate-y-1 transition-all">
                                Finalizar meu Cadastro
                            </button>
                            
                            <p class="mt-6">
                                <a href="/login" class="text-sm font-bold text-slate-400">
                                    J&aacute; &eacute; parceiro? <span class="text-indigo-600 underline">Fazer Login</span>
                                </a>
                            </p>
                        </div>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>



    <script src="/assets/js/jquery.min.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js'></script>
    <script type="text/javascript" src="/js/jquery.mask.min.js"></script>
    <script src="/assets/js/select2.min.js"></script>
    <script src="/js/cadastroEmpresa.js"></script>
    <script type="text/javascript">
        let prot = window.location.protocol;
        let host = window.location.host;
        const path_url = prot + "//" + host + "/";

    </script>
    <script src="/assets/js/jquery.min.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js'></script>
    <script type="text/javascript" src="/js/jquery.mask.min.js"></script>
    <script src="/assets/js/select2.min.js"></script>
    <script src="/assets/js/app.js"></script>
    <script src="/js/theme.js"></script>

    <script src="/js/cadastroEmpresa.js"></script>
    {{-- <script src="/js/main.js"></script> --}}

</body>
</html>
