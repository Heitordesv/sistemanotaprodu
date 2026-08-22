<?php

namespace App\Http\Controllers;

use App\Helpers\Menu;
use App\Models\Empresa;
use App\Models\PerfilAcesso;
use App\Models\Plano;
use Illuminate\Http\Request;

class PerfilAcessoController extends Controller
{
    public function index(Request $request)
    {
        $data = PerfilAcesso::all();
        return view('perfil_acesso.index', compact('data'));
    }

    public function create()
    {
        $permissoesAtivas = [];
        $menu = new Menu();
        $menu = $menu->getMenu();
        for ($i = 0; $i < sizeof($menu); $i++) {
            $temp = false;
            foreach ($menu[$i]['subs'] as $s) {
                if (in_array($s['rota'], $permissoesAtivas)) {
                    $temp = true;
                }
            }
            $menu[$i]['ativo'] = $temp;
        }
        return view('perfil_acesso.create', compact('permissoesAtivas', 'menu'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        $permissao = $this->validaPermissao($request);
        try {
            $data = [
                'nome' => $request->nome,
                'permissao' => json_encode($permissao)
            ];
            $result = PerfilAcesso::create($data);
            session()->flash("flash_sucesso", "Cadastrado com sucesso!");
        } catch (\Exception $e) {
            echo $e->getMessage();
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('perfilAcesso.index');
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

    public function edit($id)
    {
        $menu = new Menu();
        $menu = $menu->getMenu();
        $item = PerfilAcesso::findOrFail($id);
        $permissoesAtivas = $item->permissao;
        $permissoesAtivas = json_decode($permissoesAtivas);
        return view('perfil_acesso.edit', compact('item', 'permissoesAtivas', 'menu'));
    }


    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = PerfilAcesso::findOrFail($id);
        $permissao = $this->validaPermissao($request);
        try {
            $request->merge([
                'nome' => $request->nome,
                'permissao' => json_encode($permissao)
            ]);
            $item->fill($request->all())->save();
            session()->flash("flash_sucesso", "Perfil atualizado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('perfilAcesso.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'nome' => 'required|max:50'
        ];
        $messages = [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.max' => '50 caracteres maximos permitidos.'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        try{
            $item = PerfilAcesso::findOrFail($id);
            $plano = Plano::where('perfil_id', $id)->first();
            $empresa = Empresa::where('perfil_id', $id)->first();
            if ($plano != null) {
                session()->flash("flash_erro", "Perfil está vinculado a um plano");
                return redirect()->back();
            }
            if ($empresa != null) {
                session()->flash("flash_erro", "Perfil está vinculado a uma empresa");
                return redirect()->back();
            }
            $item->delete();
            session()->flash("flash_sucesso", "Perfil deletado com sucesso!");
        }catch(\Exception $e){
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('perfilAcesso.index');
    }
}
