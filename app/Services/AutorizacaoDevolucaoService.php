<?php

namespace App\Services;

use App\Models\AutorizacaoDevolucao;
use App\Models\Usuario;
use App\Models\VendaCaixa;
use Illuminate\Validation\ValidationException;

class AutorizacaoDevolucaoService
{
    public function autorizar(int $empresaId, $administradorId = null, $senha = null): array
    {
        $solicitante = Usuario::query()
            ->where('id', (int) get_id_user())
            ->where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->first();

        if (!$solicitante) {
            abort(403, 'Usuário logado inválido para esta empresa.');
        }

        if ((bool) $solicitante->adm) {
            return [
                'solicitante' => $solicitante,
                'autorizador' => $solicitante,
            ];
        }

        if (!$administradorId || !is_string($senha) || trim($senha) === '') {
            throw ValidationException::withMessages([
                'admin_senha' => 'Selecione um administrador e informe a senha para autorizar a devolução.',
            ]);
        }

        $administrador = Usuario::query()
            ->where('id', (int) $administradorId)
            ->where('empresa_id', $empresaId)
            ->where('adm', true)
            ->where('ativo', true)
            ->first();

        if (
            !$administrador ||
            !hash_equals((string) $administrador->senha, md5($senha))
        ) {
            throw ValidationException::withMessages([
                'admin_senha' => 'Administrador ou senha inválidos.',
            ]);
        }

        return [
            'solicitante' => $solicitante,
            'autorizador' => $administrador,
        ];
    }

    public function registrar(
        VendaCaixa $venda,
        Usuario $solicitante,
        Usuario $autorizador,
        string $tipo,
        ?string $motivo = null
    ): AutorizacaoDevolucao {
        return AutorizacaoDevolucao::create([
            'empresa_id' => (int) $venda->empresa_id,
            'venda_caixa_id' => (int) $venda->id,
            'usuario_solicitante_id' => (int) $solicitante->id,
            'usuario_solicitante_nome' => (string) $solicitante->nome,
            'usuario_autorizador_id' => (int) $autorizador->id,
            'usuario_autorizador_nome' => (string) $autorizador->nome,
            'tipo' => $tipo,
            'numero_nfce' => $venda->numero_nfce,
            'valor_venda' => (float) $venda->valor_total,
            'motivo' => $motivo ? trim($motivo) : null,
        ]);
    }
}