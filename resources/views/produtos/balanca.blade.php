@extends('default.layout',['title' => 'Produtos balança'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('produtos.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <hr>
            {!!Form::open()
            ->post()
            // ->route('produtos.store')
            ->multipart()!!}
            <div class="pl-lg-4">
                <div class="row g-3">
                    <div class="col-md-4 mt-2">
                        {!! Form::select('modelo', 'Modelo da balança', App\Models\Produto::modelosBalanca())->attrs(['class' => 'form-select']) !!}
                    </div>
                    <div class="mt-5">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="todos"></th>
                                        <th>Produto</th>
                                        <th>Referência</th>
                                        <th>Valor de venda</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $item)
                                    <tr>
                                        <td><input type="checkbox" class="check_prod" name="produto_id[]" value="{{$item->id}}"></td>
                                        <td>{{$item->nome}}</td>
                                        <td>{{$item->referencia_balanca}}</td>
                                        <td>{{ __moeda($item->valor_venda) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-info">Gerar arquivo</button>
                    </div>
                </div>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>

@section('js')
<script type="text/javascript">
    $('#todos').click(() => {
        if ($('#todos').is(":checked")) {
            $('.check_prod').prop('checked', true);
        } else {
            $('.check_prod').prop('checked', false);
        }
    })

</script>
@endsection

@endsection
