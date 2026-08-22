<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WsConfiempresa extends Model
{
    protected $table = 'ws_empresa';
    protected $primaryKey = 'id_empresa';
    public $timestamps = false; // Desativa created_at e updated_at, caso não use

    protected $fillable = [
        'id_empresa', 'user_id', 'nome_empresa', 'descricao_empresa', 'nome_empresa_link', 'cnpj_empresa', 'email_empresa', 'telefone_empresa',
        'end_rua_n_empresa', 'end_bairro_empresa', 'cidade_empresa', 'end_uf_empresa', 'cep_empresa',
        'img_logo', 'img_header',
        'facebook_status', 'twitter_status', 'instagram_status',
        'facebook_empresa', 'instagram_empresa', 'twitter_empresa',
        'genero_empresa',
        // Campos de configuração que serão salvos como 'true' ou 'false' (string)
        'config_segunda', 'config_terca', 'config_quarta', 'config_quinta', 'config_sexta', 'config_sabado', 'config_domingo',
        'config_segundaa', 'config_tercaa', 'config_quartaa', 'config_quintaa', 'config_sextaa', 'config_sabadoo', 'config_domingoo',
        'segunda_manha_de', 'segunda_manha_ate', 'segunda_tarde_de', 'segunda_tarde_ate',
        'terca_manha_de', 'terca_manha_ate', 'terca_tarde_de', 'terca_tarde_ate',
        'quarta_manha_de', 'quarta_manha_ate', 'quarta_tarde_de', 'quarta_tarde_ate',
        'quinta_manha_de', 'quinta_manha_ate', 'quinta_tarde_de', 'quinta_tarde_ate',
        'sexta_manha_de', 'sexta_manha_ate', 'sexta_tarde_de', 'sexta_tarde_ate',
        'sabado_manha_de', 'sabado_manha_ate', 'sabado_tarde_de', 'sabado_tarde_ate',
        'domingo_manha_de', 'domingo_manha_ate', 'domingo_tarde_de', 'domingo_tarde_ate',
        'config_delivery', 'config_delivery_free', 'op_entrar_btn', 'empresa_data_renovacao',
        'msg_tempo_delivery', 'msg_tempo_buscar', 'minimo_delivery',
        'confirm_delivery', 'confirm_balcao', 'confirm_mesa',
        'cor_topo', 'cor_loading', 'cor_titulo_produtos',
        'btn_whats', 'token_blocked', 'type_pay', 'access_token_mp', 'public_key',
        'email_pagseguro', 'token_pagseguro',
        'valorcach', 'qtcach', 'cupom', 'linkavali', 'layout', 'ativatele','data_agendada',
    ];

    /**
     * Os atributos que devem ser "castados" para tipos nativos.
     * MANTENHA SOMENTE OS CAMPOS QUE REALMENTE PRECISAM DE CASTING,
     * OU REMOVA TUDO SE FOR USAR APENAS MUTATORS.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'empresa_data_renovacao' => 'datetime', // Exemplo: se for uma data e você quiser um objeto Carbon
        // REMOVEMOS AQUI OS CAMPOS config_segunda, etc., pois vamos usar mutators
    ];

    // --- MUTATORS PARA CAMPOS BOOLEANOS ---

    public function setConfigSegundaAttribute($value)
    {
        $this->attributes['config_segunda'] = $value ? 'true' : 'false';
    }

    public function setConfigTercaAttribute($value)
    {
        $this->attributes['config_terca'] = $value ? 'true' : 'false';
    }

    public function setConfigQuartaAttribute($value)
    {
        $this->attributes['config_quarta'] = $value ? 'true' : 'false';
    }

    public function setConfigQuintaAttribute($value)
    {
        $this->attributes['config_quinta'] = $value ? 'true' : 'false';
    }

    public function setConfigSextaAttribute($value)
    {
        $this->attributes['config_sexta'] = $value ? 'true' : 'false';
    }

    public function setConfigSabadoAttribute($value)
    {
        $this->attributes['config_sabado'] = $value ? 'true' : 'false';
    }

    public function setConfigDomingoAttribute($value)
    {
        $this->attributes['config_domingo'] = $value ? 'true' : 'false';
    }

    public function setConfigSegundaaAttribute($value)
    {
        $this->attributes['config_segundaa'] = $value ? 'true' : 'false';
    }

    public function setConfigTercaaAttribute($value)
    {
        $this->attributes['config_tercaa'] = $value ? 'true' : 'false';
    }

    public function setConfigQuartaaAttribute($value)
    {
        $this->attributes['config_quartaa'] = $value ? 'true' : 'false';
    }

    public function setConfigQuintaaAttribute($value)
    {
        $this->attributes['config_quintaa'] = $value ? 'true' : 'false';
    }

    public function setConfigSextaaAttribute($value)
    {
        $this->attributes['config_sextaa'] = $value ? 'true' : 'false';
    }

    public function setConfigSabadooAttribute($value)
    {
        $this->attributes['config_sabadoo'] = $value ? 'true' : 'false';
    }

    public function setConfigDomingooAttribute($value)
    {
        $this->attributes['config_domingoo'] = $value ? 'true' : 'false';
    }

    // --- ACCESSORS PARA LER OS DADOS COMO BOOLEANOS NO PHP ---
    // (Opcional, mas recomendado para consistência no código PHP)

    public function getConfigSegundaAttribute($value)
    {
        return $value === 'true';
    }

    public function getConfigTercaAttribute($value)
    {
        return $value === 'true';
    }

    public function getConfigQuartaAttribute($value)
    {
        return $value === 'true';
    }

    public function getConfigQuintaAttribute($value)
    {
        return $value === 'true';
    }

    public function getConfigSextaAttribute($value)
    {
        return $value === 'true';
    }

    public function getConfigSabadoAttribute($value)
    {
        return $value === 'true';
    }

    public function getConfigDomingoAttribute($value)
    {
        return $value === 'true';
    }

    public function getConfigSegundaaAttribute($value)
    {
        return $value === 'true';
    }

    public function getConfigTercaaAttribute($value)
    {
        return $value === 'true';
    }

    public function getConfigQuartaaAttribute($value)
    {
        return $value === 'true';
    }

    public function getConfigQuintaaAttribute($value)
    {
        return $value === 'true';
    }

    public function getConfigSextaaAttribute($value)
    {
        return $value === 'true';
    }

    public function getConfigSabadooAttribute($value)
    {
        return $value === 'true';
    }

    public function getConfigDomingooAttribute($value)
    {
        return $value === 'true';
    }
}