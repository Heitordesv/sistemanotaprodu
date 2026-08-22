<style>
    @page {
        size: 101.6mm 25.4mm;
        margin: 0;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: Arial;
    }

    .etiqueta {
        width: 101.6mm;
        height: 25.4mm;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .preco {
        text-align: center;
        font-weight: bold;
    }
</style>

<div class="etiqueta">

    <img src="{{ $logoBase64 }}" style="max-height: 22px;">

    <div class="preco" style="background: {{ $corFonte }}; color: {{ $corFundo }}; padding: 1mm;">
        <div style="font-size:10px;">PREÇO EXCLUSIVO</div>
        <div style="font-size:14px;">
            R$ {{ number_format($produto->valor_venda, 2, ',', '.') }}
        </div>
    </div>

    <img src="data:image/png;base64,{{ $barcodeBase64 }}" style="height:16px; margin-top:1mm;">

    <div style="font-size:7px;">
        {{ $produto->codBarras ?? $produto->id }}
    </div>

    <div style="font-size:7px; white-space:nowrap; overflow:hidden;">
        {{ Str::limit($produto->nome, 25) }}
    </div>

</div>