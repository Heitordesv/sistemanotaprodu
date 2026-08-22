<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfigEcommerce;
use App\Models\CategoriaProdutoEcommerce;
use App\Models\CarrosselEcommerce;
use App\Models\ProdutoEcommerce;
use App\Models\PostBlogEcommerce;
use App\Models\CategoriaPostBlogEcommerce;
use App\Models\ContatoEcommerce;
use App\Models\InformativoEcommerce;
use App\Models\PedidoEcommerce;
use App\Models\ItemPedidoEcommerce;
use App\Models\ClienteEcommerce;
use App\Models\EnderecoEcommerce;
use App\Models\CurtidaProdutoEcommerce;
use App\Models\SubCategoriaEcommerce;
use App\Models\CupomDescontoEcommerce;
use App\Models\CupomEcommerceUtilizado;
use App\Rules\ValidaDocumento;
use App\Helpers\PedidoEcommerceHelper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Mail;
use App\Rules\EmailDupEcommerce;

use Illuminate\Support\Facades\Log;

class EcommerceController extends Controller
{
    

	public function index($link = null)
	{
		$link = strtolower($link);
		$dadosDefault = $this->getDadosDefault($link);
		$carrossel = CarrosselEcommerce::where('empresa_id', $dadosDefault['config']->empresa_id)
		->get();

		$produtosEmDestaque = $this->produtosEmDestaque($dadosDefault['config']->empresa_id);
		$categoriasEmDestaque = $this->categoriaDestesProdutos($produtosEmDestaque);

		$categorias = CategoriaProdutoEcommerce::where('empresa_id', $dadosDefault['config']->empresa_id)
		->inRandomOrder()
		->get();

		$produtosEmDestaque = $this->preparaValorDesconto($produtosEmDestaque, $dadosDefault['config']);

		return view($dadosDefault['template'] . '/home')
		->with('categorias', $categorias)
		->with('carrossel', $carrossel)
		->with('default', $dadosDefault)
		->with('produtosEmDestaque', $produtosEmDestaque)
		->with('categoriasEmDestaque', $categoriasEmDestaque)
		->with('rota', $dadosDefault['rota'])
		->with('title', 'Home');
	}

	private function produtosEmDestaque($empresa_id)
	{
		$produtos = ProdutoEcommerce::select('produto_ecommerces.*')
		->join(
			'produtos',
			'produtos.id',
			'=',
			'produto_ecommerces.produto_id'
		)
		->where('produto_ecommerces.empresa_id', $empresa_id)
		->where('produto_ecommerces.destaque', true)
		->where('produto_ecommerces.status', true)
		->groupBy('produtos.referencia_grade')
		->get();

		$produtoEcommerceHelper = new PedidoEcommerceHelper();
		$user = $produtoEcommerceHelper->getUserLogado();

		foreach ($produtos as $p) {
			if ($user) {
				$c = CurtidaProdutoEcommerce::where('produto_id', $p->id)
				->where('cliente_id', $user['cliente_id'])
				->first();

				$p->curtido = $c != null;
			} else {
				$p->curtido = false;
			}
		}

		return $this->limitaEstoque($produtos);
	}

	private function limitaEstoque($produtos)
	{
		$temp = [];
		foreach ($produtos as $p) {
			if ($p->controlar_estoque) {
				if ($p->produto->estoque) {
					if ($p->produto->estoque->quantidade > 0) {
						array_push($temp, $p);
					}
				}
			} else {
				array_push($temp, $p);
			}
		}
		return $temp;
	}
public function facebookFeed($link = null)
{
    $link = strtolower($link);
    $dadosDefault = $this->getDadosDefault($link);

    $empresa_id = $dadosDefault['config']->empresa_id;

    // Buscar produtos ativos da empresa
    $produtos = ProdutoEcommerce::where('empresa_id', $empresa_id)
        ->where('status', true)
        ->get();

    // Buscar dados completos da empresa
    $config = ConfigEcommerce::where('empresa_id', $empresa_id)->first();

    // XML base
    $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
    <rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"></rss>');

    $channel = $xml->addChild('channel');

    $channel->addChild('title', $config->nome ?? 'Produtos da Loja');
    $channel->addChild('link', url($dadosDefault['rota']));
    $channel->addChild('description', $config->descricao ?? 'Feed de produtos para Facebook');

    foreach ($produtos as $p) {
        // Ignorar produtos sem imagem
        if (empty($p->galeria) || count($p->galeria) == 0) continue;

        // Verifica estoque
        if ($p->controlar_estoque) {
            $quantidade = isset($p->produto->estoque) ? $p->produto->estoque->quantidade : 0;
            if ($quantidade <= 0) continue;
            $availability = 'in stock';
        } else {
            $quantidade = 999;
            $availability = 'in stock';
        }

        $item = $channel->addChild('item');

        $item->addChild('g:id', 'PROD_' . $p->id, 'http://base.google.com/ns/1.0');

        $titulo = htmlspecialchars(substr(trim($p->produto->nome ?? 'Produto'), 0, 65), ENT_XML1 | ENT_COMPAT, 'UTF-8');
        $item->addChild('g:title', $titulo, 'http://base.google.com/ns/1.0');

        // ✅ DESCRIÇÃO CORRETA DO PRODUTO
        $descricao = htmlspecialchars(substr(strip_tags($p->descricao ?? 'Produto disponível'), 0, 150), ENT_XML1 | ENT_COMPAT, 'UTF-8');
        $item->addChild('g:description', $descricao, 'http://base.google.com/ns/1.0');

        $item->addChild('g:link', url($dadosDefault['rota'] . '/' . $p->id . '/verProduto'), 'http://base.google.com/ns/1.0');
        $item->addChild('g:image_link', $p->galeria[0]->img, 'http://base.google.com/ns/1.0');
        $item->addChild('g:availability', $availability, 'http://base.google.com/ns/1.0');

        $preco = number_format($p->valor, 2, '.', '');
        $item->addChild('g:price', $preco . ' BRL', 'http://base.google.com/ns/1.0');

        if (!empty($p->valor_promocional) && $p->valor_promocional > 0) {
            $promo = number_format($p->valor_promocional, 2, '.', '');
            $item->addChild('g:sale_price', $promo . ' BRL', 'http://base.google.com/ns/1.0');
        }

        $item->addChild('g:condition', 'new', 'http://base.google.com/ns/1.0');

        $item->addChild('g:brand', $config->nome ?? 'Minha Loja', 'http://base.google.com/ns/1.0');
        $item->addChild('g:mpn', 'SKU_' . $p->id, 'http://base.google.com/ns/1.0');
        $item->addChild('g:quantity_to_sell_on_facebook', $quantidade, 'http://base.google.com/ns/1.0');

        if ($p->categoria) {
            $item->addChild('g:product_type', htmlspecialchars($p->categoria->nome, ENT_XML1 | ENT_COMPAT, 'UTF-8'), 'http://base.google.com/ns/1.0');
        }
    }

    return response($xml->asXML(), 200)
        ->header('Content-Type', 'application/xml');
}
	private function categoriaDestesProdutos($produtos)
	{
		$categorias = [];
		foreach ($produtos as $p) {
			if (!in_array($p->categoria_id, $categorias)) {
				array_push($categorias, $p->categoria_id);
			}
		}
		$objeto = [];
		foreach ($categorias as $c) {
			$categoria = CategoriaProdutoEcommerce::find($c);
			array_push($objeto, $categoria);
		}
		return $objeto;
	}

