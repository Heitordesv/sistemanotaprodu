<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page { size: A4; margin: 0; }
body {
    font-family: 'Helvetica', Arial, sans-serif;
    margin: 0; padding: 0; color: #333;
}

/* Container do boleto */
.boleto-wrap {
    width: 100%;
    height: 65mm; 
    margin-bottom: 3mm; 
    padding: 8mm 10mm; 
    box-sizing: border-box;
    border-bottom: 1px dashed #999;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Remove a borda da última parcela da página */
.boleto-wrap:nth-child(4n) {
    border-bottom: none;
}

table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

td {
    border: 1px solid #e0e0e0;
    padding: 4px 6px; 
    vertical-align: top;
}

/* Cabeçalho com logo, telefone e nome */
.header-table {
    margin-bottom: 2mm;
}
.header-table td { 
    border: none !important; 
    padding: 0; 
    vertical-align: middle;
}
.header-logo img {
    max-width: 70px; /* logo menor */
    height: auto;
}
.header-info {
    padding-left: 5px;
}
.empresa-nome {
    font-size: 10pt; 
    font-weight: bold; 
    color: #000; 
}
.empresa-telefone {
    font-size: 8pt;
    color: #555;
    margin-top: 2px;
}

/* Parcela */
.parcela-info { 
    text-align: right; 
    font-size: 9pt; 
    color: #444; 
    font-weight: bold; 
}

/* Campos */
.label { font-size: 6pt; font-weight: bold; color: #777; text-transform: uppercase; display: block; margin-bottom: 2px; }
.valor-venc { font-size: 11pt; font-weight: bold; color: #000; }
.dado-comum { font-size: 8pt; color: #333; }

/* QR Code */
.qr-cell {
    width: 90px; 
    text-align: center;
    vertical-align: middle !important;
    background-color: #fcfcfc;
}

.instrucoes-texto {
    font-size: 7pt;
    line-height: 1.2;
    color: #555;
}

.page-break { page-break-after: always; }
</style>
</head>
<body>

@foreach($parcelas as $index => $parcela)

<div class="boleto-wrap">
    
    <!-- Cabeçalho com logo, telefone, nome e parcela -->
    <table class="header-table">
        <tr>
            @php
                // Logo segura
                if ($config && !empty($config->logo) && file_exists(public_path('uploads/configEmitente/'.$config->logo))) {
                    $logoFile = public_path('uploads/configEmitente/'.$config->logo);
                } else {
                    $logoFile = public_path('logos/logonfenotas.png');
                }
                $logoBase64 = base64_encode(file_get_contents($logoFile));
            @endphp

            <td class="header-logo">
                <img src="data:image/jpeg;base64,{{ $logoBase64 }}" alt="Logo da Empresa">
            </td>
            
            <td class="header-info">
                <div class="empresa-nome">{{ $empresa->nome_fantasia ?? $empresa->razao_social }}</div>
                @if(!empty($empresa->telefone))
                <div class="empresa-telefone">Tel: {{ $empresa->telefone }}</div>
                @endif
            </td>

            <td class="parcela-info">
            </td>
        </tr>
    </table>

    <!-- Conteúdo do boleto -->
    <table>
        <tr>
            <td colspan="2">
                <span class="label">Beneficiário</span>
                <span class="dado-comum"><strong>{{ $empresa->razao_social }}</strong></span>
                <span class="dado-comum" style="font-size: 8pt;">CNPJ: {{ $empresa->cpf_cnpj ?? $empresa->cnpj }}</span>
                <span class="dado-comum" style="font-size: 8pt;">
                    Endereço: {{ $empresa->rua }}, {{ $empresa->numero }} - {{ $empresa->bairro }} - {{ $cidade->nome ?? '' }} @if(!empty($cidade->estado))/ {{ $cidade->estado }}@endif
                </span>
            </td>
            <td rowspan="4" class="qr-cell">
                <span class="label">Pagar com PIX</span>
                @if($parcela->qrcode)
                    <img src="data:image/png;base64,{{ $parcela->qrcode }}" width="80" height="80" style="margin: 5px 0;">
                @endif
                <div style="font-size: 6pt; font-weight: bold;">BAIXA IMEDIATA</div>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <span class="label">Pagador</span>
                <span class="dado-comum">{{ $cliente->razao_social }} — {{ $cliente->cpf_cnpj }}</span>
                @php
                    $rua = $cliente->rua_cobranca ?: $cliente->rua;
                    $numero = $cliente->numero_cobranca ?: $cliente->numero;
                    $bairro = $cliente->bairro_cobranca ?: $cliente->bairro;
                    $cep = $cliente->cep_cobranca ?: $cliente->cep;
                    $cidadeCliente = $cliente->cidade_cobranca ?? $cliente->cidade ?? null;
                @endphp
                <span class="dado-comum" style="font-size: 8pt;">
                    CEP: {{ $cep }} <br>
                    Rua: {{ $rua }}, Nº {{ $numero }} — Bairro: {{ $bairro }} <br>
                    Cidade: {{ $cidadeCliente->nome ?? '' }} @if(isset($cidadeCliente->estado)) / {{ $cidadeCliente->estado }} @endif
                </span>
            </td>
        </tr>

        <tr>
            <td style="background-color: #f9f9f9;">
                <span class="label">Data de Vencimento</span>
                <span class="valor-venc">{{ \Carbon\Carbon::parse($parcela->data_vencimento)->format('d/m/Y') }}</span>
            </td>
            <td style="background-color: #f9f9f9;">
                <span class="label">Valor da Parcela</span>
                <span class="valor-venc">R$ {{ number_format($parcela->valor_integral, 2, ',', '.') }}</span>
            </td>
        </tr>

        <tr>
            <td colspan="2" class="instrucoes-texto">
                <span class="label">Instruções de Responsabilidade do Beneficiário</span>
                • Referência: {{ $parcela->referencia }} | MENSALIDADE {{ $loop->iteration }}/{{ count($parcelas) }}.<br>
                • Pagamento exclusivo via PIX. Em caso de dúvidas, entre em contato {{ $empresa->telefone }}.
            </td>
        </tr>
    </table>
</div>

@if(($loop->iteration % 3) == 0 && !$loop->last)
    <div class="page-break"></div>
@endif

@endforeach

</body>
</html>