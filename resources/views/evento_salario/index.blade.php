@extends('default.layout',['title' => 'Eventos'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('eventoSalario.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo evento
                    </a>
                </div>
            </div>
            <div class="col">
                <h5 class="">Lista de eventos</h5>
                <p style="color: rgb(14, 14, 226)">Eventos: {{ sizeof($data) }}</p>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-5">
                        {!!Form::text('nome', 'Pesquisar por nome')
                        !!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('eventoSalario.index') }}"><i class="bx bx-eraser"></i>Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <hr>
                <div class="mt-3">
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped">
                            <thead class="">
                                <tr>
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Método</th>
                                    <th>Ativo</th>
                                    <th>Condição</th>
                                    <th>Tipo valor</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                <tr>
                                    <td>{{ $item->nome }}</td>
                                    <td>{{ $item->tipo }}</td>
                                    <td>{{ $item->metodo }}</td>
                                    <td>
                                        @if($item->ativo)
                                        <span class="btn btn-success btn-sm">Sim</span>
                                        @else
                                        <span class="btn btn-danger btn-sm">Não</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->condicao }}</td>
                                    <td>{{ $item->tipo_valor }}</td>

                                    <td>
                                        <form action="{{ route('eventoSalario.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')
                                            <a href="{{ route('eventoSalario.edit', $item) }}" class="btn btn-warning btn-sm text-white">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            @csrf
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Nada encontrado</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            {!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>
@endsection