	private function getConfig($link)
	{
		$config = ConfigEcommerce::where('link', $link)
		->first();
		return $config;
	}

	private function getConfigEmpresaId($id)
	{
		$config = ConfigEcommerce::where('empresa_id', $id)
		->first();
		return $config;
	}

	private function getActive()
	{
		$uri = $_SERVER['REQUEST_URI'];
		$uri = explode("/", $uri);
		$active = "";
		if (isset($uri[3])) {
			if ($uri[3] == 'categorias') $active = 'categorias';
			elseif ($uri[3] == '1') $active = 'categorias';
			elseif ($uri[3] == '2') $active = 'categorias';
			// elseif($uri[3] == 'carrinho') $active = 'categorias';
			elseif ($uri[3] == 'contato') $active = 'contato';
			elseif ($uri[3] == 'blog') $active = 'blog';
			// echo $uri[3];
		} else {
			$active = "home";
		}
		return $active;
	}

	private function getDadosDefault($link)
	{
		$config = $this->getConfig($link);
		$categorias = CategoriaProdutoEcommerce::where('empresa_id', $config->empresa_id)
		->get();
		$produtoEcommerceHelper = new PedidoEcommerceHelper();
		$carrinho = $produtoEcommerceHelper->getCarrinho();
		$curtidas = $produtoEcommerceHelper->getProdutosCurtidos();
		$postBlogExists = PostBlogEcommerce::where('empresa_id', $config->empresa_id)
		->exists();
		$active = $this->getActive();

		return [
			'config' => $config,
			'template' => $config->tema_ecommerce,
			'categorias' => $categorias,
			'curtidas' => $curtidas,
			'active' => $active,
			'carrinho' => $carrinho,
			'postBlogExists' => $postBlogExists,
			'rota' => '/loja/' . strtolower($config->link)
		];
	}

	public function categorias($link)
	{
		$link = strtolower($link);
		$dadosDefault = $this->getDadosDefault($link);
		$categorias = CategoriaProdutoEcommerce::where('empresa_id', $dadosDefault['config']->empresa_id)
		->get();
		return view($dadosDefault['template'] . '/categorias')
		->with('default', $dadosDefault)
		->with('categorias', $categorias)
		->with('rota', $dadosDefault['rota'])
		->with('title', 'Categorias');
	}

	public function produtosDaCategoria($link, $id)
	{
		$link = strtolower($link);
		$dadosDefault = $this->getDadosDefault($link);

		$produtos = ProdutoEcommerce::select('produto_ecommerces.*')
		->where('produto_ecommerces.empresa_id', $dadosDefault['config']->empresa_id)
		->where('produto_ecommerces.categoria_id', $id)
		->join(
			'categoria_produto_ecommerces',
			'categoria_produto_ecommerces.id',
			'=',
			'produto_ecommerces.categoria_id'
		)
		->join('produtos', 'produtos.id', '=', 'produto_ecommerces.produto_id')
		->where('produto_ecommerces.status', 1)
		->groupBy('produtos.referencia_grade')
		->paginate(15);

		$this->preparaValorDesconto($produtos, $dadosDefault['config']);

		$temp = [];
		foreach ($produtos as $p) {
			if (sizeof($p->galeria) > 0) {
				array_push($temp, $p);
			}
		}
		$produtos = $temp;
		$produtos = $this->limitaEstoque($produtos);
		$categoria = CategoriaProdutoEcommerce::find($id);
		return view($dadosDefault['template'] . '/produtos_categoria')
		->with('default', $dadosDefault)
		->with('produtos', $produtos)
		->with('categoria', $categoria)
		->with('shop', true)
		->with('rota', $dadosDefault['rota'])
		->with('title', 'Produtos por categoria');
	}

	public function produtosDaSubCategoria($link, $id)
	{
		$link = strtolower($link);
		$dadosDefault = $this->getDadosDefault($link);

		$produtos = ProdutoEcommerce::select('produto_ecommerces.*')
		->where('produto_ecommerces.empresa_id', $dadosDefault['config']->empresa_id)
		->where('produto_ecommerces.sub_categoria_id', $id)
		->join(
			'categoria_produto_ecommerces',
			'categoria_produto_ecommerces.id',
			'=',
			'produto_ecommerces.categoria_id'
		)
		->join('produtos', 'produtos.id', '=', 'produto_ecommerces.produto_id')
		->where('produto_ecommerces.status', 1)
		->groupBy('produtos.referencia_grade')
		->paginate(15);

		$this->preparaValorDesconto($produtos, $dadosDefault['config']);

		$temp = [];
		foreach ($produtos as $p) {
			if (sizeof($p->galeria) > 0) {
				array_push($temp, $p);
			}
		}

		$produtos = $temp;

		$produtos = $this->limitaEstoque($produtos);

		$subcategoria = SubCategoriaEcommerce::find($id);

		return view($dadosDefault['template'] . '/produtos_categoria')
		->with('default', $dadosDefault)
		->with('produtos', $produtos)
		->with('subcategoria', $subcategoria)
		->with('shop', true)
		->with('rota', $dadosDefault['rota'])
		->with('title', 'Produtos por categoria');
	}

	private function preparaValorDesconto($produtos, $config)
	{
		foreach ($produtos as $p) {
			if ($config->desconto_padrao_pix > 0) {
				$p->valor_pix = $p->valor - (($p->valor * $config->desconto_padrao_pix) / 100);
			}
			if ($config->desconto_padrao_cartao > 0) {
				$p->valor_cartao = $p->valor -
				(($p->valor * $config->desconto_padrao_cartao) / 100);
			}
			if ($config->desconto_padrao_boleto > 0) {
				$p->valor_boleto = $p->valor -
				(($p->valor * $config->desconto_padrao_boleto) / 100);
			}
		}
		return $produtos;
	}

	public function blog($link)
	{
		$config = $this->getConfig($link);
		$dadosDefault = $this->getDadosDefault($link);

		$categoriasPost = CategoriaPostBlogEcommerce::where('empresa_id', $config->empresa_id)
		->get();

		$posts = PostBlogEcommerce::where('empresa_id', $config->empresa_id)
		->orderBy('id', 'desc')
		->limit(6)
		->get();

		$postsRecentes = PostBlogEcommerce::where('empresa_id', $config->empresa_id)
		->orderBy('id', 'desc')
		->limit(6)
		->get();

		return view($dadosDefault['template'] . '/blog_index')
		->with('default', $dadosDefault)
		->with('posts', $posts)
		->with('blog', true)
		->with('postsRecentes', $postsRecentes)
		->with('rota', $dadosDefault['rota'])
		->with('categoriasPost', $categoriasPost)
		->with('title', 'Blog');
	}

