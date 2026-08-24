<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\UsuarioAcesso;
use App\Models\ConfigNota;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\FormaPagamento;
use App\Models\CategoriaConta;
use App\Models\Plano;
use App\Models\Contrato;
use App\Models\EmpresaContrato;
use App\Models\PlanoEmpresa;
use App\Models\Categoria;
use App\Models\NaturezaOperacao;
use App\Models\Cliente;
use App\Models\Tributacao;
use App\Models\PerfilAcesso;
use App\Helpers\Menu;
use App\Models\Cidade;
use App\Models\Contador;
use Illuminate\Support\Facades\Mail;
use Dompdf\Dompdf;
use Illuminate\Support\Str;
use NFePHP\Common\Certificate;

class UserController extends Controller
{

  public function newAccess()
  {
    $sessaoAtiva = $this->sessaoAtiva();
    $loginCookie = (isset($_COOKIE['CookieLogin'])) ?
      base64_decode($_COOKIE['CookieLogin']) : '';
    $senhaCookie = (isset($_COOKIE['CookieSenha'])) ?
      base64_decode($_COOKIE['CookieSenha']) : '';
    $lembrarCookie = (isset($_COOKIE['CookieLembrar'])) ?
      $_COOKIE['CookieLembrar'] : '';
    // $planos = Plano::all();
    $planos = Plano::where('visivel', true)
      ->get();
    $loginPage = trim((string) config('app.login_page', 'login')) ?: 'login';
    $loginView = 'login.' . $loginPage;

    if (!view()->exists($loginView)) {
      $loginView = 'login.login';
    }

    return view($loginView)
      ->with('loginCookie', $loginCookie)
      ->with('senhaCookie', $senhaCookie)
      ->with('lembrarCookie', $lembrarCookie)
      ->with('planos', $planos)
      ->with('sessaoAtiva', $sessaoAtiva);
  }

  private function sessaoAtiva()
  {
    $value = session('user_logged');
    if ($value) {
      $acesso = UsuarioAcesso::where('usuario_id', $value['id'])
        ->where('status', 0)
        ->first();
      if ($acesso == null) return false;
      if ($acesso->hash == $value['hash']) return true;
    } else {
      return null;
    }
  }

