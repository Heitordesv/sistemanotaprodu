@extends('default.layout',['title' => 'Lojas'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">

            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Lista de Lojas</h6>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-3">
                        {!!Form::text('nome', 'Pesquisar por nome')
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('status', 'Estado', ['todos' => 'Todos', 1 => 'Ativo', 2 => 'Desativado'])->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('marcas.index') }}"><i class="bx bx-eraser"></i>Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>#</th>
                                        <th>Data Cadastro</th>
                                        <th>Empresa</th>
                                        <th>Cidade</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ __data_pt($item->created_at, 0) }}</td>
                                        <td>{{ $item->nome }}</td>
                                        <td>{{ $item->empresa->cidade->nome }}</td>
                                        <td>
                                            @if($item->status)
                                            <span class="btn btn-success btn-sm">
                                                ATIVO
                                            </span>
                                            @else
                                            <span class="btn btn-danger btn-sm">
                                                DESATIVADO
                                            </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="codigo" style="width: 200px;">
                                                @if($item->status)
                                                <a onclick='swal("Atenção!", "Deseja alterar o status desta loja?", "warning").then((sim) => {if(sim){ location.href="/lojas/alterarStatus/{{ $item->id }}" }else{return false} })' href="#!"  class="btn btn-sm btn-warning">
                                                    Bloquear
                                                </a>
                                                @else
                                                <a onclick='swal("Atenção!", "Deseja alterar o status desta loja?", "warning").then((sim) => {if(sim){ location.href="/lojas/alterarStatus/{{ $item->id }}" }else{return false} })' href="#!" class="btn btn-sm btn-success">
                                                    Desbloquear
                                                </a>
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
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
