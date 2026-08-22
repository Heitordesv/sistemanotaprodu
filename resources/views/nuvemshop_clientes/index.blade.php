@extends('default.layout',['title' => 'Clientes Nuvem Shop'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">

            <div class="col">
                <h6 class="mb-0 text-uppercase">Clientes Nuvem Shop</h6>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-3">
                        {!!Form::text('nome', 'Pesquisar por nome')
                        !!}
                    </div>

                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('nuvemshop-pedidos.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
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
                                        <th width="">Nome</th>
                                        <th width="">Documento</th>
                                        <th width="">Email</th>
                                        <th width="">Telefone</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->razao_social }}</td>
                                        <td>{{ $item->cpf_cnpj }}</td>
                                        <td>{{ $item->email }}</td>
                                        <td>{{ $item->telefone }}</td>
                                        <td>
                                            <a href="{{ route('clientes.edit', $item->id) }}" class="btn btn-warning btn-sm text-white">
                                                <i class="bx bx-edit"></i>
                                            </a>

                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Nada encontrado</td>
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