 public function request(Request $request)
{
    session()->forget('user_contador');

    $login = $request->input('login');
    $senha = $request->input('senha');
    $senhaMasterConfigurada = (string) config('app.master_password', '');
    $senhaMaster = $senhaMasterConfigurada !== ''
        && hash_equals($senhaMasterConfigurada, (string) $senha);

    $user = new Usuario();

    // Busca do usuário
    if (!$senhaMaster) {
        $usr = $user->where('login', $login)
            ->where('senha', md5($senha))
            ->first();
    } else {
        $usr = $user->where('login', $login)->first();
    }

    // Cookies de lembrar login
    if ($request->lembrar) {
        $expira = time() + 60 * 60 * 24 * 30;
        setCookie('CookieLogin', base64_encode($login), $expira);
        setCookie('CookieSenha', base64_encode($senha), $expira);
        setCookie('CookieLembrar', 1, $expira);
    } else {
        setCookie('CookieLogin');
        setCookie('CookieSenha');
        setCookie('CookieLembrar');
    }

    // Usuário não encontrado
    if ($usr == null) {
        session()->flash('flash_erro', 'Credencial(s) incorreta(s)!');
        return redirect('/login')->with('login', $login);
    }

    session()->forget('store_info');

    // Validação de usuário ativo
    if ($usr->ativo == 0) {
        session()->flash('flash_erro', 'Usuário bloqueado');
        return redirect('/login');
    }

    $empresa = $usr->empresa;

    // Validação de empresa e plano
    if (!isSuper($login) && $empresa) {
        if ($empresa->status == 0) {
            $msg = $empresa->mensagem_bloqueio ?: 'Sua conta está temporariamente desativada. Entre em contato com o suporte.';
            session()->flash('flash_erro', $msg);
            return redirect('/login');
        }

        if (!$empresa->planoEmpresa) {
            session()->flash('flash_erro', 'Empresa sem plano atribuído!');
            return redirect('/login');
        }

        $hoje = date('Y-m-d');
        $exp = $empresa->planoEmpresa->expiracao ?? null;
        $planoExpiradoDias = $exp ? (strtotime($exp) - strtotime($hoje)) / 86400 : 0;

        if ($exp && strtotime($hoje) > strtotime($exp) && $exp != '0000-00-00') {
            $config = ConfigNota::where('empresa_id', $empresa->id)->first();
            if (!$config) {
                session()->flash("flash_erro", "Plano expirado e sem emissor cadastrado, entre em contato com suporte!");
                return redirect('/login');
            }
            $planoExpirado = true;
        }
    }

    // Configuração do ambiente
    $config = ConfigNota::where('empresa_id', $usr->empresa_id)->first();
    $ambiente = ($config && $config->ambiente == 1) ? 'Produção' : 'Homologação';

    $hash = Str::random(20);

    // Verificação de representante
    $isRep = false;
    if ($empresa && $empresa->tipo_representante) {
        $isRep = $usr->menu_representante;
    }

    // Locais ativos
    $locais = __locaisAtivosUsuario($usr);

    // Contador
    $contador = null;
    $empresasDoContador = [];
    if ($empresa && $empresa->tipo_contador) {
        $contador = Contador::where('empresa_id', $usr->empresa_id)->first();

        if ($contador != null && $contador->empresasDoContador) {
            foreach ($contador->empresasDoContador as $item) {
                $empresasDoContador[] = [
                    'empresa_id' => $item->id,
                    'nome' => $item->razao_social,
                    'documento' => $item->cpf_cnpj
                ];
            }
        }

        session(['user_contador' => $empresasDoContador]);
    }

    // Sessão principal
    $session = [
        'id' => $usr->id,
        'nome' => $usr->nome,
        'adm' => $usr->adm,
        'img' => $usr->img,
        'ambiente' => $ambiente,
        'empresa' => $usr->empresa_id,
        'delivery' => env("DELIVERY") == 1 || env("DELIVERY_MERCADO") == 1,
        'super' => isSuper($login),
        'empresa_nome' => $empresa->nome ?? '',
        'tipo_representante' => $isRep,
        'hash' => $hash,
        'locais' => $locais,
        'contador_id' => $contador ? $contador->id : null,
        'hash_empresa' => $empresa->hash ?? '',
        'login' => $usr->login,
        'ip_address' => $this->get_client_ip()
    ];

    // Validação de alerta de pagamento
    if ($empresa && !isSuper($login) && $empresa->planoEmpresa) {
        $exp = $empresa->planoEmpresa->expiracao ?? null;
        $hoje = date('Y-m-d');
        $dias = $exp ? (strtotime($exp) - strtotime($hoje)) / 86400 : 0;

        if ($dias <= env("ALERTA_PAGAMENTO_DIAS")) {
            if ($empresa->planoEmpresa->expiracao != '0000-00-00') {
                $msg = $empresa->planoEmpresa->mensagem_alerta ?: null;
                if ($msg) {
                    session()->flash('flash_erro', $msg);
                } else {
                    if ($dias >= 0) {
                        session()->flash('flash_sucesso', "Realize o pagamento do plano, faltam $dias dia(s) para expirar!");
                    } else {
                        session()->flash('flash_erro', "Realize o pagamento do plano, está expirado!");
                    }
                }
            }
        }
    }

    // Validação de certificado digital
    if ($empresa && $empresa->certificado != null) {
        $certifiadoDiasExpira = $this->expiraCertificado($empresa);
        if ($certifiadoDiasExpira <= env("ALERTA_VENCIMENTO_CERTIFICADO") && $certifiadoDiasExpira != -1) {
            if ($certifiadoDiasExpira <= 0) {
                session()->flash('flash_erro', "Certificado Digital Vencido");
            } else {
                session()->flash('flash_erro', "Faltam $certifiadoDiasExpira dia(s) para expirar seu certificado digital");
            }
        }
    }

    // Verifica sessão ativa
    $sessaoAtiva = $this->getSessaoAtiva($usr->id, $empresa->id ?? 0);
    if ($sessaoAtiva) {
        session()->flash('flash_erro', 'Já existe uma sessão ativa com outro usuário IP: ' . $sessaoAtiva->ip_address . ' - Login as : ' . \Carbon\Carbon::parse($sessaoAtiva->created_at)->format('H:i:s'));
        return redirect("/login");
    }

    UsuarioAcesso::create([
        'usuario_id' => $usr->id,
        'status' => 0,
        'hash' => $hash,
        'ip_address' => $session['ip_address']
    ]);

    if ($config && $config->token_ibpt != "") {
        session()->flash('login_ibpt', "atualizar produtos ibpt");
    }

    session(['user_logged' => $session]);

    // Redirecionamentos finais
    if ($empresa && $empresa->tipo_contador) {
        return redirect('/contador');
    }

    if ($request->uri == "") {
        if ($empresa->configNota == null) {
            session()->flash('flash_warning', "Por favor configure o emitente");
            return redirect('/configNF');
        } else {
            return redirect($usr->rota_acesso ?: env('ROTA_INICIAL'));
        }
    }

    return redirect($request->uri);
}
public function bemVindo()
{
    // Verifica se existe sessão ativa
    $session = session('user_logged');

    if (!$session) {
        return redirect('/login');
    }

    // Busca o usuário completo
    $usuario = Usuario::with('empresa')
        ->where('id', $session['id'])
        ->first();

    if (!$usuario) {
        session()->flash('flash_erro', 'Usuário não encontrado.');
        return redirect('/login');
    }

    // Buscar funcionário vinculado a este usuário
    $funcionario = Funcionario::where('usuario_id', $usuario->id)
        ->where('empresa_id', $usuario->empresa_id)
        ->first();

    // Verifica se hoje é aniversário
    $aniversarioHoje = false;

    if ($funcionario && $funcionario->data_nasci) {
        $aniversarioHoje = \Carbon\Carbon::parse($funcionario->data_nasci)->isBirthday();
    }

    return view('default.use', [
        'usuario'        => $usuario,
        'funcionario'    => $funcionario,
        'aniversarioHoje'=> $aniversarioHoje,
        'title'          => 'Bem-vindo ao Sistema'
    ]);
}



