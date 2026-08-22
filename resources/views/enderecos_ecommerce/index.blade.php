@extends('default.layout',['title' => 'Endereços'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('clienteEcommerce.index')}}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
            <h4>Endereço de: <strong class="" style="color: rgb(43, 43, 177)">{{$cliente->nome}}</strong></h4>
            <p>Registros: {{ sizeof($cliente->enderecos) }}</p>
            <hr>
            <div class="col">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped">
                            <thead>
                                <tr>
                                    <th>Rua</th>
                                    <th>Número</th>
                                    <th>Bairro</th>
                                    <th>Cep</th>
                                    <th>Cidade</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cliente->enderecos as $item)
                                <tr>
                                    <td>{{ $item->rua }}</td>
                                    <td>{{ $item->numero }}</td>
                                    <td>{{ $item->bairro }}</td>
                                    <td>{{ $item->cep }}</td>
                                    <td>{{ $item->cidade }}</td>
                                    <td>
                                        <a href="{{ route('enderecosEcommerce.edit', $item) }}" class="btn btn-warning btn-sm text-white">
                                            <i class="bx bx-edit"></i>
                                        </a>
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
    </div>
</div>
@endsection
