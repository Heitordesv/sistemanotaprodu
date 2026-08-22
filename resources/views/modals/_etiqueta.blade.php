<div class="modal fade" id="modal-etiqueta" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            {!!Form::open()
            ->post()
            ->route('etiquetas.store')
            ->multipart()!!}
            <div class="row m-3">
                <h5>Cadastrar etiqueta</h5>
                <div class="col-md-6">
                    {!!Form::text('nome', 'Nome')->attrs(['class' => '']) !!}
                </div>
                <div class="col-md-3">
                    {!!Form::tel('altura', 'Altura mm')->attrs(['class' => '']) !!}
                </div>
                <div class="col-md-3">
                    {!!Form::tel('largura', 'Largura mm')->attrs(['class' => '']) !!}
                </div>
                <div class="col-md-3">
                    {!!Form::tel('etiquestas_por_linha', 'Etiqueta por linha')->attrs(['class' => '']) !!}
                </div>
                <div class="col-md-3">
                    {!!Form::tel('distancia_etiquetas_lateral', 'Dist. entre etiquetas mm')->attrs(['class' => '']) !!}
                </div>
                <div class="col-md-3">
                    {!!Form::tel('distancia_etiquetas_topo', 'Dist. etiquetas topo mm')->attrs(['class' => '']) !!}
                </div>
                <div class="col-md-3">
                    {!!Form::tel('quantidade_etiquetas', 'Quantidade etiquetas')->attrs(['class' => '']) !!}
                </div>
                <div class="col-md-3">
                    {!!Form::tel('tamanho_fonte', 'Tamanho da fonte')->attrs(['class' => '']) !!}
                </div>
                <div class="col-md-3">
                    {!!Form::tel('tamanho_codigo_barras', 'Tamanho do cod. barras')->attrs(['class' => '']) !!}
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
            {!!Form::close()!!}
        </div>
    </div>
</div>
