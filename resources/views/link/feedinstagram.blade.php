<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
    <channel>
        <title>{{ $link->nome_fantasia ?? $link->razao_social ?? 'Catálogo de Produtos' }}</title>
        <link>{{ url('/catalogo-links/' . $link->nome_link) }}</link>
        <description>Catálogo de produtos de {{ $link->nome_fantasia ?? $link->razao_social ?? 'nossa loja' }}.</description>

        @foreach($produtos as $produto)
        <item>
            <g:id>{{ $produto->id }}</g:id>
            <title>{{ $produto->nome }}</title>
            <g:description>{{ $produto->descricao }}</g:description>
            <link>{{ url('/catalogo-links/' . $link->nome_link) }}</link>
            <g:image_link>{{ asset($produto->img) }}</g:image_link>
            <g:availability>{{ $produto->inativo == 0 ? 'in stock' : 'out of stock' }}</g:availability>
            <g:price>{{ number_format($produto->valor_venda, 2, '.', '') }} BRL</g:price>
            <g:condition>new</g:condition>
            <g:brand>{{ $produto->marca ?? 'Sua Marca' }}</g:brand>
            <g:google_product_category>Vestuário e acessórios > Roupas</g:google_product_category>
        </item>
        @endforeach
    </channel>
</rss>