<?php

namespace App\Http\Controllers;

use App\Models\AberturaCaixa;
use App\Models\SuprimentoCaixa;
use Illuminate\Http\Request;

class SuprimentoCaixaController extends Controller
{
    protected $empresa_id = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->empresa_id = $request->empresa_id;
            $value = session('user_logged');

            if (!$value) {
                return redirect('/login');
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

        // O suprimento pertence exclusivamente ao caixa aberto do operador atual.
        $caixaAberto = AberturaCaixa::where('empresa_id', $empresa_id)
            ->where('usuario_id', $usuario_id)
            ->where('status', 0)
            ->orderByDesc('id')
            ->first();

        if (!$caixaAberto) {
            return redirect()->route('caixa.index')
                ->with('flash_erro', 'Abra o seu caixa antes de realizar um suprimento.');
        }

        try {
            SuprimentoCaixa::create([
                'usuario_id' => $usuario_id,
                'valor' => __convert_value_bd($request->valor),
                'observacao' => $request->observacao ?? '',
                'empresa_id' => $empresa_id,
            ]);

            session()->flash(
                'flash_sucesso',
                'Suprimento realizado com sucesso no Caixa #' . $caixaAberto->id . '!'
            );
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, $empresa_id);
        }

        return redirect()->back();
    }
}
