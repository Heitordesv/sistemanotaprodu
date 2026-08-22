<div class="modal fade" id="modal-empresa_contador" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body md-cliente">
                <div class="card-title d-flex align-items-center">
                    <h5 class="mb-0 text-primary">Selecione a empresa</h5>
                </div>
                <hr>
                <div class="pl-lg-4">
                    {!!Form::open()
                    ->post()
                    ->route('contador.set-empresa')
                    !!}
                    <select class="custom-select form-select" name="empresa">
                        <option value="">Selecione...</option>
                        @foreach(session('user_contador') as $emp)
                        <option @if(session('empresa_selecionada')) @if(session('empresa_selecionada')['empresa_id']==$emp['empresa_id']) selected @endif @endif value="{{ $emp['empresa_id'] }}">{{ $emp['nome'] }}</option>
                        @endforeach
                    </select>
                    <div class="row g-3">
                        <div class="col-12 mt-5">
                            <button type="submit" class="btn btn-primary px-5">Selecionar</button>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>
