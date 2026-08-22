<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoEcommerce extends Model
{
    protected $fillable = [
        'cliente_id', 'endereco_id', 'status', 'valor_total', 'valor_frete', 'tipo_frete',
        'venda_id', 'numero_nfe', 'empresa_id', 'observacao', 'rand_pedido', 'link_boleto',
        'qr_code_base64', 'qr_code', 'transacao_id', 'forma_pagamento', 'status_pagamento',
        'status_detalhe', 'status_preparacao', 'codigo_rastreio', 'token', 'cupom_desconto',
        'desconto', 'hash',
        'correios_prepostagem_id', 'correios_rotulo_recibo', 'correios_status', 'correios_ultima_consulta_at',
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
        'valor_frete' => 'decimal:2',
        'desconto' => 'decimal:2',
        'correios_ultima_consulta_at' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function itens()
    {
        return $this->hasMany(ItemPedidoEcommerce::class, 'pedido_id', 'id');
    }

    public function venda()
    {
        return $this->hasOne(Venda::class, 'pedido_ecommerce_id', 'id');
    }

    public function cliente()
    {
        return $this->belongsTo(ClienteEcommerce::class, 'cliente_id');
    }

    public function endereco()
    {
        return $this->belongsTo(EnderecoEcommerce::class, 'endereco_id');
    }

    /**
     * Compatibilidade entre instalações antigas, onde status era inteiro,
     * e instalações novas, onde o fluxo usa nomes legíveis.
     */
    public function getStatusOperacionalAttribute(): string
    {
        $status = strtolower(trim((string) $this->getRawOriginal('status')));

        return match ($status) {
            '', '0', 'novo' => 'novo',
            '1', '3', 'aprovado', 'preparacao', 'preparação', 'aguardando_envio' => 'preparacao',
            '2', 'cancelado', 'canceled', 'cancelled' => 'cancelado',
            '4', 'enviado' => 'enviado',
            '5', 'entregue', 'finalizado' => 'entregue',
            default => $status,
        };
    }

    public function getStatusPagamentoNormalizadoAttribute(): string
    {
        $status = strtolower(trim((string) $this->status_pagamento));

        return match ($status) {
            '', 'pending', 'in_process', 'inprocess' => $status === '' ? 'pending' : $status,
            'paid', 'approved', 'aprovado' => 'approved',
            'canceled', 'cancelled', 'cancelado' => 'canceled',
            'rejected', 'recusado' => 'rejected',
            'refunded', 'estornado' => 'refunded',
            default => $status,
        };
    }

    public function somaItens(): float
    {
        $soma = 0.0;

        foreach ($this->itens as $item) {
            $produtoEcommerce = $item->produto;
            if (!$produtoEcommerce) {
                continue;
            }

            $soma += max(0, (int) $item->quantidade) * (float) ($produtoEcommerce->valor ?? 0);
        }

        return $soma;
    }

    public function somaItensPorCep($cep): float
    {
        $itensPedido = ItemPedidoEcommerce::select('item_pedido_ecommerces.*')
            ->join('produto_ecommerces', 'produto_ecommerces.id', '=', 'item_pedido_ecommerces.produto_id')
            ->where('item_pedido_ecommerces.pedido_id', $this->id)
            ->where('produto_ecommerces.cep', $cep)
            ->get();

        $soma = 0.0;
        foreach ($itensPedido as $item) {
            if ($item->produto) {
                $soma += max(0, (int) $item->quantidade) * (float) ($item->produto->valor ?? 0);
            }
        }

        return $soma;
    }

    public function somaPeso(): float
    {
        $soma = 0.0;

        foreach ($this->itens as $item) {
            if ($item->produto && $item->produto->produto) {
                $soma += max(0, (int) $item->quantidade) * (float) ($item->produto->produto->peso_bruto ?? 0);
            }
        }

        return $soma;
    }

    public function somaPesoPorCep($cep): float
    {
        $itensPedido = ItemPedidoEcommerce::select('item_pedido_ecommerces.*')
            ->join('produto_ecommerces', 'produto_ecommerces.id', '=', 'item_pedido_ecommerces.produto_id')
            ->where('item_pedido_ecommerces.pedido_id', $this->id)
            ->where('produto_ecommerces.cep', $cep)
            ->get();

        $soma = 0.0;
        foreach ($itensPedido as $item) {
            if ($item->produto && $item->produto->produto) {
                $soma += max(0, (int) $item->quantidade) * (float) ($item->produto->produto->peso_bruto ?? 0);
            }
        }

        return $soma;
    }

    public function somaDimensoes(): array
    {
        $data = ['comprimento' => 0, 'altura' => 0, 'largura' => 0];

        foreach ($this->itens as $item) {
            if (!$item->produto || !$item->produto->produto) {
                continue;
            }

            $produto = $item->produto->produto;
            $data['comprimento'] = max($data['comprimento'], (float) ($produto->comprimento ?? 0));
            $data['altura'] += (float) ($produto->altura ?? 0);
            $data['largura'] = max($data['largura'], (float) ($produto->largura ?? 0));
        }

        return $data;
    }

    public function somaDimensoesPorCep($cep): array
    {
        $data = ['comprimento' => 0, 'altura' => 0, 'largura' => 0];

        $itensPedido = ItemPedidoEcommerce::select('item_pedido_ecommerces.*')
            ->join('produto_ecommerces', 'produto_ecommerces.id', '=', 'item_pedido_ecommerces.produto_id')
            ->where('item_pedido_ecommerces.pedido_id', $this->id)
            ->where('produto_ecommerces.cep', $cep)
            ->get();

        foreach ($itensPedido as $item) {
            if (!$item->produto || !$item->produto->produto) {
                continue;
            }

            $produto = $item->produto->produto;
            $data['comprimento'] = max($data['comprimento'], (float) ($produto->comprimento ?? 0));
            $data['altura'] += (float) ($produto->altura ?? 0);
            $data['largura'] = max($data['largura'], (float) ($produto->largura ?? 0));
        }

        return $data;
    }

    public function getCepsDoPedido($cepOrigem): array
    {
        $ceps = [];

        foreach ($this->itens as $item) {
            if (!$item->produto) {
                continue;
            }

            $cep = (string) ($item->produto->cep ?? '');
            if ($cep !== '' && $cep !== (string) $cepOrigem && !in_array($cep, $ceps, true)) {
                $ceps[] = $cep;
            }
        }

        return $ceps;
    }
}