<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tributacao;
use App\Models\Produto;

class TributoController extends Controller
{
    protected $empresa_id = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->empresa_id = $request->empresa_id;
            $value = session('user_logged');
            if (!$value) {
                return redirect("/login");
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $item = Tributacao::where('empresa_id', $request->empresa_id)
            ->first();
        $regimes = Tributacao::regimes();
        return view('tributos/index', compact('item', 'regimes'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        $item = Tributacao::where('empresa_id', $request->empresa_id)
            ->first();
        if ($item == null) {
            $request->merge([
                'ncm_padrao' => $request->ncm_padrao ?? '',
                'link_nfse' => $request->link_nfse ?? '',
                'perc_ap_cred' => $request->perc_ap_cred ?? 0,
            ]);
            Tributacao::create($request->all());
            session()->flash("flash_sucesso", "Tributação cadastrada!");
        } else {
            if ($item->regime != $request->regime) {
                $this->alteraProdutos($request->regime);
            }
            $request->merge([
                'ncm_padrao' => $request->ncm_padrao ?? '',
                'link_nfse' => $request->link_nfse ?? '',
                'perc_ap_cred' => $request->perc_ap_cred ?? 0,
            ]);
            $item->fill($request->all())->save();
            session()->flash("flash_sucesso", "Tributação atualizada!");
        }
        return redirect()->route('tributos.index');
    }

    private function alteraProdutos($regime)
    {
        $produtos = Produto::where('empresa_id', $this->empresa_id)->get();
        if ($regime == 1) {
            foreach ($produtos as $p) {
                if ($p->CST_CSOSN == '102') {
                    $p->CST_CSOSN = '00';
                }
                if ($p->CST_CSOSN == '500') {
                    $p->CST_CSOSN = '60';
                }
                $p->save();
            }
        } else {
            foreach ($produtos as $p) {
                if ($p->CST_CSOSN == '00') {
                    $p->CST_CSOSN = '102';
                }
                if ($p->CST_CSOSN == '60') {
                    $p->CST_CSOSN = '500';
                }
                $p->save();
            }
        }
    }

    private function _validate(Request $request)
    {
        $rules = [
            'icms' => 'required',
            'pis' => 'required',
            'cofins' => 'required',
            'ipi' => 'required'
        ];
        $messages = [
            'icms.required' => 'O campo ICMS é obrigatório.',
            'pis.required' => 'O campo PIS é obrigatório.',
            'cofins.required' => 'O campo COFINS é obrigatório.',
            'ipi.required' => 'O campo IPI é obrigatório.'
        ];
        $this->validate($request, $rules, $messages);
    }
}
