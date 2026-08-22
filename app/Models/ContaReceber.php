<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ContaReceber extends Model
{
    protected $fillable = [
        'venda_id', 'remessa_nfe_id', 'data_vencimento', 'data_recebimento', 'valor_integral',
        'valor_recebido', 'referencia', 'categoria_id', 'status', 'empresa_id',
        'cliente_id', 'grupo_id', 'juros', 'multa', 'venda_caixa_id', 'observacao', 'tipo_pagamento',
        'filial_id', 'quantidade_parcelas', 'empresa_id_emp', 'boleto_link', 'chave_pix',
        'mercadopago_payment_id', 'mercadopago_preference_id', 'mercadopago_external_reference',
        'mercadopago_payment_method', 'mercadopago_status', 'mercadopago_status_detail',
        'mercadopago_ticket_url', 'mercadopago_digitable_line', 'mercadopago_qr_code',
        'mercadopago_qr_code_base64', 'mercadopago_checkout_url', 'mercadopago_idempotency_key',
        'mercadopago_public_token', 'mercadopago_last_sync_at',
        'abertura_caixa_id', 'received_by_user_id', 'received_at',
    ];

    protected $casts = [
        'mercadopago_last_sync_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (ContaReceber $conta) {
            if (!$conta->isDirty('valor_recebido')) {
                return;
            }

            $anterior = (float) ($conta->getOriginal('valor_recebido') ?? 0);
            $novo = (float) ($conta->valor_recebido ?? 0);

            if ($novo <= $anterior) {
                return;
            }

            $contexto = static::resolverContextoRecebimento($conta);

            $conta->received_by_user_id = $contexto['usuario_id'];
            $conta->abertura_caixa_id = $contexto['abertura_caixa_id'];
            $conta->received_at = now();
        });

        static::updated(function (ContaReceber $conta) {
            if (!$conta->wasChanged('valor_recebido')) {
                return;
            }

            $anterior = (float) ($conta->getOriginal('valor_recebido') ?? 0);
            $novo = (float) ($conta->valor_recebido ?? 0);
            $valorRecebidoAgora = round($novo - $anterior, 7);

            if ($valorRecebidoAgora <= 0 || !Schema::hasTable('conta_receber_recebimentos')) {
                return;
            }

            DB::table('conta_receber_recebimentos')->insert([
                'conta_receber_id' => (int) $conta->id,
                'empresa_id' => (int) $conta->empresa_id,
                'abertura_caixa_id' => $conta->abertura_caixa_id
                    ? (int) $conta->abertura_caixa_id
                    : null,
                'usuario_id' => $conta->received_by_user_id
                    ? (int) $conta->received_by_user_id
                    : null,
                'valor' => $valorRecebidoAgora,
                'tipo_pagamento' => $conta->tipo_pagamento,
                'received_at' => $conta->received_at ?: now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    private static function resolverContextoRecebimento(ContaReceber $conta): array
    {
        $user = session('user_logged');

        if (!$user) {
            return [
                'usuario_id' => null,
                'abertura_caixa_id' => null,
            ];
        }

        $empresaSessao = (int) (is_object($user)
            ? ($user->empresa_id ?? 0)
            : ($user['empresa'] ?? $user['empresa_id'] ?? 0));

        $usuarioId = (int) (is_object($user)
            ? ($user->id ?? 0)
            : ($user['id'] ?? $user['usuario_id'] ?? 0));

        if (
            $empresaSessao <= 0 ||
            $usuarioId <= 0 ||
            $empresaSessao !== (int) $conta->empresa_id
        ) {
            return [
                'usuario_id' => null,
                'abertura_caixa_id' => null,
            ];
        }

        $abertura = AberturaCaixa::query()
            ->where('empresa_id', $empresaSessao)
            ->where('usuario_id', $usuarioId)
            ->where('status', 0)
            ->when($conta->filial_id, function ($query) use ($conta) {
                return $query->where('filial_id', (int) $conta->filial_id);
            })
            ->orderByDesc('id')
            ->first();

        return [
            'usuario_id' => $usuarioId,
            'abertura_caixa_id' => $abertura ? (int) $abertura->id : null,
        ];
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'filial_id');
    }

    public function aberturaCaixa()
    {
        return $this->belongsTo(AberturaCaixa::class, 'abertura_caixa_id');
    }

    public function recebidoPor()
    {
        return $this->belongsTo(Usuario::class, 'received_by_user_id');
    }

    public function venda()
    {
        return $this->belongsTo(Venda::class, 'venda_id');
    }

    public function vendaCaixa()
    {
        return $this->belongsTo(VendaCaixa::class, 'venda_caixa_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function empresa_id_emp_rel()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id_emp');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaConta::class, 'categoria_id');
    }

    public function grupo()
    {
        return $this->belongsTo(GrupoCliente::class, 'grupo_id');
    }

    public function boleto()
    {
        return $this->hasOne('App\Models\Boleto', 'conta_id', 'id');
    }

    public static function filtroData($dataInicial, $dataFinal, $status)
    {
        $value = session('user_logged');
        $empresa_id = $value['empresa'];
        $c = ContaReceber::orderBy('conta_recebers.data_vencimento', 'asc')
            ->where('empresa_id', $empresa_id)
            ->whereBetween('conta_recebers.data_vencimento', [$dataInicial, $dataFinal]);

        if ($status == 'pago') {
            $c->where('status', true);
        } else if ($status == 'pendente') {
            $c->where('status', false);
        }
        return $c->get();
    }

    public static function filtroDataFornecedor($cliente, $dataInicial, $dataFinal, $status)
    {
        $value = session('user_logged');
        $empresa_id = $value['empresa'];
        $c = ContaReceber::select('conta_recebers.*')
            ->orderBy('conta_recebers.data_vencimento', 'asc')
            ->join('vendas', 'vendas.id', '=', 'conta_recebers.venda_id')
            ->join('clientes', 'clientes.id', '=', 'vendas.cliente_id')
            ->where('clientes.razao_social', 'LIKE', "%$cliente%")
            ->where('vendas.empresa_id', $empresa_id)
            ->whereBetween('conta_recebers.data_vencimento', [$dataInicial, $dataFinal]);

        if ($status == 'pago') {
            $c->where('status', true);
        } else if ($status == 'pendente') {
            $c->where('status', false);
        }
        $c1 = $c->get();

        $c = ContaReceber::select('conta_recebers.*')
            ->orderBy('conta_recebers.data_vencimento', 'asc')
            ->join('clientes', 'clientes.id', '=', 'conta_recebers.cliente_id')
            ->where('clientes.razao_social', 'LIKE', "%$cliente%")
            ->where('conta_recebers.empresa_id', $empresa_id)
            ->whereBetween('conta_recebers.data_vencimento', [$dataInicial, $dataFinal]);

        if ($status == 'pago') {
            $c->where('status', true);
        } else if ($status == 'pendente') {
            $c->where('status', false);
        }
        $c2 = $c->get();

        $temp = [];
        foreach ($c1 as $c) array_push($temp, $c);
        foreach ($c2 as $c) array_push($temp, $c);
        return $temp;
    }

    public static function filtroFornecedor($cliente, $status)
    {
        $value = session('user_logged');
        $empresa_id = $value['empresa'];
        $c = ContaReceber::select('conta_recebers.*')
            ->orderBy('conta_recebers.data_vencimento', 'asc')
            ->join('vendas', 'vendas.id', '=', 'conta_recebers.venda_id')
            ->join('clientes', 'clientes.id', '=', 'vendas.cliente_id')
            ->where('conta_recebers.empresa_id', $empresa_id)
            ->where('razao_social', 'LIKE', "%$cliente%");

        if ($status == 'pago') {
            $c->where('status', true);
        } else if ($status == 'pendente') {
            $c->where('status', false);
        }
        $c1 = $c->get();

        $c = ContaReceber::select('conta_recebers.*')
            ->orderBy('conta_recebers.data_vencimento', 'asc')
            ->join('clientes', 'clientes.id', '=', 'conta_recebers.cliente_id')
            ->where('conta_recebers.empresa_id', $empresa_id)
            ->where('razao_social', 'LIKE', "%$cliente%");

        if ($status == 'pago') {
            $c->where('status', true);
        } else if ($status == 'pendente') {
            $c->where('status', false);
        }
        $c2 = $c->get();

        $temp = [];
        foreach ($c1 as $c) array_push($temp, $c);
        foreach ($c2 as $c) array_push($temp, $c);
        return $temp;
    }

    public static function filtroStatus($status)
    {
        $value = session('user_logged');
        $empresa_id = $value['empresa'];
        $c = ContaReceber::where('empresa_id', $empresa_id)
            ->orderBy('conta_recebers.data_vencimento', 'asc');

        if ($status == 'pago') {
            $c->where('status', true);
        } else if ($status == 'pendente') {
            $c->where('status', false);
        }
        return $c->get();
    }

    public function getCliente()
    {
        if ($this->venda_id != null) {
            return $this->venda->cliente;
        } else if ($this->cliente_id != null) {
            return $this->cliente;
        }
    }

    public static function tiposPagamento()
    {
        return [
            '01' => 'Dinheiro',
            '02' => 'Cheque',
            '03' => 'Cartão de Crédito',
            '04' => 'Cartão de Débito',
            '05' => 'Crédito Loja',
            '06' => 'Crediário',
            '10' => 'Vale Alimentação',
            '11' => 'Vale Refeição',
            '12' => 'Vale Presente',
            '13' => 'Vale Combustível',
            '14' => 'Duplicata Mercantil',
            '15' => 'Boleto Bancário',
            '16' => 'Depósito Bancário',
            '17' => 'Pagamento Instantâneo (PIX)',
            '90' => 'Sem Pagamento',
            '99' => 'Outros',
        ];
    }

    public function getTipoPagamento()
    {
        foreach (Venda::tiposPagamento() as $key => $t) {
            if ($this->tipo_pagamento == $key) return $t;
        }
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id_emp');
    }

    public static function NotificarClienteVencimento()
    {
        return self::where('status', 0)
            ->whereNotNull('data_vencimento')
            ->whereHas('cliente', function ($q) {
                $q->whereNotNull('celular')->where('celular', '!=', '');
            })
            ->with(['cliente', 'empresa'])
            ->get();
    }

    public static function scopeParaNotificacaoVencimento($query)
    {
        $hoje = Carbon::today()->toDateString();
        $limiteFuturo = Carbon::today()->addDays(5)->toDateString();

        return $query->where('status', 0)
            ->whereNotNull('data_vencimento')
            ->where(function ($q) use ($hoje, $limiteFuturo) {
                $q->where('data_vencimento', '<', $hoje)
                  ->orWhereBetween('data_vencimento', [$hoje, $limiteFuturo]);
            })
            ->whereHas('cliente', function ($q) {
                $q->whereNotNull('celular')->where('celular', '!=', '');
            })
            ->with(['cliente', 'empresa']);
    }

    public static function contasParaNotificacaoEmpresa($diasAntes = 0)
    {
        $dataAlvo = now()->addDays($diasAntes)->format('Y-m-d');

        return self::where('status', 0)
            ->whereDate('data_vencimento', '=', $dataAlvo)
            ->whereNotNull('empresa_id_emp')
            ->with('empresa')
            ->get();
    }

    public static function contasVencidasEmpresa()
    {
        return self::where('status', 0)
            ->whereDate('data_vencimento', '<', now())
            ->whereNotNull('empresa_id_emp')
            ->with('empresa')
            ->get();
    }
}
