<form method="POST" action="{{ route('motoboy.store') }}">
    @csrf
    <div class="form-group">
        <label for="Nome">Nome</label>
        <input type="text" class="form-control" name="deliveryman_name" required>
    </div>

    <div class="form-group">
        <label for="Telefone">Telefone</label>
        <input type="text" class="form-control" name="deliveryman_phone_number" required>
    </div>

    <button type="submit" class="btn btn-success mt-3">Salvar</button>
</form>
