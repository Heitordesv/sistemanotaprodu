<form method="POST" action="{{ route('dispositivos.store') }}">
    @csrf
    <div class="form-group">
        <label for="DeviceToken">DeviceToken</label>
        <input type="text" class="form-control" name="DeviceToken" required>
    </div>

    <div class="form-group">
        <label for="Bearer">Bearer</label>
        <input type="text" class="form-control" name="Bearer" value="{{ env('BEARER_TOKEN_LOGIN_API_BRASIL') }}" required>
    </div>

    <button type="submit" class="btn btn-success mt-3">Salvar</button>
</form>
