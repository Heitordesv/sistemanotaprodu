@extends('default.layout', ['title' => 'Clonar Cotação'])
@section('content')

<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="row" id="anime" style="display: none">
            <div class="col s8 offset-s2">
                <lottie-player src="/anime/clone.json" background="transparent" speed="0.8" style="width: 100%; height: 300px;" autoplay>
                </lottie-player>
            </div>
        </div>
        <div class="card card-custom gutter-b" id="content" style="display: block">
            <div class="card-body">
                <input type="hidden" id="cotacao" value="{{$cotacao->id}}">
                <input type="hidden" id="fornecedor-atual" value="{{$cotacao->fornecedor->id}}">
                <input type="hidden" id="_token" value="{{ csrf_token() }}">
                <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-body">
                            <h4>Cotação: {{$cotacao->id}}</h4>
                            <p class="text-info">*Informe os fornecedores abaixo para clonar esta cotação</p>
                            <div class="row">
                                <div class="form-group validated col-sm-6 col-lg-6">
                                    <label class="col-form-label" id="lbl_cpf_cnpj">Fornecedor</label>
                                    <div class="">
                                        <select class="form-control select2 fornecedor" id="kt_select2_1" name="fornecedor">
                                            @foreach($fornecedores as $f)
                                            <option value="{{$f->id}} - {{$f->razao_social}}">{{$f->id}} - {{$f->razao_social}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-xl-2 mt-2 mt-lg-0"><br>
                                    <button style="margin-top: 14px;" id="add" class="btn btn-success px-5">Adicionar</button>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="form-group validated col-sm-12 col-lg-12">
                                    <h5 class="text-danger">Lista de Fornecedores para clonar:</h5>
                                    <div class="row" id="fornecedores">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col s4">
                                    <a href="/cotacao" class="btn btn-danger">Cancelar</a>
                                    <button id="btn-clonar" disabled class="btn btn-success">Clonar</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@section('js')
    <script type="text/javascript" src="/js/clone.js"></script>
@endsection

@endsection
