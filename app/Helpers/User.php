<?php

use Illuminate\Support\Facades\DB;
use App\Models\EscritorioContabil;
use App\Models\Usuario;
use App\Models\Filial;

function is_adm()
{
    $usr = session('user_logged');
    return $usr != null && !empty($usr['adm']);
}

function get_id_user()
{
	$usr = session('user_logged');
	return $usr == null ? null : $usr['id'];
}

function tabelasArmazenamento()
{
	// indice nome da tabela, valor em kb
	return [
		'clientes' => 5,
		'produtos' => 8,
		'fornecedors' => 4,
		'vendas' => 4,
		'venda_caixas' => 4,
		'transportadoras' => 4,
		'orcamentos' => 4,
		'categorias' => 4,
	];
}

function isSuper($login)
{
	$arrSuper = explode(',', env("USERMASTER"));

	if (in_array($login, $arrSuper)) {
		return true;
	}
	return false;
}

function getSuper()
{
	$arrSuper = explode(',', env("USERMASTER"));

	return $arrSuper[0];
}

function importaXmlSieg($file, $empresa_id)
{
	$escritorio = EscritorioContabil::where('empresa_id', $empresa_id)
		->first();

	if ($escritorio != null && $escritorio->token_sieg != "") {
		$url = "https://api.sieg.com/aws/api-xml.ashx";

		$curl = curl_init();

		$headers = [];

		$data = $file;
		curl_setopt($curl, CURLOPT_URL, $url . "?apikey=" . $escritorio->token_sieg . "&email=" . $escritorio->email);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_HEADER, false);
		$xml = json_decode(curl_exec($curl));
		if ($xml->Message == 'Importado com sucesso') {
			return $xml->Message;
		}
		return false;
	} else {
		return false;
	}
}

function valida_objeto($objeto)
{
	$usr = session('user_logged');
	if (isset($objeto['empresa_id']) && $objeto['empresa_id'] == $usr['empresa']) {
		return true;
	} else {
		return false;
	}
}

//metodos de filial

function __locaisAtivosUsuario($usuario)
{
	$locais = $usuario->locais != 'null' && $usuario->locais != '' ? json_decode($usuario->locais) : [];
	$locaisRetorno = [];
	$locaisRetorno['-1'] = 'Matriz';

	foreach ($locais as $l) {
		if ($l != -1) {
			$f = Filial::where('status', 1)->where('id', $l)->first();
			if ($f != null) {
				$locaisRetorno[$f->id] = $f->descricao;
			}
		}
	}
	return $locaisRetorno;
}

function __getLocaisUsarioLogado($usuario_id = null)
{
	$usr = Usuario::find(get_id_user());
	if ($usuario_id != null) {
		$usr = Usuario::find($usuario_id);
	}
	$loc = $usr->locais != null ? json_decode($usr->locais) : [];
	return $loc;
}

function getLocaisUsarioLogado()
{
	$usr = Usuario::find(get_id_user());
	$locais = [];
	$loc = $usr->locais != null && $usr->locais != 'null' ? json_decode($usr->locais) : [];
	if (sizeof($loc) > 0) {
		foreach ($loc as $l) {
			$f = Filial::find($l);
			if ($l == '-1') {
				$locais['-1'] = 'Matriz';
			} else {
				if ($f != null) {
					$locais[$f->id] = $f->descricao;
				}
			}
		}
	}
	return $locais;
}

function __locaisAtivos()
{
	$usr = session('user_logged');

	$locais = getLocaisUsarioLogado();
	if (sizeof($locais) > 0) {
		return $locais;
	}

	$filiais = Filial::where('empresa_id', $usr['empresa'])
		->where('status', 1)
		->get();

	$locais['-1'] = 'Matriz';

	return $locais;
}

function __locaisAtivosAll()
{
	$usr = session('user_logged');

	$filiais = Filial::where('empresa_id', $usr['empresa'])
		->where('status', 1)
		->get();

	$locais['-1'] = 'Matriz';

	foreach ($filiais as $f) {

		$locais[$f->id] = $f->descricao;
	}
	return $locais;
}

