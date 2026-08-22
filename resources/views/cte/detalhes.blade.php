@extends('default.layout',['title' => 'Detalhes'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="card">
                    <div class="row m-3">
                        <h6>Código: <strong class="text-info">{{ $item->id }}</strong></h6>
                        <h6>Natureza de Operação: <strong class="text-info">{{ $item->natureza_id }}</strong></h6>
                        <h6>Data de registro: <strong class="text-info">{{ __data_pt($item->created_at, 0) }}</strong> </h6>
                        <h6>Valor de transporte: <strong class="text-info">{{ $item->valor_transporte }}</strong></h6>
                        <h6>Valor a receber: <strong class="text-info">{{ $item->valor_receber }}</strong> </h6>
                        <h6>Valor da carga:<strong class="text-info">{{ $item->valor_carga }}</strong></h6>
                        @if($item->chave_nfe)
                        <h6>Chave: <strong class="text-info">{{$item->chave_nfe}}</strong></h6>
                        @else
                        <h6>Tipo referênciado: <strong class="text-info">{{ $item->tpDoc }}</strong></h6>
                        <h6>Descrição:<strong class="text-info">{{ $item->descOutros }}</strong></h6>
                        <h6>Nº Doc: <strong class="text-info">{{ $item->nDoc }}</strong></h6>
                        <h6>Valor:<strong class="text-info">{{ $item->vDocFisc }}</strong> </h6>
                        @endif
                        @if($adm)
                        <div class="col m-2">
                            <a href="{{ route('cte.estadoFiscal', $item->id) }}" class="btn btn-danger">
                                <i class="bx bx-error"></i>
                                Alterar estado fiscal da CTe
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="card">
                        <div class="m-3">
                            <h5>Remetente</h5>
                            <hr>
                            <h6>Razao Social: <strong class="text-info">{{ $item->remetente->razao_social }}</strong> </h6>
                            <h6>CNPJ: <strong class="text-info">{{ $item->remetente->cpf_cnpj }}</strong></h6>
                            <h6>Rua: <strong class="text-info">{{ $item->remetente->rua }}</strong></h6>
                            <h6>Bairro: <strong class="text-info">{{ $item->remetente->bairro }}</strong></h6>
                            <h6>Cidade: <strong class="text-info">{{ $item->remetente->cidade->info }}</strong></h6>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <div class="m-3">
                            <h5>Destinatário</h5>
                            <hr>
                            <h6>Razao Social: <strong class="text-info">{{ $item->destinatario->razao_social }}</strong> </h6>
                            <h6>CNPJ: <strong class="text-info">{{ $item->destinatario->cpf_cnpj }}</strong></h6>
                            <h6>Rua: <strong class="text-info">{{ $item->destinatario->rua }}</strong></h6>
                            <h6>Bairro: <strong class="text-info">{{ $item->destinatario->bairro }}</strong></h6>
                            <h6>Cidade: <strong class="text-info">{{ $item->destinatario->cidade->info }}</strong></h6>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <div class="m-3">
                            <h5>Veículo</h5>
                            <hr>
                            <h6>Marca: <strong class="text-info">{{ $item->veiculo->marca }}</strong> </h6>
                            <h6>Modelo: <strong class="text-info">{{ $item->veiculo->modelo }}</strong></h6>
                            <h6>Placa: <strong class="text-info">{{ $item->veiculo->placa }}</strong></h6>
                            <h6>Cor: <strong class="text-info">{{ $item->veiculo->cor }}</strong></h6>
                            <h6>RNTRC: <strong class="text-info">{{ $item->veiculo->rntrc }}</strong></h6>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <div class="m-3">
                            <h5>Tomador</h5>
                            <hr>
                            <h6>Razao Social: <strong class="text-info">{{ $item->destinatario->razao_social }}</strong> </h6>
                            <h6>CNPJ: <strong class="text-info">{{ $item->destinatario->cpf_cnpj }}</strong></h6>
                            <h6>Rua: <strong class="text-info">{{ $item->destinatario->rua }}</strong></h6>
                            <h6>Bairro: <strong class="text-info">{{ $item->destinatario->bairro }}</strong></h6>
                            <h6>Cidade: <strong class="text-info">{{ $item->destinatario->cidade->info }}</strong></h6>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card">
                        <div class="table-responsive m-2">
                            <h5 class="m-2">Componentes da CT-e</h5>
                            <table class="table mb-0 table-striped">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($item->componentes as $c)
                                    <tr>
                                        <td>{{ $c->nome }}</td>
                                        <td>{{ $c->valor }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center">Nada Encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-8">
                    <div class="card">
                        <h5 class="m-2">Medidas da CT-e</h5>
                        <div class="table-responsive m-2">
                            <table class="table mb-0 table-striped">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Quantidade</th>
                                        <th>Código</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($item->medidas as $m)
                                    <tr>
                                        <td>{{ $m->tipo_medida }}</td>
                                        <td>{{ $m->quantidade_carga }}</td>
                                        <td>{{ $m->cod_unidade }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Nada Encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
