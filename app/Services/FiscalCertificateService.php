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
        if ($empresaId <= 0) {
            throw new \InvalidArgumentException('Empresa inválida.');
        }

        $deleted = DB::transaction(fn () => Certificado::query()
            ->where('empresa_id', $empresaId)
            ->delete());

        $this->deletePrivateCopiesForEmpresa($empresaId);

        return $deleted;
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

    private function deletePrivateCopiesForEmpresa(int $empresaId): void
    {
        $directory = storage_path('app/private/certificados/' . $empresaId);

        if (!is_dir($directory)) {
            return;
        }

        $files = scandir($directory);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $file;
            if (is_file($path) || is_link($path)) {
                @unlink($path);
            }
        }

        @rmdir($directory);
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
