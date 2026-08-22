@extends('default.layout',['title' => 'Compra Fiscal'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="m-3">
            <div class="container @if(env('ANIMACAO')) animate__animated @endif animate__bounce">
                <div class="col-lg-12">
                    <input type="hidden" name="id" value="{{{ isset($cliente) ? $cliente->id : 0 }}}">
                    <div class="card-header">
                        <h3 class="card-title">Importando XML</h3>
                    </div>
                    {!!Form::open()
                    ->post()
                    ->route('compraFiscal.store')
                    ->multipart()!!}
                    <div class="row">
                        <div class="card col-xl-12 m-3">
                            <h5 class="center-align mt-2">Nota Fiscal: <strong class="text-primary">{{$dadosNf['nNf']}}</strong></h5>
                            <h5 class="center-align">Data de emissão: <strong class="text-primary">{{ \Carbon\Carbon::parse($dadosNf['data_emissao'])->format('d/m/Y H:i')}}</strong></h5>
                            <h5 class="center-align">Chave: <strong class="text-primary">{{$dadosNf['chave']}}</strong></h5>
                            @if(count($dadosAtualizados) > 0)
                            <div class="row">
                                <div class="col-xl-12">
                                    <h6 class="text-success">Dados atualizados do fornecedor</h5>
                                        @foreach($dadosAtualizados as $d)
                                        <p class="red-text">{{$d}}</p>
                                        @endforeach
                                </div>
                            </div>
                            @endif
                            <div class="row">
                                <div class="col s8">
                                    <h5>Fornecedor: <strong>{{$dadosEmitente['razaoSocial']}}</strong></h5>
                                    <h5>Nome Fantasia: <strong>{{$dadosEmitente['nomeFantasia']}}</strong></h5>
                                </div>
                                <div class="col s4">
                                    <h5>CNPJ: <strong>{{$dadosEmitente['cnpj']}}</strong></h5>
                                    <h5>IE: <strong>{{$dadosEmitente['ie']}}</strong></h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col s8">
                                    <h5>Logradouro: <strong>{{$dadosEmitente['logradouro']}}</strong></h5>
                                    <h5>Numero: <strong>{{$dadosEmitente['numero']}}</strong></h5>
                                    <h5>Bairro: <strong>{{$dadosEmitente['bairro']}}</strong></h5>
                                </div>
                                <div class="col s4">
                                    <h5>CEP: <strong>{{$dadosEmitente['cep']}}</strong></h5>
                                    <h5>Fone: <strong>{{$dadosEmitente['fone']}}</strong></h5>
                                </div>
                            </div>
                            <input type="hidden" name="pathXml" id="pathXml" value="{{$pathXml}}">
                            <input type="hidden" name="fornecedor_id" id="idFornecedor" value="{{$idFornecedor}}">
                            <input type="hidden" name="nNf" id="nNf" value="{{$dadosNf['nNf']}}">
                            <input type="hidden" name="data_emissao" id="data_emissao" value="{{$dadosNf['data_emissao']}}">
                            <input type="hidden" name="vDesc" id="vDesc" value="{{$dadosNf['vDesc']}}">
                            <input type="hidden" id="prodSemRegistro" value="{{$dadosNf['contSemRegistro']}}">
                            <input type="hidden" name="chave" id="chave" value="{{$dadosNf['chave']}}">
                        </div>

                        <div class="col-xl-12">
                            <div class="row">
                                {{-- {!! __view_locais_select() !!} --}}
                                <div class="col-xl-12 m-3">
                                    <p class="text-danger">* Produtos em vermelho não possui cadastro no sistema.</p>
                                    <p> Produtos sem registro no sistema: <strong class="prodSemRegistro">
                                            {{$dadosNf['contSemRegistro']}}</strong></p>

                                    <h5>Itens da NFe: <strong class="text-info">{{sizeof($itens)}}</strong></h5>
                                    <div id="kt_datatable" class="table-responsive">
                                        <table class="table mb-0 table-striped" style="">
                                            <thead class="datatable-head">
                                                <tr class="" style="left: 0px;">
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 70px;">#</span></th>
                                                    <th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 180px;">Produto</span></th>
                                                    <th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">NCM</span></th>
                                                    <th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">CFOP</span></th>
                                                    <th data-field="Status" class="datatable-cell datatable-cell-sort"><span style="width: 90px;">Cod Barra</span></th>
                                                    <th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Un. Compra</span></th>
                                                    <th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Valor</span></th>
                                                    <th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Qtd</span></th>
                                                    <th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">CFOP Ent.</span></th>
                                                    <th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Subtotal</span></th>
                                                    <th data-field="Actions" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Ações</span></th>
                                                </tr>
                                            </thead>
                                            <tbody class="">
                                                @foreach($itens as $i)
                                                <tr class="" id="tr_{{$i['codigo']}}">
                                                    <input type="hidden" class="inp-novo-{{$i['codigo']}} inp-check" value="{{$i['produtoNovo']}}">

                                                    <input type="hidden" class="produto_id_{{$i['codigo']}}" name="produto_id[]" value="{{$i['id']}}">
                                                    <input type="hidden" name="quantidade[]" value="{{$i['qCom']}}">
                                                    <input type="hidden" name="valor_unitario[]" value="{{$i['vUnCom']}}">
                                                    <input type="hidden" name="unidade_compra[]" value="{{$i['uCom']}}">
                                                    
                                                    <td>{{$i['codigo']}}</td>
                                                    <td><span id="n_{{$i['codigo']}}" class="{{$i['produtoNovo'] ? 'text-danger' : ''}} nome">{{$i['xProd']}}</span></td>
                                                    <td><span class="ncm" style="width: 80px;">{{$i['NCM']}}</span></td>
                                                    <td><span class="cfop" style="width: 80px;">{{$i['CFOP']}}</span></td>
                                                    <td><span class="codBarras" style="width: 90px;">{{$i['codBarras']}}</span></td>
                                                    <td><span class="unidade" style="width: 80px;">{{$i['uCom']}}</span></td>
                                                    <td class="mt-3">{{__moeda((float)$i['vUnCom'])}}</td>
                                                    <td class="mt-5"><span id="qtd_aux_{{$i['codigo']}}" class="quantidade mt-3">{{$i['qCom']}}</span></td>
                                                    <td>
                                                        <span class="" id="cfop_entrada_{{$i['codigo']}}" style="">
                                                            <input id="cfop_entrada_input" class="cfop form-control" style="width: 60px;" type="text" value="{{$i['CFOP_entrada']}}" name="">
                                                        </span>
                                                    </td>
                                                    <td class="">{{__moeda((float) $i['qCom'] * (float) $i['vUnCom'])}}</td>
                                                    <th>
                                                        <span>
                                                            <a class="@if(!$i['produtoNovo']) d-none @endif btn-cad-{{$i['codigo']}}" id="th_acao1_{{$i['codigo']}}" onclick="cadProd('{{$i['codigo']}}','{{$i['xProd']}}','{{$i['codBarras']}}','{{$i['NCM']}}','{{$i['CFOP']}}','{{$i['uCom']}}','{{$i['vUnCom']}}','{{$i['qCom']}}', '{{$i['CFOP']}}','{{$i['CEST']}}')" href="javascript:;" data-bs-toggle="modal" data-bs-target="#modal-produto">
                                                                <span class="btn btn-success btn-sm">
                                                                    <i class="bx bx-plus"></i>
                                                                </span>
                                                            </a>
                                                        </span>
                                                    </th>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <br><br>
                                        @if($dadosNf['contSemRegistro'] > 0)
                                        <div class="row sem-registro">
                                            <div class="col-xl-12">
                                                <p class="text-danger">*Esta nota possui produto(s) sem cadastro, inclua antes de continuar</p>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12">
                            <div class="card card-custom gutter-b example example-compact">
                                <div class="card-body">
                                    <input type="hidden" id="fatura" value="{{json_encode($fatura)}}">
                                    <div class="table-responsive">
                                        <h4>Fatura</h4>
                                        <table class="table mb-0 table-striped table-dynamic">
                                            <thead>
                                                <tr>
                                                    <th>Vencimento</th>
                                                    <th>Valor</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body" class="datatable-body">
                                                @foreach ($fatura as $f)
                                                <tr class="dynamic-form">
                                                    <td class="col-2">
                                                        <input type="date" class="form-control" name="vencimento[]" value="{{$f['vencimento']}}">
                                                    </td>
                                                    <td class="col-2">
                                                        <input type="text" class="form-control moeda" name="valor_parcela[]" value="{{__moeda($f['valor_parcela'])}}">
                                                    </td>
                                                    <td class="">
                                                        <button class="btn btn-danger btn-sm btn-remove-tr">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <br>
                                            <button type="button" class="btn btn-success btn-add-tr">
                                                Adicionar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="total" id="total" value="{{$dadosNf['vProd']}}" name="">
                        <div class="col-xl-12">
                            <div class="row">
                                <div class="col-xl-6">
                                    <h4>Total: <strong id="valorDaNF" class="blue-text">R$ {{ __moeda((float)$dadosNf['vProd']) }}</strong></h4>
                                </div>
                                <div class="col-xl-3">
                                </div>
                                <div class="col-xl-3">
                                    <button type="submit" disabled id="btn-salvar" style="width: 100%" class="btn btn-success spinner-white spinner-right">
                                        <i class=""></i>
                                        <span class="">Salvar</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {!!Form::close()!!}
                    <br>
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
<script type="text/javascript" src="/js/compraFiscal.js"></script>
<script src="/js/grade.js"></script>
<script src="/js/product.js"></script>
<script src="/assets/js/jquery.uploadPreview.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function(){

    if(document.querySelector('#info_tecnica_composto')){
        ClassicEditor
        .create(document.querySelector('#info_tecnica_composto'))
        .then(editor=>{
            editor.ui.view.editable.element.style.height = '300px';
        });
    }

    if(document.querySelector('#descricao-ecommerce')){
        ClassicEditor
        .create(document.querySelector('#descricao-ecommerce'))
        .then(editor=>{
            editor.ui.view.editable.element.style.height = '300px';
        });
    }

});
</script>
@endsection

@include('modals._produto', ['not_submit' => true])

@endsection




