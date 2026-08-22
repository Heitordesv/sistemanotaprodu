<div class="modal fade" id="modal-categoriaEcommerce" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Categoria Ecommerce</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="col-md-12">
                    {!! Form::text('nome_categoriaEcommerce', 'Nome')->attrs(['class' => 'form-control ignore']) !!}
                </div>
            </div>

            <div class="modal-footer">
                <button id="btn-store-categoriaEcommerce" type="button" class="btn btn-primary px-5">Salvar</button>
            </div>
        </div>
    </div>
</div>


