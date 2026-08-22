@php
    $configFooter = $default['config'];
    $nomeLojaFooter = $configFooter->nome ?: 'Loja Online';
    $telefoneFooter = trim((string) ($configFooter->telefone ?? ''));
    $telefoneWhatsFooter = preg_replace('/\D/', '', $telefoneFooter);
    if ($telefoneWhatsFooter !== '' && !str_starts_with($telefoneWhatsFooter, '55')) {
        $telefoneWhatsFooter = '55' . $telefoneWhatsFooter;
    }

    $cidadeFooter = optional($configFooter->cidade)->nome;
    $ufFooter = $configFooter->uf ?: optional($configFooter->cidade)->uf;
    $enderecoFooter = trim(implode(', ', array_filter([
        $configFooter->rua ?? null,
        $configFooter->numero ?? null,
        $configFooter->bairro ?? null,
    ])));
@endphp

<footer class="mt-16 border-t border-slate-200 bg-slate-950 text-slate-300">
    <div class="border-b border-white/10">
        <div class="container mx-auto grid gap-4 px-4 py-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-main">
                    <i class="fa fa-lock text-lg"></i>
                </div>
                <div>
                    <strong class="block text-sm text-white">Compra segura</strong>
                    <span class="text-[11px] text-slate-400">Seus dados protegidos</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-main">
                    <i class="fa fa-truck text-lg"></i>
                </div>
                <div>
                    <strong class="block text-sm text-white">Entrega acompanhada</strong>
                    <span class="text-[11px] text-slate-400">Opções disponíveis no checkout</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-main">
                    <i class="fa fa-credit-card text-lg"></i>
                </div>
                <div>
                    <strong class="block text-sm text-white">Pagamento protegido</strong>
                    <span class="text-[11px] text-slate-400">PIX, cartão e boleto habilitados</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-main">
                    <i class="fa fa-headphones text-lg"></i>
                </div>
                <div>
                    <strong class="block text-sm text-white">Atendimento</strong>
                    <span class="text-[11px] text-slate-400">Fale diretamente com a loja</span>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-10 lg:py-14">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
            <div class="lg:pr-8">
                <a href="{{ $rota }}" class="inline-flex rounded-2xl bg-white p-3">
                    <img src="{{ $configFooter->img }}" alt="{{ $nomeLojaFooter }}" class="h-12 max-w-[190px] object-contain">
                </a>

                <p class="mt-5 text-sm leading-7 text-slate-400">
                    Compre com praticidade e acompanhe seus pedidos diretamente pela nossa loja online.
                </p>

                <div class="mt-5 flex items-center gap-2">
                    @if(!empty($configFooter->link_facebook))
                        <a href="{{ $configFooter->link_facebook }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-main">
                            <i class="fa fa-facebook"></i>
                        </a>
                    @endif
                    @if(!empty($configFooter->link_instagram))
                        <a href="{{ $configFooter->link_instagram }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-main">
                            <i class="fa fa-instagram"></i>
                        </a>
                    @endif
                    @if(!empty($configFooter->link_twiter))
                        <a href="{{ $configFooter->link_twiter }}" target="_blank" rel="noopener noreferrer" aria-label="X / Twitter" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-main">
                            <i class="fa fa-twitter"></i>
                        </a>
                    @endif
                    @if($telefoneWhatsFooter !== '')
                        <a href="https://wa.me/{{ $telefoneWhatsFooter }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-green-600">
                            <i class="fa fa-whatsapp"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div>
                <h3 class="text-xs font-black uppercase tracking-[.18em] text-white">Navegação</h3>
                <nav class="mt-5 space-y-3 text-sm">
                    <a href="{{ $rota }}" class="block text-slate-400 transition hover:text-white">Início</a>
                    <a href="{{ $rota }}/categorias" class="block text-slate-400 transition hover:text-white">Categorias</a>
                    <a href="{{ $rota }}/curtidas" class="block text-slate-400 transition hover:text-white">Favoritos</a>
                    @if($default['postBlogExists'] ?? false)
                        <a href="{{ $rota }}/blog" class="block text-slate-400 transition hover:text-white">Blog</a>
                    @endif
                    <a href="{{ $rota }}/contato" class="block text-slate-400 transition hover:text-white">Contato</a>
                </nav>
            </div>

            <div>
                <h3 class="text-xs font-black uppercase tracking-[.18em] text-white">Minha conta</h3>
                <nav class="mt-5 space-y-3 text-sm">
                    <a href="{{ $rota }}/login" class="block text-slate-400 transition hover:text-white">Entrar / Minha conta</a>
                    <a href="{{ $rota }}/carrinho" class="block text-slate-400 transition hover:text-white">Meu carrinho</a>
                    <a href="{{ $rota }}/curtidas" class="block text-slate-400 transition hover:text-white">Meus favoritos</a>
                    @if($usuarioLogado)
                        <a href="{{ $rota }}/login" class="block text-slate-400 transition hover:text-white">Meus pedidos</a>
                        <a href="{{ $rota }}/logoff" class="block text-rose-300 transition hover:text-rose-200">Sair da conta</a>
                    @endif
                </nav>
            </div>

            <div>
                <h3 class="text-xs font-black uppercase tracking-[.18em] text-white">Fale conosco</h3>
                <div class="mt-5 space-y-4 text-sm text-slate-400">
                    @if($telefoneFooter !== '')
                        <div class="flex items-start gap-3">
                            <i class="fa fa-phone mt-1 text-main"></i>
                            <a href="tel:{{ preg_replace('/\D/', '', $telefoneFooter) }}" class="transition hover:text-white">{{ $telefoneFooter }}</a>
                        </div>
                    @endif

                    @if(!empty($configFooter->email))
                        <div class="flex items-start gap-3">
                            <i class="fa fa-envelope mt-1 text-main"></i>
                            <a href="mailto:{{ $configFooter->email }}" class="break-all transition hover:text-white">{{ $configFooter->email }}</a>
                        </div>
                    @endif

                    @if($enderecoFooter !== '')
                        <div class="flex items-start gap-3">
                            <i class="fa fa-map-marker mt-1 text-main"></i>
                            <span>
                                {{ $enderecoFooter }}
                                @if($cidadeFooter || $ufFooter)
                                    <br>{{ $cidadeFooter }}{{ $cidadeFooter && $ufFooter ? ' - ' : '' }}{{ $ufFooter }}
                                @endif
                                @if(!empty($configFooter->cep))
                                    <br>CEP {{ $configFooter->cep }}
                                @endif
                            </span>
                        </div>
                    @endif

                    @if(!empty($configFooter->funcionamento))
                        <div class="flex items-start gap-3">
                            <i class="fa fa-clock-o mt-1 text-main"></i>
                            <span>{{ $configFooter->funcionamento }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="container mx-auto flex flex-col gap-3 px-4 py-5 text-[11px] text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} {{ $nomeLojaFooter }}. Todos os direitos reservados.</p>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                @if(!empty($configFooter->politica_privacidade))
                    <span>Privacidade e proteção de dados</span>
                @endif
                <span>Loja online segura</span>
            </div>
        </div>
    </div>
</footer>