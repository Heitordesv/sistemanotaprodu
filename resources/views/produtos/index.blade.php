@extends('default.layout',['title' => 'Produtos'])

@section('content')
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="//unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="p-4 sm:p-6 lg:p-8 space-y-8 bg-gray-50 min-h-screen">
    
  {{-- Barra de ações Personalizada --}}
<div class="flex flex-wrap gap-3 sm:gap-4 justify-between items-center mb-8 p-5 bg-white rounded-2xl shadow-xl border border-gray-100">
    
    <div class="flex flex-col">
        <h1 class="text-2xl font-black text-gray-900 tracking-tight uppercase">Gestão de Produtos</h1>
        <p class="text-xs text-gray-500 font-medium">Controle de estoque, lotes e ferramentas</p>
    </div>

    <div class="flex flex-wrap gap-2 sm:gap-3 w-full lg:w-auto">
        {{-- Ações Principais --}}
        <a href="{{ route('produtos.create')}}" class="flex items-center justify-center px-4 py-2.5 text-sm font-bold rounded-xl shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 transition-all transform hover:scale-105 w-full sm:w-auto">
            <i class="bx bx-plus-circle mr-2 text-xl"></i> NOVO PRODUTO
        </a>

        {{-- Alerta de Vencimento - Destaque em Vermelho/Laranja para Atenção --}}
        <a href="{{ route('produtos.alertasvencimento')}}" class="flex items-center justify-center px-4 py-2.5 text-sm font-bold rounded-xl shadow-sm text-white bg-orange-500 hover:bg-orange-600 transition-all transform hover:scale-105 w-full sm:w-auto">
            <i class="bx bx-bell-plus mr-2 text-xl animate-tada"></i> ALERTAS
        </a>
        <a href="{{ route('produtos.auditoria-tributaria') }}" class="flex items-center justify-center px-4 py-2.5 text-sm font-bold rounded-xl shadow-sm text-white bg-violet-600 hover:bg-violet-700 transition-all transform hover:scale-105 w-full sm:w-auto">
    <i class="bx bx-bot mr-2 text-xl"></i>
    AUDITORIA IA
    <span class="ml-2 px-2 py-0.5 text-[10px] font-black uppercase bg-amber-400 text-gray-900 rounded-full animate-pulse">
        TESTE GRÁTIS
    </span>
</a>
        <div class="h-8 w-px bg-gray-200 hidden lg:block mx-1"></div>

        {{-- Ferramentas Secundárias --}}
        <a href="{{ route('produtos.import')}}" class="flex items-center justify-center px-4 py-2.5 text-sm font-bold rounded-xl text-gray-700 bg-yellow-400 hover:bg-yellow-500 transition-all w-full sm:w-auto">
            <i class="bx bx-upload mr-2 text-xl"></i> IMPORTAR
        </a>

        <a href="{{ route('produtos.exportacaoBalanca')}}" class="flex items-center justify-center px-4 py-2.5 text-sm font-bold rounded-xl text-white bg-slate-700 hover:bg-slate-800 transition-all w-full sm:w-auto">
            <i class="bx bx-barcode-reader mr-2 text-xl"></i> BALANÇA
        </a>

        <a href="{{ route ('divisaoGrade.index')}}" class="flex items-center justify-center px-4 py-2.5 text-sm font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 transition-all w-full sm:w-auto">
            <i class="bx bx-grid-alt mr-2 text-xl"></i> GRADES
        </a>
    </div>