	public function postsCategoria($link, $categoria)
	{
		$config = $this->getConfig($link);
		$dadosDefault = $this->getDadosDefault($link);

		$categoriasPost = CategoriaPostBlogEcommerce::where('empresa_id', $config->empresa_id)
		->get();

		$posts = PostBlogEcommerce::where('empresa_id', $config->empresa_id)
		->orderBy('id', 'desc')
		->limit(6)
		->get();

		$postsRecentes = PostBlogEcommerce::where('empresa_id', $config->empresa_id)
		->where('categoria_id', $categoria)
		->orderBy('id', 'desc')
		->limit(6)
		->get();

		return view($dadosDefault['template'] . '/blog_index')
		->with('default', $dadosDefault)
		->with('posts', $posts)
		->with('blog', true)
		->with('postsRecentes', $postsRecentes)
		->with('rota', $dadosDefault['rota'])
		->with('categoriasPost', $categoriasPost)
		->with('title', 'Blog');
	}

	public function contato($link)
	{
		$config = $this->getConfig($link);
		$dadosDefault = $this->getDadosDefault($link);

		return view($dadosDefault['template'] . '/contato')
		->with('default', $dadosDefault)
		->with('contato', true)
		->with('rota', $dadosDefault['rota'])
		->with('title', 'Contato');
	}

	public function verPost($link, $postId)
	{
		$config = $this->getConfig($link);
		$dadosDefault = $this->getDadosDefault($link);

		$post = PostBlogEcommerce::find($postId);
		$categoriasPost = CategoriaPostBlogEcommerce::where('empresa_id', $config->empresa_id)
		->get();

		$postsRecentes = PostBlogEcommerce::where('empresa_id', $config->empresa_id)
		->orderBy('id', 'desc')
		->limit(6)
		->get();

		return view($dadosDefault['template'] . '/blog_view_post')
		->with('default', $dadosDefault)
		->with('post', $post)
		->with('blog', true)
		->with('postsRecentes', $postsRecentes)
		->with('rota', $dadosDefault['rota'])
		->with('categoriasPost', $categoriasPost)
		->with('title', 'Blog - ' . $post->titulo);
	}

	public function saveContato(Request $request)
	{
		$empresaId = $request->empresa_id;
		$this->_validate($request);

		$contato = ContatoEcommerce::create(
			$request->all()
		);
		if ($contato) {
			session()->flash('flash_sucesso', 'Obrigado por entrar em contato, em breve retornaremos!');
		} else {
			session()->flash('flash_erro', 'Erro!');
		}

		return redirect()->back();
	}

	private function _validate(Request $request)
	{
		$doc = $request->cpf_cnpj;

		$rules = [
			'nome' => 'required|max:50',
			'email' => 'required|max:100|email',
			'texto' => 'required',
		];

		$messages = [
			'nome.required' => 'O campo nome é obrigatório.',
			'nome.max' => '50 caracteres maximos permitidos.',
			'email.required' => 'O campo email é obrigatório.',
			'email.max' => '100 caracteres maximos permitidos.',
			'email.email' => 'Email inválido.',
			'texto.required' => 'O campo texto é obrigatório.',

		];

		$this->validate($request, $rules, $messages);
	}

	public function saveInformativo(Request $request)
	{

		$this->_validateInfo($request);

		$c = InformativoEcommerce::where('email', $request->email_info)
		->where('empresa_id', $request->empresa_id)
		->first();

		if ($c != null) {
			session()->flash('flash_sucesso', 'Seu email já está incluído, obrigado por se inscrever!');
			return redirect()->back();
		}

		$contato = InformativoEcommerce::create(
			[
				'email' => $request->email_info,
				'empresa_id' => $request->empresa_id
			]
		);
		if ($contato) {
			session()->flash('flash_sucesso', 'Obrigado por se inscrever!');
		} else {
			session()->flash('flash_erro', 'Erro!');
		}

		return redirect()->back();
	}

	private function _validateInfo(Request $request)
	{
		$doc = $request->cpf_cnpj;

		$rules = [
			'email_info' => 'required|max:100|email'
		];

		$messages = [
			'email_info.required' => 'O campo email é obrigatório.',
			'email_info.max' => '100 caracteres maximos permitidos.',
			'email_info.email' => 'Email inválido.'
		];

		$this->validate($request, $rules, $messages);
	}

	private function preparaValorDescontoSingle($p, $config)
	{

		if ($config->desconto_padrao_pix > 0) {
			$p->valor_pix = $p->valor - (($p->valor * $config->desconto_padrao_pix) / 100);
		}
		if ($config->desconto_padrao_cartao > 0) {
			$p->valor_cartao = $p->valor -
			(($p->valor * $config->desconto_padrao_cartao) / 100);
		}
		if ($config->desconto_padrao_boleto > 0) {
			$p->valor_boleto = $p->valor -
			(($p->valor * $config->desconto_padrao_boleto) / 100);
		}
		return $p;
	}

	public function verProduto($link, $produtoId)
	{
		$config = $this->getConfig($link);
		$dadosDefault = $this->getDadosDefault($link);

		$produto = ProdutoEcommerce::find($produtoId);

		if ($produto == null) {
			session()->flash('flash_erro', 'Algo deu errado');
		}

		$curtida = false;
		$produtoEcommerceHelper = new PedidoEcommerceHelper();
		$user = $produtoEcommerceHelper->getUserLogado();

		if ($user) {
			$curtida = CurtidaProdutoEcommerce::where('produto_id', $produtoId)
			->where('cliente_id', $user['cliente_id'])
			->first();

			if ($curtida != null) {
				$curtida = true;
			}
		}

		$variacoes = [];

		$categoria = CategoriaProdutoEcommerce::find($produto->categoria_id);

		if ($produto->produto->grade) {
			$variacoes = ProdutoEcommerce::select('produto_ecommerces.*')
			->join('produtos', 'produtos.id', '=', 'produto_ecommerces.produto_id')
			->where('produtos.referencia_grade', $produto->produto->referencia_grade)
			->get();
			$produto = $this->preparaValorDescontoSingle($produto, $dadosDefault['config']);
			if (sizeof($produto->galeria) == 0) {
				return redirect('/loja/' . $link);
			}

			return view($dadosDefault['template'] . '/produto_view_grade')
			->with('default', $dadosDefault)
			->with('produto', $produto)
			->with('variacoes', $variacoes)
			->with('curtida', $curtida)
			->with('product', true)

			->with('categoria', $categoria)
			->with('rota', $dadosDefault['rota'])
			->with('title', $produto->produto->nome);
		} else {
			$produto = $this->preparaValorDescontoSingle($produto, $dadosDefault['config']);
			return view($dadosDefault['template'] . '/produto_view')
			->with('default', $dadosDefault)
			->with('categoria', $categoria)
			->with('produto', $produto)
			->with('variacoes', $variacoes)
			->with('product', true)
			->with('curtida', $curtida)
			->with('rota', $dadosDefault['rota'])
			->with('title', $produto->produto->nome);
		}
	}

