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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class FiscalTenantGuardService
{
    public const VERIFIED_TENANT_ATTRIBUTE = 'fiscal_tenant_empresa_id';

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

        $empresaId = (int) $empresaId;
        $request->merge(['empresa_id' => $empresaId]);
        $request->attributes->set(self::VERIFIED_TENANT_ATTRIBUTE, $empresaId);

        return $empresaId;
    }

    /**
     * Mantém o contrato de autenticação atual do AppFiscal, mas centraliza a
     * derivação do tenant para que controllers fiscais nunca confiem no
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

        $empresaId = (int) $usuario->empresa_id;
        $request->merge(['empresa_id' => $empresaId]);
        $request->attributes->set(self::VERIFIED_TENANT_ATTRIBUTE, $empresaId);

        return $empresaId;
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
        $request->attributes->set(self::VERIFIED_TENANT_ATTRIBUTE, $empresaId);

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

    /**
     * Valida todos os produtos de uma operação em uma única query para evitar
     * N+1 no boundary de tenant.
     */
    public function produtos(int $empresaId, array $ids): void
    {
        $ids = collect($ids)
            ->filter(fn ($id) => is_scalar($id) && ctype_digit((string) $id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        $validos = Produto::query()
            ->where('empresa_id', $empresaId)
            ->whereIn('id', $ids->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        if ($validos->count() !== $ids->count()) {
            throw (new ModelNotFoundException())->setModel(Produto::class, $ids->all());
        }
    }

    public function configNota(int $empresaId, int $id): ConfigNota
    {
        return $this->recurso(ConfigNota::class, $empresaId, $id);
    }

    public function configNotaDaEmpresa(int $empresaId): ConfigNota
    {
        return ConfigNota::query()
            ->where('empresa_id', $empresaId)
            ->firstOrFail();
    }

    /**
     * Garante que a natureza padrão gravada na configuração também pertence ao
     * mesmo tenant. Isso impede referência cruzada mesmo em dados legados.
     */
    public function naturezaPadraoDaConfig(int $empresaId): ?NaturezaOperacao
    {
        $config = $this->configNotaDaEmpresa($empresaId);
        $naturezaId = (int) $config->nat_op_padrao;

        if ($naturezaId <= 0) {
            return null;
        }

        return $this->natureza($empresaId, $naturezaId);
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
