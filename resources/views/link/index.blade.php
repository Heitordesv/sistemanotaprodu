<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Links e Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
@forelse ($links as $link)
    <style>
        /* Variáveis CSS para Cores Dinâmicas (Melhor Prática) */
        /* Isso facilita a reutilização das cores do banco de dados em vários lugares */
        :root {
            --primary-bg-color: {{ $link->background_color ?? '#f8f9fa' }}; /* Cor de fundo principal, com fallback */
            --primary-text-color: {{ $link->text_color ?? '#212529' }}; /* Cor do texto principal, com fallback */

            --secondary-texts-color:{{ $link->text_color ?? '#f8f9fa' }} {{ $link->text_color ?? '#212529' }}; /* Ex: para texto em containers escuros */
            --secondary-bg-color: {{ $link->background_color ?? '#f8f9fa' }}; /* Ex: para containers */
            --secondary-text-color:{{ $link->text_color ?? '#f8f9fa' }} {{ $link->text_color ?? '#212529' }}; /* Ex: para texto em containers escuros */
            --accent-color: {{ $link->text_color ?? '#212529' }}; /* Ex: para botões, links em destaque (azul padrão do Bootstrap) */
            --hover-color: {{ $link->text_color ?? '#f8f9fa' }}; /* Cor de hover para botões/links, escurece o accent-color */
            --border-color:  {{ $link->text_color ?? '#212529' }};
            --shadow-color: rgba(0, 0, 0, 0.1);
        }

body {
    font-family: 'Poppins', sans-serif;
    background-color: var(--primary-bg-color);
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    color: var(--primary-text-color);
    line-height: 1.6; /* Improved readability */
}

.container {
    margin-top: 40px; /* Increased margin for more breathing room */
    margin-bottom: 40px;
    background-color: var(--secondary-texts-color);
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 6px 15px var(--shadow-color); /* Slightly more prominent shadow */
    flex: 1;
    color: var(--secondary-text-color);
}

h1, h2 {
    color: var(--primary-text-color);
    text-align: center;
    margin-bottom: 40px; /* Increased margin for stronger visual separation */
    font-weight: 700; /* Bolder titles */
    letter-spacing: 0.5px; /* Slight letter spacing for elegance */
    text-transform: uppercase; /* Uppercase for titles for a fashion feel */
}

/* --- HEADER (Navbar) Styles --- */
.navbar {
    background-color: var(--secondary-bg-color) !important;
    box-shadow: 0 2px 10px var(--shadow-color);
    padding: 15px 0; /* More padding for a premium feel */
}

.navbar-brand {
    font-weight: 800; /* Even bolder brand name */
    font-size: 1.8rem; /* Larger brand font size */
    color: var(--primary-text-color) !important; /* Brand name stands out */
    display: flex;
    align-items: center;
    text-transform: uppercase; /* Uppercase for brand */
    letter-spacing: 1px;
}

.navbar-brand img {
    border-radius: 50%;
    margin-right: 12px;
    border: 3px solid var(--accent-color); /* Accent color border for logo */
    width: 45px; /* Slightly larger logo */
    height: 45px;
    object-fit: cover;
}

.navbar-nav .nav-link {
    color: var(--secondary-text-color) !important;
    font-weight: 600;
    margin-right: 20px; /* More spacing between links */
    transition: all 0.3s ease; /* Smoother transition */
    padding: 10px 18px; /* Larger clickable area */
    border-radius: 5px;
    text-transform: uppercase; /* Uppercase navigation links */
    font-size: 0.95rem;
}

.navbar-nav .nav-link:hover,
.navbar-nav .nav-link.active {
    color: var(--accent-color) !important; /* Accent color on hover/active */
    background-color: rgba(160, 64, 160, 0.08); /* Subtle background on hover */
}

.navbar-toggler {
    border-color: rgba(0, 0, 0, 0.2); /* Darker toggler border */
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%2851, 51, 51, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e"); /* Darker toggler icon */
}

.cart-icon-wrapper {
    position: relative;
    display: inline-block;
    margin-left: 25px; /* More space */
}

.cart-icon-wrapper .badge {
    top: -8px; /* Adjusted position */
    right: -12px;
    padding: 6px 9px; /* Larger badge */
    background-color: #d9534f; /* A more refined red */
    font-size: 0.8rem;
}

