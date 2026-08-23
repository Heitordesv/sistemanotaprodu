<?php

namespace App\Models;

use App\Services\CaixaResumoService;
use Illuminate\Database\Eloquent\Model;

class AberturaCaixa extends Model
{
    protected $fillable = [
        'usuario_id',
        'valor',
        'ultima_venda_nfe',
        'ultima_venda_nfce',
        'empresa_id',
        'primeira_venda_nfe',
        'primeira_venda_nfce',
        'status',
        'valor_dinheiro_caixa',
        'filial_id',
    ];

    protected static function booted(): void
    {
        static::saving(function (AberturaCaixa $abertura) {
            if (!$abertura->isDirty('status') || (int) $abertura->status !== 1) {
                return;
            }

            $resumo = app(CaixaResumoService::class)->resumir($abertura, now());
            $abertura->valor_dinheiro_caixa = $resumo['dinheiroNaGaveta'];
        });
    }

    public function resumoCaixa($fim = null): array
    {
        return app(CaixaResumoService::class)->resumir($this, $fim);
    }

    /**
     * O próprio ID da abertura identifica a sessão operacional do caixa.
     * Ex.: Caixa #101, Caixa #102. Cada sessão pertence a um operador.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'filial_id');
    }
}
