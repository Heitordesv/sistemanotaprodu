@extends('default.layout', ['title' => 'Cadastrar Banner'])

@section('content')
<div class="page-content">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h4 mb-0">📢 Cadastrar Banner Promocional</h1>
                <a href="{{ route('banner_promocao.index') }}" class="btn btn-secondary btn-sm">← Voltar para listagem</a>
            </div>

            @if(session('flash_sucesso'))
                <div class="alert alert-success">
                    {{ session('flash_sucesso') }}
                </div>
            @endif

            <form action="{{ route('banner_promocao.store') }}" method="POST" enctype="multipart/form-data" class="mb-5">
                @csrf
                <div class="mb-3">
                    <label for="img_banner" class="form-label">Imagem do Banner <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="img_banner" name="img_banner" required>
                </div>

                <input type="hidden" name="confirma_banner" value="1">

                <button type="submit" class="btn btn-success">
                    ✅ Cadastrar Banner
                </button>
            </form>

            <hr>

               

        </div>
    </div>
</div>
@endsection
