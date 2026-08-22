@extends('ecommerce.default')
@section('content')

<style>
    .login-ux{max-width:980px;margin:42px auto 70px;padding:0 16px}
    .login-ux-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(360px,.85fr);gap:28px;align-items:stretch}
    .login-ux-benefits,.login-ux-card{background:#fff;border:1px solid #e5e7eb;border-radius:24px;box-shadow:0 14px 38px rgba(15,23,42,.07)}
    .login-ux-benefits{padding:36px;display:flex;flex-direction:column;justify-content:center;min-height:520px;position:relative;overflow:hidden}
    .login-ux-benefits:after{content:'';position:absolute;width:230px;height:230px;border-radius:50%;background:color-mix(in srgb,var(--main-color) 10%,transparent);right:-80px;bottom:-80px}
    .login-ux-kicker{font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.12em;color:var(--main-color);margin-bottom:12px}
    .login-ux-title{font-size:36px;line-height:1.08;font-weight:900;color:#111827;margin:0 0 14px}
    .login-ux-sub{font-size:15px;line-height:1.6;color:#64748b;margin:0 0 28px;max-width:520px}
    .login-ux-list{display:grid;gap:15px;position:relative;z-index:1}
    .login-ux-item{display:flex;gap:12px;align-items:flex-start;color:#334155}
    .login-ux-icon{width:34px;height:34px;border-radius:11px;background:color-mix(in srgb,var(--main-color) 12%,white);color:var(--main-color);display:flex;align-items:center;justify-content:center;flex:none;font-weight:900}
    .login-ux-item strong{display:block;color:#111827;font-size:14px;margin-bottom:2px}.login-ux-item span{font-size:12px;color:#64748b;line-height:1.45}
    .login-ux-card{padding:32px}
    .login-ux-card h1{font-size:27px;font-weight:900;color:#111827;margin:0 0 6px}.login-ux-card>p{font-size:13px;color:#64748b;margin:0 0 22px}
    .login-ux-alert{border-radius:13px;padding:12px 14px;margin-bottom:16px;font-size:13px;font-weight:700;line-height:1.45}.login-ux-alert.error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}.login-ux-alert.ok{background:#ecfdf5;border:1px solid #bbf7d0;color:#166534}
    .login-ux-field{margin-bottom:16px}.login-ux-label{display:flex;justify-content:space-between;align-items:center;gap:10px;font-size:12px;font-weight:900;color:#475569;margin-bottom:7px}.login-ux-input-wrap{position:relative}
    .login-ux-input{width:100%;height:50px;border:1px solid #dbe2ea;border-radius:13px;padding:0 46px 0 15px;background:#fff;color:#111827;font-size:14px;outline:none;transition:.2s ease}.login-ux-input:focus{border-color:var(--main-color);box-shadow:0 0 0 4px color-mix(in srgb,var(--main-color) 12%,transparent)}
    .login-ux-toggle{position:absolute;right:10px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:#94a3b8;cursor:pointer;width:32px;height:32px;border-radius:8px}.login-ux-toggle:hover{background:#f8fafc;color:#475569}
    .login-ux-submit{width:100%;height:52px;border:0;border-radius:13px;background:var(--main-color);color:#fff;font-weight:900;cursor:pointer;transition:.2s ease;display:flex;align-items:center;justify-content:center;gap:8px}.login-ux-submit:hover{filter:brightness(.92)}.login-ux-submit:disabled{opacity:.65;cursor:not-allowed}
    .login-ux-links{display:flex;justify-content:space-between;gap:14px;align-items:center;margin-top:16px;flex-wrap:wrap}.login-ux-links a,.login-ux-links button{font-size:12px;font-weight:800;color:#64748b;text-decoration:none;background:none;border:0;padding:0;cursor:pointer}.login-ux-links a:hover,.login-ux-links button:hover{color:var(--main-color)}
    .login-ux-trust{margin-top:22px;padding-top:18px;border-top:1px solid #f1f5f9;display:flex;gap:10px;align-items:flex-start;color:#64748b;font-size:11px;line-height:1.45}.login-ux-trust i{color:var(--main-color);margin-top:2px}
    @media(max-width:800px){.login-ux{margin-top:24px}.login-ux-grid{grid-template-columns:1fr}.login-ux-benefits{display:none}.login-ux-card{padding:24px}.login-ux-card h1{font-size:24px}}
</style>

<div class="login-ux">
    <div class="login-ux-grid">
        <section class="login-ux-benefits">
            <div class="login-ux-kicker">Sua conta na loja</div>
            <h2 class="login-ux-title">Entre para continuar sua compra com mais facilidade.</h2>
            <p class="login-ux-sub">Acesse seus pedidos, acompanhe pagamentos e entregas, reutilize seus endereços e mantenha seus dados salvos para as próximas compras.</p>

            <div class="login-ux-list">
                <div class="login-ux-item">
                    <div class="login-ux-icon"><i class="fa fa-shopping-bag"></i></div>
                    <div><strong>Acompanhe seus pedidos</strong><span>Veja pagamento, preparação, envio e entrega em um só lugar.</span></div>
                </div>
                <div class="login-ux-item">
                    <div class="login-ux-icon"><i class="fa fa-map-marker"></i></div>
                    <div><strong>Endereços salvos</strong><span>Escolha rapidamente onde quer receber sem preencher tudo novamente.</span></div>
                </div>
                <div class="login-ux-item">
                    <div class="login-ux-icon"><i class="fa fa-lock"></i></div>
                    <div><strong>Acesso protegido</strong><span>Sua sessão é renovada no login e vinculada à loja correta.</span></div>
                </div>
            </div>
        </section>

        <section class="login-ux-card">
            <div class="login-ux-kicker">Área do cliente</div>
            <h1>Acesse sua conta</h1>
            <p>Use o mesmo e-mail cadastrado nesta loja.</p>

            @if(session('flash_erro') || session('mensagem_erro'))
                <div class="login-ux-alert error">{{ session('flash_erro') ?: session('mensagem_erro') }}</div>
            @endif
            @if(session('flash_sucesso') || session('mensagem_sucesso'))
                <div class="login-ux-alert ok">{{ session('flash_sucesso') ?: session('mensagem_sucesso') }}</div>
            @endif
            @if($errors->any())
                <div class="login-ux-alert error">{{ $errors->first() }}</div>
            @endif

            <form method="post" id="login-ecommerce-form">
                @csrf
                <input type="hidden" value="{{$default['config']->empresa_id}}" name="empresa_id">

                <div class="login-ux-field">
                    <label class="login-ux-label" for="login-email">E-mail</label>
                    <div class="login-ux-input-wrap">
                        <input id="login-email" autocomplete="email" name="email" type="email" required autofocus value="{{ old('email') }}" class="login-ux-input" placeholder="voce@exemplo.com">
                    </div>
                </div>

                <div class="login-ux-field">
                    <label class="login-ux-label" for="login-password">Senha</label>
                    <div class="login-ux-input-wrap">
                        <input id="login-password" autocomplete="current-password" name="senha" type="password" required class="login-ux-input" placeholder="Digite sua senha">
                        <button type="button" id="toggle-login-password" class="login-ux-toggle" aria-label="Mostrar senha" title="Mostrar senha"><i class="fa fa-eye"></i></button>
                    </div>
                </div>

                <button type="submit" id="login-submit" class="login-ux-submit">
                    <i class="fa fa-sign-in"></i><span>Entrar na minha conta</span>
                </button>

                <div class="login-ux-links">
                    <a href="{{$rota}}/esquecisenha">Esqueci minha senha</a>
                    <a href="{{$rota}}">Voltar para a loja</a>
                    @if($default['config']->politica_privacidade != "")
                        <button type="button" data-toggle="modal" data-target="#modal-politica">Política de Privacidade</button>
                    @endif
                </div>
            </form>

            <div class="login-ux-trust">
                <i class="fa fa-shield"></i>
                <span>O acesso é exclusivo para clientes desta loja. Seus dados de sessão não são compartilhados com outras lojas do sistema.</span>
            </div>
        </section>
    </div>
</div>

@if($default['config']->politica_privacidade != "")
<div class="modal fade" id="modal-politica" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-none rounded-2xl shadow-2xl">
            <div class="modal-header border-b border-gray-100 p-6">
                <h5 class="text-xl font-bold text-gray-800">Política de Privacidade</h5>
            </div>
            <div class="modal-body p-6 text-gray-600 leading-relaxed text-sm whitespace-pre-line">{{$default['config']->politica_privacidade}}</div>
            <div class="modal-footer border-t border-gray-100 p-4">
                <button type="button" class="w-full sm:w-auto px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@endif

@section('javascript')
<script>
(function(){
    const toggle = document.getElementById('toggle-login-password');
    const password = document.getElementById('login-password');
    const form = document.getElementById('login-ecommerce-form');
    const submit = document.getElementById('login-submit');

    if(toggle && password){
        toggle.addEventListener('click', function(){
            const show = password.type === 'password';
            password.type = show ? 'text' : 'password';
            this.innerHTML = show ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>';
            this.setAttribute('aria-label', show ? 'Ocultar senha' : 'Mostrar senha');
            this.setAttribute('title', show ? 'Ocultar senha' : 'Mostrar senha');
        });
    }

    if(form && submit){
        form.addEventListener('submit', function(){
            submit.disabled = true;
            submit.querySelector('span').textContent = 'Entrando...';
        });
    }
})();
</script>
@endsection
@endsection