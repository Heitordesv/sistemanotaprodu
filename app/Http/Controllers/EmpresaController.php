<?php

namespace App\Http\Controllers;

use App\Helpers\Menu;
use App\Models\AberturaCaixa;
use App\Models\CategoriaConta;
use App\Models\Categoria;
use App\Models\Certificado;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\Tributacao;
use App\Models\Compra;
use App\Models\ConfigCaixa;
use App\Models\ConfigNota;
use App\Models\WSEmpresa;
use App\Models\Contador;
use App\Models\Contrato;
use App\Models\Cte;
use App\Models\Devolucao;
use App\Models\Empresa;
use App\Models\EmpresaContrato;
use App\Models\EscritorioContabil;
use App\Models\FormaPagamento;
use App\Models\Mdfe;
use App\Models\NaturezaOperacao;
use App\Models\Orcamento;
use App\Models\PerfilAcesso;
use App\Models\Plano;
use App\Models\PlanoEmpresa;
use App\Models\Produto;
use App\Models\Representante;
use App\Models\Servico;
use App\Models\Usuario;
use App\Models\UsuarioAcesso;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Illuminate\Http\Request;
use App\Utils\UploadUtil;
use NFePHP\Common\Certificate;
use Illuminate\Support\Str;


class EmpresaController extends Controller
{
    protected $util;

    public function ajuste(){
        $data = ConfigNota::all();
        foreach($data as $item){

            if($item->cidade_id == null){
                $cidade = Cidade::where('nome', $item->municipio)->where('uf', $item->UF)->first();
                $item->cidade_id = $cidade->id;
                $item->save();
            }
        }
    }

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function index(Request $request)
    {
        $planos = Plano::all();
        $data = Empresa::when(!empty($request->nome), function ($q) use ($request) {
            return $q->where(function ($quer) use ($request) {
                return $quer->where('nome', 'LIKE', "%$request->nome%");
            });
        })
        ->orderBy('id', 'desc')
        ->paginate(env("PAGINACAO"));
        return view('empresas.index', compact('data', 'planos'));
    }