</div>

    {{-- Card principal: Filtro e tabela --}}
    <div class="bg-white shadow-2xl rounded-xl p-6 space-y-8">
        <h2 class="text-xl font-bold text-gray-700 border-b pb-4">Filtros de Pesquisa</h2>

        {!! Form::open()->fill(request()->all())->get()->attrs(['class' => 'space-y-6']) !!}
        <div class="grid grid-cols-1 md:grid-cols-6 lg:grid-cols-12 gap-6">
            <div class="col-span-12 sm:col-span-3 lg:col-span-2">
                {!! Form::select('tipo', 'Tipo de pesquisa', ['nome'=>'Descrição','referencia'=>'Referência','codBarras'=>'Código de barras'])
                    ->attrs(['class'=>'select2 border-gray-300 rounded-md shadow-sm block w-full']) !!}
            </div>
            <div class="col-span-12 sm:col-span-9 lg:col-span-4">
                {!! Form::text('nome','Pesquisar por nome')
                    ->attrs(['class'=>'border-gray-300 rounded-md shadow-sm block w-full']) !!}
            </div>
            <div class="col-span-6 sm:col-span-4 lg:col-span-2">
                {!! Form::select('categoria_id','Categoria',[''=>'Todas'] + $categorias->pluck('nome','id')->all())
                    ->attrs(['class'=>'form-select border-gray-300 rounded-md shadow-sm block w-full']) !!}
            </div>
            <div class="col-span-6 sm:col-span-4 lg:col-span-2">
                {!! Form::select('marca_id','Marca',[''=>'Todas'] + $marcas->pluck('nome','id')->all())
                    ->attrs(['class'=>'form-select border-gray-300 rounded-md shadow-sm block w-full']) !!}
            </div>
            <div class="col-span-6 sm:col-span-4 lg:col-span-2">
                {!! Form::select('estoque','Estoque',['0'=>'--','1'=>'Positivo','-1'=>'Negativo'])
                    ->attrs(['class'=>'form-select border-gray-300 rounded-md shadow-sm block w-full']) !!}
            </div>
            @if(empresaComFilial())
            <div class="col-span-12 lg:col-span-4 pt-2">
                {!! __view_locais_select_filtro("Local", $filial_id ?? '') !!}
            </div>
            @endif
        </div>

        <div class="flex flex-wrap gap-4 pt-4 border-t border-gray-100">
            <button class="flex items-center px-5 py-2 border border-transparent text-sm font-semibold rounded-lg shadow-md text-white bg-indigo-600 hover:bg-indigo-700" type="submit">
                <i class="bx bx-search mr-2"></i> Pesquisar
            </button>
            <a href="{{ route('produtos.index') }}" class="flex items-center px-5 py-2 border border-transparent text-sm font-semibold rounded-lg shadow-md text-white bg-gray-500 hover:bg-gray-600">
                <i class="bx bx-eraser mr-2"></i> Limpar Filtros
            </a>
        </div>
        {!! Form::close() !!}

        <hr class="my-6 border-gray-200" />

        <h2 class="text-xl font-bold text-gray-700 border-b pb-4">Resultados ({{ $data->total() ?? count($data) }} Produtos)</h2>

        <div class="overflow-x-auto relative rounded-lg border border-gray-200">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="py-3 px-6 w-1"></th>
                        <th class="py-3 px-6 w-1">Ações</th>
                        <th class="py-3 px-6">Descrição</th>
                      <th class="py-3 px-6">Ref</th>
                        <th class="py-3 px-6">Venda</th>
                        <th class="py-3 px-6">Un. C/V</th>
                        <th class="py-3 px-6 whitespace-nowrap">Data Cadastro</th>
                         <th class="py-3 px-6">Código de barras</th>
                        <th class="py-3 px-6">Ger. Estoque</th>
                        @if(empresaComFilial())
                        <th class="py-3 px-6">Disponibilidade</th>
                        @endif
                        <th class="py-3 px-6">Estoque Atual</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $p)
                    <tr class="border-b even:bg-gray-50 hover:bg-indigo-50 transition duration-100">
                        <td class="py-3 px-6"><img class="w-10 h-10 rounded-lg object-cover ring-2 ring-gray-200" src="{{ $p->img }}" alt="Imagem do Produto"></td>