/* --- Section for Links (Consider renaming for a clothing store context if these are categories/brands) --- */
.link-item {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 25px; /* More padding */
    margin-bottom: 25px;
    background-color: var(--secondary-bg-color); /* Links use secondary background */
    color: var(--primary-text-color);
    display: flex;
    align-items: center;
    box-shadow: 0 3px 8px var(--shadow-color);
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.link-item:hover {
    transform: translateY(-5px); /* More noticeable lift on hover */
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.12);
}

.link-item h2 {
    margin-top: 0;
    color: var(--primary-text-color);
    flex-grow: 1;
    text-align: left;
    margin-bottom: 8px; /* Slightly less margin */
    font-size: 1.6rem; /* Slightly smaller for this context */
    font-weight: 700;
    text-transform: none; /* No uppercase for these titles */
    letter-spacing: 0;
}

.link-item p {
    margin-bottom: 5px;
    color: var(--secondary-text-color); /* Lighter text for descriptions */
    font-size: 0.9rem;
}

.link-item strong {
    color: var(--primary-text-color);
}

.link-item .logo-container {
    margin-right: 30px; /* More space */
    width: 120px; /* Larger logo container */
    height: 120px;
    border: 1px solid var(--border-color); /* Subtle border for logo container */
}

.link-item .logo-container img {
    border-radius: 8px; /* Slightly more rounded corners for images */
}

.link-info {
    flex-grow: 1;
}

/* Section Divider */
.section-divider {
    border-top: 2px solid var(--border-color);
    margin-top: 70px; /* More spacing */
    margin-bottom: 70px;
}

/* --- Product Section (Bootstrap Cards) Styles --- */
.produtos-section {
    margin-top: 50px;
    padding-top: 30px;
}

.produtos-section h2 {
    margin-bottom: 50px; /* More emphasis on section titles */
}

.produto-card {
    border: 1px solid var(--border-color); /* Subtle border for cards */
    border-radius: 10px;
    box-shadow: 0 4px 12px var(--shadow-color);
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out; /* Smoother transitions */
    cursor: pointer;
    text-align: left; /* Align text left within cards for product details */
    overflow: hidden;
    background-color: var(--secondary-bg-color);
}

.produto-card:hover {
    transform: translateY(-8px); /* More pronounced lift on hover */
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.18); /* Stronger shadow on hover */
}

.produto-card .card-img-top {
    width: 100%; /* Ensure image fills container */
    height: 280px; /* Taller image area for clothes */
    object-fit: cover; /* Cover ensures image fills the space, cropping if necessary */
    border-bottom: 1px solid var(--border-color);
    padding: 0; /* Remove padding here, let object-fit handle it */
    background-color: #ffffff; /* White background for product images */
}

.produto-card .card-body {
    padding: 25px; /* More internal padding */
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.produto-card .card-title {
    font-size: 1.5rem;
    color: var(--primary-text-color);
    margin-bottom: 8px;
    font-weight: 700;
    line-height: 1.3; /* Better line height for titles */
}

.produto-card .card-subtitle {
    font-size: 0.95rem;
    color: var(--secondary-text-color); /* Use secondary text for subtitle/category */
    margin-bottom: 15px; /* Space between subtitle and price */
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.produto-card .card-text {
    font-size: 1.3rem; /* Larger price */
    color: var(--accent-color);
    font-weight: bold;
    margin-bottom: 15px; /* Space before buttons */
}

.produto-card .btn-add-cart,
.produto-card .btn-details {
    margin-top: 10px; /* Reduced margin, as price has more space */
    width: 100%;
    background-color: var(--accent-color);
    border-color: var(--accent-color);
    color: white;
    transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.1s ease; /* Add transform for subtle click effect */
    padding: 10px 15px; /* More padding for buttons */
    border-radius: 5px; /* Slightly rounded buttons */
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.produto-card .btn-add-cart:hover,
.produto-card .btn-details:hover {
    background-color: var(--hover-color);
    border-color: var(--hover-color);
    transform: translateY(-2px); /* Slight lift on hover */
}

.produto-card.hidden {
    display: none !important;
}

/* --- Product Modal Styles (Adjusted for Bootstrap) --- */
.modal-content {
    border-radius: 10px;
    background-color: var(--secondary-bg-color); /* White modal background */
    color: var(--primary-text-color);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2); /* Stronger modal shadow */
}

.modal-header {
    border-bottom: 1px solid var(--border-color);
    padding: 20px 25px;
}

.modal-title {
    color: var(--primary-text-color);
    font-weight: 700;
    font-size: 1.8rem;
    text-align: left; /* Align modal title left */
    text-transform: uppercase;
}

.modal-body .row {
    align-items: flex-start; /* Align items to the top for better layout */
}

.modal-body .col-md-5 img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 12px var(--shadow-color);
    object-fit: contain; /* Ensure full image is visible */
    background-color: #ffffff;
    padding: 10px; /* Small internal padding for image */
}

.modal-body .col-md-7 p {
    margin-bottom: 15px; /* More space between paragraphs */
    font-size: 1rem;
    color: var(--secondary-text-color); /* Lighter text for descriptions */
}

.modal-body .modal-preco {
    font-size: 2.5rem; /* Larger, more prominent price */
    color: var(--accent-color);
    font-weight: bold;
    margin-top: 20px;
    display: block;
    text-align: left; /* Align price left */
}

/* Custom styles for select filter */
#categoria-select {
    width: auto;
    max-width: 350px; /* Slightly wider filter */
    display: inline-block;
    vertical-align: middle;
    margin-left: 15px;
    border: 1px solid var(--border-color);
    border-radius: 5px;
    padding: 8px 12px;
    color: var(--primary-text-color);
    background-color: var(--secondary-bg-color);
}

