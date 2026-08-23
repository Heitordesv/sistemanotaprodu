<?php

namespace App\Services;

use App\Models\Certificado;
use Illuminate\Support\Facades\DB;
use NFePHP\Common\Certificate;

class FiscalCertificateService
{
    public function replaceForEmpresa(int $empresaId, string $arquivo, string $senha): Certificado
    {
        if ($empresaId <= 0 || $arquivo === '' || $senha === '') {
            throw new \InvalidArgumentException('Dados do certificado inválidos.');
        }

        return DB::transaction(function () use ($empresaId, $arquivo, $senha) {
            $certificados = Certificado::query()
                ->where('empresa_id', $empresaId)
                ->lockForUpdate()
                ->orderByDesc('id')
                ->get();

            $certificado = $certificados->first();

            if ($certificado) {
                $certificado->arquivo = $arquivo;
                $certificado->senha = $senha;
                $certificado->save();

                Certificado::query()
                    ->where('empresa_id', $empresaId)
                    ->where('id', '<>', $certificado->id)
                    ->delete();

                return $certificado->fresh();
            }

            return Certificado::create([
                'empresa_id' => $empresaId,
                'arquivo' => $arquivo,
                'senha' => $senha,
            ]);
        });
    }

    public function forEmpresa(int $empresaId): ?Certificado
    {
        return Certificado::query()
            ->where('empresa_id', $empresaId)
            ->orderByDesc('id')
            ->first();
    }

    public function deleteForEmpresa(int $empresaId): int
    {
        return DB::transaction(fn () => Certificado::query()
            ->where('empresa_id', $empresaId)
            ->delete());
    }

    public function publicInfoForEmpresa(int $empresaId): ?array
    {
        $certificado = $this->forEmpresa($empresaId);

        if (!$certificado) {
            return null;
        }

        try {
            $info = Certificate::readPfx(
                $this->certificateBytes((string) $certificado->arquivo),
                (string) $certificado->senha
            );

            $publicKey = $info->publicKey;

            return [
                'serial' => $publicKey->serialNumber,
                'inicio' => $publicKey->validFrom->format('d-m-Y H:i'),
                'expiracao' => $publicKey->validTo->format('d-m-Y H:i'),
                'id' => $publicKey->commonName,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'configurado' => true,
                'legivel' => false,
            ];
        }
    }

    private function certificateBytes(string $arquivo): string
    {
        $decoded = base64_decode($arquivo, true);

        if ($decoded !== false && base64_encode($decoded) === $arquivo) {
            return $decoded;
        }

        return $arquivo;
    }
}
