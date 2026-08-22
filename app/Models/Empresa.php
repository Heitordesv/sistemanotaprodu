<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UsuarioAcesso;

class Empresa extends Model
{
	protected $fillable = [
		'razao_social', 'rua', 'numero', 'bairro', 'cidade_id', 'telefone', 'email', 'status', 'cpf_cnpj',
		'permissao', 'tipo_representante', 'perfil_id', 'mensagem_bloqueio', 'info_contador',
		'nome_fantasia', 'contador_id', 'hash', 'tipo_contador', 'uf', 'cep', 'representante_legal',
		'cpf_representante_legal'
	];

	public static function getId()
	{
		$value = session('user_logged');
		return $value['empresa'];
	}

	public function countProdutos()
	{
		$count = Produto::where('empresa_id', $this->id)
			->count();
		return $count;
	}

	public function status()
	{
		if (sizeof($this->usuarios) == 0) return 0;
		$usuario = $this->usuarios[0];
		$value = session('user_logged');

		if (isSuper($usuario->login)) {
			return -1;
		}

		if ($this->status == 0) {
			return 0;
		} else if (!$this->planoEmpresa) {
			return 0;
		} else {
			$hoje = date('Y-m-d');
			$exp = $this->planoEmpresa ? $this->planoEmpresa->expiracao : null;
			$dif = strtotime($exp) - strtotime($hoje);
			$planoExpiradoDias = $dif / 60 / 60 / 24;

			if (strtotime($hoje) > strtotime($exp) && $this->planoEmpresa->expiracao != '0000-00-00') {
				return 0;
			}
		}
		return 1;
	}

	public function ultimoLogin($empresaId)
	{
		$acesso = UsuarioAcesso::select('usuario_acessos.*')
			->join('usuarios', 'usuarios.id', '=', 'usuario_acessos.usuario_id')
			->where('usuario_acessos.status', 1)
			->where('usuarios.empresa_id', $empresaId)
			->orderBy('usuario_acessos.id', 'desc')
			->first();

		return $acesso;
	}

	public function ultimoLogin2($empresaId)
	{
		$acesso = UsuarioAcesso::select('usuario_acessos.*')
			->join('usuarios', 'usuarios.id', '=', 'usuario_acessos.usuario_id')
			->where('usuarios.empresa_id', $empresaId)
			->orderBy('usuario_acessos.id', 'desc')
			->first();

		return $acesso;
	}

	public function cidade()
	{
		return $this->belongsTo(Cidade::class, 'cidade_id');
	}

	public function configNota()
	{
		return $this->hasOne(ConfigNota::class, 'empresa_id', 'id');
	}

	public function certificado()
	{
		return $this->hasOne('App\Models\Certificado', 'empresa_id', 'id');
	}

	public function tributacao()
	{
		return $this->hasOne('App\Models\Tributacao', 'empresa_id', 'id');
	}

	public function escritorio()
	{
		return $this->hasOne('App\Models\EscritorioContabil', 'empresa_id', 'id');
	}

	public function usuarioFirst()
	{
		return $this->hasOne('App\Models\Usuario', 'empresa_id', 'id');
	}

	public function acessos()
	{
		$usuarios = $this->usuarios;
		$cont = 0;
		foreach ($usuarios as $u) {
			$cont += sizeof($u->acessos);
		}
		return $cont;
	}

	public function contador()
	{
		return $this->belongsTo('App\Models\Contador', 'contador_id');
	}

	public function usuarios()
	{
		return $this->hasMany('App\Models\Usuario', 'empresa_id', 'id');
	}

	public function clientes()
	{
		return $this->hasMany('App\Models\Cliente', 'empresa_id', 'id');
	}

	public function fornecedores()
	{
		return $this->hasMany('App\Models\Fornecedor', 'empresa_id', 'id');
	}

	public function produtos()
	{
		return $this->hasMany('App\Models\Produto', 'empresa_id', 'id');
	}

	public function veiculos()
	{
		return $this->hasMany('App\Models\Veiculo', 'empresa_id', 'id');
	}

	public function vendas()
	{
		return $this->hasMany('App\Models\Venda', 'empresa_id', 'id');
	}

	public function vendasCaixa()
	{
		return $this->hasMany('App\Models\VendaCaixa', 'empresa_id', 'id');
	}

	public function contrato()
	{
		return $this->hasOne('App\Models\EmpresaContrato', 'empresa_id', 'id');
	}

	public function representante()
	{
		return $this->hasOne('App\Models\RepresentanteEmpresa', 'empresa_id', 'id');
	}

	public function cte()
	{
		return $this->hasMany('App\Models\Cte', 'empresa_id', 'id');
	}

	public function mdfe()
	{
		return $this->hasMany('App\Models\Mdfe', 'empresa_id', 'id');
	}

	public function nfes()
	{
		$vendas = $this->vendas;
		$cont = 0;
		foreach ($vendas as $v) {
			if ($v->NfNumero > 0) $cont++;
		}
		return $cont;
	}

	public function nfces()
	{
		$vendas = $this->vendasCaixa;
		$cont = 0;
		foreach ($vendas as $v) {
			if ($v->NFcNumero > 0) $cont++;
		}
		return $cont;
	}

	public function ctes()
	{
		$ct = $this->cte;
		$cont = 0;
		foreach ($ct as $c) {
			if ($c->cte_numero > 0) $cont++;
		}
		return $cont;
	}

	public function mdfes()
	{
		$md = $this->mdfe;
		$cont = 0;
		foreach ($md as $m) {
			if ($m->mdfe_numero > 0) $cont++;
		}
		return $cont;
	}

	public function planoEmpresa()
	{
		return $this->hasOne(PlanoEmpresa::class, 'empresa_id', 'id');
	} 
	public function planoEmpresas()
    {
        return $this->hasMany(PlanoEmpresa::class, 'empresa_id', 'id');
    }


	public function isMaster()
	{
		if (sizeof($this->usuarios) == 0) return 0;

		$usuario = $this->usuarios[0];
		if ($usuario->login == env("USERMASTER")) return 1;
		return 0;
	}
public function configEcommerce()
{
    return $this->hasOne(\App\Models\ConfigEcommerce::class);
}

	public static function validaLink($link, $permissoes)
	{

		if (in_array($link, $permissoes)) {
			return true;
		} else {
			if (strlen($link) > 60) {
				$rt = str_replace(".", "_", $link);
				if (in_array($rt, $permissoes)) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}
}
