<?php

namespace App\Http\Controllers;

use App\Helpers\StockMove;
use App\Models\Certificado;
use App\Models\ConfigNota;
use App\Models\ContaReceber;
use App\Models\NuvemShopPedido;
use App\Models\PedidoEcommerce;
use App\Models\Venda;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\NFe\DanfeSimples;

class NotaFiscalController extends Controller
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

    public function xmlTemp($id)
    {

        $vendaId = $id;
        $venda = Venda::where('empresa_id', $this->empresa_id)
            ->where('id', $vendaId)
            ->first();

        $config = ConfigNota::where('empresa_id', $this->empresa_id)
            ->first();

        $cnpj = str_replace(".", "", $config->cnpj);
        $cnpj = str_replace("/", "", $cnpj);
        $cnpj = str_replace("-", "", $cnpj);
        $cnpj = str_replace(" ", "", $cnpj);

        $nfe_service = new NFService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => " v8zRciG2x1Y32X8Q_ebzXXHj5yKd6cwJgkdXgeJTak5rwqe4v4yzt0537HmXrY8G",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ]);
        $nfe = $nfe_service->gerarNFe($vendaId);

        if (!isset($nfe['erros_xml'])) {
            $xml = $nfe['xml'];
            return response($xml)
                ->header('Content-Type', 'application/xml');
        } else {
            print_r($nfe['erros_xml']);
        }
    }

    public function gerarNf(Request $request)
    {

        $vendaId = $request->vendaId;
        $venda = Venda::where('empresa_id', $this->empresa_id)
            ->where('id', $vendaId)
            ->first();

        $isFilial = $venda->filial_id;
        if ($venda->filial_id == null) {
            $config = ConfigNota::where('empresa_id', $this->empresa_id)
                ->first();
        } else {
            $config = Filial::findOrFail($venda->filial_id);
            if ($config->arquivo_certificado == null) {
                echo "Necessário o certificado para realizar esta ação!";
                die;
            }
        }

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

        $nfe_service = new NFService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id,
            "is_filial" => $isFilial
        ]);

        if ($venda->estado == 'REJEITADO' || $venda->estado == 'DISPONIVEL') {
            header('Content-type: text/html; charset=UTF-8');

            $nfe = $nfe_service->gerarNFe($vendaId);
            if (!isset($nfe['erros_xml'])) {
                // file_put_contents('xml/teste2.xml', $nfe['xml']);
                // return response()->json($nfe, 200);
                $signed = $nfe_service->sign($nfe['xml']);
                $resultado = $nfe_service->transmitir($signed, $nfe['chave']);

                if (substr($resultado, 0, 4) != 'Erro') {
                    $venda->chave = $nfe['chave'];
                    $venda->path_xml = $nfe['chave'] . '.xml';
                    $venda->estado = 'APROVADO';
                    $venda->nSerie = $config->numero_serie_nfe;
                    $venda->data_emissao = date('Y-m-d H:i:s');

                    $venda->NfNumero = $nfe['nNf'];

                    if ($venda->pedido_ecommerce_id > 0) {
                        $pedido = PedidoEcommerce::find($venda->pedido_ecommerce_id);
                        $pedido->numero_nfe = $nfe['nNf'];
                        $pedido->status_preparacao = 'approved';
                        $pedido->save();
                    }

                    if ($venda->pedido_nuvemshop_id > 0) {
                        $pedido = NuvemShopPedido::find($venda->pedido_nuvemshop_id);
                        $pedido->numero_nfe = $nfe['nNf'];
                        $pedido->save();
                    }
                    $venda->save();

                    $config->ultimo_numero_nfe = $nfe['nNf'];
                    $config->save();

                    $this->criarLog($venda);

                    $this->enviarEmailAutomatico($venda);

                    $file = file_get_contents(public_path('xml_nfe/' . $nfe['chave'] . '.xml'));
                    importaXmlSieg($file, $this->empresa_id);
                } else {
                    $venda->estado = 'REJEITADO';
                    $venda->chave = $nfe['chave'];
                    $venda->save();
                }
                echo json_encode($resultado);
            } else {
                return response()->json($nfe['erros_xml'], 401);
            }
        } else {
            echo json_encode("Apro");
        }
    }

    private function criarLog($objeto, $tipo = 'emissao')
    {
        if (isset(session('user_logged')['log_id'])) {
            $record = [
                'tipo' => $tipo,
                'usuario_log_id' => session('user_logged')['log_id'],
                'tabela' => 'vendas',
                'registro_id' => $objeto->id,
                'empresa_id' => $this->empresa_id
            ];
            __saveLog($record);
        }
    }

    public function inutilizar(Request $request)
    {
        try {

            $config = ConfigNota::where('empresa_id', $this->empresa_id)
                ->first();

            $cnpj = str_replace(".", "", $config->cnpj);
            $cnpj = str_replace("/", "", $cnpj);
            $cnpj = str_replace("-", "", $cnpj);
            $cnpj = str_replace(" ", "", $cnpj);


            $nfe_service = new NFService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->UF,
                "cnpj" => $cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $config->csc,
                "CSCid" => $config->csc_id
            ]);

            // echo json_encode($request->justificativa);
            $result = $nfe_service->inutilizar(
                $config,
                $request->nInicio,
                $request->nFinal,
                $request->justificativa
            );

            echo json_encode($result);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }


    public function consultaCadastro(Request $request)
    {

        $config = ConfigNota::where('empresa_id', $this->empresa_id)
            ->first();

        $certificado = Certificado::where('empresa_id', $this->empresa_id)
            ->first();

        if ($config == null) {
            return response()->json("Configure o emitente para buscar", 403);
        }
        if ($certificado == null) {
            return response()->json("Configure o certificado para buscar", 403);
        }

        $cnpj = str_replace(".", "", $config->cnpj);
        $cnpj = str_replace("/", "", $cnpj);
        $cnpj = str_replace("-", "", $cnpj);
        $cnpj = str_replace(" ", "", $cnpj);
        try {
            $nfe_service = new NFService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->UF,
                "cnpj" => $cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $config->csc,
                "CSCid" => $config->csc_id
            ]);
            $cnpj = $request->cnpj;
            $uf = $request->uf;
            $consulta = $nfe_service->consultaCadastro($cnpj, $uf);
            return $consulta['json'];
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    public function imprimir($id)
    {

        $venda = Venda::where('id', $id)
            ->where('empresa_id', $this->empresa_id)
            ->first();
        if (valida_objeto($venda)) {

            $config = ConfigNota::where('empresa_id', $this->empresa_id)
                ->first();

            if (file_exists($public . 'xml_nfe/' . $venda->chave . '.xml')) {
                $xml = file_get_contents($public . 'xml_nfe/' . $venda->chave . '.xml');
                if ($config->logo) {
                    $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('uploads/configEmitente/' . $config->logo));
                } else {
                    $logo = null;
                }

                try {
                    $danfe = new Danfe($xml);
                    $danfe->setVUnComCasasDec($config->casas_decimais);

                    // $id = $danfe->monta($logo);
                    $pdf = $danfe->render($logo);
                    header("Content-Disposition: ; filename=DANFE $venda->NfNumero");
                    return response($pdf)
                        ->header('Content-Type', 'application/pdf');
                } catch (InvalidArgumentException $e) {
                    echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
                }
            } else {
                echo "Arquivo XML não encontrado!!";
            }
        } else {
            return redirect('/403');
        }
    }

    public function imprimirSimples($id)
    {

        $venda = Venda::where('id', $id)
            ->where('empresa_id', $this->empresa_id)
            ->first();
        if (valida_objeto($venda)) {

            $config = ConfigNota::where('empresa_id', $this->empresa_id)
                ->first();

            $public = env('SERVIDOR_WEB') ? 'public/' : '';
            if (file_exists($public . 'xml_nfe/' . $venda->chave . '.xml')) {
                $xml = file_get_contents($public . 'xml_nfe/' . $venda->chave . '.xml');
                if ($config->logo) {
                    $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($public . 'logos/' . $config->logo));
                } else {
                    $logo = null;
                }

                try {
                    $danfe = new DanfeSimples($xml);
                    // $id = $danfe->monta($logo);
                    $pdf = $danfe->render($logo);

                    return response($pdf)
                        ->header('Content-Type', 'application/pdf');
                } catch (InvalidArgumentException $e) {
                    echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
                }
            } else {
                echo "Arquivo XML não encontrado!!";
            }
        } else {
            return redirect('/403');
        }
    }

    public function escpos($id)
    {
        $venda = Venda::where('id', $id)
            ->where('empresa_id', $this->empresa_id)
            ->first();

        $public = env('SERVIDOR_WEB') ? 'public/' : '';

        $xml = file_get_contents($public . 'xml_nfe/' . $venda->chave . '.xml');
        $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($public . 'imgs/logo.jpg'));

        $connector = new NetworkPrintConnector('127.0.0.1', 9100);
        $danfcepos = new DanfcePos($connector);
    }

    public function imprimirCce($id)
    {
        $venda = Venda::where('id', $id)
            ->where('empresa_id', $this->empresa_id)
            ->first();

        if ($venda->sequencia_cce > 0) {

            $public = env('SERVIDOR_WEB') ? 'public/' : '';
            if (file_exists($public . 'xml_nfe_correcao/' . $venda->chave . '.xml')) {
                $xml = file_get_contents($public . 'xml_nfe_correcao/' . $venda->chave . '.xml');

                $config = ConfigNota::where('empresa_id', $this->empresa_id)
                    ->first();

                if ($config->logo) {
                    $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($public . 'logos/' . $config->logo));
                } else {
                    $logo = null;
                }

                $dadosEmitente = $this->getEmitente($venda->filial);

                try {
                    $daevento = new Daevento($xml, $dadosEmitente);
                    $daevento->debugMode(true);
                    $pdf = $daevento->render($logo);

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

    public function imprimirCancela($id)
    {
        $venda = Venda::where('id', $id)
            ->where('empresa_id', $this->empresa_id)
            ->first();

        if ($venda->estado == 'CANCELADO') {
            try {
                $public = env('SERVIDOR_WEB') ? 'public/' : '';
                if (file_exists($public . 'xml_nfe_cancelada/' . $venda->chave . '.xml')) {
                    $xml = file_get_contents($public . 'xml_nfe_cancelada/' . $venda->chave . '.xml');

                    $config = ConfigNota::where('empresa_id', $this->empresa_id)
                        ->first();

                    if ($config->logo) {
                        $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($public . 'logos/' . $config->logo));
                    } else {
                        $logo = null;
                    }

                    $dadosEmitente = $this->getEmitente($venda->filial);

                    $daevento = new Daevento($xml, $dadosEmitente);
                    $daevento->debugMode(true);
                    $pdf = $daevento->render($logo);
                    // header('Content-Type: application/pdf');
                    // echo $pdf;
                    return response($pdf)
                        ->header('Content-Type', 'application/pdf');
                } else {
                    echo "Arquivo XML não encontrado!!";
                }
            } catch (InvalidArgumentException $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            echo "<center><h1>Este documento não possui evento de cancelamento!<h1></center>";
        }
    }

    private function getEmitente($config = null)
    {
        if ($config == null) {
            $config = ConfigNota::where('empresa_id', $this->empresa_id)
                ->first();
        }
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

    public function cancelar(Request $request)
    {
        $venda = Venda::where('id', $request->id)
            ->first();
        $isFilial = $venda->filial_id;
        if ($venda->filial_id == null) {
            $config = ConfigNota::where('empresa_id', $this->empresa_id)
                ->first();
        } else {
            $config = Filial::findOrFail($venda->filial_id);
        }

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

        $nfe_service = new NFService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id,
            "is_filial" => $isFilial
        ]);

        $nfe = $nfe_service->cancelar($request->id, $request->justificativa);

        if (!isset($nfe['erro'])) {

            $venda = Venda::where('id', $request->id)
                ->where('empresa_id', $this->empresa_id)
                ->first();
            $venda->estado = 'CANCELADO';
            $venda->valor_total = 0;
            $venda->save();

            $this->reverteEstoque($venda->itens);
            //devolve estoque

            $file = file_get_contents(public_path('xml_nfe_cancelada/' . $venda->chave . '.xml'));
            importaXmlSieg($file, $this->empresa_id);

            $this->removerDuplicadas($venda);

            $this->criarLog($venda, 'cancelamento');
            return response()->json($nfe, 200);
        } else {
            return response()->json($nfe['data'], $nfe['status']);
        }
    }

    private function reverteEstoque($itens)
    {
        $stockMove = new StockMove();
        foreach ($itens as $i) {
            if (!empty($i->produto->receita)) {
                //baixa por receita
                $receita = $i->produto->receita;
                foreach ($receita->itens as $rec) {

                    if (!empty($rec->produto->receita)) { // se item da receita for receita
                        $receita2 = $rec->produto->receita;
                        foreach ($receita2->itens as $rec2) {
                            $stockMove->pluStock(
                                $rec2->produto_id,
                                (float) str_replace(",", ".", $i->quantidade) *
                                    ($rec2->quantidade / $receita2->rendimento)
                            );
                        }
                    } else {

                        $stockMove->pluStock(
                            $rec->produto_id,
                            (float) str_replace(",", ".", $i->quantidade) *
                                ($rec->quantidade / $receita->rendimento)
                        );
                    }
                }
            } else {
                $stockMove->pluStock(
                    $i->produto_id,
                    (float) str_replace(",", ".", $i->quantidade)
                );
            }
        }
    }

    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    private function removerDuplicadas($venda)
    {
        foreach ($venda->duplicatas as $dp) {
            $c = ContaReceber::where('id', $dp->id)
                ->delete();
        }
    }

    public function cartaCorrecao(Request $request)
    {

        $venda = Venda::where('id', $request->id)
            ->first();

        $config = ConfigNota::where('empresa_id', $this->empresa_id)
            ->first();

        $isFilial = $venda->filial_id;
        if ($venda->filial_id != null) {
            $config = Filial::findOrFail($venda->filial_id);
        }

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

        $nfe_service = new NFService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id,
            "is_filial" => $isFilial
        ]);

        $nfe = $nfe_service->cartaCorrecao($request->id, $request->correcao);
        echo json_encode($nfe);
    }


    public function consultar(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $this->empresa_id)
            ->first();

        $cnpj = str_replace(".", "", $config->cnpj);
        $cnpj = str_replace("/", "", $cnpj);
        $cnpj = str_replace("-", "", $cnpj);
        $cnpj = str_replace(" ", "", $cnpj);
        $nfe_service = new NFService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ]);
        $c = $nfe_service->consultar($request->id);
        echo json_encode($c);
    }

    public function consultar_cliente($id)
    {
        $venda = Venda::where('id', $id)
            ->where('empresa_id', $this->empresa_id)
            ->first();
        echo json_encode($venda->cliente);
    }

    public function enviarXml(Request $request)
    {
        $email = $request->email;
        $id = $request->id;

        if (!is_dir(public_path('vendas_temp'))) {
            mkdir(public_path('vendas_temp'), 0777, true);
        }

        $venda = Venda::where('id', $id)
            ->where('empresa_id', $this->empresa_id)
            ->first();

        $config = ConfigNota::where('empresa_id', $this->empresa_id)
            ->first();

        $p = view('vendas/print', compact('config', 'venda'));

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4");
        $domPdf->render();

        $public = env('SERVIDOR_WEB') ? 'public/' : '';

        file_put_contents($public . 'vendas_temp/PEDIDO_' . $venda->id . '.pdf', $domPdf->output());

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

                $public = env('SERVIDOR_WEB') ? 'public/' : '';
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
                    $m->attach($public . 'xml_nfe/' . $venda->chave . '.xml');
                    $m->attach($public . 'pdf/DANFE.pdf');
                }
                $m->attach($public . 'vendas_temp/PEDIDO_' . $venda->id . '.pdf');

                $m->to($email);
            });
            return "ok";
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

    private function criarPdfParaEnvio($venda)
    {
        $public = env('SERVIDOR_WEB') ? 'public/' : '';
        $xml = file_get_contents($public . 'xml_nfe/' . $venda->chave . '.xml');

        $config = ConfigNota::where('empresa_id', $this->empresa_id)
            ->first();

        if ($config->logo) {
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($public . 'logos/' . $config->logo));
        } else {
            $logo = null;
        }
        // $docxml = FilesFolders::readFile($xml);

        try {
            $danfe = new Danfe($xml);
            // $id = $danfe->monta($logo);
            $pdf = $danfe->render($logo);
            header('Content-Type: application/pdf');
            file_put_contents($public . 'pdf/DANFE.pdf', $pdf);
        } catch (InvalidArgumentException $e) {
            echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
        }
    }

    private function enviarEmailAutomatico($venda)
    {
        $escritorio = EscritorioContabil::where('empresa_id', $this->empresa_id)
            ->first();

        if ($escritorio != null && $escritorio->envio_automatico_xml_contador) {
            $email = $escritorio->email;
            Mail::send('mail.xml_automatico', ['descricao' => 'Envio de NF-e'], function ($m) use ($email, $venda) {
                $nomeEmpresa = env('MAIL_NAME');
                $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
                $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
                $emailEnvio = env('MAIL_USERNAME');

                $m->from($emailEnvio, $nomeEmpresa);
                $m->subject('Envio de XML Automático');

                $m->attach(public_path('xml_nfe/' . $venda->chave . '.xml'));
                $m->to($email);
            });
        }
    }

    public function testeVenda($id)
    {
        $venda = Venda::find($id);

        $file = file_get_contents(public_path('xml_nfe/' . $venda->chave . '.xml'));
        $msg = importaXmlSieg($file, $this->empresa_id);
        echo $msg;
    }

    // public function importaXmlSieg($venda){
    // 	$escritorio = EscritorioContabil::
    // 	where('empresa_id', $this->empresa_id)
    // 	->first();
    // 	if($escritorio != null && $escritorio->token_sieg != ""){
    // 		$url = "https://api.sieg.com/aws/api-xml.ashx";

    // 		$curl = curl_init();

    // 		$headers = [];

    // 		$data = file_get_contents(public_path('xml_nfe/'.$venda->chave.'.xml'));
    // 		curl_setopt($curl, CURLOPT_URL, $url . "?apikey=".$escritorio->token_sieg."&email=".$escritorio->email);
    // 		curl_setopt($curl, CURLOPT_POST, true);
    // 		curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
    // 		curl_setopt($curl,CURLOPT_RETURNTRANSFER, true );
    // 		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    // 		curl_setopt($curl, CURLOPT_HEADER, false);
    // 		$xml = json_decode(curl_exec($curl));
    // 		if($xml->Message == 'Importado com sucesso'){
    // 			return $xml->Message;
    // 		}
    // 		return false;
    // 	}else{
    // 		return false;
    // 	}
    // }

    public function filtro(Request $request)
    {
        try {
            $data = Venda::where('empresa_id', $this->empresa_id)
                ->select('vendas.*', \DB::raw("DATE_FORMAT(vendas.created_at, '%d/%m/%Y %H:%i') as data_registro"))
                ->with('cliente')
                ->where('estado', 'APROVADO');

            if ($request->data1 && $request->data2) {
                $data->whereBetween('data_emissao', [
                    $this->parseDate($request->data1),
                    $this->parseDate($request->data2, true)
                ]);
            } else {
                $data->whereBetween('data_emissao', [
                    $this->menos30Dias(),
                    date('Y-m-d H:i')
                ]);
            }
            $data = $data->get();

            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    private function menos30Dias()
    {
        return date('d/m/Y', strtotime("-30 days", strtotime(str_replace(
            "/",
            "-",
            date('Y-m-d')
        ))));
    }

    private function parseDate($date, $plusDay = false)
    {
        if ($plusDay == false)
            return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
        else
            return date('Y-m-d', strtotime("+1 day", strtotime(str_replace("/", "-", $date))));
    }
}
