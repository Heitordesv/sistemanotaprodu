@extends('default.layout', ['title' => 'Empresas'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('empresas.index') }}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <hr>
            <h5 style="color: rgb(82, 67, 216)">Detalhes da Empresa</h5>
            <br>
            <div class="row g-3">
                {!!Form::open()->fill($empresa)
                ->put()
                ->route('empresas.update', [$empresa->id])
                ->multipart()!!}
                <div class="pl-lg-4">
                    @include('empresas._forms')
                </div>
                {!!Form::close()!!}
                <div class="card">
                    <div class="row m-3">
                        <div class="col-4">
                            <h5>Total de Cadastros</h5>
                            <h6>Clientes: {{sizeof($empresa->clientes)}}</h6>
                            <h6>Fornecedores: {{sizeof($empresa->fornecedores)}}</h6>
                            <h6>Produtos: {{sizeof($empresa->produtos)}}</h6>
                            <h6>Usuários: {{sizeof($empresa->usuarios)}}</h6>
                            <h6>Veículos: {{sizeof($empresa->veiculos)}}</h6>
                        </div>
                        <div class="col-4">
                            <h5>Total de Documentos</h5>
                            <h6>NF-e: {{$empresa->nfes()}}</h6>
                            <h6>NFC-e: {{$empresa->nfces()}}</h6>
                            <h6>CT-e: {{$empresa->ctes()}}</h6>
                            <h6>MDF-e: {{$empresa->mdfes()}}</h6>
                        </div>
                        <div class="col-4">
                            <h5>Registros</h5>
                            <h6>Vendas: {{sizeof($empresa->vendas)}}</h6>
                            <h6>Vendas PDV: {{sizeof($empresa->vendasCaixa)}}</h6>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="row m-3">
                        <h4 style="color: rgb(0, 47, 255)">Data de Cadastro: {{ $empresa->created_at }}</h4>
                        <div class="col-4 mt-3">

                            <a class="btn btn-danger" href="{{ route('empresas.alterarSenha', $empresa->id) }}">Alterar Senhas de usuários</a>
                        </div>
                        <div class="col-4 mt-3">
        Plano Atual: 
        @if($empresa->planoEmpresa)
            @php
                $planoAtual = $empresa->planoEmpresa->plano;
                $planoExpirado = \Carbon\Carbon::parse($empresa->planoEmpresa->expiracao)->isPast();
            @endphp

            <strong>
                {{ $planoAtual->nome }} - 
                R$ {{ number_format($empresa->planoEmpresa->valor ?? $planoAtual->valor, 2, ',', '.') }}
                <br>
                Data de Expiração: 
                <span class="{{ $planoExpirado ? 'text-danger' : 'text-info' }}">
                    @if($empresa->planoEmpresa->expiracao && $empresa->planoEmpresa->expiracao != '0000-00-00')
                        {{ \Carbon\Carbon::parse($empresa->planoEmpresa->expiracao)->format('d/m/Y') }}
                    @else
                        Indeterminado
                    @endif
                </span>
                <br>
                <small class="text-secondary">
                    {{ $empresa->planoEmpresa->mensagem_alerta ?? 'Sem mensagens no momento.' }}
                </small>
            </strong>
        @else
            <span class="text-muted">Nenhum plano ativo</span>
        @endif
    </h5>

    <a class="btn btn-info mt-2" href="{{ route('empresas.setarPlano', $empresa->id) }}">
        <i class="bx bx-refresh"></i> Atribuir/Alterar Plano
    </a>


                    </div>
                </div>
                <div class="card">
                    <div class="row m-3">
                        @if(!$empresa->configNota)
                        <p class="text-danger">>>Esta empresa não possui os dados do emitente cadastrados!</p>
                        @endif
                        <div class="row">
                            <div class="col-sm-12">
                                <a class="btn btn-info" href="{{ route('empresas.download', $empresa->id) }}">
                                    <i class="bx bx-download"></i>
                                    Download certificado
                                </a>

                                @if($empresa->configNota)
                                <a class="btn btn-primary" href="{{ route('empresas.arquivosXml', $empresa->id) }}">
                                    <i class="bx bx-file-code"></i>
                                    Arquivos Xml
                                </a>
                                @endif

                                <a class="btn btn-warning" href="/empresas/configEmitente/{{$empresa->id}}">
                                    <i class="bx bx-edit"></i>
                                    Configuração do emitente
                                </a>

                                @if($empresa->planoEmpresa)
                                <a class="btn btn-success" onclick='swal("Atenção!", "Deseja realizar o login nesta empresa, a sua sessão irá expirar?", "warning").then((sim) => {if(sim){ location.href="/empresas/login/{{$empresa->id}}" }else{return false} })' href="#!">
                                    <i class="bx bx-check"></i>
                                    Fazer login na empresa
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@section('js')
<script src="/js/empresa.js"></script>
@endsection
@endsection
