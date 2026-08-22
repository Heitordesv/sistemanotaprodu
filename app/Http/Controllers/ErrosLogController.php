<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\ErroLog;
use Illuminate\Http\Request;

class ErrosLogController extends Controller
{
      public function index(Request $request)
    {
        // Carrega todas as empresas para o dropdown de filtro (necessário para a view)
        $empresas = Empresa::orderBy('razao_social')->get();
        
        // Extrai filtros da requisição
        $empresa_id = $request->get('empresa_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        
        // Inicia a consulta listando TODOS os logs (removendo restrições iniciais)
        // Usa ErroLog::query() para iniciar o construtor de consultas
        $data = ErroLog::query()
            ->when(!empty($start_date), function ($query) use ($start_date) {
                // Filtra logs criados na ou após a data de início (se provided)
                return $query->whereDate('created_at', '>=', $start_date);
            })
            ->when(!empty($end_date), function ($query) use ($end_date) {
                // Filtra logs criados na ou antes da data de fim (se provided)
                return $query->whereDate('created_at', '<=', $end_date);
            })
        
            ->orderBy('created_at', 'desc') // Ordenado pelo mais recente
            ->paginate(env("PAGINACAO"));

        // Retorna para a view com os dados e a lista de empresas
        return view('erros_log.index', compact('data', 'empresas'));
    }

   public function destroy($id)
{
    try {
        $item = ErroLog::find($id);

        if (!$item) {
            session()->flash('flash_erro', 'Erro não encontrado!');
            return redirect()->route('errosLog.index');
        }

        $item->delete();
        session()->flash('flash_sucesso', 'Erro removido!');
    } catch (\Exception $e) {
        session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
    }

    return redirect()->route('errosLog.index');
}

}
