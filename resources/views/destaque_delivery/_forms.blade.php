<div class="row g-3">
    <div class="col-md-4">
        {!!Form::select('empresa_id', 'Loja (opcional)', ['' => 'Selecione'] + $lojas->pluck('nome', 'id')->all())
        ->attrs(['class' => 'form-select'])
        !!}
    </div>
    @isset($item->produto_id)
    <div class="col-md-4 d-produto d-none">
        {!!Form::select('produto_id', 'Produto (opcional)')
        ->attrs(['class' => 'select2'])
        !!}
    </div>
    @endif
    <div class="col-12 mt-4">
        <div class="row">
            <div class="col-md-2 mt-2">
                {!!Form::select('status', 'Status', [1 => 'Ativo', 0 => 'Desativado'])
                ->attrs(['class' => 'form-select'])
                !!}
            </div>
            <div class="col-md-2">
                @php
                $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Informe um número para ordenar os destaque da pagina inicial" data-bs-original-title="" title="">
                    <i class="bx bx-info-circle m-1"></i>
                </label>';
                @endphp
                {!!Form::tel('ordem', 'Ordem' . $appendAttr)->required()->attrs(['class' => 'popover-button'])->wrapperAttrs([''])
                !!}
            </div>
        </div>
    </div>
    <div class="col-md-12 mt-5">
        <label for="">Imagem</label>
        @if (!isset($not_submit))
        <div id="image-preview" class="_image-preview col-md-4">
            <label for="" id="image-label" class="_image-label">Selecione a imagem</label>
            <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
            @isset($item)

            @if ($item->img)
            <img src="/uploads/categoriaMasterDelivery/{{ $item->img }}" class="img-default">
            @else
            <img src="/imgs/no_product.png" class="img-default">
            @endif
            @else
            <img src="/imgs/no_product.png" class="img-default">
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
        <button type="submit" class="btn btn-primary px-5" id="btn-store">Salvar</button>
    </div>
</div>

@section('js')
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
<script type="text/javascript" src="/js/destaqueDelivery.js"></script>

@endsection
