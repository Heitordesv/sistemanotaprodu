<div class="modal fade" id="modal-finalizar_venda" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Finalizar Venda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row">
                @if ($usuario->somente_fiscal == 0)
                <div class="col-lg-6 col-12">
                    <button @if($config->arquivo == null) disabled @endif class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#modal-cpf_nota" style="height: 50px; width: 100%">
                    <i class="bx bx-file"> </i> CUPOM FISCAL
                    @if ($config->arquivo == null)
                    <br>
                    <h6 class="text-danger">Sem certificado</h6>
                    @endif

                    @if ($atalhos != null && $atalhos->finalizar_fiscal != '')
                    <br>
                    <h6 class="text-white">{{ $atalhos->finalizar_fiscal }}</h6>@endif</button>
                </div>
                @endif
                <div class="col-lg-6 col-12">
                    <button type="submit" class="btn btn-info" id="btn_nao_fiscal"
                    style="height: 50px; width: 100%">
                    <i class="bx bx-file-blank"> </i> CUPOM NÃOFISCAL
                    @if ($atalhos != null && $atalhos->finalizar_nao_fiscal != '')
                    <br>
                    <b class="text-white">{{ $atalhos->finalizar_nao_fiscal }}</b>@endif</button>
                </div>
            </div>
        </div>
    </div>
</div>

@include('modals.frontBox._cpf_nota', ['not_submit' => true])

