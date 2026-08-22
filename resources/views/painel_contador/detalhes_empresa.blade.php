@extends('default.layout',['title' => 'Detalhes da empresa'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="row">
                <div class="col-xl-12">
                    <div class="row">
                        <div class="col-sm-4 col-lg-4 col-md-4 col-12">
                            <div class="card card-custom gutter-b">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        Total de Cadastros
                                    </h3>
                                </div>
                                <div class="card-body" style="height: 200px;">
                                    <h4>Clientes: <strong class="text-info">{{sizeof($empresa->clientes)}}</strong></h4>
                                    <h4>Fornecedores: <strong class="text-info">{{sizeof($empresa->fornecedores)}}</strong></h4>
                                    <h4>Produtos: <strong class="text-info">{{ $empresa->countProdutos() }}</strong></h4>
                                    <h4>Usuários: <strong class="text-info">{{sizeof($empresa->usuarios)}}</strong></h4>
                                    <h4>Veiculos: <strong class="text-info">{{sizeof($empresa->veiculos)}}</strong></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-lg-4 col-md-4 col-12">
                            <div class="card card-custom gutter-b">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        Total de Documentos
                                    </h3>
                                </div>
                                <div class="card-body" style="height: 200px;">
                                    <h4>NFe: <strong class="text-info">{{$empresa->nfes()}}</strong></h4>
                                    <h4>NFCe: <strong class="text-info">{{$empresa->nfces()}}</strong></h4>
                                    <h4>CTe: <strong class="text-info">{{$empresa->ctes()}}</strong></h4>
                                    <h4>MDFe: <strong class="text-info">{{$empresa->mdfes()}}</strong></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-lg-4 col-md-4 col-12">
                            <div class="card card-custom gutter-b">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        Registros
                                    </h3>
                                </div>
                                <div class="card-body" style="height: 200px;">
                                    <h4>Vendas: <strong class="text-info">{{sizeof($empresa->vendas)}}</strong></h4>
                                    <h4>Vendas PDV: <strong class="text-info">{{sizeof($empresa->vendasCaixa)}}</strong></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-8 col-lg-">
                            <h3 class="text-success">Data de cadastro: {{ \Carbon\Carbon::parse($empresa->created_at)->format('d/m/Y H:i:s')}}</h3>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-sm-12 col-lg-12">
                            <h3 class="">Plano atual:
                                @if(!$empresa->planoEmpresa)
                                <a href="/empresas/setarPlano/{{$empresa->id}}" class="btn btn-info">
                                    Atribuir plano
                                </a>
                                @else
                                <span class="text-info">{{$empresa->planoEmpresa->plano->nome}}</span>
                                - Data de expiração: <span class="@if($planoExpirado) text-danger @else text-info @endif">
                                    @if($empresa->planoEmpresa->expiracao != '0000-00-00')
                                    {{ \Carbon\Carbon::parse($empresa->planoEmpresa->expiracao)->format('d/m/Y')}}
                                    @else
                                    Indeterminado
                                    @endif
                                </span>
                                @endif


                            </h3>
                        </div>
                    </div>
                    @if(!$empresa->configNota)
                    <p class="text-danger mt-3">>>Esta empresa não possui os dados do emitente cadastrados</p>
                    @endif
                    <div class="row mt-4">
                        <div class="col-sm-12">
                            <a class="btn btn-info" href="/contador/download-certificado/{{$empresa->id}}">
                                <i class="la la-download"></i>
                                Download certificado
                            </a>
                        </div>
                    </div>
                    <br>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
