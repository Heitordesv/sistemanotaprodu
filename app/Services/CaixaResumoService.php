<?php

namespace App\Services;

use App\Models\AberturaCaixa;
use App\Models\ContaReceber;
use App\Models\SangriaCaixa;
use App\Models\SuprimentoCaixa;
use App\Models\Usuario;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CaixaResumoService
{
    public function resumir(AberturaCaixa $abertura, $fim = null): array
    {
        $empresaId = (int) $abertura->empresa_id;
        $usuarioId = (int) $abertura->usuario_id;
        $caixaAberto = (int) $abertura->status === 0;
        $fim = $fim ? Carbon::parse($fim) : ($caixaAberto ? now() : ($abertura->updated_at ?: now()));

        if ($caixaAberto) {
            $ultimaVendaNfceId = (int) (VendaCaixa::query()
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->max('id') ?? 0);

            $ultimaVendaNfeId = (int) (Venda::query()
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->max('id') ?? 0);
        } else {
            $ultimaVendaNfceId = (int) $abertura->ultima_venda_nfce;
            $ultimaVendaNfeId = (int) $abertura->ultima_venda_nfe;
        }

        $vendasPdv = $this->vendasDaAbertura(
            VendaCaixa::class,
            $abertura,
            (int) $abertura->primeira_venda_nfce,
            $ultimaVendaNfceId
        );

        $vendasNfe = $this->vendasDaAbertura(
            Venda::class,
            $abertura,
            (int) $abertura->primeira_venda_nfe,
            $ultimaVendaNfeId
        );

        $suprimentos = $this->movimentacoesDaAbertura(SuprimentoCaixa::class, $abertura, $fim);
        $sangrias = $this->movimentacoesDaAbertura(SangriaCaixa::class, $abertura, $fim);

        $vendas = $this->agruparVendas($vendasNfe, $vendasPdv);
        $somaTiposPagamento = $this->somarTiposPagamento($vendas);
        $recebimentos = $this->recebimentosDaAbertura($abertura, $fim);

        $totalRecebimentos = round((float) $recebimentos->sum('valor'), 2);
        $totalRecebimentosDinheiro = round((float) $recebimentos
            ->where('tipo_pagamento', '01')
            ->sum('valor'), 2);

        $totalVendasDinheiro = round((float) ($somaTiposPagamento['01'] ?? 0), 2);
        $totalVendasFinanceiras = 0.0;

        foreach ($somaTiposPagamento as $tipo => $valor) {
            if (in_array((string) $tipo, ['06', '90'], true)) {
                continue;
            }

            $totalVendasFinanceiras += (float) $valor;
        }

        $totalSuprimentos = round((float) $suprimentos->sum('valor'), 2);
        $totalSangrias = round((float) $sangrias->sum('valor'), 2);

        $resultadoFinanceiro = round(
            $totalVendasFinanceiras + $totalRecebimentos + $totalSuprimentos - $totalSangrias,
            2
        );

        $dinheiroNaGaveta = round(
            (float) $abertura->valor
            + $totalVendasDinheiro
            + $totalRecebimentosDinheiro
            + $totalSuprimentos
            - $totalSangrias,
            2
        );

        return [
            'vendas' => $vendas,
            'suprimentos' => $suprimentos,
            'sangrias' => $sangrias,
            'somaTiposPagamento' => $somaTiposPagamento,
            'recebimentos' => $recebimentos,
            'totalRecebimentos' => $totalRecebimentos,
            'totalRecebimentosDinheiro' => $totalRecebimentosDinheiro,
            'totalVendasDinheiro' => $totalVendasDinheiro,
            'totalVendasFinanceiras' => round($totalVendasFinanceiras, 2),
            'totalSuprimentos' => $totalSuprimentos,
            'totalSangrias' => $totalSangrias,
            'resultadoFinanceiro' => $resultadoFinanceiro,
            'dinheiroNaGaveta' => $dinheiroNaGaveta,
        ];
    }

    private function vendasDaAbertura(
        string $modelClass,
        AberturaCaixa $abertura,
        int $primeiraVendaId,
        int $ultimaVendaId
    ): Collection {
        $model = new $modelClass();
        $query = $modelClass::query()
            ->where('empresa_id', (int) $abertura->empresa_id)
            ->where('usuario_id', (int) $abertura->usuario_id);

        if (Schema::hasColumn($model->getTable(), 'abertura_caixa_id')) {
            $query->where(function ($scope) use ($abertura, $primeiraVendaId, $ultimaVendaId) {
                $scope->where('abertura_caixa_id', (int) $abertura->id);

                if ($ultimaVendaId > $primeiraVendaId) {
                    $scope->orWhere(function ($legado) use ($primeiraVendaId, $ultimaVendaId) {
                        $legado->whereNull('abertura_caixa_id')
                            ->where('id', '>', $primeiraVendaId)
                            ->where('id', '<=', $ultimaVendaId);
                    });
                }
            });
        } else {
            if ($ultimaVendaId <= $primeiraVendaId) {
                return collect();
            }

            $query->where('id', '>', $primeiraVendaId)
                ->where('id', '<=', $ultimaVendaId);
        }

        return $query->orderBy('id')->get();
    }

    private function movimentacoesDaAbertura(
        string $modelClass,
        AberturaCaixa $abertura,
        Carbon $fim
    ): Collection {
        $model = new $modelClass();
        $query = $modelClass::query()
            ->where('empresa_id', (int) $abertura->empresa_id)
            ->where('usuario_id', (int) $abertura->usuario_id);

        if (Schema::hasColumn($model->getTable(), 'abertura_caixa_id')) {
            $query->where(function ($scope) use ($abertura, $fim) {
                $scope->where('abertura_caixa_id', (int) $abertura->id)
                    ->orWhere(function ($legado) use ($abertura, $fim) {
                        $legado->whereNull('abertura_caixa_id')
                            ->whereBetween('created_at', [$abertura->created_at, $fim]);
                    });
            });
        } else {
            $query->whereBetween('created_at', [$abertura->created_at, $fim]);
        }

        return $query->orderBy('created_at')->orderBy('id')->get();
    }

    private function recebimentosDaAbertura(AberturaCaixa $abertura, Carbon $fim): Collection
    {
        $recebimentos = collect();
        $idsComHistorico = collect();

        if (Schema::hasTable('conta_receber_recebimentos')) {
            $historico = DB::table('conta_receber_recebimentos')
                ->where('empresa_id', (int) $abertura->empresa_id)
                ->where('abertura_caixa_id', (int) $abertura->id)
                ->whereBetween('received_at', [$abertura->created_at, $fim])
                ->orderBy('received_at')
                ->orderBy('id')
                ->get();

            $idsComHistorico = $historico->pluck('conta_receber_id')->map(fn ($id) => (int) $id)->unique();
            $nomesUsuarios = $this->nomesUsuarios($historico->pluck('usuario_id'));

            foreach ($historico as $registro) {
                $recebimentos->push($this->normalizarRecebimento(
                    (int) $registro->conta_receber_id,
                    (float) $registro->valor,
                    $registro->tipo_pagamento,
                    $registro->received_at,
                    $registro->usuario_id,
                    $nomesUsuarios[(int) $registro->usuario_id] ?? 'Não informado'
                ));
            }
        }

        if (
            Schema::hasTable('conta_recebers')
            && Schema::hasColumn('conta_recebers', 'abertura_caixa_id')
            && Schema::hasColumn('conta_recebers', 'received_at')
            && Schema::hasColumn('conta_recebers', 'received_by_user_id')
        ) {
            $legados = ContaReceber::query()
                ->with('recebidoPor')
                ->where('empresa_id', (int) $abertura->empresa_id)
                ->where('abertura_caixa_id', (int) $abertura->id)
                ->where('valor_recebido', '>', 0)
                ->whereBetween('received_at', [$abertura->created_at, $fim])
                ->when($idsComHistorico->isNotEmpty(), function ($query) use ($idsComHistorico) {
                    return $query->whereNotIn('id', $idsComHistorico->all());
                })
                ->orderBy('received_at')
                ->orderBy('id')
                ->get();

            foreach ($legados as $conta) {
                $recebimentos->push($this->normalizarRecebimento(
                    (int) $conta->id,
                    (float) $conta->valor_recebido,
                    $conta->tipo_pagamento,
                    $conta->received_at,
                    $conta->received_by_user_id,
                    $conta->recebidoPor->nome ?? 'Não informado'
                ));
            }
        }

        return $recebimentos->sortBy('received_at')->values();
    }

    private function normalizarRecebimento(
        int $contaId,
        float $valor,
        $tipoPagamento,
        $receivedAt,
        $usuarioId,
        string $usuarioNome
    ): object {
        $tipo = str_pad((string) ($tipoPagamento ?? ''), 2, '0', STR_PAD_LEFT);
        $tipos = ContaReceber::tiposPagamento();

        return (object) [
            'conta_receber_id' => $contaId,
            'valor' => round($valor, 2),
            'tipo_pagamento' => $tipo,
            'tipo_pagamento_nome' => $tipos[$tipo] ?? 'Não informado',
            'received_at' => $receivedAt,
            'usuario_id' => $usuarioId ? (int) $usuarioId : null,
            'usuario_nome' => $usuarioNome,
        ];
    }

    private function nomesUsuarios(Collection $ids): array
    {
        $ids = $ids->filter()->map(fn ($id) => (int) $id)->unique()->values();

        if ($ids->isEmpty()) {
            return [];
        }

        return Usuario::query()->whereIn('id', $ids->all())->pluck('nome', 'id')->all();
    }

    private function agruparVendas($vendas, $vendasPdv): array
    {
        $resultado = [];

        foreach ($vendas as $venda) {
            $venda->tipo = 'VENDA';
            $resultado[] = $venda;
        }

        foreach ($vendasPdv as $venda) {
            $venda->tipo = 'PDV';
            $resultado[] = $venda;
        }

        return $resultado;
    }

    private function prepararTipos(): array
    {
        $tipos = [];
        foreach (VendaCaixa::tiposPagamento() as $key => $tipo) {
            $tipos[$key] = 0;
        }
        return $tipos;
    }

    private function somarTiposPagamento(array $vendas): array
    {
        $tipos = $this->prepararTipos();

        foreach ($vendas as $venda) {
            if (
                strtoupper((string) ($venda->estado_emissao ?? '')) === 'CANCELADO'
                || strtoupper((string) ($venda->estado ?? '')) === 'CANCELADO'
            ) {
                continue;
            }

            if ((string) $venda->tipo_pagamento !== '99') {
                if (!isset($tipos[$venda->tipo_pagamento])) {
                    continue;
                }

                if (isset($venda->NFcNumero)) {
                    if (!$venda->rascunho && !$venda->consignado) {
                        $tipos[$venda->tipo_pagamento] += (float) $venda->valor_total;
                    }
                    continue;
                }

                if ($venda->duplicatas && count($venda->duplicatas) > 0) {
                    foreach ($venda->duplicatas as $duplicata) {
                        $key = Venda::getTipoPagamentoNFe($duplicata->tipo_pagamento);
                        if (isset($tipos[$key])) {
                            $tipos[$key] += (float) $duplicata->valor_integral;
                        }
                    }
                } else {
                    $tipos[$venda->tipo_pagamento] += (float) $venda->valor_total;
                }
                continue;
            }

            if ($venda->fatura) {
                foreach ($venda->fatura as $fatura) {
                    $key = trim((string) $fatura->forma_pagamento);
                    if (isset($tipos[$key])) {
                        $tipos[$key] += (float) $fatura->valor;
                    }
                }
            }
        }

        return $tipos;
    }
}
