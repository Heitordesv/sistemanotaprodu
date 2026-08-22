<div class="modal fade" id="modal-whatsApp" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body md-cliente">
                <div class="card-title d-flex align-items-center">
                    <h5 class="mb-0 text-primary">Enviar whatsApp</h5>
                </div>
                <hr>
                <div class="pl-lg-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            {!! Form::text('celular', 'Celular')->attrs(['class' => 'fone']) !!}
                        </div>
                        <div class="col-md-12">
                            {!! Form::text('texto', 'Texto')->attrs(['class' => '']) !!}
                        </div>
                        <div class="col-12 mt-5">
                            <button type="button" onclick="enviarWhatsApp()" class="btn btn-primary px-5">Enviar</button>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>
