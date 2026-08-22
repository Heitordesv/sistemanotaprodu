<div class="content">
    <div class="page-content">
        <div class="col-md-4 mt-3">
            <label for="">Logo 60 x 60</label>
            <div id="image-preview" class="_image-preview col-md-4">
                <label for="" id="image-label" class="_image-label">Selecione a imagem</label>
                <input type="file" name="logo1" id="image-upload" class="_image-upload" accept="image/*" />
              
                <img src="/logos/logonfenotas.png" class="img-default">
             
            </div>

            @if($errors->has('image'))
            <div class="text-danger mt-2">
                {{ $errors->first('image') }}
            </div>
            @endif
        </div>
        <div class="col-12 mt-4">
            <button type="submit" class="btn btn-primary px-5">Salvar</button>
        </div>
    </div>
</div>


@section('js')
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
@endsection