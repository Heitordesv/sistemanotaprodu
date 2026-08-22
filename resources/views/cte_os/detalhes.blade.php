@extends('default.layout',['title' => 'Detalhes'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="card">
                    <div class="row m-3">
                        <h6>Código: <strong class="text-info">{{ $item->id }}</strong></h6>
                        <h6>Natureza de Operação: <strong class="text-info">{{ $item->natureza->natureza }}</strong></h6>
                        <h6>Data de registro: <strong class="text-info">{{ __data_pt($item->created_at, 0) }}</strong> </h6>
                        <h6>Valor de transporte: <strong class="text-info">{{ __moeda($item->valor_transporte) }}</strong></h6>
                        <h6>Valor a receber: <strong class="text-info">{{ __moeda($item->valor_receber) }}</strong> </h6>
                        <h6>Descrição do serviço: <strong class="text-info">{{ $item->descricao_servico }}</strong> </h6>
                        @if($adm)
                        <div class="col m-2">
                            <a href="{{ route('cteOs.estadoFiscal', $item->id) }}" class="btn btn-danger">
                                <i class="bx bx-error"></i>
                                Alterar estado fiscal da CTe Os
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
                            <h5>Emitente</h5>
                            <hr>
                            <h6>Razao Social: <strong class="text-info">{{ $item->emitente->razao_social }}</strong> </h6>
                            <h6>CNPJ: <strong class="text-info">{{ $item->emitente->cpf_cnpj }}</strong></h6>
                            <h6>Rua: <strong class="text-info">{{ $item->emitente->rua }}</strong></h6>
                            <h6>Bairro: <strong class="text-info">{{ $item->emitente->bairro }}</strong></h6>
                            <h6>Cidade: <strong class="text-info">{{ $item->emitente->cidade->info }}</strong></h6>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <div class="m-3">
                            <h5>Tomador</h5>
                            <hr>
                            <h6>Razao Social: <strong class="text-info">{{ $item->tomador_cli->razao_social }}</strong> </h6>
                            <h6>CNPJ: <strong class="text-info">{{ $item->tomador_cli->cpf_cnpj }}</strong></h6>
                            <h6>Rua: <strong class="text-info">{{ $item->tomador_cli->rua }}</strong></h6>
                            <h6>Bairro: <strong class="text-info">{{ $item->tomador_cli->bairro }}</strong></h6>
                            <h6>Cidade: <strong class="text-info">{{ $item->tomador_cli->cidade->info }}</strong></h6>
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
            </div>
        </div>
    </div>
</div>
@endsection
