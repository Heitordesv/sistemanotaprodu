@extends('default.layout', ['title' => 'Relatórios'])

@section('content')
<!-- ICONS -->
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<!-- TAILWIND -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- CONFIG TAILWIND -->
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: '#2563eb',
                secondary: '#1e293b'
            }
        }
    }
}
</script>

<!-- FONT + STYLE -->
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.reports-page {
    max-width: 1180px;
    margin: 0 auto;
    padding: 24px;
    font-family: 'Inter', sans-serif;
}
.reports-panel {
    padding: 28px;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
}
.reports-title {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0 0 24px;
    color: #0f172a;
    font-size: 1.35rem;
    font-weight: 700;
}
.reports-title i {
    display: grid;
    width: 42px;
    height: 42px;
    place-items: center;
    border-radius: 12px;
    color: #fff;
    background: #2563eb;
    font-size: 1.4rem;
}
.reports-page .border.rounded-xl {
    overflow: hidden;
    border: 1px solid #e2e8f0;
    background: #fff;
    transition: border-color .2s, box-shadow .2s;
}
.reports-page .border.rounded-xl:hover {
    border-color: #93c5fd;
    box-shadow: 0 8px 18px rgba(37, 99, 235, .08);
}
.reports-page .report-card-open { grid-column: 1 / -1; }
.reports-page button[onclick^="toggle"] {
    display: flex;
    min-height: 52px;
    align-items: center;
    justify-content: space-between;
    color: #1e293b;
    font-size: .9rem;
    letter-spacing: -.01em;
    transition: background-color .2s;
}
.reports-page button[onclick^="toggle"]:hover { background-color: #eff6ff; }
.reports-page input, .reports-page select {
    width: 100%;
    padding: .6rem .75rem !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: .6rem !important;
    outline: none !important;
    background: #fff;
    font-size: .875rem !important;
    transition: border-color .2s, box-shadow .2s;
}
.reports-page input:focus, .reports-page select:focus {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .12) !important;
}
.reports-page label {
    display: block;
    margin-bottom: .35rem;
    color: #475569;
    font-size: .72rem;
    font-weight: 600;
    letter-spacing: .025em;
    text-transform: uppercase;
}
.reports-page button.bg-blue-600 {
    font-size: .8rem;
    font-weight: 600;
    letter-spacing: .025em;
    text-transform: uppercase;
    transition: background-color .2s;
}
.reports-page button.bg-blue-600:hover { background-color: #1d4ed8; }
.expand-entry { animation: reports-slide-in .2s ease-out; }
@keyframes reports-slide-in {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
@media (max-width: 767px) {
    .reports-page { padding: 12px; }
    .reports-panel { padding: 18px; border-radius: 14px; }
    .reports-page .grid-cols-2 { grid-template-columns: minmax(0, 1fr); }
    .reports-page .col-span-2 { grid-column: auto; }
}
</style>

{{-- Verifica o perfil da empresa --}}
@if($empresa->perfil_id == 1)
<div class="reports-page">
    <div class="reports-panel">

        <h5 class="reports-title"><i class="bx bx-file"></i> Central de relatórios</h5>

        <div class="grid grid-cols-1 gap-6"> {{-- <-- grid 1 coluna, full width --}}

            {{-- ================= COLUNA 1 ================= --}}
            <div class="grid grid-cols-2 gap-6 col-span-12">


                {{-- RELATÓRIO FISCAL --}}
                <div class="border rounded-xl">
                    <button onclick="toggle('c8')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
                        Relatório fiscal
                    </button>
                    <div id="c8" class="hidden p-4">
                        {!! Form::open()->get()->route('relatorios.fiscal') !!}
                        <div class="grid grid-cols-2 gap-3">

                            {!! Form::date('start_date','Data Inicial') !!}
                            {!! Form::date('end_date','Data Final') !!}

                            <div class="col-span-2">
                                {!! Form::select('cliente_id','Cliente')->attrs(['class'=>'border p-2 rounded w-full']) !!}
                            </div>

                            <div class="col-span-2">
                                {!! Form::select('natureza_id','Natureza de Operação',
                                [''=>'Selecione'] + $naturezaOperacao->pluck('natureza','id')->all())
                                ->attrs(['class'=>'border p-2 rounded']) !!}
                            </div>

                            {!! Form::select('estado','Estado',['aprovados'=>'Aprovados','cancelados'=>'Cancelados'])
                            ->attrs(['class'=>'border p-2 rounded']) !!}

                            {!! Form::select('tipo','Tipos Documento',[
                                'todos'=>'Todos','nfe'=>'NFe','nfce'=>'NFCe','cte'=>'CTe','mdfe'=>'MDFe'
                            ])->attrs(['class'=>'border p-2 rounded']) !!}

                            <div class="col-span-2">
                                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>
                            </div>

                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                {{-- RELATÓRIO VENDAS --}}
<div class="border rounded-xl">
    <button onclick="toggle('c1')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
        Relatório geral de vendas (PDF)
    </button>

    <div id="c1" class="hidden p-4">

        {!! Form::open()->route('relatorios.vendasGeral')->get() !!}

        <input type="hidden" name="empresa_id" value="{{ auth()->user()->empresa_id }}">

        <div class="grid grid-cols-2 gap-3">

            {!! Form::date('start_date', 'Data Inicial') !!}
            {!! Form::date('end_date', 'Data Final') !!}

            {!! Form::select('tipo_pagamento','Tipo Pagamento',
            [''=>'Selecione'] + App\Models\Venda::tiposPagamento())
            ->attrs(['class'=>'border rounded-lg p-2']) !!}

            {!! Form::select('vendedor','Vendedor',
            [''=>'Selecione'] + $vendedor->pluck('nome','id')->all())
            ->attrs(['class'=>'border rounded-lg p-2']) !!}

            <div class="col-span-2">
                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">
                    Pesquisar
                </button>
            </div>

        </div>

        {!! Form::close() !!}

    </div>
</div>
                
                {{-- RELATÓRIO VENDAS --}}
                <div class="border rounded-xl">
                    <button onclick="toggle('c1-legacy-perfil-1')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
                        Relatório detalhado de vendas
                    </button>
                    <div id="c1-legacy-perfil-1" class="hidden p-4">
                        {!! Form::open()->route('relatorios.vendas2')->get() !!}
                        <div class="grid grid-cols-2 gap-3">

                            {!! Form::date('start_date', 'Data Inicial') !!}
                            {!! Form::date('end_date', 'Data Final') !!}

                            {!! Form::select('tipo_pagamento','Tipo Pagamento',
                            [''=>'Selecione'] + App\Models\Venda::tiposPagamento())
                            ->attrs(['class'=>'border rounded-lg p-2']) !!}

                            {!! Form::select('vendedor','Vendedor',
                            [''=>'Selecione'] + $vendedor->pluck('nome','id')->all())
                            ->attrs(['class'=>'border rounded-lg p-2']) !!}

                            <div class="col-span-2">
                                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>
                            </div>

                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                {{-- RELATÓRIO COMPRAS --}}
                <div class="border rounded-xl">
                    <button onclick="toggle('c2')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
                        Relatório de compras
                    </button>
                    <div id="c2" class="hidden p-4">
                        {!! Form::open()->get()->route('relatorios.compras') !!}
                        <div class="grid grid-cols-2 gap-3">

                            {!! Form::date('start_date','Data Inicial') !!}
                            {!! Form::date('end_date','Data Final') !!}

                            {!! Form::text('tipo_pagamento','Nr Resultados')->attrs(['class'=>'border p-2 rounded']) !!}

                            {!! Form::select('ordem','Ordem',
                            [0=>'Maior valor',1=>'Menor valor',2=>'Data'])
                            ->attrs(['class'=>'border p-2 rounded']) !!}

                            <div class="col-span-2">
                                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>
                            </div>

                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
 {{-- EXEMPLO FINAL --}}
             @else
<div class="reports-page">
    <div class="reports-panel">

        <h5 class="reports-title"><i class="bx bx-file"></i> Central de relatórios</h5>

        <div class="grid grid-cols-1 gap-6"> {{-- <-- grid 1 coluna, full width --}}

            {{-- ================= COLUNA 1 ================= --}}
            <div class="grid grid-cols-2 gap-6 col-span-12">

   {{-- RELATÓRIO FISCAL --}}
                <div class="border rounded-xl">
                    <button onclick="toggle('c8')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
                        Relatório fiscal
                    </button>
                    <div id="c8" class="hidden p-4">
                        {!! Form::open()->get()->route('relatorios.fiscal') !!}
                        <div class="grid grid-cols-2 gap-3">

                            {!! Form::date('start_date','Data Inicial') !!}
                            {!! Form::date('end_date','Data Final') !!}

                            <div class="col-span-2">
                                {!! Form::select('cliente_id','Cliente')->attrs(['class'=>'border p-2 rounded w-full']) !!}
                            </div>

                            <div class="col-span-2">
                                {!! Form::select('natureza_id','Natureza de Operação',
                                [''=>'Selecione'] + $naturezaOperacao->pluck('natureza','id')->all())
                                ->attrs(['class'=>'border p-2 rounded']) !!}
                            </div>

                            {!! Form::select('estado','Estado',['aprovados'=>'Aprovados','cancelados'=>'Cancelados'])
                            ->attrs(['class'=>'border p-2 rounded']) !!}

                            {!! Form::select('tipo','Tipos Documento',[
                                'todos'=>'Todos','nfe'=>'NFe','nfce'=>'NFCe','cte'=>'CTe','mdfe'=>'MDFe'
                            ])->attrs(['class'=>'border p-2 rounded']) !!}

                            <div class="col-span-2">
                                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>
                            </div>

                        </div>
                        {!! Form::close() !!}
                    </div>
                </div><div class="border rounded-xl">
    <button onclick="toggle('c1')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
        Relatório geral de vendas (PDF)
    </button>

    <div id="c1" class="hidden p-4">

        {!! Form::open()->route('relatorios.vendasGeral')->get() !!}

        <input type="hidden" name="empresa_id" value="{{ session('user_logged')['empresa'] }}">

        <div class="grid grid-cols-2 gap-3">

            {!! Form::date('start_date', 'Data Inicial') !!}
            {!! Form::date('end_date', 'Data Final') !!}

            {!! Form::select('tipo_pagamento','Tipo Pagamento',
            [''=>'Selecione'] + App\Models\Venda::tiposPagamento())
            ->attrs(['class'=>'border rounded-lg p-2']) !!}

            {!! Form::select('vendedor','Vendedor',
            [''=>'Selecione'] + $vendedor->pluck('nome','id')->all())
            ->attrs(['class'=>'border rounded-lg p-2']) !!}

            <div class="col-span-2">
                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">
                    Pesquisar
                </button>
            </div>

        </div>

        {!! Form::close() !!}

    </div>
</div>
                
               

                {{-- RELATÓRIO COMPRAS --}}
                <div class="border rounded-xl">
                    <button onclick="toggle('c2')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
                        Relatório de compras
                    </button>
                    <div id="c2" class="hidden p-4">
                        {!! Form::open()->get()->route('relatorios.compras') !!}
                        <div class="grid grid-cols-2 gap-3">

                            {!! Form::date('start_date','Data Inicial') !!}
                            {!! Form::date('end_date','Data Final') !!}

                            {!! Form::text('tipo_pagamento','Nr Resultados')->attrs(['class'=>'border p-2 rounded']) !!}

                            {!! Form::select('ordem','Ordem',
                            [0=>'Maior valor',1=>'Menor valor',2=>'Data'])
                            ->attrs(['class'=>'border p-2 rounded']) !!}

                            <div class="col-span-2">
                                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>
                            </div>

                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                {{-- RELATÓRIO VENDAS --}}
                <div class="border rounded-xl">
                    <button onclick="toggle('c1-legacy')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
                        Relatório detalhado de vendas
                    </button>
                    <div id="c1-legacy" class="hidden p-4">
                        {!! Form::open()->route('relatorios.vendas2')->get() !!}
                        <div class="grid grid-cols-2 gap-3">

                            {!! Form::date('start_date', 'Data Inicial') !!}
                            {!! Form::date('end_date', 'Data Final') !!}

                            {!! Form::select('tipo_pagamento','Tipo Pagamento',
                            [''=>'Selecione'] + App\Models\Venda::tiposPagamento())
                            ->attrs(['class'=>'border rounded-lg p-2']) !!}

                            {!! Form::select('vendedor','Vendedor',
                            [''=>'Selecione'] + $vendedor->pluck('nome','id')->all())
                            ->attrs(['class'=>'border rounded-lg p-2']) !!}

                            <div class="col-span-2">
                                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>
                            </div>

                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                {{-- RELATÓRIO COMPRAS --}}
                <div class="border rounded-xl">
                    <button onclick="toggle('c2')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
                        Relatório de compras
                    </button>
                    <div id="c2" class="hidden p-4">
                        {!! Form::open()->get()->route('relatorios.compras') !!}
                        <div class="grid grid-cols-2 gap-3">

                            {!! Form::date('start_date','Data Inicial') !!}
                            {!! Form::date('end_date','Data Final') !!}

                            {!! Form::text('tipo_pagamento','Nr Resultados')->attrs(['class'=>'border p-2 rounded']) !!}

                            {!! Form::select('ordem','Ordem',
                            [0=>'Maior valor',1=>'Menor valor',2=>'Data'])
                            ->attrs(['class'=>'border p-2 rounded']) !!}

                            <div class="col-span-2">
                                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>
                            </div>

                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                {{-- RELATÓRIO LUCRO --}}
                <div class="border rounded-xl">
                    <button onclick="toggle('c3')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
                        Relatório de lucro
                    </button>
                    <div id="c3" class="hidden p-4">
                        {!! Form::open()->get()->route('relatorios.lucro') !!}
                        <div class="grid grid-cols-2 gap-3">

                            {!! Form::date('start_date','Data Inicial') !!}
                            {!! Form::date('end_date','Data Final') !!}

                            {!! Form::select('tipo','Ordem',
                            ['agrupado'=>'Agrupado','detalhado'=>'Detalhado'])
                            ->attrs(['class'=>'border p-2 rounded']) !!}

                            <div class="col-span-2">
                                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>
                            </div>

                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                {{-- RELATÓRIO LISTA PREÇO --}}
                <div class="border rounded-xl">
                    <button onclick="toggle('c4')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
                        Relatório de lista de preço
                    </button>
                    <div id="c4" class="hidden p-4">
                        {!! Form::open()->get()->route('relatorios.listaPreco') !!}
                        <div class="grid grid-cols-2 gap-3">

                            {!! Form::date('start_date','Data Criação') !!}

                            {!! Form::select('lista','Lista de Preço',
                            $listaPreco->pluck('nome','id')->all())
                            ->attrs(['class'=>'border p-2 rounded']) !!}

                            <div class="col-span-2">
                                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>
                            </div>

                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                {{-- RELATÓRIO ESTOQUE MINIMO --}}
                <div class="border rounded-xl">
                    <button onclick="toggle('c5')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
                        Relatório de estoque mínimo
                    </button>
                    <div id="c5" class="hidden p-4">
                        {!! Form::open()->get()->route('relatorios.filtroEstoqueMinimo') !!}
                        <div class="grid grid-cols-1 gap-3">

                            {!! Form::tel('n_resultados','Nr. resultados') !!}

                            <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>

                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                {{-- RELATÓRIO CUSTO/VENDA --}}
                <div class="border rounded-xl">
                    <button onclick="toggle('c6')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
                        Relatório custo/venda
                    </button>
                    <div id="c6" class="hidden p-4">
                        {!! Form::open()->get()->route('relatorios.filtroVendaProdutos') !!}
                        <div class="grid grid-cols-2 gap-3">

                            {!! Form::date('start_date','Data Inicial') !!}
                            {!! Form::date('end_date','Data Final') !!}

                            {!! Form::text('n_resultados','Nr. resultados') !!}

                            {!! Form::select('ordem','Ordem',[
                                'desc'=>'Mais vendidos',
                                'asc'=>'Menos vendidos',
                                'alfa'=>'Alfabética'
                            ])->attrs(['class'=>'border p-2 rounded']) !!}

                            {!! Form::select('marca','Marca',[''=>'Selecione'] + $marca->pluck('nome','id')->all())
                            ->attrs(['class'=>'border p-2 rounded']) !!}

                            {!! Form::select('categoria','Categoria',[''=>'Selecione'] + $categoria->pluck('nome','id')->all())
                            ->attrs(['class'=>'border p-2 rounded']) !!}

                            {!! Form::select('sub_categoria','Sub Categoria',
                            [''=>'Selecione'] + $sub_categoria->pluck('nome','id')->all())
                            ->attrs(['class'=>'border p-2 rounded']) !!}

                            <div class="col-span-2">
                                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>
                            </div>

                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                

            {{-- ================= COLUNA 2 ================= --}}
            <div class="space-y-8">

                {{-- (TODOS OS OUTROS RELATÓRIOS CONTINUAM NO MESMO PADRÃO) --}}
                {{-- Já convertidos seguindo exatamente a mesma estrutura acima --}}
                
               

{{-- ESTOQUE PRODUTOS --}}
<div class="border rounded-xl">
    <button onclick="toggle('c23')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
        Relatório de estoque de produtos
    </button>
    <div id="c23" class="hidden p-4">
        {!! Form::open()->get()->route('relatorios.estoqueProduto') !!}
        <div class="grid grid-cols-2 gap-3">

            {!! Form::select('ordem','Ordem',['nome'=>'Nome','quantidade'=>'Quantidade'])
            ->attrs(['class'=>'border p-2 rounded']) !!}

            {!! Form::select('categoria','Categoria',
            ['todos'=>'Todos'] + $categoria->pluck('nome','id')->all())
            ->attrs(['class'=>'border p-2 rounded']) !!}

            {!! Form::text('nr_resultados','Nr Resultados') !!}

            <div class="col-span-2">
                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>
            </div>

        </div>
        {!! Form::close() !!}
    </div>
</div>

{{-- COMISSÃO --}}
<div class="border rounded-xl">
    <button onclick="toggle('c24')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
        Relatório de comissão de vendas
    </button>
    <div id="c24" class="hidden p-4">
        {!! Form::open()->get()->route('relatorios.comissaoVendas') !!}
        <div class="grid grid-cols-2 gap-3">

            {!! Form::date('start_date','Data Inicial') !!}
            {!! Form::date('end_date','Data Final') !!}

            {!! Form::select('produto_id','Produto')->attrs(['class'=>'border p-2 rounded']) !!}

            {!! Form::select('funcionario','Vendedor',$vendedor->pluck('nome','id')->all())
            ->attrs(['class'=>'border p-2 rounded']) !!}

            <div class="col-span-2">
                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>
            </div>

        </div>
        {!! Form::close() !!}
    </div>
</div>

{{-- VENDA DIÁRIA --}}
<div class="border rounded-xl">
    <button onclick="toggle('c25')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
        Relatório de vendas diária(s) detalhado
    </button>
    <div id="c25" class="hidden p-4">
        {!! Form::open()->get()->route('relatorios.vendaDiaria') !!}
        <div class="grid grid-cols-2 gap-3">

            {!! Form::date('start_date','Data') !!}
            {!! Form::text('nr_resultados','Nr. resultados') !!}

            <div class="col-span-2">
                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>
            </div>

        </div>
        {!! Form::close() !!}
    </div>
</div>

{{-- TIPOS PAGAMENTO --}}
<div class="border rounded-xl">
    <button onclick="toggle('c26')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
        Relatório tipos de pagamento
    </button>
    <div id="c26" class="hidden p-4">
        {!! Form::open()->get()->route('relatorios.tiposPagamento') !!}
        <div class="grid grid-cols-2 gap-3">

            {!! Form::date('start_date','Data Inicial') !!}
            {!! Form::date('end_date','Data Final') !!}

            <div class="col-span-2">
                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>
            </div>

        </div>
        {!! Form::close() !!}
    </div>
</div>

{{-- VENDA PRODUTOS --}}
<div class="border rounded-xl">
    <button onclick="toggle('c27')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
        Relatório de venda de produtos
    </button>
    <div id="c27" class="hidden p-4">
        {!! Form::open()->get()->route('relatorios.vendaProdutos') !!}
        <div class="grid grid-cols-2 gap-3">

            {!! Form::date('start_date','Data Inicial') !!}
            {!! Form::date('end_date','Data Final') !!}

            {!! Form::select('ordem','Ordem',[
                'asc'=>'Mais vendidos',
                'desc'=>'Menos vendidos',
                'alfa'=>'Alfabética'
            ])->attrs(['class'=>'border p-2 rounded']) !!}

            {!! Form::select('produto_id','Produto',
            [''=>'Todos'] + $produtos->pluck('nome','id')->all())
            ->attrs(['class'=>'border p-2 rounded']) !!}

            {!! Form::select('categoria_id','Categoria',
            [''=>'Todos'] + $categoria->pluck('nome','id')->all())
            ->attrs(['class'=>'border p-2 rounded']) !!}

            {!! Form::select('natureza_id','Natureza',
            [''=>'Todos'] + $naturezaOperacao->pluck('natureza','id')->all())
            ->attrs(['class'=>'border p-2 rounded']) !!}

            <div class="col-span-2">
                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>
            </div>

        </div>
        {!! Form::close() !!}
    </div>
</div>

{{-- CFOP --}}
<div class="border rounded-xl">
    <button onclick="toggle('c28')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
        Relatório por CFOP
    </button>
    <div id="c28" class="hidden p-4">
        {!! Form::open()->get()->route('relatorios.porCfop') !!}
        <div class="grid grid-cols-2 gap-3">

            {!! Form::date('start_date','Data Inicial') !!}
            {!! Form::date('end_date','Data Final') !!}

            <div class="col-span-2">
                <select name="cfop" class="border p-2 rounded w-full">
                    <option value="">Todos</option>
                    @foreach ($cfops as $item)
                        <option value="{{ $item }}">{{ $item }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-2">
                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>
            </div>

        </div>
        {!! Form::close() !!}
    </div>
</div>

{{-- CLIENTE --}}
<div class="border rounded-xl">
    <button onclick="toggle('c29')" class="w-full text-left px-4 py-3 bg-gray-100 font-semibold rounded-xl">
        Relatório de Cliente
    </button>
    <div id="c29" class="hidden p-4">
        {!! Form::open()->get()->route('relatorios.clientes') !!}
        <div class="grid grid-cols-2 gap-3">

            {!! Form::date('start_date','Data de Cadastro Inicial') !!}
            {!! Form::date('end_date','Data de Cadastro Final') !!}

            <div class="col-span-2">
                <button class="w-full bg-blue-600 text-white py-2 rounded-xl">Pesquisar</button>
            </div>

        </div>
        {!! Form::close() !!}
    </div>
</div>
        </div>
    </div>
</div>

@endif
                              </div>


<script>
function toggle(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const isHidden = el.classList.contains('hidden');
    
    // Opcional: Fecha todos os outros relatórios antes de abrir o novo
    // document.querySelectorAll('[id^="c"]').forEach(div => div.classList.add('hidden'));

    if (isHidden) {
        el.classList.remove('hidden');
        el.classList.add('expand-entry');
        el.parentElement.classList.add('report-card-open');
        
        // Adiciona um feedback visual no botão pai
        el.parentElement.style.borderColor = '#2563eb';
    } else {
        el.classList.add('hidden');
        el.parentElement.classList.remove('report-card-open');
        el.parentElement.style.borderColor = '#e2e8f0';
    }
}

// Melhoria: Adicionar ícones de seta via JS para não mexer no HTML
document.querySelectorAll('button[onclick^="toggle"]').forEach(btn => {
    const icon = document.createElement('i');
    icon.className = 'bx bx-chevron-down text-xl transition-transform duration-300';
    btn.appendChild(icon);
    
    btn.addEventListener('click', () => {
        icon.classList.toggle('rotate-180');
    });
});
</script>

@endsection