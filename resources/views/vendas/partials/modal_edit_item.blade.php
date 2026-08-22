<div class="modal fade" id="modal-edit_item" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>


            <div class="modal-body">
                <div class="row">
                    <div class="col-md-2">
                        {!! Form::tel('quantidade_modal', 'Quantidade')->attrs(['class' => 'qtd']) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::tel('valor_modal', 'Valor unitário')->attrs(['class' => 'moeda']) !!}
                    </div>

                    <div class="col-md-5">
                        {!! Form::text('x_pedido', 'Descriçao do pedido')->attrs(['class' => '']) !!}
                    </div>

                    <div class="col-md-3">
                        {!! Form::text('num_item_pedido', 'Nº item do pedido')->attrs(['class' => '']) !!}
                    </div>
                    
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="salvarItem()" class="btn btn-primary px-5">Editar</button>
            </div>

        </div>
    </div>
</div>
