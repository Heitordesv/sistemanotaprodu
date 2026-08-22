<div class="row card-body p-4">

 
    <div class="col-md-6 mt-3">
        {!! Form::text('mercadopago_public_key', 'Mercado pago public key') !!}
    </div>
    <div class="col-md-6 mt-3">
        {!! Form::text('mercadopago_access_token', 'Mercado pago access token') !!}
    </div>
   
     
    <div class="mt-2">
        <hr>
    </div>
 
    </div>
    <div class="col-12 mt-4">
        @isset($not_submit)
        <button type="button" class="btn btn-primary px-5" id="">Salvar</button>
        @else
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
        @endif
    </div>
</div>

@section('js')


<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
@endsection