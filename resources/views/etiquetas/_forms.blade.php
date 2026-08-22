<div class="row g-3">
    <div class="col-md-6">
        {!!Form::text('nome', 'Nome')->attrs(['class' => ''])->required() !!}
    </div>

    <div class="col-md-3">
        <label for="cor_fundo" class="form-label">Cor de Fundo da Etiqueta</label>
        <input type="color" name="cor_fundo" id="cor_fundo" 
               class="form-control form-control-color" 
               title="Escolha a cor de fundo" 
               value="{{ old('cor_fundo', $item->cor_fundo ?? '#ffffff') }}">
    </div>

    <div class="col-md-3">
        <label for="cor_fonte" class="form-label">Cor da Fonte da Etiqueta</label>
        <input type="color" name="cor_fonte" id="cor_fonte" 
               class="form-control form-control-color" 
               title="Escolha a cor da fonte" 
               value="{{ old('cor_fonte', $item->cor_fonte ?? '#000000') }}">
    </div>

    <div class="col-md-3">
        {!!Form::tel('altura', 'Altura mm')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-3">
        {!!Form::tel('largura', 'Largura mm')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-3">
        {!!Form::tel('etiquestas_por_linha', 'Etiqueta por linha')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-3">
        {!!Form::tel('distancia_etiquetas_lateral', 'Dist. entre etiquetas mm')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-3">
        {!!Form::tel('distancia_etiquetas_topo', 'Dist. etiquetas topo mm')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-3">
        {!!Form::tel('quantidade_etiquetas', 'Quantidade etiquetas')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-3">
        {!!Form::tel('tamanho_fonte', 'Tamanho da fonte')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-3">
        {!!Form::tel('tamanho_codigo_barras', 'Tamanho do cod. barras')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-3">
        {!!Form::select('nome_empresa', 'Nome da empresa', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-3">
        {!!Form::select('nome_produto', 'Nome do Produto', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-3">
        {!!Form::select('valor_produto', 'Valor do Produto', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-3">
        {!!Form::select('codigo_produto', 'Código do Produto', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-3">
        {!!Form::select('codigo_barras_numerico', 'Código de barras numérico', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select']) !!}
    </div>
    
    <div class="col-md-12">
        {!!Form::text('observacao', 'Observação')->attrs(['class' => '']) !!}
    </div>
    
    <div class="col-12 mt-4">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>