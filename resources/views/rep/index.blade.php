@extends('default.layout',['title' => 'Representante'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('rep.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo empresa
                    </a>
                </div>
            </div>
            <div class="col">
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-4">
                        {!!Form::text('nome', 'Pesquisar por nome')
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::tel('cpf', 'Cpf/Cnpj')->attrs(['class' => 'cpf_cnpj'])
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('plano', 'Plano', ['todos' => 'Todos'] + $planos->pluck('nome', 'id')->all())->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    <div class="col-md-3 mt-3">
                        {!!Form::select('estado', 'Estado', ['todos' => 'Todos', 'ativo' => 'Ativo', 'desativo' => 'Desativo'])->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    <div class="col-md-2 mt-3">
                        {!!Form::text('dias', 'Dia para expirar')->attrs(['class' => ''])
                        !!}
                    </div>
                    <div class="col-md-4 text-left mt-3 ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('representantes.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <hr />
                <div>
                    <h5>Lista de empresas</h5>
                    {{-- <p style="color: mediumblue">Registros: {{ sizeof($data) }} </p> --}}
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive tbl-400">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>#</th>
                                        <th>Nome</th>
                                        <th>Documento</th>
                                        <th>Data cadastro</th>
                                        <th>Endereço</th>
                                        <th>Cidade</th>
                                        <th>Plano</th>
                                        <th>Status</th>
                                        <th>Dias para expirar<th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data->empresas as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->empresa->razao_social }}</td>
                                            <td>{{ $item->empresa->cpf_cnpj }}</td>
                                            <td>{{ $item->empresa->created_at }}</td>
                                            <td>{{ $item->empresa->rua }}, {{ $item->empresa->numero }}</td>
                                            <td>{{ $item->empresa->cidade->nome }}</td>
                                            <td>@if($item->empresa->planoEmpresa)
                                                {{$item->empresa->planoEmpresa->plano->nome}}
                                                @else
                                                --
                                                @endif</td>
                                            <td>{{ $item->empresa->status() }}</td>
                                            <td>{{ $item->empresa->planoEmpresa }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            {{-- {!! $data->appends(request()->all())->links() !!} --}}
        </div>
    </div>
</div>
@endsection
