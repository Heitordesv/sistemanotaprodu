<div class="row g-3">
    {{-- <input type="hidden" name="produto_id" value="{{{ isset($produto->id) ? $produto->id : 0 }}}"> --}}
    <div class="col-md-6">
        <div class="form-group">
            <label for="inp-produto_id" class="required">Produto</label>
            <div class="input-group">
                <select class="form-control select2" name="produto_id" id="inp-produto_id">
                    @isset($item)
                    <option value="{{$item->produto->id}}">{{$item->produto->nome}}</option>
                    @endif
                </select>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-produtoRapido">
                    <i class="bx bx-plus"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        {!! Form::tel('valor', 'Valor')->attrs(['class' => 'moeda'])->required()->value(isset($item) ? __moeda($item->valor) : '') !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('percentual_desconto_view', '% Desconto')->attrs(['class' => 'moeda']) !!}
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="inp-categoria_ecommerce_id" class="required">Categoria Ecommerce</label>
            <div class="input-group">
                <select class="form-control select2" name="categoriaEcommerce_id" id="inp-categoriaEcommerce_id">
                    <option value="">Selecione a categoria</option>
                    @foreach ($categoriasEcommerce as $c)
                    <option @isset($item) @if ($item->categoria_id == $c->id) selected @endif @endif
                        value="{{ $c->id }}">
                        {{ $c->nome }}
                    </option>
                    @endforeach
                </select>
                @if(!isset($not_submit))
                {{-- <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-categoriaEcommerce">
                    <i class="bx bx-plus"></i></button> --}}
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="inp-sub_categoria_id" class="">Sub Categoria Ecommerce</label>
            <div class="input-group">
                <select class="form-control select2" name="sub_categoriaEcommerce_id" id="inp-sub_categoriaEcommerce_id">
                    <option value="">Selecione</option>
                    @isset($item)
                    <option selected value="{{ $item->sub_categoriaEcommerce_id }}">
                        {{ $item->subCategoriaEcommerce }}
                    </option>
                    @endif
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        {!! Form::select('controlar_estoque', 'Controlar Estoque', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-3">
        {!! Form::select('status', 'Status', [1 => 'Ativo', 0 => 'Desativado'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-3">
        {!! Form::select('destaque', 'Destaque', [ 0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-12">
        {!! Form::textarea('descricao', 'Descrição')->required() !!}
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
            <img src="/uploads/produtoEcommerce/{{$g->path}}" class="img-default">
            @endforeach
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
        </div>
        @endif
        @if($errors->has('image'))
        <div class="text-danger mt-2">
            {{ $errors->first('image') }}
        </div>
        @endif
    </div>
    <div class="col-12 mt-5">
        @isset($not_submit)
        <button type="button" class="btn btn-primary px-5" id="btn-store-produtoecommerce">Salvar</button>
        @else
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
        @endif
    </div>
</div>

{{-- @include('modals._grade') --}}

@section('js')
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
{{-- <script type="text/javascript" src="/js/product.js"></script> --}}
<script type="text/javascript" src="/js/productEcommerce.js"></script>
{{-- <script type="text/javascript" src="/js/grade.js"></script> --}}
@endsection