<td class="py-3 px-6 **whitespace-nowrap**">
    {{-- Dropdown de Ações --}}
    {{-- Ajuste a DIV pai do dropdown para ser relativa --}}
    <div class="relative inline-block text-left" x-data="{ open:false }" @click.away="open=false">
        <button @click="open=!open" class="inline-flex justify-center items-center rounded-md border border-gray-300 shadow-sm px-3 py-1 bg-white text-xs font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Ações <i class="bx bx-chevron-down ml-1 text-sm"></i>
        </button>
        
        {{-- Removido 'origin-top-right' e alterado para 'left-0' --}}
        <ul x-show="open" 
            x-transition:enter="transition ease-out duration-100" 
            x-transition:enter-start="transform opacity-0 scale-95" 
            x-transition:enter-end="transform opacity-100 scale-100" 
            x-transition:leave="transition ease-in duration-75" 
            x-transition:leave-start="transform opacity-100 scale-100" 
            x-transition:leave-end="transform opacity-0 scale-95" 
            class="**origin-top-left absolute left-0** mt-2 w-56 rounded-md shadow-xl bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-50">
            
            <form action="{{ route('produtos.destroy', $p->id) }}" method="post" id="form-delete-{{$p->id}}">
                @method('delete') @csrf
                <div class="py-1">
                    <li><a href="{{ route('produtos.edit', $p->id) }}" class="flex items-center text-gray-700 px-4 py-2 text-sm hover:bg-gray-100"><i class="bx bx-edit-alt mr-2"></i> Editar</a></li>
                    <li><button type="button" class="flex items-center w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 btn-delete"><i class="bx bx-trash mr-2"></i> Apagar</button></li>
                </div>
                <div class="py-1">
                    <li><a href="{{ route('produtos.movimentacao', $p->id) }}" class="flex items-center text-gray-700 px-4 py-2 text-sm hover:bg-gray-100"><i class="bx bx-bar-chart-alt-2 mr-2"></i> Movimentação</a></li>
                    <li><a href="{{ route('produtos.duplicar', $p->id) }}" class="flex items-center text-gray-700 px-4 py-2 text-sm hover:bg-gray-100"><i class="bx bx-copy mr-2"></i> Duplicar</a></li>
                </div>
                <div class="py-1">
                    <li><a href="{{ route('produtos.etiqueta', $p->id) }}" class="flex items-center text-gray-700 px-4 py-2 text-sm hover:bg-gray-100"><i class="bx bx-barcode mr-2"></i> Cód. de Barras</a></li>
                    <li><a href="javascript:void(0)" onclick="abrirEtiquetaModal({{ $p->id }})" class="flex items-center text-gray-700 px-4 py-2 text-sm hover:bg-gray-100"><i class="bx bx-label mr-2"></i> Etiqueta Personalizada</a></li>
                </div>
            </form>
        </ul>
    </div>