	public function addProduto(Request $request)
	{
		$temEstoque = $this->temEstoqueItem($request->produto_id, $request->quantidade);
		if ($temEstoque) {
			$data = [
				'empresa_id' => $request->empresa_id,
				'produto_id' => $request->produto_id,
				'quantidade' => $request->quantidade
			];
			$produtoEcommerceHelper = new PedidoEcommerceHelper();
			try {
				$produtoEcommerceHelper->addProduto($data);
				session()->flash('flash_sucesso', 'Item adicionado!!');
			} catch (\Exception $e) {
				session()->flash('flash_erro', 'Erro ' . $e->getMessage());
			}

			$config = ConfigEcommerce::where('empresa_id', $request->empresa_id)
			->first();

			return redirect('/loja/' . strtolower($config->link) . '/carrinho');
		} else {
			session()->flash('flash_erro', 'Quantidade em estoque insuficiente!!');
			return redirect()->back();
		}
	}

	private function temEstoqueItem($produtoId, $quantidade)
	{

		$p = ProdutoEcommerce::find($produtoId);
		if ($p->controlar_estoque) {
			if ($p->produto->estoque) {
				if ($p->produto->estoque->quantidade < $quantidade) {
					return false;
				} else {
					return true;
				}
			}
		}
		return "12";
	}

	public function carrinho($link)
	{
		$config = $this->getConfig($link);
		$dadosDefault = $this->getDadosDefault($link);

		$carrinho = $dadosDefault['carrinho'];

		if ($carrinho) {
			$carrinho->valor_frete = 0;
			$carrinho->observacao = "";
			$carrinho->save();
		}

		return view($dadosDefault['template'] . '/carrinho')
		->with('default', $dadosDefault)
		->with('carrinhoJs', true)
		->with('cart', true)
		->with('rota', $dadosDefault['rota'])
		->with('title', 'Carrinho');
	}

	public function curtidas($link)
	{
		$config = $this->getConfig($link);
		$dadosDefault = $this->getDadosDefault($link);

		$produtoEcommerceHelper = new PedidoEcommerceHelper();
		$user = $produtoEcommerceHelper->getUserLogado();

		if ($user == null) {
			session()->flash('flash_erro', 'Faça o login!!');
			return redirect()->back();
		}
		
		$curtidas = CurtidaProdutoEcommerce::
		where('cliente_id', $user['cliente_id'])
		->get();

		return view($dadosDefault['template'] . '/curtidas')
		->with('default', $dadosDefault)
		->with('curtidas', $curtidas)
		->with('cart', true)
		->with('rota', $dadosDefault['rota'])
		->with('title', 'Produtos Favoritos');
	}

	public function deleteItemCarrinho($link, $id)
	{
		try {
			$item = ItemPedidoEcommerce::find($id)->delete();
			session()->flash('flash_sucesso', 'Item removido!!');
		} catch (\Exception $e) {
			session()->flash('flash_erro', 'Erro ao remover item!!');
		}

		return redirect()->back();
	}
public function atualizaItem(Request $request, $link)
{
    // Verifique se o nome do model é exatamente este (ItemPedidoEcommerce)
    // Se estiver em outra pasta, ajuste o namespace
    $item = \App\Models\ItemPedidoEcommerce::find($request->id);

    if (!$item) {
        return response()->json("Item não encontrado!", 404);
    }

    // Validação de estoque
    $temEstoque = $this->temEstoqueItem($item->produto_id, $request->quantidade);
    if (!$temEstoque) {
        return response()->json("Quantidade em estoque insuficiente!!", 400);
    }

    try {
        $item->quantidade = $request->quantidade;
        $item->save();

        // Pega o pedido pai (supondo que a relação se chama 'pedido')
        $pedido = $item->pedido; 
        
        return response()->json([
            // Cálculo do subtotal do item
            'item_subtotal' => number_format($item->quantidade * $item->produto->valor, 2, ',', '.'),
            // Cálculo do total do carrinho (método somaItens deve existir no Model Pedido)
            'carrinho_total' => number_format($pedido->somaItens(), 2, ',', '.')
        ], 200);

    } catch (\Exception $e) {
        return response()->json("Erro ao processar: " . $e->getMessage(), 500);
    }
}
    /**
     * 1. SELEÇÃO DE ENDEREÇO (PÁGINA PRINCIPAL)
     */
    public function endereco(Request $request, $link)
    {
        $dadosDefault = $this->getDadosDefault($link);
        $carrinho = $dadosDefault['carrinho'];
        $tipoFrete = $request->tipo_frete;

        if (!$carrinho) {
            session()->flash('flash_erro', 'Adicione um item ao carrinho!');
            return redirect()->back();
        }

        if (!$carrinho->cliente) {
            session()->flash('flash_erro', 'Realize o login para continuar!');
            return redirect($dadosDefault['rota'] . '/login');
        }

        $cliente = $carrinho->cliente;
        $total = $carrinho->somaItens();
        $enderecos = $cliente->enderecos;

        $config = ConfigEcommerce::where('empresa_id', $carrinho->empresa_id)->first();
        $token = $this->getCorreiosToken($config);

        foreach ($enderecos as $e) {
            $calc = $this->calculaFreteEnderecos($e, $carrinho, $token, $config);

            $e->preco_sedex = $calc['preco_sedex'];
            $e->prazo_sedex = $calc['prazo_sedex'];
            $e->preco = $calc['preco'];
            $e->prazo = $calc['prazo'];

            if ($total > ($config->frete_gratis_valor ?? 999999)) {
                $e->frete_gratis = 1;
            }

            if ($config->habilitar_retirada) {
                $e->habilitar_retirada = 1;
            }
        }

        return view($dadosDefault['template'] . '/selecionar_endereco')
            ->with('default', $dadosDefault)
            ->with('carrinho', $carrinho)
            ->with('enderecos', $enderecos)
            ->with('cliente', $cliente)
            ->with('total', $total)
            ->with('contato', true)
            ->with('tipoFrete', $tipoFrete)
            ->with('rota', $dadosDefault['rota'])
            ->with('title', 'Selecionar o Endereço');
    }

