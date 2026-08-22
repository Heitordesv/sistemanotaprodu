<div class="modal fade" id="modal-selecionar_cliente" aria-modal="true" role="dialog" style="overflow:scroll;"
    tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Selecione um Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body row">
                <div class="col-md-12">
                    {!! Form::select('cliente_id', 'Cliente')
                    ->attrs(['class' => 'select2'])->options((isset($item) && $item->cliente_id) ? [$item->cliente_id => $item->cliente->razao_social] : []) !!}
                </div>
            </div>
            <div class="modal-footer">
                <button data-bs-dismiss="modal" type="button" id="btn-seleciona-cliente" class="btn btn-primary cliente_selecionado px-5">OK</button>
            </div>
        </div>
    </div>
</div>

@section('js')
    <script>
        $('.modal .select2').each(function() {
            console.log($(this))
            let id = $(this).prop('id')

            if (id == 'inp-cliente_id') {
                $(this).select2({
                    dropdownParent: $(this).parent(),
                    theme: 'bootstrap4',
                });
            }

            if (id == 'inp-cliente_id') {

                // $(this).select2({
                //     minimumInputLength: 2,
                //     language: "pt-BR",
                //     placeholder: "Digite para buscar o cliente",
                //     width: "80%",
                //     theme: "bootstrap4",

                //     ajax: {
                //         cache: true,
                //         url: path_url + "api/cliente/pesquisa",
                //         dataType: "json",
                //         data: function(params) {
                //             console.clear();
                //             var query = {
                //                 pesquisa: params.term,
                //                 empresa_id: $("#empresa_id").val(),
                //             };
                //             return query;
                //         },
                //         processResults: function(response) {
                //             console.log("response", response);
                //             var results = [];

                //             $.each(response, function(i, v) {
                //                 var o = {};
                //                 o.id = v.id;

                //                 o.text = v.razao_social + " - " + v.cpf_cnpj;
                //                 o.value = v.id;
                //                 results.push(o);
                //             });
                //             return {
                //                 results: results,
                //             };
                //         },
                //     },
                // });
            }
        });
    </script>
@endsection
