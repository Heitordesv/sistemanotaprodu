<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ env("APP_NAME") }} | Ative sua Gestão Inteligente</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; color: #1e293b; overflow-x: hidden; }
        
        /* Fundo decorativo */
        .bg-pattern {
            position: absolute; top: 0; left: 0; width: 100%; height: 500px;
            background: radial-gradient(circle at 10% 20%, rgba(34, 197, 94, 0.08) 0%, rgba(255, 255, 255, 0) 60%);
            z-index: -1;
        }

        /* Inputs modernos */
        .input-field {
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 14px 16px; color: #1e293b; transition: all 0.2s ease; width: 100%; outline: none;
        }
        .input-field:focus { border-color: #22c55e; background: #ffffff; box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1); }
        
        .label-text {
            display: block; font-size: 11px; font-weight: 800; color: #94a3b8;
            margin-bottom: 6px; margin-left: 2px; text-transform: uppercase; letter-spacing: 1px;
        }

        /* Botão Premium */
        .btn-primary {
            background: #0f172a; color: #ffffff; font-weight: 700; padding: 18px;
            border-radius: 14px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase; letter-spacing: 1px; width: 100%;
            box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.1); border: none; cursor: pointer;
        }
        .btn-primary:hover { background: #22c55e; transform: translateY(-2px); box-shadow: 0 20px 25px -5px rgba(34, 197, 94, 0.2); }

        /* Custom Scrollbar Modal */
        .modal-content::-webkit-scrollbar { width: 6px; }
        .modal-content::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        /* Select2 Style Overrides */
        .select2-container--bootstrap4 .select2-selection {
            border-radius: 12px !important;
            height: 52px !important;
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            display: flex !important;
            align-items: center !important;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 relative">
    <div class="bg-pattern"></div>

    <div id="modalTermos" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="toggleModal()"></div>
        <div class="bg-white w-full max-w-2xl rounded-[32px] shadow-2xl relative z-10 overflow-hidden flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="text-xl font-bold text-slate-900 font-inter">Segurança e Transparência</h3>
                <button type="button" onclick="toggleModal()" class="text-slate-400 hover:text-slate-600 transition-colors border-none bg-transparent cursor-pointer">
                    <i class='bx bx-x text-3xl'></i>
                </button>
            </div>
            <div class="p-8 overflow-y-auto modal-content text-slate-600 leading-relaxed font-inter">
                <h4 class="text-slate-900 font-bold mb-2 italic text-sm">Sua privacidade é nossa prioridade.</h4>
                <p class="mb-6">Operamos em total conformidade com a <strong>LGPD (Lei nº 13.709/2018)</strong>. Seus dados fiscais e empresariais são criptografados e utilizados exclusivamente para a emissão de documentos e automação da sua gestão.</p>
                <h4 class="text-slate-900 font-bold mb-2">Garantia de Teste</h4>
                <p>Ao iniciar seus 10 dias grátis, você tem acesso total às ferramentas contratadas sem qualquer compromisso de permanência ou taxas ocultas.</p>
            </div>
            <div class="p-6 border-t border-slate-100 bg-slate-50 text-right">
                <button type="button" onclick="toggleModal()" class="bg-slate-900 text-white font-bold py-3 px-8 rounded-xl hover:bg-green-600 transition-all cursor-pointer">Estou de acordo</button>
            </div>
        </div>
    </div>

    <div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        
        <div class="pr-8">
            
            <div class="flex items-center gap-2 mb-10 group cursor-default">
                <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center shadow-xl shadow-slate-200 group-hover:scale-110 group-hover:bg-green-600 transition-all duration-300">
                    <i class='bx bxs-zap text-green-400 text-2xl group-hover:text-white'></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-2xl font-black text-slate-900 leading-none tracking-tighter">
                        NFe<span class="text-green-500">Notas</span>
                    </span>
                    <span class="text-[9px] font-extrabold text-slate-400 uppercase tracking-[3px] leading-none mt-1">
                        Gestão Fiscal Inteligente
                    </span>
                </div>
            </div>

            <h1 class="text-5xl lg:text-6xl font-black text-slate-900 leading-[1.1] mb-6 tracking-tight">
                Escale seu negócio <br> sem o peso da <span class="text-green-500 underline decoration-green-200 underline-offset-8 italic">burocracia.</span>
            </h1>
            
            <p class="text-slate-500 text-lg leading-relaxed mb-10 max-w-md font-medium">
                Automatize sua emissão de notas e foque no que realmente importa: **crescer e vender mais.**
            </p>

            <div class="space-y-6">
                <div class="flex items-start gap-4">
                    <div class="bg-green-100 p-2 rounded-lg"><i class='bx bx-check-double text-green-600 text-2xl'></i></div>
                    <div>
                        <h4 class="font-bold text-slate-800">Ativação em Minutos</h4>
                        <p class="text-sm text-slate-500">Cadastro rápido para você não perder tempo.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="bg-blue-100 p-2 rounded-lg"><i class='bx bx-headphone text-blue-600 text-2xl'></i></div>
                    <div>
                        <h4 class="font-bold text-slate-800">Suporte Premium</h4>
                        <p class="text-sm text-slate-500">Especialistas prontos para te ajudar via WhatsApp.</p>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex items-center gap-4 p-4 bg-slate-50 rounded-2xl w-fit border border-slate-100">
                <div class="flex -space-x-3">
                    <img src="https://i.pravatar.cc/100?u=12" class="w-10 h-10 rounded-full border-2 border-white">
                    <img src="https://i.pravatar.cc/100?u=22" class="w-10 h-10 rounded-full border-2 border-white">
                    <img src="https://i.pravatar.cc/100?u=43" class="w-10 h-10 rounded-full border-2 border-white">
                </div>
                <span class="text-xs text-slate-600 font-bold uppercase tracking-wider">+1.200 empresas automatizadas</span>
            </div>
        </div>

        <div class="bg-white p-10 rounded-[40px] shadow-2xl shadow-slate-200 border border-slate-100 relative">
            <div class="mb-8">
                <h2 class="text-2xl font-extrabold text-slate-900">Comece Agora</h2>
                <p class="text-slate-400 text-sm font-medium italic">Seus primeiros 10 dias são por nossa conta.</p>
            </div>

            {!! Form::open()->post()->route('cadastro.storeEmpresa') !!}
                <div class="space-y-5">
                    <div>
                        <label class="label-text">CNPJ da Empresa</label>
                        <div class="flex gap-2">
                            {!! Form::tel('cpf_cnpj', '')->attrs(['class' => 'cpf_cnpj input-field font-bold', 'placeholder' => '00.000.000/0001-00'])->required() !!}
                            <button type="button" id="btn-consulta" class="bg-slate-900 text-white px-5 rounded-xl hover:bg-green-500 transition-all flex items-center justify-center min-w-[56px]">
                                <i class='bx bx-search-alt-2 text-xl'></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="label-text">Razão Social</label>
                        {!! Form::text('razao_social', '')->required()->attrs(['class' => 'input-field', 'placeholder' => 'Nome oficial da empresa']) !!}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text">WhatsApp para Contato</label>
                            {!! Form::tel('telefone', '')->required()->attrs(['class' => 'fone input-field', 'placeholder' => '(00) 0.0000-0000']) !!}
                        </div>
                        <div>
                            <label class="label-text">Sua Cidade</label>
                            {!! Form::select('cidade_id', '')->required()->attrs(['class' => 'select2']) !!}
                        </div>
                    </div>

                    <div>
                        <label class="label-text">E-mail Corporativo</label>
                        {!! Form::text('email', '')->type('email')->required()->attrs(['class' => 'input-field', 'placeholder' => 'contato@empresa.com']) !!}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text">Defina um Usuário</label>
                            {!! Form::text('login', '')->required()->attrs(['class' => 'input-field', 'placeholder' => 'ex: admin']) !!}
                        </div>
                        <div class="relative">
                            <label class="label-text">Crie uma Senha</label>
                            <input required type="password" name="senha" class="input-field" placeholder="••••••••">
                        </div>
                    </div>

                    <input type="hidden" value="{{$plano}}" name="plano">

                    <button type="submit" class="btn-primary mt-4 group">
                        Ativar meu acesso gratuito <i class='bx bx-right-arrow-alt align-middle ml-1 group-hover:translate-x-1 transition-transform'></i>
                    </button>
                    
                    <div class="text-center pt-2">
                         <p class="text-slate-400 text-xs">
                            Ao prosseguir, você concorda com nossos <br>
                            <a href="javascript:void(0)" onclick="toggleModal()" class="text-slate-600 font-bold hover:text-green-600 underline">Termos de Uso e Privacidade</a>.
                        </p>
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </div>

    <script src="/assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="/js/jquery.mask.min.js"></script>
    <script src="/assets/js/select2.min.js"></script>
    <script src="/js/cadastroEmpresa.js"></script>

    <script>
        // Configurações Globais
        const path_url = window.location.protocol + "//" + window.location.host + "/";

        // Gerenciamento do Modal
        function toggleModal() {
            const modal = document.getElementById('modalTermos');
            if (modal) {
                const isHidden = modal.classList.contains('hidden');
                modal.classList.toggle('hidden', !isHidden);
                modal.classList.toggle('flex', isHidden);
                document.body.style.overflow = isHidden ? 'hidden' : 'auto';
            }
        }

        $(document).ready(function() {
            // Máscaras de Input
            $('.fone').mask('(00) 00000-0000');
            $('.cpf_cnpj').mask('00.000.000/0000-00');
            
            // Inicialização Select2
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap4',
                    placeholder: "Selecione sua cidade..."
                });
            }

            // Exibir/Esconder Senha (Se necessário implementar o ícone no HTML posterior)
            $("#show_hide_password a").on('click', function(e) {
                e.preventDefault();
                let input = $(this).siblings('input');
                let icon = $(this).find('i');
                if (input.attr("type") == "text") {
                    input.attr('type', 'password');
                    icon.addClass("bx-hide").removeClass("bx-show");
                } else {
                    input.attr('type', 'text');
                    icon.removeClass("bx-hide").addClass("bx-show");
                }
            });
        });
    </script>
</body>
</html>