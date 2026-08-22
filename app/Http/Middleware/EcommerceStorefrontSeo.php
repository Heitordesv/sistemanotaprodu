<?php

namespace App\Http\Middleware;

use App\Models\ConfigEcommerce;
use App\Models\ProdutoEcommerce;
use App\Services\EcommerceStockService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EcommerceStorefrontSeo
{
    public function __construct(private EcommerceStockService $stockService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$response->isSuccessful() || !str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        if (!$html || !str_contains($html, '</head>')) {
            return $response;
        }

        $link = strtolower((string) $request->route('link'));
        if ($link === '') {
            return $response;
        }

        $config = ConfigEcommerce::where('link', $link)->first();
        if (!$config) {
            return $response;
        }

        $html = preg_replace('/<html\s+lang=["\'][^"\']+["\']/i', '<html lang="pt-BR"', $html, 1) ?: $html;
        $html = preg_replace('/<meta\s+name=["\']description["\'][^>]*>\s*/i', '', $html) ?: $html;

        // Corrige o campo de busca do tema OneTech sem depender de duplicação de Blade.
        $html = preg_replace(
            '/<input([^>]*type=["\']search["\'][^>]*class=["\'][^"\']*header_search_input[^"\']*["\'])(?![^>]*name=)([^>]*)>/i',
            '<input$1 name="pesquisa"$2>',
            $html
        ) ?: $html;
        $html = preg_replace(
            '/<input([^>]*type=["\']search["\'][^>]*class=["\'][^"\']*page_menu_search_input[^"\']*["\'])(?![^>]*name=)([^>]*)>/i',
            '<input$1 name="pesquisa"$2>',
            $html
        ) ?: $html;

        // Imagens fora do primeiro viewport passam a usar carregamento preguiçoso.
        $imageIndex = 0;
        $html = preg_replace_callback('/<img\b([^>]*)>/i', function ($matches) use (&$imageIndex) {
            $imageIndex++;
            $attrs = $matches[1];
            if ($imageIndex <= 2 || stripos($attrs, 'loading=') !== false) {
                return $matches[0];
            }
            return '<img loading="lazy" decoding="async"' . $attrs . '>';
        }, $html) ?: $html;

        $canonical = url()->current();
        $storeName = trim((string) ($config->nome ?: $config->nome_fantasia ?: 'Loja Online'));
        $title = $storeName;
        $description = trim(strip_tags((string) ($config->descricao ?? '')));
        if ($description === '') {
            $description = 'Compre online com segurança, consulte produtos, condições de pagamento e entrega.';
        }
        $description = mb_substr(preg_replace('/\s+/', ' ', $description), 0, 160);
        $image = $this->absoluteUrl((string) ($config->img ?? ''));
        $type = 'website';
        $structured = null;

        $produtoId = (int) ($request->route('produtoId') ?: $request->route('id'));
        if ($produtoId > 0 && str_contains($request->path(), '/verProduto')) {
            $produto = ProdutoEcommerce::with(['produto', 'galeria'])
                ->where('id', $produtoId)
                ->where('empresa_id', $config->empresa_id)
                ->where('status', 1)
                ->first();

            if ($produto && $produto->produto && !$produto->produto->inativo) {
                $title = trim((string) $produto->produto->nome) . ' | ' . $storeName;
                $description = mb_substr(
                    preg_replace('/\s+/', ' ', trim(strip_tags((string) ($produto->descricao ?: $produto->produto->nome)))),
                    0,
                    160
                );
                $image = $this->absoluteUrl((string) ($produto->img ?: $image));
                $type = 'product';
                $disponivel = !$produto->controlar_estoque || $this->stockService->disponivel($produto) > 0;
                $structured = [
                    '@context' => 'https://schema.org',
                    '@type' => 'Product',
                    'name' => (string) $produto->produto->nome,
                    'description' => $description,
                    'image' => $image ? [$image] : [],
                    'sku' => (string) ($produto->produto->referencia ?: $produto->id),
                    'offers' => [
                        '@type' => 'Offer',
                        'url' => $canonical,
                        'priceCurrency' => 'BRL',
                        'price' => number_format((float) $produto->valor, 2, '.', ''),
                        'availability' => $disponivel
                            ? 'https://schema.org/InStock'
                            : 'https://schema.org/OutOfStock',
                    ],
                ];
            }
        }

        if ($request->routeIs('*') && str_contains($request->path(), '/pesquisa')) {
            $termo = trim((string) $request->query('pesquisa'));
            if ($termo !== '') {
                $title = 'Busca por ' . $termo . ' | ' . $storeName;
                $description = 'Resultados da busca por ' . $termo . ' na loja ' . $storeName . '.';
            }
        }

        $privatePaths = ['carrinho', 'checkout', 'endereco', 'pagamento', 'pagar/', 'pix/', 'pedido-finalizado', 'login', 'pedido_detalhe'];
        $robots = collect($privatePaths)->contains(fn ($part) => str_contains($request->path(), $part))
            ? 'noindex,nofollow'
            : 'index,follow,max-image-preview:large';

        $tags = $this->tags($title, $description, $canonical, $image, $type, $robots, $storeName, $structured);
        $html = preg_replace('/<title>.*?<\/title>/is', '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>', $html, 1) ?: $html;
        $html = str_replace('</head>', $tags . "\n</head>", $html);
        $response->setContent($html);

        return $response;
    }

    private function tags(
        string $title,
        string $description,
        string $canonical,
        ?string $image,
        string $type,
        string $robots,
        string $storeName,
        ?array $structured
    ): string {
        $e = fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        $tags = [
            '<meta name="description" content="' . $e($description) . '">',
            '<meta name="robots" content="' . $e($robots) . '">',
            '<link rel="canonical" href="' . $e($canonical) . '">',
            '<meta property="og:locale" content="pt_BR">',
            '<meta property="og:type" content="' . $e($type) . '">',
            '<meta property="og:site_name" content="' . $e($storeName) . '">',
            '<meta property="og:title" content="' . $e($title) . '">',
            '<meta property="og:description" content="' . $e($description) . '">',
            '<meta property="og:url" content="' . $e($canonical) . '">',
            '<meta name="twitter:card" content="summary_large_image">',
            '<meta name="twitter:title" content="' . $e($title) . '">',
            '<meta name="twitter:description" content="' . $e($description) . '">',
        ];

        if ($image) {
            $tags[] = '<meta property="og:image" content="' . $e($image) . '">';
            $tags[] = '<meta name="twitter:image" content="' . $e($image) . '">';
        }

        if ($structured) {
            $tags[] = '<script type="application/ld+json">'
                . json_encode($structured, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP)
                . '</script>';
        }

        return implode("\n", $tags);
    }

    private function absoluteUrl(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') return null;
        if (preg_match('#^https?://#i', $value)) return $value;
        return url('/' . ltrim($value, '/'));
    }
}