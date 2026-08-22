<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\NFe\DanfeSimples;
use App\Models\ConfigNota;
use App\Models\Venda;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use NFePHP\DA\NFe\Daevento;

class NfeController extends Controller
{

    public function __construct()
    {
        if (!is_dir(public_path('pdf'))) {
            mkdir(public_path('pdf'), 0777, true);
        }
    }

    public function imprimir($id)
    {
        $venda = Venda::findOrFail($id);
        if (!__valida_objeto($venda)) {
            abort(403);
        }
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
        if (file_exists(public_path('xml_nfe/') . $venda->chave . '.xml')) {
            $xml = file_get_contents(public_path('xml_nfe/') . $venda->chave . '.xml');
            if ($config->logo) {
                $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents(public_path('uploads/configEmitente/') . $config->logo));
            } else {
                $logo = null;
            }
            try {
                $danfe = new Danfe($xml);
                $danfe->setVUnComCasasDec($config->casas_decimais);
                $pdf = $danfe->render($logo);
                header("Content-Disposition: ; filename=DANFE $venda->numero_nfe");
                return response($pdf)
                    ->header('Content-Type', 'application/pdf');
            } catch (InvalidArgumentException $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            echo "Arquivo XML não encontrado!!";
        }
    }

    public function imprimirCorrecao($id)
    {
        $venda = Venda::findOrFail($id);
        if (!__valida_objeto($venda)) {
            abort(403);
        }
        if ($venda->sequencia_cce > 0) {
            $config = ConfigNota::where('empresa_id', request()->empresa_id)
                ->first();
            if (file_exists(public_path('xml_nfe_correcao/') . $venda->chave . '.xml')) {
                $xml = file_get_contents(public_path('xml_nfe_correcao/') . $venda->chave . '.xml');
                if ($config->logo) {
                   $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents(public_path('uploads/configEmitente/') . $config->logo));
                } else {
                    $logo = null;
                }
                $dadosEmitente = $this->getEmitente();
                try {
                    $daevento = new Daevento($xml, $dadosEmitente);
                    $daevento->debugMode(true);
                    $pdf = $daevento->render($logo);

                    header("Content-Disposition: ; filename=CCe $venda->numero_nfe");
                    return response($pdf)
                        ->header('Content-Type', 'application/pdf');
                } catch (InvalidArgumentException $e) {
                    echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
                }
            } else {
                echo "Arquivo XML não encontrado!!";
            }
        } else {
            echo "<center><h1>Este documento não possui evento de correção!<h1></center>";
        }
    }

    public function imprimirCancelamento($id)
    {
        $venda = Venda::findOrFail($id);
        if (!__valida_objeto($venda)) {
            abort(403);
        }
        if ($venda->estado_emissao == 'cancelado') {
            $config = ConfigNota::where('empresa_id', request()->empresa_id)
                ->first();
            if (file_exists(public_path('xml_nfe_cancelada/') . $venda->chave . '.xml')) {
                $xml = file_get_contents(public_path('xml_nfe_cancelada/') . $venda->chave . '.xml');
                if ($config->logo) {
                    $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents(public_path('uploads/configEmitente/') . $config->logo));
                } else {
                    $logo = null;
                }
                $dadosEmitente = $this->getEmitente();
                try {
                    $daevento = new Daevento($xml, $dadosEmitente);
                    $daevento->debugMode(true);
                    $pdf = $daevento->render($logo);
                    header("Content-Disposition: ; filename=Cancelamento $venda->numero_nfe");
                    return response($pdf)
                        ->header('Content-Type', 'application/pdf');
                } catch (InvalidArgumentException $e) {
                    echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
                }
            } else {
                echo "Arquivo XML não encontrado!!";
            }
        } else {
            echo "<center><h1>Este documento não possui evento de cancelamento!<h1></center>";
        }
    }

    private function getEmitente()
    {
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
        return [
            'razao' => $config->razao_social,
            'logradouro' => $config->logradouro,
            'numero' => $config->numero,
            'complemento' => '',
            'bairro' => $config->bairro,
            'CEP' => $config->cep,
            'municipio' => $config->municipio,
            'UF' => $config->UF,
            'telefone' => $config->telefone,
            'email' => ''
        ];
    }

    public function estadoFiscal($id)
    {
        $item = Venda::findOrFail($id);
        return view('nfe.state_fiscal', compact('item'));
    }

    public function updateState(Request $request, $id)
    {
        $item = Venda::findOrFail($id);
        try {
            $item->estado_emissao = $request->estado_emissao;
            if ($request->hasFile('xml')) {
                $xml = simplexml_load_file($request->xml);
                $chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
                $file = $request->xml;
                $file->move(public_path('xml_nfe/'), $chave . '.xml');
                $item->chave = $chave;
                $item->data_emissao = date('Y-m-d H:i:s');
                $item->numero_nfe = (int)$xml->NFe->infNFe->ide->nNF;
            }
            $item->save();
            session()->flash("flash_sucesso", "Estado alterado");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('vendas.index');
    }

    public function baixarXml($id)
    {
        $venda = Venda::findOrFail($id);
        if (valida_objeto($venda)) {
            if (file_exists(public_path('xml_nfe/') . $venda->chave . '.xml')) {

                return response()->download(public_path('xml_nfe/') . $venda->chave . '.xml');
            } else {
                echo "Arquivo XML não encontrado!!";
            }
        } else {
            return redirect('/403');
        }
    }

    public function enviarXml(Request $request)
    {
        $email = $request->email;
        $id = $request->venda_id;
        if (!is_dir(public_path('vendas_temp'))) {
            mkdir(public_path('vendas_temp'), 0777, true);
        }
        $venda = Venda::where('id', $id)
            ->where('empresa_id', request()->empresa_id)
            ->first();
        $config = ConfigNota::where('empresa_id', $request->empresa_id)
            ->first();
        $p = view('vendas.print', compact('config', 'venda'));
        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();
        $domPdf->setPaper("A4");
        $domPdf->render();

        file_put_contents(public_path('vendas_temp/') . 'PEDIDO_' . $venda->id . '.pdf', $domPdf->output());
        if ($venda->chave != "") {
            $this->criarPdfParaEnvio($venda);
        }
        $value = session('user_logged');
        if ($config->usar_email_proprio) {
            $send = $this->enviaEmailPHPMailer($venda, $email, $config);
            if (isset($send['erro'])) {
                return response()->json($send['erro'], 401);
            }
            return "ok";
        } else {
            Mail::send('mail.xml_send', [
                'emissao' => $venda->data_registro, 'nf' => $venda->NfNumero,
                'valor' => $venda->valor_total, 'usuario' => $value['nome'], 'venda' => $venda, 'config' => $config
            ], function ($m) use ($venda, $email) {
                $nomeEmpresa = env('MAIL_NAME');
                $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
                $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
                $emailEnvio = env('MAIL_USERNAME');
                $m->from($emailEnvio, $nomeEmpresa);
                $subject = "Envio de Pedido #$venda->id";
                if ($venda->NfNumero > 0) {
                    $subject .= " | NFe: $venda->NfNumero";
                }
                $m->subject($subject);
                if ($venda->chave != "") {
                    $m->attach(public_path('xml_nfe/') . $venda->chave . '.xml');
                    $m->attach(public_path('pdf/') . 'DANFE.pdf');
                }
                $m->attach(public_path('vendas_temp/') . 'PEDIDO_' . $venda->id . '.pdf');
                $m->to($email);
            });
            return "ok";
        }
    }

    private function criarPdfParaEnvio($venda)
    {

        $xml = file_get_contents(public_path('xml_nfe/') . $venda->chave . '.xml');
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
        if ($config->logo) {
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents(public_path('uploads/configEmitente/') . $config->logo));
        } else {
            $logo = null;
        }
        // $docxml = FilesFolders::readFile($xml);
        try {
            $danfe = new Danfe($xml);
            // $id = $danfe->monta($logo);
            $pdf = $danfe->render($logo);
            header('Content-Type: application/pdf');
            file_put_contents(public_path('pdf/') . 'DANFE.pdf', $pdf);
        } catch (InvalidArgumentException $e) {
            echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
        }
    }

    private function enviaEmailPHPMailer($venda, $email, $config)
    {
        $emailConfig = EmailConfig::where('empresa_id', $this->empresa_id)
            ->first();
        if ($emailConfig == null) {
            return [
                'erro' => 'Primeiramente configure seu email'
            ];
        }
        $public = env('SERVIDOR_WEB') ? 'public/' : '';
        $value = session('user_logged');
        $mail = new PHPMailer(true);
        try {
            if ($emailConfig->smtp_debug) {
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            }
            $mail->isSMTP();
            $mail->Host = $emailConfig->host;
            $mail->SMTPAuth = $emailConfig->smtp_auth;
            $mail->Username = $emailConfig->email;
            $mail->Password = $emailConfig->senha;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $emailConfig->porta;

            $mail->setFrom($emailConfig->email, $emailConfig->nome);
            $mail->addAddress($email);

            $mail->addAttachment($public . 'vendas_temp/PEDIDO_' . $venda->id . '.pdf');

            $mail->addAttachment($public . 'pdf/DANFE.pdf');

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            $mail->Subject = "Envio de Pedido #$venda->id";
            $body = view('mail.xml_send', [
                'emissao' => $venda->data_registro, 'nf' => $venda->NfNumero,
                'valor' => $venda->valor_total, 'usuario' => $value['nome'], 'venda' => $venda, 'config' => $config
            ]);
            $mail->Body = $body;
            $mail->send();
            return [
                'sucesso' => true
            ];
        } catch (Exception $e) {
            return [
                'erro' => $mail->ErrorInfo
            ];
            // echo "Message could; not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