  public function create()
{
    $perfis = PerfilAcesso::all();
    $cidades = Cidade::all();
    $contadores = Contador::pluck('razao_social', 'id'); // aqui

    $menu = new Menu();
    $menu = $menu->getMenu();

    return view('empresas.create', compact('cidades', 'perfis', 'menu', 'contadores'));
}
    private function _validate(Request $request)
    {
        $rules = [
          'login' => 'required|min:5|unique:usuarios',
          'email' => 'required|email|unique:usuarios',
      ];
      $messages = [
          'login.required' => 'Campo obrigatório.',
          'email.email' => 'Informe um email válido.',
          'login.unique' => 'Usuário já cadastrado em nosso sistema.',
          'email.unique' => 'Usuário já cadastrado em nosso sistema.'
      ];
      $this->validate($request, $rules, $messages);
  }

public function store(Request $request)
{
    $permissao = $this->validaPermissao($request);
    $perfilId = 0;
    $this->_validate($request);
    
    if(isset($request->perfil_id) && $request->perfil_id != '0'){
        $tp = json_decode($request->perfil_id);
        $perfilId = $tp->id;
    }
    try {
        $request->merge([
            'contador_id' => $request->contador_id ?? '',
            'permissao' => json_encode($permissao),
            'perfil_id' => $perfilId,
            'email' => $request->email ?? ''
        ]);
        $empresa = Empresa::create($request->all());
        $usuario = Usuario::create([
            'nome' => $request->usuario,
            'senha' => md5($request->senha),
            'login' => $request->login,
            'adm' => 1,
            'ativo' => 1,
            'empresa_id' => $empresa->id,
            'permissao' => json_encode($permissao),
            'permite_desconto' => 1,
            'menu_representante' => 1
        ]);

        if ($request->tipo_representante) {
            Representante::create([
                'nome' => $request->usuario,
                'rua' => $request->rua,
                'telefone' => $request->telefone,
                'email' => $request->email ?? '',
                'numero' => $request->numero,
                'bairro' => $request->bairro,
                'cidade_id' => $request->cidade_id,
                'cpf_cnpj' => $request->cpf_cnpj,
                'comissao' => __convert_value_bd($request->comissao),
                'usuario_id' => $usuario->id,
                'acesso_xml' => $request->acesso_xml ? true : false,
                'bloquear_empresa' => $request->bloquear_empresa ? true : false,
                'limite_cadastros' => $request->limite_cadastros ?? 0
            ]);
        }

        $this->criaConfigCaixa($usuario);
        $this->criaCategoriasConta($empresa->id);
        $this->criaFormasDePagamento($empresa->id);

        if ($empresa->escritorio == null && $request->contador_id > 0) {
            $this->insertEscritorio($request->contador_id, $empresa->id);
        }

        if(env("HERDAR_DADOS_SUPER") == 1){
            // adiciona categoria, tributação, natureza e produto do super
            $this->herdaSuper($empresa);
        }

        // Buscar configuração da empresa para envio de WhatsApp
$config = ConfigNota::where('empresa_id', 1)->first();

        // Buscar o cliente (caso tenha sido cadastrado)
        $cliente = Cliente::where('empresa_id', $empresa->id)->first();

        // Verifica se a configuração está completa antes de tentar enviar WhatsApp
        if ($config && $config->DeviceToken && $config->Bearer && $cliente && $cliente->celular) {
            $numero = preg_replace('/\D/', '', $cliente->celular); // Remove tudo que não for número
            $numero = '55' . $numero; // Adiciona o código do Brasil

            $this->enviarWhatsApp([
                'number' => $numero,
                'text' => "Olá {$cliente->razao_social}, seu cadastro foi realizado com sucesso! Bem-vindo! {$config->nome_fantasia}"
            ]);
        }

        session()->flash("flash_sucesso", "Empresa cadastrada com sucesso!");
    } catch (\Exception $e) {
        session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
        __saveLogError($e, $request->empresa_id);
    }
    return redirect()->route('empresas.index');
}


private function enviarWhatsApp($dados)
{
$config = ConfigNota::where('empresa_id', 1)->first();

  // Verifica se as configurações necessárias existem antes de tentar enviar a mensagem
    if (!$config || empty($config->DeviceToken) || empty($config->Bearer)) {
        // Se não houver configuração, não tenta enviar a mensagem
        return false; 
    }


    $data = json_encode([
        'number' => $dados['number'],
        'text' => $dados['text'],
    ]);

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/whatsapp/sendText",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "DeviceToken: {$config->DeviceToken}",
            "Authorization: Bearer {$config->Bearer}"
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    return json_decode($response, true);
}
private function criaConfigCaixa($usuario)
{
    $data = [
        'finalizar' => '',
        'reiniciar' => '',
        'editar_desconto' => '',
        'editar_acrescimo' => '',
        'editar_observacao' => '',
        'setar_valor_recebido' => '',
        'forma_pagamento_dinheiro' => '',
        'forma_pagamento_debito' => '',
        'forma_pagamento_credito' => '',
        'setar_quantidade' => '',
        'forma_pagamento_pix' => '',
        'setar_leitor' => '',
        'finalizar_fiscal' => '',
        'finalizar_nao_fiscal' => '',
        'valor_recebido_automatico' => 0,
        'modelo_pdv' => 2,
        'balanca_valor_peso' => 0,
        'balanca_digito_verificador' => 5,
        'valor_recebido_automatico' => 0,
        'impressora_modelo' => 80,
        'usuario_id' => $usuario->id,
        'mercadopago_public_key' => '',
        'mercadopago_access_token' => '',
        'tipos_pagamento' => '["01","02","03","04","05","06","10","11","12","13","14","15","16","17","90","99"]',
        'tipo_pagamento_padrao' => '01'
    ];
    ConfigCaixa::create($data);
}

private function criaCategoriasConta($empresa_id)
{
    CategoriaConta::create([
        'nome' => 'Compras',
        'empresa_id' => $empresa_id,
        'tipo' => 'pagar'
    ]);
    CategoriaConta::create([
        'nome' => 'Vendas',
        'empresa_id' => $empresa_id,
        'tipo' => 'receber'
    ]);
}

private function criaFormasDePagamento($empresa_id)
{
    FormaPagamento::create([
        'empresa_id' => $empresa_id,
        'nome' => 'A vista',
        'chave' => 'a_vista',
        'taxa' => 0,
        'status' => 1,
        'prazo_dias' => 0,
        'tipo_taxa' => 'perc'
    ]);
    FormaPagamento::create([
        'empresa_id' => $empresa_id,
        'nome' => '30 dias',
        'chave' => '30_dias',
        'taxa' => 0,
        'status' => 1,
        'prazo_dias' => 30,
        'tipo_taxa' => 'perc'
    ]);
    FormaPagamento::create([
        'empresa_id' => $empresa_id,
        'nome' => 'Personalizado',
        'chave' => 'personalizado',
        'taxa' => 0,
        'status' => 1,
        'prazo_dias' => 0,
        'tipo_taxa' => 'perc'
    ]);
    FormaPagamento::create([
        'empresa_id' => $empresa_id,
        'nome' => 'Conta crediario',
        'chave' => 'conta_crediario',
        'taxa' => 0,
        'status' => 1,
        'prazo_dias' => 0,
        'tipo_taxa' => 'perc'
    ]);
}

public function edit($id)
{

    return view('empresas.edit');
}
public function detalhes($id)
{
    if (env("APP_ENV") == "demo") {
        session()->flash("flash_erro", "Aplicação em modo demonstração");
        return redirect()->route('empresas.index');
    }

    $empresa = Empresa::findOrFail($id);
    $cidades = Cidade::all();
    $hoje = date('Y-m-d');
    $planoExpirado = false;
    $permissoesAtivas = $empresa->permissao;
    $permissoesAtivas = $permissoesAtivas ? json_decode($permissoesAtivas) : [];

    if ($empresa->planoEmpresa) {
        $exp = $empresa->planoEmpresa->expiracao;
        if (strtotime($hoje) > strtotime($exp)) {
            $planoExpirado = true;
        }
    }

    $menu = new Menu();
    $menu = $menu->getMenu();
    
    // Simplificando a lógica de contadores para o componente Select
    // Buscamos ID como chave e RAZAO_SOCIAL como valor exibido
        $contadores = Contador::pluck('razao_social', 'id'); // aqui

    $perfis = PerfilAcesso::all();
    $certificado = $empresa->certificado;

    return view('empresas.show', compact(
        'empresa',
        'perfis',
        'menu',
        'cidades',
        'contadores',
        'certificado',
        'planoExpirado',
        'permissoesAtivas'
    ));
}

public function alterarSenha($id)
{
    $empresa = Empresa::findOrFail($id);
    return view('empresas.alterar_senha', compact('empresa'));
}

public function alterarSenhaPost(Request $request)
{
    $empresa = Empresa::findOrFail($request->id);

    $senha = $request->senha;

    foreach ($empresa->usuarios as $u) {
        $u->senha = md5($senha);
        $u->save();
    }
    session()->flash("flash_sucesso", "Senhas alteradas!");
    return redirect()->route('empresas.index');
}

public function online()
{
    $empresas = Empresa::orderBy('id', 'desc')->get();
    $minutos = env("MINUTOS_ONLINE");
    $online = [];
    foreach ($empresas as $e) {
        $ult = $e->ultimoLogin2($e->id);
        if ($ult != null) {
            $strValidade = strtotime($ult->updated_at);
            $strHoje = strtotime(date('Y-m-d H:i:s'));
            $dif = $strHoje - $strValidade;
            $dif = $dif / 60;
            if ((int) $dif <= $minutos && $e->usuarios[0]->login != env("USERMASTER")) {
                array_push($online, $e);
            }
        }
    }
    return view('empresas.online', compact('empresas'));
}

public function update(Request $request, $id)
{
    $empresa = Empresa::findOrFail($id);
    $permissao = $this->validaPermissao($request);

    $perfilId = 0;

    if (isset($request->perfil_id) && $request->perfil_id != '0') {
        $tp = json_decode($request->perfil_id);
        $perfilId = $tp->id;
    }

    try {
        $request->merge([
            'contador_id' => $request->contador_id ?? '',
            'permissao' => json_encode($permissao),
            'perfil_id' => $perfilId
        ]);
        $empresa->fill($request->all())->save();
        if ($request->contador_id > 0) {
            $this->insertEscritorio($request->contador_id, $empresa->id);
        }
        $this->percorreUsuariosEmpresa($empresa, $permissao);
        session()->flash("flash_sucesso", "Cadastro Atualizado");
    } catch (\Exception $e) {
        session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
    }
    return redirect()->back();
}

