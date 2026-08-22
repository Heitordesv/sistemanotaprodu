<div class="row g-3">

    {{-- REFERÊNCIA --}}
    <div class="col-md-3">

        {!! Form::text('referencia', 'Referência')
            ->attrs([
                'class' => 'form-control'
            ])
            ->required()
        !!}

    </div>

    {{-- FORNECEDOR --}}
    <div class="col-md-4">

        <label for="inp-fornecedor_id">
            Fornecedor
        </label>

        <div class="input-group">

            <select
                class="form-control select2 fornecedor_id @if($errors->has('fornecedor_id')) is-invalid @endif"
                name="fornecedor_id"
                id="inp-fornecedor_id">

                <option value="">
                    Selecione
                </option>

                @foreach($fornecedores as $fornecedor)

                    <option value="{{ $fornecedor->id }}"
                        @if(isset($item) && $item->fornecedor_id == $fornecedor->id)
                            selected
                        @endif>

                        {{ $fornecedor->razao_social }}

                    </option>

                @endforeach

            </select>

            <button class="btn btn-primary"
                type="button"
                onclick="window.location='{{ route('fornecedores.create') }}'">

                <i class="bx bx-plus"></i>

            </button>

        </div>

    </div>

    {{-- FUNCIONÁRIO --}}
    <!--<div class="col-md-3">

        <label for="inp-funcionario_id">
            Funcionário
        </label>

        <select
            class="form-control select2 funcionario_id @if($errors->has('funcionario_id')) is-invalid @endif"
            name="funcionario_id"
            id="inp-funcionario_id">

            <option value="">
                Selecione
            </option>

            @foreach($funcionarios as $funcionario)

                <option value="{{ $funcionario->id }}"
                    @if(isset($item) && $item->funcionario_id == $funcionario->id)
                        selected
                    @endif>

                    {{ $funcionario->nome }}

                </option>

            @endforeach

        </select>

    </div>-->

    {{-- CATEGORIA --}}
    <div class="col-md-2">

        {!! Form::select(
            'categoria_id',
            'Categoria',
            $categorias->pluck('nome', 'id')->all()
        )
        ->attrs([
            'class' => 'form-select'
        ])
        ->required()
        !!}

    </div>

    {{-- VALOR --}}
    <div class="col-md-2">

        {!! Form::tel('valor_integral', 'Valor')
            ->attrs([
                'class' => 'form-control moeda'
            ])
            ->required()
            ->value(isset($item) ? __moeda($item->valor_integral) : '')
        !!}

    </div>

    {{-- VENCIMENTO --}}
    <div class="col-md-2">

        {!! Form::date('data_vencimento', 'Vencimento')
            ->attrs([
                'class' => 'form-control'
            ])
            ->required()
        !!}

    </div>

    {{-- TIPO PAGAMENTO --}}
    <div class="col-md-3">

        <label for="inp-tipo_pagamento">
            Tipo de Pagamento
        </label>

        <select class="form-control form-select"
            name="tipo_pagamento"
            id="inp-tipo_pagamento">

            <option value="Dinheiro">Dinheiro</option>
            <option value="Cheque">Cheque</option>
            <option value="Boleto">Boleto</option>
            <option value="Cartão de Crédito">Cartão de Crédito</option>
            <option value="Cartão de Débito">Cartão de Débito</option>
            <option value="Vale Alimentação">Vale Alimentação</option>
            <option value="Vale Refeição">Vale Refeição</option>
            <option value="Vale Presente">Vale Presente</option>
            <option value="Vale Combustível">Vale Combustível</option>
            <option value="Depósito Bancário">Depósito Bancário</option>
            <option value="Pix">Pix</option>
            <option value="Outros">Outros</option>

        </select>

    </div>

    {{-- STATUS --}}
    <div class="col-md-2">

        {!! Form::select(
            'status',
            'Conta Paga',
            ['0' => 'Não', '1' => 'Sim']
        )
        ->attrs([
            'class' => 'form-select'
        ])
        !!}

    </div>

    {{-- RECORRÊNCIA --}}
    <div class="col-md-2">

        <label class="form-label">
            Recorrência
        </label>

        <select name="tem_recorrencia"
            id="tem_recorrencia"
            class="form-select">

            <option value="0">Não</option>
            <option value="1">Sim</option>

        </select>

    </div>

    {{-- OBSERVAÇÃO --}}
    <div class="col-12">

        <label for="inp-observacao" class="form-label">
            Observações
        </label>

        <textarea
            name="observacao"
            id="inp-observacao"
            rows="4"
            class="form-control"
            placeholder="Adicione informações importantes sobre esta conta, pagamento ou fornecedor...">{{ $item->observacao ?? '' }}</textarea>

        <small class="text-muted">
            Campo opcional para detalhes adicionais.
        </small>

    </div>

    {{-- PARCELAS --}}
    <div class="col-md-2 d-none" id="campo_parcelas">

        <label class="form-label">
            Qtd Parcelas
        </label>

        <input type="number"
            min="1"
            value="1"
            class="form-control"
            name="qtd_parcelas">

    </div>

    {{-- TIPO RECORRÊNCIA --}}
    <div class="col-md-3 d-none" id="campo_tipo_recorrencia">

        <label class="form-label">
            Tipo Recorrência
        </label>

        <select name="tipo_recorrencia"
            class="form-select">

            <option value="mensal">
                Mensal
            </option>

            <option value="quinzenal">
                Quinzenal
            </option>

            <option value="semanal">
                Semanal
            </option>

        </select>

    </div>

    {{-- LOCAL --}}
    @isset($item)
        {!! __view_locais_select_edit("Local", $item->filial_id) !!}
    @else
        {!! __view_locais_select() !!}
    @endif

    {{-- BOLETO --}}
    <div class="col-12 d-none" id="boleto-campos">

        <div class="card border">
            <div class="card-body">

                <h6 class="mb-3">
                    Dados do Boleto
                </h6>

                <div class="row">

                    <div class="col-md-6">

                        <label class="form-label">
                            Anexar PDF
                        </label>

                        <input type="file"
                            class="form-control"
                            name="boleto_pdf"
                            accept="application/pdf">

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">
                            Código de Barras
                        </label>

                        <input type="text"
                            class="form-control"
                            name="boleto_codigo"
                            placeholder="Digite o código">

                    </div>

                </div>

            </div>
        </div>

    </div>

    {{-- PIX --}}
    <div class="col-12 d-none" id="pix-campos">

        <div class="card border">
            <div class="card-body">

                <h6 class="mb-3">
                    Dados PIX
                </h6>

                <input type="text"
                    class="form-control"
                    name="pix_chave"
                    placeholder="Digite a chave PIX">

            </div>
        </div>

    </div>

    {{-- DEPÓSITO --}}
    <div class="col-12 d-none" id="deposito-campos">

        <div class="card border">
            <div class="card-body">

                <h6 class="mb-3">
                    Dados Bancários
                </h6>

                <textarea
                    class="form-control"
                    name="dados_bancarios"
                    rows="3"
                    placeholder="Banco, agência, conta..."></textarea>

            </div>
        </div>

    </div>

    {{-- BOTÃO --}}
    <div class="col-12">

        <button type="submit"
            class="btn btn-primary px-5 float-end">

            Salvar

        </button>

    </div>

</div>

<script>

    // PAGAMENTO
    document.getElementById('inp-tipo_pagamento').addEventListener('change', function() {

        let tipo = this.value;

        document.getElementById('boleto-campos').classList.add('d-none');
        document.getElementById('pix-campos').classList.add('d-none');
        document.getElementById('deposito-campos').classList.add('d-none');

        if (tipo === 'Boleto') {
            document.getElementById('boleto-campos').classList.remove('d-none');
        }

        if (tipo === 'Pix') {
            document.getElementById('pix-campos').classList.remove('d-none');
        }

        if (tipo === 'Depósito Bancário') {
            document.getElementById('deposito-campos').classList.remove('d-none');
        }

    });

    // RECORRÊNCIA
    document.getElementById('tem_recorrencia').addEventListener('change', function(){

        let parcelas = document.getElementById('campo_parcelas');
        let tipo = document.getElementById('campo_tipo_recorrencia');

        parcelas.classList.add('d-none');
        tipo.classList.add('d-none');

        if(this.value == '1'){
            parcelas.classList.remove('d-none');
            tipo.classList.remove('d-none');
        }

    });

</script>