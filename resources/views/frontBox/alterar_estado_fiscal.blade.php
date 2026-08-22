@extends('default.layout', ['title' => 'Alterar estado fiscal Nfc-e'])
@section('content')

@section('css')
<style type="text/css">
    input[type="file"] {
        display: none;
    }

    .file label {
        padding: 10px 10px;
        width: 100%;
        background-color: #8833FF;
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
    <div class="card ">

        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <h5>Venda: <strong style="color: mediumblue">{{ $item->id }}</strong> </h5>
            </div>
            <hr>
            <div class="row">
                <h5>Data: {{ $item->created_at }}</h5>
            </div>
            <div class="mt-3">
                <h5 class="mt-3">Valor total: <strong style="color: mediumseagreen">R$ {{ __moeda($item->valor_total) }}</strong> </h5>
                <h5 class="mt-3">Chave NFe: {{$item->chave != "" ? $item->chave : '--'}}</h5>
                {!! Form::open()
                ->put()
                ->route('nfce.update-state', [$item->id])
                ->multipart()
                !!}
                <div class="row mt-3">
                    <div class="row mt-3">
                        <div class="col-md-2">
                            <h5>Estado:</h5>
                        </div>
                        <div class="col-md-3">
                            <select class="col-3 form-select" id="estado" name="estado_emissao">
                                <option @if($item->estado_emissao == 'novo') selected @endif value="novo">NOVO</option>
                                <option @if($item->estado_emissao == 'aprovado') selected @endif value="aprovado">APROVADO</option>
                                <option @if($item->estado_emissao == 'rejeitado') selected @endif value="rejeitado">REJEITADO</option>
                                <option @if($item->estado_emissao == 'cancelado') selected @endif value="cancelado">CANCELADO</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-2 mt-4">
                        <h5>Upload XML: </h5>
                    </div>
                    <div class="col-3 file mt-1" style="margin-top: -23px; margin-left: -8px">
                        {!! Form::file('xml', 'Procurar arquivo')->attrs(['accept' => '.xml']) !!}
                        <span class="text-danger" id="filename"></span>
                    </div>
                    <hr class="mt-4">
                    <div class="mt-4">
                        <button class="btn btn-info px-5" type="submit"><i class="bx bx-check"></i> Salvar</button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection
