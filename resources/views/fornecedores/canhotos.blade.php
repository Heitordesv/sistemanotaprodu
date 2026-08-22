<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Recibos</title>

<style>
    @page {
        margin: 10mm;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
    }

    .recibo {
        border: 1px dashed #000;
        padding: 10px;
        margin-bottom: 15px;
    }

    .titulo {
        text-align: center;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .linha {
        margin-bottom: 4px;
    }

    .assinatura {
        margin-top: 20px;
        text-align: center;
    }

    .assinatura .linha-assinatura {
        border-top: 1px solid #000;
        margin-top: 15px;
        width: 80%;
        margin-left: auto;
        margin-right: auto;
    }

    .page-break {
        page-break-after: always;
    }
</style>
</head>
<body>

@for($i = 1; $i <= 60; $i++)

    <div class="recibo">

        <div class="titulo">RECIBO Nº {{ $i }}</div>

        <div class="linha">
            Recebi de: <strong>{{ $empresa->razao_social }}</strong>
        </div>

        <div class="linha">
            CPF/CNPJ: {{ $empresa->cpf_cnpj }}
        </div>

        <div class="linha">
            Referente a: ________________________________________
        </div>

        <div class="linha">
            Valor: R$ __________________________
        </div>

        <div class="linha">
            Forma: ( ) Pix &nbsp; ( ) Dinheiro &nbsp; ( ) Cartão
        </div>

        <div class="linha">
            Data: ____/____/________
        </div>

        <div class="assinatura">
            <div class="linha-assinatura"></div>
            Assinatura
        </div>

    </div>

    {{-- QUEBRA A CADA 4 RECIBOS --}}
    @if($i % 4 == 0)
        <div class="page-break"></div>
    @endif

@endfor

</body>
</html>