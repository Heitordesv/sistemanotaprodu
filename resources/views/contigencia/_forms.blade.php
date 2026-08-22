<div class="row g-3">

    <div class="col-md-2">
        {!! Form::select('tipo', 'Tipo', \App\Models\Contigencia::tiposContigencia())
        ->attrs(['class' => 'form-select'])->required() !!}
    </div>

    <div class="col-md-2">
        {!! Form::select('documento', 'Documento', ['NFe' => 'NFe', 'NFCe' => 'NFCe'])
        ->attrs(['class' => 'form-select'])->required() !!}
    </div>

    <div class="col-md-5">
        {!! Form::tel('motivo', 'Motivo')->attrs(['class' => ''])->required() !!}
    </div>

    <div class="col-12 mt-4">
        <button class="btn btn-info px-5" type="submit">Salvar</button>
    </div>
</div>

@section('js')
<script type="text/javascript">
    $(document).on("change", "#inp-tipo", function() {
        let tipo = $(this).val()

        $("#inp-documento option").removeAttr('disabled');
        if(tipo == 'OFFLINE'){
            $("#inp-documento option[value='NFe']").attr('disabled', 1);
            $("#inp-documento").val('NFCe').change()
        }else{
            $("#inp-documento option[value='NFCe']").attr('disabled', 1);
            $("#inp-documento").val('NFe').change()
        }
    })

</script>
@endsection