<div class="row g-3">
    <div class="col-md-3">
        {!! Form::text('nome', 'Nome')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-5">
        {!! Form::text('sobre_nome', 'Sobre Nome')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-4">
        {!! Form::tel('cpf', 'CPF/CNPJ')->attrs(['class' => 'cpf_cnpj']) !!}
    </div>
    <div class="col-md-3">
        {!! Form::tel('celular', 'Celular')->attrs(['class' => 'fone']) !!}
    </div>  
    <div class="col-md-6">
        {!! Form::text('email', 'E-mail')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3">
        {!! Form::text('senha', 'Senha')->type('password')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-4 mt-3">
        <label for="">Imagem</label>
        @if (!isset($not_submit))
        <div id="image-preview" class="_image-preview col-md-4">
            <label for="" id="image-label" class="_image-label">Selecione a imagem</label>
            <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
            @isset($item)
            @if ($item->foto)
            <img src="/uploads/clientesDelivery/{{ $item->foto }}" class="img-default">
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
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>


@section('js')
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
@endsection

