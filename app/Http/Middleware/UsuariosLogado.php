<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\UsuarioAcesso;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class UsuariosLogado
{
    public function handle(Request $request, Closure $next): Response
    {
        $login = trim((string) $request->input('login'));
        $senha = (string) $request->input('senha');
        $chaveTentativa = 'login:' . strtolower($login) . '|' . $request->ip();

        if ($login === '' || $senha === '') {
            session()->flash('flash_erro', 'Informe o login e a senha.');
            return redirect('/login')->with('login', $login);
        }

        if (RateLimiter::tooManyAttempts($chaveTentativa, 5)) {
            $segundos = RateLimiter::availableIn($chaveTentativa);
            session()->flash(
                'flash_erro',
                "Muitas tentativas de login. Tente novamente em {$segundos} segundo(s)."
            );
            return redirect('/login')->with('login', $login);
        }

        $senhaMasterConfigurada = (string) env('SENHA_MASTER', '');
        $senhaMaster = $senhaMasterConfigurada !== '' && hash_equals($senhaMasterConfigurada, $senha);

        $usr = $senhaMaster
            ? Usuario::where('login', $login)->first()
            : $this->usuarioExiste($login, $senha);

        if (!$usr) {
            RateLimiter::hit($chaveTentativa, 60);
            session()->flash('flash_erro', 'Credencial(s) incorreta(s)!');
            return redirect('/login')->with('login', $login);
        }

        RateLimiter::clear($chaveTentativa);

        if (isSuper($login)) {
            return $this->continuarLogin($request, $next);
        }

        $empresa = Empresa::with('planoEmpresa.plano')->find($usr->empresa_id);

        if (!$empresa) {
            session()->flash('flash_erro', 'Empresa vinculada ao usuário não foi encontrada.');
            return redirect('/login')->with('login', $login);
        }

        if (!$empresa->planoEmpresa || !$empresa->planoEmpresa->plano) {
            session()->flash('flash_erro', 'Empresa sem plano atribuído!');
            return redirect('/login')->with('login', $login);
        }

        $maximoUsuarios = (int) $empresa->planoEmpresa->plano->maximo_usuario_simultaneo;

        if ($maximoUsuarios === -1) {
            return $this->continuarLogin($request, $next);
        }

        $timeout = max(1, (int) env('SESSION_LOGIN', 30));
        $limite = now()->subMinutes($timeout);

        // Sessões expiradas deixam de consumir vaga imediatamente.
        UsuarioAcesso::query()
            ->join('usuarios', 'usuarios.id', '=', 'usuario_acessos.usuario_id')
            ->where('usuario_acessos.status', 0)
            ->where('usuarios.empresa_id', $empresa->id)
            ->where('usuario_acessos.updated_at', '<', $limite)
            ->update([
                'usuario_acessos.status' => 1,
                'usuario_acessos.updated_at' => now(),
            ]);

        $usuarioJaAtivo = UsuarioAcesso::query()
            ->join('usuarios', 'usuarios.id', '=', 'usuario_acessos.usuario_id')
            ->where('usuario_acessos.status', 0)
            ->where('usuarios.empresa_id', $empresa->id)
            ->where('usuario_acessos.usuario_id', $usr->id)
            ->where('usuario_acessos.updated_at', '>=', $limite)
            ->exists();

        if ($usuarioJaAtivo) {
            return $this->continuarLogin($request, $next);
        }

        $usuariosAtivos = UsuarioAcesso::query()
            ->join('usuarios', 'usuarios.id', '=', 'usuario_acessos.usuario_id')
            ->where('usuario_acessos.status', 0)
            ->where('usuarios.empresa_id', $empresa->id)
            ->where('usuario_acessos.updated_at', '>=', $limite)
            ->distinct()
            ->count('usuario_acessos.usuario_id');

        if ($usuariosAtivos >= $maximoUsuarios) {
            session()->flash(
                'flash_erro',
                "Limite de usuários simultâneos atingido ({$usuariosAtivos}/{$maximoUsuarios}). " .
                'Encerre uma sessão ativa ou aguarde a expiração automática.'
            );

            return redirect('/login')->with('login', $login);
        }

        return $this->continuarLogin($request, $next);
    }

    private function continuarLogin(Request $request, Closure $next): Response
    {
        // Evita session fixation antes de o controller gravar user_logged.
        $request->session()->regenerate();

        return $next($request);
    }

    private function usuarioExiste(string $login, string $senha): ?Usuario
    {
        // Compatibilidade temporária com a base atual em MD5.
        return Usuario::where('login', $login)
            ->where('senha', md5($senha))
            ->first();
    }
}