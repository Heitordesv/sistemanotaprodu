@section('css')
<style type="text/css">
    input[type="file"] {
        display: none;
    }

    .file-certificado label{
        padding: 10px 10px;
        width: 100%;
        background-color: #1C1F23;
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

<div class="row g-3">
    <div class="col-md-3 file-certificado">
        {!! Form::file('xml', 'Procurar arquivo')->attrs(['accept' => '.xml']) !!}
    </div>
    <hr>
    <div>

    </div>
</div>

@section('js')
<script type="text/javascript">
    $('#inp-xml').change(function() {
        $('#form-import').submit();
    });
</script>
@endsection
