<?php

namespace App\Services;

use App\Models\ConfigNota;
use App\Models\Empresa;
use App\Models\NaturezaOperacao;
use App\Models\Produto;
use App\Models\Usuario;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class FiscalTenantGuardService
{
    /**
     * Resolve o tenant de chamadas fiscais web/API pelo hash já atribuído à empresa.
     * O empresa_id enviado pelo cliente nunca é considerado fonte de identidade.
     */
    public function empresaIdPorHash(Request $request): int
    {
        $hash = trim((string) ($request->header('X-Empresa-Hash') ?: $request->input('hash')));

        if ($hash === '') {
            throw new AccessDeniedHttpException('Empresa não identificada.');
        }

        $empresaId = Empresa::query()
            ->where('hash', $hash)
            ->value('id');

        if (!$empresaId) {
            throw new AccessDeniedHttpException('Empresa não identificada.');
        }

        $request->merge(['empresa_id' => (int) $empresaId]);

        return (int) $empresaId;
    }

    /**
     * Mantém o contrato de autenticação atual do AppFiscal, mas centraliza a
     * derivação do tenant para que produto/configuração nunca confiem no
     * empresa_id recebido no payload.
     */
    public function empresaIdPorTokenApp(Request $request): int
    {
        $token = (string) $request->header('token');
        $decoded = base64_decode($token, true);
        $parts = $decoded === false ? [] : explode(';', $decoded);
        $appKey = (string) env('KEY_APP');

        if (
            $appKey === '' ||
            count($parts) < 3 ||
            !hash_equals($appKey, (string) $parts[2])
        ) {
            throw new AccessDeniedHttpException('Credencial inválida.');
        }

        $usuario = Usuario::query()
            ->where('id', (int) $parts[0])
            ->where('login', (string) $parts[1])
            ->first();

        if (!$usuario || (int) $usuario->empresa_id <= 0) {
            throw new AccessDeniedHttpException('Credencial inválida.');
        }

        $request->merge(['empresa_id' => (int) $usuario->empresa_id]);

        return (int) $usuario->empresa_id;
    }

    public function empresaIdDaSessao(Request $request): int
    {
        $user = $request->session()->get('user_logged');

        $empresaId = (int) (is_object($user)
            ? ($user->empresa_id ?? 0)
            : ($user['empresa'] ?? $user['empresa_id'] ?? 0));

        if ($empresaId <= 0) {
            throw new AccessDeniedHttpException('Empresa da sessão não identificada.');
        }

        $request->merge(['empresa_id' => $empresaId]);

        return $empresaId;
    }

    public function venda(int $empresaId, int $id): Venda
    {
        return $this->recurso(Venda::class, $empresaId, $id);
    }

    public function vendaCaixa(int $empresaId, int $id): VendaCaixa
    {
        return $this->recurso(VendaCaixa::class, $empresaId, $id);
    }

    public function natureza(int $empresaId, int $id): NaturezaOperacao
    {
        return $this->recurso(NaturezaOperacao::class, $empresaId, $id);
    }

    public function produto(int $empresaId, int $id): Produto
    {
        return $this->recurso(Produto::class, $empresaId, $id);
    }

    public function configNota(int $empresaId, int $id): ConfigNota
    {
        return $this->recurso(ConfigNota::class, $empresaId, $id);
    }

    /**
     * Retorna 404 em vez de 403 para não confirmar a existência de recurso de
     * outro tenant (proteção contra enumeração/IDOR).
     *
     * @template TModel of Model
     * @param class-string<TModel> $modelClass
     * @return TModel
     */
    private function recurso(string $modelClass, int $empresaId, int $id): Model
    {
        return $modelClass::query()
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->firstOrFail();
    }
}