function __get_local_padrao()
{
	$usr = Usuario::find(get_id_user());
	return $usr->local_padrao;
}

function __user_all_locations()
{
	if (sizeof(__locaisAtivosAll()) == sizeof(__locaisAtivos()))
		return true;
	else
		return false;
}

function __view_locais_user_edit($locais_ativos, $lbl = "Locais de acesso")
{
	$locais = __locaisAtivosAll();

	$locais_ativos = $locais_ativos != null && $locais_ativos != 'null' ? json_decode($locais_ativos) : [];

	// $locais_ativos[] = 2;
	if (sizeof($locais) > 1) {
		$html = view('filial.partials.user_edit', compact('locais', 'lbl', 'locais_ativos'));
	} else {
		$v = array_key_first($locais);
		if ($v == -1) {
			$html = "";
		} else {
			$html = "<input type='' name='local[]' value='$v' />";
		}
	}

	return $html;
}

function empresaComFilial()
{
	$usr = session('user_logged');
	// dd($usr);
	$filiais = Filial::where('empresa_id', $usr['empresa'])
		->where('status', 1)
		->exists();
	return $filiais;
}

function __get_locais($locais_ativos)
{
	// $locais_ativos = $locais_ativos ? json_decode($locais_ativos) : [];
	$locais_ativos = $locais_ativos != null && $locais_ativos != 'null' ? json_decode($locais_ativos) : [];

	$html = "";
	foreach ($locais_ativos as $l) {
		$f = Filial::find($l);
		if ($l == '-1') {
			$html .= "Matriz | ";
		} else {
			if ($f != null) {
				$html .= "$f->descricao | ";
			}
		}
	}

	$html = substr($html, 0, strlen($html) - 2);
	return $html;
}

function __view_locais_user($lbl = "Locais de acesso")
{
	$locais = __locaisAtivosAll();

	if (sizeof($locais) > 1) {

		// $html = '<div class="form-group validated col-sm-10 col-lg-3">';
		// $html .= '<label class="col-form-label">' . $lbl . '</label>';
		// $html .= '<div class="">';
		// $html .= '<select id="locais" name="local[]" required class="form-control select2-custom" multiple>';
		// foreach ($locais as $key => $l) {
		// 	$html .= '<option value="' . $key . '">' . $l . '</option>';
		// }
		// $html .= '</select></div></div>';

		$html = view('filial.partials.user_create', compact('locais', 'lbl'));
	} else {
		$v = array_key_first($locais);
		if ($v == -1) {
			$html = "";
		} else {
			$html = "<input type='hidden' name='local[]' value='$v' />";
		}
	}

	return $html;
}

function __view_locais_select_filtro($lbl = "Local", $filial_id = null)
{
	$locais = __locaisAtivos();
	if (sizeof($locais) > 1) {
		if ($filial_id == null) {

			$url = request()->fullUrl();
			if (!str_contains($url, 'filtro')) {
				$filial_id = __get_local_padrao();
			}
		}
		// $html = '<div class="form-group col-12 col-lg-2">';
		// $html .= '<label class="col-form-label">' . $lbl . '</label>';
		// $html .= '<div><div class="input-group">';
		// $html .= '<select id="locais" name="filial_id" class="form-control custom-select">';
		// $html .= '<option value="">--</option>';
		// foreach ($locais as $key => $l) {
		// 	$html .= '<option ' . ($filial_id == $key ? 'selected' : '') . ' value="' . $key . '">' . $l . '</option>';
		// }
		// $html .= '</select></div></div></div>';

		$html = view('filial.partials.filtro', compact('locais', 'lbl', 'filial_id'));
	} else {

		$v = array_key_first($locais);
		if ($v == -1) {
			$html = "";
		} else {
			$html = "<input type='hidden' name='filial_id' value='$v' />";
		}
	}

	return $html;
}


