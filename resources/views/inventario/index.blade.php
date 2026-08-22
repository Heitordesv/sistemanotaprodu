@extends('default.layout',['title' => 'Lista de inventário'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('inventario.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo inventário
                    </a>
                </div>
            </div>
            <div class="">
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row mt-5">
                    <div class="col-md-4">
                        {!!Form::tel('referencia', 'Pesquisar por referência')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('start_date', 'Data inicial')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('end_date', 'Data final')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::select('tipo', 'Tipo', App\Models\Inventario::tipos())->attrs(['class' => 'form-select'])
                        !!}
                    </div>

                    <div class="col-md-2 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        {{-- <a id="clear-filter" class="btn btn-danger"
						href="{{ route('inventario.index') }}">Limpar</a> --}}
                    </div>
                </div>

                {!!Form::close()!!}

                <hr />
                <div class="card">
                    <h5 class="m-3">Lista de inventário</h5>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th width="30%">Data início</th>
                                        <th width="20%">Data final</th>
                                        <th width="20%">Tipo</th>
                                        <th width="20%">Status</th>
                                        <th width="20%">Observação</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $item)
                                    <tr>
                                        <td>{{ $item->inicio }}</td>
                                        <td>{{ $item->fim }}</td>
                                        <td>{{ $item->tipo }}</td>
                                        <td>
                                            @if($item->status)
                                            <span class="btn btn-success position-relative me-lg-5 btn-sm">
                                                <i class="bx bx-like"></i> Ativo
                                            </span>
                                            @else
                                            <span class="btn btn-warning position-relative me-lg-5 btn-sm">
                                                <i class="bx bx-error"></i> Desativado
                                            </span>
                                            @endif
                                        </td>
                                        <td>{{ $item->observacao }}</td>
                                        <td>
                                            <form action="{{ route('inventario.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')
                                                <a href="{{ route('inventario.edit', $item) }}" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                @csrf
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                                <a class="btn btn-info btn-sm" title="Apontar" href="{{ route('inventario.apontar', $item->id) }}"><i class="bx bx-barcode"></i>
                                                </a>
                                            </form>
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
            {!! $data->appends(request()->all())->links() !!}

        </div>
    </div>
</div>
@endsection
