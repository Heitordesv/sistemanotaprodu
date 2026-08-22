@extends('default.layout',['title' => 'Definir estoque'])
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
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Definir estoque para filiais</h5>
            </div>
            <hr>
            {!!Form::open()
            ->post()
            ->route('estoque.set-estoque-local')
            ->multipart()!!}
            <div class="pl-lg-4">
                <h6>Adicionar estoque ao produto: <strong style="color: royalblue">{{$item->nome}}</strong> </h6>
            </div>
            <input type="hidden" name="produto_id" value="{{ $item->id }}">

            <div class="mt-3">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Local</th>
                                <th>Quantidade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($locais as $key => $local)
                            @if(sizeof($grade) > 1)
                            @foreach($grade as $k => $produto)
                            <tr>
                                <td>
                                    {{$local}} -
                                    <strong>
                                        grade: {{$produto->str_grade}}
                                    </strong>
                                </td>
                                <td>
                                    <input type="hidden" value="{{$key}}" name="filial_id[]">
                                    <input type="hidden" value="{{$produto->id}}" name="produto_grade_id[]">
                                    <input id="quantidade" type="tel" class="form-control" required name="quantidade[]" value="{{ $item->estoqueAtual() }}">
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td>
                                    {{$local}}
                                </td>
                                <td>
                                    <input type="hidden" value="{{$key}}" name="filial_id[]">
                                    <input id="quantidade" type="tel" class="form-control" required name="quantidade[]" value="{{ $item->estoqueAtual() }}">
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <button class="btn btn-info" type="submit">Salvar</button>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>
@endsection
