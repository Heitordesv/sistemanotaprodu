<?php

namespace App\Http\Controllers;

use App\Models\AberturaCaixa;
use App\Models\Acessor;
use App\Models\Categoria;
use App\Models\Certificado;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\ApiBrasil;
use App\Jobs\EnviarMensagemWhatsAppPreVenda; // ✅ Corrigido nome da Job
use App\Models\ConfigCaixa;
use App\Models\ConfigNota;
use App\Models\Empresa;
use App\Models\FaturaPreVenda;
use App\Models\Funcionario;
use App\Models\GrupoCliente;
use App\Models\ItemVendaCaixaPreVenda;
use App\Models\ListaPreco;
use App\Models\NaturezaOperacao;
use App\Models\Pais;
use App\Models\VendaCaixaPreVenda;
use App\Models\Produto;
use App\Models\SangriaCaixa;
use App\Models\SuprimentoCaixa;
use App\Models\Tributacao;
use App\Models\Usuario;
use App\Models\VendaCaixa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreVendaController extends Controller
{
    public function index(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
        $naturezas = NaturezaOperacao::where('empresa_id', $request->empresa_id)->get();
        $categorias = Categoria::where('empresa_id', $request->empresa_id)->get();
        $produtos = Produto::where('empresa_id', $request->empresa_id)->where('inativo', false)->get();
        $produtosGroup = Produto::where('empresa_id', $request->empresa_id)
            ->where('inativo', false)
            ->where('valor_venda', '>', 0)
            ->groupBy('referencia_grade')
            ->get();
        $tributacao = Tributacao::where('empresa_id', $request->empresa_id)->get();
        $tiposPagamento = VendaCaixa::tiposPagamento();
        $certificado = Certificado::where('empresa_id', $request->empresa_id)->first();
        $usuario = Usuario::find(get_id_user());

        if (count($naturezas) == 0 || count($produtos) == 0 || $config == null || count($categorias) == 0 || $tributacao == null) {
            return view("frontBox/alerta", compact('produtos', 'categorias', 'naturezas', 'config', 'tributacao'))
                ->with('title', "Validação para Emitir");
        } else {
            if ($config->nat_op_padrao == 0) {
                session()->flash('flash_erro', 'Informe a natureza de operação para o PDV!');
                return redirect('/configNF');
            } else {
                $tiposPagamentoMulti = VendaCaixa::tiposPagamentoMulti();
                $categorias = Categoria::where('empresa_id', $request->empresa_id)->orderBy('nome')->get();
                $clientes = Cliente::orderBy('razao_social')->where('empresa_id', $request->empresa_id)->get();
                $atalhos = ConfigCaixa::where('usuario_id', get_id_user())->first();
                $listas = ListaPreco::where('empresa_id', $request->empresa_id)->get();
                $vendedor = Funcionario::where('empresa_id', $request->empresa_id)->get();
                $estados = Cliente::estados();
                $cidades = Cidade::all();
                $pais = Pais::all();
                $grupos = GrupoCliente::get();
                $acessores = Acessor::where('empresa_id', $request->empresa_id)->get();
                $funcionarios = Funcionario::where('empresa_id', $request->empresa_id)->get();

                $preVendas = VendaCaixaPreVenda::where('empresa_id', $request->empresa_id)
                    ->limit(20)
                    ->orderBy('id', 'desc')
                    ->get();

                return view('pre_venda.index', compact(
                    'vendedor',
                    'tiposPagamento',
                    'config',
                    'certificado',
                    'listas',
                    'atalhos',
                    'usuario',
                    'produtosGroup',
                    'clientes',
                    'categorias',
                    'tiposPagamentoMulti',
                    'cidades',
                    'estados',
                    'acessores',
                    'funcionarios',
                    'grupos',
                    'pais',
                    'preVendas'
                ));
            }
        }
    }

    public function store(Request $request)
    {
        try {
            $preVenda = DB::transaction(function () use ($request) {
                $valor_total = $this->somaItens($request);
                $empresa = Empresa::findOrFail($request->empresa_id);

                $request->merge([
                    'cliente_id' => $request->cliente_id,
                    'bandeira_cartao' => $request->bandeira_cartao ?? '',
                    'cnpj_cartao' => $request->cnpj_cartao ?? '',
                    'cAut_cartao' => $request->cAut_cartao ?? '',
                    'descricao_pag_outros' => $request->descricao_pag_outros ?? '',
                    'rascunho' => $request->rascunho ?? 0,
                    'usuario_id' => get_id_user(),
                    'observacao' => $request->observacao ?? '',
                    'qtd_volumes' => $request->qtd_volumes ?? 0,
                    'peso_liquido' => $request->peso_liquido ?? 0,
                    'peso_bruto' => $request->peso_bruto ?? 0,
                    'desconto' => $request->desconto ?? 0,
                    'valor_total' => $valor_total,
                    'acrescimo' => $request->acrescimo ?? 0,
                    'natureza_id' => $empresa->configNota->nat_op_padrao,
                    'forma_pagamento' => '',
                    'tipo_pagamento' => '',
                    'nome' => $request->nome,
                    'cpf' => $request->cpf ?? '',
                    'funcionario_id' => $request->vendedor_id,
                    'status' => 0
                ]);

                $preVenda = VendaCaixaPreVenda::create($request->all());

                // itens
                for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                    $product = Produto::findOrFail($request->produto_id[$i]);
                    ItemVendaCaixaPreVenda::create([
                        'pre_venda_id' => $preVenda->id,
                        'produto_id' => (int)$request->produto_id[$i],
                        'quantidade' => __convert_value_bd($request->quantidade[$i]),
                        'valor' => __convert_value_bd($request->valor_unitario[$i]),
                        'valor_custo' => $product->valor_compra,
                        'cfop' => 0,
                        'observacao' => $request->observacao ?? '',
                    ]);
                }

                // faturas
                if ($request->tipo_pagamento_row) {
                    for ($i = 0; $i < sizeof($request->tipo_pagamento_row); $i++) {
                        FaturaPreVenda::create([
                            'valor' => __convert_value_bd($request->valor_integral_row[$i]),
                            'forma_pagamento' => $request->tipo_pagamento_row[$i],
                            'pre_venda_id' => $preVenda->id,
                            'vencimento' => $request->data_vencimento_row[$i]
                        ]);
                    }
                }

                return $preVenda;
            });

            // ✅ Dispara job corretamente
            $empresa = Empresa::findOrFail($request->empresa_id);
            dispatch(new EnviarMensagemWhatsAppPreVenda($preVenda, $empresa));

            session()->flash("flash_sucesso", "Pré-venda realizada com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado por aqui: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('preVenda.index');
    }

    private function somaItens($request)
    {
        $valor_total = 0;
        for ($i = 0; $i < sizeof($request->produto_id); $i++) {
            $valor_total += __convert_value_bd($request->subtotal_item[$i]);
        }
        return $valor_total;
    }
}