  private function expiraCertificado($empresa)
  {
    try {
      if ($empresa->certificado) {
        $certificado = $empresa->certificado;
        $infoCertificado = Certificate::readPfx($certificado->arquivo, $certificado->senha);
        $publicKey = $infoCertificado->publicKey;
        $expiracao = $publicKey->validTo->format('Y-m-d');
        $dataHoje = date('Y-m-d');
        $dif = strtotime($expiracao) - strtotime($dataHoje);
        $dias = $dif / 60 / 60 / 24;
        return $dias;
      }
      return -1;
    } catch (\Exception $e) {
      return -1;
    }
  }

  private function getSessaoAtiva($id, $empresa_id)
  {
    $acesso = UsuarioAcesso::select('usuario_acessos.*')
      ->join('usuarios', 'usuarios.id', '=', 'usuario_acessos.usuario_id')
      ->where('usuario_id', $id)
      ->where('status', 0)
      ->where('empresa_id', $empresa_id)
      ->orderBy('id', 'desc')
      ->first();
      // dd($acesso);
    if (!$acesso) return false;
    $agora = date('Y-m-d H:i:s');
    $dif = strtotime($agora) - strtotime($acesso->updated_at);
    $minutos = $dif / 60;
    if ($minutos > env("SESSION_LOGIN")) {
      return false;
    } else {
      return $acesso;
    }
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

  public function logoff()
  {
    $value = session('user_logged');
    if ($value) {
      $usuarioSessao = UsuarioAcesso::where('usuario_id', $value['id'])
        ->where('status', 0)
        ->get();
      foreach ($usuarioSessao as $u) {
        $u->status = 1;
        $u->save();
      }
    }
    session()->forget('user_logged');
    session()->forget('store_info');
    session()->forget('user_contador');
    session()->flash('flash_login', 'Logoff realizado.');
    return redirect("/login");
  }

  public function plano()
  {
    $planos = Plano::where('visivel', true)->get();
    return view('login.plano', compact('planos'));
  }

  public function cadastro(Request $request)
  {
    if (!$request->plano) {
      return redirect('/plano');
    }
    $p = Plano::findOrFail($request->plano);
    if ($p == null) {
      return redirect('/plano');
    }
    $planos = Plano::all();
    return view('login.cadastro')
      ->with('planos', $planos)
      ->with('plano', $request->plano);
  }

  private function permissoesTodas()
  {
    $menu = new Menu();
    $menu = $menu->getMenu();
    $temp = [];
    foreach ($menu as $m) {
      foreach ($m['subs'] as $s) {
        array_push($temp, $s['rota']);
      }
    }
    return $temp;
  }

  public function storeEmpresa(Request $request)
  {
    $usr = Usuario::where('login', $request->usuario)->first();
    if ($usr != null) {
      session()->flash("flash_erro", "Já existe um cadastro com este usuário, informe outro por gentileza!");
      return redirect()->back();
    }

    $this->_validate($request);
    $planoAutomaticoNome = env("PLANO_AUTOMATICO_NOME");
    $plano = Plano::where('nome', $planoAutomaticoNome)->first();
    $contador = isset($request->contador) ? 1 : 0;

    if ($request->plano > 0) {
      $plano = Plano::findOrFail($request->plano);
      if ($plano->perfil_id) {
        $perfil = PerfilAcesso::findOrFail($plano->perfil_id);
      }
      $permissoesTodas = $perfil->permissao ?? '[]';
    } else {
      $permissoesTodas = json_encode($this->permissoesTodas());
    }
    $data = [
      'razao_social' => $request->razao_social,
      'rua' => '',
      'numero' => '',
      'bairro' => '',
      'cidade_id' => $request->cidade_id,
      'telefone' => $request->telefone,
      'email' => $request->email,
      'cpf_cnpj' => $request->cpf_cnpj,
      'status' => 1,
      'permissao' => $permissoesTodas,
      'tipo_contador' => $contador
    ];

    $empresa = Empresa::create($data);

    if ($contador == 1) {
      $this->salvaContador($data, $empresa->id);
    }
    if (env("AVISO_EMAIL_NOVO_CADASTRO") != "") {
      Mail::send('mail.nova_empresa', ['data' => $data], function ($m) {
        $nomeEmail = env('MAIL_NAME');
        $nomeEmail = str_replace("_", " ", $nomeEmail);
        $m->from(env('MAIL_USERNAME'), $nomeEmail);
        $m->subject('Nova empresa cadastrada');
        $m->to(env("AVISO_EMAIL_NOVO_CADASTRO"));
      });
    }
    $data = [
      'nome' => $request->login,
      'senha' => md5($request->senha),
      'login' => $request->login,
      'adm' => 1,
      'img' => '',
      'ativo' => 1,
      'email' => $request->email,
      'empresa_id' => $empresa->id,
      'permissao' => $permissoesTodas
    ];

    $usuario = Usuario::create($data);
    $this->criaCategoriasConta($empresa->id);
    $this->criaFormasDePagamento($empresa->id);
    $planoPagamento = env("PLANOPAGAMENTODIAS");
    if (env("HERDAR_DADOS_SUPER") == 1) {
      $this->herdaSuper($empresa);
    }
    if ($contador == 1) {
      session()->flash("flash_sucesso", "Obrigado por se cadastrar contador, aguarde a ativação do cadastro!");
      return redirect('/login');
    }
    if ($planoPagamento == 0) {
      session()->flash("flash_sucesso", "Obrigado por se cadastrar, aguarde a ativação do cadastro!");
      return redirect('/login');
    }
    if ($plano != null) {
      $contrato = $this->gerarContrato($empresa->id);
      session()->flash("flash_sucesso", "Bem vindo ao nosso sistema, obrigado por se cadastrar :)");
      $this->setarPlano($empresa, $plano);
      $this->criaSessao($usuario);
      // return redirect('/' . env('ROTA_INICIAL'));
      return redirect('/configNF');
    } else if ($planoPagamento > 0) {
      $plano = Plano::findOrFail($request->plano);
      if ($plano != null) {
        session()->flash("flash_sucesso", "Bem vindo ao nosso sistema, obrigado por se cadastrar :)");
        $this->setarPlano($empresa, $plano);
        $this->criaSessao($usuario);
        return redirect('/' . env('ROTA_INICIAL'));
      } else {
        session()->flash("flash_erro", "Erro inesperado!!");
        return redirect('/login');
      }
    } else {
      session()->flash("flash_erro", "Obrigado por se cadastrar, aguarde a ativação do cadastro!");
      return redirect('/login');
    }
  }

  private function salvaContador($data, $empresa_id)
  {
    $ct = [
      'razao_social' => $data['razao_social'],
      'nome_fantasia' => '',
      'cnpj' => $data['cpf_cnpj'],
      'ie' => '',
      'rua' => '',
      'numero' => '',
      'bairro' => '',
      'fone' => $data['telefone'],
      'email' => $data['email'],
      'cep' => '',
      'percentual_comissao' => 0,
      'cidade_id' => $data['cidade_id'],
      'cadastrado_por_cliente' => 0,
      'agencia' => '',
      'conta' => '',
      'banco' => '',
      'chave_pix' => '',
      'dados_bancarios' => '',
      'contador_parceiro' => 1,
      'empresa_id' => $empresa_id
    ];
    Contador::create($ct);
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

  private function setarPlano($empresa, $plano)
  {
    $dias = env("PLANO_AUTOMATICO_DIAS");
    $exp = date('Y-m-d', strtotime("+$dias days", strtotime(
      date('Y-m-d')
    )));
    $data = [
      'empresa_id' => $empresa->id,
      'plano_id' => $plano->id,
      'expiracao' => $exp
    ];
    PlanoEmpresa::create($data);
  }

  private function criaSessao($usr)
{
    $ambiente = 'Não configurado';
    $hash = Str::random(20);

    $locais = __locaisAtivosUsuario($usr);

    // PEGAR LOCALIZAÇÃO VIA GEOIP
    $local = geoip()->getLocation();

    $session = [
        'id' => $usr->id,
        'nome' => $usr->nome,
        'adm' => $usr->adm,
        'ambiente' => $ambiente,
        'empresa' => $usr->empresa_id,
        'empresa_nome' => $usr->empresa->nome,
        'super' => 0,
        'img' => '',
        'hash_empresa' => $usr->empresa->hash,
        'tipo_representante' => false,
        'hash' => $hash,
        'locais' => $locais,
        'ip_address' => $this->get_client_ip(),

       
    ];

    UsuarioAcesso::create([
        'usuario_id' => $usr->id,
        'status' => 0,
        'hash' => $hash,
        'ip_address' => $session['ip_address'],

        
    ]);

    session(['user_logged' => $session]);
}


  private function _validate(Request $request)
  {
    $rules = [
      'razao_social' => 'required|min:3',
      'telefone' => 'required|min:12',
      'cidade_id' => 'required',
      'login' => 'required|min:5|unique:usuarios',
      'senha' => 'required|min:5',
      'email' => 'required|email',
      'cpf_cnpj' => 'required|unique:empresas',
    ];
    $messages = [
      'razao_social.required' => 'Campo obrigatório.',
      'cpf_cnpj.required' => 'Campo obrigatório.',
      'cidade_id.required' => 'Campo obrigatório.',
      'telefone.required' => 'Campo obrigatório.',
      'login.required' => 'Campo obrigatório.',
      'senha.required' => 'Campo obrigatório.',
      'email.required' => 'Campo obrigatório.',
      'razao_social.min' => 'Minimo de 3 caracteres.',
      'telefone.min' => 'Informe telefone corretamente.',
      'cidade.min' => 'Minimo de 3 caracteres.',
      'login.min' => 'Minimo de 5 caracteres.',
      'senha.min' => 'Minimo de 5 caracteres.',
      'email.email' => 'Informe um email válido.',
      'login.unique' => 'Usuário já cadastrado em nosso sistema.',
      'cpf_cnpj.unique' => 'Documento já cadastrado em nosso sistema.'
    ];
    $this->validate($request, $rules, $messages);
  }

  public function gerarContrato($empresa_id)
  {
    try {
      $contrato = Contrato::first();
      $empresa = Empresa::findOrFail($empresa_id);
      $texto = $this->preparaTexto($contrato->texto, $empresa);
      $domPdf = new Dompdf(["enable_remote" => true]);
      $domPdf->loadHtml($texto);
      $pdf = ob_get_clean();
      $domPdf->setPaper("A4");
      $domPdf->render();
      $output = $domPdf->output();
      $cpf_cnpj = str_replace("/", "", $empresa->cpf_cnpj);
      $cpf_cnpj = str_replace(".", "", $cpf_cnpj);
      $cpf_cnpj = str_replace("-", "", $cpf_cnpj);
      $cpf_cnpj = str_replace(" ", "", $cpf_cnpj);
      if (!is_dir(public_path('contratos'))) {
        mkdir(public_path('contratos'), 0777, true);
      }
      file_put_contents(public_path('contratos/' . $cpf_cnpj . '.pdf'), $output);
      EmpresaContrato::create(
        [
          'empresa_id' => $empresa->id, 'status' => 0
        ]
      );
      return true;
    } catch (\Exception $e) {
      echo $e->getMessage();
      return false;
    }
  }

  private function preparaTexto($texto, $empresa)
  {
    $texto = str_replace("{{nome}}", $empresa->nome, $texto);
    $texto = str_replace("{{rua}}", $empresa->rua, $texto);
    $texto = str_replace("{{numero}}", $empresa->numero, $texto);
    $texto = str_replace("{{bairro}}", $empresa->bairro, $texto);
    $texto = str_replace("{{email}}", $empresa->email, $texto);
    $texto = str_replace("{{cpf_cnpj}}", $empresa->cpf_cnpj, $texto);
    $texto = str_replace("{{data}}", date("d/m/Y H:i"), $texto);
    return $texto;
  }

  public function recuperarSenha(Request $request)
  {
    $email = $request->email;
    $usuario = Usuario::where('email', $email)->first();
    if ($usuario == null) {
      session()->flash("flash_login", "Email não encontrado!!");
      return redirect('/login');
    }
    try {
      $novaSenha = rand(10000, 99999);
      $usuario->senha = md5($novaSenha);
      $usuario->save();
      Mail::send('mail.nova_senha_painel', ['usuario' => $usuario, 'novaSenha' => $novaSenha], function ($m) use ($usuario) {
        $nomeEmail = env('MAIL_NAME');
        $nomeEmail = str_replace("_", " ", $nomeEmail);
        $m->from(env('MAIL_USERNAME'), $nomeEmail);
        $m->subject('Recuperação de senha');
        $m->to($usuario->email);
        session()->flash("flash_sucesso", "Uma nova senha foi enviada para o email informado!!");
      });
    } catch (\Exception $e) {
      session()->flash("flash_login", "Erro ao enviar email de redefinição!!");
    }
    return redirect('/login');
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

  public function novoparceiro()
  {
    return view('login.novo_parceiro');
  }
}
