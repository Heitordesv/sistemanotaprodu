@extends('default.layout',['title' => 'Empresas'])
@section('content')

<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="card-body">
                <h4>Lista de Empresas</h4>
                <div class="table-responsive">
                    <table class="table mb-0 table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>CPF/CNPJ</th>
                                <th>Rua</th>
                                <th>Número</th>
                                <th>Bairro</th>
                                <th>Cidade</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $item)
                            <tr>
                                <td>{{ $item->razao_social }}</td>
                                <td>{{ $item->cpf_cnpj }}</td>
                                <td>{{ $item->rua }}</td>
                                <td>{{ $item->numero }}</td>
                                <td>{{ $item->bairro }}</td>
                                <td>{{ $item->cidade->info }}</td>
                                <td>
                                    <form method="post" action="{{ route('contador.set-empresa') }}">
                                        @csrf
                                        <a title="Detalhes da Empresa" class="btn btn-info btn-sm" href="{{ route('contador.empresaDetalhes', $item->id) }}">
                                            <i class="bx bx-file"></i>
                                        </a>
                                        @if($empresaSelecionada != $item->id)
                                        <input type="hidden" name="empresa" value="{{$item->id}}">
                                        <button title="Selecionar Empresa" class="btn btn-success btn-sm" type="submit">
                                            <i class="bx bx-check"></i>
                                        </button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
