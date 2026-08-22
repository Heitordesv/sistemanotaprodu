<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Holerite - {{ $apuracao->funcionario->nome }}</title>
    <style>
        /* Estilos CSS (Mantidos para a estrutura padrão de contracheque) */
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 0; padding: 0; }
        .container { width: 100%; padding: 10px; }
        h2, h3 { margin: 0; font-size: 14px; }
        .bloco { border: 1px solid #000; margin-bottom: 5px; padding: 0; }
        .bloco-header { display: table; width: 100%; table-layout: fixed; }
        .bloco-header > div { display: table-cell; padding: 3px 5px; border-right: 1px solid #000; vertical-align: top; height: 30px; }
        .bloco-header > div:last-child { border-right: none; }
        .bloco-header p { margin: 1px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .bloco-titulo { font-weight: bold; font-size: 10px; background-color: #e0e0e0; border: 1px solid #000; padding: 3px 5px; margin-bottom: 5px; text-align: center; }
        .tabela-eventos { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .tabela-eventos th, .tabela-eventos td { border: 1px solid #000; padding: 3px 5px; text-align: right; font-size: 10px; }
        .tabela-eventos th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
        .tabela-eventos td:nth-child(2) { text-align: left; width: 40%; }
        .tabela-eventos td:nth-child(1) { text-align: center; width: 10%; }
        .tabela-eventos td:nth-child(3) { width: 15%; }
        .tabela-eventos td:nth-child(4), .tabela-eventos td:nth-child(5) { width: 17.5%; }
        .rodape-totais { margin-top: 5px; display: table; width: 100%; table-layout: fixed; border: 1px solid #000; }
        .rodape-totais > div { display: table-cell; padding: 5px; vertical-align: top; }
        .bases { width: 35%; border-right: 1px solid #000; font-size: 10px; }
        .bases table { width: 100%; border-collapse: collapse; margin: 0; }
        .bases th, .bases td { border: none; padding: 1px 3px; text-align: right; }
        .bases th { text-align: left; font-weight: normal; }
        .resumo-valores { width: 30%; border-right: 1px solid #000; text-align: center; }
        .liquido { width: 35%; text-align: center; }
        .resumo-valores p, .liquido p { margin: 0; font-size: 10px; }
        .valor-total { font-size: 14px; font-weight: bold; color: #000; margin-top: 2px; }
        .valor-liquido { font-size: 20px; font-weight: bold; color: #000; margin-top: 5px; }
        .assinatura { margin-top: 40px; text-align: center; }
        .linha-assinatura { border-top: 1px dashed #000; width: 250px; margin: auto; margin-bottom: 5px; }
        .footer { margin-top: 10px; font-size: 9px; text-align: center; color: #555; }
    </style>
</head>
<body>
<div class="container">
    
    <div class="bloco">
        <div class="bloco-header">
            <div style="width: 55%;">
                <p><strong>{{ $empresa->razao_social }} ({{ $empresa->nome_fantasia }})</strong></p>
                <p><strong>Endereço:</strong> {{ $empresa->rua }}, {{ $empresa->numero }} - {{ $empresa->cidade['nome'] ?? '-' }}/{{ $empresa->cidade['uf'] ?? '-' }}</p>
                <p><strong>CNPJ:</strong> {{ $empresa->cpf_cnpj }}</p> 
            </div>
            <div style="width: 45%; text-align: center; border-right: none;">
                <h3 style="margin-bottom: 5px;">DEMONSTRATIVO DE PAGAMENTO</h3>
                <p style="font-size: 12px;"><strong>MÊS/ANO:</strong> {{ $apuracao->mes }}/{{ $apuracao->ano }}</p>
            </div>
        </div>
    </div>

    <div class="bloco">
        <div class="bloco-header">
            <div style="width: 40%;">
                <p><strong>Funcionário:</strong> {{ $apuracao->funcionario->nome }}</p>
                <p><strong>RG:</strong> {{ $apuracao->funcionario->rg ?? '-' }}</p> 
            </div>
            <div style="width: 30%;">
                <p><strong>CPF:</strong> {{ $apuracao->funcionario->cpf }}</p> 
                <p><strong>Salário Base:</strong> R$ {{ number_format($apuracao->funcionario->salario, 2, ',', '.') }}</p> 
            </div>
            <div style="width: 30%; border-right: none;">
                <p><strong>Cargo:</strong> {{ $apuracao->funcionario->cargo ?? 'Não Informado' }}</p> 
                <p><strong>Depto/Setor:</strong> {{ $apuracao->funcionario->setor ?? '-' }}</p>
            </div>
        </div>
    </div>
    
    <div class="bloco-titulo">EVENTOS DO MÊS</div>
    <table class="tabela-eventos">
        <thead>
            <tr>
                <th>Cód.</th>
                <th>Descrição</th>
                <th>Ref.</th>
                <th>Vencimentos (R$)</th>
                <th>Descontos (R$)</th>
            </tr>
        </thead>
        <tbody>
            {{-- Lógica de unificação das coleções $proventos e $descontos --}}
            @php 
                $eventos = collect([]); 
                $eventos = $eventos->merge($proventos); 
                $eventos = $eventos->merge($descontos);
                
                // Ordena os eventos por código para melhor visualização (opcional)
                $eventos = $eventos->sortBy('codigo');
            @endphp

            @forelse($eventos as $e)
                <tr>
                    {{-- ACESSANDO PROPRIEDADES MAPEADAS NO CONTROLLER --}}
                    <td>{{ $e->codigo ?? '-' }}</td>
                    <td>{{ $e->nome ?? 'Descrição Não Informada' }}</td> 
                    
                    {{-- USANDO A NOVA PROPRIEDADE 'referencia' --}}
                    <td>{{ $e->referencia ?? '1' }}</td> 
                    
                    {{-- USANDO A NOVA PROPRIEDADE 'valor_calculado' para Vencimentos --}}
                    <td>
                        {{ ($e->condicao ?? '') === 'soma' && $e->valor_calculado > 0 
                            ? number_format($e->valor_calculado, 2, ',', '.') 
                            : '' 
                        }}
                    </td>
                    
                    {{-- USANDO A NOVA PROPRIEDADE 'valor_calculado' para Descontos --}}
                    <td>
                        {{ ($e->condicao ?? '') === 'diminui' && $e->valor_calculado > 0
                            ? number_format($e->valor_calculado, 2, ',', '.') 
                            : '' 
                        }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align: center;">Nenhum evento (provento ou desconto) registrado para o mês.</td></tr>
            @endforelse
            
            {{-- Linhas em branco para preencher o espaço (opcional) --}}
            @for ($i = $eventos->count(); $i < 5; $i++)
                <tr><td colspan="5" style="height: 15px; border-left: none; border-right: none; border-bottom: none;"></td></tr>
            @endfor
            
        </tbody>
    </table>

    <div class="rodape-totais">
        <div class="bases">
            <p style="font-weight: bold; text-align: center; margin-bottom: 5px;">BASES DE CÁLCULO</p>
            <table>
                {{-- USANDO BASES DE CÁLCULO (Variáveis que devem vir do Controller) --}}
                <tr><th>Base Cálc. INSS:</th><td>R$ {{ number_format($baseInss, 2, ',', '.') }}</td></tr>
                <tr><th>Base Cálc. IRRF:</th><td>R$ {{ number_format($baseIrrf, 2, ',', '.') }}</td></tr>
                <tr><th>Base Cálc. FGTS:</th><td>R$ {{ number_format($baseFgts, 2, ',', '.') }}</td></tr>
                <tr><th>Valor FGTS Mês:</th><td>R$ {{ number_format($fgtsMes, 2, ',', '.') }}</td></tr>
            </table>
        </div>
        
        <div class="resumo-valores">
            <p>TOTAL DE VENCIMENTOS:</p>
            <p class="valor-total">R$ {{ number_format($totalProventos, 2, ',', '.') }}</p>
            <p style="margin-top: 10px;">TOTAL DE DESCONTOS:</p>
            <p class="valor-total">R$ {{ number_format($totalDescontos, 2, ',', '.') }}</p>
        </div>
        
        <div class="liquido" style="border-left: 1px solid #000;">
            <p>VALOR LÍQUIDO A RECEBER</p>
            <p style="font-size: 8px;">(Vencimentos - Descontos)</p>
            <p class="valor-liquido">R$ {{ number_format($valorLiquido, 2, ',', '.') }}</p>
        </div>
    </div>

    @if(!empty($apuracao->observacao))
        <div class="bloco" style="margin-top: 5px; padding: 5px;">
            <strong>Observações:</strong> {{ $apuracao->observacao }}
        </div>
    @endif

    <div class="assinatura">
        <p>Declaro haver recebido a importância líquida detalhada neste demonstrativo.</p>
        <div class="linha-assinatura"></div>
        <p style="margin: 0;">Assinatura do Funcionário</p>
    </div>

    <div class="footer">
        Holerite gerado automaticamente pelo sistema. | Data de Emissão: {{ date('d/m/Y H:i:s') }}
    </div>
</div>
</body>
</html>