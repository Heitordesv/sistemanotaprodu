<form method="POST" action="{{ route('apiFacebook.store') }}">
    @csrf

    <div class="form-group">
        <label for="nome_empresa">Nome da Empresa</label>
        <input type="text" class="form-control" name="nome_empresa" required>
    </div>

    <div class="form-group">
        <label for="pixel_id">ID do Pixel</label>
        <input type="text" class="form-control" name="pixel_id" required>
    </div>

    <div class="form-group">
        <label for="access_token">Access Token</label>
        <input type="text" class="form-control" name="access_token" required>
    </div>

    <button type="submit" class="btn btn-success mt-3">Salvar</button>
</form>
