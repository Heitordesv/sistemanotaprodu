<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>DANFE - {{ $pedido->id }}</title>
    <style type="text/css">
        @media print {
            @page { margin: 8mm; }
            footer { page-break-after: always; }
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { padding: 5mm; font-family: "Times New Roman", serif; background: #fff; color: #000; }

        .nfeArea { width: 19cm; margin: 0 auto; }

        .nfeArea table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: -1px;
        }

        .nfeArea td {
            border: 1px solid #000;
            padding: 1px 4px;
            vertical-align: top;
        }

        .nf-label {
            text-transform: uppercase;
            font-size: 5pt;
            display: block;
            margin-bottom: 1px;
        }

        .info {
            font-weight: bold;
            font-size: 7.5pt;
            display: block;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .titulo-secao {
            font-size: 6.5pt;
            font-weight: bold;
            margin: 4px 0 1px 0;
            text-transform: uppercase;
        }

        .txt-center { text-align: center; }
        .txt-right { text-align: right; }
        .bold { font-weight: bold; }
        
        .barcode { height: 35px; width: 100%; margin: 3px 0; }
        .qrcode { width: 50px; height: 50px; margin-top: 2px; }
        
        .item-table thead td {
            background: #eee;
            font-weight: bold;
            font-size: 5pt;
            text-align: center;
            padding: 3px 1px;
        }
        
        .item-table tbody td { 
            font-size: 6.5pt; 
            height: 14px; 
            border-top: none; 
            border-bottom: none; 
        }

        .logo-container {
            width: 100%;
            height: 60px;
            text-align: center;
        }
        .img-logo {
            max-width: 100%;
            max-height: 55px;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>

<div class="nfeArea">
    <table>
        <tr>
            <td width="80%">
                <span class="nf-label">RECEBEMOS DE {{ $config->razao_social ?? $pedido->empresa->razao_social }} OS PRODUTOS/SERVIÇOS CONSTANTES DA NOTA FISCAL INDICADA AO LADO</span>
            </td>
            <td width="20%" rowspan="2" class="txt-center">
                <span class="info" style="font-size: 9pt;">NF-e</span><br>
                <span class="info">Nº {{ str_pad($pedido->id, 9, '0', STR_PAD_LEFT) }}</span>
                <span class="info">SÉRIE {{ $config->numero_serie_nfe ?? '001' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <table style="border:none;">
                    <tr>
                        <td width="25%" style="border:none; border-right: 1px solid #000;"><span class="nf-label">DATA DE RECEBIMENTO</span></td>
                        <td width="75%" style="border:none;"><span class="nf-label">IDENTIFICAÇÃO E ASSINATURA DO RECEBEDOR</span></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <hr style="border: 0.5px dashed #000; margin: 8px 0;">

    <table>
        <tr>
            <td width="18%" class="txt-center" style="vertical-align: middle;">
                @php
                    $logoBase64 = null;
                    if(!empty($config->logo)){
                        $path = public_path('storage/' . $config->logo);
                        if(file_exists($path)){
                            $logoBase64 = base64_encode(file_get_contents($path));
                        }
                    }
                @endphp

                @if($logoBase64)
                    <div class="logo-container">
                        <img src="data:image/png;base64,{{ $logoBase64 }}" class="img-logo">
                    </div>
                @else
                    <div style="font-size: 7pt; font-weight: bold; padding-top: 15px;">
                        {{ $config->razao_social ?? $pedido->empresa->razao_social }}
                    </div>
                @endif
            </td>
            <td width="22%" class="txt-center">
                <span class="info" style="font-size: 7.5pt; margin-top: 2px;">{{ $config->razao_social ?? $pedido->empresa->razao_social }}</span>
                <span class="nf-label" style="font-size: 5.5pt; line-height: 1.2;">
                    {{ $config->logradouro ?? $pedido->empresa->rua }}, {{ $config->numero ?? $pedido->empresa->numero }}<br>
                    {{ $config->bairro ?? $pedido->empresa->bairro }} - CEP: {{ $config->cep ?? $pedido->empresa->cep }}<br>
                    {{ $pedido->empresa->cidade->nome }} - {{ $pedido->empresa->cidade->uf }}<br>
                    Fone: {{ $config->fone ?? $pedido->empresa->telefone }}
                </span>
            </td>
            <td width="15%" class="txt-center">
                <span class="bold" style="font-size: 9pt;">DANFE</span><br>
                <span class="nf-label">Documento Auxiliar da<br>Nota Fiscal Eletrônica</span>
                <div style="border: 1px solid #000; width: 18px; margin: 2px auto; font-weight: bold;">1</div>
                <span class="nf-label">0 - Entrada<br>1 - Saída</span>
                <span class="info">Nº {{ str_pad($pedido->id, 9, '0', STR_PAD_LEFT) }}</span>
                <span class="info">SÉRIE {{ $config->numero_serie_nfe ?? '001' }}</span>
                <span class="nf-label">FOLHA 1 / 1</span>
            </td>
            <td width="45%">
                <div class="txt-center">
                    <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text={{ $chave }}&height=12&scale=2" class="barcode">
                    <span class="nf-label">CHAVE DE ACESSO</span>
                    <span class="info" style="font-size: 7pt;">{{ chunk_split($chave, 4, ' ') }}</span>
                </div>
                <div style="border-top: 1px solid #000; margin-top: 4px; padding-top: 2px;" class="txt-center">
                    <span class="nf-label" style="font-size: 4.5pt;">Consulta de autenticidade no portal nacional da NF-e www.nfe.fazenda.gov.br ou no site da Sefaz Autorizadora</span>
                </div>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td width="65%">
                <span class="nf-label">NATUREZA DA OPERAÇÃO</span>
                <span class="info">{{ $config->nat_op_padrao ?? 'VENDA DE MERCADORIA' }}</span>
            </td>
            <td>
                <span class="nf-label">PROTOCOLO DE AUTORIZAÇÃO DE USO</span>
                <span class="info">13524000{{ $pedido->id }} - {{ date('d/m/Y H:i') }}</span>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td width="33%"><span class="nf-label">INSCRIÇÃO ESTADUAL</span><span class="info">{{ $config->ie ?? 'ISENTO' }}</span></td>
            <td width="33%"><span class="nf-label">INSCR. ESTADUAL DO SUBST. TRIB.</span><span class="info">---</span></td>
            <td width="34%"><span class="nf-label">CNPJ</span><span class="info">{{ $config->cnpj }}</span></td>
        </tr>
    </table>

    <div class="titulo-secao">DESTINATÁRIO / REMETENTE</div>
    <table>
        <tr>
            <td width="55%"><span class="nf-label">NOME / RAZÃO SOCIAL</span><span class="info">{{ $pedido->cliente->nome }} {{ $pedido->cliente->sobre_nome }}</span></td>
            <td width="25%"><span class="nf-label">CNPJ / CPF</span><span class="info">{{ $pedido->cliente->cpf_cnpj }}</span></td>
            <td width="20%"><span class="nf-label">DATA EMISSÃO</span><span class="info">{{ date('d/m/Y') }}</span></td>
        </tr>
    </table>
    <table>
        <tr>
            <td width="45%"><span class="nf-label">ENDEREÇO</span><span class="info">{{ $pedido->endereco->rua }}, {{ $pedido->endereco->numero }}</span></td>
            <td width="20%"><span class="nf-label">BAIRRO / DISTRITO</span><span class="info">{{ $pedido->endereco->bairro }}</span></td>
            <td width="15%"><span class="nf-label">CEP</span><span class="info">{{ $pedido->endereco->cep }}</span></td>
            <td width="20%"><span class="nf-label">DATA SAÍDA/ENTRADA</span><span class="info">{{ date('d/m/Y') }}</span></td>
        </tr>
    </table>
    <table>
        <tr>
            <td width="40%"><span class="nf-label">MUNICÍPIO</span><span class="info">{{ $pedido->endereco->cidade }}</span></td>
            <td width="10%"><span class="nf-label">UF</span><span class="info">{{ $pedido->endereco->uf }}</span></td>
            <td width="20%"><span class="nf-label">FONE / FAX</span><span class="info">{{ $pedido->cliente->telefone }}</span></td>
            <td width="20%"><span class="nf-label">INSCRIÇÃO ESTADUAL</span><span class="info">{{ $pedido->cliente->ie ?? 'ISENTO' }}</span></td>
            <td width="10%"><span class="nf-label">HORA SAÍDA</span><span class="info">{{ date('H:i') }}</span></td>
        </tr>
    </table>

    <div class="titulo-secao">CÁLCULO DO IMPOSTO</div>
    <table>
        <tr>
            <td><span class="nf-label">BASE DE CÁLC. ICMS</span><span class="info txt-right">0,00</span></td>
            <td><span class="nf-label">VALOR DO ICMS</span><span class="info txt-right">0,00</span></td>
            <td><span class="nf-label">BASE CÁLC. ICMS ST</span><span class="info txt-right">0,00</span></td>
            <td><span class="nf-label">VALOR DO ICMS ST</span><span class="info txt-right">0,00</span></td>
            <td><span class="nf-label">V. TOTAL PRODUTOS</span><span class="info txt-right">R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}</span></td>
        </tr>
        <tr>
            <td><span class="nf-label">VALOR DO FRETE</span><span class="info txt-right">0,00</span></td>
            <td><span class="nf-label">VALOR DO SEGURO</span><span class="info txt-right">0,00</span></td>
            <td><span class="nf-label">DESCONTO</span><span class="info txt-right">0,00</span></td>
            <td><span class="nf-label">OUTRAS DESP.</span><span class="info txt-right">0,00</span></td>
            <td><span class="nf-label">V. TOTAL DA NOTA</span><span class="info txt-right" style="font-size: 9pt;">R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}</span></td>
        </tr>
    </table>

    <div class="titulo-secao">TRANSPORTADOR / VOLUMES TRANSPORTADOS</div>
    <table>
        <tr>
            <td width="25%"><span class="nf-label">RAZÃO SOCIAL</span><span class="info">O MESMO</span></td>
            <td width="15%"><span class="nf-label">FRETE POR CONTA</span><span class="info">{{ $config->frete_padrao ?? '0 - EMITENTE' }}</span></td>
            <td width="15%"><span class="nf-label">CÓDIGO ANTT</span><span class="info">---</span></td>
            <td width="15%"><span class="nf-label">PLACA VEÍCULO</span><span class="info">---</span></td>
            <td width="5%"><span class="nf-label">UF</span><span class="info">---</span></td>
            <td width="25%"><span class="nf-label">CNPJ / CPF</span><span class="info">---</span></td>
        </tr>
    </table>
    <table>
        <tr>
            <td width="10%"><span class="nf-label">QUANTIDADE</span><span class="info">1</span></td>
            <td width="15%"><span class="nf-label">ESPÉCIE</span><span class="info">VOLUME</span></td>
            <td width="15%"><span class="nf-label">MARCA</span><span class="info">---</span></td>
            <td width="15%"><span class="nf-label">NUMERAÇÃO</span><span class="info">---</span></td>
            <td width="20%"><span class="nf-label">PESO BRUTO (kg)</span><span class="info">0.000</span></td>
            <td width="25%"><span class="nf-label">PESO LÍQUIDO (kg)</span><span class="info">0.000</span></td>
        </tr>
    </table>

    <div class="titulo-secao">DADOS DOS PRODUTOS / SERVIÇOS</div>
    <table class="item-table" style="border-bottom: 1px solid #000;">
        <thead>
            <tr>
                <td width="8%">CÓDIGO</td>
                <td width="28%">DESCRIÇÃO</td>
                <td width="8%">NCM/SH</td>
                <td width="5%">CST</td>
                <td width="5%">CFOP</td>
                <td width="5%">UN</td>
                <td width="7%">QTD</td>
                <td width="9%">V.UNIT</td>
                <td width="9%">V.TOTAL</td>
                <td width="8%">BC ICMS</td>
                <td width="8%">V.ICMS</td>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->itens as $item)
            @php 
                $p = $item->produtoEcommerce->produto;
                $vUnit = $p->valor_venda ?? 0;
                $cst_display = $p->CST_CSOSN ?? ($config->CST_CSOSN_padrao ?? '000');
            @endphp
            <tr>
                <td class="txt-center">{{ $p->id }}</td>
                <td>{{ $p->nome }}</td>
                <td class="txt-center">{{ $p->NCM }}</td>
                <td class="txt-center">{{ $cst_display }}</td>
                <td class="txt-center">{{ $p->CFOP_saida_estadual }}</td>
                <td class="txt-center">{{ $p->unidade_venda }}</td>
                <td class="txt-center">{{ $item->quantidade }}</td>
                <td class="txt-right">{{ number_format($vUnit, 2, ',', '.') }}</td>
                <td class="txt-right">{{ number_format($vUnit * $item->quantidade, 2, ',', '.') }}</td>
                <td class="txt-right">0,00</td>
                <td class="txt-right">0,00</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="titulo-secao">DADOS ADICIONAIS</div>
    <table style="min-height: 80px;">
        <tr>
            <td width="75%">
                <span class="nf-label">INFORMAÇÕES COMPLEMENTARES</span>
                <div style="font-size: 6pt; line-height: 1.2;">
                    {{ $config->campo_obs_nfe ?? '' }}<br>
                    Pedido: #{{ $pedido->id }} | Emitido via Sistema ERP.<br>
                    Impostos aproximados (Lei 12.741/12): R$ {{ number_format($pedido->valor_total * 0.18, 2, ',', '.') }} (18%)<br>
                    {{ $pedido->observacao ?? '' }}
                </div>
            </td>
            <td width="25%" class="txt-center">
                <span class="nf-label">RESERVADO AO FISCO</span>
                <img src="https://chart.googleapis.com/chart?chs=100x100&cht=qr&chl={{ urlencode('https://seusite.com/danfe/'.$chave) }}&choe=UTF-8" class="qrcode">
            </td>
        </tr>
    </table>
</div>

</body>
</html>