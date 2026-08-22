<?php

namespace App\Http\Controllers;

use App\Helpers\PedidoEcommerceHelper;
use App\Models\ClienteEcommerce;
use App\Models\ConfigEcommerce;
use App\Models\CupomEcommerceUtilizado;
use App\Models\EnderecoEcommerce;
use App\Models\PedidoEcommerce;
use App\Rules\ValidaDocumento;
use App\Services\EcommerceCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EcommerceCheckoutSecurityController extends Controller
{
    public function __construct(private EcommerceCheckoutService $checkoutService)
    {
    }

    private function configLoja(string $link): ConfigEcommerce
    {
        return ConfigEcommerce::where('link', strtolower($link))->firstOrFail();
    }

    private function carrinhoDaLoja(ConfigEcommerce $config): PedidoEcommerce
    {
        $pedido = (new PedidoEcommerceHelper())->getCarrinho($config->empresa_id);
        abort_if(!$pedido, 404, 'Carrinho não encontrado.');
        abort_if((int) $pedido->empresa_id !== (int) $config->empresa_id, 403);

        return $pedido;
    }

    private function clienteDaSessao(ConfigEcommerce $config): ?ClienteEcommerce
    {
        $sessao = session('user_ecommerce');
        if (!$sessao) {
            return null;
        }

        if ((int) ($sessao['empresa_id'] ?? 0) !== (int) $config->empresa_id) {
            return null;
        }

        return ClienteEcommerce::where('id', $sessao['cliente_id'])
            ->where('empresa_id', $config->empresa_id)
            ->where('status', 1)
            ->first();
    }

    public function checkoutStore(Request $request, string $link)
    {
        $config = $this->configLoja($link);
        $pedido = $this->carrinhoDaLoja($config);

        if ($this->clienteDaSessao($config)) {
            return redirect('/loja/' . strtolower($config->link) . '/endereco');
        }

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:30'],
            'sobre_nome' => ['required', 'string', 'max:30'],
            'cpf' => [
                'required',
                new ValidaDocumento,
                Rule::unique('cliente_ecommerces', 'cpf')->where(fn ($q) => $q->where('empresa_id', $config->empresa_id)),
            ],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('cliente_ecommerces', 'email')->where(fn ($q) => $q->where('empresa_id', $config->empresa_id)),
            ],
            'senha' => ['required', 'string', 'min:8', 'max:72'],
            'telefone' => ['required', 'string', 'max:20'],
            'ie' => ['nullable', 'string', 'max:20'],
            'rua' => ['required', 'string', 'max:120'],
            'numero' => ['required', 'string', 'max:20'],
            'bairro' => ['required', 'string', 'max:80'],
            'cidade' => ['required', 'string', 'max:80'],
            'uf' => ['required', 'string', 'size:2'],
            'cep' => ['required', 'string', 'max:10'],
            'complemento' => ['nullable', 'string', 'max:120'],
            'observacao' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated, $config, $pedido) {
            $cliente = ClienteEcommerce::create([
                'nome' => $validated['nome'],
                'sobre_nome' => $validated['sobre_nome'],
                'cpf' => $validated['cpf'],
                'ie' => $validated['ie'] ?? '',
                'email' => $validated['email'],
                'telefone' => $validated['telefone'],
                'senha' => Hash::make($validated['senha']),
                'status' => 1,
                'token' => Str::random(40),
                'empresa_id' => $config->empresa_id,
            ]);

            $endereco = EnderecoEcommerce::create([
                'rua' => $validated['rua'],
                'numero' => $validated['numero'],
                'bairro' => $validated['bairro'],
                'cep' => $validated['cep'],
                'cidade' => $validated['cidade'],
                'uf' => strtoupper($validated['uf']),
                'complemento' => $validated['complemento'] ?? '',
                'cliente_id' => $cliente->id,
            ]);

            $pedido->cliente_id = $cliente->id;
            $pedido->endereco_id = $endereco->id;
            $pedido->observacao = $validated['observacao'] ?? '';
            $pedido->save();

            (new PedidoEcommerceHelper())->setUserEcommerce($cliente->id, $config->empresa_id);
        });

        return redirect('/loja/' . strtolower($config->link) . '/endereco');
    }

    public function calculaFrete(Request $request, string $link)
    {
        $config = $this->configLoja($link);
        $pedido = $this->carrinhoDaLoja($config);

        $data = $request->validate([
            'cep' => ['required', 'string', 'max:10'],
        ]);

        try {
            return response()->json(
                $this->checkoutService->opcoesFrete($pedido, $config, $data['cep'])
            );
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function setaFrete(Request $request, string $link)
    {
        $config = $this->configLoja($link);
        $pedido = $this->carrinhoDaLoja($config);

        $data = $request->validate([
            'tipo' => ['required', Rule::in(['pac', 'sedex', 'gratis', 'retirada'])],
            'cep' => ['required_unless:tipo,retirada', 'nullable', 'string', 'max:10'],
        ]);

        try {
            $frete = $this->checkoutService->calcularFrete(
                $pedido,
                $config,
                $data['cep'] ?? $config->cep,
                $data['tipo']
            );

            $pedido->tipo_frete = $data['tipo'];
            $pedido->valor_frete = $frete['valor'];
            $pedido->save();

            return response()->json([
                'tipo_frete' => $pedido->tipo_frete,
                'valor_frete' => $pedido->valor_frete,
                'subtotal' => round((float) $pedido->somaItens(), 2),
                'total' => round((float) $pedido->somaItens() + (float) $pedido->valor_frete, 2),
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function buscaCupom(Request $request, string $link)
    {
        $config = $this->configLoja($link);
        $pedido = $this->carrinhoDaLoja($config);

        $data = $request->validate([
            'cupom_desconto' => ['required', 'string', 'max:50'],
        ]);

        try {
            $cupom = $this->checkoutService->calcularCupom(
                $pedido,
                $config,
                $data['cupom_desconto']
            );

            $subtotal = round((float) $pedido->somaItens(), 2);
            return response()->json([
                'codigo' => $cupom['codigo'],
                'desconto' => $cupom['desconto'],
                'subtotal' => $subtotal,
                'total_com_desconto' => round(max(0, $subtotal - $cupom['desconto']), 2),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function pagamento(Request $request, string $link)
    {
        $config = $this->configLoja($link);
        $pedido = $this->carrinhoDaLoja($config);
        $cliente = $this->clienteDaSessao($config);

        abort_if(!$cliente, 403, 'Faça login para continuar.');
        abort_if((int) $pedido->cliente_id !== (int) $cliente->id, 403, 'Carrinho não pertence ao cliente logado.');

        $data = $request->validate([
            'endereco_id' => ['nullable', 'integer'],
            'endereco' => ['nullable'],
            'tipo' => ['required', Rule::in(['pac', 'sedex', 'gratis', 'retirada'])],
            'cupom_desconto' => ['nullable', 'string', 'max:50'],
        ]);

        $enderecoId = (int) ($data['endereco_id'] ?? 0);
        if ($enderecoId <= 0 && !empty($data['endereco'])) {
            $decoded = is_string($data['endereco']) ? json_decode($data['endereco'], true) : $data['endereco'];
            $enderecoId = (int) ($decoded['id'] ?? 0);
        }

        $endereco = EnderecoEcommerce::where('id', $enderecoId)
            ->where('cliente_id', $cliente->id)
            ->firstOrFail();

        try {
            $resumo = $this->checkoutService->resumo(
                $pedido,
                $config,
                $endereco,
                $data['tipo'],
                $data['cupom_desconto'] ?? null
            );

            $pedido = DB::transaction(function () use ($pedido, $endereco, $resumo, $config, $cliente) {
                $pedido = $this->checkoutService->salvarResumo($pedido, $endereco, $resumo);

                if ($resumo['cupom']) {
                    CupomEcommerceUtilizado::updateOrCreate(
                        ['pedido_id' => $pedido->id],
                        [
                            'empresa_id' => $config->empresa_id,
                            'cupom_id' => $resumo['cupom']->id,
                            'cliente_id' => $cliente->id,
                        ]
                    );
                }

                return $pedido;
            });

            $totais = $this->checkoutService->totaisPorFormaPagamento($resumo['total'], $config);
            $formasPagamento = array_values(array_intersect(
                (array) json_decode($config->formas_pagamento, true),
                ['pix', 'cartao', 'boleto']
            ));

            if (empty($formasPagamento)) {
                return back()->with('flash_erro', 'Nenhuma forma de pagamento está habilitada nesta loja.');
            }

            $dadosDefault = $this->dadosDefault($config, $pedido);
            $view = $config->modelo_orcamento
                ? $dadosDefault['template'] . '/finaliza_orcamento'
                : $dadosDefault['template'] . '/pay';

            return view($view, [
                'default' => $dadosDefault,
                'carrinho' => $pedido,
                'cliente' => $cliente,
                'descricao' => $this->descricao($pedido),
                'total' => $resumo['total'],
                'formas_pagamento' => $formasPagamento,
                'forma_inicial' => $formasPagamento[0],
                'totais' => $totais,
                'payJs' => true,
                'cart' => true,
                'rota' => $dadosDefault['rota'],
                'title' => 'Pagamento do Pedido',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('flash_erro', $e->getMessage());
        }
    }

    private function dadosDefault(ConfigEcommerce $config, PedidoEcommerce $pedido): array
    {
        return [
            'config' => $config,
            'template' => $config->tema_ecommerce,
            'categorias' => \App\Models\CategoriaProdutoEcommerce::where('empresa_id', $config->empresa_id)->get(),
            'curtidas' => (new PedidoEcommerceHelper())->getProdutosCurtidos($config->empresa_id),
            'active' => '',
            'carrinho' => $pedido,
            'postBlogExists' => \App\Models\PostBlogEcommerce::where('empresa_id', $config->empresa_id)->exists(),
            'rota' => '/loja/' . strtolower($config->link),
        ];
    }

    private function descricao(PedidoEcommerce $pedido): string
    {
        return $pedido->itens
            ->map(fn ($item) => $item->quantidade . ' x ' . optional(optional($item->produto)->produto)->nome)
            ->filter()
            ->implode(' ');
    }
}