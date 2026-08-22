
<div class="card card-custom gutter-b example">
    <div class="col-lg-12 mt-2">
        <div class="row row-cols-auto m-3">
            <h5><strong id="timer" class="is-desktop"></strong>
                @if($usuario->caixa_livre)
                <span class="text-info">Caixa Livre</span>
                <button data-toggle="modal" data-target="#modal-funcionarios" class="btn btn-sm btn-light-info">
                    <i class="la la-user"></i>
                </button>
                @endif
            </h5>
            <div class="col is-desktop">
                <button type="button" class="btn btn-dark btn-sm" style="margin-left: -10px" data-bs-toggle="modal" data-bs-target="#modal-selecionar_vendedor"><i class="bx bx-user-check"></i> Informar
                Vendedor</button>
            </div>
            

            <div class="col is-desktop">
                <a class="btn btn-success btn-sm" style="margin-left: -10px" href="{{ route('preVenda.index') }}"><i class="bx bx-file-blank"></i> Nova Pré Venda</a>
            </div>

            <div class="col is-mobile">
                <button class="btn btn-outline-success dropdown-toggle btn-sm" data-bs-toggle="dropdown" aria-expanded="false">PDV</button>
                <ul class="dropdown-menu">
                    <li>
                        <button type="button" class="btn btn-outline-secondary dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-selecionar_vendedor"><i class="bx bx-user-check"></i> Informar Vendedor
                        </button>
                    </li>
                    <li>
                        <button type="button" class="btn btn-outline-secondary dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-lista_pre_venda"><i class="bx bx-folder-open"></i> Lista de
                        Pré-vendas</button>
                    </li>
                    <li>
                        <a href="{{ route('preVenda.index') }}" type="button" class="btn btn-outline-secondary dropdown-item" ><i class="bx bx-list-check"></i> Nova Pré-venda</a>
                    </li>
                </ul>
            </div>
            <div class="col">
                <div class="col" style="margin-left: -10px">
                    <a class="btn btn-outline-danger btn-sm" href="{{ route('vendas.index') }}">Sair</a>
                </div>
            </div>
        </div>
        <div class="row m-1">
            <div class="col-lg-8 col-12">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="focus-codigo">
                        <li class="bx bx-barcode"></li>
                        <input class="mousetrap" type="" autofocus id="codBarras" name="">
                        <span id="mousetrapTitle"><span class="texto-leitor">CLIQUE AQUI PARA ATIVAR O LEITOR</span> <i class="las la-sort-down" style="margin-top: 4px;"></i></span>
                    </span>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="inp-produto_id" class="">Produto</label>
                            <div class="input-group">
                                <select class="form-control produto_id" name="produto_id" id="inp-produto_id"></select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        {!! Form::tel('quantidade', 'Quantidade')->attrs(['class' => 'qtd']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::tel('valor_unitario', 'Valor Unitário')->attrs(['class' => 'moeda value_unit']) !!}
                    </div>
                    <div class="col-md-1 is-desktop" style="margin-left: 20px">
                        <br>
                        <button class="btn btn-primary btn-add-item" type="button">Adicionar</button>
                    </div>
                    <div class="col-md-1 is-mobile" style="margin-top: 10px">
                        <button class="btn btn-primary btn-add-item w-100" type="button">Adicionar</button>
                    </div>
                    <div class="table-responsive" style=" height: 480px">
                        <table class="table mb-0 table-striped mt-2 table-itens">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>QTD</th>
                                    <th>Valor</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (isset($item))
                                @foreach ($item->itens as $key => $product)
                                <tr>
                                    <td>
                                        <input readonly type="tel" name="produto_id[]" class="form-control" value="{{ $product->produto_id }}">
                                    </td>
                                    <td>
                                        <input readonly type="text" name="produto_nome[]" class="form-control" value="{{ $product->produto->nome }}">
                                    </td>
                                    <td>
                                        <input readonly type="tel" name="quantidade[]" class="form-control qtd-item" value="{{ __estoque($product->quantidade) }}">
                                    </td>
                                    <td>
                                        <input readonly type="tel" name="valor[]" class="form-control" value="{{ __moeda($product->valor) }}">
                                    </td>
                                    <td>
                                        <input readonly type="tel" name="subtotal_item[]" class="form-control subtotal-item" value="{{ __moeda($product->valor * $product->quantidade) }}">
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                        <hr>
                    </div>
                </div>
                <div class="card" style="background-color: rgb(243, 230, 230) ; margin-top: -18px">
                    <div class="row" style="margin-left: 25px">
                        <div class="col-md-3">
                            <p class="mt-2">Desconto: <strong id="valor_desconto">R$ 0,00 </strong> <button type="button" onclick="setaDesconto()" class="btn btn-warning btn-sm mt-1 btn-desconto"><i class="bx bx-edit"></i></button></p>
                        </div>
                        <div class="col-md-3">
                            <p class="mt-2">Acréscimo: <strong id="valor_acrescimo">R$ 0,00 </strong> <button type="button" onclick="setaAcrescimo()" class="btn btn-warning btn-sm mt-1 btn-acrescimo"><i class="bx bx-edit"></i></button></p>
                        </div>

                        <div class="col-md-6 col-12 mt-1 mb-1">
                            <label>Lista de Preços:</label>

                            <select name="" id="" class="form-select mt-2 w-75">
                                @foreach ($listas as $item)
                                <option value="">{{ $item->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-12">
                <div class="card" style="background-color: rgb(243, 230, 230)">
                    <div class="row row-cols-auto m-2">
                        <div class="col">
                            <button type="button" style="margin-left: 25px" data-bs-toggle="modal" data-bs-target="#modal-selecionar_cliente" class="btn btn-info btn-sm btn-selecionar_cliente"><i class="bx bx-user"></i>Cliente</button>
                        </div>
                        <div class="col">
                            <button type="button" style="margin-left: -10px" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modal-observacoes_pdv"><i class="bx bx-pencil"></i>Observações</button>
                        </div>
                        <!-- <div class="col-10 mt-3">
                            <button type="button" style="width: 100%; margin-left: 25px" class="btn btn-primary btn-sm modal-pag_mult" data-bs-toggle="modal" data-bs-target="#modal-pag_multi_pdv"><i class="bx bx-list-ol"></i>Pag. Multiplo</button>
                        </div> -->
                    </div>
                    <hr>
                    <div class="card m-2" style="background-color: rgb(181, 192, 167); height: 120px">
                        <h6 class="m-3">TOTAL</h6>
                        <div class="row">
                            <p class="col-2 m-3">R$:</p>
                            <h1 class="col-6 m-1">
                                @isset($item)
                                <strong class="total-venda" style="margin-left:-40px">{{ __moeda($item->valor_total) }}</strong>
                                @else
                                <strong class="total-venda" style="margin-left:-40px">0,00</strong>
                                @endif
                            </h1>
                        </div>
                    </div>
                    <hr>
                    <div class="col-md-1">
                        {!! Form::hidden('subtotal', 'SubTotal')->attrs(['class' => 'moeda']) !!}
                    </div>
                    <div class="alerts mt-2 m-2">
                    </div>
                    <div class="col-md-12 m-2 mb-2">
                        <button style=" width: 98%;" type="submit" id="enviar_caixa" disabled class="btn btn-primary px-5">
                            <i class="bx bx-paper-plane"></i> Enviar para caixa
                        </button>
                    </div>  
                </div>
            </div>
        </div>
    </div>
</div>

@include('modals.frontBox._selecionar_cliente', ['not_submit' => true])
@include('modals.frontBox._observacoes_pdv', ['not_submit' => true])
@include('modals.frontBox._selecionar_vendedor', ['not_submit' => true])
@include('modals.frontBox._pag_multi_pdv', ['not_submit' => true])
@include('modals.frontBox._lista_pre_venda', ['not_submit' => true])
