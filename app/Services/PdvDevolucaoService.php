<?php

namespace App\Services;

use App\Models\AberturaCaixa;
use App\Models\AutorizacaoDevolucao;
use App\Models\PdvDevolucao;
use App\Models\Usuario;
use App\Models\VendaCaixa;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PdvDevolucaoService
{
    private const SEFAZ_PROCESSING_TTL_SECONDS = 120;

    public function __construct(
        private AutorizacaoDevolucaoService $autorizacaoService,
        private DevolucaoEstoqueService $estoqueService,
        private PdvDevolucaoFinanceiroService $financeiroService,
        private NFCeCancelamentoSeguroService $nfceCancelamentoService
    ) {
    }

    public function devolverNaoFiscal(
        int $empresaId,
        int $vendaId,
        $administradorId,
        $senha,
        string $motivo
    ): array {
        $autorizacao = $this->autorizacaoService->autorizar(
            $empresaId,
            $administradorId,
            $senha
        );

        return DB::transaction(function () use ($empresaId, $vendaId, $motivo, $autorizacao) {
            $venda = VendaCaixa::query()
                ->where('id', $vendaId)
                ->where('empresa_id', $empresaId)
                ->lockForUpdate()
                ->firstOrFail();

            // Evita que um fechamento transforme o caixa original em fechado no
            // meio da decisão sobre compensação financeira.
            $this->lockAberturaOriginal($venda);

            $existente = PdvDevolucao::query()
                ->where('venda_caixa_id', $vendaId)
                ->lockForUpdate()
                ->first();

            if ($existente) {
                if ($existente->status === 'concluida') {
                    return [
                        'idempotente' => true,
                        'devolucao_id' => (int) $existente->id,
                        'message' => 'Esta venda já foi devolvida. Nenhum estoque ou valor foi movimentado novamente.',
                    ];
                }

                throw ValidationException::withMessages([
                    'venda' => 'Existe uma devolução desta venda em andamento ou pendente de reconciliação. Regularize a operação existente antes de tentar novamente.',
                ]);
            }

            if ((bool) $venda->retorno_estoque || strtolower((string) $venda->estado_emissao) === 'cancelado') {
                throw ValidationException::withMessages([
                    'venda' => 'Esta venda já está marcada como cancelada/devolvida no sistema.',
                ]);
            }

            if ($this->ehNfceAutorizada($venda)) {
                throw ValidationException::withMessages([
                    'venda' => 'Esta venda possui NFC-e autorizada. Use o cancelamento fiscal para cancelar a SEFAZ antes da devolução local.',
                ]);
            }

            $snapshot = $this->financeiroService->validarPreCondicoes(
                $venda,
                $autorizacao['solicitante']
            );

            $devolucao = $this->criarLedger(
                $venda,
                $autorizacao,
                'nao_fiscal',
                'processando',
                $motivo,
                $snapshot
            );

            $financeiro = $this->financeiroService->processar(
                $venda,
                $devolucao,
                $autorizacao['solicitante'],
                false
            );

            $this->estoqueService->devolver(
                $venda,
                $autorizacao['solicitante'],
                $autorizacao['autorizador']
            );

            $this->autorizacaoService->registrar(
                $venda,
                $autorizacao['solicitante'],
                $autorizacao['autorizador'],
                'devolucao_manual',
                $motivo
            );

            $venda->estado_emissao = 'cancelado';
            $venda->retorno_estoque = 1;
            $venda->save();

            $this->concluirLedger($devolucao, $financeiro, true);

            return [
                'idempotente' => false,
                'devolucao_id' => (int) $devolucao->id,
                'message' => 'Devolução concluída com estoque e financeiro reconciliados.',
            ];
        }, 5);
    }

    public function cancelarFiscal(
        int $empresaId,
        int $vendaId,
        $administradorId,
        $senha,
        string $motivo
    ): array {
        $autorizacao = $this->autorizacaoService->autorizar(
            $empresaId,
            $administradorId,
            $senha
        );

        $preparo = DB::transaction(function () use ($empresaId, $vendaId, $motivo, $autorizacao) {
            $venda = VendaCaixa::query()
                ->where('id', $vendaId)
                ->where('empresa_id', $empresaId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->lockAberturaOriginal($venda);

            $existente = PdvDevolucao::query()
                ->where('venda_caixa_id', $vendaId)
                ->lockForUpdate()
                ->first();

            if ($existente) {
                if ($existente->status === 'concluida') {
                    return [
                        'acao' => 'concluida',
                        'devolucao_id' => (int) $existente->id,
                    ];
                }

                if (in_array($existente->status, ['sefaz_cancelada', 'pendente_financeiro'], true)) {
                    return [
                        'acao' => 'concluir_local',
                        'devolucao_id' => (int) $existente->id,
                    ];
                }

                if ($existente->status === 'aguardando_sefaz' && !$this->operacaoSefazExpirada($existente)) {
                    throw ValidationException::withMessages([
                        'venda' => 'O cancelamento desta NFC-e já está em processamento. Aguarde a conclusão antes de enviar novamente.',
                    ]);
                }

                if (!in_array($existente->status, ['aguardando_sefaz', 'falha_sefaz'], true)) {
                    throw ValidationException::withMessages([
                        'venda' => 'A devolução está em um estado que exige reconciliação antes de novo cancelamento fiscal.',
                    ]);
                }

                // Retry de falha ou operação abandonada: revalida financeiro e caixa.
                // A consulta prévia da própria SEFAZ no serviço fiscal torna o retry
                // idempotente se a homologação anterior ocorreu e a resposta se perdeu.
                $this->financeiroService->validarPreCondicoes(
                    $venda,
                    $autorizacao['solicitante']
                );

                $existente->status = 'aguardando_sefaz';
                $existente->motivo = trim($motivo);
                $existente->usuario_solicitante_id = (int) $autorizacao['solicitante']->id;
                $existente->usuario_solicitante_nome = (string) $autorizacao['solicitante']->nome;
                $existente->usuario_autorizador_id = (int) $autorizacao['autorizador']->id;
                $existente->usuario_autorizador_nome = (string) $autorizacao['autorizador']->nome;
                $existente->save();

                return [
                    'acao' => 'sefaz',
                    'devolucao_id' => (int) $existente->id,
                    'venda' => $venda,
                ];
            }

            if ((bool) $venda->retorno_estoque) {
                throw ValidationException::withMessages([
                    'venda' => 'O estoque desta venda já foi devolvido por um fluxo anterior. A operação exige reconciliação manual antes de novo cancelamento.',
                ]);
            }

            if (!$this->ehNfceAutorizada($venda)) {
                throw ValidationException::withMessages([
                    'venda' => 'A venda não possui uma NFC-e autorizada apta a cancelamento fiscal.',
                ]);
            }

            $snapshot = $this->financeiroService->validarPreCondicoes(
                $venda,
                $autorizacao['solicitante']
            );

            $devolucao = $this->criarLedger(
                $venda,
                $autorizacao,
                'cancelamento_fiscal',
                'aguardando_sefaz',
                $motivo,
                $snapshot
            );

            return [
                'acao' => 'sefaz',
                'devolucao_id' => (int) $devolucao->id,
                'venda' => $venda,
            ];
        }, 5);

        if ($preparo['acao'] === 'concluida') {
            $devolucao = PdvDevolucao::findOrFail($preparo['devolucao_id']);
            return $this->respostaFiscalDoLedger($devolucao, true);
        }

        if ($preparo['acao'] === 'concluir_local') {
            return $this->concluirFiscalLocal((int) $preparo['devolucao_id']);
        }

        /** @var VendaCaixa $venda */
        $venda = $preparo['venda'];

        try {
            $sefaz = $this->nfceCancelamentoService->cancelar($venda, $motivo);
        } catch (\Throwable $e) {
            $this->marcarFalhaSefaz((int) $preparo['devolucao_id'], $e->getMessage());
            throw $e;
        }

        if (!$sefaz['ok']) {
            $this->marcarFalhaSefaz(
                (int) $preparo['devolucao_id'],
                (string) ($sefaz['mensagem'] ?? 'Cancelamento recusado pela SEFAZ.'),
                $sefaz
            );

            return [
                'ok' => false,
                'http_status' => 422,
                'payload' => $sefaz['data'] ?? [
                    'message' => $sefaz['mensagem'] ?? 'Cancelamento recusado pela SEFAZ.',
                ],
            ];
        }

        // Este commit é intencionalmente separado do processamento local. Se o
        // processo cair depois daqui, a próxima tentativa vê sefaz_cancelada e
        // conclui estoque/financeiro SEM reenviar o evento fiscal.
        DB::transaction(function () use ($preparo, $sefaz) {
            $devolucao = PdvDevolucao::query()
                ->where('id', (int) $preparo['devolucao_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $devolucao->status = 'sefaz_cancelada';
            $devolucao->sefaz_cstat = $sefaz['cstat'] ?? null;
            $devolucao->sefaz_protocolo = $sefaz['protocolo'] ?? null;
            $devolucao->sefaz_mensagem = mb_substr((string) ($sefaz['mensagem'] ?? ''), 0, 255);
            $devolucao->sefaz_cancelada_em = now();
            $devolucao->save();
        }, 5);

        return $this->concluirFiscalLocal(
            (int) $preparo['devolucao_id'],
            $sefaz['data'] ?? null
        );
    }

    private function concluirFiscalLocal(int $devolucaoId, ?array $sefazData = null): array
    {
        $resultado = DB::transaction(function () use ($devolucaoId) {
            $devolucao = PdvDevolucao::query()
                ->where('id', $devolucaoId)
                ->lockForUpdate()
                ->firstOrFail();

            $venda = VendaCaixa::query()
                ->where('id', (int) $devolucao->venda_caixa_id)
                ->where('empresa_id', (int) $devolucao->empresa_id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->lockAberturaOriginal($venda);

            if ($devolucao->status === 'concluida') {
                return ['devolucao' => $devolucao, 'pendente' => false];
            }

            if (!in_array($devolucao->status, ['sefaz_cancelada', 'pendente_financeiro'], true)) {
                throw new RuntimeException('A devolução fiscal ainda não possui confirmação de cancelamento da SEFAZ.');
            }

            $solicitante = Usuario::query()
                ->where('id', (int) $devolucao->usuario_solicitante_id)
                ->where('empresa_id', (int) $devolucao->empresa_id)
                ->firstOrFail();

            $autorizador = Usuario::query()
                ->where('id', (int) $devolucao->usuario_autorizador_id)
                ->where('empresa_id', (int) $devolucao->empresa_id)
                ->firstOrFail();

            $financeiro = $this->financeiroService->processar(
                $venda,
                $devolucao,
                $solicitante,
                true
            );

            if (!(bool) $venda->retorno_estoque) {
                $this->estoqueService->devolver($venda, $solicitante, $autorizador);
                $devolucao->estoque_processado_em = now();
            }

            // A verdade fiscal local precisa acompanhar a SEFAZ mesmo que uma
            // reconciliação financeira excepcional fique pendente.
            $venda->estado_emissao = 'cancelado';
            $venda->retorno_estoque = 1;
            $venda->save();

            if (!$this->autorizacaoJaRegistrada($devolucao)) {
                $this->autorizacaoService->registrar(
                    $venda,
                    $solicitante,
                    $autorizador,
                    'cancelamento_fiscal',
                    $devolucao->motivo
                );
            }

            $this->concluirLedger($devolucao, $financeiro, !$financeiro['pendente']);

            return [
                'devolucao' => $devolucao->fresh(),
                'pendente' => (bool) $financeiro['pendente'],
            ];
        }, 5);

        /** @var PdvDevolucao $devolucao */
        $devolucao = $resultado['devolucao'];
        $payload = $sefazData ?: $this->respostaFiscalDoLedger($devolucao, false)['payload'];
        $payload['pdv_devolucao'] = [
            'id' => (int) $devolucao->id,
            'status' => (string) $devolucao->status,
            'pendente_financeiro' => (bool) $resultado['pendente'],
        ];

        return [
            'ok' => true,
            'http_status' => $resultado['pendente'] ? 202 : 200,
            'payload' => $payload,
        ];
    }

    private function criarLedger(
        VendaCaixa $venda,
        array $autorizacao,
        string $tipo,
        string $status,
        string $motivo,
        array $snapshot
    ): PdvDevolucao {
        try {
            return PdvDevolucao::create([
                'empresa_id' => (int) $venda->empresa_id,
                'venda_caixa_id' => (int) $venda->id,
                'tipo' => $tipo,
                'status' => $status,
                'usuario_solicitante_id' => (int) $autorizacao['solicitante']->id,
                'usuario_solicitante_nome' => (string) $autorizacao['solicitante']->nome,
                'usuario_autorizador_id' => (int) $autorizacao['autorizador']->id,
                'usuario_autorizador_nome' => (string) $autorizacao['autorizador']->nome,
                'motivo' => trim($motivo),
                'valor_venda' => (float) $venda->valor_total,
                'filial_id' => $venda->filial_id === null ? null : (int) $venda->filial_id,
                'estoque_filial_id' => Schema::hasColumn('venda_caixas', 'estoque_filial_id') && $venda->estoque_filial_id !== null
                    ? (int) $venda->estoque_filial_id
                    : null,
                'abertura_caixa_original_id' => $venda->abertura_caixa_id ?: null,
                'valor_reembolso_dinheiro' => (float) ($snapshot['valor_dinheiro'] ?? 0),
                'financeiro_json' => json_encode([
                    'pre_operacao' => $snapshot,
                ], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION),
            ]);
        } catch (QueryException $e) {
            // UNIQUE(venda_caixa_id) é a última barreira contra corrida dupla.
            if ((string) $e->getCode() === '23000') {
                $existente = PdvDevolucao::query()
                    ->where('venda_caixa_id', (int) $venda->id)
                    ->first();

                if ($existente) {
                    return $existente;
                }
            }

            throw $e;
        }
    }

    private function concluirLedger(PdvDevolucao $devolucao, array $financeiro, bool $concluir): void
    {
        $devolucao->abertura_caixa_original_id = $financeiro['abertura_original_id'] ?: $devolucao->abertura_caixa_original_id;
        $devolucao->abertura_caixa_compensacao_id = $financeiro['abertura_compensacao_id'];
        $devolucao->valor_reembolso_dinheiro = (float) $financeiro['valor_dinheiro'];
        $devolucao->financeiro_json = json_encode([
            'resultado' => $financeiro,
        ], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        $devolucao->estoque_processado_em = $devolucao->estoque_processado_em ?: now();

        if ($concluir) {
            $devolucao->status = 'concluida';
            $devolucao->financeiro_processado_em = now();
            $devolucao->concluida_em = now();
        } else {
            $devolucao->status = 'pendente_financeiro';
        }

        $devolucao->save();
    }

    private function marcarFalhaSefaz(int $devolucaoId, string $mensagem, ?array $sefaz = null): void
    {
        DB::transaction(function () use ($devolucaoId, $mensagem, $sefaz) {
            $devolucao = PdvDevolucao::query()
                ->where('id', $devolucaoId)
                ->lockForUpdate()
                ->first();

            if (!$devolucao || in_array($devolucao->status, ['sefaz_cancelada', 'concluida'], true)) {
                return;
            }

            $devolucao->status = 'falha_sefaz';
            $devolucao->sefaz_cstat = $sefaz['cstat'] ?? null;
            $devolucao->sefaz_protocolo = $sefaz['protocolo'] ?? null;
            $devolucao->sefaz_mensagem = mb_substr($mensagem, 0, 255);
            $devolucao->save();
        }, 5);
    }

    private function lockAberturaOriginal(VendaCaixa $venda): ?AberturaCaixa
    {
        if ((int) ($venda->abertura_caixa_id ?? 0) > 0) {
            return AberturaCaixa::query()
                ->where('id', (int) $venda->abertura_caixa_id)
                ->where('empresa_id', (int) $venda->empresa_id)
                ->lockForUpdate()
                ->first();
        }

        // Fallback para vendas legadas anteriores ao vínculo explícito do caixa.
        return AberturaCaixa::query()
            ->where('empresa_id', (int) $venda->empresa_id)
            ->where('usuario_id', (int) $venda->usuario_id)
            ->where('created_at', '<=', $venda->created_at)
            ->where(function ($query) use ($venda) {
                $query->where('status', 0)
                    ->orWhere('updated_at', '>=', $venda->created_at);
            })
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();
    }

    private function operacaoSefazExpirada(PdvDevolucao $devolucao): bool
    {
        if (!$devolucao->updated_at) {
            return true;
        }

        return $devolucao->updated_at->lte(
            now()->subSeconds(self::SEFAZ_PROCESSING_TTL_SECONDS)
        );
    }

    private function ehNfceAutorizada(VendaCaixa $venda): bool
    {
        $estado = strtolower(trim((string) $venda->estado_emissao));
        $chave = preg_replace('/\D/', '', (string) $venda->chave);

        return $estado === 'aprovado' && strlen($chave) === 44;
    }

    private function autorizacaoJaRegistrada(PdvDevolucao $devolucao): bool
    {
        return AutorizacaoDevolucao::query()
            ->where('empresa_id', (int) $devolucao->empresa_id)
            ->where('venda_caixa_id', (int) $devolucao->venda_caixa_id)
            ->where('tipo', 'cancelamento_fiscal')
            ->exists();
    }

    private function respostaFiscalDoLedger(PdvDevolucao $devolucao, bool $idempotente): array
    {
        $payload = [
            'retEvento' => [
                'infEvento' => [
                    'cStat' => (string) ($devolucao->sefaz_cstat ?: '135'),
                    'xMotivo' => (string) ($devolucao->sefaz_mensagem ?: 'Cancelamento já processado.'),
                    'nProt' => $devolucao->sefaz_protocolo,
                ],
            ],
            'pdv_devolucao' => [
                'id' => (int) $devolucao->id,
                'status' => (string) $devolucao->status,
                'idempotente' => $idempotente,
                'pendente_financeiro' => $devolucao->status === 'pendente_financeiro',
            ],
        ];

        return [
            'ok' => true,
            'http_status' => $devolucao->status === 'pendente_financeiro' ? 202 : 200,
            'payload' => $payload,
        ];
    }
}
