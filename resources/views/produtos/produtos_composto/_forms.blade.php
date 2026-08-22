<div class="d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="row">
        <div class="col-lg-12">
            {!! Form::text('descricao', 'Descrição *') !!}
        </div>
        <div class="col-md-3 mt-2">
            {!! Form::tel('rendimento', 'Rendimento') !!}
        </div>
        <div class="col-md-3 mt-2">
            {!! Form::tel('tempo_preparo', 'Tempo de Preparo') !!}
        </div>
        <div class="col-md-3 mt-2">
            @php
                $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Informe a quantidade de pedaços. EX: pizza." data-bs-original-title="" title="">
                    <i class="bx bx-info-circle m-1"></i>
                </label>';
            @endphp
            {!! Form::text('pedacos', 'Quantidade de Pedaços' . $appendAttr) !!}
        </div>
        <div class="col-12 mt-4">

            <button type="submit" class="btn btn-primary px-5">Salvar</button>

        </div>
    </div>
</div>