    // private function validaPermissao($request)
    // {
    //     $menu = new Menu();
    //     $arr = $request->all();
    //     $arr = (array) ($arr);
    //     $menu = $menu->getMenu();
    //     $temp = [];
    //     foreach ($menu as $m) {
    //         foreach ($m['subs'] as $s) {
    //             // $nome = str_replace("", "_", $s['rota']);
    //             // echo $s['rota'] . "<br>";
    //             if (isset($arr[$s['rota']])) {
    //                 array_push($temp, $s['rota']);
    //             }
    //             if (strlen($s['rota']) > 60) {
    //                 $rt = str_replace(".", "_", $s['rota']);
    //                 // $rt = str_replace(":", "_", $s['rota']);
    //                 // echo $rt . "<br>";
    //                 foreach ($arr as $key => $a) {
    //                     if ($key == $rt) {
    //                         array_push($temp, $rt);
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     return $temp;
    // }

private function validaPermissao($request)
{
    $menu = new Menu();
    $arr = $request->all();
    $arr = (array) ($arr);
    $menu = $menu->getMenu();
    $temp = [];

    foreach ($menu as $m) {
        foreach ($m['subs'] as $s) {
            if (isset($arr[str_replace(".", "_", $s['rota'])])) {
                array_push($temp, $s['rota']);
            }
        }
    }
    return $temp;
}

private function insertEscritorio($contador_id, $empresa_id)
{
    $contador = Contador::findOrFail($contador_id);
    EscritorioContabil::create([
        'razao_social' => $contador->razao_social,
        'nome_fantasia' => $contador->nome_fantasia,
        'cpf_cnpj' => $contador->cpf_cnpj,
        'ie' => $contador->ie,
        'fone' => $contador->fone,
        'logradouro' => $contador->logradouro,
        'numero' => $contador->numero,
        'bairro' => $contador->bairro,
        'cep' => $contador->cep,
        'email' => $contador->email,
        'envio_automatico_xml_contador' => false,
        'token_sieg' => '',
        'empresa_id' => $empresa_id
    ]);
}

public function percorreUsuariosEmpresa($empresa, $permissao)
{
    foreach ($empresa->usuarios as $e) {
        $temp = [];
        $permissaoAntiga = json_decode($e->permissao);
        foreach ($permissao as $p) {
                // if(in_array($p, $permissaoAntiga)){
            array_push($temp, $p);
                // }
        }
            // print_r($temp);
            // die();
        $e->permissao = json_encode($temp);
        $e->save();
    }
}

public function setarPlano($id)
{
    $empresa = Empresa::findOrFail($id);
    $planos = Plano::all();
    return view('empresas.setar_plano', compact('empresa', 'planos'));
}

    // public function setarPlanoPost(Request $request)
    // {
    //     $empresa = Empresa::find($request->empresa);
    //     $empresa = $empresa;
    //     $plano = $empresa->planoEmpresa;
    //     if ($plano != null) {
    //         $plano->delete();
    //     }

