<?php

namespace App\Services;

use App\Models\ConfigEcommerce;
use App\Models\PedidoEcommerce;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class EcommerceCorreiosService
{
    public function gerarOuConsultarEtiqueta(PedidoEcommerce $pedido, ConfigEcommerce $config): array
    {
        $lock = Cache::lock('ecommerce-correios:' . $pedido->id, 30);

        try {
            return $lock->block(5, function () use ($pedido, $config) {
                $pedido = $pedido->fresh(['cliente', 'endereco', 'empresa.cidade', 'itens.produto.produto']);
                $this->validarPedido($pedido, $config);

                if ($pedido->correios_rotulo_recibo) {
                    return $this->consultarRotulo($pedido, $config);
                }

                $prepostagemId = $pedido->correios_prepostagem_id;
                if (!$prepostagemId) {
                    $prepostagemId = $this->criarPrePostagem($pedido, $config);
                    $pedido->correios_prepostagem_id = $prepostagemId;
                    $pedido->correios_status = 'PREPOSTAGEM_CRIADA';
                    $pedido->save();
                }

                $recibo = $this->solicitarRotulo($prepostagemId, $config);
                $pedido->correios_rotulo_recibo = $recibo;
                $pedido->correios_status = 'ROTULO_PROCESSANDO';
                $pedido->correios_ultima_consulta_at = now();
                $pedido->save();

                return [
                    'status' => 'processing',
                    'message' => 'Etiqueta solicitada aos Correios. O PDF está sendo processado.',
                    'retry_after' => 3,
                ];
            });
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            throw new RuntimeException('A etiqueta deste pedido já está sendo processada. Tente novamente em instantes.');
        } finally {
            optional($lock)->release();
        }
    }

    public function consultarRotulo(PedidoEcommerce $pedido, ConfigEcommerce $config): array
    {
        if (!$pedido->correios_rotulo_recibo) {
            return [
                'status' => 'not_started',
                'message' => 'A geração da etiqueta ainda não foi iniciada.',
            ];
        }

        $response = $this->request($config)
            ->get('https://api.correios.com.br/prepostagem/v1/prepostagens/rotulo/download/assincrono/' . urlencode($pedido->correios_rotulo_recibo));

        $pedido->correios_ultima_consulta_at = now();

        if ($response->failed()) {
            $pedido->correios_status = 'ERRO_ROTULO';
            $pedido->save();
            throw new RuntimeException($this->mensagemErroCorreios($response->json()));
        }

        $json = $response->json();
        if (!empty($json['dados'])) {
            $pdf = base64_decode($json['dados'], true);
            if ($pdf === false) {
                throw new RuntimeException('Os Correios retornaram uma etiqueta inválida.');
            }

            $pedido->correios_status = 'ROTULO_PRONTO';
            $pedido->save();

            return [
                'status' => 'ready',
                'pdf' => $pdf,
                'filename' => 'Etiqueta_Pedido_' . $pedido->id . '.pdf',
            ];
        }

        if (($json['status'] ?? null) === 'ERRO') {
            $pedido->correios_status = 'ERRO_ROTULO';
            $pedido->save();
            throw new RuntimeException($this->mensagemErroCorreios($json));
        }

        $pedido->correios_status = 'ROTULO_PROCESSANDO';
        $pedido->save();

        return [
            'status' => 'processing',
            'message' => 'Os Correios ainda estão processando o PDF da etiqueta.',
            'retry_after' => 3,
        ];
    }

    private function criarPrePostagem(PedidoEcommerce $pedido, ConfigEcommerce $config): string
    {
        $payload = $this->payloadPrePostagem($pedido, $config);
        $response = $this->request($config)
            ->post('https://api.correios.com.br/prepostagem/v1/prepostagens', $payload);

        if ($response->failed()) {
            throw new RuntimeException($this->mensagemErroCorreios($response->json()));
        }

        $id = (string) ($response->json('id') ?? '');
        if ($id === '') {
            throw new RuntimeException('Os Correios não retornaram o identificador da pré-postagem.');
        }

        return $id;
    }

    private function solicitarRotulo(string $prepostagemId, ConfigEcommerce $config): string
    {
        $response = $this->request($config)
            ->post('https://api.correios.com.br/prepostagem/v1/prepostagens/rotulo/assincrono/pdf', [
                'idCorreios' => (string) $config->correios_usuario,
                'numeroCartaoPostagem' => (string) $config->correios_cartao,
                'tipoRotulo' => 'P',
                'formatoRotulo' => 'ET',
                'imprimeRemetente' => 'S',
                'idsPrePostagem' => [$prepostagemId],
            ]);

        if ($response->failed()) {
            throw new RuntimeException($this->mensagemErroCorreios($response->json()));
        }

        $recibo = (string) ($response->json('idRecibo') ?? '');
        if ($recibo === '') {
            throw new RuntimeException('Os Correios não retornaram o recibo de geração da etiqueta.');
        }

        return $recibo;
    }

    private function payloadPrePostagem(PedidoEcommerce $pedido, ConfigEcommerce $config): array
    {
        $empresa = $pedido->empresa;
        $cliente = $pedido->cliente;
        $endereco = $pedido->endereco;

        $servico = strtolower((string) $pedido->tipo_frete) === 'sedex' ? '03220' : '03298';
        $itens = [];
        $pesoTotal = 0;
        $altura = 2;
        $largura = 11;
        $comprimento = 16;

        foreach ($pedido->itens as $item) {
            $produtoEcommerce = $item->produto;
            $produto = optional($produtoEcommerce)->produto;
            if (!$produtoEcommerce) {
                continue;
            }

            $quantidade = max(1, (int) $item->quantidade);
            $pesoUnitarioGramas = max(1, (int) round(((float) ($produto->peso_bruto ?? $produto->peso_liquido ?? 0.1)) * 1000));
            $pesoTotal += $pesoUnitarioGramas * $quantidade;
            $altura = max($altura, (int) ceil((float) ($produto->altura ?? 2)));
            $largura = max($largura, (int) ceil((float) ($produto->largura ?? 11)));
            $comprimento = max($comprimento, (int) ceil((float) ($produto->comprimento ?? 16)));

            $itens[] = [
                'conteudo' => mb_substr(preg_replace('/[^\pL\pN .,_-]/u', '', (string) ($produto->nome ?? 'Produto')), 0, 50),
                'quantidade' => $quantidade,
                'valor' => max(0.01, round((float) $produtoEcommerce->valor, 2)),
                'peso' => $pesoUnitarioGramas,
            ];
        }

        if (!$itens) {
            throw new RuntimeException('O pedido não possui itens válidos para gerar a etiqueta.');
        }

        return [
            'codigoServico' => $servico,
            'destinatario' => [
                'cpfCnpj' => preg_replace('/\D/', '', (string) $cliente->cpf),
                'nome' => mb_substr(trim($cliente->nome . ' ' . $cliente->sobre_nome), 0, 50),
                'dddTelefone' => $this->telefone($cliente->telefone)['ddd'],
                'telefone' => $this->telefone($cliente->telefone)['numero'],
                'email' => mb_substr((string) $cliente->email, 0, 50),
                'endereco' => [
                    'cep' => preg_replace('/\D/', '', (string) $endereco->cep),
                    'logradouro' => mb_substr((string) $endereco->rua, 0, 40),
                    'numero' => mb_substr((string) ($endereco->numero ?: 'SN'), 0, 10),
                    'complemento' => mb_substr((string) ($endereco->complemento ?? ''), 0, 20),
                    'bairro' => mb_substr((string) $endereco->bairro, 0, 20),
                    'cidade' => mb_substr((string) $endereco->cidade, 0, 30),
                    'uf' => strtoupper((string) $endereco->uf),
                ],
            ],
            'remetente' => [
                'cpfCnpj' => preg_replace('/\D/', '', (string) $empresa->cpf_cnpj),
                'nome' => mb_substr((string) ($empresa->nome_fantasia ?: $empresa->razao_social), 0, 50),
                'dddTelefone' => $this->telefone($empresa->telefone)['ddd'],
                'telefone' => $this->telefone($empresa->telefone)['numero'],
                'email' => mb_substr((string) $empresa->email, 0, 50),
                'endereco' => [
                    'cep' => preg_replace('/\D/', '', (string) $empresa->cep),
                    'logradouro' => mb_substr((string) $empresa->rua, 0, 40),
                    'numero' => mb_substr((string) ($empresa->numero ?: 'SN'), 0, 10),
                    'bairro' => mb_substr((string) $empresa->bairro, 0, 20),
                    'cidade' => mb_substr((string) optional($empresa->cidade)->nome, 0, 30),
                    'uf' => strtoupper((string) optional($empresa->cidade)->uf),
                ],
            ],
            'numeroCartaoPostagem' => (string) $config->correios_cartao,
            'pesoInformado' => max(1, $pesoTotal),
            'codigoFormatoObjetoInformado' => 2,
            'alturaInformada' => $altura,
            'larguraInformada' => $largura,
            'comprimentoInformado' => $comprimento,
            'cienteObjetoNaoProibido' => '1',
            'numeroNotaFiscal' => (string) ($pedido->numero_nfe ?: $pedido->id),
            'itensDeclaracaoConteudo' => $itens,
        ];
    }

    private function request(ConfigEcommerce $config): PendingRequest
    {
        $this->validarCredenciais($config);
        $token = $this->token($config);

        return Http::acceptJson()
            ->asJson()
            ->withToken($token)
            ->timeout(20)
            ->retry(2, 300, throw: false);
    }

    private function token(ConfigEcommerce $config): string
    {
        return Cache::remember('correios-token:' . $config->id, now()->addMinutes(50), function () use ($config) {
            $basic = base64_encode($config->correios_usuario . ':' . $config->correios_senha);
            $response = Http::acceptJson()
                ->asJson()
                ->withHeaders(['Authorization' => 'Basic ' . $basic])
                ->timeout(15)
                ->retry(2, 300, throw: false)
                ->post('https://api.correios.com.br/token/v1/autentica/cartaopostagem', [
                    'numero' => (string) $config->correios_cartao,
                ]);

            if ($response->failed() || !$response->json('token')) {
                Log::warning('Falha ao autenticar Correios no ecommerce.', [
                    'config_id' => $config->id,
                    'status' => $response->status(),
                ]);
                throw new RuntimeException('Não foi possível autenticar nos Correios. Revise usuário, senha e cartão de postagem.');
            }

            return (string) $response->json('token');
        });
    }

    private function validarCredenciais(ConfigEcommerce $config): void
    {
        if (!$config->correios_usuario || !$config->correios_senha || !$config->correios_cartao) {
            throw new RuntimeException('Credenciais dos Correios não configuradas para esta loja.');
        }
    }

    private function validarPedido(PedidoEcommerce $pedido, ConfigEcommerce $config): void
    {
        if ((int) $pedido->empresa_id !== (int) $config->empresa_id) {
            throw new RuntimeException('Pedido inválido para a empresa atual.');
        }
        if (!in_array(strtolower((string) $pedido->tipo_frete), ['pac', 'sedex'], true)) {
            throw new RuntimeException('Este pedido não usa um serviço dos Correios.');
        }
        if (!$pedido->cliente || !$pedido->endereco || !$pedido->empresa) {
            throw new RuntimeException('Pedido sem cliente, endereço ou empresa completos.');
        }
        if (!in_array((string) $pedido->status_pagamento, ['approved', 'pending', 'in_process'], true)) {
            throw new RuntimeException('O pagamento do pedido não está em um estado válido para postagem.');
        }
    }

    private function telefone(?string $telefone): array
    {
        $numero = preg_replace('/\D/', '', (string) $telefone);
        if (str_starts_with($numero, '55') && strlen($numero) > 11) {
            $numero = substr($numero, 2);
        }
        return [
            'ddd' => substr($numero, 0, 2) ?: '00',
            'numero' => substr($numero, 2) ?: '00000000',
        ];
    }

    private function mensagemErroCorreios($erro): string
    {
        $texto = is_array($erro) ? json_encode($erro, JSON_UNESCAPED_UNICODE) : (string) $erro;

        return match (true) {
            str_contains($texto, 'RTL-076'), str_contains($texto, 'UF do remetente') => 'O CEP e a UF do remetente não combinam. Revise o cadastro da empresa.',
            str_contains(strtolower($texto), 'cep de destino') => 'O CEP do cliente é inválido ou não é aceito pelos Correios.',
            str_contains(strtolower($texto), 'peso') => 'O peso informado está fora dos limites do serviço selecionado. Revise os produtos.',
            str_contains(strtolower($texto), 'comprimento'), str_contains(strtolower($texto), 'largura'), str_contains(strtolower($texto), 'altura') => 'As dimensões da embalagem estão fora dos limites dos Correios.',
            str_contains(strtolower($texto), 'cpfcnpj'), str_contains(strtolower($texto), 'documento') => 'O CPF/CNPJ do cliente ou da empresa é inválido.',
            default => 'Os Correios recusaram os dados da postagem. Revise endereço, documentos, peso e dimensões.',
        };
    }
}