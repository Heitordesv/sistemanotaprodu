<div class="row g-3">
    <div class="col-lg-6">
        <label for="inp-cliente_id" class="form-label required">Cliente</label>
        <div class="input-group">
            <select class="form-select select2 @error('cliente_id') is-invalid @enderror" name="cliente_id" id="inp-cliente_id" required>
                <option value="">Selecione um cliente</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}" @selected((string) old('cliente_id') === (string) $cliente->id)>{{ $cliente->razao_social }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-cliente" title="Cadastrar cliente"><i class="bx bx-plus"></i></button>
        </div>
        @error('cliente_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="descricao" class="form-label required">Descrição do serviço</label>
        <textarea class="form-control @error('descricao') is-invalid @enderror" name="descricao" id="descricao" rows="5" maxlength="5000" required placeholder="Descreva o problema, equipamento e serviço solicitado">{{ old('descricao') }}</textarea>
        @error('descricao')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Inclua os detalhes necessários para a execução do serviço.</div>
    </div>
    <div class="col-12 d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('ordemServico.index') }}" class="btn btn-light">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4"><i class="bx bx-save"></i> Salvar e adicionar itens</button>
    </div>
</div>