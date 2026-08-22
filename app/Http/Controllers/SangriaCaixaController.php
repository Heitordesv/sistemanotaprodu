<?php

namespace App\Http\Controllers;

use App\Models\AberturaCaixa;
use App\Models\ConfigNota;
use App\Models\ListaPreco;
use App\Models\SangriaCaixa;
use App\Models\SuprimentoCaixa;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Illuminate\Http\Request;

class SangriaCaixaController extends Controller
{

	protected $empresa_id = null;

	public function __construct()
	{
		$this->middleware(function ($request, $next) {
			$this->empresa_id = $request->empresa_id;
			$value = session('user_logged');
			if (!$value) {
				return redirect("/login");
			}
			return $next($request);
		});
	}

	public function store(Request $request)
    {
        $user = session('user_logged');

        if (!$user) {
            return redirect('/login')
                ->with('flash_erro', 'Sessão expirada. Faça login novamente.');
        }

        $empresa_id = is_object($user) ? $user->empresa_id : $user['empresa'];
        $usuario_id = is_object($user) ? $user->id : $user['id'];
        $valor = __convert_value_bd($request->valor);

        $caixaAberto = AberturaCaixa::where('empresa_id', $empresa_id)
            ->where('usuario_id', $usuario_id)
            ->where('status', 0)
            ->orderByDesc('id')
            ->first();

        if (!$caixaAberto) {
            return redirect()->route('caixa.index')
                ->with('flash_erro', 'Abra o seu caixa antes de realizar uma sangria.');
        }

        try {
            if ($valor <= $this->somaTotalEmCaixa($empresa_id, $usuario_id)) {
                SangriaCaixa::create([
                    'usuario_id' => $usuario_id,
                    'valor' => $valor,
                    'observacao' => $request->observacao ?? '',
                    'empresa_id' => $empresa_id
                ]);

                session()->flash("flash_sucesso", "Sangria realizada com sucesso!");
            } else {
                session()->flash("flash_erro", "Valor de sangria ultrapassa o valor disponível neste caixa!");
            }
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, $empresa_id);
        }

        return redirect()->back();
    }

    private function somaTotalEmCaixa($empresa_id, $usuario_id)
    {
        $abertura = AberturaCaixa::where('empresa_id', $empresa_id)
            ->where('usuario_id', $usuario_id)
            ->where('status', 0)
            ->orderByDesc('id')
            ->first();

        if (!$abertura) {
            return 0;
        }

        $soma = (float) $abertura->valor;

        $soma += (float) VendaCaixa::where('empresa_id', $empresa_id)
            ->where('usuario_id', $usuario_id)
            ->where('id', '>', $abertura->primeira_venda_nfce)
            ->where('rascunho', false)
            ->where('consignado', false)
            ->where('estado_emissao', '!=', 'cancelado')
            ->sum('valor_total');

        $soma += (float) Venda::where('empresa_id', $empresa_id)
            ->where('usuario_id', $usuario_id)
            ->where('id', '>', $abertura->primeira_venda_nfe)
            ->where('estado_emissao', '!=', 'cancelado')
            ->sum('valor_total');

        $soma += (float) SuprimentoCaixa::where('empresa_id', $empresa_id)
            ->where('usuario_id', $usuario_id)
            ->where('created_at', '>=', $abertura->created_at)
            ->sum('valor');

        $soma -= (float) SangriaCaixa::where('empresa_id', $empresa_id)
            ->where('usuario_id', $usuario_id)
            ->where('created_at', '>=', $abertura->created_at)
            ->sum('valor');

        return $soma;
    }
}
