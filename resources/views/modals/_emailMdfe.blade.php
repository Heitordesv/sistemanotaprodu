<div class="modal fade" id="modal-emailMdfe" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body md-cliente">
                <div class="card-title d-flex align-items-center">
                    <h5 class="mb-0 text-primary">Enviar xml por e-mail - MDFE</h5>
                </div>
                <hr>
                <div class="pl-lg-4">
                    {!!Form::open()
                    ->post()
                    ->route('mdfe.enviarXml')
                    !!}
                    @isset($item)
                    <input type="hidden" name="mdfe_id" id="" value=" {{$item->id}}">   
                    @endisset
                    <div class="row g-3">
                        <div class="col-md-12">
                            {!! Form::text('email', 'E-mail')->attrs(['class' => '']) !!}
                        </div>
                        <div class="col-12 mt-5">
                            <button type="submit" class="btn btn-primary px-5">Enviar</button>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>
