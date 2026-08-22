<div class="modal fade" id="modal-clientePedido" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body md-cliente">
                <div class="card-title d-flex align-items-center">
                    <h5 class="mb-0 text-primary">Novo Cliente</h5>
                </div>
                <hr>
                <div class="pl-lg-4">
                    {!!Form::open()
                    ->post()
                    ->route('pedidos.storeCliente')
                    !!}
                    <div class="row g-3">
                        <div class="col-md-8">
                            {!! Form::text('razao_social', 'Nome')->attrs(['class' => ''])->required() !!}
                        </div>
                        <div class="col-md-4">
                            {!! Form::text('cpf_cnpj', 'CPF/CNPJ')->attrs(['class' => 'cpf_cnpj'])->required() !!}
                        </div>
                        <div class="col-md-2">
                            {!! Form::tel('limite_venda', 'Limite crédito')->attrs(['class' => 'moeda']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::tel('telefone', 'Telefone')->attrs(['class' => 'fone']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::tel('celular', 'Celular')->attrs(['class' => 'fone']) !!}
                        </div>
                        <div class="col-md-4">
                            {!! Form::select('cidade_id', 'Cidade')->attrs(['class' => 'select2'])->required() !!}
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

