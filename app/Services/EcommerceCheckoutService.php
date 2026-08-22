<?php

namespace App\Services;

use App\Models\ConfigEcommerce;
use App\Models\CupomDescontoEcommerce;
use App\Models\CupomEcommerceUtilizado;
use App\Models\EnderecoEcommerce;
use App\Models\PedidoEcommerce;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class EcommerceCheckoutService
{
    private const CORREIOS_TOKEN_URL = 'https://api.correios.com.br/token/v1/autentica/cartaopostagem';
    private const CORREIOS_PRECO_URL = 'https://api.correios.com.br/preco/v1/nacional/';
    private const CORREIOS_PRAZO_URL = 'https://api.correios.com.br/prazo/v1/nacional/';
    private const CORREIOS_PAC = '03298';
    private const CORREIOS_SEDEX = '03220';

    public function resumo(
        PedidoEcommerce $pedido,
        ConfigEcommerce $config,
        EnderecoEcommerce $endereco,
        string $tipoFrete,
        ?string $codigoCupom = null
    ): array {
        $this->validarPedido($pedido, $config);

        $subtotal = $this->subtotal($pedido);
        $frete = $this->calcularFrete($pedido, $config, $endereco->cep, $tipoFrete);
        $cupom = $this->calcularCupom($pedido, $config, $codigoCupom, $subtotal);

        $total = round(max(0, $subtotal + $frete['valor'] - $cupom['desconto']), 2);

        return [
            'subtotal' => $subtotal,
            'valor_frete' => $frete['valor'],
            'tipo_frete' => $tipoFrete,
            'prazo_frete' => $frete['prazo'],
            'cupom' => $cupom['cupom'],
            'cupom_codigo' => $cupom['codigo'],
            'desconto' => $cupom['desconto'],
            'total' => $total,
        ];
    }

    public function opcoesFrete(PedidoEcommerce $pedido, ConfigEcommerce $config, string $cep): array
    {
        $this->validarPedido($pedido, $config);

        $cep = $this->normalizarCep($cep);
        $subtotal = $this->subtotal($pedido);
        $freteGratis = $this->temFreteGratis($subtotal, $config);
        $retirada = (int) ($config->habilitar_retirada ?? 0);

        $resultado = [
            'preco' => null,
            'prazo' => null,
            'preco_sedex' => null,
            'prazo_sedex' => null,
            'frete_gratis' => $freteGratis,
            'habilitar_retirada' => $retirada,
        ];

        if (!$this->temCredenciaisCorreios($config)) {
            if ($freteGratis || $retirada === 1) {
                $resultado['aviso'] = 'PAC e SEDEX não estão configurados nesta loja.';
                return $resultado;
            }

            throw new RuntimeException('O cálculo de frete pelos Correios não está configurado nesta loja.');
        }

        $erros = [];

        try {
            $pac = $this->consultarCorreios($pedido, $config, $cep, 'pac');
            $resultado['preco'] = number_format($pac['valor'], 2, ',', '.');
            $resultado['prazo'] = (string) $pac['prazo'];
        } catch (\Throwable $e) {
            report($e);
            $erros[] = 'PAC: ' . $e->getMessage();
        }

        try {
            $sedex = $this->consultarCorreios($pedido, $config, $cep, 'sedex');
            $resultado['preco_sedex'] = number_format($sedex['valor'], 2, ',', '.');
            $resultado['prazo_sedex'] = (string) $sedex['prazo'];
        } catch (\Throwable $e) {
            report($e);
            $erros[] = 'SEDEX: ' . $e->getMessage();
        }

        if ($resultado['preco'] === null && $resultado['preco_sedex'] === null) {
            if (!$freteGratis && $retirada !== 1) {
                throw new RuntimeException($erros[0] ?? 'Não foi possível calcular o frete pelos Correios.');
            }

            $resultado['aviso'] = $erros[0] ?? 'PAC e SEDEX indisponíveis para este CEP.';
        }

        return $resultado;
    }

    public function calcularCupom(
        PedidoEcommerce $pedido,
        ConfigEcommerce $config,
        ?string $codigoCupom,
        ?float $subtotal = null
    ): array {
        $codigo = trim((string) $codigoCupom);
        $subtotal = $subtotal ?? $this->subtotal($pedido);

        if ($codigo === '') {
            return [
                'cupom' => null,
                'codigo' => null,
                'desconto' => 0.0,
            ];
        }

        $cupom = CupomDescontoEcommerce::where('empresa_id', $config->empresa_id)
            ->where('codigo', $codigo)
            ->where('status', 1)
            ->first();

        if (!$cupom) {
            throw new RuntimeException('Cupom inválido ou inativo.');
        }

        $minimo = max(0, (float) ($cupom->valor_minimo_pedido ?? 0));
        if ($subtotal < $minimo) {
            throw new RuntimeException(
                'Este cupom exige pedido mínimo de R$ ' . number_format($minimo, 2, ',', '.') . '.'
            );
        }

        if ($pedido->cliente_id && Schema::hasTable('cupom_ecommerce_utilizados')) {
            $jaUtilizado = CupomEcommerceUtilizado::where('cupom_id', $cupom->id)
                ->where('cliente_id', $pedido->cliente_id)
                ->exists();

            if ($jaUtilizado) {
                throw new RuntimeException('Este cupom já foi utilizado por você.');
            }
        }

        $valorCupom = max(0, (float) $cupom->valor);
        if ($cupom->tipo === 'fixo') {
            $desconto = min($subtotal, $valorCupom);
        } else {
            $percentual = min(100, $valorCupom);
            $desconto = $subtotal * ($percentual / 100);
        }

        return [
            'cupom' => $cupom,
            'codigo' => $cupom->codigo,
            'desconto' => round(min($subtotal, max(0, $desconto)), 2),
        ];
    }

    public function totaisPorFormaPagamento(float $total, ConfigEcommerce $config): object
    {
        $totais = new \stdClass();
        $totais->total_pix = $this->aplicarPercentual($total, (float) ($config->desconto_padrao_pix ?? 0));
        $totais->total_cartao = $this->aplicarPercentual($total, (float) ($config->desconto_padrao_cartao ?? 0));
        $totais->total_boleto = $this->aplicarPercentual($total, (float) ($config->desconto_padrao_boleto ?? 0));

        return $totais;
    }

    public function salvarResumo(
        PedidoEcommerce $pedido,
        EnderecoEcommerce $endereco,
        array $resumo
    ): PedidoEcommerce {
        $pedido->endereco_id = $endereco->id;
        $pedido->valor_frete = $resumo['valor_frete'];
        $pedido->valor_total = $resumo['total'];
        $pedido->tipo_frete = $resumo['tipo_frete'];
        $pedido->desconto = $resumo['desconto'];
        $pedido->cupom_desconto = $resumo['cupom_codigo'] ?? '';
        $pedido->save();

        return $pedido->fresh();
    }

    public function calcularFrete(
        PedidoEcommerce $pedido,
        ConfigEcommerce $config,
        string $cep,
        string $tipoFrete
    ): array {
        $tipoFrete = strtolower(trim($tipoFrete));
        $permitidos = ['pac', 'sedex', 'gratis', 'retirada'];

        if (!in_array($tipoFrete, $permitidos, true)) {
            throw new RuntimeException('Selecione uma forma de entrega válida.');
        }

        $subtotal = $this->subtotal($pedido);

        if ($tipoFrete === 'retirada') {
            if (!(bool) $config->habilitar_retirada) {
                throw new RuntimeException('Retirada na loja não está disponível.');
            }

            return ['valor' => 0.0, 'prazo' => 0];
        }

        if ($tipoFrete === 'gratis') {
            if (!$this->temFreteGratis($subtotal, $config)) {
                throw new RuntimeException('Este pedido não atende às regras de frete grátis.');
            }

            return ['valor' => 0.0, 'prazo' => 0];
        }

        if (!$this->temCredenciaisCorreios($config)) {
            throw new RuntimeException('O cálculo de frete pelos Correios não está configurado nesta loja.');
        }

        return $this->consultarCorreios($pedido, $config, $this->normalizarCep($cep), $tipoFrete);
    }

    private function consultarCorreios(
        PedidoEcommerce $pedido,
        ConfigEcommerce $config,
        string $cepDestino,
        string $tipoFrete
    ): array {
        $cepOrigem = $this->normalizarCep((string) $config->cep);

        $peso = max(0.1, (float) $pedido->somaPeso());
        $dimensoes = $pedido->somaDimensoes();

        $psObjeto = (int) max(100, ceil($peso * 1000));
        $comprimento = (int) max(15, ceil((float) ($dimensoes['comprimento'] ?? 20)));
        $altura = (int) max(2, ceil((float) ($dimensoes['altura'] ?? 5)));
        $largura = (int) max(11, ceil((float) ($dimensoes['largura'] ?? 15)));

        // Os códigos atuais da API de Preço para contrato são 03298 (PAC)
        // e 03220 (SEDEX). Não usar os códigos legados 04510/04014.
        $servico = $tipoFrete === 'sedex' ? self::CORREIOS_SEDEX : self::CORREIOS_PAC;

        $params = [
            'cepDestino' => $cepDestino,
            'cepOrigem' => $cepOrigem,
            'psObjeto' => $psObjeto,
            'tpObjeto' => 2,
            'comprimento' => $comprimento,
            'altura' => $altura,
            'largura' => $largura,
        ];

        $precoResponse = $this->getCorreios(self::CORREIOS_PRECO_URL . $servico, $params, $config);

        if ($precoResponse->failed()) {
            $this->registrarErroCorreios('preco', $precoResponse, $config, [
                'servico' => $servico,
                'cep_origem' => $cepOrigem,
                'cep_destino' => $cepDestino,
            ]);

            throw new RuntimeException($this->mensagemErroCorreios($precoResponse, 'preço'));
        }

        $precoData = $precoResponse->json();

        // A API atual retorna pcFinal. Mantemos fallbacks para instalações/respostas antigas.
        $valor = $this->valorCorreios(
            $precoData['pcFinal']
                ?? $precoData['valorCobrado']
                ?? $precoData['pcProduto']
                ?? null
        );

        if ($valor <= 0) {
            Log::warning('Correios retornou preço sem valor válido no e-commerce.', [
                'empresa_id' => $config->empresa_id,
                'servico' => $servico,
                'retorno' => $precoData,
            ]);

            throw new RuntimeException('Os Correios não retornaram um valor válido para este CEP.');
        }

        $prazo = 0;

        try {
            $prazoResponse = $this->getCorreios(
                self::CORREIOS_PRAZO_URL . $servico,
                [
                    'cepOrigem' => $cepOrigem,
                    'cepDestino' => $cepDestino,
                ],
                $config
            );

            if ($prazoResponse->successful()) {
                $prazoData = $prazoResponse->json();
                $prazo = (int) ($prazoData['prazoEntrega'] ?? 0);
            } else {
                $this->registrarErroCorreios('prazo', $prazoResponse, $config, [
                    'servico' => $servico,
                    'cep_origem' => $cepOrigem,
                    'cep_destino' => $cepDestino,
                ]);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return [
            'valor' => round($valor, 2),
            'prazo' => $prazo,
        ];
    }

    private function getCorreios(string $url, array $params, ConfigEcommerce $config): Response
    {
        $token = $this->tokenCorreios($config);

        $response = Http::acceptJson()
            ->withToken($token)
            ->timeout(15)
            ->retry(2, 300, throw: false)
            ->get($url, $params);

        // Se o token expirou antes do cache, renova uma única vez e repete.
        if (in_array($response->status(), [401, 403], true)) {
            Cache::forget($this->tokenCacheKey($config));
            $token = $this->tokenCorreios($config, true);

            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(15)
                ->retry(1, 300, throw: false)
                ->get($url, $params);
        }

        return $response;
    }

    private function tokenCorreios(ConfigEcommerce $config, bool $forcar = false): string
    {
        if (!$this->temCredenciaisCorreios($config)) {
            throw new RuntimeException('Credenciais dos Correios incompletas.');
        }

        $cacheKey = $this->tokenCacheKey($config);

        if ($forcar) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, now()->addMinutes(45), function () use ($config) {
            $accessBasic = base64_encode(
                trim((string) $config->correios_usuario)
                . ':'
                . trim((string) $config->correios_senha)
            );

            $response = Http::acceptJson()
                ->asJson()
                ->withHeaders(['Authorization' => 'Basic ' . $accessBasic])
                ->timeout(15)
                ->retry(2, 300, throw: false)
                ->post(self::CORREIOS_TOKEN_URL, [
                    'numero' => preg_replace('/\D/', '', (string) $config->correios_cartao),
                ]);

            if ($response->failed() || !$response->json('token')) {
                $this->registrarErroCorreios('token', $response, $config);

                if ($response->status() === 401) {
                    throw new RuntimeException('Usuário ou código de acesso da API dos Correios inválido.');
                }

                if ($response->status() === 403) {
                    throw new RuntimeException('O cartão de postagem não está autorizado para este usuário dos Correios.');
                }

                throw new RuntimeException('Não foi possível autenticar nos Correios. Revise usuário, código de acesso da API e cartão de postagem.');
            }

            return (string) $response->json('token');
        });
    }

    private function tokenCacheKey(ConfigEcommerce $config): string
    {
        return 'ecommerce-correios-token:' . $config->id . ':' . hash('sha256', implode('|', [
            (string) $config->correios_usuario,
            (string) $config->correios_cartao,
            (string) $config->correios_senha,
        ]));
    }

    private function registrarErroCorreios(
        string $etapa,
        Response $response,
        ConfigEcommerce $config,
        array $contexto = []
    ): void {
        Log::warning('Falha na API dos Correios no e-commerce.', array_merge([
            'etapa' => $etapa,
            'empresa_id' => $config->empresa_id,
            'http_status' => $response->status(),
            'retorno' => $response->json() ?: mb_substr($response->body(), 0, 1500),
        ], $contexto));
    }

    private function mensagemErroCorreios(Response $response, string $etapa): string
    {
        $json = $response->json();
        $texto = is_array($json)
            ? json_encode($json, JSON_UNESCAPED_UNICODE)
            : (string) $response->body();
        $textoLower = mb_strtolower($texto);

        if ($response->status() === 401) {
            return 'A autenticação dos Correios expirou ou está inválida.';
        }

        if ($response->status() === 403) {
            return 'Seu contrato/cartão dos Correios não possui acesso à API de ' . $etapa . '. Verifique no CWS se a API está liberada.';
        }

        if (str_contains($textoLower, 'cep')) {
            return 'Os Correios recusaram o CEP informado. Confira o CEP de origem e o CEP de destino.';
        }

        if (str_contains($textoLower, 'peso')) {
            return 'O peso do pacote não foi aceito pelos Correios. Revise o peso dos produtos.';
        }

        if (str_contains($textoLower, 'dimens') || str_contains($textoLower, 'comprimento') || str_contains($textoLower, 'largura') || str_contains($textoLower, 'altura')) {
            return 'As dimensões do pacote não foram aceitas pelos Correios. Revise os produtos.';
        }

        if (str_contains($textoLower, 'produto') || str_contains($textoLower, 'serviço') || str_contains($textoLower, 'servico')) {
            return 'O serviço PAC/SEDEX não está disponível ou habilitado no contrato dos Correios.';
        }

        return 'Não foi possível consultar o ' . $etapa . ' nos Correios agora.';
    }

    private function subtotal(PedidoEcommerce $pedido): float
    {
        $subtotal = round((float) $pedido->somaItens(), 2);

        if ($subtotal <= 0) {
            throw new RuntimeException('O carrinho está vazio.');
        }

        return $subtotal;
    }

    private function validarPedido(PedidoEcommerce $pedido, ConfigEcommerce $config): void
    {
        if ((int) $pedido->empresa_id !== (int) $config->empresa_id) {
            throw new RuntimeException('O pedido não pertence a esta loja.');
        }

        foreach ($pedido->itens as $item) {
            if (!$item->produto || (int) $item->produto->empresa_id !== (int) $config->empresa_id) {
                throw new RuntimeException('O carrinho contém um item inválido para esta loja.');
            }

            if (!(bool) $item->produto->status) {
                throw new RuntimeException('Um dos produtos do carrinho não está mais disponível.');
            }
        }
    }

    private function temFreteGratis(float $subtotal, ConfigEcommerce $config): bool
    {
        $limite = (float) ($config->frete_gratis_valor ?? 0);
        return $limite > 0 && $subtotal >= $limite;
    }

    private function temCredenciaisCorreios(ConfigEcommerce $config): bool
    {
        return trim((string) $config->correios_usuario) !== ''
            && trim((string) $config->correios_senha) !== ''
            && trim((string) $config->correios_cartao) !== ''
            && trim((string) $config->cep) !== '';
    }

    private function normalizarCep(string $cep): string
    {
        $cep = preg_replace('/\D/', '', $cep);

        if (strlen($cep) !== 8) {
            throw new RuntimeException('Informe um CEP válido.');
        }

        return $cep;
    }

    private function valorCorreios($valor): float
    {
        if ($valor === null || $valor === '') {
            return 0.0;
        }

        $valor = trim((string) $valor);
        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return (float) preg_replace('/[^0-9.\-]/', '', $valor);
    }

    private function aplicarPercentual(float $total, float $percentual): float
    {
        $percentual = min(100, max(0, $percentual));
        return round(max(0, $total - ($total * ($percentual / 100))), 2);
    }
}