@extends('default.layout',['title' => 'XML'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex mb-3">
                <div class="">
                    <h4>Enviar XML para o escritório</h4>
                    <a href="{{ route('enviarXml.filtroCfop') }}" class="btn btn-primary mt-3">Filtrar por CFOP</a>
                </div>
            </div>
            <hr>
            <div class="col">
                {!!Form::open()
                ->get()
                ->route('enviarXml.filtro')
                !!}
                <div class="row mt-5">
                    <div class="col-md-3">
                        {!!Form::date('start_date', 'Data inicial')
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::date('end_date', 'Data final')
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('estado_emissao', 'Estado', [1 => 'Aprovado', 0 => 'Cancelado'])->attrs(['class' => 'form-select'])
                        !!}
                    </div>

                    @if(empresaComFilial())
                    {!! __view_locais_select_filtro_xml(isset($filial_id) ? $filial_id : '') !!}
                    @endif

                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Buscar arquivos</button>
                    </div>
                </div>
                {!!Form::close()!!}

                <hr />

                <div class="card">
                    @if(isset($xml)&& sizeof($xml) > 0)
                    <div class="form-group col-lg-12 col-md-12 col-sm-12">
                        <div class="card card-custom gutter-b example example-compact">
                            <div class="card-header">
                                <div class="col-lg-6 col-md-4 col-sm-6">
                                    <h3 class="card-title">Total de arquivos de NFe: <strong style="margin-left: 5px; color: blue">{{sizeof($xml)}}</strong></h3>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <a target="_blank" style="width: 100%" href="/enviarXml/download" class="btn">
                                        <span style="width: 100%" class="btn btn-danger">
                                            Baixar Arquivos de XML NFe
                                        </span>
                                    </a>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <a target="_blank" style="width: 100%" href="/enviarXml/email/{{$start_date}}/{{$end_date}}" class="btn">
                                        <span style="width: 100%" class="btn btn-success">
                                            Enviar Arquivos de XML NFe
                                        </span>
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table mb-0 table-striped" style="max-width: 100%; overflow: scroll">
                                        <thead class="datatable-head">
                                            <tr class="datatable-row" style="left: 0px;">
                                                <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">Venda ID</span></th>
                                                <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Cliente</span></th>
                                                <th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Valor</span></th>
                                                <th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Chave</span></th>
                                                <th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Data</span></th>
                                            </tr>
                                        </thead>
                                        <tbody class="datatable-body">
                                            @foreach($xml as $x)
                                            <tr class="datatable-row" style="left: 0px;">
                                                <td class="datatable-cell"><span class="codigo" style="width: 60px;">
                                                        {{$x->id}}</span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 150px;">
                                                        {{$x->cliente->razao_social}}
                                                    </span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 100px;">
                                                        R$ {{__moeda($x->valor_total)}}
                                                    </span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 150px;">
                                                        {{$x->chave}}
                                                    </span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 100px;">
                                                        {{ \Carbon\Carbon::parse($x->created_at)->format('d/m/Y H:i:s')}}
                                                    </span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    @endif

                    @if(isset($xmlEntrada) && sizeof($xmlEntrada) > 0)
                    <div class="form-group col-lg-12 col-md-12 col-sm-12">
                        <div class="card card-custom gutter-b example example-compact">
                            <div class="card-header">
                                <div class="col-lg-6 col-md-4 col-sm-6">
                                    <h3 class="card-title">Total de arquivos de NFe Entrada: <strong style="margin-left: 5px; color: blue">{{sizeof($xmlEntrada)}}</strong></h3>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <a target="_blank" style="width: 100%" href="/enviarXml/downloadEntrada" class="btn">
                                        <span style="width: 100%" class="btn btn-danger">
                                            Baixar Arquivos de XML NFe Entrada
                                        </span>
                                    </a>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <a target="_blank" style="width: 100%" href="/enviarXml/emailEntrada/{{$start_date}}/{{$end_date}}" class="btn">
                                        <span style="width: 100%" class="btn btn-success">
                                            Enviar Arquivos de XML NFe Entrada
                                        </span>
                                    </a>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table mb-0 table-striped" style="max-width: 100%; overflow: scroll">
                                        <thead class="datatable-head">
                                            <tr class="datatable-row" style="left: 0px;">
                                                <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">Compra ID</span></th>
                                                <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Cliente</span></th>
                                                <th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Valor</span></th>
                                                <th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Chave</span></th>
                                                <th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Data</span></th>
                                            </tr>
                                        </thead>
                                        <tbody class="datatable-body">
                                            @foreach($xmlEntrada as $x)
                                            <tr class="datatable-row" style="left: 0px;">
                                                <td class="datatable-cell"><span class="codigo" style="width: 60px;">
                                                        {{$x->id}}</span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 150px;">
                                                        {{$x->fornecedor->razao_social}}
                                                    </span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 100px;">
                                                        R$ {{__moeda($x->valor)}}
                                                    </span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 150px;">
                                                        {{$x->chave}}
                                                    </span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 100px;">
                                                        {{ \Carbon\Carbon::parse($x->created_at)->format('d/m/Y H:i:s')}}
                                                    </span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    @endif

                    @if(isset($xmlDevolucao) && sizeof($xmlDevolucao) > 0)
                    <div class="form-group col-lg-12 col-md-12 col-sm-12">
                        <div class="card card-custom gutter-b example example-compact">
                            <div class="card-header">
                                <div class="col-lg-6 col-md-4 col-sm-6">
                                    <h3 class="card-title">Total de arquivos de NFe Devolução: <strong style="margin-left: 5px; color: blue">{{sizeof($xmlDevolucao)}}</strong></h3>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <a target="_blank" style="width: 100%" href="/enviarXml/downloadDevolucao" class="btn">
                                        <span style="width: 100%" class="btn btn-danger">
                                            Baixar Arquivos de XML NFe Devolução
                                        </span>
                                    </a>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <a target="_blank" style="width: 100%" href="/enviarXml/emailDevolucao/{{$start_date}}/{{$end_date}}" class="btn">
                                        <span style="width: 100%" class="btn btn-success">
                                            Enviar Arquivos de XML NFe Devolução
                                        </span>
                                    </a>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table mb-0 table-striped" style="max-width: 100%; overflow: scroll">
                                        <thead class="datatable-head">
                                            <tr class="datatable-row" style="left: 0px;">
                                                <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">Venda ID</span></th>
                                                <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Cliente</span></th>
                                                <th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Valor</span></th>
                                                <th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Chave</span></th>
                                                <th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Data</span></th>
                                            </tr>
                                        </thead>
                                        <tbody class="datatable-body">
                                            @foreach($xmlDevolucao as $x)
                                            <tr class="datatable-row" style="left: 0px;">
                                                <td class="datatable-cell"><span class="codigo" style="width: 60px;">
                                                        {{$x->id}}</span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 150px;">
                                                        {{$x->fornecedor->razao_social}}
                                                    </span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 100px;">
                                                        R$ {{__moeda($x->valor_devolvido)}}
                                                    </span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 150px;">
                                                        {{$x->chave_gerada}}
                                                    </span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 100px;">
                                                        {{ \Carbon\Carbon::parse($x->created_at)->format('d/m/Y H:i:s')}}
                                                    </span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    @endif

                    @if(isset($xmlNfce) && sizeof($xmlNfce) > 0)
                    <hr>
                    <div class="form-group col-lg-12 col-md-12 col-sm-12">
                        <div class="card card-custom gutter-b example example-compact">
                            <div class="card-header">
                                <div class="col-lg-6 col-md-4 col-sm-6">
                                    <h3 class="card-title">Total de arquivos de NFCe: <strong style="margin-left: 5px; color: blue">{{sizeof($xmlNfce)}}</strong></h3>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <a target="_blank" style="width: 100%" href="/enviarXml/downloadNfce" class="btn">
                                        <span style="width: 100%" class="btn btn-danger">
                                            Baixar Arquivos de XML NFCe
                                        </span>
                                    </a>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <a target="_blank" style="width: 100%" href="/enviarXml/emailNfce/{{$start_date}}/{{$end_date}}" class="btn">
                                        <span style="width: 100%" class="btn btn-success">
                                            Enviar Arquivos de XML NFCe
                                        </span>
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table mb-0 table-striped" style="max-width: 100%; overflow: scroll">
                                        <thead class="datatable-head">
                                            <tr class="datatable-row" style="left: 0px;">
                                                <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">Venda ID</span></th>
                                                <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Cliente</span></th>
                                                <th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Valor</span></th>
                                                <th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Chave</span></th>
                                                <th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Data</span></th>
                                            </tr>
                                        </thead>
                                        <tbody class="datatable-body">
                                            @foreach($xmlNfce as $x)
                                            <tr class="datatable-row" style="left: 0px;">
                                                <td class="datatable-cell"><span class="codigo" style="width: 60px;">
                                                        {{$x->id}}</span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 150px;">
                                                        @if($x->cliente)
                                                        {{$x->cliente->razao_social}}
                                                        @else
                                                        --
                                                        @endif
                                                    </span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 100px;">
                                                        R$ {{__moeda($x->valor_total)}}
                                                    </span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 150px;">
                                                        {{$x->chave}}
                                                    </span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 100px;">
                                                        {{ \Carbon\Carbon::parse($x->created_at)->format('d/m/Y H:i:s')}}
                                                    </span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    @endif

                    @if(isset($xmlCte) && sizeof($xmlCte) > 0)
                    <hr>
                    <div class="form-group col-lg-12 col-md-12 col-sm-12">
                        <div class="card card-custom gutter-b example example-compact">
                            <div class="card-header">
                                <div class="col-lg-6 col-md-4 col-sm-6">
                                    <h3 class="card-title">Total de arquivos de CTe: <strong style="margin-left: 5px; color: blue">{{sizeof($xmlCte)}}</strong></h3>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <a target="_blank" style="width: 100%" href="/enviarXml/downloadCte" class="btn">
                                        <span style="width: 100%" class="btn btn-danger">
                                            Baixar Arquivos de XML CTe
                                        </span>
                                    </a>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <a target="_blank" style="width: 100%" href="/enviarXml/emailCte/{{$start_date}}/{{$end_date}}" class="btn">
                                        <span style="width: 100%" class="btn btn-success">
                                            Enviar Arquivos de XML CTe
                                        </span>
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table mb-0 table-striped" style="max-width: 100%; overflow: scroll">
                                        <thead class="datatable-head">
                                            <tr class="datatable-row" style="left: 0px;">
                                                <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">ID</span></th>
                                                <th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 350px;">Chave</span></th>
                                                <th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Data</span></th>
                                            </tr>
                                        </thead>
                                        <tbody class="datatable-body">
                                            @foreach($xmlCte as $x)
                                            <tr class="datatable-row" style="left: 0px;">
                                                <td class="datatable-cell"><span class="codigo" style="width: 60px;">
                                                        {{$x->id}}</span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 350px;">
                                                        {{$x->chave}}
                                                    </span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 100px;">
                                                        {{ \Carbon\Carbon::parse($x->created_at)->format('d/m/Y H:i:s')}}
                                                    </span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(isset($xmlMdfe) && sizeof($xmlMdfe) > 0)
                    <hr>
                    <div class="form-group col-lg-12 col-md-12 col-sm-12">
                        <div class="card card-custom gutter-b example example-compact">
                            <div class="card-header">
                                <div class="col-lg-6 col-md-4 col-sm-6">
                                    <h3 class="card-title">Total de arquivos de MDFe: <strong style="margin-left: 5px; color: blue">{{sizeof($xmlMdfe)}}</strong></h3>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <a target="_blank" style="width: 100%" href="/enviarXml/downloadMdfe" class="btn">
                                        <span style="width: 100%" class="btn btn-danger">
                                            Baixar Arquivos de XML MDFe
                                        </span>
                                    </a>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <a target="_blank" style="width: 100%" href="/enviarXml/emailMdfe/{{$start_date}}/{{$end_date}}" class="btn">
                                        <span style="width: 100%" class="btn btn-success">
                                            Enviar Arquivos de XML MDFe
                                        </span>
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table mb-0 table-striped" style="max-width: 100%; overflow: scroll">
                                        <thead class="datatable-head">
                                            <tr class="datatable-row" style="left: 0px;">
                                                <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">ID</span></th>
                                                <th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 350px;">Chave</span></th>
                                                <th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Data</span></th>
                                            </tr>
                                        </thead>
                                        <tbody class="datatable-body">
                                            @foreach($xmlMdfe as $x)
                                            <tr class="datatable-row" style="left: 0px;">
                                                <td class="datatable-cell"><span class="codigo" style="width: 60px;">
                                                        {{$x->id}}</span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 350px;">
                                                        {{$x->chave}}
                                                    </span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 100px;">
                                                        {{ \Carbon\Carbon::parse($x->created_at)->format('d/m/Y H:i:s')}}
                                                    </span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    @endif

                    @if(isset($xmlCompraFiscal) && sizeof($xmlCompraFiscal) > 0)
                    <hr>
                    <div class="form-group col-lg-12 col-md-12 col-sm-12">
                        <div class="card card-custom gutter-b example example-compact">
                            <div class="card-header">
                                <div class="col-lg-4 col-md-4 col-sm-6">
                                    <h3 class="card-title">Total de arquivos de Compra Fiscal: <strong style="margin-left: 5px; color: blue">{{sizeof($xmlCompraFiscal)}}</strong></h3>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <a target="_blank" style="width: 100%" href="/enviarXml/downloadCompraFiscal" class="btn">
                                        <span style="width: 100%" class="btn btn-danger">
                                            Baixar Arquivos de XML Compra fiscal
                                        </span>
                                    </a>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <a target="_blank" style="width: 100%" href="/enviarXml/emailCompraFiscal/{{$start_date}}/{{$end_date}}" class="btn">
                                        <span style="width: 100%" class="btn btn-success">
                                            Enviar Arquivos de XML Compra fiscal
                                        </span>
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table mb-0 table-striped" style="max-width: 100%; overflow: scroll">
                                        <thead class="datatable-head">
                                            <tr class="datatable-row" style="left: 0px;">
                                                <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">ID</span></th>
                                                <th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 350px;">Chave</span></th>
                                                <th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Data</span></th>
                                            </tr>
                                        </thead>
                                        <tbody class="datatable-body">
                                            @foreach($xmlCompraFiscal as $x)
                                            <tr class="datatable-row" style="left: 0px;">
                                                <td class="datatable-cell"><span class="codigo" style="width: 60px;">
                                                        {{$x['id']}}</span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 350px;">
                                                        {{$x['chave']}}
                                                    </span></td>
                                                <td class="datatable-cell"><span class="codigo" style="width: 100px;">
                                                        {{ \Carbon\Carbon::parse($x['data_emissao'])->format('d/m/Y H:i')}}
                                                    </span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if(isset($xml) && isset($xmlNfce) && isset($xmlCte) && isset($xmlMdfe) &&
                    sizeof($xml) == 0 && sizeof($xmlNfce) == 0 && sizeof($xmlCte) == 0 && sizeof($xmlMdfe) == 0 && sizeof($xmlCompraFiscal) == 0 && sizeof($xmlEntrada) == 0)
                    <h2 class="m-3" style="color: red">Nenhum arquivo encontrado</h2>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
