  <header>
                <div class="topbar d-flex align-items-center">
                    <nav class="navbar navbar-expand">
                        <div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
                        </div>

                        @if(!session('user_contador'))
                        <div class="top-menu-left d-none d-lg-block">
                          <!--  <ul class="nav">
                                <li class="nav-item">
                                    <a href="{{ route('configNF.index')}}" class="btn btn-dark position-relative me-lg-2 btn-sm"> <i class="bx bx-certification align-middle"></i> Ambiente: {{session('user_logged')['ambiente']}} </span></span>
                                    </a>
                                </li>
                            </ul>-->
                        </div>
                     <div class="top-menu d-none d-md-block">
    <a class="btn btn-primary btn-sm px-3" href="{{ route('frenteCaixa.index') }}">
        <i class="bx bx-cart"> </i> PDV
    </a>

    @if($ultimoAcesso != null)
    <!--
    <button type="button" class="btn btn-light btn-sm float-right btn-ip">
        Endereço do IP: <span class="badge bg-secondary">{{ $ultimoAcesso->ip_address }}</span>
    </button>
    -->
    @endif
</div>

                        @endif

                        

                        @if(session('user_contador'))
                        <div class="top-menu">
                            <a data-bs-toggle="modal" href="#!" data-bs-target="#modal-empresa_contador" class="btn btn-success">
                                Empresa selecionada: {{ session('empresa_selecionada') ? session('empresa_selecionada')['nome'] : ' -- '  }}
                            </a>
                        </div>
                        @endif

                        <div class="search-bar flex-grow-1">
                            <div class="position-relative search-bar-box">
                                <input type="text" class="form-control search-control" placeholder="Pesquise no sistema"> <span class="position-absolute top-50 search-show translate-middle-y"><i class='bx bx-search'></i></span>
                                <span class="position-absolute top-50 search-close translate-middle-y"><i class='bx bx-x'></i></span>
                            </div>
                        </div>
                        <div class="top-menu ms-auto">
                            <ul class="navbar-nav align-items-center">
                                <!-- <li class="nav-item mobile-search-icon">
                                    <a class="nav-link" href="javascript:;"> <i class='bx bx-search'></i>
                                    </a>
                                </li> -->

                                @if($video_url != null)

                                <a style="width: 100%" target="_blank" href="{{$video_url}}" class="btn btn-sm btn-info">
                                    <i class="bx bx-video"></i>
                                    Video Ajuda
                                </a>

                                @endif
                                @if((session('user_logged')['adm'] ?? false) || (session('user_logged')['super'] ?? false))
                                <li class="nav-item dropdown dropdown-large">
                                    <input type="hidden" id="notification_session_hash" value="{{ session('user_logged')['hash'] ?? '' }}">
                                    <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> <span class="alert-count"></span>
                                        <i class='bx bx-bell'></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="javascript:;">
                                            <div class="msg-header">
                                                <p class="msg-header-title">Notificações</p>
                                            </div>
                                        </a>
                                        <div class="header-notifications-list">

                                        </div>

                                    </div>
                                </li>
                                @endif
                                <li class="nav-item dropdown dropdown-large">
                                    <!-- nao remover -->
                                    <div class="dropdown-menu">
                                        <div class="header-message-list">
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="user-box dropdown">
                            <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                @if(session('user_logged')['img'] == '')
                                <img src="/logos/user.png" class="user-img" alt="foto usuário">
                                @else
                                <img src="/logos/user.png" class="user-img" alt="foto usuário">
                                @endif
                                <div class="user-info ps-3">
                                    <p class="user-name mb-0">{{session('user_logged')['nome']}}</p>
                                    <p class="designattion mb-0">{{session('user_logged')['empresa_nome']}}</p>
                                </div>
                                <input type="hidden" value="{{session('user_logged')['empresa']}}" id="empresa_id">
                                <input type="hidden" value="{{session('user_logged')['id']}}" id="usuario_id">

                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                            <!--  <li><a class="dropdown-item" href="javascript:;"><i class="bx bx-user"></i><span>Profile</span></a>
                            </li>
                            <li><a class="dropdown-item" href="javascript:;"><i class="bx bx-cog"></i><span>Settings</span></a>
                            </li>
                        -->
                        <li><a class="dropdown-item" href="/login/logoff"><i class='bx bx-log-out-circle'></i><span>Sair</span></a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>
</div>