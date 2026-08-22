<?php
namespace App\Http\Controllers;

use App\Models\TelaPedido;
use App\Models\ConfigNota;
use Illuminate\Http\Request;

class TelaPedidoController extends Controller
{
    // Método para listar pedidos
    public function index()
    {
        $config = ConfigNota::where('empresa_id', request()->empresa_id)->first();

        if (!$config) {
            return redirect()->back()->with('error', 'Configuração não encontrada para esta empresa.');
        }

        if (!$config->user_id) {
            return redirect()->back()->with('error', 'Token de autenticação não configurado.');
        }

        $pedidos = TelaPedido::where('user_id', $config->user_id)
                             ->orderBy('data_chart2', 'desc')
                             ->paginate(10);

        if ($pedidos->isEmpty()) {
            return redirect()->back()->with('error', 'Nenhum pedido encontrado para este usuário.');
        }

        return view('tela_pedido.index', compact('pedidos'));
    }

    // Método para editar pedido
    public function edit($pedidoId)
    {
        $pedido = TelaPedido::findOrFail($pedidoId);
        return view('tela_pedido.edit', compact('pedido'));
    }

    // Método para imprimir pedido
    public function print($pedidoId)
    {
        $pedido = TelaPedido::findOrFail($pedidoId);
        return view('pedidos.print', compact('pedido')); // Aqui você pode gerar um PDF ou exibir dados
    }

    // Método para excluir pedido
    public function destroy($id)
    {
        $pedido = TelaPedido::findOrFail($id);

        try {
            $pedido->delete();
            session()->flash('flash_sucesso', 'Pedido apagado com sucesso');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('telasPedido.index');
    }
}
