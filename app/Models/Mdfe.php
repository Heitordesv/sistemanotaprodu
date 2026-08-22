<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mdfe extends Model
{

	protected $fillable = [
		'uf_inicio', 'uf_fim', 'encerrado', 'data_inicio_viagem', 'carga_posterior',
		'veiculo_tracao_id', 'veiculo_reboque_id', 'veiculo_reboque2_id',
		'veiculo_reboque3_id', 'estado_emissao', 'seguradora_nome',
		'seguradora_cnpj', 'numero_apolice', 'numero_averbacao', 'valor_carga',
		'quantidade_carga', 'info_complementar', 'info_adicional_fisco', 'cnpj_contratante',
		'mdfe_numero', 'condutor_nome', 'condutor_cpf', 'tp_emit', 'tp_transp', 'lac_rodo',
		'chave', 'protocolo', 'empresa_id', 'produto_pred_nome', 'produto_pred_ncm',
		'produto_pred_cod_barras', 'cep_carrega', 'cep_descarrega', 'tp_carga',
		'latitude_carregamento', 'longitude_carregamento', 'latitude_descarregamento',
		'longitude_descarregamento', 'filial_id'
	];

	public function veiculoTracao()
	{
		return $this->belongsTo(Veiculo::class, 'veiculo_tracao_id');
	}

	public function filial()
	{
		return $this->belongsTo(Filial::class, 'filial_id');
	}

	public function veiculoReboque()
	{
		return $this->belongsTo(Veiculo::class, 'veiculo_reboque_id');
	}

	public function veiculoReboque2()
	{
		return $this->belongsTo(Veiculo::class, 'veiculo_reboque2_id');
	}

	public function veiculoReboque3()
	{
		return $this->belongsTo(Veiculo::class, 'veiculo_reboque3_id');
	}

	public function municipiosCarregamento()
	{
		return $this->hasMany(MunicipioCarregamento::class, 'mdfe_id', 'id');
	}

	public function ciots()
	{
		return $this->hasMany(Ciot::class, 'mdfe_id', 'id');
	}

	public function percurso()
	{
		return $this->hasMany('App\Models\Percurso', 'mdfe_id', 'id');
	}

	public function valesPedagio()
	{
		return $this->hasMany(ValePedagio::class, 'mdfe_id', 'id');
	}

	public function infoDescarga()
	{
		return $this->hasMany('App\Models\InfoDescarga', 'mdfe_id', 'id');
	}

	public static function filtroData($dataInicial, $dataFinal, $estado)
	{
		$value = session('user_logged');
		$empresa_id = $value['empresa'];
		$c = Mdfe::where('empresa_id', $empresa_id)
			->whereBetween('created_at', [
				$dataInicial,
				$dataFinal
			]);

		if ($estado != 'TODOS') $c->where('estado', $estado);

		return $c->get();
	}

	public static function lastMdfe()
	{
		$value = session('user_logged');
		$empresa_id = $value['empresa'];
		$mdfe = Mdfe::where('mdfe_numero', '!=', 0)
			->where('empresa_id', $empresa_id)
			->orderBy('mdfe_numero', 'desc')
			->first();

		if ($mdfe == null) {
			return ConfigNota::where('empresa_id', $empresa_id)
				->first()->ultimo_numero_mdfe;
		} else {
			$configNum = ConfigNota::where('empresa_id', $empresa_id)
				->first()->ultimo_numero_mdfe;
			return $configNum > $mdfe->mdfe_numero ? $configNum : $mdfe->mdfe_numero;
		}
	}

	public function itens()
	{
		return $this->hasMany('App\Models\ItemVenda', 'venda_id', 'id');
	}

	public static function cUF()
	{
		return [
			'AC' => 'AC',
			'AL' => 'AL',
			'AM' => 'AM',
			'AP' => 'AP',
			'BA' => 'BA',
			'CE' => 'CE',
			'DF' => 'DF',
			'ES' => 'ES',
			'GO' => 'GO',
			'MA' => 'MA',
			'MG' => 'MG',
			'MS' => 'MS',
			'MT' => 'MT',
			'PA' => 'PA',
			'PB' => 'PB',
			'PE' => 'PE',
			'PI' => 'PI',
			'PR' => 'PR',
			'RJ' => 'RJ',
			'RN' => 'RN',
			'RO' => 'RO',
			'RR' => 'RR',
			'RS' => 'RS',
			'SC' => 'SC',
			'SE' => 'SE',
			'SP' => 'SP',
			'TO' => 'TO'
		];
	}

	public static function tiposUnidadeTransporte()
	{
		return [
			'1' => 'Rodoviário Tração',
			'2' => 'Rodoviário Reboque',
			'3' => 'Navio',
			'4' => 'Balsa',
			'5' => 'Aeronave',
			'6' => 'Vagão',
			'7' => 'Outros'
		];
	}

	public static function tiposCarga()
	{
		return [
			'01' => 'Granel sólido',
			'02' => 'Granel líquido',
			'03' => 'Frigorificada',
			'04' => 'Conteinerizada',
			'05' => 'Carga Geral',
			'06' => 'Neogranel',
			'07' => 'Perigosa (granel sólido)',
			'08' => 'Perigosa (granel líquido)',
			'09' => 'Perigosa (carga frigorificada)',
			'10' => 'Perigosa (conteinerizada)',
			'11' => 'Perigosa (carga geral)'
		];
	}

	public function estadoEmissao()
	{
		if ($this->estado_emissao == 'aprovado') {
			return "<span class='btn btn-sm btn-success px-3'>Aprovado</span>";
		} else if ($this->estado_emissao == 'cancelado') {
			return "<span class='btn btn-sm btn-danger px-3'>Cancelado</span>";
		} else if ($this->estado_emissao == 'rejeitado') {
			return "<span class='btn btn-sm btn-warning px-3'>Rejeitado</span>";
		}
		return "<span class='btn btn-sm btn-info px-3'>Novo</span>";
	}
}
