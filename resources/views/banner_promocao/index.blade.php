@extends('default.layout', ['title' => 'Listar Banners Promocionais'])

@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">

            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <!-- Botão Cadastrar Banner -->
                    <a href="{{ route('banner_promocao.create') }}" class="btn btn-success">Cadastrar Banner Promocional</a>
                </div>
            </div>

            <h1>Listar Banners Promocionais</h1>

            @if(session('flash_sucesso'))
                <div class="alert alert-success">
                    {{ session('flash_sucesso') }}
                </div>
            @endif

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Imagem</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $banner)
                        <tr>
                            <td>{{ $banner->id_banner }}</td>
                            <td><img src="{{ asset('public/' . $banner->img_banner) }}" alt="Banner" style="width: 100px; height: auto;"></td>
                            <td>
                                <!-- Botão Editar -->

                                <!-- Formulário de Exclusão -->
                                <form action="{{ route('banner_promocao.destroy', $banner->id_banner) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Tem certeza que deseja excluir este banner?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Paginação -->
            {{ $data->links() }}

        </div>
    </div>
</div>
@endsection
