<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Empresa;
use App\Models\EmpresaContrato;
use App\Models\ConfigNota;

use Dompdf\Dompdf;
use Illuminate\Http\Request;

class ContratoController extends Controller
{
public function index(Request $request)
{
    $item = ConfigNota::where('empresa_id', $request->empresa_id)->first();
    return view('contrato.create', compact('item'));
}


    public function store(Request $request)
    {
        $item = Contrato::first();
        try {
            if ($item == null) {
                Contrato::create($request->all());
                session()->flash("flash_sucesso", "Contrato salvo com sucesso!");
            } else {
                $item->fill($request->all())->save();
                session()->flash("flash_sucesso", "Contrato editado com sucesso!");
            }
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('contrato.index');
    }

    public function gerarContrato($empresa_id)
    {
        try {
            $contrato = Contrato::first();
            if ($contrato == null) {
                session()->flash("flash_erro", "Cadastre o contrato!!");
                return redirect()->route('contrato.index');
            }

            if ($contrato == null) return false;
            $empresa = Empresa::find($empresa_id);
            $texto = $this->preparaTexto($contrato->texto, $empresa);
            $domPdf = new Dompdf(["enable_remote" => true]);
            $domPdf->loadHtml($texto);
            $pdf = ob_get_clean();
            $domPdf->setPaper("A4");
            $domPdf->render();
            $output = $domPdf->output();
            $cpf_cnpj = preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj);

            if (!is_dir(public_path('contratos'))) {
                mkdir(public_path('contratos'), 0777, true);
            }
            file_put_contents(public_path('contratos/' . $cpf_cnpj . '.pdf'), $output);
            EmpresaContrato::create(
                [
                    'empresa_id' => $empresa->id,
                    'status' => 0,
                    'cpf_cnpj' => $empresa->cpf_cnpj
                ]
            );
            session()->flash("flash_sucesso", "Contrato criado!");
            return redirect()->back();
        } catch (\Exception $e) {
            echo $e->getMessage();
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

    public function imprimir($empresa_id)
    {
        $empresa = Empresa::find($empresa_id);
        $cpf_cnpj = preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj);
        $pdf = file_get_contents(public_path('contratos/') . $cpf_cnpj . '.pdf');
        if ($pdf) {
            header("Content-Disposition: ; filename=Contrato.pdf");
            return response($pdf)
                ->header('Content-Type', 'application/pdf');
        } else {
            session()->flash("flash_erro", "Contrato não encontrado!");
            return redirect()->back();
        }
    }

    public function download($empresa_id)
    {
        $empresa = Empresa::find($empresa_id);
        $cpf_cnpj = preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj);
        return response()->download(public_path('contratos/' . $cpf_cnpj . '.pdf'));
    }
}
