<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class EmpresaIntegracao extends Model
{
    protected $table = 'empresa_integracoes';

    protected $fillable = [
        'empresa_id',
        'whatsapp_provider',
        'evolution_base_url',
        'evolution_api_key',
        'evolution_instance',
        'evolution_status',
        'evolution_numero',
        'evolution_webhook_secret',
        'whatsapp_ativo',
        'whatsapp_conectado_em',
        'ai_provider',
        'gemini_model',
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
        'agente_ativo',
        'agente_instrucoes',
        'agente_cobranca',
        'agente_ordem_servico',
        'responder_whatsapp',
    ];

    protected $casts = [
        'whatsapp_ativo' => 'boolean',
        'agente_ativo' => 'boolean',
        'agente_cobranca' => 'boolean',
        'agente_ordem_servico' => 'boolean',
        'responder_whatsapp' => 'boolean',
        'whatsapp_conectado_em' => 'datetime',
        'google_token_expires_at' => 'datetime',
    ];

    public function getEvolutionApiKeyAttribute($value)
    {
        return $this->decryptSensitiveValue($value);
    }

    public function setEvolutionApiKeyAttribute($value): void
    {
        $this->attributes['evolution_api_key'] = $this->encryptSensitiveValue($value);
    }

    public function getEvolutionWebhookSecretAttribute($value)
    {
        return $this->decryptSensitiveValue($value);
    }

    public function setEvolutionWebhookSecretAttribute($value): void
    {
        $this->attributes['evolution_webhook_secret'] = $this->encryptSensitiveValue($value);
    }

    public function getGoogleAccessTokenAttribute($value)
    {
        return $this->decryptSensitiveValue($value);
    }

    public function setGoogleAccessTokenAttribute($value): void
    {
        $this->attributes['google_access_token'] = $this->encryptSensitiveValue($value);
    }

    public function getGoogleRefreshTokenAttribute($value)
    {
        return $this->decryptSensitiveValue($value);
    }

    public function setGoogleRefreshTokenAttribute($value): void
    {
        $this->attributes['google_refresh_token'] = $this->encryptSensitiveValue($value);
    }

    private function decryptSensitiveValue($value)
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // Compatibilidade com registros criados antes da criptografia.
            // Ao salvar novamente, o mutator grava o valor criptografado.
            return $value;
        }
    }

    private function encryptSensitiveValue($value)
    {
        if ($value === null || $value === '') {
            return $value;
        }

        // Evita criptografar novamente um valor que já esteja criptografado.
        try {
            Crypt::decryptString($value);
            return $value;
        } catch (DecryptException $e) {
            return Crypt::encryptString($value);
        }
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}