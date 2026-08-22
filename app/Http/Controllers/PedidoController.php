<?php

namespace App\Http\Controllers;

use App\Models\AberturaCaixa;
use App\Models\Acessor;
use App\Models\ApkComanda;
use App\Models\Categoria;
use App\Models\Certificado;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\ComplementoDelivery;
use App\Models\ConfigCaixa;
use App\Models\ConfigNota;
use App\Models\Funcionario;
use App\Models\GrupoCliente;
use App\Models\ItemPedido;
use App\Models\ItemPedidoComplementoLocal;
use App\Models\ItemPizzaPedidoLocal;
use App\Models\ItemVendaCaixa;
use App\Models\ListaPreco;
use App\Models\Mesa;
use App\Models\NaturezaOperacao;
use App\Models\Pais;
use App\Models\Pedido;
use App\Models\PedidoDelete;
use App\Models\VendaCaixaPreVenda;
use App\Models\Produto;
use App\Models\ProdutoDelivery;
use App\Models\SangriaCaixa;
use App\Models\SuprimentoCaixa;
use App\Models\TamanhoPizza;
use App\Models\Tributacao;
use App\Models\Usuario;
use App\Models\Venda;
use App\Models\CreditoVenda;
use App\Models\VendaCaixa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use NFePHP\DA\NFe\CupomPedido;
use NFePHP\DA\NFe\Itens;

class PedidoController extends Controller
{

    public function index()
    {
        $pedidos = Pedido::where('desativado', false)
        ->where('empresa_id', request()->empresa_id)
        ->get();
        $mesas = Mesa::where('empresa_id', request()->empresa_id)->get();
        $mesasParaAtivar = $this->mesasParaAtivar();
        $mesasFechadas = $this->mesasFechadas();
        return view('pedidos.index', compact('mesas', 'mesasParaAtivar', 'mesasFechadas', 'pedidos'));
    }

