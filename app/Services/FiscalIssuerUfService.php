<?php

namespace App\Services;

use App\Models\ConfigNota;

class FiscalIssuerUfService
{
    public function resolve(ConfigNota $config): string
    {
        $cidade = $config->relationLoaded('cidade')
            ? $config->getRelation('cidade')
            : $config->cidade;

        foreach ([
            $cidade?->uf,
            ConfigNota::getUF($config->cUF),
            $config->UF,
        ] as $uf) {
            $uf = strtoupper(trim((string) $uf));

            if (in_array($uf, ConfigNota::estados(), true)) {
                return $uf;
            }
        }

        return '';
    }
}
