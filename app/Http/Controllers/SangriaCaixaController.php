<?php

namespace App\Http\Controllers;

use App\Models\AberturaCaixa;
use App\Models\SangriaCaixa;
use App\Services\CaixaResumoService;
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
        $valor = round((float) __convert_value_bd($request->valor), 2);

        if ($valor <= 0) {
            return redirect()->back()
                ->with('flash_erro', 'Informe um valor de sangria maior que zero.');
        }

        // A rota financeira usa caixaMovimento:obrigatorio. Quando disponível,
        // reutilizamos exatamente a AberturaCaixa que o middleware já manteve
        // sob lockForUpdate durante toda a requisição. O fallback preserva a
        // compatibilidade com chamadas legadas/diretas do controller.
        $caixaAberto = $request->attributes->get('abertura_caixa_bloqueada');

        if (!$caixaAberto instanceof AberturaCaixa) {
            $caixaAberto = AberturaCaixa::query()
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->where('status', 0)
                ->orderByDesc('id')
                ->first();
        }

        if (!$caixaAberto) {
            return redirect()->route('caixa.index')
                ->with('flash_erro', 'Abra o seu caixa antes de realizar uma sangria.');
        }

        try {
            $resumo = app(CaixaResumoService::class)->resumir($caixaAberto);
            $dinheiroDisponivel = round((float) ($resumo['dinheiroNaGaveta'] ?? 0), 2);

            // Sangria representa retirada física da gaveta. Portanto o limite é
            // somente o dinheiro efetivamente disponível: abertura + vendas em
            // dinheiro + recebimentos de contas em dinheiro + suprimentos -
            // sangrias. PIX, cartões e demais meios não aumentam este limite.
            if ($valor > $dinheiroDisponivel) {
                session()->flash(
                    'flash_erro',
                    'Valor de sangria ultrapassa o dinheiro disponível neste caixa! Disponível: R$ '
                    . number_format(max(0, $dinheiroDisponivel), 2, ',', '.')
                );

                return redirect()->back();
            }

            SangriaCaixa::create([
                'usuario_id' => $usuarioId,
                'valor' => $valor,
                'observacao' => trim((string) ($request->observacao ?? '')),
                'empresa_id' => $empresaId,
                'abertura_caixa_id' => (int) $caixaAberto->id,
            ]);

            session()->flash('flash_sucesso', 'Sangria realizada com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Não foi possível registrar a sangria.');
            __saveLogError($e, $empresaId);
        }

        return redirect()->back();
    }
}
