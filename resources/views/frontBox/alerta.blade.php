@extends('default.layout', ['title' => 'Alertas'])
@section('content')
<style type="text/css">
    .btn {
        margin-left: 5px;
    }
</style>

<div class="card card-custom gutter-b">
    <div class="card-body m-3">
        <h5>Para acessar o PDV é necessario ter cadastrado:</h5>
        <h4 class="mt-3">* Emitente fiscal com certificado digital</h4>
        <h4>* Natureza de operação cadastrado</h4>
        <h4>* Categorias</h4>
        <h4>* Produtos</h4>
        <h4>* Tributação</h4>

        <div class="row m-3">
            @if(sizeof($naturezas) == 0)
            <a href="{{ route('naturezas.index') }}" class="btn btn-danger col-2">ir para naturezas de operação</a>
            @endif

            @if($config == null)
            <a href="{{ route('configNF.index') }}" class="btn btn-danger col-2">ir para emitente fiscal</a>
            @endif

            @if(sizeof($tributacao) == 0)
            <a href="{{ route('tributos.index') }}" class="btn btn-danger col-2">ir para tributação</a>
            @endif

            @if(sizeof($categorias) == 0)
            <a href="{{ route('categorias.index') }}" class="btn btn-danger col-2">ir para categorias</a>
            @endif

            @if(sizeof($produtos) == 0)
            <a href="/produtos" class="btn btn-danger col-2">ir para produtos</a>
            @endif

        </div>
    </div>
</div>
@endsection
