<?php

namespace App\Http\Controllers;

use App\Helpers\Menu;
use App\Models\CategoriaConta;
use App\Models\Contrato;
use App\Models\Empresa;
use App\Models\EmpresaContrato;
use App\Models\FormaPagamento;
use App\Models\PerfilAcesso;
use App\Models\Plano;
use App\Models\Representante;
use App\Models\RepresentanteEmpresa;
use App\Models\Usuario;
use Dompdf\Dompdf;
use Illuminate\Http\Request;

class RepController extends Controller
{
    protected $usuario_id = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {

            $value = session('user_logged');
            if (!$value) {
                return redirect("/login");
            }

            if (!$value['super'] && !$value['tipo_representante']) {
                return redirect('/graficos');
            }

            $this->usuario_id = $value['id'];
            return $next($request);
        });
    }

    public function index()
    {
        $data = Representante::where('usuario_id', $this->usuario_id)->first();
        $planos = Plano::all();
        if ($data == null) {
            session()->flash("flash_erro", "Erro ao encontrar empresas!");
            return redirect()->back();
        }
        return view('rep.index', compact('planos', 'data'));
    }

    public function create()
    {
        $perfis = PerfilAcesso::all();
        $menu = new Menu();
        $menu = $menu->getMenu();
        return view('rep.create', compact('perfis', 'menu'));
    }

    public function store(Request $request)
    {
        $permissao = $this->validaPermissao($request);
        $perfilId = 0;
        if (isset($request->perfil_id) && $request->perfil_id != '0') {
            $tp = json_decode($request->perfil_id);
            $perfilId = $tp->id;
        }
        $this->_validateEmpresa($request);
        $data = [
            'razao_social' => $request->razao_social,
            'nome_fantasia' => $request->nome_fantasia ?? '',
            'rua' => $request->rua,
            'numero' => $request->numero,
            'bairro' => $request->bairro,
            'cidade_id' => $request->cidade_id,
            'telefone' => $request->telefone,
            'email' => $request->email,
            'cpf_cnpj' => $request->cpf_cnpj,
            'perfil_id' => $perfilId,
            'status' => 1,
            'tipo_representante' => $request->tipo_representante ? true : false,
            'permissao' => json_encode($permissao)
        ];
        $empresa = Empresa::create($data);
        if ($empresa) {
            $data = [
                'nome' => $request->usuario,
                'senha' => md5($request->senha),
                'login' => $request->login,
                'adm' => 1,
                'ativo' => 1,
                'permissao' => json_encode($permissao),
                'img' => '',
                'empresa_id' => $empresa->id
            ];
            $usuario = Usuario::create($data);

            if ($request->tipo_representante) {
                Representante::create(
                    [
                        'nome' => $request->nome_usuario,
                        'rua' => $request->rua,
                        'telefone' => $request->telefone,
                        'email' => $request->email,
                        'numero' => $request->numero,
                        'bairro' => $request->bairro,
                        'cidade_id' => $request->cidade_id,
                        'cpf_cnpj' => $request->cpf_cnpj,
                        'comissao' => __convert_value_bd($request->comissao),
                        'usuario_id' => $usuario->id
                    ]
                );
            }
            CategoriaConta::create([
                'nome' => 'Compras',
                'empresa_id' => $empresa->id,
                'tipo' => 'pagar'
            ]);
            CategoriaConta::create([
                'nome' => 'Vendas',
                'empresa_id' => $empresa->id,
                'tipo' => 'receber'
            ]);
            $this->criaFormasDePagamento($empresa->id);
            $contrato = $this->gerarContrato($empresa->id);
            $representante = Representante::where('usuario_id', get_id_user())
                ->first();

            RepresentanteEmpresa::create(
                [
                    'representante_id' => $representante->id,
                    'empresa_id' => $empresa->id
                ]
            );
            session()->flash("flash_sucesso", "Empresa cadastrada!");
            return redirect('/rep');
        }
    }

    private function _validateEmpresa(Request $request)
    {
        $rules = [
            'razao_social' => 'required',
            'cpf_cnpj' => 'required',
            'rua' => 'required',
            'numero' => 'required',
            'bairro' => 'required',
            'cidade_id' => 'required',
            'login' => 'required|unique:usuarios',
            'senha' => 'required',
            'telefone' => 'required',
            'usuario' => 'required',
            'comissao' => $request->tipo_representante ? 'required' : ''
        ];
        $messages = [
            'nome.required' => 'Campo obrigatório.',
            'cpf_cnpj.required' => 'Campo obrigatório.',
            'rua.required' => 'Campo obrigatório.',
            'numero.required' => 'Campo obrigatório.',
            'bairro.required' => 'Campo obrigatório.',
            'cidade_id.required' => 'Campo obrigatório.',
            'login.required' => 'Campo obrigatório.',
            'telefone.required' => 'Campo obrigatório.',
            'senha.required' => 'Campo obrigatório.',
            'usuario.required' => 'Campo obrigatório.',
            'login.unique' => 'Usuário já cadastrado no sistema.',
            'comissao.required' => 'Informe a comissão.',
        ];
        $this->validate($request, $rules, $messages);
    }

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

    public function gerarContrato($empresa_id)
    {
        try {
            $contrato = Contrato::first();
            if ($contrato == null) return false;
            $empresa = Empresa::find($empresa_id);
            $texto = $this->preparaTexto($contrato->texto, $empresa);
            $domPdf = new Dompdf(["enable_remote" => true]);
            $domPdf->loadHtml($texto);
            $pdf = ob_get_clean();
            $domPdf->setPaper("A4");
            $domPdf->render();
            $output = $domPdf->output();
            $cnpj = str_replace("/", "", $empresa->cnpj);
            $cnpj = str_replace(".", "", $cnpj);
            $cnpj = str_replace("-", "", $cnpj);
            $cnpj = str_replace(" ", "", $cnpj);

            if (!is_dir(public_path('contratos'))) {
                mkdir(public_path('contratos'), 0777, true);
            }
            file_put_contents(public_path('contratos/' . $cnpj . '.pdf'), $output);
            EmpresaContrato::create(
                [
                    'empresa_id' => $empresa->id, 'status' => 0
                ]
            );
            return true;
        } catch (\Exception $e) {
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
        $texto = str_replace("{{cidade}}", $empresa->cidade, $texto);
        $texto = str_replace("{{cnpj}}", $empresa->cnpj, $texto);
        $texto = str_replace("{{data}}", date("d/m/Y H:i"), $texto);
        return $texto;
    }
}