    public function store(Request $request)
    {
        try {
            $item = Pedido::create($request->all());
            session()->flash('flash_sucesso', 'Pedido adicionado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        }
        return redirect()->route('pedidos.show', $item->id);
    }

    public function show($id)
    {
        $produtos = Produto::where('empresa_id', request()->empresa_id)
        ->orderBy('nome')->get();
        $tamanhos = TamanhoPizza::all();
        $item = Pedido::findOrFail($id);
        $adicionais = ComplementoDelivery::where('empresa_id', request()->empresa_id)->get();
        return view('pedidos.show', compact('item', 'adicionais', 'produtos', 'tamanhos'));
    }

    public function storeItem(Request $request)
    {
        // dd($request);
        $this->_validateItem($request);
        $pedido = Pedido::where('id', $request->id)
        ->first();
        $produto = $request->input('produto');
        $produto = explode("-", $produto);
        $produto = $produto[0];
        if ($pedido->cliente) {
            $limite_venda = $pedido->cliente->limite_venda;
            if ($limite_venda > 0) {
                $soma = $pedido->somaItems() + (float)(__convert_value_bd($request->valor) * __convert_value_bd($request->quantidade));
                if ($soma > $limite_venda) {
                    session()->flash('flash_erro', 'Limite de venda para este cliente é R$ ' .
                        __convert_value_bd($limite_venda));
                    return redirect()->back();
                }
            }
        }
        $result = ItemPedido::create([
            'pedido_id' => $pedido->id,
            'produto_id' => $produto,
            'quantidade' => __convert_value_bd($request->quantidade),
            'status' => $request->status,
            'tamanho_pizza_id' => $request->tamanho_pizza_id ?? NULL,
            'observacao' => $request->observacao ?? '',
            'valor' => __convert_value_bd($request->valor),
            'impresso' => false
        ]);
        if ($request->tamanho_pizza_id && $request->sabores_escolhidos) {
            $saborDup = false;
            $sabores = explode(",", $request->sabores_escolhidos);
            if (count($sabores) > 0) {
                foreach ($sabores as $sab) {
                    $prod = Produto::where('id', $sab)
                    ->first();
                    $item = ItemPizzaPedidoLocal::create([
                        'item_pedido' => $result->id,
                        'sabor_id' => $prod->delivery->id,
                    ]);
                    if ($prod->id == $produto) $saborDup = true;
                }
            }
            if (!$saborDup) {
                $prod = Produto
                ::where('id', $produto)
                ->first();
                $item = ItemPizzaPedidoLocal::create([
                    'item_pedido' => $result->id,
                    'sabor_id' => $prod->delivery->id,
                ]);
            }
        } else if ($request->tamanho_pizza_id) {
            $prod = Produto
            ::where('id', $produto)
            ->first();
            $item = ItemPizzaPedidoLocal::create([
                'item_pedido' => $result->id,
                'sabor_id' => $prod->delivery->id,
            ]);
        }
        if ($request->adicionais_escolhidos) {
            $adicionais = explode(",", $request->adicionais_escolhidos);
            foreach ($adicionais as $id) {
                $id = (int)$id;
                $adicional = ComplementoDelivery::where('id', $id)
                ->first();
                $item = ItemPedidoComplementoLocal::create([
                    'item_pedido' => $result->id,
                    'complemento_id' => $adicional->id,
                    'quantidade' => str_replace(",", ".", $request->quantidade),
                ]);
            }
        }
        if ($result) {
            session()->flash('flash_sucesso', 'Item adicionado!');
        } else {
            session()->flash('flash_erro', 'Erro');
        }
        return redirect()->route('pedidos.show', $pedido->id);
    }

    public function alterarStatus($id)
    {
        $item = ItemPedido::where('id', $id)
        ->first();
        $item->status = 1;
        $item->save();
        session()->flash('flash_sucesso', 'Produto ' . $item->produto->nome . ' marcado como concluido!');
        return redirect()->back();
    }

    private function _validateItem(Request $request)
    {
        $validaTamanho = false;
        if ($request->input('produto')) {
            $produto = $request->input('produto');
            $produto = explode("-", $produto);
            $produto = $produto[0];
            $p = Produto::where('id', $produto)
            ->first();
            if ($p && strpos(strtolower($p->categoria->nome), 'izza') !== false) {
                $validaTamanho = true;
            }
            if ($produto == 'null') {
                $request->merge(['produto' => '']);
            }
        }
        $rules = [
            'produto' => 'required',
            'quantidade' => 'required',
            'tamanho_pizza_id' => $validaTamanho ? 'required' : '',
        ];
        $messages = [
            'produto.required' => 'O campo produto é obrigatório.',
            'quantidade.required' => 'O campo quantidade é obrigatório.',
            'tamanho_pizza_id.required' => 'Selecione um tamanho.',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function storeCliente(Request $request)
    {
        // dd($request);
        try {
            $request->merge([
                'limite_venda' => $request->limite_venda ? __convert_value_bd($request->limite_venda) : 0,
                'ie_rg' => $request->ie_rg ?? '',
                'observacao' => $request->observacao ?? '',
                'nome_fantasia' => $request->nome_fantasia ?? '',
                'acessor_id' => $request->acessor_id ?? 0,
                'grupo_id' => $request->grupo_id ?? 0,
                'cidade_id' => $request->cidade_id ?? 0
            ]);
            DB::transaction(function () use ($request) {
                Cliente::create($request->all());
            });
            session()->flash("flash_sucesso", "Cliente cadastrado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('pedidos.index');
    }

    public function verMesa($mesa_id)
    {
        $mesa = Mesa::findOrFail($mesa_id);
        $pedidos = Pedido::where('mesa_id', $mesa_id)
        ->where('desativado', false)
        ->where('empresa_id', request()->empresa_id)
        ->where('status', false)
        ->get();
        return view('pedidos.ver_mesa', compact('mesa', 'pedidos'));
    }

    private function mesasParaAtivar()
    {
        $mesas = Pedido::where('mesa_ativa', false)
        ->where('mesa_id', '!=', null)
        ->where('empresa_id', request()->empresa_id)
        ->get();
        return $mesas;
    }

    private function mesasFechadas()
    {
        $mesas = Pedido::where('fechar_mesa', true)
        ->where('mesa_id', '!=', null)
        ->where('desativado', false)
        ->where('empresa_id', request()->empresa_id)
        ->get();
        return $mesas;
    }

    public function deleteItem($id)
    {
        $item = ItemPedido::where('id', $id)
        ->first();
        PedidoDelete::create(
            [
                'pedido_id' => $item->pedido_id,
                'produto' => $item->nomeDoProduto(),
                'quantidade' => $item->quantidade,
                'valor' => $item->valor,
                'data_insercao' => $item->created_at,
                'empresa_id' => request()->empresa_id
            ]
        );
        if ($item->delete()) {
            session()->flash('flash_sucesso', 'Item removido!');
        } else {
            session()->flash('flash_erro', 'Erro');
        }
        return redirect()->route('pedidos.show', $item->pedido_id);
    }

    public function abrir(Request $request)
    {
        $codComanda = $request->comanda;
        if (!$codComanda) {
            $codComanda = rand(50, 1000);
        }
        $comanda = Pedido::where('comanda', $codComanda)
        ->where('desativado', false)
        ->where('empresa_id', request()->empresa_id)
        ->first();
        // dd($codComanda);
        if (empty($comanda)) {
            $res = Pedido::create([
                'comanda' => $codComanda,
                'observacao' => $request->observacao ?? '',
                'status' => false,
                'nome' => '',
                'rua' => '',
                'numero' => '',
                'bairro_id' => null,
                'referencia' => '',
                'telefone' => '',
                'desativado' => false,
                'mesa_id' => $request->mesa_id != 'null' ? $request->mesa_id : null,
                'cliente_id' => $request->cliente_id != 'null' ? $request->cliente_id : null,
                'empresa_id' => request()->empresa_id
            ]);
            if ($res) {
                session()->flash('flash_sucesso', 'Comanda aberta com sucesso!');
                return redirect()->route('pedidos.show', $res->id);
            }
        } else {
            session()->flash('flash_erro', 'Esta comanda encontra-se ativa!');
            return redirect()->route('pedidos.index');
        }
    }

    public function imprimirPedido($id)
    {
        $pedido = Pedido::where('id', $id)
        ->where('empresa_id', request()->empresa_id)
        ->first();
        if (valida_objeto($pedido)) {
            $public = env('SERVIDOR_WEB') ? 'public/' : '';
            $pathLogo = $public . 'imgs/logo.jpg';
            $cupom = new CupomPedido($pedido, $pathLogo);
            $cupom->monta();
            $pdf = $cupom->render();
            // file_put_contents($public.'pdf/CUPOM_PEDIDO.pdf',$pdf);
            // return redirect($public.'pdf/CUPOM_PEDIDO.pdf');
            return response($pdf)
            ->header('Content-Type', 'application/pdf');
        } else {
            return redirect('/403');
        }
    }

    private function getTotalContaCredito($cliente)
    {
        return CreditoVenda::selectRaw('sum(vendas.valor_total) as total')
        ->join('vendas', 'vendas.id', '=', 'credito_vendas.venda_id')
        ->where('credito_vendas.cliente_id', $cliente->id)
        ->where('status', 0)
        ->first();
    }

    public function finalizar($id)
    {
        $pedido = Pedido::findOrFail($id);
        if ($pedido->desativado == false) {
            $itensDopedido = [];
            foreach ($pedido->itens as $i) {
                $product = $i->produto;
                $qtd = $i->quantidade;
                $value_unit = $i->valor;
                $sub_total = $i->valor * $i->quantidade;
                $key = null;
                $itensDopedido[] = view('frontBox.partials.row_frontBox', compact('product', 'qtd', 'value_unit', 'sub_total', 'key'));
            }
        } else {
            return redirect()->route('pedidos.index');
        }
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
        ->first();
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)
        ->get();
        $categorias = Categoria::where('empresa_id', request()->empresa_id)
        ->get();
        $produtos = Produto::where('empresa_id', request()->empresa_id)
        ->get();
        $tributacao = Tributacao::where('empresa_id', request()->empresa_id)
        ->get();
        $tiposPagamento = VendaCaixa::tiposPagamento();
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
        ->first();
        if ($config->nat_op_padrao == null) {
            session()->flash("flash_warning", "Informe a natureza de operação primeiramente!");
            return redirect()->route('configNF.index');
        }
        $certificado = Certificado::where('empresa_id', request()->empresa_id)
        ->first();
        $usuario = Usuario::findOrFail(get_id_user());
        if (count($naturezas) == 0 || $config == null || count($categorias) == 0  || count($produtos) == 0 || $tributacao == null) {
            $p = view("frontBox.alerta", compact('produtos', 'categorias', 'naturezas', 'config', 'tributacao'));
            return $p;
        } else {
            $tiposPagamentoMulti = VendaCaixa::tiposPagamentoMulti();
            $categorias = Categoria::where('empresa_id', request()->empresa_id)
            ->orderBy('nome')->get();
            $clientes = Cliente::orderBy('razao_social')
            ->where('empresa_id', request()->empresa_id)
            ->get();
            foreach ($clientes as $c) {
                $c->totalEmAberto = 0;
                $soma = $this->getTotalContaCredito($c);
                if ($soma->total != null) {
                    $c->totalEmAberto = $soma->total;
                }
            }
            $atalhos = ConfigCaixa::where('usuario_id', get_id_user())
            ->first();
            $lista = ListaPreco::where('empresa_id', request()->empresa_id)->get();
            $rascunhos = $this->getRascunhos();
            $preVendas = VendaCaixaPreVenda::where('empresa_id', request()->empresa_id)
            ->where('status', 0)
            ->limit(20)
            ->orderBy('id', 'desc')
            ->get();
            $funcionarios = Funcionario::where('funcionarios.empresa_id', request()->empresa_id)
            ->select('funcionarios.*')
            ->join('usuarios', 'usuarios.id', '=', 'funcionarios.usuario_id')
            ->get();
            $funcionarios = $this->validaCaixaAberto($funcionarios);
            if (sizeof($funcionarios) == 0 && $usuario->caixa_livre) {
                session()->flash("flash_erro", "Usuário definido para caixa livre, cadastre ao menos um funcionário!");
                return redirect('/funcionarios');
            }

            $usuarios = Usuario::where('empresa_id', request()->empresa_id)
            ->where('ativo', 1)
            ->orderBy('nome', 'asc')
            ->get();
            $vendedor = Funcionario::where('empresa_id', request()->empresa_id)->get();
            $estados = Cliente::estados();
            $cidades = Cidade::all();
            $pais = Pais::all();
            $grupos = GrupoCliente::get();
            $acessores = Acessor::where('empresa_id', request()->empresa_id)->get();
            $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();
            $abertura = AberturaCaixa::where('empresa_id', request()->empresa_id)
            ->where('usuario_id', get_id_user())
            ->where('status', 0)
            ->orderBy('id', 'desc')
            ->first();
            $sangrias = [];
            $suprimentos = [];
            $vendas = [];
            if ($abertura != null) {
                $sangrias = SangriaCaixa::where('empresa_id', request()->empresa_id)
                ->where('usuario_id', get_id_user())
                ->whereBetween('created_at', [
                    $abertura->created_at,
                    date('Y-m-d H:i:s')
                ])
                ->get();
                $suprimentos = SuprimentoCaixa::where('empresa_id', request()->empresa_id)
                ->where('usuario_id', get_id_user())
                ->whereBetween('created_at', [
                    $abertura->created_at,
                    date('Y-m-d H:i:s')
                ])
                ->get();
                $vendas = VendaCaixa::where('empresa_id', request()->empresa_id)
                ->where('usuario_id', get_id_user())
                ->whereBetween('created_at', [
                    $abertura->created_at,
                    date('Y-m-d H:i:s')
                ])->get();
            }
            return view('frontBox.index', compact(
                'tiposPagamento',
                'config',
                'pedido',
                'itensDopedido',
                'abertura',
                'certificado',
                'rascunhos',
                'preVendas',
                'estados',
                'sangrias',
                'vendas',
                'suprimentos',
                'cidades',
                'pais',
                'grupos',
                'acessores',
                'vendedor',
                'usuarios',
                'funcionarios',
                'lista',
                'atalhos',
                'usuario',
                'clientes',
                'categorias',
                'tiposPagamentoMulti',
            ));
        }
    }

    private function validaCaixaAberto($funcionarios)
    {
        $temp = [];
        $config = ConfigNota::where('empresa_id', request()->empresa_id)->first();
        foreach ($funcionarios as $f) {
            $aberturaNfe = AberturaCaixa::where('empresa_id', request()->empresa_id)
            ->when($config->caixa_por_usuario == 1, function ($q) use ($f) {
                return $q->where('usuario_id', $f->usuario_id);
            })
            ->orderBy('id', 'desc')->first();
            if ($aberturaNfe != null) {
                if ($aberturaNfe->status == 0)
                    array_push($temp, $f);
            }
        }
        return $temp;
    }

    private function getRascunhos()
    {
        return VendaCaixa::where('rascunho', 1)
        ->where('empresa_id', request()->empresa_id)
        ->limit(20)
        ->orderBy('id', 'desc')
        ->get();
    }


    public function mesas()
    {
        $pedidos = Pedido::where('desativado', false)
        ->where('mesa_id', '!=', null)
        ->where('empresa_id', request()->empresa_id)
        ->groupBy('mesa_id')
        ->get();
        return view('pedidos.mesas', compact('pedidos'));
    }

    public function atribuirMesa(Request $request)
    {
        // dd($request);
        $pedido = Pedido::find($request->pedido_id_modal);
        $pedido->mesa_id = $request->mesa;
        $pedido->save();
        session()->flash('flash_sucesso', 'Mesa atribuida a comanda ' . $pedido->comanda . '!');
        return redirect()->route('pedidos.index');
    }

    public function desativar($id)
    {
        $item = Pedido::where('id', $id)
        ->first();
        if (valida_objeto($item)) {
            $item->desativado = true;
            $res = $item->save();
            if ($res) {
                session()->flash('flash_sucesso', 'Comanda desativada!');
            } else {
                session()->flash('flash_erro', 'Algo deu errado');
            }
            return redirect()->route('pedidos.index');
        } else {
            return redirect('/403');
        }
    }

    public function imprimirItens(Request $request)
    {
        // dd($request);
        $ids = $request->ids;
        $ids = explode(",", $ids);
        $itens = [];
        foreach ($ids as $i) {
            if ($i != null) {
                $item = ItemPedido::find($i);
                // dd($item);
                $item->impresso = true;
                $item->save();
                array_push($itens, $item);
            }
        }
        if (sizeof($itens) > 0) {
            $public = env('SERVIDOR_WEB') ? 'public/' : '';
            $pathLogo = $public . 'imgs/logo.jpg';
            $cupom = new Itens($itens, $pathLogo);
            $pdf = $cupom->render();
            return response($pdf)
            ->header('Content-Type', 'application/pdf');
        } else {
            echo "Selecione ao menos um item!";
        }
    }

    public function controleComandas(Request $request)
    {
        $comandas = Pedido::limit(30)
        ->when(!empty($request->comanda), function ($q) use ($request) {
            return  $q->where(function ($quer) use ($request) {
                return $quer->where('comanda', 'LIKE', "%$request->comanda%");
            });
        })
        ->where('empresa_id', request()->empresa_id)
        ->orderBy('id', 'desc')
        ->get();
        return view('pedidos.controle_comandas', compact('comandas'));
    }

    public function verDetalhes($id)
    {
        $pedido = Pedido::find($id);
        $removidos = PedidoDelete::where('pedido_id', $id)->where('empresa_id', request()->empresa_id)->get();
        return view('pedidos.detalhes', compact('pedido', 'removidos'));
    }

    public function upload()
    {
        $config = ApkComanda::where('empresa_id', request()->empresa_id)
        ->first();
        $title = 'Upload de APK comanda';
        $rotaDownload = "";
        if ($config != null) {
            if (file_exists(public_path('apks/') . $config->nome_arquivo)) {
                $rotaDownload = env('APP_URL') . '/pedidos/download';
            }
        }
        $rotaDownloadGenerico = "";
        if (file_exists(public_path('apks/app.apk'))) {
            $rotaDownloadGenerico = env('APP_URL') . '/pedidos/download_generic';
        }
        return view('pedidos.upload', compact('config', 'title', 'rotaDownload', 'rotaDownloadGenerico'));
    }

    public function apkUpload(Request $request)
    {

        $config = ApkComanda::where('empresa_id', $request->empresa_id)
        ->first();

        try {
            if (!is_dir(public_path('apks'))) {
                mkdir(public_path('apks'), 0777, true);
            }
            $file = $request->file('file');
            $extensao = $file->getClientOriginalExtension();
            $fileName = "controle_comandas_" . date('Y-m-d H:i') . "." . $extensao;
            $upload = $file->move(public_path('apks'), $fileName);
            if ($config == null) {
                ApkComanda::create([
                    'nome_arquivo' => $fileName,
                    'empresa_id' => $request->empresa_id
                ]);
            } else {
                if (file_exists(public_path('apks/') . $config->nome_arquivo)) {
                    unlink(public_path('apks/') . $config->nome_arquivo);
                }
                $config->nome_arquivo = $fileName;
                $config->save();
            }
            session()->flash('flash_sucesso', 'Upload realizado com sucesso!!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro: ' . $e->getMessage());
        }
        return redirect()->back();
    }

    public function download()
    {
        $config = ApkComanda::where('empresa_id', request()->empresa_id)
        ->first();
        $title = 'Upload de APK comanda';
        if ($config != null) {
            if (file_exists(public_path('apks/') . $config->nome_arquivo)) {
                // return response()->download(public_path('apks/').$config->nome_arquivo);
                return response()->file(public_path('apks/') . $config->nome_arquivo, [
                    'Content-Type' => 'application/vnd.android.package-archive',
                    'Content-Disposition' => 'attachment; filename="app.apk"',
                ]);
            } else {
                echo "Nenhum arquivo encontrado!";
            }
        } else {
            echo "Nenhum arquivo encontrado!";
        }
    }

    public function download_generic()
    {
        try {
            // return response()->download(public_path('apks/app'));
            return response()->file(public_path('apks/app.apk'), [
                'Content-Type' => 'application/vnd.android.package-archive',
                'Content-Disposition' => 'attachment; filename="app.apk"',
            ]);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
