<div class="modal fade" id="modal-soma_detalhada" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Soma detalhada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row">
                @foreach($somaTiposPagamento as $key => $s)
				@if($s > 0)
				<h4 class="center-align">{{App\Models\VendaCaixa::getTipoPagamento($key)}} = <strong class="red-text">R$ {{__moeda($s)}}</strong></h4>
				@endif
				@endforeach
            </div>
        </div>
    </div>
</div>

