@extends('default.layout', ['title' => 'Editar API Facebook'])

@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('apiFacebook.index') }}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Editar API Facebook</h5>
            </div>
            <hr>
            <form method="POST" action="{{ route('apiFacebook.update', $item->id) }}">
                @csrf
                @method('PUT') <!-- Usando PUT para atualização -->

                <div class="form-group">
                    <label for="nome_empresa">Nome da Empresa</label>
                    <input type="text" class="form-control" name="nome_empresa" value="{{ old('nome_empresa', $item->nome_empresa) }}" required>
                </div>

                <div class="form-group">
                    <label for="pixel_id">ID do Pixel</label>
                    <input type="text" class="form-control" name="pixel_id" value="{{ old('pixel_id', $item->pixel_id) }}" required>
                </div>

                <div class="form-group">
                    <label for="access_token">Access Token</label>
                    <input type="text" class="form-control" name="access_token" value="{{ old('access_token', $item->access_token) }}" required>
                </div>

                <button type="submit" class="btn btn-success mt-3">Salvar</button>
            </form>
        </div>
    </div>
</div>
@endsection
