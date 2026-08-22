@extends('default.layout',['title' => 'Etiqueta'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Gerar etiqueta: <strong>{{ $item->nome }}</strong></h5>
            </div>
            <input type="hidden" id="padroes" value="{{json_encode($padrosEtiqueta)}}">
            <div class="col-md-4 mt-5">
                <label for="" class="">Modelo de Etiqueta Pré-definido</label>
                <div class="input-group">
                    <select class="form-select modelo" name="" id="modelo">
                        <option value="">Selecione</option>
                        @foreach ($padrosEtiqueta as $i)
                        <option value="{{$i->id}}">{{$i->nome}}</option>
                        @endforeach
                    </select>
                    @if (!isset($not_submit))
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-etiqueta">
                        <i class="bx bx-plus"></i></button>
                    @endif
                </div>
            </div>
            {!!Form::open()
            ->post()
            ->route('produtos.montaEtiqueta')
            ->multipart()!!}
            <div class="row">
                <input type="hidden" name="produto_id" value="{{ $item->id }}">
                <div class="col-md-3 mt-5">
                    {!! Form::tel('altura', 'Altura mm*')->attrs(['' => '']) !!}
                </div>
                <div class="col-md-3 mt-5">
                    {!! Form::tel('largura', 'Largura mm*')->attrs(['' => '']) !!}
                </div>
                <div class="col-md-3 mt-5">
                    {!! Form::tel('etiquestas_por_linha', 'Num. de etiqueta por linha')->attrs(['' => '']) !!}
                </div>
                <div class="col-md-3 mt-5">
                    {!! Form::tel('distancia_etiquetas_lateral', 'Dist. entre etiquetas lateral mm*')->attrs(['' => '']) !!}
                </div>
                <div class="col-md-3 mt-3">
                    {!! Form::tel('distancia_etiquetas_topo', 'Dist. entre etiquetas topo mm*')->attrs(['' => '']) !!}
                </div>
                <div class="col-md-3 mt-3">
                    {!! Form::tel('quantidade_etiquetas', 'Qtd de etiquetas*')->attrs(['' => '']) !!}
                </div>
                <div class="col-md-3 mt-3">
                    {!! Form::tel('tamanho_fonte', 'Tamanho da fonte*')->attrs(['' => '']) !!}
                </div>
                <div class="col-md-3 mt-3">
                    {!! Form::tel('tamanho_codigo_barras', 'Tamanho do código de barras mm*')->attrs(['' => '']) !!}
                </div>
            </div>
            <p class="mt-4" style="color: royalblue">Campos marcados como "SIM" serão impressos na etiqueta</p>
            <div class="row mt-4">
                <div class="col-md-2">
                    {!! Form::select('nome_empresa', 'Nome da empresa', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select']) !!}
                </div>
                <div class="col-md-2">
                    {!! Form::select('nome_produto', 'Nome do produto', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select']) !!}
                </div>
                <div class="col-md-2">
                    {!! Form::select('valor_produto', 'Valor do produto', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select']) !!}
                </div>
                <div class="col-md-2">
                    {!! Form::select('codigo_produto', 'código do produto', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select']) !!}
                </div>
                <div class="col-md-3">
                    {!! Form::select('codigo_barras_numerico', 'Código de barras numérico', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select']) !!}
                </div>
            </div>
            <div class="col-12 mt-5">
                <button type="submit" class="btn btn-primary px-5">Salvar</button>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>

@include('modals._etiqueta')

@section('js')
<script src="/js/etiqueta.js" type="text/javascript">
</script>
@endsection
@endsection
