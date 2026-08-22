<div class="modal fade" id="modal-importar_nfe" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importar NFe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="row m-3">
                <div class="col-md-3">
                    {!! Form::date('start_date', 'Data Inicial')->attrs(['class' => 'ignore']) !!}
                </div>
                <div class="col-md-3">
                    {!! Form::date('end_date', 'Data Final')->attrs(['class' => 'ignore']) !!}
                </div>
                <div class="col-md-3">
                    <br>
                    <button class="btn btn-info btn-filtro px-3"><i class="bx bx-search" style="margin-top: -16px"></i> Filtrar</button>
                </div>
            </div>
            <div class="table-responsive m-3">
                <table class="table mb-0 table-striped tbl-vendas">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Data</th>
                            <th>Razão Social</th>
                            <th>Valor Total</th>
                            <th>Chave</th>
                            <th>Nº NFe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                        </tr>   
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button id="btn-importar" type="button" class="btn btn-primary px-5">Importar</button>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
    function importarNfe() {
        let ids = []
        $('#nfe-list tr').each(function() {
            if ($(this).find('input').is(':checked')) {
                let id = $(this).find('input').val()
                console.log(id)
                ids.push(id)
            }
        })
        if (ids.length > 0) {
            location.href = path + 'mdfe/createWithNfe/' + ids
        } else {
            swal("Alerta", "Selecione ao menos um documento!", "warning")
        }
    }


</script>
@endsection
