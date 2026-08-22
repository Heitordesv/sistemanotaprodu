@extends('default.layout',['title' => 'Locação itens'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('locacao.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Locação itens</h5>
            </div>
            <hr>
            {!!Form::open()
            ->post()
            ->route('locacao.storeItem')
            !!}
            <input type="hidden" id="idLocacao" name="locacao_id" value="{{$locacao->id}}">
            <div class="pl-lg-4">
                <div class="row">
                    <div class="col-md-4">
                        {!! Form::select('produto_id', 'Produto/Item')->attrs(['class' => 'select2']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::tel('valor', 'Valor')->attrs(['class' => 'moeda']) !!}
                    </div>
                    <div class="col-md-6">
                        {!! Form::text('observacao', 'Observação')->attrs(['class' => '']) !!}
                    </div>
                </div>
                <div class="col-12 mt-5">
                    <button type="submit" class="btn btn-primary px-5"><i class="bx bx-check"></i>Adicionar item</button>
                </div>
            </div>
            <hr class="mt-4">
            {!!Form::close()!!}
            <div class="card-body">
				<h5>Itens:</h5>
                <div class="table-responsive">
                    <table class="table mb-0 table-striped">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Valor</th>
                                <th>Observação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($locacao->itens as $item)
                            <tr>
                                <td>{{ $item->produto->nome }}</td>
                                <td>{{ __moeda($item->valor) }}</td>
                                <td>{{ $item->observacao }}</td>
                                <td>
                                    <form action="{{ route('locacao.deleteItem', $item->id) }}" method="delete" id="form-{{$item->id}}">
                                        @method('delete')
                                        @csrf
                                        <button type="button" class="btn btn-delete btn-sm btn-danger">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
			<hr>
            <div class="col-lg-12">
                <h5>Data de início: <strong class="text-info">{{ __data_pt($locacao->inicio, 0) }}</strong></h5>
                <h5>Data de término: <strong class="text-info">
                        @if($locacao->fim != '1969-12-31')
                        {{ __data_pt($locacao->fim, 0)}}
                        @else
                        --
                        @endif
                    </strong></h5>
                <h5>Cliente: <strong class="text-info">{{$locacao->cliente->razao_social}}</strong></h5>
                <h5>Total: R$ <strong class="text-success">{{number_format($locacao->total, 2, ',', '.')}}</strong></h5>
                <h5>Observação: <strong class="text-success">{{$locacao->observacao}}</strong> <a data-bs-toggle="modal" data-bs-target="#modal-locacao"><i class="bx bx-edit text-warning"></i></a></h5>
                @if($locacao->status == 0)
                <a href="/locacao/alterarStatus/{{$locacao->id}}" class="btn btn-info">
                    <i class="bx bx-check"></i>
                    Alterar para finalizado
                </a>
                @endif
                <a target="_blank" href="/locacao/comprovante/{{$locacao->id}}" class="btn btn-success">
                    <i class="bx bx-printer"></i>
                    Comprovante
                </a>
            </div>
        </div>
    </div>
</div>

@section('js')
<script type="text/javascript">
    $('#inp-produto_id').change(() => {
        let p = $('#inp-produto_id').val()
        if (p > 0) {
            console.clear()
            let idLocacao = $('#idLocacao').val()
            $.get(path_url + 'locacao/validaEstoque/' + p + '/' + idLocacao)
                .done((res) => {
                    console.log("res", res)
                    if (res.semEstoqueData != "") {
                        swal("Erro", "Produto sem estoque na data " + res.semEstoqueData, "error")
                        $('#inp-produto_id').val('').change()
                        $('#inp-valor').val('')
                    } else {
                        $('#inp-valor').val(parseFloat(res.valor_locacao).toFixed(casas_decimais).replace(".", ","))
                    }
                })
                .fail((err) => {
                    console.log(err)
                    swal('Erro', 'Algo deu errado', 'error')
                })
        } else {
            $('#inp-valor').val('')
        }
    })

</script>
@endsection

@include('modals._locacao')

@endsection
