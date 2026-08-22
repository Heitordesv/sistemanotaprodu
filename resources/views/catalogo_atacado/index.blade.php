<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index,follow">
    <title>{{ $identidade['nome'] }} | Catálogo de atacado</title>
    <meta name="description" content="Consulte os produtos e preços de atacado de {{ $identidade['nome'] }}.">

    <style>
        :root {
            --preto: #000000;
            --cinza-900: #171717;
            --cinza-700: #404040;
            --cinza-500: #737373;
            --cinza-300: #d4d4d4;
            --cinza-200: #e5e5e5;
            --cinza-100: #f5f5f5;
            --branco: #ffffff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--branco);
            color: var(--preto);
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            line-height: 1.5;
        }

        a { color: var(--preto); }

        .cabecalho {
            background: var(--branco);
            color: var(--preto);
            border-bottom: 1px solid var(--cinza-200);
            padding: 28px 20px;
        }

        .cabecalho-conteudo,
        .conteudo {
            width: min(1180px, 100%);
            margin: 0 auto;
        }

        .empresa {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .empresa-logo {
            width: 78px;
            height: 78px;
            flex: 0 0 78px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border: 1px solid var(--cinza-200);
            border-radius: 14px;
            background: var(--branco);
        }

        .empresa-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 8px;
        }

        .empresa-inicial {
            color: var(--preto);
            font-size: 30px;
            font-weight: 800;
        }

        .empresa h1 {
            margin: 0 0 4px;
            color: var(--preto);
            font-size: clamp(24px, 4vw, 38px);
            line-height: 1.15;
        }

        .empresa p {
            margin: 0;
            color: var(--cinza-700);
        }

        .contatos {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 18px;
            margin-top: 18px;
            color: var(--cinza-700);
            font-size: 14px;
        }

        .contatos a,
        .contatos span { text-decoration: none; }

        .conteudo { padding: 28px 20px 40px; }

        .painel {
            background: var(--branco);
            border: 1px solid var(--cinza-200);
            border-radius: 14px;
        }

        .pesquisa { padding: 22px; }

        .pesquisa-topo {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 16px;
            margin-bottom: 18px;
        }

        .pesquisa-topo h2,
        .lista-cabecalho h2 {
            margin: 0 0 4px;
            color: var(--preto);
        }

        .pesquisa-topo p,
        .contador {
            margin: 0;
            color: var(--cinza-500);
            font-size: 14px;
        }

        .contador { white-space: nowrap; }

        .filtros {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) minmax(180px, 290px) auto auto;
            gap: 12px;
        }

        .campo {
            width: 100%;
            min-height: 46px;
            border: 1px solid var(--cinza-300);
            border-radius: 9px;
            background: var(--branco);
            color: var(--preto);
            padding: 10px 13px;
            font-size: 15px;
            outline: none;
        }

        .campo:focus {
            border-color: var(--preto);
            box-shadow: 0 0 0 3px rgba(0, 0, 0, .08);
        }

        .botao {
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--preto);
            border-radius: 9px;
            padding: 10px 18px;
            background: var(--preto);
            color: var(--branco);
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
        }

        .botao:hover { opacity: .84; }

        .botao-secundario {
            background: var(--branco);
            color: var(--preto);
        }

        .aviso {
            margin: 16px 0 0;
            padding: 12px 14px;
            border: 1px solid var(--cinza-300);
            border-radius: 9px;
            background: var(--cinza-100);
            color: var(--cinza-900);
            font-size: 14px;
        }

        .lista {
            margin-top: 18px;
            overflow: hidden;
        }

        .lista-cabecalho {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 18px 22px;
            border-bottom: 1px solid var(--cinza-200);
        }

        .lista-cabecalho h2 { font-size: 18px; }

        .selo {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border: 1px solid var(--cinza-300);
            border-radius: 999px;
            background: var(--branco);
            color: var(--preto);
            font-size: 12px;
            font-weight: 800;
        }

        .tabela-responsiva { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px 18px;
            border-bottom: 1px solid var(--cinza-200);
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: var(--cinza-100);
            color: var(--preto);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        tbody tr:last-child td { border-bottom: 0; }
        tbody tr:hover { background: #fafafa; }

        .produto-nome {
            display: block;
            max-width: 470px;
            color: var(--preto);
            font-weight: 750;
        }

        .produto-codigo {
            color: var(--cinza-500);
            font-size: 13px;
        }

        .preco {
            white-space: nowrap;
            color: var(--preto);
            font-size: 17px;
            font-weight: 850;
        }

        .vazio {
            padding: 46px 22px;
            text-align: center;
            color: var(--cinza-500);
        }

        .vazio strong {
            display: block;
            margin-bottom: 5px;
            color: var(--preto);
            font-size: 18px;
        }

        .paginacao {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 18px 22px;
            border-top: 1px solid var(--cinza-200);
            color: var(--cinza-500);
            font-size: 14px;
        }

        .paginacao-acoes { display: flex; gap: 8px; }

        .pagina-link,
        .pagina-inativa {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            border: 1px solid var(--cinza-300);
            border-radius: 8px;
            padding: 7px 13px;
            text-decoration: none;
            font-weight: 700;
        }

        .pagina-link {
            background: var(--branco);
            color: var(--preto);
        }

        .pagina-inativa {
            background: var(--cinza-100);
            color: var(--cinza-500);
        }

        .rodape {
            padding: 4px 20px 32px;
            color: var(--cinza-500);
            text-align: center;
            font-size: 13px;
        }

        @media (max-width: 850px) {
            .filtros { grid-template-columns: 1fr 1fr; }
            .botao { width: 100%; }
        }

        @media (max-width: 640px) {
            .cabecalho { padding: 22px 16px; }
            .empresa { align-items: flex-start; }
            .empresa-logo { width: 62px; height: 62px; flex-basis: 62px; }
            .empresa-inicial { font-size: 24px; }
            .conteudo { padding: 20px 12px 30px; }
            .pesquisa-topo, .lista-cabecalho { align-items: flex-start; flex-direction: column; }
            .filtros { grid-template-columns: 1fr; }
            .tabela-responsiva { overflow: visible; }
            table, thead, tbody, tr, th, td { display: block; width: 100%; }
            thead { display: none; }
            tbody { padding: 10px; }
            tbody tr {
                margin-bottom: 12px;
                overflow: hidden;
                border: 1px solid var(--cinza-200);
                border-radius: 10px;
                background: var(--branco);
            }
            td {
                display: grid;
                grid-template-columns: 105px 1fr;
                gap: 12px;
                padding: 11px 12px;
                border-bottom: 1px solid var(--cinza-200);
            }
            td::before {
                content: attr(data-label);
                color: var(--cinza-500);
                font-size: 11px;
                font-weight: 800;
                text-transform: uppercase;
            }
            td:last-child { border-bottom: 0; }
            .produto-nome { max-width: none; }
            .paginacao { align-items: stretch; flex-direction: column; }
            .paginacao-acoes > * { flex: 1; }
        }
    </style>
</head>
<body>
<header class="cabecalho">
    <div class="cabecalho-conteudo">
        <div class="empresa">
            <div class="empresa-logo" aria-hidden="true">
                @if ($identidade['logo_url'])
                    <img src="{{ $identidade['logo_url'] }}" alt="Logo de {{ $identidade['nome'] }}">
                @else
                    <span class="empresa-inicial">{{ mb_strtoupper(mb_substr($identidade['nome'], 0, 1)) }}</span>
                @endif
            </div>
            <div>
                <h1>{{ $identidade['nome'] }}</h1>
                <p>Catálogo de produtos para atacado</p>
            </div>
        </div>

        @if ($identidade['telefone'] || $identidade['email'] || $identidade['endereco'])
            <div class="contatos">
                @if ($identidade['telefone'])
                    <a href="tel:{{ $identidade['telefone_link'] }}">☎ {{ $identidade['telefone'] }}</a>
                @endif
                @if ($identidade['email'])
                    <a href="mailto:{{ $identidade['email'] }}">✉ {{ $identidade['email'] }}</a>
                @endif
                @if ($identidade['endereco'])
                    <span>⌖ {{ $identidade['endereco'] }}</span>
                @endif
            </div>
        @endif
    </div>
</header>

<main class="conteudo">
    <section class="painel pesquisa" aria-labelledby="titulo-pesquisa">
        <div class="pesquisa-topo">
            <div>
                <h2 id="titulo-pesquisa">Encontre um produto</h2>
                <p>Pesquise por nome, referência, código de barras ou categoria.</p>
            </div>
            <span class="contador">{{ number_format($produtos->total(), 0, ',', '.') }} produto(s)</span>
        </div>

        <form class="filtros" method="GET" action="{{ route('catalogo.atacado', ['empresaId' => $empresa->id]) }}">
            <input class="campo" type="search" name="q" value="{{ $termo }}" placeholder="Digite o produto, referência ou código" aria-label="Pesquisar produtos" autocomplete="off">
            <select class="campo" name="categoria_id" aria-label="Filtrar por categoria">
                <option value="">Todas as categorias</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ (int) $categoriaId === (int) $categoria->id ? 'selected' : '' }}>{{ $categoria->nome }}</option>
                @endforeach
            </select>
            <button class="botao" type="submit">Pesquisar</button>
            <a class="botao botao-secundario" href="{{ route('catalogo.atacado', ['empresaId' => $empresa->id]) }}">Limpar</a>
        </form>

        @if (!$listaAtacado)
            <p class="aviso">A empresa ainda não possui uma lista de preços chamada “Atacado”. Por isso, estão sendo exibidos os preços de venda padrão.</p>
        @endif
    </section>

    <section class="painel lista" aria-labelledby="titulo-produtos">
        <div class="lista-cabecalho">
            <h2 id="titulo-produtos">Produtos disponíveis</h2>
            <span class="selo">{{ $listaAtacado ? 'Tabela: ' . $listaAtacado->nome : 'Tabela padrão' }}</span>
        </div>

        @if ($produtos->count())
            <div class="tabela-responsiva">
                <table>
                    <thead>
                    <tr>
                        <th>Produto</th><th>Categoria</th><th>Referência</th><th>Código de barras</th><th>Unidade</th><th>{{ $listaAtacado ? 'Preço atacado' : 'Preço' }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($produtos as $produto)
                        <tr>
                            <td data-label="Produto"><span class="produto-nome">{{ $produto->nome }}</span></td>
                            <td data-label="Categoria">{{ optional($produto->categoria)->nome ?: 'Sem categoria' }}</td>
                            <td data-label="Referência"><span class="produto-codigo">{{ $produto->referencia ?: '—' }}</span></td>
                            <td data-label="Cód. barras"><span class="produto-codigo">{{ $produto->codBarras && $produto->codBarras !== 'SEM GTIN' ? $produto->codBarras : '—' }}</span></td>
                            <td data-label="Unidade">{{ $produto->unidade_venda ?: 'UN' }}</td>
                            <td data-label="Preço"><span class="preco">R$ {{ number_format((float) $produto->catalogo_valor, $casasDecimais, ',', '.') }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if ($produtos->hasPages())
                <nav class="paginacao" aria-label="Paginação do catálogo">
                    <span>Página {{ $produtos->currentPage() }} de {{ $produtos->lastPage() }}</span>
                    <div class="paginacao-acoes">
                        @if ($produtos->onFirstPage())
                            <span class="pagina-inativa">Anterior</span>
                        @else
                            <a class="pagina-link" href="{{ $produtos->previousPageUrl() }}" rel="prev">Anterior</a>
                        @endif
                        @if ($produtos->hasMorePages())
                            <a class="pagina-link" href="{{ $produtos->nextPageUrl() }}" rel="next">Próxima</a>
                        @else
                            <span class="pagina-inativa">Próxima</span>
                        @endif
                    </div>
                </nav>
            @endif
        @else
            <div class="vazio"><strong>Nenhum produto encontrado</strong>Revise os termos da pesquisa ou remova os filtros.</div>
        @endif
    </section>
</main>

<footer class="rodape">
    @if ($identidade['razao_social']){{ $identidade['razao_social'] }} · @endif Catálogo atualizado conforme o cadastro de produtos da empresa.
</footer>
</body>
</html>