/* --- New Cart Modal Styles --- */
#cartModal .modal-body .cart-item {
    display: flex;
    align-items: center;
    border-bottom: 1px solid var(--border-color);
    padding: 15px 0; /* More padding for cart items */
}

#cartModal .modal-body .cart-item:last-child {
    border-bottom: none;
}

#cartModal .modal-body .cart-item img {
    width: 80px; /* Larger cart item images */
    height: 80px;
    object-fit: cover;
    border-radius: 5px;
    margin-right: 20px;
    border: 1px solid var(--border-color);
}

#cartModal .modal-body .item-details h6 {
    margin-bottom: 8px;
    font-weight: 700;
    color: var(--primary-text-color);
    font-size: 1.1rem;
}

#cartModal .modal-body .item-details p {
    margin-bottom: 0;
    font-size: 0.9rem;
    color: var(--secondary-text-color);
}

#cartModal .modal-body .item-controls {
    display: flex;
    align-items: center;
    flex-grow: 1; /* Allow controls to take available space */
    justify-content: flex-end; /* Push controls to the right */
}

#cartModal .modal-body .item-controls input {
    width: 70px; /* Wider input for quantity */
    text-align: center;
    margin: 0 8px;
    border: 1px solid var(--border-color);
    border-radius: 5px;
    padding: 5px 8px;
    color: var(--primary-text-color);
    background-color: #fcfcfc;
}

#cartModal .modal-body .total-price {
    font-size: 1.5rem; /* Larger total price */
    font-weight: bold;
    color: var(--accent-color);
    text-align: right;
    margin-top: 25px; /* More space above total */
    padding-top: 15px; /* Padding above total */
    border-top: 2px dashed var(--border-color); /* Dashed line for total separation */
}

#cartModal .modal-body .empty-cart-message {
    text-align: center;
    padding: 40px;
    color: #999; /* Softer gray for empty message */
    font-style: italic;
}

/* --- Footer Styles --- */
.footer {
    background-color: var(--primary-text-color); /* Darker footer for contrast */
    color: #f8f8f8; /* Light text on dark footer */
    padding: 40px 0; /* More padding */
    margin-top: 70px;
}

