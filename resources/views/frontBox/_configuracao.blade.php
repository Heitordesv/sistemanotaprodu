<div class="row m-3">
    <p>O atalho deve ter o nome separados por teclas '+' . Exemplos: <strong>ctrl+shift+b , ctrl+h</strong></p>
    <div class="col-md-3 mt-2">
        {!! Form::text('finalizar', 'Finalizar Venda')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3 mt-2">
        {!! Form::text('reiniciar', 'Reiniciar')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3 mt-2">
        {!! Form::text('editar_desconto', 'Editar Desconto')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3 mt-2">
        {!! Form::text('editar_acrescimo', 'Editar Acréscimo')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3 mt-2">
        {!! Form::text('editar_observacao', 'Editar Observação')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3 mt-2">
        {!! Form::text('setar_valor_recebido', 'Setar Valor Recebido')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3 mt-2">
        {!! Form::text('forma_pagamento_dinheiro', 'Forma Pagamento Dinheiro')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3 mt-2">
        {!! Form::text('forma_pagamento_debito', 'Forma Pagamento Débito')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3 mt-2">
        {!! Form::text('forma_pagamento_credito', 'Forma Pagamento Crédito')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3 mt-2">
        {!! Form::text('forma_pagamento_pix', 'Forma Pagamento Pix')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3 mt-2">
        {!! Form::text('setar_leitor', 'Leitor Ativo')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3 mt-2">
        {!! Form::text('setar_quantidade', 'Setar Quantidade')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3 mt-2">
        {!! Form::text('finalizar_fiscal', 'Finalizar Venda Fiscal')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3 mt-2">
        {!! Form::text('finalizar_nao_fiscal', 'Finalizar Não Venda Fiscal')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3 mt-2">
        {!! Form::tel('balanca_digito_verificador', 'Digitos Referência Produto Balança')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3 mt-2">
        {!! Form::select('balanca_valor_peso', 'Tipo Unidade Balança', [1 => 'Valor', 2 => 'Peso'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-4 mt-2">
        {!! Form::select('valor_recebido_automatico', 'Valor Recebido Automático', [0 => 'Sim', 1 => 'Não'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-4 mt-2">
        {!! Form::text('mercadopago_public_key', 'Mercado Pago Public Key')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-4 mt-2">
        {!! Form::text('mercadopago_access_token', 'Mercado Pago Access Token')->attrs(['class' => '']) !!}
    </div>
    {{-- <div class="col-md-4 mt-2">
        {!! Form::select('modelo_pdv', 'Modelo PDV', [1 => 'PDV 1', 2 => 'PDV 2', 3 => 'PDV 3'])->attrs(['class' => 'form-select']) !!}
    </div> --}}
    <div class="col-md-4 mt-2">
        {!! Form::tel('impressora_modelo', 'Impressora Largura Valor entre(58 e 80)')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-4 mt-2">
        {!! Form::select('pagamento_padrao', 'Tipo de Pagamento Padrão', App\Models\VendaCaixa::tiposPagamento())->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="mt-4">
        <p>Tipos de Pagamento a serem mostrados:</p>
    </div>
    <div class="form-group col-12">
        <label class="col-form-label">
            Tipos de pagamento a serem mostrados
        </label>
        <div class="" style="display: grid;grid-template-columns: 1fr 1fr 1fr;">
            @foreach(App\Models\VendaCaixa::tiposPagamento() as $key => $t)
            <label>
                <input  type="checkbox" name="tipos_pagamento[]" value="{{$key}}" @if($item != null) @if(sizeof($item->tipos_pagamento) > 0 && in_array($key, $item->tipos_pagamento)) checked="true" @endif @endif>
                {{$t}}
            </label>
            @endforeach
        </div>
    </div>
    <div class="col mt-4">
        <button type="submit" class="btn btn-primary">Salvar Configurações</button>
    </div>
</div>
