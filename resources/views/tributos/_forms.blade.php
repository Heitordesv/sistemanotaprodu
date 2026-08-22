<div class="row g-3">
    <div class="col-md-2">
        {!!Form::tel('icms', '%ICMS')->required()
        ->attrs(['class' => 'form-control perc'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('pis', '%PIS')->required()
        ->attrs(['class' => 'form-control perc'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('cofins', '%COFINS')->required()
        ->attrs(['class' => 'form-control perc'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('ipi', '%IPI')->required()
        ->attrs(['class' => 'form-control perc'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::tel('ncm_padrao', 'NCM padrão')->required()
        ->attrs(['class' => 'form-control ncm'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::select('regime', 'Regime', $regimes)->required()
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-3 perc_cred" @if($item != null && $item->regime != 0) style="display: block" @else style="display: none" @endif>
        {!!Form::text('perc_ap_cred', '% Aproveitamento crédito')
        ->attrs(['class' => 'form-control perc'])
        !!}
    </div>

    {{-- <div class="col-md-7">
        {!!Form::text('link_nfse', 'Link NFSe')
        ->attrs(['class' => 'form-control'])
        !!}
    </div> --}}

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>


