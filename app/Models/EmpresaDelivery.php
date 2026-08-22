<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresaDelivery extends Model
{
    // Especifica o nome da tabela
    protected $table = 'ws_empresa';

    // Especifica a chave primária
    protected $primaryKey = 'id_empresa';

    // Indica se a chave primária é auto-incrementada
    public $incrementing = true;

    // Indica se o modelo deve gerenciar os timestamps automaticamente
    public $timestamps = false;

    // Lista dos atributos que podem ser atribuídos em massa
    protected $fillable = [
        'user_id',
        'nome_empresa',
        'descricao_empresa',
        'nome_empresa_link',
        'cnpj_empresa',
        'email_empresa',
        'telefone_empresa',
        'end_rua_n_empresa',
        'end_bairro_empresa',
        'cidade_empresa',
        'end_uf_empresa',
        'cep_empresa',
        'img_logo',
        'img_header',
        'facebook_status',
        'twitter_status',
        'instagram_status',
        'facebook_empresa',
        'instagram_empresa',
        'twitter_empresa',
        'genero_empresa',
        'config_segunda',
        'config_terca',
        'config_quarta',
        'config_quinta',
        'config_sexta',
        'config_sabado',
        'config_domingo',
        'config_segundaa',
        'config_tercaa',
        'config_quartaa',
        'config_quintaa',
        'config_sextaa',
        'config_sabadoo',
        'config_domingoo',
        'segunda_manha_de',
        'segunda_manha_ate',
        'segunda_tarde_de',
        'segunda_tarde_ate',
        'terca_manha_de',
        'terca_manha_ate',
        'terca_tarde_de',
        'terca_tarde_ate',
        'quarta_manha_de',
        'quarta_manha_ate',
        'quarta_tarde_de',
        'quarta_tarde_ate',
        'quinta_manha_de',
        'quinta_manha_ate',
        'quinta_tarde_de',
        'quinta_tarde_ate',
        'sexta_manha_de',
        'sexta_manha_ate',
        'sexta_tarde_de',
        'sexta_tarde_ate',
        'sabado_manha_de',
        'sabado_manha_ate',
        'sabado_tarde_de',
        'sabado_tarde_ate',
        'domingo_manha_de',
        'domingo_manha_ate',
        'domingo_tarde_de',
        'domingo_tarde_ate',
        'config_delivery',
        'config_delivery_free',
        'op_entrar_btn',
        'empresa_data_renovacao',
        'msg_tempo_delivery',
        'msg_tempo_buscar',
        'minimo_delivery',
        'confirm_delivery',
        'confirm_balcao',
        'confirm_mesa',
        'cor_topo',
        'cor_loading',
        'cor_titulo_produtos',
        'btn_whats',
        'token_blocked',
        'type_pay',
        'access_token_mp',
        'public_key',
        'email_pagseguro',
        'token_pagseguro',
        'valorcach',
        'qtcach',
        'cupom',
        'linkavali',
        'layout',
    ];
}
