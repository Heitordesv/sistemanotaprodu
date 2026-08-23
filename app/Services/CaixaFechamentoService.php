<?php

namespace App\Services;

use App\Models\AberturaCaixa;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Illuminate\Support\Facades\DB;
use DomainException;

class CaixaFechamentoService
{
    public function fechar(int $aberturaId, int $empresaId, int $usuarioId): AberturaCaixa
    {
        if ($aberturaId <= 0 || $empresaId <= 0 || $usuarioId <= 0) {
            throw new DomainException('Dados inválidos para fechamento do caixa.');
        }

        return DB::transaction(function () use ($aberturaId, $empresaId, $usuarioId) {
            $abertura = AberturaCaixa::query()
                ->where('id', $aberturaId)
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->where('status', 0)
                ->lockForUpdate()
                ->first();

            if (!$abertura) {
                throw new DomainException('Este caixa não está aberto para o usuário atual ou já foi fechado.');
            }

            $ultimaVendaNfceId = (int) (VendaCaixa::query()
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->max('id') ?? 0);

            $ultimaVendaNfeId = (int) (Venda::query()
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->max('id') ?? 0);

            $abertura->ultima_venda_nfce = $ultimaVendaNfceId > (int) $abertura->primeira_venda_nfce
                ? $ultimaVendaNfceId
                : (int) $abertura->primeira_venda_nfce;

            $abertura->ultima_venda_nfe = $ultimaVendaNfeId > (int) $abertura->primeira_venda_nfe
                ? $ultimaVendaNfeId
                : (int) $abertura->primeira_venda_nfe;

            // Não existe exigência de venda. Um caixa com apenas recebimentos,
            // suprimentos/sangrias ou mesmo sem movimento pode ser encerrado.
            $abertura->status = 1;

            // O evento saving de AberturaCaixa recalcula valor_dinheiro_caixa
            // no servidor via CaixaResumoService. Nada vindo do navegador é confiado.
            $abertura->save();

            return $abertura->fresh();
        });
    }
}
