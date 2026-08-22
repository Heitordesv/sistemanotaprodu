<div class="row g-3">
    <div class="col-md-4">
        <div class="form-group">
            <label for="inp-cliente_id" class="">Cliente</label>
            <div class="input-group">
                <select required class="form-control" name="cliente_id" id="inp-cliente_id">
                    @isset($item)
                    <option value="{{ $item->cliente_id }}">{{ $item->cliente->razao_social }}</option>
                    @endif
                </select>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-cliente">
                    <i class="bx bx-plus"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <label for="">Vendedor</label>
        <select required class="select2" name="funcionario_id" id="">
            @foreach ($funcionarios as $f)
            <option value="">Selecione</option>
            <option value="{{ $f->id }}">{{ $f->nome }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-2 mt-3">
        {!! Form::date('data', 'Data')->attrs(['class' => ''])->required()
        ->value(date('Y-m-d')) !!}
    </div>
    <div class="col-lg-2 mt-3">
        {!! Form::tel('inicio', 'Horário Início')->attrs(['data-mask' => '00:00:00'])
        ->required() !!}
    </div>

    <div class="card">
        <div class="m-3">
            <h6>Categorias:</h6>
            @foreach ($categorias as $c)
            <button type="button" class="btn btn-light btn_cat btn_cat_{{ $c->id }}" onclick="selectCat('{{ $c->id }}')">
                {{ $c->nome }}
            </button>
            @endforeach
        </div>

        <div class="m-3">
            <h6>Serviços:</h6>
            @foreach ($servicos as $s)
            <button type="button" onclick="selectServico('{{$s->id}}', '{{$s->valor}}', '{{$s->tempo_servico}}')" class="btn btn-light servico_{{ $s->id }} sub_cat sub_cat_{{ $s->categoria_id }}">
                {{ $s->nome }}
            </button>
            @endforeach
        </div>
    </div>
    <div class="card">
        <div class="row m-3">
            <h4 class="col-5">Total: <strong class="total text-success">0,00</strong></h4>
            <input type="hidden" name="total" id="total">
            <h4 class="col-5">Tempo de Serviço: <strong class="tempo_servico text-primary">0,00</strong></h4>
        </div>
    </div>
    <div class="row">
        <div class="col-md-2 mt-3">
            {!! Form::text('termino', 'Horário do término')->attrs(['data-mask' => '00:00:00'])->required() !!}
        </div>
        <div class="col-md-4 mt-3">
            {!! Form::text('observacao', 'Observação')->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-2 mt-3">
            {!! Form::text('desconto', 'Desconto')->attrs(['class' => 'moeda']) !!}
        </div>
        <div class="col-md-2 mt-3">
            {!! Form::text('acrescimo', 'Acréscimo')->attrs(['class' => 'moeda']) !!}
        </div>
    </div>

    <div class="input_hidden">
    </div>

    <div class="col-12 mt-4">
        <button type="submit" class="btn btn-primary px-5" id="btn-store">Salvar</button>
    </div>
</div>

@section('js')

<script src='/fullcalendar/main.js'></script>
<script src='/fullcalendar/locales/pt-br.js'></script>
<script type="text/javascript" src="/js/client.js"></script>
<script type="text/javascript" src="/js/agendamento.js"></script>

@endsection

