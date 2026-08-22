<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigCaixa extends Model
{
	use HasFactory;
	protected $fillable = [
		'finalizar', 'reiniciar', 'editar_desconto', 'editar_acrescimo', 'editar_observacao',
		'setar_valor_recebido', 'forma_pagamento_dinheiro', 'forma_pagamento_debito',
		'forma_pagamento_credito', 'forma_pagamento_pix', 'setar_leitor',
		'valor_recebido_automatico', 'usuario_id', 'balanca_valor_peso',
		'balanca_digito_verificador', 'mercadopago_public_key', 'mercadopago_access_token',
		'modelo_pdv', 'impressora_modelo', 'setar_quantidade', 'finalizar_fiscal',
		'finalizar_nao_fiscal', 'tipos_pagamento', 'pagamento_padrao'
	];
}
