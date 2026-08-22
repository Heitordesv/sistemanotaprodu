<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SistemaAiDatabaseContextService
{
    private const MAX_TABLES_WITH_DATA = 10;
    private const MAX_ROWS_PER_TABLE = 8;
    private const MAX_COLUMNS_PER_TABLE = 18;
    private const MAX_SCHEMA_TABLES = 220;
    private const SCHEMA_CACHE_MINUTES = 30;

    private array $blockedTables = [
        'migrations',
        'jobs',
        'failed_jobs',
        'job_batches',
        'sessions',
        'cache',
        'cache_locks',
        'empresa_integracoes',
        'certificados',
    ];

    private array $allowedExactColumns = [
        'id', 'empresa_id', 'cliente_id', 'fornecedor_id', 'produto_id', 'venda_id', 'venda_caixa_id',
        'compra_id', 'ordem_servico_id', 'usuario_id', 'categoria_id', 'filial_id', 'funcionario_id',
        'razao_social', 'nome_fantasia', 'nome', 'descricao', 'referencia', 'observacao',
        'status', 'estado', 'cpf_cnpj', 'email', 'telefone', 'celular', 'codBarras', 'NCM', 'CEST',
        'CST_CSOSN', 'CFOP_saida_estadual', 'CFOP_saida_inter_estadual', 'CFOP_entrada_estadual',
        'CFOP_entrada_inter_estadual', 'tipo_pagamento', 'forma_pagamento', 'estado_emissao', 'numero_nfe',
        'valor_total', 'valor_venda', 'valor_compra', 'valor_integral', 'valor_recebido', 'valor_pago',
        'quantidade', 'estoque_minimo', 'data_vencimento', 'data_recebimento', 'data_pagamento',
        'data_emissao', 'created_at', 'updated_at',
    ];

    public function buildContext(int $empresaId, string $question): string
    {
        $metadata = [];
        $questionNormalized = $this->normalize($question);

        foreach ($this->loadSchemaMetadata() as $table => $columns) {
            if (in_array($table, $this->blockedTables, true)) {
                continue;
            }

            $safeColumns = array_values(array_filter(
                $columns,
                fn (array $column) => $this->isAllowedColumn($column['name'])
            ));

            if (empty($safeColumns)) {
                continue;
            }

            $metadata[] = [
                'table' => $table,
                'columns' => $safeColumns,
                'tenant_mode' => $this->tenantMode($table, $columns),
                'score' => $this->scoreTable($table, $safeColumns, $questionNormalized),
            ];
        }

        usort($metadata, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        $candidates = array_values(array_filter(
            $metadata,
            fn (array $meta) => $meta['score'] > 0 && $meta['tenant_mode'] !== 'schema_only'
        ));

        if (empty($candidates)) {
            $candidates = array_values(array_filter(
                $metadata,
                fn (array $meta) => $meta['tenant_mode'] !== 'schema_only'
            ));
        }

        $candidates = array_slice($candidates, 0, self::MAX_TABLES_WITH_DATA);
        $dataBlocks = [];

        foreach ($candidates as $meta) {
            $dataBlocks[] = $this->tableDataBlock($meta, $empresaId, $questionNormalized);
        }

        return implode("\n", [
            'CONTEXTO DO BANCO DE DADOS DA EMPRESA LOGADA',
            'Empresa autorizada: ' . $empresaId,
            'Modo: somente leitura.',
            'Regra: dados só são lidos de tabelas com vínculo direto à empresa logada.',
            '',
            'TABELAS CONHECIDAS:',
            $this->schemaSummary($metadata),
            '',
            'DADOS MAIS RELEVANTES PARA A PERGUNTA:',
            implode("\n\n", array_filter($dataBlocks)),
        ]);
    }

    /**
     * Antes o serviço fazia 1 consulta para listar tabelas e depois mais 1 consulta
     * ao information_schema para CADA tabela. Agora o schema inteiro é lido em uma
     * única consulta e fica em cache por 30 minutos.
     */
    private function loadSchemaMetadata(): array
    {
        $database = DB::getDatabaseName();
        $cacheKey = 'sistema_ai:schema:' . sha1($database);

        return Cache::remember($cacheKey, now()->addMinutes(self::SCHEMA_CACHE_MINUTES), function () use ($database) {
            $rows = DB::select(
                'SELECT c.TABLE_NAME AS table_name,
                        c.COLUMN_NAME AS column_name,
                        c.DATA_TYPE AS data_type,
                        c.ORDINAL_POSITION AS ordinal_position
                 FROM information_schema.COLUMNS c
                 INNER JOIN information_schema.TABLES t
                    ON t.TABLE_SCHEMA = c.TABLE_SCHEMA
                   AND t.TABLE_NAME = c.TABLE_NAME
                 WHERE c.TABLE_SCHEMA = ?
                   AND t.TABLE_TYPE = ?
                 ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION',
                [$database, 'BASE TABLE']
            );

            $metadata = [];
            foreach ($rows as $row) {
                $table = (string) $row->table_name;
                $metadata[$table] ??= [];
                $metadata[$table][] = [
                    'name' => (string) $row->column_name,
                    'type' => strtolower((string) $row->data_type),
                ];
            }

            return $metadata;
        });
    }

    private function tenantMode(string $table, array $columns): string
    {
        $names = array_column($columns, 'name');

        if (in_array('empresa_id', $names, true)) {
            return 'empresa_id';
        }

        if ($table === 'empresas' && in_array('id', $names, true)) {
            return 'empresa_primary_key';
        }

        return 'schema_only';
    }

    private function isAllowedColumn(string $column): bool
    {
        if (in_array($column, $this->allowedExactColumns, true)) {
            return true;
        }

        foreach (['data_', 'valor_', 'quantidade_', 'numero_', 'status_', 'estado_', 'nome_', 'descricao_'] as $prefix) {
            if (str_starts_with($column, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function scoreTable(string $table, array $columns, string $question): int
    {
        $score = 0;
        $tableText = $this->normalize(str_replace('_', ' ', $table));

        foreach ($this->tokens($question) as $token) {
            if (str_contains($tableText, $token)) {
                $score += 12;
            }

            foreach ($columns as $column) {
                if (str_contains($this->normalize($column['name']), $token)) {
                    $score += 2;
                }
            }
        }

        foreach ($this->businessAliases() as $alias => $relatedTables) {
            if (!str_contains($question, $this->normalize($alias))) {
                continue;
            }

            foreach ($relatedTables as $relatedTable) {
                if ($table === $relatedTable || str_contains($table, $relatedTable)) {
                    $score += 30;
                }
            }
        }

        return $score;
    }

    private function businessAliases(): array
    {
        return [
            'cliente' => ['clientes'],
            'fornecedor' => ['fornecedors'],
            'produto' => ['produtos', 'estoques'],
            'estoque' => ['estoques', 'produtos'],
            'venda' => ['vendas', 'venda_caixas'],
            'pdv' => ['venda_caixas', 'caixas'],
            'caixa' => ['caixas', 'fluxo_caixas', 'venda_caixas'],
            'receber' => ['conta_recebers'],
            'cobranca' => ['conta_recebers', 'boletos'],
            'pagar' => ['conta_pagars'],
            'despesa' => ['conta_pagars'],
            'compra' => ['compras', 'item_compras'],
            'ordem de servico' => ['ordem_servicos'],
            'os' => ['ordem_servicos'],
            'orcamento' => ['orcamentos', 'orcamento_vendas'],
            'nfe' => ['vendas'],
            'nfce' => ['venda_caixas'],
            'nfse' => ['nfses'],
            'whatsapp' => ['whatsapp_message_logs', 'mensagem_personalizadas'],
            'lead' => ['leads', 'lead_observacoes'],
            'usuario' => ['usuarios'],
        ];
    }

    private function tableDataBlock(array $meta, int $empresaId, string $question): string
    {
        $columns = array_slice(array_column($meta['columns'], 'name'), 0, self::MAX_COLUMNS_PER_TABLE);

        try {
            $base = DB::table($meta['table'])->select($columns);

            if ($meta['tenant_mode'] === 'empresa_id') {
                $base->where('empresa_id', $empresaId);
            } elseif ($meta['tenant_mode'] === 'empresa_primary_key') {
                $base->where('id', $empresaId);
            } else {
                return '';
            }

            $total = Cache::remember(
                'sistema_ai:count:' . $empresaId . ':' . $meta['table'],
                now()->addMinutes(2),
                fn () => (clone $base)->count()
            );

            $query = clone $base;
            $terms = $this->searchTerms($question);
            $textColumns = array_values(array_map(
                fn (array $column) => $column['name'],
                array_filter(
                    $meta['columns'],
                    fn (array $column) => in_array($column['type'], ['char', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext'], true)
                )
            ));
            $textColumns = array_values(array_intersect($textColumns, $columns));

            if (!empty($terms) && !empty($textColumns)) {
                foreach (array_slice($terms, 0, 3) as $term) {
                    $query->where(function ($sub) use ($textColumns, $term) {
                        foreach ($textColumns as $index => $column) {
                            $index === 0
                                ? $sub->where($column, 'LIKE', '%' . $term . '%')
                                : $sub->orWhere($column, 'LIKE', '%' . $term . '%');
                        }
                    });
                }
            }

            if (in_array('id', $columns, true)) {
                $query->orderByDesc('id');
            }

            $rows = $query
                ->limit(self::MAX_ROWS_PER_TABLE)
                ->get()
                ->map(fn ($row) => (array) $row)
                ->values()
                ->all();

            if (empty($rows) && !empty($terms)) {
                $fallback = clone $base;
                if (in_array('id', $columns, true)) {
                    $fallback->orderByDesc('id');
                }
                $rows = $fallback->limit(3)->get()->map(fn ($row) => (array) $row)->values()->all();
            }

            return "TABELA: {$meta['table']}\nTOTAL DA EMPRESA: {$total}\nDADOS: "
                . json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            return "TABELA: {$meta['table']} - consulta indisponível para esta pergunta.";
        }
    }

    private function schemaSummary(array $metadata): string
    {
        $lines = [];

        foreach (array_slice($metadata, 0, self::MAX_SCHEMA_TABLES) as $meta) {
            $mode = $meta['tenant_mode'] === 'schema_only' ? 'somente estrutura' : 'consulta da empresa';
            $columns = array_slice(array_column($meta['columns'], 'name'), 0, 10);
            $lines[] = '- ' . $meta['table'] . ' [' . $mode . '] ' . implode(', ', $columns);
        }

        return implode("\n", $lines);
    }

    private function searchTerms(string $question): array
    {
        $stop = [
            'onde', 'como', 'quero', 'preciso', 'mostrar', 'mostra', 'consultar', 'consulta',
            'sistema', 'empresa', 'tabela', 'dados', 'cliente', 'clientes', 'produto', 'produtos',
            'venda', 'vendas', 'conta', 'contas'
        ];

        return array_values(array_filter(
            $this->tokens($question),
            fn ($token) => mb_strlen($token) >= 3 && !in_array($token, $stop, true)
        ));
    }

    private function tokens(string $text): array
    {
        $parts = preg_split('/[^a-z0-9]+/', $this->normalize($text), -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_unique($parts ?: []));
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $value = strtr($value, [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'é' => 'e', 'ê' => 'e',
            'í' => 'i', 'ó' => 'o', 'õ' => 'o', 'ô' => 'o', 'ú' => 'u', 'ç' => 'c',
        ]);

        return preg_replace('/\s+/', ' ', $value) ?: $value;
    }
}