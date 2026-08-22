<?php

namespace App\Http\Controllers;

use App\Models\Motoboy;
use App\Models\TelaPedidoDeli;
use App\Models\ConfigNota;
use App\Models\EmpresaDelivery;
use App\Models\WsDatasClose;
use App\Models\CupomDesconto;
use App\Models\WSEmpresa;
use App\Models\ApiBrasil;
use App\Models\StatusPagamento;
use App\Models\MensagemPersonalizada;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendWhatsappMessageJob; // <--- Importe o Job que você criou
use App\Jobs\EnviarMensagemWhatsAppJob; // Importe o novo Job

use Illuminate\Http\Request;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class TelaPedidoController extends Controller
{
      public function index(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return redirect()->back()->with('error', 'Configuração não encontrada para esta empresa.');
        }

        if (!$config->user_id) {
            return redirect()->back()->with('error', 'Token de autenticação não configurado.');
        }

        $query = TelaPedidoDeli::where('user_id', $config->user_id);

        if ($request->has('pesquisa') && $request->pesquisa) {
            $pesquisa = $request->pesquisa;
            $query->where(function ($query) use ($pesquisa) {
                $query->where('nome', 'like', '%' . $pesquisa . '%')
                    ->orWhere('telefone', 'like', '%' . $pesquisa . '%')
                    ->orWhere('codigo_pedido', 'like', '%' . $pesquisa . '%');
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('data_chart2', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('data_chart2', '<=', $request->end_date);
        }

        $query->orderBy('data_chart2', 'desc');

        // Clone the query for *all* filtered orders to calculate totals for the current page/filter set
        $allFilteredOrders = (clone $query)->get();

        // Calculate totals from the full filtered set (for the cards that react to filters)
        $totalPedidosValue = $allFilteredOrders->sum('sub_total'); // Use 'total' here if you want final order total including discounts/fees
        $totalTaxaEntrega = $allFilteredOrders->sum('valor_taxa');

        // --- New: Calculate Annual Total ---
        $currentYear = Carbon::now()->year;
        $totalAnualPedidosValue = TelaPedidoDeli::where('user_id', $config->user_id)
                                                    ->whereYear('data_chart2', $currentYear)
                                                    ->sum('sub_total'); // Sum 'total' for the annual card
        // --- End New ---


        // Paginate the results (after the clone for totals)
        $pedidos = $query->paginate(100);

        // Buscar os venda_id dos pedidos (from the paginated set for payment statuses)
        $vendaIds = $pedidos->pluck('id')->toArray();

        // Buscar dados de pagamento relacionados
        $statusPagamentos = StatusPagamento::whereIn('venda_id', $vendaIds)->get()->keyBy('venda_id');

        // Totais por status (assuming this is a static method in your model)
        $totaisPorStatus = TelaPedidoDeli::totaisPorStatus($config->user_id, $request->start_date, $request->end_date);

        // Enviar para a view, including all new total variables
        return view('tela_pedido.index', compact('pedidos', 'totaisPorStatus', 'statusPagamentos', 'totalPedidosValue', 'totalTaxaEntrega', 'totalAnualPedidosValue'))
            ->with('message', $pedidos->isEmpty() ? 'Nenhum pedido encontrado com os critérios informados.' : '');
    }

    public function grafico(Request $request)
    {
        // Recuperando a configuração da empresa
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return redirect()->back()->with('error', 'Configuração não encontrada para esta empresa.');
        }

        if (!$config->user_id) {
            return redirect()->back()->with('error', 'Token de autenticação não configurado.');
        }

        // Construção da query para filtrar os pedidos
        $query = TelaPedidoDeli::where('user_id', $config->user_id);

        // Filtro por pesquisa (nome, telefone, código do pedido)
        if ($request->filled('pesquisa')) {
            $pesquisa = $request->pesquisa;
            $query->where(function ($query) use ($pesquisa) {
                $query->where('nome', 'like', '%' . $pesquisa . '%')
                    ->orWhere('telefone', 'like', '%' . $pesquisa . '%')
                    ->orWhere('codigo_pedido', 'like', '%' . $pesquisa . '%');
            });
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por intervalo de datas
        $startDate = $request->filled('start_date') ? $request->start_date : now()->toDateString();
        $endDate = $request->filled('end_date') ? $request->end_date : now()->toDateString();

        $query->whereDate('data_chart2', '>=', $startDate);
        $query->whereDate('data_chart2', '<=', $endDate);

        $query->orderBy('data_chart2', 'desc');

        // Recuperando os pedidos com paginação
        $pedidos = $query->paginate(100);

        // Verifica se não há pedidos e define uma variável para a mensagem
        $noDataMessage = '';
        if ($pedidos->isEmpty()) {
            $noDataMessage = 'Nenhum pedido encontrado com os critérios informados.';
        }

        // Soma total dos pedidos
        $somaTotalPedidos = $pedidos->sum('total');

        // Totais por status (usando o método estático da model)
        $totaisPorStatus = TelaPedidoDeli::totaisPorStatus($config->user_id, $startDate, $endDate);

        // Passando as variáveis para a view
        return view('tela_pedido.grafico', compact('pedidos', 'totaisPorStatus', 'startDate', 'endDate', 'somaTotalPedidos', 'noDataMessage'));
    }


    public function motoboyView()
    {
        return view('tela_pedido.motoboy');
    }

    public function motoboyStore(Request $request)

    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return redirect()->back()->with('error', 'Configuração não encontrada para esta empresa.');
        }

        if (!$config->user_id) {
            return redirect()->back()->with('error', 'Token de autenticação não configurado.');
        }

        $validatedData = $request->validate([
            'deliveryman_name' => 'required|string|max:20',
            'deliveryman_phone_number' => 'required|numeric|min:0|max:100',

        ]);

        try {
            Motoboy::create([
                'user_id' => $config->user_id,
                'deliveryman_name' => $validatedData['deliveryman_name'],
                'deliveryman_phone_number' => $validatedData['deliveryman_phone_number'],

            ]);

            return redirect()->route('tela_pedido.motoboy')->with('success', 'Motobouy cadastrado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocorreu um erro ao cadastrar o cupom: ' . $e->getMessage());
        }
    }

    public function create()
    {
        return view('tela_pedido.create');
    }

    public function store(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return redirect()->back()->with('error', 'Configuração não encontrada para esta empresa.');
        }

        if (!$config->user_id) {
            return redirect()->back()->with('error', 'Token de autenticação não configurado.');
        }

        $validatedData = $request->validate([
            'ativacao' => 'required|string|max:20',
            'porcentagem' => 'required|numeric|min:0|max:100',
            'total_vezes' => 'required|integer|min:1',
            'mostrar_site' => 'required|boolean',
            'data_validade' => 'required|date',
            'vip' => 'required|boolean',
        ]);

        try {
            CupomDesconto::create([
                'user_id' => $config->user_id,
                'ativacao' => $validatedData['ativacao'],
                'porcentagem' => $validatedData['porcentagem'],
                'total_vezes' => $validatedData['total_vezes'],
                'mostrar_site' => $validatedData['mostrar_site'],
                'data_validade' => $validatedData['data_validade'],
                'vip' => $validatedData['vip'],
            ]);

            return redirect()->route('tela-pedido.create')->with('success', 'Cupom cadastrado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocorreu um erro ao cadastrar o cupom: ' . $e->getMessage());
        }
    }


    public function pedidosDodia(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return redirect()->route('telasPedido.index')->with('error', 'Configuração não encontrada para esta empresa.');
        }

        if (!$config->user_id) {
            return redirect()->route('telasPedido.index')->with('error', 'Token de autenticação não configurado.');
        }

        $datadodia = WsDatasClose::where('user_id', $config->user_id)
            ->orderBy('id', 'desc')
            ->first();

        $pedidos = TelaPedidoDeli::where('user_id', $config->user_id)
            ->whereDate('data', now()->toDateString())
            ->orderByRaw("
                CASE
                      WHEN status = 'Aberto' THEN 1
                    WHEN status = 'Em Andamento' THEN 2
                    WHEN status = 'Saiu para Entrega' THEN 3
                    WHEN status = 'Disponível para Retirada' THEN 4
                    WHEN status = 'Finalizado' THEN 5
                    WHEN status = 'Cancelado' THEN 6
                    ELSE 7
                END ASC,
                created_at DESC                              -- Opcional: Para pedidos com o mesmo status, os mais recentes primeiro
            ")
            ->paginate(100);

        // Buscar os venda_id dos pedidos
        $vendaIds = $pedidos->pluck('id')->toArray();

        // Buscar dados de pagamento relacionados
        $statusPagamentos = StatusPagamento::whereIn('venda_id', $vendaIds)->get()->keyBy('venda_id');

        return view('tela_pedido.pedidosDodia', compact('pedidos', 'datadodia', 'statusPagamentos'));
    }

    public function Engajamento(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return redirect()->back()->with('error', 'Configuração da empresa não encontrada.');
        }

        $nome_empresa_link = EmpresaDelivery::where('user_id', $config->user_id)
            ->value('nome_empresa_link');

        $cupomvip = CupomDesconto::where('user_id', $config->user_id)
            ->where('vip', 0)
            ->orderBy('id_cupom', 'desc')
            ->first();

