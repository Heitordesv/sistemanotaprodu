<div class="modal fade" id="modal-transportadora" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Transportadora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body md-transportadora">
                @include('transportadoras._forms')
            </div>

        </div>
    </div>
</div>


@section('js')
<script>
    $('.modal .select2').each(function() {
        console.log($(this))
        let id = $(this).prop('id')

        if (id == 'inp-cidade_id') {
                $(this).select2({
                    dropdownParent: $(this).parent(),
                    theme: 'bootstrap4',
                });
            }

        if (id == 'inp-cidade_id') {
            $(this).select2({
                minimumInputLength: 2
                , language: "pt-BR"
                , placeholder: "Digite para buscar a cidade"
                , width: "100%"
                , theme: 'bootstrap4'
                , dropdownParent: $(this).parent()
                , ajax: {
                    cache: true
                    , url: path_url + 'api/buscaCidades'
                    , dataType: "json"
                    , data: function(params) {
                        console.clear()
                        var query = {
                            pesquisa: params.term
                        , };
                        return query;
                    }
                    , processResults: function(response) {
                        console.log("response", response)
                        var results = [];
                        $.each(response, function(i, v) {
                            var o = {};
                            o.id = v.id;
                            o.text = v.nome + "(" + v.uf + ")";
                            o.value = v.id;
                            results.push(o);
                        });
                        return {
                            results: results
                        };
                    }
                }
            });
        }
    })

</script>
@endsection