    //     $plano = $request->plano;
    //     $plano = Plano::find($request->plano);
    //     $perfil = $planos->perfil_id;
    //     $perfil_id = PerfilAcesso::find($perfil);
    //     $permissao = $perfil_id->permissao;

    //     $empresa->permissao = $permissao;
    //     $empresa->save();

    //     $empresa = $empresa->id;

    //     if ($request->indeterminado) {
    //         $expiracao = '0000-00-00';
    //     } else {
    //         $expiracao = $request->date_expiracao;
    //     }
    //     PlanoEmpresa::create([
    //         'empresa_id' => $request->empresa,
    //         'plano_id' => $plano,
    //         'expiracao' => $expiracao,
    //         'valor' => __convert_value_bd($request->valor),
    //         'mensagem_alerta' => $request->mensagem ?? ''
    //     ]);
    //     session()->flash("flash_sucesso", "Plano atribuido!");
    //     return redirect()->route('empresas.index');
    // }

public function setarPlanoPost(Request $request)
{
    $empresa = Empresa::find($request->empresa);
    $plano = $empresa->planoEmpresa;
    if ($plano != null) {
        $plano->delete();
    }

    $plano = $request->plano;
    if ($request->indeterminado) {
        $expiracao = '0000-00-00';
    } else {
        $expiracao = $request->date_expiracao;
    }

    $data = [
        'empresa_id' => $empresa->id,
        'plano_id' => $plano,
        'expiracao' => $expiracao,
        'valor' => __convert_value_bd($request->valor),
        'mensagem_alerta' => $request->mensagem ?? ''
    ];

    PlanoEmpresa::create($data);
    session()->flash("flash_sucesso", "Plano atribuido!");

    return redirect()->route('empresas.detalhes', [$empresa->id]);
}

public function configEmitente($empresa_id)
{
    $empresa = Empresa::find($empresa_id);
    $item = $empresa->configNota;
    try {
        $naturezas = NaturezaOperacao::where('empresa_id', $empresa_id)
        ->get();
        $tiposPagamento = ConfigNota::tiposPagamento();
        $tiposFrete = ConfigNota::tiposFrete();
        $listaCSTCSOSN = ConfigNota::listaCST();
        $listaCSTPISCOFINS = ConfigNota::listaCST_PIS_COFINS();
        $listaCSTIPI = ConfigNota::listaCST_IPI();
        $certificado = ConfigNota::where('empresa_id', $empresa_id)
        ->first();
        $cUF = ConfigNota::estados();
        $infoCertificado = null;
        if ($certificado != null) {
            $infoCertificado = $this->getInfoCertificado($certificado);
        }
        $soapDesativado = !extension_loaded('soap');
        $cidades = Cidade::all();
        if ($item != null) {
            $item->graficos_dash = $item->graficos_dash ? json_decode($item->graficos_dash) : [];
        }
        return view('empresas.configEmitente', compact(
            'item',
            'empresa',
            'naturezas',
            'tiposPagamento',
            'tiposFrete',
            'infoCertificado',
            'soapDesativado',
            'listaCSTCSOSN',
            'listaCSTPISCOFINS',
            'listaCSTIPI',
            'cUF',
            'cidades',
            'certificado',
        ));
    } catch (\Exception $e) {
        echo $e->getMessage();
        echo "<br><a href='/configNF/deleteCertificado'>Remover Certificado</a>";
    }
}

public function download($id)
{
    $config = ConfigNota::where('empresa_id', $id)
    ->first();
    if ($config == null) {
        session()->flash("flash_erro", "Nenhum certificado!");
        return redirect()->back();
    }
    $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
    $files = array_diff(scandir(public_path('certificados')), array('.', '..'));
    $certificados = [];
    foreach ($files as $file) {
        $name_file = explode(".", $file);
        if ($name_file[0] == $cnpj) {
            array_push($certificados, $file);
        }
    }
    if (sizeof($certificados) > 1) {
        return view('empresas.certificados', compact('certificados'));
    }
    try {
        if (file_exists(public_path('certificados/') . $cnpj . '.p12')) {
            return response()->download(public_path('certificados/') . $cnpj . '.p12');
        } elseif (file_exists(public_path('certificados/') . $cnpj . '.pfx')) {
            return response()->download(public_path('certificados/') . $cnpj . '.pfx');
        } elseif (file_exists(public_path('certificados/') . $cnpj . '.bin')) {
            return response()->download(public_path('certificados/') . $cnpj . '.bin');
        } else {
            echo "Nenhum arquivo encontrado!";
        }
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}

public function arquivosXml($empresa_id)
{
    $empresa = Empresa::find($empresa_id);
    return view('empresas.enviarXml', compact('empresa'));
}

public function filtroXml(Request $request)
{
    $empresa = Empresa::find($request->empresa_filtro_id);
    $cnpj = $this->getCnpjEmpresa($empresa);
    $xml = Venda::whereBetween('updated_at', [
        $start_date = $request->start_date,
        $end_date = $request->end_date
    ])
    ->where('empresa_id', $request->empresa_filtro_id);
    $estado = $request->estado;
    if ($estado == 1) {
        $xml->where('estado_emissao', 'aprovado');
    } else {
        $xml->where('estado_emissao', 'cancelado');
    }
    $xml = $xml->get();
    $public = env('SERVIDOR_WEB') ? 'public/' : '';
    try {
        if (count($xml) > 0) {
                // $zip_file = 'zips/xml_'.$cnpj.'.zip';
            $zip_file = public_path('zips') . '/xml_' . $cnpj . '.zip';
            $zip = new \ZipArchive();
            $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            if ($estado == 1) {
                foreach ($xml as $x) {
                    if (file_exists($public . 'xml_nfe/' . $x->chave . '.xml'))
                        $zip->addFile($public . 'xml_nfe/' . $x->chave . '.xml', $x->path_xml);
                }
            } else {
                foreach ($xml as $x) {
                    if (file_exists($public . 'xml_nfe_cancelada/' . $x->chave . '.xml'))
                        $zip->addFile($public . 'xml_nfe_cancelada/' . $x->chave . '.xml', $x->path_xml);
                }
            }
            $zip->close();
        }
    } catch (\Exception $e) {
    }
    try {
        $xmlCte = Cte::whereBetween('updated_at', [
            $start_date = $request->start_date,
            $end_date = $request->end_date
        ])
        ->where('empresa_id', $request->empresa_filtro_id);
        $estado = $request->estado;
        if ($estado == 1) {
            $xmlCte->where('estado_emissao', 'aprovado');
        } else {
            $xmlCte->where('estado_emissao', 'cancelado');
        }
        $xmlCte = $xmlCte->get();
        if (count($xmlCte) > 0) {
                // $zip_file = $public.'xmlcte.zip';
                // $zip_file = 'zips/xmlcte_'.$cnpj.'.zip';
            $zip_file = public_path('zips') . '/xmlcte_' . $cnpj . '.zip';
            $zip = new \ZipArchive();
            $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            if ($estado == 1) {
                foreach ($xmlCte as $x) {
                    if (file_exists($public . 'xml_cte/' . $x->chave . '.xml'))
                        $zip->addFile($public . 'xml_cte/' . $x->chave . '.xml', $x->path_xml);
                }
            } else {
                foreach ($xmlCte as $x) {
                    if (file_exists($public . 'xml_cte_cancelada/' . $x->chave . '.xml'))
                        $zip->addFile($public . 'xml_cte_cancelada/' . $x->chave . '.xml', $x->path_xml);
                }
            }
            $zip->close();
        }
    } catch (\Exception $e) {
    }
    try {
        $xmlNfce = VendaCaixa::whereBetween('updated_at', [
            $start_date = $request->start_date,
            $end_date = $request->end_date
        ])
        ->where('empresa_id', $request->empresa_filtro_id);
        if ($estado == 1) {
            $xmlNfce->where('estado_emissao', 'aprovado');
        } else {
            $xmlNfce->where('estado_emissao', 'cancelado');
        }
        $xmlNfce = $xmlNfce->get();
        if (sizeof($xmlNfce) > 0) {
                // $zip_file = 'zips/xmlnfce_'.$cnpj.'.zip';
            $zip_file = public_path('zips') . '/xmlnfce_' . $cnpj . '.zip';
            $zip = new \ZipArchive();
            $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            if ($estado == 1) {
                foreach ($xmlNfce as $x) {
                    if (file_exists($public . 'xml_nfce/' . $x->chave . '.xml'))
                        $zip->addFile($public . 'xml_nfce/' . $x->chave . '.xml', $x->chave . '.xml');
                }
            } else {
                foreach ($xmlNfce as $x) {
                    if (file_exists($public . 'xml_nfce_cancelada/' . $x->chave . '.xml'))
                        $zip->addFile($public . 'xml_nfce_cancelada/' . $x->chave . '.xml', $x->chave . '.xml');
                }
            }
            $zip->close();
        }
    } catch (\Exception $e) {
    }
    $xmlMdfe = Mdfe::whereBetween('updated_at', [
        $start_date = $request->start_date,
        $end_date = $request->end_date
    ])
    ->where('empresa_id', $request->empresa_filtro_id);
    $estado = $request->estado;
    if ($estado == 1) {
        $xmlMdfe->where('estado_emissao', 'aprovado');
    } else {
        $xmlMdfe->where('estado_emissao', 'cancelado');
    }
    $xmlMdfe = $xmlMdfe->get();
    if (count($xmlMdfe) > 0) {
        try {
                // $zip_file = 'zips/xmlmdfe_'.$cnpj.'.zip';
            $zip_file = public_path('zips') . '/xmlmdfe_' . $cnpj . '.zip';
            $zip = new \ZipArchive();
            $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            if ($estado == 1) {
                foreach ($xmlMdfe as $x) {
                    if (file_exists($public . 'xml_mdfe/' . $x->chave . '.xml')) {
                        $zip->addFile($public . 'xml_mdfe/' . $x->chave . '.xml', $x->chave . '.xml');
                    }
                }
            } else {
                foreach ($xmlMdfe as $x) {
                    if (file_exists($public . 'xml_mdfe_cancelada/' . $x->chave . '.xml')) {
                        $zip->addFile($public . 'xml_mdfe_cancelada/' . $x->chave . '.xml', $x->chave . '.xml');
                    }
                }
            }
            $zip->close();
        } catch (\Exception $e) {
                // echo $e->getMessage();
        }
    }
        //nfe entrada
    $xmlEntrada = Compra::whereBetween('updated_at', [
        $start_date = $request->start_date,
        $end_date = $request->end_date
    ])
    ->where('empresa_id', $request->empresa_filtro_id);
    if ($estado == 1) {
        $xmlEntrada->where('estado', 'aprovado');
    } else {
        $xmlEntrada->where('estado', 'cancelado');
    }
    $xmlEntrada = $xmlEntrada->get();
    if (count($xmlEntrada) > 0) {
        try {
                // $zip_file = 'zips/xmlmdfe_'.$cnpj.'.zip';
            $zip_file = public_path('zips') . '/xmlEntrada_' . $cnpj . '.zip';
            $zip = new \ZipArchive();
            $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            if ($estado == 1) {
                foreach ($xmlEntrada as $x) {
                    if (file_exists($public . 'xml_entrada_emitida/' . $x->chave . '.xml')) {
                        $zip->addFile($public . 'xml_entrada_emitida/' . $x->chave . '.xml', $x->chave . '.xml');
                    }
                }
            } else {
                foreach ($xmlEntrada as $x) {
                    if (file_exists($public . 'xml_nfe_entrada_cancelada/' . $x->chave . '.xml')) {
                        $zip->addFile($public . 'xml_nfe_entrada_cancelada/' . $x->chave . '.xml', $x->chave . '.xml');
                    }
                }
            }
            $zip->close();
        } catch (\Exception $e) {
                // echo $e->getMessage();
        }
    }
    $xmlDevolucao = Devolucao::whereBetween('updated_at', [
        $start_date = $request->start_date,
        $end_date = $request->end_date
    ])
    ->where('empresa_id', $request->empresa_filtro_id);
        // 1- Aprovado, 3 - Cancelado
    if ($estado == 1) {
        $xmlDevolucao->where('estado_emissao', 'aprovado');
    } else {
        $xmlDevolucao->where('estado_emissao', 'cancelado');
    }
    $xmlDevolucao = $xmlDevolucao->get();
    if (count($xmlDevolucao) > 0) {
        try {
                // $zip_file = $public.'xmlmdfe.zip';
                // $zip_file = 'zips/xmlmdfe_'.$cnpj.'.zip';
            $zip_file = public_path('zips') . '/xmlDevolucao_' . $cnpj . '.zip';
            $zip = new \ZipArchive();
            $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            if ($estado == 1) {
                foreach ($xmlDevolucao as $x) {
                    if (file_exists($public . 'xml_devolucao/' . $x->chave_gerada . '.xml')) {
                        $zip->addFile($public . 'xml_devolucao/' . $x->chave_gerada . '.xml', $x->chave_gerada . '.xml');
                    }
                }
            } else {
                foreach ($xmlDevolucao as $x) {
                    if (file_exists($public . 'xml_devolucao_cancelada/' . $x->chave_gerada . '.xml')) {
                        $zip->addFile($public . 'xml_devolucao_cancelada/' . $x->chave_gerada . '.xml', $x->chave_gerada . '.xml');
                    }
                }
            }
            $zip->close();
        } catch (\Exception $e) {
                // echo $e->getMessage();
        }
    }
    $start_date = str_replace("/", "-", $request->data_inicial);
    $end_date = str_replace("/", "-", $request->data_final);
    return view('empresas.enviarXml', compact(
        'xml',
        'xmlNfce',
        'xmlCte',
        'xmlMdfe',
        'empresa',
        'estado',
        'xmlEntrada',
        'xmlDevolucao',
        'start_date',
        'end_date',
    ));
}

private function getCnpjEmpresa($empresa)
{
    $empresa = Empresa::find($empresa->id);
    $cpf_cnpj = $empresa->configNota->cpf_cnpj;
    $cpf_cnpj = str_replace(".", "", $cpf_cnpj);
    $cpf_cnpj = str_replace("/", "", $cpf_cnpj);
    $cpf_cnpj = str_replace("-", "", $cpf_cnpj);
    $cpf_cnpj = str_replace(" ", "", $cpf_cnpj);
    return $cpf_cnpj;
}


private function getInfoCertificado($item)
{
    try {
        $infoCertificado = Certificate::readPfx($item->arquivo, $item->senha);
        $publicKey = $infoCertificado->publicKey;
        $inicio =  $publicKey->validFrom->format('Y-m-d H:i:s');
        $expiracao =  $publicKey->validTo->format('Y-m-d H:i:s');
        return [
            'serial' => $publicKey->serialNumber,
            'inicio' => \Carbon\Carbon::parse($inicio)->format('d-m-Y H:i'),
            'expiracao' => \Carbon\Carbon::parse($expiracao)->format('d-m-Y H:i'),
            'id' => $publicKey->commonName
        ];
    } catch (\Exception $e) {
        return [];
    }
}

public function storeConfig(Request $request)
{
    $empresa = Empresa::findOrFail($request->empresaId);
    $item = $empresa->configNota;
    try {
        $file_name = '';
        if ($request->hasFile('image')) {
            $file_name = $this->util->uploadImage($request, '/configEmitente');
        }
        if ($request->id == 0) {
            $request->merge([
                'empresa_id' => $request->empresaId,
                'pais' => $request->pais ?? '',
                'cUF' => $request->cUF ?? '',
                'campo_obs_pedido' => $request->campo_obs_pedido ?? '',
                'campo_obs_nfe' => $request->campo_obs_nfe ?? '',
                'certificado_a3' => $request->certificado_a3 ?? 0,
                'inscricao_municipal' => $request->inscricao_minicipal ?? '',
                'complemento' => $request->complemento ?? '',
                'token_ibpt' => $request->token_ibpt ?? '',
                 'user_id' => $request->user_id ?? '',
                'percentual_lucro_padrao' => $request->percentual_lucro_padrao ?? 0,
                'validade_orcamento' => $request->validade_orcamento ?? 0,
                'senha_remover' => $request->senha_remover ?? '',
                'email' => $request->email ?? '',
                'casas_decimais' => $request->casas_decimais ?? 2,
                'logo' => $file_name,
                'nat_op_padrao' => $request->nat_op_padrao ?? 0,
                'sobrescrita_csonn_consumidor_final' => $request->sobrescrita_csonn_consumidor_final ?? ''
            ]);
            if ($request->hasFile('certificado')) {
                $file = $request->file('certificado');
                $temp = file_get_contents($file);
                $extensao = $file->getClientOriginalExtension();
                $request->merge([
                    'arquivo' => $temp
                ]);
                $cnpj = preg_replace('/[^0-9]/', '', $request->cnpj);

                $fileName = "$cnpj.$extensao";
                if (!is_dir(public_path('certificados'))) {
                    mkdir(public_path('certificados'), 0777, true);
                }
                if (env("CERTIFICADO_ARQUIVO") == 1) {
                    $file->move(public_path('certificados'), $fileName);
                }
            }
            ConfigNota::create($request->all());
            session()->flash("flash_sucesso", "Configuração realizada com sucesso pelo super ADM!");
        } else {
            $config = ConfigNota::where('empresa_id', $request->empresaId)
            ->first();
            $config->razao_social = $request->razao_social;
            $config->nome_fantasia = $request->nome_fantasia;
            $config->user_id = $request->user_id;
            $config->cnpj = $request->cnpj;
            $config->ie = $request->ie;
            $config->logradouro = $request->logradouro;
            $config->numero = $request->numero;
            $config->bairro = $request->bairro;
            $config->cep = $request->cep;
            $config->fone = $request->fone;
            $config->email = $request->email;
            $config->CST_CSOSN_padrao = $request->CST_CSOSN_padrao;
            $config->CST_COFINS_padrao = $request->CST_COFINS_padrao;
            $config->CST_PIS_padrao = $request->CST_PIS_padrao;
            $config->CST_IPI_padrao = $request->CST_IPI_padrao;
            $config->ultimo_numero_nfe = $request->ultimo_numero_nfe;
            $config->ultimo_numero_nfce = $request->ultimo_numero_nfce;
            $config->ultimo_numero_cte = $request->ultimo_numero_cte;
            $config->ultimo_numero_mdfe = $request->ultimo_numero_mdfe;
            $config->numero_serie_nfe = $request->numero_serie_nfe;
            $config->numero_serie_nfce = $request->numero_serie_nfce;
            $config->numero_serie_cte = $request->numero_serie_cte;
            $config->numero_serie_mdfe = $request->numero_serie_mdfe;
            $config->csc = $request->csc;
            $config->csc_id = $request->csc_id;
            $config->campo_obs_nfe = $request->campo_obs_nfe ?? '';
            $config->casas_decimais = $request->casas_decimais;
            $config->complemento = $request->complemento;
            $config->ambiente = $request->ambiente;
            $config->logo = $request->file_name;

            $file_name = '';
            if ($request->hasFile('image')) {
                $this->util->unlinkImage($config, '/configEmitente');
                $file_name = $this->util->uploadImage($request, '/configEmitente');
            };
            if ($request->hasFile('certificado')) {
                $file = $request->file('certificado');
                $temp = file_get_contents($file);
                $extensao = $file->getClientOriginalExtension();
                $request->merge([
                    'arquivo' => $temp
                ]);
                $cnpj = preg_replace('/[^0-9]/', '', $item->cnpj);
                $fileName = "$cnpj.$extensao";
                if (!is_dir(public_path('certificados'))) {
                    mkdir(public_path('certificados'), 0777, true);
                }
                if (env("CERTIFICADO_ARQUIVO") == 1) {
                    $file->move(public_path('certificados'), $fileName);
                }
            }

            $config->save();
            session()->flash("flash_sucesso", "Alteração realizada com sucesso pelo super ADM!");
        }
    } catch (\Exception $e) {
            // echo $e->getMessage() . '<br>' . $e->getLine();
            // die;
        session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
    }
    return redirect()->back();
}

public function login($id)
{
    $empresa = Empresa::findOrFail($id);
    $hash = Str::random(20);
    $usr = $empresa->usuarioFirst;
    $usrLog = Usuario::findOrFail(get_id_user());
    $config = $empresa->configNota;
    $ambiente = 'Não configurado';
    if ($config != null) {
        $ambiente = $config->ambiente == 1 ? 'Produção' : 'Homologação';
    }

    $locais = __locaisAtivosUsuario($usr);

    $session = [
        'id' => $usr->id,
        'nome' => $usr->nome,
        'adm' => $usr->adm,
        'img' => '',
        'ambiente' => $ambiente,
        'empresa' => $empresa->id,
        'delivery' => env("DELIVERY") == 1 || env("DELIVERY_MERCADO") == 1 ? true : false,
        'super' => 0,
        'empresa_nome' => $usr->empresa->nome,
        'hash_empresa' => $usr->empresa->hash,
        'tipo_representante' => 0,
        'hash' => $hash,
        'log_id' => $usrLog->id,
        'locais' => $locais,
        'log_nome' => $usrLog->nome,
        'ip_address' => $this->get_client_ip()
    ];
    $value = session('user_logged');
    if ($value) {
        $usuarioSessao = UsuarioAcesso::where('usuario_id', $value['id'])
        ->where('status', 0)
        ->get();
        foreach ($usuarioSessao as $u) {
            $u->status = 1;
            $u->save();
        }
        $usuarioSessao = UsuarioAcesso::where('usuario_id', $usr->id)
        ->where('status', 0)
        ->get();
        foreach ($usuarioSessao as $u) {
            $u->status = 1;
            $u->save();
        }
    }
    UsuarioAcesso::create(
        [
            'usuario_id' => $usr->id,
            'status' => 0,
            'hash' => $hash,
            'ip_address' => $session['ip_address']
        ]
    );
    session()->forget('user_logged');
    session()->forget('store_info');
    session(['user_logged' => $session]);
    session()->flash("flash_sucesso", "Troca de usuário realizada!");
    return redirect('/graficos');
}

private function get_client_ip()
{
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if (isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

public function destroy($id)
{
    // Deleta vendas
    Venda::where('empresa_id', $id)->delete();

    // Deleta compras e itens
    $compras = Compra::where('empresa_id', $id)->get();
    foreach ($compras as $c) {
        foreach ($c->itens as $i) {
            $i->delete();
        }
        $c->delete();
    }

    // Deleta itens de compras diretamente
    \App\Models\ItemCompra::whereIn('compra_id', function($query) use ($id) {
        $query->select('id')->from('compras')->where('empresa_id', $id);
    })->delete();

    // Deleta registros de caixa
    VendaCaixa::where('empresa_id', $id)->delete();
    AberturaCaixa::where('empresa_id', $id)->delete();

    // Deleta usuários e dependências
    $usuarios = Usuario::where('empresa_id', $id)->get();
    foreach ($usuarios as $u) {
        // Deleta configurações e dependências do usuário
        ConfigCaixa::where('usuario_id', $u->id)->delete();

        // Deleta temas associados
        \App\Models\TemaUsuario::where('usuario_id', $u->id)->delete();

        // Deleta usuário
        $u->delete();
    }

    // Deleta orçamentos
    Orcamento::where('empresa_id', $id)->delete();

    // Deleta produtos e serviços
    Produto::where('empresa_id', $id)->delete();
    Servico::where('empresa_id', $id)->delete();

    // Finalmente, deleta a empresa
    $empresa = Empresa::findOrFail($id);
    $empresa->delete();

    session()->flash("flash_sucesso", "Empresa removida!");
    return redirect()->route('empresas.index');
}

public function alterarStatus($id)
{
    $item = Empresa::findOrFail($id);
        // dd($item->status);
    try {
        $item->status = !$item->status;
        $item->save();
        session()->flash('flash_sucesso', 'Empresa bloqueada');

        if($item->status){
            session()->flash('flash_sucesso', 'Empresa desbloqueada');
        }
    } catch (\Exception $e) {
        session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
    }
    return redirect()->route('empresas.index');
}

public function buscar(Request $request)
{
    $data = Empresa::where('contador_id', 0)
    ->where('tipo_contador', 0)
    ->orderBy('razao_social', 'asc')->get();
    return response()->json($data, 200);
}

private function herdaSuper($novaEmpresa)
{
    $usuario = Usuario::where('login', getSuper())
    ->first();
    if ($usuario) {
        $empresaId = $usuario->empresa->id;
        $categorias = Categoria::where('empresa_id', $empresaId)
        ->get();
        foreach ($categorias as $c) {
            $c->empresa_id = $novaEmpresa->id;
            $cat = $c->toArray();
            unset($cat['id']);
            unset($cat['created_at']);
            unset($cat['updated_at']);
            Categoria::create($cat);
        }
        $naturezas = NaturezaOperacao::where('empresa_id', $empresaId)
        ->get();
        foreach ($naturezas as $c) {
            $c->empresa_id = $novaEmpresa->id;
            $nat = $c->toArray();
            unset($nat['id']);
            unset($nat['created_at']);
            unset($nat['updated_at']);
            NaturezaOperacao::create($nat);
        }
        $tributacao = Tributacao::where('empresa_id', $empresaId)
        ->first();
        if ($tributacao != null) {
            $tributacao->empresa_id = $novaEmpresa->id;
            $trib = $tributacao->toArray();
            unset($trib['id']);
            unset($trib['created_at']);
            unset($trib['updated_at']);
            Tributacao::create($nat);
        }
        $clientes = Cliente::where('empresa_id', $empresaId)
        ->get();
        foreach ($clientes as $c) {
            $c->empresa_id = $novaEmpresa->id;
            $cli = $c->toArray();
            unset($cli['id']);
            unset($cli['created_at']);
            unset($cli['updated_at']);
            Cliente::create($cli);
        }
    }
}
}
