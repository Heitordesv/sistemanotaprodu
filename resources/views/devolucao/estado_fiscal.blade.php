@extends('default.layout',['title' => 'Alterar estado fiscal'])
@section('content')
@section('css')
<style type="text/css">
    input[type="file"] {
        display: none;
    }

    .file-certificado label {
        padding: 10px 10px;
        width: 100%;
        background-color: #1C1F23;
        color: #FFF;
        text-transform: uppercase;
        text-align: center;
        display: block;
        margin-top: 15px;
        cursor: pointer;
        border-radius: 5px;
    }

</style>
@endsection
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('devolucao.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary text-dark">Devolução <strong>N: {{ $item->id }}</strong></h5>
            </div>
            <hr>
            {!!Form::open()
            ->post()
            ->route('devolucao.estadoFiscalStore')
            ->multipart()!!}
            <div class="pl-lg-4">
             <input type="hidden" value="{{$item->id}}" name="devolucao_id">
             <div>
                <h5>Cliente: <strong class="text-info">{{ $item->fornecedor->razao_social }}</strong></h5>
                <h5>CNPJ: <strong class="text-info">{{ $item->fornecedor->cpf_cnpj }}</strong></h5>
                <h5>Data: <strong class="text-info">{{ $item->created_at }}</strong></h5>
                <h5>Valor integral: <strong class="text-info">{{ $item->valor_integral }}</strong></h5>
                <h5>Valor devolvido: <strong class="text-info">{{ $item->valor_devolvido }}</strong></h5>
                <h5>Transportadora: <strong class="text-info">{{ $item->transportadora ? $item->transportadora->razao_social : '--' }}</strong></h5>
                <h5>Chave: <strong class="text-info">{{ $item->chave_nf_entrada }}</strong></h5>
            </div>
            <div class="row mt-5">
                <div class="col-3">
                    {!!Form::select('estado_emissao', 'Estado fiscal',
                    ['novo' => 'Novo', 'rejeitado' => 'Rejeitado', 'cancelado' => 'Cancelado', 'aprovado' => 'Aprovado']
                    )
                    ->attrs(['class' => 'form-select'])->value(isset($item) ? $item->estado_emissao : $item->estado_emissao)
                    !!}
                </div>
            </div>
            <hr class="mt-4">
            <div class="row">
                <div class="col-md-4 file-certificado">
                    {!! Form::file('xml', 'Procurar arquivo XML')->attrs(['accept' => '.xml']) !!}                    
                </div>
                <label class="text-info" id="filename"></label>
                
            </div>
            <hr class="mt-4">
            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-5">Salvar</button>
            </div>
        </div>
        {!!Form::close()!!}
    </div>
</div>
</div>
@endsection
