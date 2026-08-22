@extends('default.layout', ['title' => 'Atualizar ordem de serviço'])

@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h4 class="mb-1">Atualizar OS #{{ $ordem->numero_sequencial ?? $ordem->id }}</h4>
                    <p class="text-muted mb-0">Revise o andamento e as informações de pagamento.</p>
                </div>
                <a href="{{ route('ordemServico.completa', $ordem->id) }}" class="btn btn-light btn-sm"><i class="bx bx-arrow-back"></i> Voltar</a>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted d-block">Cliente</small><strong>{{ optional($ordem->cliente)->razao_social ?? 'Não informado' }}</strong></div></div>
                <div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted d-block">Data da ordem</small><strong>{{ optional($ordem->created_at)->format('d/m/Y H:i') }}</strong></div></div>
                <div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted d-block">Total</small><strong class="text-success">R$ {{ __moeda($ordem->valor ?? 0) }}</strong></div></div>
            </div>

            <form method="POST" action="{{ route('ordemServico.alterarEstadoPost') }}">
                @csrf
                <input type="hidden" name="id" value="{{ $ordem->id }}">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="novo_estado" class="form-label required">Status da ordem</label>
                        <select class="form-select @error('novo_estado') is-invalid @enderror" id="novo_estado" name="novo_estado" required>
                            @foreach(['pendente' => 'Pendente', 'Em Andamento' => 'Em andamento', 'pronto' => 'Serviço pronto', 'finalizado' => 'Finalizado', 'reprovado' => 'Reprovado'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('novo_estado', $ordem->estado) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="novo_status_pagamento" class="form-label required">Status do pagamento</label>
                        <select class="form-select" id="novo_status_pagamento" name="novo_status_pagamento" required>
                            <option value="0" @selected((int) old('novo_status_pagamento', $ordem->status_pagamento) === 0)>Pagamento pendente</option>
                            <option value="1" @selected((int) old('novo_status_pagamento', $ordem->status_pagamento) === 1)>Pago</option>
                        </select>
                    </div>

                    <div class="col-12"><div id="campos-pagamento" class="row g-3 p-3 bg-light rounded">
                        <div class="col-md-3"><label for="valor_entrada" class="form-label">Entrada</label><input type="text" class="form-control moeda" id="valor_entrada" name="valor_entrada" value="{{ old('valor_entrada', __moeda($ordem->valor_entrada ?? 0)) }}"></div>
                        <div class="col-md-3"><label for="valor_pago" class="form-label">Valor pago</label><input type="text" class="form-control moeda" id="valor_pago" name="valor_pago" value="{{ old('valor_pago', __moeda($ordem->valor_pago ?? 0)) }}"></div>
                        <div class="col-md-3"><label for="desconto" class="form-label">Desconto</label><input type="text" class="form-control moeda" id="desconto" name="desconto" value="{{ old('desconto', __moeda($ordem->desconto ?? 0)) }}"></div>
                        <div class="col-md-3"><label for="forma_pagamento" class="form-label">Forma de pagamento</label><select class="form-select" id="forma_pagamento" name="forma_pagamento"><option value="">Selecione</option>@foreach(['dinheiro'=>'Dinheiro','cartao'=>'Cartão','pix'=>'PIX','boleto'=>'Boleto'] as $value=>$label)<option value="{{ $value }}" @selected(old('forma_pagamento', $ordem->forma_pagamento) === $value)>{{ $label }}</option>@endforeach</select></div>
                    </div></div>

                    <div class="col-12">
                        <label for="descricao" class="form-label required">Descrição / observações</label>
                        <textarea class="form-control @error('descricao') is-invalid @enderror" id="descricao" name="descricao" rows="4" maxlength="5000" required>{{ old('descricao', $ordem->descricao) }}</textarea>
                        @error('descricao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('ordemServico.completa', $ordem->id) }}" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Salvar alterações</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
(function () {
    const status = document.getElementById('novo_status_pagamento');
    const fields = document.getElementById('campos-pagamento');
    function togglePaymentFields() {
        fields.classList.toggle('d-none', status.value !== '1');
        fields.querySelectorAll('input, select').forEach(function (field) { field.disabled = status.value !== '1'; });
    }
    status.addEventListener('change', togglePaymentFields);
    togglePaymentFields();
})();
</script>
@endsection