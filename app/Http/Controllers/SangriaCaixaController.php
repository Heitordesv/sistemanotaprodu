<?php

namespace App\Http\Controllers;

use App\Models\AberturaCaixa;
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
            $value = session('user_logged');

            if (!$value) {
                return redirect('/login');
            }

            $this->empresa_id = (int) (is_object($value)
                ? ($value->empresa_id ?? 0)
                : ($value['empresa'] ?? $value['empresa_id'] ?? 0));

            // Nunca confiar no empresa_id recebido do navegador.
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
                ->with('flash_erro', 'Informe um valor de sangria maior que zero.');
        }

        $caixaAberto = AberturaCaixa::query()
            ->where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->where('status', 0)
            ->orderByDesc('id')
            ->first();

        if (!$caixaAberto) {
            return redirect()->route('caixa.index')
                ->with('flash_erro', 'Abra o seu caixa antes de realizar uma sangria.');
        }

        try {
            if ($valor <= $this->somaTotalEmCaixa($caixaAberto)) {
                SangriaCaixa::create([
                    'usuario_id' => $usuarioId,
                    'valor' => round($valor, 2),
                    'observacao' => trim((string) ($request->observacao ?? '')),
                    'empresa_id' => $empresaId,
                    'abertura_caixa_id' => (int) $caixaAberto->id,
                ]);

                session()->flash('flash_sucesso', 'Sangria realizada com sucesso!');
            } else {
                session()->flash(
                    'flash_erro',
                    'Valor de sangria ultrapassa o valor disponível neste caixa!'
                );
            }
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Não foi possível registrar a sangria.');
            __saveLogError($e, $empresaId);
        }

        return redirect()->back();
    }

    private function somaTotalEmCaixa(AberturaCaixa $abertura): float
    {
        $empresaId = (int) $abertura->empresa_id;
        $usuarioId = (int) $abertura->usuario_id;
        $aberturaId = (int) $abertura->id;

        $soma = (float) $abertura->valor;

        $soma += (float) VendaCaixa::query()
            ->where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->where(function ($query) use ($abertura, $aberturaId) {
                $query->where('abertura_caixa_id', $aberturaId)
                    ->orWhere(function ($legacy) use ($abertura) {
                        $legacy->whereNull('abertura_caixa_id')
                            ->where('id', '>', (int) $abertura->primeira_venda_nfce);
                    });
            })
            ->where('rascunho', false)
            ->where('consignado', false)
            ->where('estado_emissao', '!=', 'cancelado')
            ->sum('valor_total');

        $soma += (float) Venda::query()
            ->where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->where(function ($query) use ($abertura, $aberturaId) {
                $query->where('abertura_caixa_id', $aberturaId)
                    ->orWhere(function ($legacy) use ($abertura) {
                        $legacy->whereNull('abertura_caixa_id')
                            ->where('id', '>', (int) $abertura->primeira_venda_nfe);
                    });
            })
            ->where('estado_emissao', '!=', 'cancelado')
            ->sum('valor_total');

        $soma += (float) SuprimentoCaixa::query()
            ->where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->where(function ($query) use ($abertura, $aberturaId) {
                $query->where('abertura_caixa_id', $aberturaId)
                    ->orWhere(function ($legacy) use ($abertura) {
                        $legacy->whereNull('abertura_caixa_id')
                            ->where('created_at', '>=', $abertura->created_at);
                    });
            })
            ->sum('valor');

        $soma -= (float) SangriaCaixa::query()
            ->where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->where(function ($query) use ($abertura, $aberturaId) {
                $query->where('abertura_caixa_id', $aberturaId)
                    ->orWhere(function ($legacy) use ($abertura) {
                        $legacy->whereNull('abertura_caixa_id')
                            ->where('created_at', '>=', $abertura->created_at);
                    });
            })
            ->sum('valor');

        return round($soma, 2);
    }
}
