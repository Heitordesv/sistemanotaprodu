<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use App\Models\ConfigNota;
use App\Models\Contrato;
use App\Models\Empresa;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use NFePHP\Common\Certificate;

class AssinarContratoController extends Controller
{
    public function index(Request $request)
    {
        $contrato = Contrato::first();
        $empresa = Empresa::find($request->empresa_id);
        $texto = $this->preparaTexto($contrato->texto, $empresa);
        return view('assinar_contrato.index', compact('texto'));
    }

    private function preparaTexto($texto, $empresa)
    {
        $texto = str_replace("{{raza_social}}", $empresa->razao_social, $texto);
        $texto = str_replace("{{rua}}", $empresa->rua, $texto);
        $texto = str_replace("{{numero}}", $empresa->numero, $texto);
        $texto = str_replace("{{bairro}}", $empresa->bairro, $texto);
        $texto = str_replace("{{email}}", $empresa->email, $texto);
        $texto = str_replace("{{cidade}}", $empresa->cidade, $texto);
        $texto = str_replace("{{cnpj}}", $empresa->cnpj, $texto);
        $texto = str_replace("{{data}}", date("d/m/Y H:i"), $texto);
        return $texto;
    }

    public function assinar(Request $request)
    {
        if (!$request->aceito) {
            session()->flash("flash_erro", "Aceite os termos!");
            return redirect()->back();
        }
        $config = ConfigNota::where('empresa_id', $request->empresa_id)
            ->first();
        $empresa = Empresa::find($request->empresa_id);
        if ($config == null) {
            session()->flash("flash_erro", "Configure o emitente!");
            return redirect()->back();
        }
        $certificado = Certificado::where('empresa_id', $request->empresa_id)
            ->first();
        $contrato = Contrato::first();
        if ($certificado == null && $contrato->usar_certificado) {
            session()->flash("flash_erro", "Configure o certificado!");
            return redirect()->back();
        }
        try {
            $cnpj = preg_replace('/[^0-9]/', '', $empresa->cnpj);
            if ($contrato->usar_certificado) {
                $cert = Certificate::readPfx($certificado->arquivo, $certificado->senha);
                $publicKey = $cert->publicKey;

                // return view('land_page', compact('publicKey'));

                $pdf = new Dompdf(["enable_remote" => true]);

                $output = $pdf->output();

                $info = array(
                    'Name' => $empresa->nome,
                    'Date' => date("Y.m.d H:i:s"),
                    'Reason' => 'Assinatura de contrato',
                    'ContactInfo' => $empresa->telefone,
                );

                $pageCount = file_put_contents(public_path('contratos/' . $cnpj . '.pdf'), $output);

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $pdf->setSignature($cert->__toString(), $cert->privateKey, $certificado->senha, '', 2, $info);
                    $pdf->fontDir('helvetica', '', 11);
                    $pdf->AddPage();

                    if ($pageNo == $pageCount) {
                        $len = strlen($publicKey->commonName);
                        $name1 = substr($publicKey->commonName, 0, 25);
                        $name2 = substr($publicKey->commonName, 25, $len);

                        $pdf->SetFont('helvetica', '', 12);
                        $pdf->Text(30, 255, $name1);
                        $pdf->Text(30, 260, $name2);

                        // $pdf->SetFont('helvetica', '', 12);
                        // $pdf->Text(10, 260, $publicKey->commonName);
                        $pdf->SetFont('helvetica', '', 8);
                        $pdf->Text(100, 255, "Assinado de  forma digital por:");
                        $pdf->Text(100, 258, $publicKey->commonName);
                        $pdf->Text(100, 261, "Dados: " . date('d/m/y H:i:s'));
                    }

                    $tplId = $pdf->importPage($pageNo);
                    // $pdf->setSignatureAppearance(180, 60, 15, 15);
                    // $pdf->addEmptySignatureAppearance(180, 80, 15, 15);
                    $pdf->useTemplate($tplId, 0, 0);
                }

                $pdf->Output(public_path('contratos/' . $cnpj . '.pdf'), 'F');

                // return response($pdf)
                // ->header('Content-Type', 'application/pdf');


                $empresa = Empresa::find($this->empresa_id);
                $contrato = $empresa->contrato;
                $contrato->status = 1;
                $contrato->save();
                session()->flash("mensagem_sucesso", "Contrato assinado!");
                return redirect('/graficos');
            } else {
                $pdf = new Dompdf(["enable_remote" => true]);
                $output = $pdf->output();

                $info = array(
                    'Name' => $empresa->nome,
                    'Date' => date("Y.m.d H:i:s"),
                    'Reason' => 'Assinatura de contrato',
                    'ContactInfo' => $empresa->telefone,
                );

                
                $pageCount = file_put_contents(public_path('contratos/' . $cnpj . '.pdf'), $output);


                // $pdf->SetFont('helvetica', '', 12);
                // $pdf->AddPage();

                // $pdf->loadHtml(10, 255, "Contrato assinado $cnpj");
                // $pdf->Text(10, 265, "Data da assinatura: " . date('d/m/y H:i:s'));
                // $tplId = $pdf->importPage(1);

                // $pdf->setSignatureAppearance(180, 60, 15, 15);
                // $pdf->addEmptySignatureAppearance(180, 80, 15, 15);
                // $pdf->useTemplate($tplId, 0, 0);

                // $pdf->Output(public_path('contratos/' . $cnpj . '.pdf'), 'F');
                $empresa = Empresa::find($request->empresa_id);
                $contrato = $empresa->contrato;
                $contrato->status = 1;
                $contrato->save();
                session()->flash("mensagem_sucesso", "Contrato assinado!");
                return redirect('/graficos');
            }
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            $this->gerarContrato($this->empresa_id);
            return redirect()->back();
        }
    }
}
