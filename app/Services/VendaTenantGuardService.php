<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Filial;
use App\Models\NaturezaOperacao;
use App\Models\Produto;
use App\Models\Transportadora;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VendaTenantGuardService
{
    public function prepararUpdate(Request $request, int $vendaId): Venda
    {
        $venda = $this->validar($request, $vendaId);

        // abertura_caixa_id existe no $fillable somente para permitir a criação
        // vinculada ao caixa resolvido pelo servidor. Depois da criação, o vínculo
        // histórico é imutável e nunca pode vir de PUT/PATCH do navegador.
        $request->request->remove('abertura_caixa_id');

        return $venda;
    }

    public function validar(Request $request, ?int $vendaId = null): ?Venda
    {
        $empresaId = (int) $request->empresa_id;

        if ($empresaId <= 0) {
            throw ValidationException::withMessages([
                'empresa_id' => 'Empresa da sessão não identificada.',
            ]);
        }

        $venda = null;
        if ($vendaId !== null) {
            $venda = Venda::query()
                ->where('id', $vendaId)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();
        }

        $this->validarEntidade(
            NaturezaOperacao::class,
            $request->input('natureza_id'),
            $empresaId,
            'natureza_id',
            'A natureza de operação informada não pertence à empresa atual.'
        );

        $produtoIds = collect((array) $request->input('produto_id', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($produtoIds->isNotEmpty()) {
            $produtosValidos = Produto::query()
                ->where('empresa_id', $empresaId)
                ->whereIn('id', $produtoIds->all())
                ->pluck('id')
                ->map(fn ($id) => (int) $id);

            if ($produtosValidos->count() !== $produtoIds->count()) {
                throw ValidationException::withMessages([
                    'produto_id' => 'Um ou mais produtos não pertencem à empresa atual.',
                ]);
            }
        }

        $this->validarEntidade(
            Cliente::class,
            $request->input('cliente_id'),
            $empresaId,
            'cliente_id',
            'O cliente informado não pertence à empresa atual.'
        );

        $this->validarEntidade(
            Transportadora::class,
            $request->input('transportadora_id'),
            $empresaId,
            'transportadora_id',
            'A transportadora informada não pertence à empresa atual.'
        );

        $filialId = $request->input('filial_id');
        if ($filialId !== null && $filialId !== '' && (int) $filialId !== -1) {
            $this->validarEntidade(
                Filial::class,
                $filialId,
                $empresaId,
                'filial_id',
                'A filial informada não pertence à empresa atual.'
            );
        }

        return $venda;
    }

    private function validarEntidade(
        string $modelClass,
        $id,
        int $empresaId,
        string $campo,
        string $mensagem
    ): void {
        if ($id === null || $id === '') {
            return;
        }

        $existe = $modelClass::query()
            ->where('id', (int) $id)
            ->where('empresa_id', $empresaId)
            ->exists();

        if (!$existe) {
            throw ValidationException::withMessages([
                $campo => $mensagem,
            ]);
        }
    }
}