    /**
     * 2. CÁLCULO DE FRETE PARA LISTA DE ENDEREÇOS
     */
    private function calculaFreteEnderecos($endereco, $carrinho, $token, $config)
    {
        if (!$token || !$config) {
            return ['preco_sedex' => '0,00', 'prazo_sedex' => 0, 'preco' => '0,00', 'prazo' => 0];
        }

        $cepOrigem = $config->cep;
        $cepDestino = $endereco->cep;
        $peso = $carrinho->somaPeso();
        $dimensoes = $carrinho->somaDimensoes();
        $idContrato = $config->numero_contrato ?? "";

        $resPac = $this->consultarNovaApiCorreios($token, $cepOrigem, $cepDestino, $peso, $dimensoes, '03298', $idContrato);
        $resSedex = $this->consultarNovaApiCorreios($token, $cepOrigem, $cepDestino, $peso, $dimensoes, '03220', $idContrato);

        $tratar = function($res) {
            if ($res && isset($res['valorCobrado'])) {
                $valorLimpo = str_replace(',', '.', $res['valorCobrado']);
                $valorFloat = (float) filter_var($valorLimpo, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                return [
                    'preco' => number_format($valorFloat, 2, ',', '.'),
                    'prazo' => (string)($res['prazoEntrega'] ?? "0")
                ];
            }
            return ['preco' => '0,00', 'prazo' => '0'];
        };

        $dadosPac = $tratar($resPac);
        $dadosSedex = $tratar($resSedex);

        return [
            'preco'       => $dadosPac['preco'],
            'prazo'       => $dadosPac['prazo'],
            'preco_sedex' => $dadosSedex['preco'],
            'prazo_sedex' => $dadosSedex['prazo']
        ];
    }

    /**
     * 3. GERAÇÃO DE TOKEN (AUTENTICAÇÃO)
     */
    private function getCorreiosToken($config)
    {
        if (!$config) {
            Log::error("Configuração da empresa não encontrada para gerar token Correios.");
            return null;
        }

        $usuario = $config->correios_usuario;
        $senha   = $config->correios_senha;
        $cartao  = $config->correios_cartao;

        if (!$usuario || !$senha || !$cartao) {
            Log::error("Credenciais dos Correios incompletas para empresa ID {$config->empresa_id}");
            return null;
        }

        $accessBasic = base64_encode($usuario . ':' . $senha);
        $url_auth = 'https://api.correios.com.br/token/v1/autentica/cartaopostagem';

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . $accessBasic,
            ])->post($url_auth, ["numero" => (string)$cartao]);

            if ($response->failed()) {
                Log::error("Erro Autenticação Correios: " . $response->body());
                return null;
            }

