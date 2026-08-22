<div class="modal fade" id="modal-inutilizar_nfce" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">INUTILIZAÇÃO DE NÚMERO(s) DE NFCe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!!Form::open()
            ->post()
            ->route('nfce.inutilizar')
            !!}
            <div class="modal-body row">
                <div class="col-md-4">
                    {!! Form::tel('numero_nfce_inicial', 'Número NFCe inicial')
                    ->attrs(['class' => '']) !!}
                </div>
                <div class="col-md-4">
                    {!! Form::tel('numero_nfce_final', 'Número NFCe final')
                    ->attrs(['class' => '']) !!}
                </div>
               
                <div class="col-md-12 mt-3">
                    {!! Form::text('justificativa', 'Justificativa')
                    ->attrs(['class' => '']) !!}
                </div>
            </div>
            <div class="modal-footer">
                <button data-bs-dismiss="modal" id="btn-inutilizar-send" type="button" class="btn btn-primary px-5">Inutilizar</button>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>
