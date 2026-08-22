<div class="modal fade" id="modal-adicionais" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content m-3">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Produto - <strong id="nome_produto"></strong></h5>
                <div class="ms-auto">
                    <h6 class="text-info unit">VALOR UNITÁRIO DO PRODUTO - R$ <strong id="valor_produto"> </strong></h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!!Form::open()
            ->post() 
            ->route('pedidosDelivery.store')
            !!}
            <div class="modal-body">
                <h5 class="tipo-pizza d-none">Adicionais para pizza</h5>
                {!! Form::hidden('pedido_nr', '')->attrs(['class' => '']) !!}

                <div class="div-adicionais">    
                </div>

                <div class="row mt-3 tamanho-pizza d-none">
                    <div class="col-md-4">
                        <label for="">Escolha o tamanho da pizza</label>
                        <select name="tamanho_pizza" id="inp-tamanho_pizza" class="form-select tamanho_pizza">
                            <option value="">Selecione</option>
                            @foreach ($tamanhos as $item)
                            <option onclick="tamPizza('{{$item->id}}', '{{$item->maximo_sabores}}')" data-qtdsabores="{{$item->maximo_sabores}}" value="{{$item->id}}">{{$item->nome}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="margin-left: 250px" class="col-md-4 mt-3">
                        <h6>Selecione até <strong id="tamanhos_pizza" class="qtd_sabores"> 0 </strong>Sabor(es) </h6>
                    </div>
                    <div class="row pizzas mt-1">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        {!! Form::text('observacao', 'Observação')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-4 mt-3 quantidade">
                        {!! Form::tel('quantidade', 'Quantidade de Produto')->attrs(['class' => '']) !!}
                    </div>
                </div>
                <div class="input_hidden">
                </div>
                <div class="mt-3">
                    <h5>Valor total - R$ <strong id="valor_com_adicional"></strong></h5>
                </div>
                <div class="col-md-4">
                    {!! Form::hidden('prod_id', '')->attrs(['class' => '']) !!}
                </div>
                <div class="col-md-4">
                    {!! Form::hidden('valor_com_add', '') !!}
                </div>

                <input type="hidden" name="inp_adicionais" id="inp-adicionais" value="">
                <input type="hidden" name="inp_sabores" id="inp-sabores" value="">
                <hr>
                <div class="">
                    <button type="submit" class="btn btn-info">Adicionar Produto</button>
                </div>
            </div>

            {!!Form::close()!!}
        </div>
    </div>
</div>
