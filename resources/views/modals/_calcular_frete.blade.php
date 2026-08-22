<div class="modal fade" id="modal-calcular_frete" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Calcular Frete - Correios</h5>
            </div>
            <div class="modal-body row">
                <div class="col-md-4">
                    {!! Form::text('cep_origem', 'Cep Origem')->attrs(['class' => 'cep']) !!}
                </div>
                <div class="col-md-4">
                    {!! Form::tel('', 'Cep Destino')->attrs(['class' => 'cep']) !!}
                </div>
                <div class="col-md-4">
                    {!! Form::tel('', 'Peso')->attrs(['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="modal-body row">
                <div class="col-md-4">
                    {!! Form::text('cep_origem', 'Comprimento')->attrs(['class' => '']) !!}
                </div>
                <div class="col-md-4">
                    {!! Form::tel('', 'Altura')->attrs(['class' => '']) !!}
                </div>
                <div class="col-md-4">
                    {!! Form::tel('', 'Largura')->attrs(['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="modal-footer">
                <button id="btn-store-cal_frete" type="button" class="btn btn-primary px-5">Calcular</button>
            </div>
        </div>
    </div>
</div>


