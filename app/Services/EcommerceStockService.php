<?php

namespace App\Services;

use App\Models\EcommerceStockReservation;
use App\Models\Estoque;
use App\Models\PedidoEcommerce;
use App\Models\ProdutoEcommerce;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EcommerceStockService
{
    public function estoqueFisico(ProdutoEcommerce $produto): float
    {
        return (float) Estoque::where('empresa_id', $produto->empresa_id)
            ->where('produto_id', $produto->produto_id)
            ->sum('quantidade');
    }

    public function reservadoAtivo(ProdutoEcommerce $produto, ?int $ignorarPedidoId = null): float
    {
        return (float) EcommerceStockReservation::where('empresa_id', $produto->empresa_id)
            ->where('produto_id', $produto->produto_id)
            ->whereNull('consumed_at')
            ->whereNull('released_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->when($ignorarPedidoId, fn ($q) => $q->where('pedido_id', '<>', $ignorarPedidoId))
            ->sum('quantidade');
    }

    public function disponivel(ProdutoEcommerce $produto, ?int $ignorarPedidoId = null): float
    {
        if (!$produto->controlar_estoque) {
            return INF;
        }

        return max(0, $this->estoqueFisico($produto) - $this->reservadoAtivo($produto, $ignorarPedidoId));
    }

    /**
     * Calcula a disponibilidade de vários produtos com apenas duas consultas por empresa.
     * O array retornado usa o id do ProdutoEcommerce como chave.
     */
    public function disponibilidadeEmLote(iterable $produtos): array
    {
        $collection = $produtos instanceof Collection ? $produtos : collect($produtos);
        $resultado = [];

        foreach ($collection->groupBy(fn ($produto) => (int) $produto->empresa_id) as $empresaId => $grupo) {
            $produtoIds = $grupo->pluck('produto_id')->filter()->unique()->values();

            $fisico = Estoque::query()
                ->where('empresa_id', $empresaId)
                ->whereIn('produto_id', $produtoIds)
                ->selectRaw('produto_id, SUM(quantidade) as total')
                ->groupBy('produto_id')
                ->pluck('total', 'produto_id');

            $reservado = EcommerceStockReservation::query()
                ->where('empresa_id', $empresaId)
                ->whereIn('produto_id', $produtoIds)
                ->whereNull('consumed_at')
                ->whereNull('released_at')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->selectRaw('produto_id, SUM(quantidade) as total')
                ->groupBy('produto_id')
                ->pluck('total', 'produto_id');

            foreach ($grupo as $produto) {
                if (!$produto->controlar_estoque) {
                    $resultado[$produto->id] = null;
                    continue;
                }

                $quantidadeFisica = (float) ($fisico[$produto->produto_id] ?? 0);
                $quantidadeReservada = (float) ($reservado[$produto->produto_id] ?? 0);
                $resultado[$produto->id] = max(0, $quantidadeFisica - $quantidadeReservada);
            }
        }

        return $resultado;
    }

    public function validarQuantidade(ProdutoEcommerce $produto, float $quantidade, ?int $ignorarPedidoId = null): void
    {
        if (!$produto->controlar_estoque) return;

        if ($quantidade <= 0 || $quantidade > $this->disponivel($produto, $ignorarPedidoId)) {
            throw new RuntimeException('Quantidade em estoque insuficiente para este produto.');
        }
    }

    public function reservarPedido(PedidoEcommerce $pedido, int $minutos = 30): void
    {
        $lock = Cache::lock('ecommerce-stock-order:' . $pedido->id, 20);
        try {
            $lock->block(5, function () use ($pedido, $minutos) {
                DB::transaction(function () use ($pedido, $minutos) {
                    $pedido->load('itens.produto.produto');
                    foreach ($pedido->itens as $item) {
                        $produto = $item->produto;
                        if (!$produto || !$produto->controlar_estoque) continue;

                        $this->validarQuantidade($produto, (float) $item->quantidade, $pedido->id);
                        EcommerceStockReservation::updateOrCreate(
                            [
                                'pedido_id' => $pedido->id,
                                'produto_ecommerce_id' => $produto->id,
                            ],
                            [
                                'empresa_id' => $pedido->empresa_id,
                                'produto_id' => $produto->produto_id,
                                'quantidade' => $item->quantidade,
                                'expires_at' => now()->addMinutes($minutos),
                                'released_at' => null,
                                'consumed_at' => null,
                            ]
                        );
                    }
                });
            });
        } finally {
            optional($lock)->release();
        }
    }

    public function liberarPedido(PedidoEcommerce $pedido): void
    {
        EcommerceStockReservation::where('pedido_id', $pedido->id)
            ->whereNull('consumed_at')
            ->whereNull('released_at')
            ->update(['released_at' => now()]);
    }

    public function consumirPedido(PedidoEcommerce $pedido): void
    {
        DB::transaction(function () use ($pedido) {
            $reservas = EcommerceStockReservation::where('pedido_id', $pedido->id)
                ->whereNull('consumed_at')->whereNull('released_at')->lockForUpdate()->get();

            foreach ($reservas as $reserva) {
                $restante = (float) $reserva->quantidade;
                $estoques = Estoque::where('empresa_id', $reserva->empresa_id)
                    ->where('produto_id', $reserva->produto_id)
                    ->where('quantidade', '>', 0)
                    ->orderByRaw('filial_id is null desc')
                    ->lockForUpdate()->get();

                foreach ($estoques as $estoque) {
                    if ($restante <= 0) break;
                    $baixar = min($restante, (float) $estoque->quantidade);
                    $estoque->quantidade = (float) $estoque->quantidade - $baixar;
                    $estoque->save();
                    $restante -= $baixar;
                }

                if ($restante > 0.0001) {
                    throw new RuntimeException('Estoque físico insuficiente ao confirmar o pagamento do pedido #' . $pedido->id . '.');
                }

                $reserva->consumed_at = now();
                $reserva->save();
            }
        });
    }
}