            $dados = $response->json();
            return $dados['token'] ?? null;
        } catch (\Exception $e) {
            Log::error("Exceção ao gerar token Correios: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 4. CONSULTA API CORREIOS NOVA
     */
    private function consultarNovaApiCorreios($token, $cepOrigem, $cepDestino, $peso, $dimensoes, $servico, $idContrato)
    {
        $cepOrigem  = preg_replace('/\D/', '', $cepOrigem);
        $cepDestino = preg_replace('/\D/', '', $cepDestino);

        $psObjeto = (int) max(100, ceil(($peso > 0 ? $peso : 0.3) * 1000));
        $c = (int) max(15, ($dimensoes['comprimento'] ?? 20));
        $a = (int) max(2, ($dimensoes['altura'] ?? 5));
        $l = (int) max(11, ($dimensoes['largura'] ?? 15));

        if (empty($idContrato)) {
            if ($servico == '03298') $servico = '04510';
            if ($servico == '03220') $servico = '04014';
        }

        $urlPreco = "https://api.correios.com.br/preco/v1/nacional/{$servico}"
            . "?cepDestino={$cepDestino}&cepOrigem={$cepOrigem}&psObjeto={$psObjeto}&formato=1&comprimento={$c}&altura={$a}&largura={$l}&diametro=0";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $urlPreco,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                "Accept: application/json"
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $resPreco = curl_exec($ch);
        $httpCodePreco = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $dadosPreco = json_decode($resPreco, true);

        $urlPrazo = "https://api.correios.com.br/prazo/v1/nacional/{$servico}?cepOrigem={$cepOrigem}&cepDestino={$cepDestino}";

        $ch = curl_init($urlPrazo);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                "Accept: application/json"
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $resPrazo = curl_exec($ch);
        $dadosPrazo = json_decode($resPrazo, true);
        curl_close($ch);

        if ($httpCodePreco === 200) {
            return [
                'valorCobrado' => $dadosPreco['pcFinal'] ?? 0,
                'prazoEntrega' => $dadosPrazo['prazoEntrega'] ?? 0
            ];
        }

        return null;
    }

    /**
     * 5. ENDPOINT PRINCIPAL DE CÁLCULO
     */
    public function calculaFrete(Request $request, $link)
    {
        $pedido = PedidoEcommerce::find($request->pedido_id);
        if (!$pedido) return response()->json(['error' => 'Pedido não encontrado'], 404);

        $config = ConfigEcommerce::where('empresa_id', $pedido->empresa_id)->first();
        $token = $this->getCorreiosToken($config);

        if (env("CEP_PRODUTO_ECOMMERCE") != 0) {
            return response()->json($this->calculaFreteCepProduto($request, $config, $token), 200);
        }

        return response()->json($this->calculaFreteNormal($request, $config, $token), 200);
    }

    private function calculaFreteNormal($request, $config, $token)
    {
        $pedido = PedidoEcommerce::find($request->pedido_id);
        if (!$pedido) return ['error' => 'Pedido não encontrado'];

        if (!$token) return ['error' => 'Falha na autenticação'];

        $peso = $pedido->somaPeso();
        $dimensoes = $pedido->somaDimensoes();
        $idContrato = $config->numero_contrato ?? "";

        $resPac = $this->consultarNovaApiCorreios($token, $config->cep, $request->cep, $peso, $dimensoes, '03298', $idContrato);
        $resSedex = $this->consultarNovaApiCorreios($token, $config->cep, $request->cep, $peso, $dimensoes, '03220', $idContrato);

        return $this->formatarRespostaFrete($resPac, $resSedex, $pedido, $config);
    }

    private function calculaFreteCepProduto($request, $config, $token)
    {
        $pedido = PedidoEcommerce::find($request->pedido_id);
        if (!$pedido) return ['error' => 'Pedido não encontrado'];

        if (!$token) return ['error' => 'Falha na autenticação'];

        $peso = $pedido->somaPeso();
        $dimensoes = $pedido->somaDimensoes();
        $idContrato = $config->numero_contrato ?? "";
        $cepOrigem = env("CEP_PRODUTO_ECOMMERCE");

        $resPac = $this->consultarNovaApiCorreios($token, $cepOrigem, $request->cep, $peso, $dimensoes, '03298', $idContrato);
        $resSedex = $this->consultarNovaApiCorreios($token, $cepOrigem, $request->cep, $peso, $dimensoes, '03220', $idContrato);

        return $this->formatarRespostaFrete($resPac, $resSedex, $pedido, $config);
    }

    private function formatarRespostaFrete($resPac, $resSedex, $pedido, $config)
    {
        $tratarDados = function($res) {
            if ($res && isset($res['valorCobrado'])) {
                $valorLimpo = str_replace(',', '.', $res['valorCobrado']);
                $valorFloat = (float) filter_var($valorLimpo, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                return [
                    'preco' => number_format($valorFloat, 2, ',', '.'),
                    'prazo' => (string)($res['prazoEntrega'] ?? "0")
                ];
            }
            return ['preco' => '0,00', 'prazo' => '0'];
        };

        $pac = $tratarDados($resPac);
        $sedex = $tratarDados($resSedex);

        $valorTotalItens = (float)($pedido->somaItens() ?? 0);
        $limiteFreteGratis = (float)($config->frete_gratis_valor ?? 999999);

        return [
            'preco'             => $pac['preco'],
            'prazo'             => $pac['prazo'],
            'preco_sedex'       => $sedex['preco'],
            'prazo_sedex'       => $sedex['prazo'],
            'frete_gratis'      => ($valorTotalItens >= $limiteFreteGratis && $limiteFreteGratis > 0),
            'habilitar_retirada' => (int)($config->habilitar_retirada ?? 0)
        ];
    }

    /**
     * 6. SETA FRETE (SALVAR ESCOLHA)
     */
    public function setaFrete(Request $request)
    {
        try {
            $pedido = PedidoEcommerce::findOrFail($request->pedido_id);
            $pedido->tipo_frete = $request->tipo;
            $pedido->observacao = $request->cep;
            $pedido->valor_frete = (float)str_replace(',', '.', $request->valor);
            $pedido->save();

            return response()->json($pedido, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao salvar frete'], 400);
        }
    }

	public function checkout(Request $request, $link)
	{
		$config = $this->getConfig($link);
		$dadosDefault = $this->getDadosDefault($link);

		$tipoFrete = $request->tp_frete;

		if (!session('user_ecommerce')) {
			if ($dadosDefault['carrinho'] == null) {
				return redirect($dadosDefault['rota']);
			}

			$cep = preg_replace("/[^0-9]/", "", $dadosDefault['carrinho']->observacao);
			if ($cep != "") {
				$url = "http://viacep.com.br/ws/$cep/xml/";
				$enderecoCep = simplexml_load_file($url);
			} else {
				$enderecoCep = null;
			}

			return view($dadosDefault['template'] . '/checkout')
			->with('default', $dadosDefault)
			->with('carrinhoJs', true)
			->with('cart', true)
			->with('contato', true)
			->with('enderecoCep', $enderecoCep)
			->with('rota', $dadosDefault['rota'])
			->with('title', 'Checkout');
		} else {
			return redirect($dadosDefault['rota'] . '/endereco?tipo_frete=' . $tipoFrete);
		}
	}

	public function checkoutStore(Request $request)
	{
		$pedido = PedidoEcommerce::find($request->pedido_id);
		$config = $this->getConfigEmpresaId($pedido->empresa_id);

		$this->_validateCheckout($request, $config);


		$dataCliente = [
			'nome' => $request->nome,
			'sobre_nome' => $request->sobre_nome,
			'cpf' => $request->cpf,
			'ie' => $request->ie ?? '',
			'email' => $request->email,
			'telefone' => $request->telefone,
			'senha' => md5($request->senha),
			'status' => 1,
			'token' => Str::random(20),
			'empresa_id' => $request->empresa_id
		];

		$cliente = ClienteEcommerce::create($dataCliente);

		$dataEndereco = [
			'rua' => $request->rua,
			'numero' => $request->numero,
			'bairro' => $request->bairro,
			'cep' => $request->cep,
			'cidade' => $request->cidade,
			'uf' => $request->uf,
			'complemento' => $request->complemento ?? '',
			'cliente_id' => $cliente->id
		];

		$endereco = EnderecoEcommerce::create($dataEndereco);

		$pedido->observacao = $request->observacao ?? '';
		$pedido->cliente_id = $cliente->id;
		$pedido->endereco_id = $endereco->id;

		$pedido->save();

		$produtoEcommerceHelper = new PedidoEcommerceHelper();
		$produtoEcommerceHelper->setUserEcommerce($cliente->id);

		return redirect('/loja/' . strtolower($config->link) . '/endereco');
	}

	private function _validateCheckout(Request $request, $config)
	{
		$doc = $request->cpf_cnpj;

		$rules = [
			'nome' => 'required|max:30',
			'sobre_nome' => 'required|max:30',
			'cpf' => ['required', \Illuminate\Validation\Rule::unique('cliente_ecommerces')->ignore($request->id), new ValidaDocumento],
			'email' => ['required', 'max:60', 'email', new EmailDupEcommerce($config)],
			'senha' => 'required|min:6',
			'ie' => $request->tp_doc == 'cnpj' ? 'required' : '',

			'rua' => 'required|max:60',
			'numero' => 'required|max:10',
			'bairro' => 'required|max:30',
			'cidade' => 'required|max:30',
			'telefone' => 'required|max:15',

			'uf' => 'required|max:2|min:2',
			'cep' => 'required|max:9|min:9',
			'complemento' => 'max:30'
		];

		$messages = [
			'nome.required' => 'O campo nome é obrigatório.',
			'nome.max' => '30 caracteres maximos permitidos.',
			'sobre_nome.required' => 'O campo sobre nome é obrigatório.',
			'sobre_nome.max' => '30 caracteres maximos permitidos.',
			'cpf.required' => 'O campo CPF é obrigatório.',
			'cpf.unique' => 'CPF já cadastrado no sistema.',
			'email.required' => 'O campo email é obrigatório.',
			'email.max' => '60 caracteres maximos permitidos.',
			// 'email.unique' => 'email já cadastrado no sistema.',
			'senha.required' => 'O campo senha é obrigatório.',
			'senha.min' => 'Use uma senha com no minímo 6 caracteres.',

			'telefone.required' => 'O campo telefone é obrigatório.',
			'telefone.max' => '15 caracteres maximos permitidos.',

			'rua.required' => 'O campo rua é obrigatório.',
			'rua.max' => '60 caracteres maximos permitidos.',
			'numero.required' => 'O campo número é obrigatório.',
			'numero.max' => '10 caracteres maximos permitidos.',
			'bairro.required' => 'O campo bairro é obrigatório.',
			'bairro.max' => '30 caracteres maximos permitidos.',
			'cidade.required' => 'O campo cidade é obrigatório.',
			'cidade.max' => '30 caracteres maximos permitidos.',

			'uf.required' => 'O campo uf é obrigatório.',
			'uf.max' => 'UF inválido.',
			'uf.min' => 'UF inválido.',

			'cep.required' => 'O campo CEP é obrigatório.',
			'cep.max' => 'CEP inválido.',
			'cep.min' => 'CEP inválido.',
			'complemento.max' => '30 caracteres maximos permitidos.',
			'ie.required' => 'O campo IE é obrigatório.',
		];

		$this->validate($request, $rules, $messages);
	}

	public function logoff($link)
	{
		$dadosDefault = $this->getDadosDefault($link);
		$produtoEcommerceHelper = new PedidoEcommerceHelper();
		$produtoEcommerceHelper->logoff();

		session()->flash('flash_sucesso', 'Logoff realizado!!');
		return redirect($dadosDefault['rota']);
	}

	// public function pagamento($link){
	// 	$dadosDefault = $this->getDadosDefault($link);
	// 	$carrinho = $dadosDefault['carrinho'];

	// 	$descricao = $this->getDescricao($carrinho);

	// 	$total = $carrinho->somaItens();

	// 	return view('ecommerce/pay')
	// 	->with('default', $dadosDefault)
	// 	->with('carrinho', $carrinho)
	// 	->with('descricao', $descricao)
	// 	->with('total', $total)
	// 	->with('payJs', true)
	// 	->with('rota', $dadosDefault['rota'])
	// 	->with('title', 'Pagamento do Pedido');
	// }

	public function pagamento(Request $request)
	{
		$empresa_id = $request->empresa_id;
		$pedido_id = $request->pedido_id;
		$tipo = $request->tipo;
		$cupom_desconto = $request->cupom_desconto;
		$desconto = $request->desconto;

		$cupom = CupomDescontoEcommerce::where('codigo', $cupom_desconto)
		->where('empresa_id', $empresa_id)
		->first();

		$produtoEcommerceHelper = new PedidoEcommerceHelper();
		$pedido = $produtoEcommerceHelper->getCarrinho();

		if (!$pedido) {
			session()->flash('flash_erro', 'Pedido encerrado!');
			$config = $this->getConfigEmpresaId($empresa_id);
			return redirect('/loja/' . strtolower($config->link));
		}

		if (!$request->endereco) {
			session()->flash('flash_erro', 'Selecione o endereço');
			return redirect()->back();
		}

		$endereco = json_decode($request->endereco);

		$valorFrete = 0;

		if ($tipo == 'sedex') {
			$valorFrete = __convert_value_bd($endereco->preco_sedex);
		} else if ($tipo == 'pac') {
			$valorFrete = __convert_value_bd($endereco->preco);
		}

		$pedido = PedidoEcommerce::find($pedido_id);

		$pedido->endereco_id = $endereco->id;

		$total = $pedido->somaItens();
		$total = $pedido->somaItens() + $valorFrete - $desconto;

		$pedido->valor_frete = $valorFrete;
		$pedido->valor_total = $total;
		$pedido->tipo_frete = $tipo;
		$pedido->desconto = $desconto;
		$pedido->cupom_desconto = $cupom_desconto;

		$pedido->save();

		$config = $this->getConfigEmpresaId($empresa_id);
		$dadosDefault = $this->getDadosDefault($config->link);

		$descricao = $this->getDescricao($pedido);

		$totais = $this->preparaValorTotal($total, $config);

		if ($cupom) {
			CupomDescontoEcommerce::create([
				'cupom_id' => $cupom->id,
				'cliente_id' => $pedido->cliente_id
			]);
		}

		$formas_pagamento = json_decode($config->formas_pagamento);
		$forma_inicial = $formas_pagamento[0];

		$view = $dadosDefault['template'] . '/pay';

		if ($dadosDefault['config']->modelo_orcamento) {
			$view = $dadosDefault['template'] . '/finaliza_orcamento';
		}
		return view($view)
		->with('default', $dadosDefault)
		->with('carrinho', $pedido)
		->with('cliente', $pedido->cliente)
		->with('descricao', $descricao)
		->with('total', $total)
		->with('formas_pagamento', $formas_pagamento)
		->with('forma_inicial', $forma_inicial)
		->with('totais', $totais)
		->with('payJs', true)
		->with('cart', true)
		->with('rota', $dadosDefault['rota'])
		->with('title', 'Pagamento do Pedido');
	}

	private function preparaValorTotal($total, $config)
	{
		$vTotal = new \stdClass();
		if ($config->desconto_padrao_pix > 0) {
			$vTotal->total_pix = number_format($total - (($total * $config->desconto_padrao_pix) / 100), 2, '.', '');
		} else {
			$vTotal->total_pix = $total;
		}

		if ($config->desconto_padrao_cartao > 0) {
			$vTotal->total_cartao = number_format($total - (($total * $config->desconto_padrao_cartao) / 100), 2, '.', '');
		} else {
			$vTotal->total_cartao = $total;
		}

		if ($config->desconto_padrao_boleto > 0) {
			$vTotal->total_boleto = number_format($total - (($total * $config->desconto_padrao_boleto) / 100), 2, '.', '');
		} else {
			$vTotal->total_boleto = $total;
		}
		return $vTotal;
	}

	private function getDescricao($carrinho)
	{
		$descricao = "";

		foreach ($carrinho->itens as $i) {
			$descricao .= "$i->quantidade x " . $i->produto->produto->nome . " ";
		}

		return $descricao;
	}

	public function login($link)
	{
		$dadosDefault = $this->getDadosDefault($link);

		if (!session('user_ecommerce')) {
			return view($dadosDefault['template'] . '/login')
			->with('default', $dadosDefault)
			->with('contato', true)
			->with('rota', $dadosDefault['rota'])
			->with('title', 'Login');
		} else {
			$id = session('user_ecommerce')['cliente_id'];
			$cliente = ClienteEcommerce::find($id);
			return view($dadosDefault['template'] . '/area_cliente')
			->with('default', $dadosDefault)
			->with('cliente', $cliente)
			->with('contato', true)

			->with('rota', $dadosDefault['rota'])
			->with('title', 'Area do Cliente');
		}
	}

	public function loginPost(Request $request)
	{

		$cliente = ClienteEcommerce::where('email', $request->email)
		->where('empresa_id', $request->empresa_id)
		->where('senha', md5($request->senha))
		->first();

		if ($cliente == null) {
			session()->flash('flash_erro', 'Email e/ou senha não encontrado!');
			return redirect()->back();
		}

		$produtoEcommerceHelper = new PedidoEcommerceHelper();
		$pedido = $produtoEcommerceHelper->getCarrinho();
		if ($pedido) {

			$pedido->cliente_id = $cliente->id;
			$pedido->save();
		}
		$produtoEcommerceHelper->setUserEcommerce($cliente->id);

		$config = $this->getConfigEmpresaId($cliente->empresa_id);

		session()->flash('flash_sucesso', "Bem vindo(a) $cliente->nome!");
		return redirect('/loja/' . strtolower($config->link));
	}

	public function esquecisenha($link)
	{
		$dadosDefault = $this->getDadosDefault($link);

		return view($dadosDefault['template'] . '/esquecisenha')
		->with('default', $dadosDefault)
		->with('contato', true)
		->with('rota', $dadosDefault['rota'])
		->with('title', 'Login');
	}

	public function esquecisenhaPost(Request $request)
	{

		$cliente = ClienteEcommerce::where('email', $request->email)
		->first();

		session()->flash('flash_sucesso', 'Se o email existir foi enviado uma nova senha para você!');


		if ($cliente != null) {
			$this->sendEmail($cliente);

			$config = ConfigEcommerce::where('empresa_id', $cliente->empresa_id)
			->first();

			return redirect("/loja/$config->link");
		} else {
			return redirect()->back();
		}
	}

	private function sendEmail($cliente)
	{

		$senha = Str::random(4);
		$cliente->senha = md5($senha);
		$cliente->save();
		$config = ConfigEcommerce::where('empresa_id', $cliente->empresa_id)
		->first();

		Mail::send('mail.esqueci_senha', [
			'senha' => $senha, 'nome' => $cliente->nome,
			'empresa' => $config->nome
		], function ($m) use ($cliente, $config) {

			$nomeEmail = $config->nome;
			$m->from(env('MAIL_USERNAME'), $nomeEmail);
			$m->subject('Recuperação de senha');
			$m->to($cliente->email);
		});
	}

	public function ecommerceUpdateCliente(Request $request)
	{
		$cliente = ClienteEcommerce::find($request->id);
		$cliente->nome = $request->nome;
		$cliente->sobre_nome = $request->sobre_nome;
		$cliente->telefone = $request->telefone;
		$cliente->email = $request->email;

		$cliente->save();
		session()->flash('flash_sucesso', 'Dados alterados!');
		return redirect()->back();
	}

	public function ecommerceUpdateSenha(Request $request)
	{

		if ($request->senha != $request->repita_senha) {
			session()->flash('flash_erro', 'Senhas digitas não coincidem!');
		} else {
			$cliente = ClienteEcommerce::find($request->id);
			$cliente->senha = md5($request->senha);
			$cliente->save();
			session()->flash('flash_sucesso', 'Senha alterada!');
		}
		return redirect()->back();
	}

	public function ecommerceSaveEndereco(Request $request)
	{

		try {

			if ($request->endereco_id == 0) {
				$data = [
					'rua' => $request->rua,
					'numero' => $request->numero,
					'bairro' => $request->bairro,
					'cep' => $request->cep,
					'cidade' => $request->cidade,
					'uf' => $request->uf,
					'complemento' => $request->complemento ?? '',
					'cliente_id' => $request->id
				];

				$endereco = EnderecoEcommerce::create($data);

				session()->flash('flash_sucesso', 'Endereço cadastrado!');
			} else {

				$endereco = EnderecoEcommerce::find($request->endereco_id);

				$endereco->rua = $request->rua;
				$endereco->numero = $request->numero;
				$endereco->bairro = $request->bairro;
				$endereco->cep = $request->cep;
				$endereco->cidade = $request->cidade;
				$endereco->uf = $request->uf;
				$endereco->complemento = $request->complemento ?? '';

				$endereco->save();
				session()->flash('flash_sucesso', 'Endereço atualizado!');
			}
		} catch (\Exception $e) {
			session()->flash('flash_erro', 'Erro ao cadastrar endereço!');
		}

		return redirect()->back();
	}



	public function curtirProduto($link, $id)
	{
		$link = strtolower($link);
		$dadosDefault = $this->getDadosDefault($link);

		$produtoEcommerceHelper = new PedidoEcommerceHelper();
		$user = $produtoEcommerceHelper->getUserLogado();

		if ($user == null) {
			session()->flash('flash_erro', 'Faça o login para adicionar aos favoritos!');
			return redirect('/loja/' . $link . '/login');
		}

		try {
			$produto = ProdutoEcommerce::find($id);

			$c = CurtidaProdutoEcommerce::where('produto_id', $produto->id)
			->where('cliente_id', $user['cliente_id'])
			->first();

			if ($c != null) {
				$c->delete();
				session()->flash('flash_erro', $produto->produto->nome . " removido da sua lista de favoritos!");
			} else {
				$data = [
					'produto_id' => $produto->id,
					'cliente_id' => $user['cliente_id']
				];
				CurtidaProdutoEcommerce::create($data);
				session()->flash('flash_sucesso', $produto->produto->nome . " adicionado a sua lista de favoritos!");
			}
		} catch (\Exception $e) {
			session()->flash('flash_erro', 'Erro: ' . $e->getMessage());
		}
		return redirect()->back();
	}

	public function pedidoDetalhe($link, $id)
	{
		$produtoEcommerceHelper = new PedidoEcommerceHelper();
		$user = $produtoEcommerceHelper->getUserLogado();
		$pedido = PedidoEcommerce::where('id', $id)
		->where('cliente_id', $user['cliente_id'])
		->first();

		if ($pedido == null) {
			session()->flash('flash_erro', 'Nada encontrado!');
			return redirect()->back();
		}

		$dadosDefault = $this->getDadosDefault($link);

		$config = $this->getConfig($link);

		$default = $this->getDadosDefault($link);

		return view($dadosDefault['template'] . '/pedido_detalhe')
		->with('pedido', $pedido)
		->with('default', $default)
		->with('cart', true)
		->with('rota', $default['rota'])
		->with('title', 'Detalhes do pedido');
	}

	public function pesquisa(Request $request, $link)
	{
		$link = strtolower($link);
		$dadosDefault = $this->getDadosDefault($link);

		$produtos = ProdutoEcommerce::select('produto_ecommerces.*')
		->join('produtos', 'produtos.id', '=', 'produto_ecommerces.produto_id')
		->where('produtos.nome', 'LIKE', "%$request->pesquisa%")
		->where('produto_ecommerces.empresa_id', $dadosDefault['config']->empresa_id)
		->join(
			'categoria_produto_ecommerces',
			'categoria_produto_ecommerces.id',
			'=',
			'produto_ecommerces.categoria_id'
		)
		->where('produto_ecommerces.status', 1)
		->paginate(15);

		return view($dadosDefault['template'] . '/produtos_categoria')
		->with('default', $dadosDefault)
		->with('produtos', $produtos)
		->with('pesquisa', $request->pesquisa)
		->with('categoria', null)
		->with('shop', true)

		->with('rota', $dadosDefault['rota'])
		->with('title', 'Pesquisa');
	}

	public function buscaCupomEcommerce(Request $request)
	{
		$cupom_desconto = $request->cupom_desconto;
		$empresa_id = $request->empresa_id;
		$cliente_id = $request->cliente_id;
		$pedido_id = $request->pedido_id;
		try {
			$item = CupomDescontoEcommerce::where('empresa_id', $empresa_id)
			->where('codigo', $cupom_desconto)
			->first();

			$usado = CupomDescontoEcommerce::where('cliente_id', $cliente_id)
			->where('cupom_id', $item->id)
			->first();

			$pedido = PedidoEcommerce::find($pedido_id);

			if (!$item->status) {
				return response()->json("Cupom inátivo!", 401);
			}

			$total = $pedido->somaItens();

			if ($total < $item->valor_minimo_pedido) {
				return response()->json(
					"Valor minímo para o pedido de R$" . __moeda($item->valor_minimo_pedido),
					401
				);
			}

			if ($usado == null) {
				$desconto = $total * $item->valor / 100;
				if ($item->tipo == 'fixo') {
					$desconto = $item->valor;
				}
				return response()->json($desconto, 200);
			} else {
				return response()->json("Cupom já foi utilizado!", 401);
			}
		} catch (\Exception $e) {
			return response()->json($e->getMessage(), 404);
		}
	}
}
