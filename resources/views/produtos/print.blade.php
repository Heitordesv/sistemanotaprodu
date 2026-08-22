<div id="preview_body">
    @php
    $contLinha = 0;
    @endphp
    @for($i=0; $i<$quantidade; $i++) <div style="height: {{$altura}}mm; !important; width: {{$largura}}mm !important; display: inline-block; margin-top: {{$distancia_topo}}mm !important; margin-left: {{$quantidade_por_linhas > 1 && $contLinha > 0 ? $distancia_lateral : 4 }}mm !important;" class="sticker-border text-center">
        <div style="margin-top: {{$distancia_topo}}mm;">
            @if($data['nome_empresa'])
            <b style="display: block !important; font-size: {{$tamanho_fonte}}px" class="text-uppercase">{{$data['empresa']}}</b>
            @endif

            @if($data['nome_produto'])
            <span style="display: block !important; font-size: {{$tamanho_fonte}}px">
                {{$data['nome']}}
            </span>
            @endif
            @if($data['cod_produto'])
            <span style="display: block !important; margin-top: 3px; font-size: {{$tamanho_fonte}}px">
                ID: <b>{{$data['codigo']}}</b>
            </span>
            @endif
            <img class="center-block" style="margin-top: 5px; max-width:90%; !important;height: {{$tamanho_codigo}}mm !important;" src="/barcode/{{$rand}}.png">
            @if($data['codigo_barras_numerico'])
            <span style="display: block !important; font-size: {{$tamanho_fonte}}px;">{{$codigo}}</span>
            @endif

            @if($data['valor_produto'])
            <span style="display: block !important; font-size: {{$tamanho_fonte}}px; margin-top: 4px;">
                <b>R$ {{number_format($data['valor'], 2, ',', '.')}}</b>
            </span>
            @endif
        </div>
</div>
@php

$contLinha++;
if($contLinha == $quantidade_por_linhas){
echo "<br>"; $contLinha = 0;
}

@endphp

@endfor
<div style="margin-top: 25px; margin-left: 15px">
    <button class="btn btn-success" onclick="window.print()">Imprimir</button>
</div>
</div>


<script type="text/javascript">
</script>
<style type="text/css">
    .text-center {
        text-align: center;
    }

    .text-uppercase {
        text-transform: uppercase;
    }

    /*Css related to printing of barcode*/
    .label-border-outer {
        border: 0.1px solid grey !important;
    }

    .label-border-internal {
        /*border: 0.1px dotted grey !important;*/
    }

    .sticker-border {
        border: 0.1px dotted grey !important;
        overflow: hidden;
        box-sizing: border-box;
    }

    #preview_box {
        padding-left: 30px !important;
    }

    @media print {
        .content-wrapper {
            border-left: none !important;
            /*fix border issue on invoice*/
        }

        .label-border-outer {
            border: none !important;
        }

        .label-border-internal {
            border: none !important;
        }

        .sticker-border {
            border: none !important;
        }

        #preview_box {
            padding-left: 0px !important;
        }

        #toast-container {
            display: none !important;
        }

        .tooltip {
            display: none !important;
        }

        .btn {
            display: none !important;
        }
    }

    @media print {
        #preview_body {
            display: block !important;
        }
    }

    @page {
        margin-top: 0in;
        margin-bottom: 0in;
        margin-left: 0in;
        margin-right: 0in;

    }

</style>