</td>
                        <td class="py-3 px-6 font-semibold text-gray-800">{{$p->nome}} <span class="text-xs text-gray-500">{{$p->str_grade}}</span></td>
                        
                            <td class="py-3 px-6">{{ $p->referencia }}</td>

                        <td class="py-3 px-6 text-xl text-green-600 font-extrabold whitespace-nowrap">{{ __moeda($p->valor_venda) }}</td>
                        <td class="py-3 px-6">{{ $p->unidade_compra }}/{{ $p->unidade_venda }}</td>
                        <td class="py-3 px-6 text-gray-500 whitespace-nowrap">{{ __data_pt($p->created_at) }}</td>
                         <td class="py-3 px-6">{{$p->codBarras}} </td>

                        <td class="py-3 px-6">
                            <span class="px-3 py-1 text-xs font-bold rounded-full {{ $p->gerenciar_estoque ? 'bg-green-100 text-green-700':'bg-red-100 text-red-700' }}">
                                {{ $p->gerenciar_estoque ? 'Sim':'Não' }}
                            </span>
                        </td>
                        @if(empresaComFilial())
                        <td class="py-3 px-6 text-xs text-gray-600 max-w-[200px] overflow-hidden text-ellipsis">{!! $p->locais_produto() !!}</td>
                        @endif
                        <td class="py-3 px-6 text-lg font-extrabold text-indigo-700">{{ $p->estoquePorLocal($filial_id ?? $p->locais) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="py-8 px-6 text-center text-lg text-gray-500"><i class="bx bx-info-circle text-2xl mr-2"></i> Nenhum produto encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>

{{-- Modal Etiqueta --}}
<div class="modal fade" id="etiquetaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-header">
                <h5 class="modal-title">Etiqueta Personalizada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="etiquetaConteudo">Carregando etiqueta...</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btnImprimirEtiqueta"><i class="bx bx-printer"></i> Imprimir</button>
            </div>
        </div>
    </div>
</div>
<style>/* CSS para o corpo da página e impressão */
body {
    margin: 0;
    padding: 0;
    font-family: Arial, Helvetica, sans-serif;
    color: #000;
}

/* Container principal da etiqueta (dimensões da sua etiqueta de exemplo) */
.etiqueta-container {
    width: 210px; /* Largura da etiqueta */
    padding: 8px 10px; /* Padding interno para o conteúdo */
    border: 1px solid #000; /* Borda visível, útil para debug ou corte */
    background: #fff;
    text-align: center;
    line-height: 1.1;
    margin: 10px auto; /* Centraliza e dá um pequeno respiro */
    box-sizing: border-box; /* Garante que padding e border estão dentro do width */
}

/* Melhorias Visuais nos Componentes */

.etiqueta-logo {
    max-width: 55px;
    max-height: 30px;
    margin-bottom: 5px;
    display: block; /* Garante que fique em uma linha própria */
    margin-left: auto;
    margin-right: auto;
}

.etiqueta-produto {
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
    margin-bottom: 4px;
    white-space: normal; /* Permite quebras de linha longas */
    word-wrap: break-word; /* Força quebra de palavras longas */
    overflow: hidden; /* Oculta se o texto for muito grande */
    max-height: 2.2em; /* Limita a 2 linhas (ou ajuste conforme necessário) */
}

.etiqueta-preco {
    font-size: 14px; /* Aumentado ligeiramente para destaque */
    font-weight: 900; /* Mais negrito */
    margin-bottom: 6px;
    padding: 1px 0; /* Pequeno espaçamento vertical para destacar */
}

.etiqueta-barcode {
    margin: 3px 0;
}

.etiqueta-barcode-img {
    height: 35px;
    margin-bottom: 1px; /* Diminuído para aproximar do código */
    max-width: 100%; /* Garante que não ultrapasse a largura do container */
}

.etiqueta-barcode-text {
    font-size: 9px;
}

.etiqueta-rodape {
    border-top: 1px dashed #000;
    margin-top: 5px; /* Aumentado ligeiramente o espaço do topo */
    padding-top: 3px;
    font-size: 8px; /* Diminuído para otimizar espaço */
    text-transform: uppercase;
    line-height: 1.2;
}

/* Otimização para Impressão */
@media print {
    .etiqueta-container {
        border: none !important; /* Remove a borda se não for necessária na impressão final */
        margin: 0 !important; /* Remove a margem para alinhamento preciso na folha */
        page-break-inside: avoid; /* Evita que a etiqueta seja quebrada entre páginas */
    }
}
</style></style>>
<script>
function abrirEtiquetaModal(id) {
    // Mostra o modal e o loading
    const modal = new bootstrap.Modal(document.getElementById('etiquetaModal'));
    document.getElementById('etiquetaConteudo').innerHTML = '<p>Carregando etiqueta...</p>';
    modal.show();

    // Faz o request via AJAX
    fetch(`/produtos/etiqueta-personalizada/${id}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('etiquetaConteudo').innerHTML = html;

            // Configura o botão de impressão
            document.getElementById('btnImprimirEtiqueta').onclick = () => {
                imprimirEtiqueta();
            };
        })
        .catch(() => {
            document.getElementById('etiquetaConteudo').innerHTML = '<p>Erro ao carregar etiqueta.</p>';
        });
}
/*
function imprimirEtiqueta() {
    const conteudoEtiqueta = document.getElementById('etiquetaConteudo').innerHTML;

    // 1️⃣ — 18 etiquetas por folha (3 colunas × 6 linhas)
    let htmlEtiquetasA4 = '';
    const QTD_ETIQUETAS_POR_FOLHA = 18;
    for (let i = 0; i < QTD_ETIQUETAS_POR_FOLHA; i++) {
        htmlEtiquetasA4 += `<div class="etiqueta-container">${conteudoEtiqueta}</div>`;
    }

    // 2️⃣ — CSS preciso para etiqueta 63x46mm (Ca4361)
    const cssCompleto = `
        <style>
            @page {
                size: A4 portrait;
                margin: 0;
            }

            body {
                margin: 0;
                padding: 0;
                width: 210mm;
                height: 297mm;
                font-family: Arial, sans-serif;
                -webkit-print-color-adjust: exact;
            }

            .folha-a4 {
                width: 210mm;
                height: 297mm;
                display: flex;
                flex-wrap: wrap;
                box-sizing: border-box;
                padding-top: 10mm;   /* Margem superior */
                padding-left: 5mm;   /* Margem esquerda */
            }

            .etiqueta-container {
                width: 63mm;
                height: 46mm;
                margin-right: 2.5mm;  /* Espaçamento horizontal */
                margin-bottom: 0mm;   /* Espaçamento vertical */
                padding: 2mm;
                box-sizing: border-box;
                text-align: center;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                border: none;
            }

            /* === Estilo interno da etiqueta === */
            .etiqueta-logo {
                max-width: 18mm;
                max-height: 12mm;
                object-fit: contain;
                margin-bottom: 2mm;
            }

            .etiqueta-produto {
                font-size: 9pt;
                font-weight: bold;
                text-transform: uppercase;
                margin-bottom: 1mm;
            }

            .etiqueta-preco {
                font-size: 12pt;
                font-weight: 900;
                margin-bottom: 2mm;
            }

            .etiqueta-barcode-img {
                height: 10mm;
                margin-bottom: 1mm;
            }

            .etiqueta-barcode-text {
                font-size: 7pt;
            }

            .etiqueta-rodape {
                border-top: 1px dashed #000;
                margin-top: 1mm;
                padding-top: 1mm;
                font-size: 6pt;
            }
        </style>
    `;

    // 3️⃣ — Abre nova janela e imprime
    const janela = window.open('', '_blank', 'width=800,height=600');
    janela.document.write(`
        <html>
        <head>
            <title>Impressão de Etiquetas - 63x46mm (Ca4361)</title>
            ${cssCompleto}
        </head>
        <body>
            <div class="folha-a4">
                ${htmlEtiquetasA4}
            </div>
        </body>
        </html>
    `);
    janela.document.close();

    janela.onload = () => janela.print();
}
*/

function imprimirEtiqueta() {
    const conteudoEtiqueta = document.getElementById('etiquetaConteudo').innerHTML;

    const htmlEtiquetasTermicas = `
        <div class="linha-etiqueta">
            <div class="etiqueta-item">${conteudoEtiqueta}</div>
            <div class="gap"></div>
            <div class="etiqueta-item">${conteudoEtiqueta}</div>
        </div>
    `;

    const cssCompleto = `
        <style>
            @page {
                size: 83mm 25mm;
                margin: 0 !important;
            }

            html, body {
                margin: 0 !important;
                padding: 0 !important;
                width: 83mm;
                height: 25mm;
                overflow: hidden;
                background: #fff;
            }

            .linha-etiqueta {
                width: 83mm;
                height: 25mm;
                display: flex;
                flex-direction: row;
                box-sizing: border-box;
            }

            .etiqueta-item {
                width: 40mm;
                height: 25mm;
                position: relative;
                box-sizing: border-box;
                padding: 0.5mm 1mm;
                overflow: hidden;
            }

            .gap { width: 3mm; height: 25mm; }

            /* RESET DE ESTILOS DA VIEW */
            .etiqueta-container, .etiqueta-container * {
                margin: 0 !important;
                padding: 0 !important;
                line-height: 1 !important;
                text-align: center;
            }

            .etiqueta-logo {
                max-height: 4mm !important;
                margin-top: 1mm !important;
                margin-bottom: 0.5mm !important;
                display: block;
                margin-left: auto;
                margin-right: auto;
            }

            .etiqueta-produto {
                font-size: 6.5pt !important;
                font-weight: bold;
                max-height: 6.5mm;
                overflow: hidden;
                display: block;
                margin-bottom: 0.5mm !important;
            }

            .etiqueta-price-box {
                background-color: #000 !important;
                color: #fff !important;
                padding: 0.5mm 0 !important;
                margin: 0.5mm 0 !important;
                -webkit-print-color-adjust: exact;
            }

            .etiqueta-preco { font-size: 11.5pt !important; font-weight: 900; }

            /* TRAVA O BARCODE NO FINAL DA ETIQUETA PARA NÃO CORTAR */
            .etiqueta-barcode {
                position: absolute;
                bottom: 0.8mm;
                left: 0;
                width: 100%;
            }

            .etiqueta-barcode-img {
                height: 4.2mm !important;
                width: 90% !important;
                margin: 0 auto !important;
                display: block;
            }

            .etiqueta-barcode-text {
                font-size: 5pt !important;
                margin-top: 0.1mm !important;
                display: block;
            }

            /* REMOVE O QUE SOBRA */
            .etiqueta-rodape, .etiqueta-price-box div:first-child { display: none !important; }
        </style>
    `;

    const janela = window.open('', '_blank', 'width=900,height=500');
    janela.document.write(`<html><head>${cssCompleto}</head><body>${htmlEtiquetasTermicas}</body></html>`);
    janela.document.close();

    setTimeout(() => {
        janela.print();
    }, 1000);
}

</script>

@endsection
