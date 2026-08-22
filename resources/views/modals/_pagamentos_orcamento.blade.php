<div class="modal fade" id="modal-pagamentos_orcamento" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pagamentos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!!Form::open()
            ->post()
            ->autocomplete('off')
            ->route('orcamentoVenda.gerarPagamentos')
            !!}
            <input type="hidden" value="{{$data->id}}" name="orcamento_id">
            <div class="modal-body row">
                <div class="col-md-6">
                    {!! Form::tel('intervalo', 'Intervalo')->value('30')
                    ->attrs(['class' => 'form-control']) !!}
                </div>
                <div class="col-md-6">
                    <label class="" id="">Quantidade de parcelas</label>
                    <select class="custom-select form-select" name="qtd_parcelas">
                        @foreach($simulacao as $p)
                        <option value="{{$p['parcelas']}}">{{$p['parcelas']}} x R$ {{$p['valor']}}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <hr>
                    @isset($not_submit)
                    <button type="button" class="btn btn-primary px-5" id="">Salvar</button>
                    @else
                    <button type="submit" class="btn btn-primary px-5">Gerar</button>
                    @endif
                </div>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>
