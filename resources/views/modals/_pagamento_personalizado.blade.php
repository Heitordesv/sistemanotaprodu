<div class="modal fade" id="modal-pagamento_personalizado" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pagamentos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body row">
                <div class="col-md-6">
                    {!! Form::tel('intervalo', 'Intervalo')->value('30')
                    ->attrs(['class' => 'form-control']) !!}
                </div>
                <div class="col-md-6">
                    <label class="" id="">Quantidade de parcelas</label>
                    <select class="form-select form-select" id="qtd_parcelas"></select>
                </div>
                <div class="modal-footer mt-2">
                    <button type="button" class="btn btn-primary btn-pag_personalizado px-5" class="btn-close" data-bs-dismiss="modal" aria-label="Close">Gerar</button>
                </div>
            </div>
        </div>
    </div>
</div>
