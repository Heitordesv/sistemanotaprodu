<div class="modal fade" id="modal-bairros" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bairros cadastrados pelo administrador</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    @foreach($bairrosDoSuper as $b)
                    <div class="col-sm-12 col-lg-6 col-md-6 col-xl-4">
                        <div class="card card-custom gutter-b example example-compact">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3 style="width: 230px; font-size: 18px; height: 10px;" class="card-title">
                                        {{ $b->nome   }}
                                    </h3>
                                    <h3 style="font-size: 18px; width: 150px;" class="mt-3">R$ {{ __moeda($b->valor_entrega) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('bairrosDeliveryLoja.herdar') }}" class="btn btn-primary px-5">Usar esses bairros</a>
            </div>
        </div>
    </div>
</div>
