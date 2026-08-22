@extends('default.layout',['title' => 'Contador'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('contadores.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            {!!Form::open()->fill(request()->all())
            ->get()
            !!}
            <div class="row">
                <div class="col-md-4">
                    {!!Form::text('razao_social', 'Pesquisar')
                    !!}
                </div>
                <div class="col-md-3 text-left ">
                    <br>
                    <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i></button>
                    <a id="clear-filter" class="btn btn-danger" href="{{ route('contadores.show', $item->id) }}">Limpar</a>
                </div>
            </div>
            {!!Form::close()!!}
            <hr class="mt-4">
            <div class="row mt-4">
                <form action="{{ route('contadores.set-empresa') }}" method="post">
                    @csrf
                    <input type="hidden" name="contador_id" value="{{ $item->id }}">
                    <div class="col-md-6">
                        {!!Form::select('empresa', 'Pesquisar empresa a ser atribuída')->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-3">
                        <br>
                        <button class="btn btn-success"> Atribuir empresa</button>
                    </div>
                </form>

            </div>
            <hr class="mt-4">
            <h5 class="mt-3">Lista de empresas do contador: <strong>{{$item->razao_social}}</strong></h5>
            <p>Registros: {{ sizeof($empresas) }}</p>
            <div class="table-responsive">
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Razão Social</th>
                            <th>Nome Fantasia</th>
                            <th>CNPJ</th>
                            <th>Cidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->razao_social }}</td>
                            <td>{{ $item->nome_fantasia }}</td>
                            <td>{{ $item->cpf_cnpj }}</td>
                            <td>{{ $item->cidade->info }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@section('js')
<script type="text/javascript">
    $(function() {
        setTimeout(() => {
            $("#inp-empresa").select2({
                minimumInputLength: 2
                , language: "pt-BR"
                , placeholder: "Digite para buscar a empresa"
                , theme: "bootstrap4",

                ajax: {
                    cache: true
                    , url: path_url + 'empresas/buscar'
                    , dataType: "json"
                    , data: function(params) {
                        console.clear()
                        var query = {
                            pesquisa: params.term
                        , };
                        return query;
                    }
                    , processResults: function(response) {
                        console.log("response", response);
                        var results = [];
                        $.each(response, function(i, v) {
                            var o = {};
                            o.id = v.id;

                            o.text = v.razao_social
                            o.value = v.id;
                            results.push(o);
                        });
                        return {
                            results: results
                        };
                    }
                }
            });
            // $('.select2-selection__arrow').addClass('select2-selection__arroww')
            // $('.select2-selection__arrow').removeClass('select2-selection__arrow')
        }, 100);
    });

</script>
@endsection

@endsection
