<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\PlanoEmpresa;
use App\Models\PlanoEmpresaRepresentante;
use Illuminate\Http\Request;

class PlanoRepresentanteController extends Controller
{
    public function index()
    {
        $data = PlanoEmpresaRepresentante::orderBy('id', 'desc')->get();
        return view('empresas.planos_pendentes', compact('data'));
    }

    public function ativar($id)
    {
        $p = PlanoEmpresaRepresentante::findOrFail($id);
        try {
            $data = [
                'empresa_id' => $p->empresa_id,
                'plano_id' => $p->plano_id,
                'expiracao' => $p->expiracao,
                'mensagem_alerta' => ''
            ];
            $empresa = Empresa::find($p->empresa_id);
            $plano = $empresa->planoEmpresa;
            if ($plano != null) {
                $plano->delete();
            }
            PlanoEmpresa::create($data);
            $p->delete();
            session()->flash("flash_sucesso", "Plano ativado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado, " . $e->getMessage());
        }
        return redirect()->back();
    }

    public function destroy($id)
    {
        $p = PlanoEmpresaRepresentante::find($id);
        try {
            $p->delete();
            session()->flash("flash_sucesso", "Plano removido!");
        } catch (\Exception $e) {
            session()->flash('flash_erro' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }
}