.footer h5 {
    color: #ffffff; /* White titles in footer */
    margin-bottom: 25px; /* More space */
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.footer p, .footer a {
    color: #e0e0e0; /* Slightly off-white for regular text and links */
    font-size: 0.95rem;
    text-decoration: none;
    margin-bottom: 8px; /* Spacing for list items */
}

.footer a:hover {
    color: var(--accent-color); /* Accent color on footer link hover */
    text-decoration: underline;
}

.footer .social-icons a {
    font-size: 1.8rem; /* Larger social icons */
    margin-right: 20px;
    color: #ffffff;
    transition: color 0.2s ease-in-out;
}

.footer .social-icons a:hover {
    color: var(--accent-color);
}

/* --- Floating Cart Button Styles --- */
.floating-cart-btn {
    position: fixed;
    bottom: 30px; /* Further from bottom */
    right: 30px; /* Further from right */
    width: 65px; /* Slightly larger button */
    height: 65px;
    font-size: 1.6rem;
    background-color: var(--accent-color);
    color: white;
    border: none;
    border-radius: 50%; /* Fully round button */
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2) !important; /* Stronger shadow */
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.floating-cart-btn:hover {
    background-color: var(--hover-color);
    transform: scale(1.05); /* Subtle scale effect on hover */
}

.floating-cart-btn .badge {
    font-size: 0.8rem;
    padding: 0.5em 0.7em;
    background-color: #d9534f;
}/* --- Estilos para a Seção de Logo e Nome da Empresa --- */
.company-logo {
    width: 120px; /* Tamanho base da logo */
    height: 120px; /* Garante que ela seja quadrada antes de virar círculo */
    object-fit: contain; /* Garante que a imagem se ajuste sem cortar */
    border: 3px solid var(--accent-color); /* Borda com a cor de destaque */
    box-shadow: 0 4px 10px var(--shadow-color); /* Sombra sutil para profundidade */
    transition: transform 0.3s ease-in-out; /* Transição para efeito hover */
    padding: 5px; /* Pequeno padding interno para evitar que a imagem cole na borda */
    background-color: #ffffff; /* Fundo branco para a imagem da logo */
}

.company-logo:hover {
    transform: scale(1.05); /* Leve zoom no hover */
}

.company-name {
    font-size: 2.5rem; /* Tamanho da fonte grande para o nome */
    font-weight: 800; /* Super negrito */
    color: var(--primary-text-color); /* Cor principal do texto */
    text-transform: uppercase; /* Maiúsculas para um visual moderno */
    letter-spacing: 1.5px; /* Espaçamento entre as letras */
    margin-top: 15px; /* Espaço entre a logo e o nome */
    text-shadow: 1px 1px 2px rgba(0,0,0,0.05); /* Sombra de texto muito sutil */
}

/* --- Media Query para Responsividade em Telas Menores --- */
@media (max-width: 767.98px) {
    .company-logo {
        width: 90px; /* Logo menor em mobile */
        height: 90px;
    }

    .company-name {
        font-size: 1.8rem; /* Nome menor em mobile */
        letter-spacing: 1px;
    }

    .col-12.text-center.my-4 {
        margin-top: 25px; /* Ajusta margem em mobile */
        margin-bottom: 25px;
    }
}
    </style>
@empty
    {{-- Optional: fallback styles if $links is empty --}}
    <style>
        body {
            background-color: #f8f9fa; /* Default light background */
            color: #212529; /* Default dark text */
        }
        /* ... other default styles ... */
    </style>
@endforelse
</head>
<body>
   @php
    $mainCompany = $links->first();
    $companyName = $mainCompany->nome_fantasia ?? $mainCompany->razao_social ?? 'Meu Catálogo Online';

    $logoFile = $mainCompany->configuracaoNota->logo ?? null;

    // Caminho local (servidor) para verificar se o arquivo existe
    $logoLocalPath = public_path('uploads/configEmitente/' . $logoFile);

    if ($logoFile && file_exists($logoLocalPath)) {
        // Se o arquivo existir localmente
        $companyLogo = asset('uploads/configEmitente/' . $logoFile);
    } elseif ($logoFile) {
        // Se não existir localmente, usa o link externo fornecido
        $companyLogo = 'https://saas.mixksolutions.com.br/uploads/configEmitente/' . $logoFile;
    } else {
        // Caso não haja logo definida
        $companyLogo = asset('images/default_logo.png');
    }

    $whatsappNumber = $mainCompany->telefone_whatsapp ?? $mainCompany->telefone ?? '5551999999999';
@endphp


    {{-- HEADER (Navbar) --}}
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="{{ $companyLogo }}" alt="Logo de {{ $companyName }}" width="40" height="40" class="d-inline-block align-text-top me-2">

            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Início</a>
                    </li>
                   
                    <li class="nav-item">
                        <a class="nav-link" href="#produtos-section">Produtos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact-footer">Contato</a>
                    </li>
                    {{-- Item do carrinho na navbar --}}
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#cartModal" id="cartNavLink">
                            <div class="cart-icon-wrapper">
                                <i class="bi bi-cart-fill"></i> <span id="cartNavLinkText">Carrinho</span>
                                <span class="badge" id="cart-count">0</span>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">

      {{-- Seção de exibição dos Links/Empresas --}}
<div class="row" id="links-section">
 
  <div class="col-12 text-center my-4"> <img src="{{ $companyLogo }}" alt="Logo de {{ $companyName }}" class="img-fluid rounded-circle company-logo mb-2">
    <h1 class="company-name">{{ $companyName }}</h1>
</div>
    {{-- Verifica se há links para exibir o título da seção --}}
    @forelse ($links as $link)
      
    @empty
        <div class="col-12">
            <p class="alert alert-info text-center">Nenhum link de parceiro ou loja encontrado no catálogo.</p>
        </div>
    @endforelse
</div>
        <hr class="section-divider">

        <div class="produtos-section" id="produtos-section">
            @if(isset($currentNomeLink) && $currentNomeLink)
            @else
                <h2 class="display-5 mb-4" style="color: {{ $link->text_color ?? '#212529' }};">Todos os Nossos Produtos</h2>
            @endif

            <div class="row mb-4 justify-content-center"> {{-- Added row for layout and centering --}}
                @if(isset($currentNomeLink) && $currentNomeLink)
                    <div class="col-md-auto mb-3 mb-md-0"> {{-- Column for category filter --}}
                        <label for="categoria-select" class="form-label fw-bold me-2" style="color: {{ $link->text_color ?? '#212529' }};">Filtrar por Categoria:</label>
                        <select id="categoria-select" class="form-select d-inline-block w-auto">
                            <option value="todos"
                                {{ (isset($selectedCategoryId) && $selectedCategoryId == 'todos') ? 'selected' : '' }}
                            >
                                Todas as Categorias
                            </option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}"
                                    {{ (isset($selectedCategoryId) && $selectedCategoryId == $categoria->id) ? 'selected' : '' }}
                                >
                                    {{ $categoria->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-auto"> {{-- Column for search input --}}
                    <label for="product-search" class="form-label fw-bold me-2" style="color: {{ $link->text_color ?? '#212529' }};">Pesquisar Produto:</label>
                    <input type="text" id="product-search" class="form-control d-inline-block w-auto" placeholder="Digite o nome do produto...">
                </div>
            </div>

            {{-- Grid de exibição dos Produtos (usando row e col do Bootstrap) --}}
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="produtos-grid">
                @forelse ($produtos as $produto)
                    <div class="col">
                        <div class="card produto-card h-100">                      
                        <img src="{{ asset($produto->img) }}" class="card-img-top" alt="{{ $produto->nome }}">
                            <div class="card-body">
                                <h3 class="card-title">{{ $produto->nome }}</h3>
                                <p class="card-text">R$ {{ number_format($produto->valor_venda, 2, ',', '.') }}</p>
                                @if ($produto->categoria)
                                    <h6 class="card-subtitle mb-2 text-muted">Categoria: {{ $produto->categoria->nome }}</h6>
                                @endif
                                {{-- Botão Adicionar ao Carrinho no Card --}}
                                <button class="btn btn-primary btn-add-cart"
                                    data-id="{{ $produto->id }}"
                                    data-nome="{{ $produto->nome }}"
                                    data-preco="{{ $produto->valor_venda }}" {{-- Valor numérico para cálculo --}}
                                    data-imagem="{{ asset($produto->img) }}"
                                    data-categoria-id="{{ $produto->categoria_id ?? '0' }}"
                                >
                                    Adicionar ao Carrinho <i class="bi bi-cart-plus"></i>
                                </button>
                                {{-- Botão Ver Detalhes (já existente, mantido) --}}
                                <button class="btn btn-outline-info btn-sm mt-2 btn-details"
                                    data-id="{{ $produto->id }}"
                                    data-nome="{{ $produto->nome }}"
                                    data-preco="{{ number_format($produto->valor_venda, 2, ',', '.') }}"
                                    data-imagem="{{ asset($produto->img) }}"
                                    data-categoria-id="{{ $produto->categoria_id ?? '0' }}"
                                    data-bs-toggle="modal" data-bs-target="#productModal"
                                >
                                    Ver Detalhes
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="alert alert-warning text-center">Nenhum produto encontrado neste catálogo.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- A Estrutura da Modal de Produto (Detalhes) --}}
        <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel">Detalhes do Produto: <span id="modalProductName"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-5 text-center">
                                <img id="modalProductImage" src="" class="img-fluid" alt="Imagem do Produto">
                            </div>
                            <div class="col-md-7">
                                <p><strong>Preço:</strong> <span id="modalProductPrice" class="modal-preco"></span></p>
                                {{-- Botão Adicionar ao Carrinho na Modal de Detalhes --}}
                                <button class="btn btn-success mt-3" id="modalAddToCartBtn"
                                    data-id=""
                                    data-nome=""
                                    data-preco=""
                                    data-imagem=""
                                >
                                    Adicionar ao Carrinho <i class="bi bi-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal do Carrinho de Compras --}}
        <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cartModalLabel"><i class="bi bi-cart-fill me-2"></i>Seu Carrinho de Compras</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="cart-items-container">
                            {{-- Itens do carrinho serão inseridos aqui pelo JavaScript --}}
                        </div>
                        <div class="total-price mt-3">
                            Total: <span id="cart-total">R$ 0,00</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continuar Comprando</button>
                        <button type="button" class="btn btn-danger" id="clearCartBtn">Limpar Carrinho</button>
                        <button type="button" class="btn btn-success" id="checkoutBtn">Finalizar Pedido (WhatsApp)</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <button class="btn btn-primary rounded-circle floating-cart-btn shadow-lg" type="button" data-bs-toggle="modal" data-bs-target="#cartModal">
        <i class="bi bi-cart-fill"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="floating-cart-count">
            0
            <span class="visually-hidden">itens no carrinho</span>
        </span>
    </button>


    {{-- FOOTER --}}
    <footer class="footer bg-dark text-light mt-auto py-4" id="contact-footer">
        <div class="container text-center text-md-start">
            <div class="row">
                <div class="col-md-4 col-lg-3 mx-auto mb-4 text-center">
                    <h5 class="text-uppercase fw-bold">{{ $companyName }}</h5>
                    <p>
                        Apresentando as melhores empresas e produtos em um só lugar. Facilitando sua busca e conexão com o que você precisa!
                    </p>
                </div>

                <div class="col-md-4 col-lg-3 mx-auto mb-4 text-center">
                    <h5 class="text-uppercase fw-bold">Links Úteis</h5>
                    <ul class="list-unstyled">
                        <li><a href="#links-section" class="text-decoration-none text-light">Nossos Parceiros</a></li>
                        <li><a href="#produtos-section" class="text-decoration-none text-light">Todos os Produtos</a></li>
                        <li><a href="#" class="text-decoration-none text-light">Sobre Nós</a></li>
                        <li><a href="#" class="text-decoration-none text-light">Privacidade</a></li>
                    </ul>
                </div>

                <div class="col-md-4 col-lg-3 mx-auto mb-md-0 mb-4 text-center">
                    <h5 class="text-uppercase fw-bold">Contato</h5>
                    <p><i class="bi bi-house-door-fill me-2"></i> {{ $mainCompany->rua ?? 'Rua Exemplo' }}, {{ $mainCompany->numero ?? '123' }} - {{ $mainCompany->bairro ?? 'Centro' }}, {{ $mainCompany->cidade ?? 'Cidade' }} - {{ $mainCompany->uf ?? 'Estado'}}</p>
                    <p><i class="bi bi-envelope-fill me-2"></i> {{ $mainCompany->email ?? 'contato@meucatalogo.com' }}</p>
                    <p><i class="bi bi-phone-fill me-2"></i> {{ $mainCompany->telefone ?? '+55 (XX) 9XXXX-XXXX' }}</p>
                </div>
            </div>
            <hr class="my-3">
            <div class="row align-items-center">
                <div class="col-md-7 col-lg-8 text-center text-md-start">
                    <p class="text-light">&copy; {{ date('Y') }} {{ $companyName }}. Todos os direitos reservados.</p>
                </div>
                <div class="col-md-5 col-lg-4 text-center text-md-end social-icons">
                    <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-light me-3"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="text-light me-3"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-light"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

