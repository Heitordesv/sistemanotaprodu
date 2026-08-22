<div class="row">
    <div class="col-6 col-md-4">
        <p class="text-info">Selecione o funcionário para buscar os eventos de pagamento!</p>
        <label class="col-form-label required" id="">Funcionário</label>
        <div class="input-group">
            @isset($item)
            <h4>{{$item->nome}}</h4>
            @else
            <select required class="form-select" id="kt_select2_3" name="funcionario">
                <option value="">Selecione o funcionário</option>
                @foreach($funcionarios as $f)
                <option value="{{$f->id}}">{{$f->nome}} ({{$f->cpf}})</option>
                @endforeach
            </select>
            @endif
        </div>
    </div>
    <div class="col-md-2 mt-5">
        <br>
        <select required class="form-select" name="mes">
            @foreach(\App\Models\ApuracaoMensal::mesesApuracao() as $key => $m)
            <option value="{{$m}}" @if($key==$mesAtual) selected @endif>{{ strtoupper($m) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2 mt-5">
        <br>
        <select required class="form-select" name="ano">
            @foreach(\App\Models\ApuracaoMensal::anosApuracao() as $key => $a)
            <option value="{{$a}}">{{ strtoupper($a) }}</option>
            @endforeach
        </select>
    </div>

    <div class="row">
        <div class="col-12 func-select d-none mt-4">
            <div id="kt_datatable" class="table-responsive row">
                <table class="table mb-0 table-striped table-dynamic" style="max-width: 100%; overflow: scroll;">
                    <thead class="">
                        <tr class="datatable-row" style="left: 0px;">
                            <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 70px;"></span></th>
                            <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 200px;">Evento</span></th>
                            <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Condição</span></th>
                            <th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Valor</span></th>
                            <th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Método</span></th>
                        </tr>
                    </thead>
                    <tbody id="body" class="datatable-body">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row func-select d-none mt-4 g-2">
        <div class="form-group validated col-sm-12 col-lg-3">
            <label class="col-form-label required" id="">Tipo de Pagamento</label>
            <select required class="custom-select form-select" id="forma" name="tipo_pagamento">
                <option value="">Selecione o tipo de pagamento</option>
                @foreach(App\Models\ApuracaoMensal::tiposPagamento() as $c)
                <option value="{{$c}}">{{$c}}</option>
                @endforeach
            </select>
        </div>
<div class="form-group validated col-sm-12 col-lg-3">
    <label class="col-form-label required" id="">Categoria de Conta</label>
    <select required class="custom-select form-select" name="categoria_id">
        <option value="">Selecione a categoria</option>
        @foreach($CategoriaConta as $c)
        <option value="{{ $c->id }}">{{ $c->nome }}</option>
        @endforeach
    </select>
</div>

        <div class="form-group validated col-sm-12 col-lg-2">
            <label class="col-form-label required" id="">Valor total</label>
            <input required type="tel" class="form-control total moeda" name="valor_total">
        </div>

        <div class="form-group validated col-sm-12 col-lg-6">
            <label class="col-form-label" id="">Observação</label>
            <input type="text" class="form-control" name="observacao">
        </div>

        <div class="col-md-2">
            {!!Form::select('conta_pagar', 'Adicionar conta a pagar', ['0' => 'Não', '1' => 'Sim'])
            ->attrs(['class' => 'form-select'])
            !!}
        </div>

        <div class="col-md-2">
            {!!Form::date('vencimento', 'Vencimento')
            ->attrs(['class' => 'conta-pagar'])
            !!}
        </div>

        <div class="col-md-2">
            {!!Form::select('conta_paga', 'Conta paga', ['0' => 'Não', '1' => 'Sim'])
            ->attrs(['class' => 'form-select'])
            !!}
        </div>
    </div>
    <hr class="mt-4">
    <div class="col mt-3">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>


@section('js')
<script type="text/javascript">
    $(function() {
        $('#kt_select2_3').val('').change()
        $('#inp-conta_pagar').val('0').change()
    })
    $('#kt_select2_3').change(() => {
        $('.datatable-body').html('')
        $('.func-select').addClass('d-none')
        let funcionario = $('#kt_select2_3').val()
        if (funcionario) {
            $.get(path_url + 'apuracaoMensal/getEventos/' + funcionario)
            .done((html) => {
                console.clear();
                console.log(html)
                if (html == "") {
                    swal("Erro", "Funcionário sem eventos de pagamento cadastrados!", "error")
                } else {
                    $('.func-select').removeClass('d-none')
                    $('.datatable-body').html(html)
                    calcTotal()
                }

            }).fail((err) => {
                console.log(err)
            })
        }
    })

    function calcTotal() {
        console.clear()
        let total = 0
        $('.dynamic-form').each(function() {
            console.log($(this))
            var value = $(this).find('input').val();
            var condicao = $(this).find('.condicao_chave').val();
            console.log("condicao", condicao)
            if (value) {
                value = value.replace(",", ".")
                value = parseFloat(value)
                if (condicao == "soma") {
                    total += value
                } else {
                    total -= value
                }

            }
        })
        setTimeout(() => {

            $('.total').val(total.toFixed(2).replace(".", ","))
            $('.value').addClass('moeda')
        }, 100)

    }

    $('#inp-conta_pagar').change(() => {
        let isContaPagar = $('#inp-conta_pagar').val()
        if(isContaPagar){
            $('#inp-vencimento').attr('required', 1)
        }else{
            $('#inp-vencimento').removeAttr('required')
        }
    })

    $(document).on("blur", ".moeda", function () {
        calcTotal()
    })

</script>
@endsection
