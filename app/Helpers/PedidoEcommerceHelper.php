<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use App\Models\ClienteEcommerce;
use App\Models\PedidoEcommerce;
use App\Models\ItemPedidoEcommerce;
use App\Models\CurtidaProdutoEcommerce;
use App\Models\ProdutoEcommerce;
use App\Services\EcommerceStockService;

class PedidoEcommerceHelper
{
    public function addProduto($data)
    {
        $empresaId = (int) ($data['empresa_id'] ?? 0);
        if ($empresaId <= 0) {
            throw new \InvalidArgumentException('Empresa da loja não informada.');
        }

        $typeUser = $this->getUser($empresaId);

        return $typeUser === 'temp'
            ? $this->setPedidoRand($data)
            : $this->setPedido($data);
    }

    private function setPedido($data)
    {
        $empresaId = (int) $data['empresa_id'];
        $user = $this->getUserLogado($empresaId);
        if (!$user) throw new \RuntimeException('Sessão do cliente não pertence a esta loja.');

        $pedido = PedidoEcommerce::where('cliente_id', $user['cliente_id'])
            ->where('empresa_id', $empresaId)->where('status', 0)->first();
        if ($pedido === null) $pedido = $this->criaPedido($data, $user['cliente_id']);
        $this->addItem($data, $pedido);
        return $pedido;
    }

    private function setPedidoRand($data)
    {
        $empresaId = (int) $data['empresa_id'];
        $userTemp = $this->getUserTemp($empresaId);
        if (!$userTemp) throw new \RuntimeException('Sessão temporária não pertence a esta loja.');

        $pedido = PedidoEcommerce::where('rand_pedido', $userTemp['rand'])
            ->where('empresa_id', $empresaId)->where('status', 0)->first();
        if ($pedido === null) $pedido = $this->criaPedidoRand($data, $userTemp['rand']);
        $this->addItem($data, $pedido);
        return $pedido;
    }

    private function criaPedido($data, $clienteId)
    {
        return PedidoEcommerce::create([
            'cliente_id' => $clienteId, 'endereco_id' => null, 'status' => 0,
            'valor_total' => 0, 'valor_frete' => 0, 'tipo_frete' => '', 'venda_id' => 0,
            'numero_nfe' => 0, 'empresa_id' => (int) $data['empresa_id'], 'observacao' => '',
            'rand_pedido' => '', 'token' => Str::random(40),
        ]);
    }

    private function criaPedidoRand($data, $rand)
    {
        return PedidoEcommerce::create([
            'cliente_id' => null, 'endereco_id' => null, 'status' => 0,
            'valor_total' => 0, 'valor_frete' => 0, 'tipo_frete' => '', 'venda_id' => 0,
            'numero_nfe' => 0, 'empresa_id' => (int) $data['empresa_id'], 'observacao' => '',
            'rand_pedido' => $rand, 'token' => Str::random(40),
        ]);
    }

    private function addItem($data, $pedido)
    {
        if ((int) $pedido->empresa_id !== (int) $data['empresa_id']) {
            throw new \RuntimeException('Carrinho inválido para esta loja.');
        }

        $produto = ProdutoEcommerce::where('id', $data['produto_id'])
            ->where('empresa_id', $pedido->empresa_id)
            ->where('status', 1)
            ->firstOrFail();

        $item = ItemPedidoEcommerce::where('pedido_id', $pedido->id)
            ->where('produto_id', $produto->id)->first();

        $adicionar = max(1, (int) $data['quantidade']);
        $quantidadeFinal = ($item ? (float) $item->quantidade : 0) + $adicionar;
        app(EcommerceStockService::class)->validarQuantidade($produto, $quantidadeFinal, $pedido->id);

        if ($item) {
            $item->quantidade = $quantidadeFinal;
            $item->save();
            return $item;
        }

        return ItemPedidoEcommerce::create([
            'pedido_id' => $pedido->id,
            'produto_id' => $produto->id,
            'quantidade' => $adicionar,
        ]);
    }

    public function getUser($empresaId = null)
    {
        $user = $this->getUserLogado($empresaId);
        if ($user !== null) return 'logado';

        $userTemp = $this->getUserTemp($empresaId);
        if ($userTemp === null) {
            $userTemp = [
                'rand' => Str::random(40),
                'empresa_id' => $empresaId ? (int) $empresaId : null,
                'start' => now()->toDateTimeString(),
            ];
            session(['user_temp' => $userTemp]);
        }
        return 'temp';
    }

    public function getUserLogado($empresaId = null)
    {
        $usr = session('user_ecommerce');
        if (!$usr || !is_array($usr)) return null;

        $clienteId = (int) ($usr['cliente_id'] ?? 0);
        $sessaoEmpresaId = (int) ($usr['empresa_id'] ?? 0);

        if ($clienteId <= 0 || $sessaoEmpresaId <= 0) {
            session()->forget('user_ecommerce');
            return null;
        }

        if ($empresaId !== null && $sessaoEmpresaId !== (int) $empresaId) return null;

        return $usr;
    }

    public function getUserTemp($empresaId = null)
    {
        $usr = session('user_temp');
        if (!$usr || !is_array($usr)) return null;
        if ($empresaId !== null && (int) ($usr['empresa_id'] ?? 0) !== (int) $empresaId) return null;
        return $usr;
    }

    public function getProdutosCurtidos($empresaId = null)
    {
        $user = $this->getUserLogado($empresaId);
        if ($user === null) return 0;

        return CurtidaProdutoEcommerce::where('cliente_id', $user['cliente_id'])
            ->when($empresaId, function ($query) use ($empresaId) {
                $query->whereHas('produto', fn ($q) => $q->where('empresa_id', $empresaId));
            })->count();
    }

    public function setUserEcommerce($clienteId, $empresaId)
    {
        $cliente = ClienteEcommerce::where('id', (int) $clienteId)
            ->where('empresa_id', (int) $empresaId)
            ->where('status', 1)
            ->firstOrFail();

        // Evita reutilização do mesmo identificador de sessão após autenticação.
        session()->regenerate();
        session()->forget('user_temp');

        session(['user_ecommerce' => [
            'cliente_id' => (int) $cliente->id,
            'empresa_id' => (int) $cliente->empresa_id,
            'nome' => (string) $cliente->nome,
            'email' => (string) $cliente->email,
            'start' => now()->toDateTimeString(),
            'last_activity' => now()->toDateTimeString(),
        ]]);
    }

    public function logoff()
    {
        session()->forget('user_ecommerce');
        session()->forget('user_temp');

        // Mantém outras sessões do sistema intactas, mas troca o identificador
        // da sessão usada pelo e-commerce e renova o token CSRF.
        session()->regenerate();
        session()->regenerateToken();
    }

    public function getCarrinho($empresaId = null)
    {
        $user = $this->getUserLogado($empresaId);
        $userTemp = $this->getUserTemp($empresaId);

        if ($userTemp !== null) {
            return PedidoEcommerce::where('rand_pedido', $userTemp['rand'])
                ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
                ->where('status', 0)->first();
        }

        if ($user !== null) {
            return PedidoEcommerce::where('cliente_id', $user['cliente_id'])
                ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
                ->where('status', 0)->first();
        }

        return null;
    }
}