</body><script>

document.addEventListener('DOMContentLoaded', function () {
    const productCards = document.querySelectorAll('.produto-card');
    const categoriaSelect = document.getElementById('categoria-select');
    const productModal = document.getElementById('productModal');
    const cartModal = document.getElementById('cartModal');

    const cartItemsContainer = document.getElementById('cart-items-container');
    const cartTotalSpan = document.getElementById('cart-total');
    const cartCountBadge = document.getElementById('cart-count');
    const floatingCartCountBadge = document.getElementById('floating-cart-count');
    const clearCartBtn = document.getElementById('clearCartBtn');
    const checkoutBtn = document.getElementById('checkoutBtn');

    const whatsappCompanyNumber = "{{ $whatsappNumber ?? '' }}";
    const productSearchInput = document.getElementById('product-search');

    function formatPhoneNumberForWhatsApp(phoneNumber) {
        let cleaned = phoneNumber.replace(/\D/g, '');
        if (cleaned.length === 10 || cleaned.length === 11) {
            if (!cleaned.startsWith('55')) {
                cleaned = '55' + cleaned;
            }
        }
        return cleaned;
    }

    function getCart() {
        const cart = localStorage.getItem('cart');
        return cart ? JSON.parse(cart) : [];
    }

    function saveCart(cart) {
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartUI();
    }

    function addItemToCart(item) {
        let cart = getCart();
        const existingItemIndex = cart.findIndex(i => i.id === item.id);

        if (existingItemIndex > -1) {
            cart[existingItemIndex].quantity += 1;
        } else {
            cart.push({ ...item, preco: parseFloat(item.preco), quantity: 1 });
        }
        saveCart(cart);
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: `${item.nome} adicionado ao carrinho!`,
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
        });
    }

    function removeItemFromCart(itemId) {
        let cart = getCart();
        const itemToRemove = cart.find(item => item.id === itemId);

        if (itemToRemove) {
            Swal.fire({
                title: `Remover "${itemToRemove.nome}" do carrinho?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    cart = cart.filter(item => item.id !== itemId);
                    saveCart(cart);
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: `${itemToRemove.nome} removido do carrinho.`,
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                }
            });
        }
    }

    function updateItemQuantity(itemId, newQuantity) {
        let cart = getCart();
        const itemIndex = cart.findIndex(item => item.id === itemId);

        if (itemIndex > -1) {
            if (newQuantity <= 0) {
                removeItemFromCart(itemId);
            } else {
                cart[itemIndex].quantity = newQuantity;
                saveCart(cart);
            }
        }
    }

    function renderCartItems() {
        const cart = getCart();
        cartItemsContainer.innerHTML = '';

        if (cart.length === 0) {
            cartItemsContainer.innerHTML = '<p class="empty-cart-message">Seu carrinho está vazio.</p>';
            checkoutBtn.disabled = true;
            clearCartBtn.disabled = true;
        } else {
            checkoutBtn.disabled = false;
            clearCartBtn.disabled = false;

            cart.forEach(item => {
                const itemElement = document.createElement('div');
                itemElement.classList.add('cart-item', 'd-flex', 'align-items-center', 'mb-3', 'py-2', 'border-bottom');
                itemElement.innerHTML = `
                    <img src="${item.imagem}" alt="${item.nome}" class="img-thumbnail me-3" style="width: 70px; height: 70px; object-fit: cover;">
                    <div class="item-details flex-grow-1">
                        <h6 class="mb-0">${item.nome}</h6>
                        <small class="text-muted">R$ ${parseFloat(item.preco).toFixed(2).replace('.', ',')}</small>
                    </div>
                    <div class="item-controls d-flex align-items-center">
                        <button class="btn btn-sm btn-outline-secondary decrease-quantity me-1" data-id="${item.id}">-</button>
                        <input type="number" class="form-control form-control-sm text-center item-quantity" value="${item.quantity}" min="1" data-id="${item.id}" style="width: 60px;">
                        <button class="btn btn-sm btn-outline-secondary increase-quantity ms-1" data-id="${item.id}">+</button>
                        <button class="btn btn-sm btn-danger ms-2 remove-item" data-id="${item.id}"><i class="bi bi-trash"></i></button>
                    </div>
                `;
                cartItemsContainer.appendChild(itemElement);
            });

            cartItemsContainer.querySelectorAll('.increase-quantity').forEach(button => {
                button.addEventListener('click', (event) => {
                    const itemId = parseInt(event.target.closest('button').dataset.id);
                    const input = event.target.closest('.item-controls').querySelector('.item-quantity');
                    let newQuantity = parseInt(input.value) + 1;
                    updateItemQuantity(itemId, newQuantity);
                });
            });

            cartItemsContainer.querySelectorAll('.decrease-quantity').forEach(button => {
                button.addEventListener('click', (event) => {
                    const itemId = parseInt(event.target.closest('button').dataset.id);
                    const input = event.target.closest('.item-controls').querySelector('.item-quantity');
                    let newQuantity = parseInt(input.value) - 1;
                    updateItemQuantity(itemId, newQuantity);
                });
            });

            cartItemsContainer.querySelectorAll('.item-quantity').forEach(input => {
                input.addEventListener('change', (event) => {
                    const itemId = parseInt(event.target.dataset.id);
                    let newQuantity = parseInt(event.target.value);
                    if (isNaN(newQuantity) || newQuantity < 1) {
                        newQuantity = 1;
                        event.target.value = 1;
                    }
                    updateItemQuantity(itemId, newQuantity);
                });
            });

            cartItemsContainer.querySelectorAll('.remove-item').forEach(button => {
                button.addEventListener('click', (event) => {
                    const itemId = parseInt(event.target.closest('button').dataset.id);
                    removeItemFromCart(itemId);
                });
            });
        }
    }

    function updateCartUI() {
        const cart = getCart();
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        const totalPrice = cart.reduce((sum, item) => sum + (item.preco * item.quantity), 0);

        cartCountBadge.textContent = totalItems;
        floatingCartCountBadge.textContent = totalItems;
        cartTotalSpan.textContent = `R$ ${totalPrice.toFixed(2).replace('.', ',')}`;
        renderCartItems();
    }

    function clearCart() {
        Swal.fire({
            title: 'Tem certeza?',
            text: 'Deseja limpar todo o carrinho?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, limpar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                localStorage.removeItem('cart');
                updateCartUI();
                Swal.fire('Limpo!', 'Carrinho limpo com sucesso.', 'success');
            }
        });
    }

    function checkout() {
        const cart = getCart();
        if (cart.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Carrinho vazio',
                text: 'Adicione produtos para finalizar o pedido.'
            });
            return;
        }

        if (!whatsappCompanyNumber) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Número de WhatsApp não configurado. Contate o administrador.'
            });
            return;
        }

        const formattedWhatsAppNumber = formatPhoneNumberForWhatsApp(whatsappCompanyNumber);
        let message = `Olá! Meu pedido:\n\n`;
        let total = 0;

        cart.forEach(item => {
            message += `* ${item.nome} (x${item.quantity}) - R$ ${(item.preco * item.quantity).toFixed(2).replace('.', ',')}\n`;
            total += item.preco * item.quantity;
        });

        message += `\nTotal do Pedido: *R$ ${total.toFixed(2).replace('.', ',')}*`;
        message += `\n\nPor favor, confirme a disponibilidade e o valor total.`;

        const encodedMessage = encodeURIComponent(message);
        const whatsappUrl = `https://wa.me/${formattedWhatsAppNumber}?text=${encodedMessage}`;

        window.open(whatsappUrl, '_blank');

        Swal.fire({
            icon: 'success',
            title: 'Pedido enviado!',
            text: 'Aguarde o contato da loja via WhatsApp.'
        });

        clearCart();
    }

    function filterProducts() {
        const searchText = productSearchInput ? productSearchInput.value.toLowerCase() : '';
        const selectedCategoryId = categoriaSelect ? categoriaSelect.value : 'todos';

        productCards.forEach(card => {
            const productName = card.querySelector('.card-title').textContent.toLowerCase();
            const productCategoryId = card.querySelector('.btn-add-cart').dataset.categoriaId;

            const matchesSearch = productName.includes(searchText);
            const matchesCategory = (selectedCategoryId === 'todos' || productCategoryId === selectedCategoryId);

            if (matchesSearch && matchesCategory) {
                card.closest('.col').classList.remove('d-none');
            } else {
                card.closest('.col').classList.add('d-none');
            }
        });
    }

    document.querySelectorAll('.btn-details').forEach(button => {
        button.addEventListener('click', function () {
            const productId = this.dataset.id;
            const productName = this.dataset.nome;
            const productPrice = this.dataset.preco;
            const productImage = this.dataset.imagem;

            document.getElementById('modalProductName').textContent = productName;
            document.getElementById('modalProductImage').src = productImage;
            document.getElementById('modalProductPrice').textContent = `R$ ${parseFloat(productPrice).toFixed(2).replace('.', ',')}`;

            const modalAddToCartBtn = document.getElementById('modalAddToCartBtn');
            modalAddToCartBtn.dataset.id = productId;
            modalAddToCartBtn.dataset.nome = productName;
            modalAddToCartBtn.dataset.preco = productPrice;
            modalAddToCartBtn.dataset.imagem = productImage;
        });
    });

    document.querySelectorAll('.btn-add-cart').forEach(button => {
        button.addEventListener('click', function () {
            const item = {
                id: parseInt(this.dataset.id),
                nome: this.dataset.nome,
                preco: parseFloat(this.dataset.preco),
                imagem: this.dataset.imagem
            };
            addItemToCart(item);
        });
    });

    if (document.getElementById('modalAddToCartBtn')) {
        document.getElementById('modalAddToCartBtn').addEventListener('click', function () {
            const item = {
                id: parseInt(this.dataset.id),
                nome: this.dataset.nome,
                preco: parseFloat(this.dataset.preco),
                imagem: this.dataset.imagem
            };
            addItemToCart(item);
            const productModalInstance = bootstrap.Modal.getInstance(productModal);
            if (productModalInstance) {
                productModalInstance.hide();
            }
        });
    }

    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', clearCart);
    }

    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', checkout);
    }

    if (productSearchInput) {
        productSearchInput.addEventListener('input', filterProducts);
    }

    if (categoriaSelect) {
        categoriaSelect.addEventListener('change', filterProducts);
    }

    updateCartUI();
    filterProducts();
});
</script>
</body>
</html>