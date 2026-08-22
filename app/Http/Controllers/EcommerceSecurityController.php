<?php

namespace App\Http\Controllers;

use App\Helpers\PedidoEcommerceHelper;
use App\Models\CategoriaProdutoEcommerce;
use App\Models\ClienteEcommerce;
use App\Models\ConfigEcommerce;
use App\Models\EnderecoEcommerce;
use App\Models\ItemPedidoEcommerce;
use App\Models\PedidoEcommerce;
use App\Models\PostBlogEcommerce;
use App\Models\ProdutoEcommerce;
use App\Models\SubCategoriaEcommerce;
use App\Services\EcommerceStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EcommerceSecurityController extends Controller
{
    public function __construct(private EcommerceStockService $stockService)
    {
    }

    private function legacy(): EcommerceController
    {
        return app(EcommerceController::class);
    }

    private function configLoja(?string $link = null): ConfigEcommerce
    {
        if ($link) {
            return ConfigEcommerce::where('link', strtolower($link))->firstOrFail();
        }

        $empresaId = (int) (session('user_ecommerce')['empresa_id'] ?? 0);
        abort_if($empresaId <= 0, 403, 'Sessão da loja inválida.');

        return ConfigEcommerce::where('empresa_id', $empresaId)->firstOrFail();
    }

    private function clienteLogado(ConfigEcommerce $config): ClienteEcommerce
    {
        $sessao = session('user_ecommerce');
        abort_if(!$sessao, 403, 'Faça login para continuar.');
        abort_if((int) ($sessao['empresa_id'] ?? 0) !== (int) $config->empresa_id, 403, 'Sessão inválida para esta loja.');

        return ClienteEcommerce::where('id', $sessao['cliente_id'])
            ->where('empresa_id', $config->empresa_id)
            ->where('status', 1)
            ->firstOrFail();
    }

    public function login($link)
    {
        $config = $this->configLoja($link);
        $sessao = session('user_ecommerce');

        if ($sessao && (int) ($sessao['empresa_id'] ?? 0) !== (int) $config->empresa_id) {
            session()->forget('user_ecommerce');
        }

        return $this->legacy()->login($link);
    }

    public function loginPost(Request $request, $link)
    {
        $config = $this->configLoja($link);

        $request->validate([
            'email' => ['required', 'email'],
            'senha' => ['required', 'string'],
        ]);

        $cliente = ClienteEcommerce::where('email', $request->email)
            ->where('empresa_id', $config->empresa_id)
            ->where('status', 1)
            ->first();

        if (!$cliente || !$cliente->senhaConfere($request->senha)) {
            return back()->withInput($request->only('email'))
                ->with('flash_erro', 'E-mail e/ou senha inválidos.');
        }

        $helper = new PedidoEcommerceHelper();
        $pedidoTemporario = $helper->getCarrinho($config->empresa_id);

        if ($pedidoTemporario && !$pedidoTemporario->cliente_id) {
            $pedidoTemporario->cliente_id = $cliente->id;
            $pedidoTemporario->save();
        }

        $helper->setUserEcommerce($cliente->id, $config->empresa_id);

        session()->flash('flash_sucesso', "Bem-vindo(a), {$cliente->nome}!");
        return redirect('/loja/' . strtolower($config->link));
    }

    public function logoff($link)
    {
        $config = $this->configLoja($link);
        (new PedidoEcommerceHelper())->logoff();

        return redirect('/loja/' . strtolower($config->link));
    }

    public function esquecisenhaPost(Request $request, $link)
    {
        $config = $this->configLoja($link);
        $request->validate(['email' => ['required', 'email']]);

        $cliente = ClienteEcommerce::where('email', $request->email)
            ->where('empresa_id', $config->empresa_id)
            ->where('status', 1)
            ->first();

        session()->flash('flash_sucesso', 'Se o e-mail existir nesta loja, uma nova senha temporária será enviada.');

        if (!$cliente) {
            return back();
        }

        $senhaTemporaria = Str::random(12);
        $cliente->senha = Hash::make($senhaTemporaria);
        $cliente->save();

        try {
            Mail::send('mail.esqueci_senha', [
                'senha' => $senhaTemporaria,
                'nome' => $cliente->nome,
                'empresa' => $config->nome,
            ], function ($m) use ($cliente, $config) {
                $m->from(env('MAIL_USERNAME'), $config->nome);
                $m->subject('Recuperação de senha');
                $m->to($cliente->email);
            });
        } catch (\Throwable $e) {
            report($e);
            return back()->with('flash_erro', 'Não foi possível enviar o e-mail de recuperação agora.');
        }

        return redirect('/loja/' . strtolower($config->link) . '/login');
    }

    public function ecommerceUpdateCliente(Request $request, $link = null)
    {
        $config = $this->configLoja($link);
        $cliente = $this->clienteLogado($config);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:80'],
            'sobre_nome' => ['required', 'string', 'max:80'],
            'telefone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:150'],
        ]);

        $emailExiste = ClienteEcommerce::where('empresa_id', $config->empresa_id)
            ->where('email', $validated['email'])
            ->where('id', '<>', $cliente->id)
            ->exists();

        if ($emailExiste) {
            return back()->with('flash_erro', 'Este e-mail já está sendo usado por outro cliente desta loja.');
        }

        $cliente->fill($validated)->save();
        return back()->with('flash_sucesso', 'Dados alterados com sucesso!');
    }

    public function ecommerceUpdateSenha(Request $request, $link = null)
    {
        $config = $this->configLoja($link);
        $cliente = $this->clienteLogado($config);

        $request->validate([
            'senha' => ['required', 'string', 'min:8', 'max:72'],
            'repita_senha' => ['required', 'same:senha'],
        ], [
            'senha.min' => 'A senha deve possuir pelo menos 8 caracteres.',
            'repita_senha.same' => 'As senhas digitadas não coincidem.',
        ]);

        $cliente->senha = Hash::make($request->senha);
        $cliente->save();

        return back()->with('flash_sucesso', 'Senha alterada com sucesso!');
    }

    public function ecommerceSaveEndereco(Request $request, $link = null)
    {
        $config = $this->configLoja($link);
        $cliente = $this->clienteLogado($config);

        $data = $request->validate([
            'rua' => ['required', 'string', 'max:120'],
            'numero' => ['required', 'string', 'max:20'],
            'bairro' => ['required', 'string', 'max:80'],
            'cep' => ['required', 'string', 'max:10'],
            'cidade' => ['required', 'string', 'max:80'],
            'uf' => ['required', 'string', 'size:2'],
            'complemento' => ['nullable', 'string', 'max:120'],
            'endereco_id' => ['nullable', 'integer'],
        ]);

        $data['cliente_id'] = $cliente->id;
        $enderecoId = (int) ($data['endereco_id'] ?? 0);
        unset($data['endereco_id']);

        if ($enderecoId > 0) {
            $endereco = EnderecoEcommerce::where('id', $enderecoId)
                ->where('cliente_id', $cliente->id)
                ->firstOrFail();
            $endereco->fill($data)->save();
            return back()->with('flash_sucesso', 'Endereço atualizado!');
        }

        EnderecoEcommerce::create($data);
        return back()->with('flash_sucesso', 'Endereço cadastrado!');
    }

    public function addProduto(Request $request, $link)
    {
        $config = $this->configLoja($link);

        $request->validate([
            'produto_id' => ['required', 'integer'],
            'quantidade' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $produto = ProdutoEcommerce::with('produto')
            ->where('id', $request->produto_id)
            ->where('empresa_id', $config->empresa_id)
            ->where('status', 1)
            ->firstOrFail();

        abort_if(!$produto->produto || $produto->produto->inativo, 404, 'Produto indisponível.');

        $request->merge([
            'empresa_id' => $config->empresa_id,
            'produto_id' => $produto->id,
            'quantidade' => max(1, (int) $request->quantidade),
        ]);

        return $this->legacy()->addProduto($request);
    }

    public function deleteItemCarrinho($link, $id)
    {
        $config = $this->configLoja($link);
        $helper = new PedidoEcommerceHelper();
        $carrinho = $helper->getCarrinho($config->empresa_id);

        abort_if(!$carrinho, 404);

        $item = ItemPedidoEcommerce::where('id', $id)
            ->where('pedido_id', $carrinho->id)
            ->firstOrFail();

        $item->delete();
        return back()->with('flash_sucesso', 'Item removido!');
    }

    public function atualizaItem(Request $request, $link)
    {
        $config = $this->configLoja($link);
        $helper = new PedidoEcommerceHelper();
        $carrinho = $helper->getCarrinho($config->empresa_id);

        abort_if(!$carrinho, 404);

        $request->validate([
            'id' => ['required', 'integer'],
            'quantidade' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $item = ItemPedidoEcommerce::where('id', $request->id)
            ->where('pedido_id', $carrinho->id)
            ->firstOrFail();

        $produto = ProdutoEcommerce::where('id', $item->produto_id)
            ->where('empresa_id', $config->empresa_id)
            ->where('status', 1)
            ->firstOrFail();

        try {
            $this->stockService->validarQuantidade($produto, (float) $request->quantidade, $carrinho->id);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $item->quantidade = (int) $request->quantidade;
        $item->save();

        return response()->json([
            'item_subtotal' => number_format($item->quantidade * $produto->valor, 2, ',', '.'),
            'carrinho_total' => number_format($carrinho->fresh()->somaItens(), 2, ',', '.'),
        ]);
    }

    public function produtosDaCategoria($link, $id)
    {
        $config = $this->configLoja($link);
        CategoriaProdutoEcommerce::where('id', $id)
            ->where('empresa_id', $config->empresa_id)
            ->firstOrFail();

        $view = $this->legacy()->produtosDaCategoria($link, $id);
        return $this->anotarDisponibilidade($view);
    }

    public function produtosDaSubCategoria($link, $id)
    {
        $config = $this->configLoja($link);
        SubCategoriaEcommerce::where('id', $id)
            ->whereHas('categoria', fn ($q) => $q->where('empresa_id', $config->empresa_id))
            ->firstOrFail();

        $view = $this->legacy()->produtosDaSubCategoria($link, $id);
        return $this->anotarDisponibilidade($view);
    }

    public function pesquisa(Request $request, $link)
    {
        $config = $this->configLoja($link);
        $request->validate([
            'pesquisa' => ['required', 'string', 'min:2', 'max:80'],
        ], [
            'pesquisa.min' => 'Digite pelo menos 2 caracteres para pesquisar.',
        ]);

        $view = $this->legacy()->pesquisa($request, $link);
        return $this->anotarDisponibilidade($view);
    }

    public function verPost($link, $postId)
    {
        $config = $this->configLoja($link);
        PostBlogEcommerce::where('id', $postId)
            ->where('empresa_id', $config->empresa_id)
            ->firstOrFail();

        return $this->legacy()->verPost($link, $postId);
    }

    public function verProduto($link, $produtoId)
    {
        $config = $this->configLoja($link);
        $produto = ProdutoEcommerce::with(['produto', 'galeria', 'categoria'])
            ->where('id', $produtoId)
            ->where('empresa_id', $config->empresa_id)
            ->where('status', 1)
            ->firstOrFail();

        abort_if(!$produto->produto || $produto->produto->inativo, 404, 'Produto indisponível.');

        $view = $this->legacy()->verProduto($link, $produtoId);
        if (method_exists($view, 'with')) {
            $disponivel = $produto->controlar_estoque ? $this->stockService->disponivel($produto) : null;
            $view->with('estoqueDisponivel', $disponivel);
            $view->with('produtoSeo', $produto);
        }

        return $view;
    }

    public function curtirProduto($link, $id)
    {
        $config = $this->configLoja($link);
        $this->clienteLogado($config);

        ProdutoEcommerce::where('id', $id)
            ->where('empresa_id', $config->empresa_id)
            ->where('status', 1)
            ->firstOrFail();

        return $this->legacy()->curtirProduto($link, $id);
    }

    public function pedidoDetalhe($link, $id)
    {
        $config = $this->configLoja($link);
        $cliente = $this->clienteLogado($config);

        PedidoEcommerce::where('id', $id)
            ->where('empresa_id', $config->empresa_id)
            ->where('cliente_id', $cliente->id)
            ->firstOrFail();

        return $this->legacy()->pedidoDetalhe($link, $id);
    }

    private function anotarDisponibilidade($view)
    {
        if (!method_exists($view, 'getData')) {
            return $view;
        }

        $data = $view->getData();
        $produtos = $data['produtos'] ?? null;
        if (!$produtos) {
            return $view;
        }

        $disponibilidades = $this->stockService->disponibilidadeEmLote($produtos);
        foreach ($produtos as $produto) {
            $produto->estoque_disponivel = $disponibilidades[$produto->id] ?? null;
        }

        return $view;
    }
}