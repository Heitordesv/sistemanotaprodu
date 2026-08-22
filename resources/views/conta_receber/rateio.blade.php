@extends('layouts.app')

@section('content')

<div class="container">

    <h3>💰 Criar Conta com Rateio por Grupo</h3>

    <form action="{{ route('conta-receber.store-rateio') }}" method="POST">
        @csrf

        <div class="row">

            <!-- Empresa -->
            <div class="col-md-4">
                <label>Empresa</label>
                <select name="empresa_id" class="form-control" required>
                    @foreach($empresas as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->nome }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Grupo -->
            <div class="col-md-4">
                <label>Grupo de Clientes</label>
                <select name="grupo_id" id="grupo_id" class="form-control" required>
                    <option value="">Selecione</option>
                    @foreach($grupos as $g)
                        <option value="{{ $g->grupo_id }}">
                            Grupo {{ $g->grupo_id }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Valor -->
            <div class="col-md-4">
                <label>Valor Total</label>
                <input type="text" name="valor_integral" class="form-control" required>
            </div>

        </div>

        <div class="row mt-3">

            <div class="col-md-4">
                <label>Vencimento</label>
                <input type="date" name="data_vencimento" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label>Categoria</label>
                <input type="text" name="categoria_id" class="form-control">
            </div>

            <div class="col-md-4">
                <label>Parcelas</label>
                <input type="number" name="quantidade_parcelas" value="1" class="form-control">
            </div>

        </div>

        <div class="mt-3">
            <label>Referência</label>
            <input type="text" name="referencia" class="form-control">
        </div>

        <div class="mt-3">
            <label>Observação</label>
            <textarea name="observacao" class="form-control"></textarea>
        </div>

        <hr>

        <h5>👥 Preview do Rateio (aparece depois via JS)</h5>

        <div id="preview-rateio" class="alert alert-info">
            Selecione um grupo para visualizar o rateio.
        </div>

        <button class="btn btn-success mt-3">
            💾 Salvar Rateio
        </button>

    </form>

</div>

@endsection