$clientesInativos = TelaPedidoDeli::where('user_id', $config->user_id)
    ->whereDate('created_at', '<', now()->subDays(30))
            ->groupBy('telefone', 'user_id') // Agrupa por telefone (e user_id para garantir unicidade por usuário)

    ->paginate(20);
        return view('tela_pedido.Engajamento', compact('clientesInativos', 'cupomvip', 'nome_empresa_link'));
    }


    public function vip(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return redirect()->route('telasPedido.index')->with('error', 'Configuração da empresa não encontrada.');
        }

        if (empty($config->user_id)) {
            return redirect()->route('telasPedido.index')->with('error', 'Token de autenticação não configurado.');
        }

        // Recuperando os clientes VIP
        $vip = TelaPedidoDeli::vip($config->user_id);

        // Definindo mensagem para ausência de dados
        $noVipMessage = '';
        if ($vip->isEmpty()) {
            $noVipMessage = 'Nenhum cliente VIP encontrado.';
        }

        // Recuperando o nome da empresa e o cupom
        $nome_empresa_link = EmpresaDelivery::where('user_id', $config->user_id)
            ->value('nome_empresa_link');

        $cupomvip = CupomDesconto::where('user_id', $config->user_id)
            ->where('vip', 1)
            ->orderBy('id_cupom', 'desc')
            ->first();

        // Passando a mensagem de ausência de dados para a view
        return view('tela_pedido.vip', compact('vip', 'cupomvip', 'nome_empresa_link', 'noVipMessage'));
    }
