<div class="row g-3">
    <p style="color: red">* O produto de delivery depende do produto principal, isso é necessário para baixa de estoque</p>
    <div class="col-md-6 col-12">
        <div class="form-group">
            <label for="inp-produto_id" class="">Produto *</label>
            <div class="input-group">
                <select class="form-control select2" name="produto_id" id="inp-produto_id">
                    @isset($item)
                    <option value="{{$item->produto->id}}">{{$item->produto->nome}}</option>
                    @endisset
                </select>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-produtoRapido">
                    <i class="bx bx-plus"></i>
                </button>
            </div>
            @if($errors->has('produto_id'))
            <div class="text-danger mt-2">
                {{ $errors->first('produto_id') }}
            </div>
            @endif
        </div>
    </div>
    <div class="col-md-3 co-6">
        {!! Form::select('categoriaDelivery_id', 'Categoria delivery', ['' => 'Selecione...'] + $categoriasDelivery->pluck('nome', 'id')->all())
        ->attrs(['class' => 'form-select'])
        ->value(isset($item) ? $item->categoria_id : '') !!}
    </div>
    <div class="">
        <div class="row d-pizza">
        </div>
    </div>
    <div class="col-md-2 d-normal">
        {!! Form::tel('valor', 'Valor de venda')->attrs(['class' => 'moeda'])->value(isset($item) ? __moeda($item->valor) : '') !!}
    </div>
    <div class="col-md-2 d-normal">
        {!! Form::tel('valor_anterior', 'Valor anterior')->attrs(['class' => 'moeda']) !!}
    </div>
    <div class="col-md-2 d-normal">
        {!! Form::text('referencia', 'Referência')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('limite_diario', 'Limite diário')->attrs(['class' => '']) !!}
        <p style="color: royalblue">-1 = sem limite</p>
    </div>
    <div class="col-md-2">
        {!! Form::select('status', 'Status', [1 => 'Ativo', 0 => 'Desativado'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-2">
        {!! Form::select('destaque', 'Destaque', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-3">
        {!! Form::select('tem_adicionais', 'Liberar adicionais', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-9">
        {!! Form::text('descricao_curta', 'Descrição curta')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-12">
        {!! Form::textarea('descricao', 'Descrição') !!}
    </div>
    <div class="col-12 mt-4">
        <h6>Imagem</h6>
        @if (!isset($not_submit))
        <div id="image-preview" class="col-md-4">
            <label for="image-upload" id="image-label">Selecione a imagem</label>
            <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
            @isset($item)
            @if(sizeof($item->galeria) > 0)
            @foreach($item->galeria as $v => $g)
            <img src="/uploads/produtoDelivery/{{$g->path}}" class="img-default">
            @endforeach
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
        </div>
        @endif
    </div>
    <div class="col-12 mt-5">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>

@section('js')
<script type="text/javascript">
    $('#btn-store-produtored').click(() => {
        let valid = validaCamposModal("#modal-produtoRapido")
        if (valid.length > 0) {
            let msg = ""
            valid.map((x) => {
                msg += x + "\n"
            })
            swal("Ops, erro no formulário", msg, "error")
        } else {
            console.clear()
            console.log("salvando...")

            let data = {}
            $(".modal input, .modal select").each(function() {
                let indice = $(this).attr('id')
                console.log("indice", indice)
                indice = indice.substring(4, indice.length)
                data[indice] = $(this).val()
            });
            data['empresa_id'] = $('#empresa_id').val()
            console.log(data)
            $.post(path_url + 'api/produtos/storeProdutoRapido', data)
                .done((success) => {
                    console.log("success", success)
                    swal("Sucesso", "Produto cadastrado!", "success")
                        .then(() => {
                            var newOption = new Option(success.nome, success.id, false, false);
                            $('#inp-produto_id').append(newOption).trigger('change');
                            $('#modal-produtoRapido').modal('hide')
                        })
                }).fail((err) => {
                    console.log(err)
                    swal("Ops", "Algo deu errado ao salvar produto!", "error")
                })
        }
    });

    $('#inp-percentual_lucro').keyup(() => {
        let valorCompra = parseFloat($('#inp-valor_compra').val().replace(',', '.'));
        let percentualLucro = parseFloat($('#inp-percentual_lucro').val().replace(',', '.'));
        if (valorCompra > 0 && percentualLucro > 0) {
            let valorVenda = valorCompra + (valorCompra * (percentualLucro / 100));
            valorVenda = formatReal(valorVenda);
            valorVenda = valorVenda.replace('.', '')
            valorVenda = valorVenda.substring(3, valorVenda.length)
            $('#inp-valor_venda').val(valorVenda)
        } else {
            $('#inp-valor_venda').val('0')
        }
    })

    $('#inp-valor_venda').keyup(() => {
        let valorCompra = parseFloat($('#inp-valor_compra').val().replace(',', '.'));
        let valorVenda = parseFloat($('#inp-valor_venda').val().replace(',', '.'));
        if (valorCompra > 0 && valorVenda > 0) {
            let dif = (valorVenda - valorCompra) / valorCompra * 100;
            // valorVenda = formatReal(valorVenda);
            // valorVenda = valorVenda.replace('.', '')
            // valorVenda = valorVenda.substring(3, valorVenda.length)
            $('#inp-percentual_lucro').val(dif)
        } else {
            $('#inp-percentual_lucro').val('0')
        }
    })

    function formatReal(v) {
        return v.toLocaleString('pt-br', {
            style: 'currency'
            , currency: 'BRL'
            , minimumFractionDigits: casas_decimais
        });
    }

    $('#inp-categoriaDelivery_id').change(() => {
        let id = $('#inp-categoriaDelivery_id').val();
        getTipo(id)
    });

    function getTipo(id) {
        let empresa_id = $('#empresa_id').val()
        $.get(path_url + "api/produtosDelivery/filtroCategoria", {
                categoria_id: id
                , empresa_id: empresa_id
            })
            .done((res) => {
                console.log(res)
                if (res != '') {
                    $(".d-normal").addClass('d-none');
                    $(".d-pizza").removeClass('d-none');
                    $(".d-pizza").html(res);
                } else {
                    $(".d-pizza").addClass('d-none');
                    $(".d-normal").removeClass('d-none');
                }
            })
            .fail((err) => {
                console.error(err)
            })

    }

</script>
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>

@endsection

