@extends('default.layout', ['title' => 'Editar API Brasil'])

@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('apiBrasil.index') }}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Editar API Brasil</h5>
            </div>
            <hr>
            <form method="POST" action="{{ route('dispositivos.update', $item->id) }}">
                @csrf
                @method('PUT') <!-- Isso é importante para informar que estamos fazendo uma atualização -->

                <div class="form-group">
                    <label for="type">Tipo Dispositivo</label>
                    <select name="type_display" id="type" class="form-control" disabled>
                        <option value="cellphone" @if($item->type == 'cellphone') selected @endif>Celular</option>
                        <option value="tablet" @if($item->type == 'tablet') selected @endif>Tablet</option>
                    </select>
                    <input type="hidden" name="type" value="{{ $item->type }}">
                </div>


                <div class="form-group">
                    <label for="DeviceToken">DeviceToken</label>
                    <input type="text" class="form-control" name="DeviceToken" value="{{ old('DeviceToken', $item->DeviceToken) }}" required readonly>
                </div>

                <div class="form-group">
                    <label for="Bearer">Bearer</label>
                    <input type="text" class="form-control" name="Bearer" value="{{ old('Bearer', $item->Bearer) }}" required readonly>
                </div>

                <div class="form-group">
                    <label for="device_name">Nome Device</label>
                    <input type="text" class="form-control" name="device_name" value="{{ old('device_name', $item->device_name) }}" required readonly>
                </div>

                <div class="form-group">
                    <label for="device_key">Senha Dispositivo</label>
                    <input type="text" class="form-control" name="device_key" value="{{ old('device_key', $item->device_key) }}" required readonly>
                </div>

                <div class="form-group">
                    <label for="device_key">Servidor</label>
                    <input type="text" class="form-control" name="server_search" value="{{ old('server_search', $item->server_search) }}" required>
                </div>

                
                <div class="form-group">
                    <label for="situacao">Situação</label>
                    <select name="situacao" id="situacao" class="form-control">
                        <option value=""></option>
                        <option value="DISCONNECTED" @if($item->situacao == 'DISCONNECTED') selected @endif>DISCONNECTED</option>
                        <option value="inChat" @if($item->situacao == 'inChat') selected @endif>inChat</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success mt-3">Salvar</button>
            </form>
        </div>
    </div>
</div>
@endsection
