
<div class="switcher-wrapper">
    <!-- Botão de abrir switcher -->
    <div class="switcher-btn">
        <i class='bx bx-cog bx-spin'></i>
    </div>

    <!-- Painel de customização -->
    <div class="switcher-body">
        <!-- Cabeçalho -->
        <div class="d-flex align-items-center mb-2">
            <h5 class="mb-0">Customização do Sistema</h5>
            <button type="button" class="btn-close ms-auto close-switcher" aria-label="Close"></button>
        </div>

        <hr />

        <!-- Temas -->
        <h6 class="mb-1">Temas</h6>
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="form-check">
                <input class="form-check-input click-theme" type="radio" value="light-theme" name="flexRadioDefault" id="lightmode" @isset($theme->tema) @if($theme->tema == 'light-theme') checked @endif @endisset>
                <label class="form-check-label" for="lightmode">Claro</label>
            </div>
            <div class="form-check">
                <input class="form-check-input click-theme" value="dark-theme" type="radio" name="flexRadioDefault" id="darkmode" @isset($theme->tema) @if($theme->tema == 'dark-theme') checked @endif @endisset>
                <label class="form-check-label" for="darkmode">Escuro</label>
            </div>
            <div class="form-check">
                <input class="form-check-input click-theme" type="radio" value="semi-dark" name="flexRadioDefault" id="semidark" @isset($theme->tema) @if($theme->tema == 'semi-dark') checked @endif @endisset>
                <label class="form-check-label" for="semidark">Semi Escuro</label>
            </div>
        </div>
        <div class="form-check mb-2">
            <input class="form-check-input click-theme" type="radio" id="minimal-theme" value="minimaltheme" name="flexRadioDefault" @isset($theme->tema) @if($theme->tema == 'minimaltheme') checked @endif @endisset>
            <label class="form-check-label" for="minimaltheme">Tema básico</label>
        </div>

        <hr />

        <!-- Cores do Cabeçalho -->
        <h6 class="mb-1">Cores do Cabeçalho</h6>
        <div class="header-colors-indigators mb-2">
            <div class="row row-cols-auto g-2">
                @for ($i = 1; $i <= 8; $i++)
                    <div class="col">
                        <div class="indigator headercolor{{ $i }}" onclick="setHeaderColor('headercolor{{ $i }}')" id="headercolor{{ $i }}"></div>
                    </div>
                @endfor
            </div>
        </div>

        <hr />

        <!-- Planos de fundo da barra lateral -->
        <h6 class="mb-1">Planos de fundo da barra lateral</h6>
        <div class="header-colors-indigators mb-2">
            <div class="row row-cols-auto g-2">
                @for ($i = 1; $i <= 8; $i++)
                    <div class="col">
                        <div class="indigator sidebarcolor{{ $i }}" onclick="setSidebar('sidebarcolor{{ $i }}')" id="sidebarcolor{{ $i }}"></div>
                    </div>
                @endfor
            </div>
        </div>

        <hr />

        <!-- Áudio -->
        <div class="header-colors-indigators mb-2">
            <div class="row row-cols-auto g-2">
                @if($audio == 0)
                    <div class="col indigator text-info">
                        <i class="bx bx-bell font-30 aviso-on" onclick="avisoSonoro('1')"></i>
                    </div>
                @else
                    <div class="col indigator text-info">
                        <i class="bx bx-bell-off font-30 aviso-off" onclick="avisoSonoro('0')"></i>
                    </div>
                @endif
            </div>
        </div>

        <hr />

        <!-- Botões de Atalho -->
        <h6 class="mb-1">Atalhos Rápidos</h6>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('clientes.index') }}" class="btn btn-primary btn-atalho">
                <i class="fa fa-users"></i> Clientes
            </a>

            <a href="{{ route('vendas.create') }}" class="btn btn-warning btn-atalho">
                <i class="fa fa-file-invoice"></i> NF-e
            </a>
             <a href="{{ route('dfe.index') }}" class="btn btn-primary btn-atalho">
                <i class="fa fa-file-invoice"></i>Manifesto
            </a>
        
            <a href="{{ route('relatorios.index') }}" class="btn btn-warning btn-atalho">
                <i class="fa fa-file-invoice"></i>Relatorios
            </a>

            <a href="{{ route('frenteCaixa.index') }}" class="btn btn-info btn-atalho">
                <i class="fa fa-dollar-sign"></i> Pdv
            </a>
        </div>
    </div>
</div>

