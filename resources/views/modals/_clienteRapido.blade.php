<div class="modal fade" id="modal-clienteRapido" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body md-cliente">
                <div class="card-title d-flex align-items-center">
                    <h5 class="mb-0 text-primary">Novo Cliente Delivery</h5>
                </div>
                <hr>
                <div class="pl-lg-4">
                    {!!Form::open()
                    ->post()
                    ->route('clientesDelivery.store')
                    !!}
                    <div class="row g-3">
                        <div class="col-md-6">
                            {!! Form::text('nome', 'Nome')->attrs(['class' => ''])->required() !!}
                        </div>
                        <div class="col-md-6">
                            {!! Form::text('sobre_nome', 'Sobre Nome')->attrs(['class' => ''])->required() !!}
                        </div>
                        <div class="col-md-6">
                            {!! Form::tel('celular', 'Celular')->attrs(['class' => 'fone'])->required() !!}
                        </div>
                        <div class="col-md-6">
                            {!! Form::text('senha', 'Senha')->type('password')->attrs(['class' => '']) !!}
                        </div>
                        <div class="col-12 mt-5">
                            <button type="submit" class="btn btn-primary px-5">Salvar</button>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>
