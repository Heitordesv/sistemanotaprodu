@section('css')
<style type="text/css">
    input[type="file"] {
        display: none;
    }

    .file-padrao label {
        padding: 10px 10px;
        width: 100%;
        background-color: #8833FF;
        color: #FFF;
        text-transform: uppercase;
        text-align: center;
        display: block;
        margin-top: 15px;
        cursor: pointer;
        border-radius: 5px;
    }
</style>
@endsection

<div class="page-content">
    <div class="row">
        <div class="col-md-4 file-padrao">
            {!! Form::file('file', 'Selecione o Arquivo') !!}
        </div>
        @isset($item)
        <input type="hidden" name="uf" value="{{ $item->uf }}">
        @else
        <div class="col-md-2">
            {!! Form::select('uf', 'UF', ['' => 'Selecione'] + $estados)->attrs(['class' => 'select2'])->required() !!}
        </div>
        @endif
        <div class="col-md-3">
            {!! Form::text('versao', 'Versão')->required() !!}
        </div>
    </div>
    <hr>
    <div class="col-md-12">
        <button class="btn btn-success px-5 btn-submit" type="submit">Importar CSV</button>
    </div>
</div>

@section('js')
<script type="text/javascript">
    $('.btn-submit').click(() => {
        if($("form").isValid){
            $body = $("body");
            $body.addClass("loading");
        }
    })
</script>
@endsection
