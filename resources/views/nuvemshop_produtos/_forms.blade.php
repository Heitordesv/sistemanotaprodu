<div class="row g-3">
    @if(!isset($item))
    <div class="col-md-3">

        <div class="form-group">
            <div class="input-group">
                <label for="inp-produto_id" class="">Referência</label>

                <select required class="form-control select2 produto_id" name="referencia" id="inp-produto_id">
                </select>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-produto">
                    <i class="bx bx-plus"></i>
                </button>
            </div>
        </div>
    </div>
    @else
    <input type="hidden" name="produto_id" value="{{$item->id}}">
    @endif

    <div class="col-md-3">
        {!!Form::text('nome', 'Nome do produto')
        ->required()
        ->value(isset($item) ? $produto->name->pt : '')
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('valor', 'Valor')
        ->attrs(['class' => 'moeda'])
        ->required()
        ->value(isset($item) ? __moeda($produto->variants[0]->price) : '')
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('valor_promocional', 'Valor promocional')
        ->attrs(['class' => 'moeda'])
        ->value(isset($item) ? __moeda($produto->variants[0]->promotional_price) : '')
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('estoque', 'Estoque')
        ->attrs(['class' => ''])
        ->value(isset($item) ? $produto->variants[0]->stock : '')
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('codigo_barras', 'Código de barras')
        ->attrs(['class' => ''])
        ->value(isset($item) ? $produto->variants[0]->barcode : '')
        !!}
    </div>

    <div class="col-md-3">
        <label>Categoria</label>
        <select name="categoria_id" class="form-select">
            <option value="">--</option>
            @foreach($categoriasNuvemShop as $c)
            <option @isset($produto) @if($c->id == (isset($produto->categories[0]) ? $produto->categories[0]->id : '')) selected @endif @endif value="{{$c->id}}">{{$c->name->pt}}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        {!!Form::text('largura', 'Largura (cm)')
        ->attrs(['class' => ''])
        ->value(isset($item) ? $produto->variants[0]->width : '')
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('altura', 'Altura (cm)')
        ->attrs(['class' => ''])
        ->value(isset($item) ? $produto->variants[0]->height : '')
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('comprimento', 'Comprimento (cm)')
        ->attrs(['class' => ''])
        ->value(isset($item) ? $produto->variants[0]->depth : '')
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('peso', 'Peso (g)')
        ->attrs(['class' => ''])
        ->value(isset($item) ? $produto->variants[0]->weight : '')
        !!}
    </div>

    <div class="col-md-8">
        {!!Form::textarea('descricao', 'Descrição')
        ->attrs(['class' => ''])
        ->value(isset($item) ? $produto->description->pt : '')
        ->required()
        !!}
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
        
    </div>
</div>

@section('js')
<script type="text/javascript" src="/js/product.js"></script>

<script type="text/javascript">
    $(function(){

        $('.modal .select2').each(function() {
            let id = $(this).prop('id')

            if(id == 'inp-categoria_id'){
                $(this).select2({
                    dropdownParent: $(this).parent(),
                    theme: 'bootstrap4',
                });
            }


            /*  select de marcas não estava funcionando, então coloquei mais essa condição para
            teste */

            else if(id == 'inp-marca_id'){
                $(this).select2({
                    dropdownParent: $(this).parent(),
                    theme: 'bootstrap4',
                });
            }

            else if(id == 'inp-sub_categoria_id'){

                $(this).select2({
                    minimumInputLength: 2,
                    language: "pt-BR",
                    placeholder: "Digite para buscar a subcategoria",
                    width: "100%",
                    dropdownParent: $(this).parent(),
                    theme: "bootstrap4",
                    ajax: {
                        cache: true,
                        url: path_url + "api/categorias/buscarSubCategoria",
                        dataType: "json",
                        data: function (params) {
                            console.clear();

                            let empresa_id = $("#empresa_id").val();
                            let categoria_id = $("#inp-categoria_id").val();

                            if (categoria_id) {
                                var query = {
                                    pesquisa: params.term,
                                    empresa_id: empresa_id,
                                    categoria_id: categoria_id,
                                };
                                return query;
                            } else {
                                swal("Erro", "Selecione uma categoria!", "warning");
                            }
                        },
                        processResults: function (response) {
                            var results = [];

                            $.each(response, function (i, v) {
                                var o = {};
                                o.id = v.id;

                                o.text = v.nome;
                                o.value = v.id;
                                results.push(o);
                            });
                            return {
                                results: results,
                            };
                        },
                    },

                });
            }else{
                $(this).select2({
                    dropdownParent: $(this).parent(),
                    theme: 'bootstrap4',
                });
            }
        })

        setTimeout(() => {
            $("#inp-produto_id").change(() => {
                let product_id = $("#inp-produto_id").val()
                if(product_id){
                    $.get(path_url + "api/produtos/find/"+product_id)
                    .done((e) => {

                        $('#inp-quantidade').val('1,00')
                        $('#inp-valor_unitario').val(convertFloatToMoeda(e.valor_venda))
                        $('#inp-subtotal').val(convertFloatToMoeda(e.valor_venda))
                    })
                    .fail((e) => {
                        console.log(e)
                    })
                }

            })
        }, 100)

        $('body').on('blur', '.value_unit', function() {
            let qtd = $('#inp-quantidade').val();
            let value_unit = $(this).val();
            value_unit = convertMoedaToFloat(value_unit)
            qtd = convertMoedaToFloat(qtd)
            $('#inp-subtotal').val(convertFloatToMoeda(qtd * value_unit))
        })


    })

    $('#inp-produto_id').change(() => {
        let id = $('#inp-produto_id').val()

        $.get(path_url + "api/produtos/find/"+id)
        .done((e) => {
            console.log(e);
            $('#inp-nome').val(e.nome)
            $('#inp-valor').val(convertFloatToMoeda(e.valor_venda))
            $('#inp-codigo_barras').val(e.codBarras)


            
        })
        .fail((err) => {
            console.log(err);
        });
    })

    $('#btn-store-produto').click(() => {

        let valid = validaCamposModal("#modal-produto")
        if(valid.length > 0){

            let msg = ""
            valid.map((x) => {
                console.log(x)
                msg += x + "\n"
            })
            swal("Ops, erro no formulário", msg, "error")
        }else{
            console.clear()

            let data = {}
            $(".modal input, .modal select").each(function() {

                let indice = $(this).attr('id')
                indice = indice.substring(4, indice.length)
                data[indice] = $(this).val()
            });
            data['empresa_id'] = $('#empresa_id').val()

            $.post(path_url + 'api/produtos/store', data)
            .done((success) => {
                swal("Sucesso", "Produto cadastrado!", "success")
                .then(() => {
                    var newOption = new Option(success.nome, success.id, false, false);
                    $('#inp-produto_id').append(newOption).trigger('change');
                    $('#modal-produto').modal('hide')
                })

            }).fail((err) => {
                swal("Ops", "Algo deu errado ao salvar produto!", "error")
            })
        }
    })
</script>
@endsection