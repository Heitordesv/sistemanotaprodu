@extends('default.layout', ['title' => 'Consulta Débitos - API Brasil'])

@section('content')
<div class="container mt-5">
    <h2>Consulta de Débitos por CPF</h2>

    <form action="{{ route('debitos.consultar') }}" method="POST" class="mt-3">
        @csrf
        <div class="mb-3">
            <label for="cpf" class="form-label">CPF</label>
            <input 
                type="text" 
                name="cpf" 
                id="cpf" 
                class="form-control" 
                placeholder="000.000.000-00" 
                required
                maxlength="14">
        </div>
        <button type="submit" class="btn btn-primary">Consultar</button>
    </form>
</div>

{{-- Script para aplicar máscara de CPF --}}
@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function(){
        $('#cpf').mask('000.000.000-00');
    });
</script>
@endsection
@endsection
