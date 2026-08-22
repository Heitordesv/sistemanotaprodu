<div class="modal fade" id="modal-local" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="post" action="{{ route('configDelivery.save-coords') }}" id="form-coords">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Deslize o pino até sua localização!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div> 

                <div class="modal-body">
                    <input type="hidden" name="lat" id="lat">
                    <input type="hidden" name="lng" id="lng">
                    <div id="map"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary px-5">Pronto</button>
                </div>
            </form>
        </div>
    </div>
</div>