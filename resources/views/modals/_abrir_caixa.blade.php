<div class="modal fade" id="modal-abrir_caixa" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Abrir Caixa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {!!Form::open()
            ->post()
            ->route('caixa.store')
            ->multipart()!!}
            <div class="modal-body">
                {!! __view_locais_select_pdv() !!}
                <div class="col-md-12 mt-3">
                    {!! Form::tel('valor', 'Valor')->attrs(['class' => 'moeda']) !!}
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary px-5 w-100">Abrir</button>
            </div>
            {!!Form::close()!!}

        </div>
    </div>
</div>

@section('js')
<script src="/js/caixa.js"></script>
<script>
    // O PDV usa Bootstrap 5. A abertura automática precisa usar a API nativa,
    // pois $.fn.modal foi removido a partir do Bootstrap 5.
    window.validaCaixa = function () {
        const abertura = document.getElementById('abertura');

        if (!abertura || abertura.value) {
            return;
        }

        const modalElement = document.getElementById('modal-abrir_caixa');
        if (!modalElement || !window.bootstrap || !window.bootstrap.Modal) {
            return;
        }

        window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.validaCaixa();
    });
</script>
@endsection
