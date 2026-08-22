<form method="POST" action="{{ route('motoboy.update', $motoboy->id) }}">
    @csrf
    @method('PUT') <!-- Adicionando o método PUT, pois estamos atualizando -->

    <div class="form-group">
        <label for="Nome">Nome</label>
        <input type="text" class="form-control" name="deliveryman_name" value="{{ old('deliveryman_name', $motoboy->deliveryman_name) }}" required>
    </div>

    <div class="form-group">
        <label for="Telefone">Telefone</label>
        <input type="text" class="form-control" name="deliveryman_phone_number" value="{{ old('deliveryman_phone_number', $motoboy->deliveryman_phone_number) }}" required>
    </div>

    <button type="submit" class="btn btn-success mt-3">Salvar</button>
</form>
