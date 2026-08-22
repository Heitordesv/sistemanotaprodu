<?php

namespace App\Http\Middleware;

use App\Models\UsuarioAcesso;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AcessoUsuario
{
    public function handle(Request $request, Closure $next): Response
    {
        $session = session('user_logged');

        if (!$session || empty($session['id']) || empty($session['hash'])) {
            return $this->encerrarSessao('Sua sessão expirou. Faça login novamente.');
        }

        $empresaId = $session['empresa'] ?? $request->empresa_id;

        if (!$empresaId) {
            return $this->encerrarSessao('Empresa da sessão não identificada. Faça login novamente.');
        }

        $timeout = max(1, (int) env('SESSION_LOGIN', 30));
        $limite = now()->subMinutes($timeout);

        // Sessões antigas não podem continuar com status=0 e bloquear novos logins.
        UsuarioAcesso::query()
            ->where('usuario_id', $session['id'])
            ->where('status', 0)
            ->where('updated_at', '<', $limite)
            ->update([
                'status' => 1,
                'updated_at' => now(),
            ]);

        $acessoAtual = UsuarioAcesso::query()
            ->select('usuario_acessos.*')
            ->join('usuarios', 'usuarios.id', '=', 'usuario_acessos.usuario_id')
            ->where('usuario_acessos.usuario_id', $session['id'])
            ->where('usuario_acessos.hash', $session['hash'])
            ->where('usuario_acessos.status', 0)
            ->where('usuarios.empresa_id', $empresaId)
            ->first();

        if (!$acessoAtual) {
            $outraSessao = UsuarioAcesso::query()
                ->select('usuario_acessos.*')
                ->join('usuarios', 'usuarios.id', '=', 'usuario_acessos.usuario_id')
                ->where('usuario_acessos.usuario_id', $session['id'])
                ->where('usuario_acessos.status', 0)
                ->where('usuarios.empresa_id', $empresaId)
                ->where('usuario_acessos.updated_at', '>=', $limite)
                ->latest('usuario_acessos.id')
                ->first();

            if ($outraSessao) {
                return $this->encerrarSessao(
                    'Sua conta está ativa em outra sessão. IP: ' . ($outraSessao->ip_address ?: 'não identificado')
                );
            }

            return $this->encerrarSessao('Sessão não encontrada ou expirada. Faça login novamente.');
        }

        // Mantém a sessão viva. O código anterior retornava antes desta atualização.
        $acessoAtual->updated_at = now();
        $acessoAtual->save();

        return $next($request);
    }

    private function encerrarSessao(string $mensagem): Response
    {
        session()->forget('user_logged');
        session()->forget('store_info');
        session()->forget('user_contador');
        session()->flash('flash_erro', $mensagem);

        return redirect('/login');
    }
}