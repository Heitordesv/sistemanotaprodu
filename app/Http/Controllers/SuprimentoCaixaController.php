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
            $value = session('user_logged');

            if (!$value) {
                return redirect('/login');
            }

            $this->empresa_id = (int) (is_object($value)
                ? ($value->empresa_id ?? 0)
                : ($value['empresa'] ?? $value['empresa_id'] ?? 0));

            $request->merge(['empresa_id' => $this->empresa_id]);

            return $next($request);
        });
    }

    public function store(Request $request)
    {
        $request->validate([
            'valor' => ['required'],
            'observacao' => ['nullable', 'string', 'max:500'],
        ]);

        $user = session('user_logged');

        if (!$user) {
            return redirect('/login')
                ->with('flash_erro', 'Sessão expirada. Faça login novamente.');
        }

        $empresaId = (int) (is_object($user) ? $user->empresa_id : $user['empresa']);
        $usuarioId = (int) (is_object($user) ? $user->id : $user['id']);
        $valor = (float) __convert_value_bd($request->valor);

        if ($valor <= 0) {
            return redirect()->back()
                ->with('flash_erro', 'Informe um valor de suprimento maior que zero.');
        }

        $caixaAberto = AberturaCaixa::query()
            ->where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->where('status', 0)
            ->orderByDesc('id')
            ->first();

        if (!$caixaAberto) {
            return redirect()->route('caixa.index')
                ->with('flash_erro', 'Abra o seu caixa antes de realizar um suprimento.');
        }

        try {
            SuprimentoCaixa::create([
                'usuario_id' => $usuarioId,
                'valor' => round($valor, 2),
                'observacao' => trim((string) ($request->observacao ?? '')),
                'empresa_id' => $empresaId,
                'abertura_caixa_id' => (int) $caixaAberto->id,
            ]);

            session()->flash(
                'flash_sucesso',
                'Suprimento realizado com sucesso no Caixa #' . $caixaAberto->id . '!'
            );
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Não foi possível registrar o suprimento.');
            __saveLogError($e, $empresaId);
        }

        return redirect()->back();
    }
}