public function clientes(Request $request)
{
    $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

    if (!$config) {
        return redirect()->route('telasPedido.index')->with('error', 'Configuração da empresa não encontrada.');
    }

    if (empty($config->user_id)) {
        return redirect()->route('telasPedido.index')->with('error', 'Token de autenticação não configurado.');
    }

    $dataInicio = now()->subDays(30)->startOfDay();
    $dataFim = now()->endOfDay();

    // Recuperando os clientes e agrupando por telefone para garantir unicidade
    $clientes = TelaPedidoDeli::where('user_id', $config->user_id)
        ->whereBetween('data', [$dataInicio, $dataFim])
        ->select('telefone', 'nome', 'data')  // Selecionando apenas as colunas necessárias
        ->groupBy('telefone')  // Agrupando por telefone para garantir que não haja repetição
        ->paginate(12);  // Paginação diretamente na consulta

    // Mensagem caso não haja clientes
    $noVipMessage = '';
    if ($clientes->isEmpty()) {
        $noVipMessage = 'Nenhum cliente encontrado nos últimos 30 dias.';
    }

    // Recuperando o nome da empresa e o cupom (caso exista)
    $nome_empresa_link = EmpresaDelivery::where('user_id', $config->user_id)
        ->value('nome_empresa_link');

    $cupomvip = CupomDesconto::where('user_id', $config->user_id)
        ->where('vip', 1)
        ->orderBy('id_cupom', 'desc')
        ->first();

    // Adicionando uma variável para a mensagem padrão para o formulário
    // Você pode deixá-la vazia ou preencher com um texto inicial
    $mensagemPadrao = "Olá [Nome do Cliente], temos uma oferta especial para você!";


    // Retornando para a view
    return view('tela_pedido.clientes', compact('clientes', 'cupomvip', 'nome_empresa_link', 'noVipMessage', 'mensagemPadrao'));
}
 public function enviarWhatsAppa(Request $request)
    {
        $dados = $request->validate([
            'nomes_selecionados' => 'required|string',
            'telefones_selecionados' => 'required|string',
            'mensagem' => 'required|string',
        ]);

        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return redirect()->back()->with('flash_erro', 'Nenhuma configuração encontrada para esta empresa.');
        }

        if (!$config->user_id) {
            return redirect()->back()->with('flash_erro', 'Token de autenticação não configurado para esta empresa.');
        }

        // Não precisa mais do merge no request, pois o user_id será passado diretamente para o Job
        // $request->merge(['user_id' => $config->user_id]);

        $apiBrasilConfig = ApiBrasil::where('user_id', $config->user_id)->first(); // Use $config->user_id diretamente

        $DeviceToken = $apiBrasilConfig->DeviceToken ?? null;
        $Bearer = $apiBrasilConfig->Bearer ?? null;

        if (!$DeviceToken || !$Bearer) {
            return redirect()->back()->with('error', 'Token de autenticação da API Brasil não configurado. Por favor, verifique as configurações da API.');
        }

        $telefonesBrutos = array_map('trim', explode(',', $dados['telefones_selecionados']));
        $nomesBrutos = array_map('trim', explode(',', $dados['nomes_selecionados']));

        $clientesParaEnviar = [];
        foreach ($telefonesBrutos as $index => $telefone) {
            $telefoneLimpo = preg_replace('/[^0-9]/', '', $telefone);
            $nome = $nomesBrutos[$index] ?? 'Cliente';

            if (!empty($telefoneLimpo) && !isset($clientesParaEnviar[$telefoneLimpo])) {
                $clientesParaEnviar[$telefoneLimpo] = [
                    'telefone' => $telefoneLimpo,
                    'nome' => $nome
                ];
            }
        }

        if (empty($clientesParaEnviar)) {
            return redirect()->back()->with('error', 'Nenhum telefone válido para envio encontrado.');
        }

        $delaySeconds = 0; // Inicializa o atraso

        foreach ($clientesParaEnviar as $cliente) {
            // Despacha o Job para a fila com um atraso acumulativo
            SendWhatsappMessageJob::dispatch(
                $cliente['telefone'],
                $cliente['nome'],
                $dados['mensagem'],
                $config->user_id // Passa o user_id para o Job
            )->delay(now()->addSeconds($delaySeconds));

            $delaySeconds += 30; // Incrementa o atraso para o próximo Job
        }

        return redirect()->back()->with('success', 'As mensagens estão sendo processadas em segundo plano e serão enviadas em breve.');
    }


    public function cozinha(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return redirect()->route('telasPedido.index')->with('error', 'Nenhuma configuração encontrada para esta empresa.');
        }

        if (!$config->user_id) {
            return redirect()->route('telasPedido.index')->with('error', 'O token de autenticação não está configurado.');
        }

        // Obtém os pedidos com status "Em Andamento" do dia atual
        $pedidos = TelaPedidoDeli::where('user_id', $config->user_id)
            ->where('status', 'Em Andamento')
            ->orderByRaw("
        CASE 
            WHEN status = 'Em Andamento' THEN 0 
            ELSE 1 
        END
    ")
            ->orderByRaw("
        CASE 
            WHEN status = 'Em Andamento' THEN data 
        END ASC
    ")->whereDate('data', now()->toDateString())
            ->orderBy('data', 'desc')
            ->paginate(100);

        return view('tela_pedido.cozinha', compact('pedidos'));
    }


    public function edit($pedidoId)
    {
        $pedido = TelaPedidoDeli::findOrFail($pedidoId);
        return view('tela_pedido.edit', compact('pedido'));
    }


    public function diaFechado(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if ($config) {
            $hoje = Carbon::today()->format('Y-m-d');

            // Deleta todos os registros com a data de hoje (caso existam múltiplos)
            WsDatasClose::where('user_id', $config->user_id)
                ->whereDate('data', $hoje)
                ->delete();

            // Cria nova data de hoje
            WsDatasClose::create([
                'user_id' => $config->user_id,
                'data' => $hoje,
            ]);

            return redirect()->back()->with('success', 'Data de hoje foi atualizada com sucesso!');
        }

        return redirect()->back()->with('error', 'Empresa não encontrada.');
    }

    public function abrirDia(Request $request)
    {
        $id = $request->id;

        $registro = WsDatasClose::find($id);

        if ($registro) {
            $registro->delete();
            return redirect()->back()->with('success', 'Dia reaberto com sucesso!');
        }

        return redirect()->back()->with('error', 'Registro não encontrado.');
    }


 public function update(Request $request, $pedidoId)
{
    try {
        // Operações de banco de dados MUITO rápidas e locais
        $pedido = TelaPedidoDeli::findOrFail($pedidoId);
        if ($pedido->view == 0) {
            $pedido->view = 1;
        }
        $pedido->status = $request->status;
        $pedido->save();

        // Dispara o Job para a fila. Esta linha é EXTREMAMENTE RÁPIDA.
        // Ela não espera a API do WhatsApp.
        EnviarMensagemWhatsAppJob::dispatch(
            $pedidoId,
            request()->empresa_id,
            $request->status
        );

        // Define uma mensagem de sucesso para o usuário, que será exibida após o redirecionamento.
        session()->flash('flash_sucesso', 'Status do pedido alterado com sucesso! As mensagens de WhatsApp estão sendo enviadas em segundo plano.');

    } catch (\Exception $e) {
        // Este bloco só é executado se houver um erro ANTES do dispatch,
        // ou se o Job estiver mal configurado e rodando em sync,
        // ou se o Job lançar uma exceção e você tiver um listener que a repassa para o front-end.
        session()->flash('flash_erro', 'Algo deu errado ao atualizar o pedido: ' . $e->getMessage());
        Log::error("Erro crítico no PedidoController::update para empresa_id " . request()->empresa_id . ": " . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'pedido_id' => $pedidoId]);
    }

    // REDIRECIONA IMEDIATAMENTE. Esta é a ÚLTIMA linha que o Laravel executa para o navegador.
    return redirect()->back();
}
public function formaPagemento(Request $request)
{
    try {
        // Pega o pedidoId enviado no form
        $pedidoId = $request->input('pedido_id');

        $pedido = TelaPedidoDeli::findOrFail($pedidoId);
        $pedido->forma_pagamento = $request->input('forma_pagamento');
        $pedido->save();

        session()->flash('flash_sucesso', 'Forma de pagamento atualizada com sucesso!');
    } catch (\Exception $e) {
        Log::error("Erro ao atualizar forma de pagamento para pedido {$pedidoId}: " . $e->getMessage(), [
            'id ' => $pedidoId,
            'trace' => $e->getTraceAsString(),
        ]);
        session()->flash('flash_erro', 'Erro ao atualizar o pedido: ' . $e->getMessage());
    }

    return redirect()->back();
}


   public function destroy($pedidoId)
    {
        $pedido = TelaPedidoDeli::findOrFail($pedidoId);

        try {
            $pedido->delete();
            session()->flash('flash_sucesso', 'Apagado com sucesso');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
    return redirect()->back();
    }


    public function imprimirPedido($pedidoId)
    {
        try {
            $pedido = TelaPedidoDeli::findOrFail($pedidoId);


            return view('telasPedido.print', compact('pedido', 'config'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('telasPedido.index')->with('error', 'Pedido não encontrado.');
        } catch (\Exception $e) {
            return redirect()->route('telasPedido.index')->with('error', 'Erro ao carregar impressão.');
        }
    }
}