
@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Enviar Push para Empresa</h1>

    @if(session('flash_sucesso'))
        <div class="bg-green-200 text-green-800 p-3 rounded mb-4">
            {{ session('flash_sucesso') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-200 text-red-800 p-3 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('push.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 bg-white p-6 rounded shadow">
        @csrf

        <div>
            <label for="empresa_id" class="block font-semibold mb-1">Empresa</label>
            <select name="empresa_id" id="empresa_id" class="w-full border rounded p-2" required>
                <option value="">Selecione uma empresa</option>
                @foreach($empresas as $empresa)
                    <option value="{{ $empresa->id }}">{{ $empresa->nome }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="titulo" class="block font-semibold mb-1">Título</label>
            <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}" class="w-full border rounded p-2" maxlength="100" required>
        </div>

        <div>
            <label for="texto" class="block font-semibold mb-1">Mensagem</label>
            <textarea name="texto" id="texto" rows="4" class="w-full border rounded p-2" required>{{ old('texto') }}</textarea>
        </div>

        <div>
            <label for="path_img" class="block font-semibold mb-1">Imagem (opcional)</label>
            <input type="file" name="path_img" id="path_img" class="w-full border rounded p-2">
        </div>

        <div>
            <label for="referencia_produto" class="block font-semibold mb-1">Referência Produto (opcional)</label>
            <input type="text" name="referencia_produto" id="referencia_produto" value="{{ old('referencia_produto') }}" class="w-full border rounded p-2">
        </div>

        <div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Enviar Push
            </button>
        </div>
    </form>
</div>
@endsection