function __view_locais($lbl = "Locais de acesso")
{
	$locais = __locaisAtivos();
	if (sizeof($locais) > 1) {

		// $html = '<div class="form-group validated col-sm-10 col-lg-3">';
		// $html .= '<label class="col-form-label">' . $lbl . '</label>';
		// $html .= '<div class="">';
		// $html .= '<select id="locais" name="local[]" required class="form-control select2-custom" multiple>';
		// foreach ($locais as $key => $l) {
		// 	$html .= '<option value="' . $key . '">' . $l . '</option>';
		// }
		// $html .= '</select></div></div>';
		$html = view('filial.partials.create_produtos', compact('locais', 'lbl'));
	} else {
		$v = array_key_first($locais);
		if ($v == -1) {
			$html = "";
		} else {
			$html = "<input type='hidden' name='local[]' value='$v' />";
		}
	}
	return $html;
}

function __view_locais_edit($locais_ativos, $lbl = "Locais de acesso")
{
	$locais = __locaisAtivos();
	if (sizeof($locais) > 1) {
		$locais_ativos = $locais_ativos == 'null' ? [] : json_decode($locais_ativos);
		$locais_ativos = $locais_ativos == '' ? [-1] : $locais_ativos;
		$html = view('filial.partials.edit_produtos', compact('locais', 'lbl', 'locais_ativos'));
	} else {
		$v = array_key_first($locais);
		if ($v == -1) {
			$html = "";
		} else {
			$html = "<input type='hidden' name='local[]' value='$v' />";
		}
	}
	return $html;
}

// create vendas e contas
function __view_locais_select($lbl = "Local")
{
	$filial_id = __get_local_padrao();
	$locais = __locaisAtivos();
	if (sizeof($locais) > 1) {
		$local_padrao = __get_local_padrao();
		$html = view('filial.partials.create_contas', compact('locais', 'lbl', 'filial_id'));
	} else {
		$v = array_key_first($locais);
		$html = "<input type='hidden' id='filial_id' name='filial_id' value='$v' />";
	}
	return $html;
}

function __view_locais_select_edit($lbl, $local_id)
{
	$locais = __locaisAtivos();
	if (sizeof($locais) > 1) {
		if (!$local_id) {
			$local_id = -1;
		}
		$filial_id = $local_id;
		$html = view('filial.partials.edit_contas', compact('locais', 'lbl', 'filial_id'));
	} else {
		$v = array_key_first($locais);
		$html = "<input type='hidden' id='filial_id' name='filial_id' value='$v' />";
	}
	return $html;
}

function __view_locais_select_home($lbl = "Local", $filial_id = null)
{
	$locais = __locaisAtivos();
	if (sizeof($locais) > 1) {
		$local_padrao = __get_local_padrao();
		$filial_id = $local_padrao;
		$html = view('filial.partials.filtro', compact('locais', 'lbl', 'filial_id'));
	} else {
		$v = array_key_first($locais);
		if ($v == -1) {
			$html = "";
		} else {
			$html = "<input type='hidden' id='filial_id' name='filial_id' value='$v' />";
		}
	}
	return $html;
}

function __view_locais_select_pdv($lbl = "Local")
{
	$locais = __locaisAtivos();
	if (sizeof($locais) > 1) {
		$html = '<div class="form-group col-lg-12 col-sm-6">';
		$html .= '<label class="col-form-label">' . $lbl . '</label>';
		$html .= '<div>';
		$html .= '<select name="filial_id" id="filial_id" class="form-control custom-select" required>';
		$html .= '<option value="">--</option>';
		foreach ($locais as $key => $l) {
			$html .= '<option value="' . $key . '">' . $l . '</option>';
		}
		$html .= '</select></div></div>';
	} else {
		$v = array_key_first($locais);
		$html = "<input type='hidden' id='filial_id' name='filial_id' value='$v' />";
	}
	return $html;
}

function __view_locais_select_filtro_xml($filial_id = null)
{
	$locais = __locaisAtivos();
	if (sizeof($locais) > 1) {
		if ($filial_id == null) {
			$filial_id = __get_local_padrao();
		}
		$html = view('filial.partials.filtro_enviarXml', compact('locais', 'filial_id'));
	} else {
		$v = array_key_first($locais);
		if ($v == -1) {
			$html = "";
		} else {
			$html = "<input type='hidden' name='filial_id' value='$v' />";
		}
	}
	return $html;
}