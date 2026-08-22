<?php

namespace App\Services;
use NFePHP\CTe\Make;
use NFePHP\CTe\Tools;
use NFePHP\CTe\Complements;
use NFePHP\CTe\Common\Standardize;
use NFePHP\Common\Certificate;
use NFePHP\Common\Soap\SoapCurl;
use App\Models\ConfigNota;
use App\Models\CteOs;
use App\Models\Cte;
use App\Models\Certificado;

error_reporting(E_ALL);
ini_set('display_errors', 'On');
class CTeOsService{

	private $config; 
	private $tools;
	protected $empresa_id = null;

	public function __construct($config, $emitente){
		
		$this->empresa_id = $emitente->empresa_id;
		$this->config = $config;
		$this->tools = new Tools(json_encode($config), Certificate::readPfx($emitente->arquivo, $emitente->senha));
		$this->tools->model(67);
	}

	public function gerarCTe($cteEmit){

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		// $cteEmit = Cte::where('id', $id)
		// ->first();

		$cte = new Make();
		$dhEmi = date("Y-m-d\TH:i:sP");
		$numeroCTE = $config->ultimo_numero_cte++;

		$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

		$chave = $this->montaChave(
			$config->cUF, date('y', strtotime($dhEmi)), date('m', strtotime($dhEmi)), $cnpj, $this->tools->model(), '1', $numeroCTE, '1', '10'
		);
		
		$infCte = new \stdClass();
		$infCte->Id = "";
		$infCte->versao = "3.00";
		$cte->taginfCTe($infCte);

		$cDV = substr($chave, -1);      
		$ide = new \stdClass();
		
		$ide->cUF = ConfigNota::getCodUF($config->cidade->uf); 
		$ide->cCT = rand(11111111, 99999999); 
		$ide->CFOP = $cteEmit->emitente->cidade->uf != $cteEmit->tomador_cli->cidade->uf ?
		$cteEmit->natureza->CFOP_saida_inter_estadual : $cteEmit->natureza->CFOP_saida_estadual;
		$ide->natOp = $cteEmit->natureza->natureza;
		$ide->mod = '67'; 
		$ide->serie = $config->numero_serie_cte; 
		$nCte = $ide->nCT = $numeroCTE; 
		$ide->dhEmi = $dhEmi; 
		$ide->tpImp = '1'; 
		$ide->tpEmis = '1'; 
		$ide->cDV = $cDV; 
		$ide->tpAmb = (int)$config->ambiente; 
		$ide->tpCTe = '0'; 
		$ide->procEmi = '0'; 
		$ide->verProc = '3.0'; 

		$ide->cMunEnv = $cteEmit->municipioEnvio->codigo; 
		$ide->xMunEnv = strtoupper($cteEmit->municipioEnvio->nome); 
		$ide->UFEnv = $cteEmit->municipioEnvio->uf; 

		$ide->modal = $cteEmit->modal; 
		$ide->tpServ = '6'; 

		$ide->cMunIni = $cteEmit->municipioInicio->codigo; 
		$ide->xMunIni = strtoupper($cteEmit->municipioInicio->nome); 
		$ide->UFIni = $cteEmit->municipioInicio->uf; 
		$ide->cMunFim = $cteEmit->municipioFim->codigo; 
		$ide->xMunFim = strtoupper($cteEmit->municipioFim->nome); 
		$ide->UFFim = $cteEmit->municipioFim->uf; 

		if($cteEmit->tomador == 0){
			if($cteEmit->emitente->contribuinete){
				if($cteEmit->emitente->ie_rg == 'ISENTO'){
					$ide->indIEToma = '2';
				}else{
					$ide->indIEToma = '1';
				}
			}else{
				$ide->indIEToma = '9';
			}
		}else if($cteEmit->tomador == 3){
			if($cteEmit->destinatario->contribuinete){
				if($cteEmit->destinatario->ie_rg == 'ISENTO'){
					$ide->indIEToma = '2';
				}else{
					$ide->indIEToma = '1';
				}
			}else{
				$ide->indIEToma = '9';
			}
		}
		// $ide->indIEToma = $cteEmit->destinatario;
		$ide->dhCont = ''; 
		$ide->xJust = '';

		$cte->tagide($ide);

		$taginfServico = new \stdClass();
		$taginfServico->xDescServ = $cteEmit->descricao_servico;
		$taginfServico->qCarga = $this->format($cteEmit->quantidade_carga, 4);
		$cte->taginfServico($taginfServico);

		// Indica o "papel" do tomador: 0-Remetente; 1-Expedidor; 2-Recebedor; 3-Destinatário
		$toma = new \stdClass();

		$toma->toma = '4'; 

		$docTomador = preg_replace('/[^0-9]/', '', $cteEmit->tomador_cli->cpf_cnpj);

		if(strlen($docTomador) == 14){
			$toma->CNPJ = $docTomador;
		}else{
			$toma->CPF = $docTomador;
		}

		$ie = preg_replace('/[^0-9]/', '', $cteEmit->tomador_cli->ie_rg);

		$toma->IE = $ie;
		$toma->xNome = $cteEmit->tomador_cli->razao_social; 
		$toma->xFant = $cteEmit->tomador_cli->razao_social; 

		$fone = str_replace(" ", "", $cteEmit->emitente->tomador_cli);
		$fone = str_replace("-", "", $fone);
		$toma->fone = $fone; 
		$toma->email = $cteEmit->tomador_cli->email;   
		$toma->xLgr = $cteEmit->tomador_cli->rua; 
		$toma->nro = $cteEmit->tomador_cli->numero; 
		$toma->xCpl = ''; 
		$toma->xBairro = $cteEmit->tomador_cli->bairro; 
		$toma->cMun = $cteEmit->tomador_cli->cidade->codigo; 
		$toma->xMun = $cteEmit->tomador_cli->cidade->nome; 
		$toma->CEP = str_replace("-", "", $cteEmit->tomador_cli->cep); 
		$toma->UF = $cteEmit->tomador_cli->cidade->uf; 
		$toma->cPais = '1058'; 
		$toma->xPais = 'Brasil';                   
		$tomador = $cte->tagtomador($toma);
		
		$emit = new \stdClass();

		$emit->CNPJ = $cnpj; 

		$ie = preg_replace('/[^0-9]/', '', $cteEmit->emitente->ie_rg);

		$emit->IE = $ie; 
		$emit->IEST = "";
		$emit->xNome = $config->razao_social; 
		$emit->xFant = $cteEmit->emitente->nome_fantasia; 
		$cte->tagemit($emit); 

		$enderEmit = new \stdClass();
		$enderEmit->xLgr = $cteEmit->emitente->rua; 
		$enderEmit->nro = $cteEmit->emitente->numero; 
		$enderEmit->xCpl = '';
		$enderEmit->xBairro = $cteEmit->emitente->bairro; 
		$enderEmit->cMun = $cteEmit->emitente->cidade->codigo;
		$enderEmit->xMun = $this->retiraAcentos($config->cidade->nome); 

		$cep = str_replace("-", "", $cteEmit->emitente->cep);
		$cep = str_replace(".", "", $cep);
		$enderEmit->CEP = $cep; 
		$enderEmit->UF = $cteEmit->emitente->cidade->uf; 

		$fone = str_replace(" ", "", $cteEmit->emitente->telefone);
		$fone = str_replace("-", "", $fone);
		$enderEmit->fone = $fone; 
		$cte->tagenderEmit($enderEmit);

		$vPrest = new \stdClass();
		$vPrest->vTPrest = $this->format($cteEmit->valor_transporte); 
		$vPrest->vRec = $this->format($cteEmit->valor_receber);      
		$cte->tagvPrest($vPrest);


		$icms = new \stdClass();
		$icms->cst = $cteEmit->cst;
		$icms->pRedBC = ''; 
		$icms->vBC = $vPrest->vTPrest; 
		$icms->pICMS = $this->format($cteEmit->perc_icms);
		if($cteEmit->perc_icms > 0){ 
			$icms->vICMS = $this->format($vPrest->vTPrest * ($cteEmit->perc_icms/100)); 
		}else{
			$icms->vICMS = 0;
		}

		$icms->vBCUFFim = 0.00; 
		$icms->pFCPUFFim = 0.00; 
		$icms->pICMSUFFim = 0.00; 
		$icms->pICMSInter = 0.00; 
		$icms->vFCPUFFim = 0.00; 

		$icms->vBCSTRet = ''; 
		$icms->vICMSSTRet = ''; 
		$icms->pICMSSTRet = ''; 
		$icms->vCred = ''; 
		$icms->vTotTrib = 0.00; 
		$icms->outraUF = false;    
		$icms->vICMSUFIni = 0;  
		$icms->vICMSUFFim = 0;
		$icms->infAdFisco = '';
		$cte->tagicms($icms);

		if(strlen($docTomador) == 14){
			$tribFed = new \stdClass();
			$tribFed->vINSS = 0.00;
			$cte->taginfTribFed($tribFed);
		}

		$cte->taginfCTeNorm();              // Grupo de informações do CT-e Normal e Substituto
		
		$infModal = new \stdClass();
		$infModal->versaoModal = '3.00';
		$cte->taginfModal($infModal);

		$rodo = new \stdClass();
		$rodo->TAF = $cteEmit->veiculo->TAF;
		$rodo->NroRegEstadual = $cteEmit->veiculo->numero_registro_estadual;
		$cte->tagrodoOS($rodo);

		$veic = new \stdClass();
		$veic->placa = strtoupper(str_replace("-", "", $cteEmit->veiculo->placa));
		$veic->RENAVAM = $cteEmit->veiculo->renavam;
		$veic->tpCarga = $cteEmit->veiculo->tipo_carroceria;

		$veic->taf = $cteEmit->veiculo->taf;

		// $veic->xNome = $this->retiraAcentos($cteEmit->veiculo->proprietario_nome);
		$doc = preg_replace('/[^0-9]/', '', $cteEmit->veiculo->proprietario_documento);
		if(strlen($doc) == 14){
			$veic->CNPJ = $doc;
		}else{
			$veic->CNPJ = $doc;
		}
		// $veic->tpCarga = $cteEmit->veiculo->tipo_carroceria;
		$veic->uf = $cteEmit->veiculo->uf;
		$veic->ufProp = $cteEmit->veiculo->proprietario_uf;
		$veic->tpProp = $cteEmit->veiculo->proprietario_tp;
		$veic->taf = $cteEmit->veiculo->TAF;
		
		$cte->tagveicCTeOS($veic);
		
		if($cteEmit->observacao != ""){
			$stdCompl = new \stdClass();
			$stdCompl->xObs = $this->retiraAcentos($cteEmit->observacao);
			$cte->tagcompl($stdCompl);
		}

		$fr = new \stdClass();
		$fr->tpFretamento = 1;

		$fr->dhViagem = \Carbon\Carbon::parse(str_replace("/", "-", $cteEmit->data_viagem))->format('Y-m-d') . 'T'.$cteEmit->horario_viagem.':00-03:00';
		$cte->infFretamento($fr);

		$std = new \stdClass();
		$std->CNPJ = env('RESP_CNPJ'); //CNPJ da pessoa jurídica responsável pelo sistema utilizado na emissão do documento fiscal eletrônico
		$std->xContato= env('RESP_NOME'); //Nome da pessoa a ser contatada
		$std->email = env('RESP_EMAIL'); //E-mail da pessoa jurídica a ser contatada
		$std->fone = env('RESP_FONE'); //Telefone da pessoa jurídica/física a ser contatada
		$cte->taginfRespTec($std);

		try{
			$cte->montaCTe();
			$chave = $cte->chCTe;
			$xml = $cte->getXML();
			$arr = [
				'chave' => $chave,
				'xml' => $xml,
				'nCte' => $nCte
			];
			return $arr;
		}catch(\Exception $e){

			return [
				'erros_xml' => $cte->getErrors()
			];
		}
	}

	private function retiraAcentos($texto){
		return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/", "/(ç)/"),explode(" ","a A e E i I o O u U n N c"),$texto);
	}

	public function sign($xml){
		return $this->tools->signCTe($xml);
	}

	public function transmitir($signXml, $chave){
		try{
			$idLote = substr(str_replace(',', '', number_format(microtime(true) * 1000000, 0)), 0, 15);
			$resp = $this->tools->sefazEnviaCTeOS($signXml);
			sleep(4);
			$stdCl = new Standardize($resp);

			$arr = $stdCl->toArray();
			$std = $stdCl->toStd();

			// return "Erro: " . json_encode($arr);

			if(!isset($std->protCTe->infProt)){
				if ($std->cStat != 103) {
				// erro
					return "Erro: [$std->cStat] - $std->xMotivo";
				// return $std;
				}
			}

			if ($std->protCTe->infProt->cStat == 100) {

				$recibo = $std->infRec->nRec; 
				$protocolo = $this->tools->sefazConsultaRecibo($recibo);
				// sleep(3);
			// return $protocolo;
				$public = env('SERVIDOR_WEB') ? 'public/' : '';
				if(!is_dir(public_path('xml_cte_os'))){
					mkdir(public_path('xml_cte_os'), 0777, true);
				}
				try {
					$xml = Complements::toAuthorize($signXml, $resp);

					file_put_contents($public.'xml_cte_os/'.$chave.'.xml',$xml);
					return $std->protCTe->infProt->nProt;
				// $this->printDanfe($xml);
				} catch (\Exception $e) {
					return "Erro: " . $stdCl->toJson($protocolo);
				}
			}

		} catch(\Exception $e){
			return "Erro: ".$e->getMessage() ;
		}

	}	

	private function format($number, $dec = 2){
		return number_format((float) $number, $dec, ".", "");
	}

	private function montaChave($cUF, $ano, $mes, $cnpj, $mod, $serie, 
		$numero, $tpEmis, $codigo = ''){
		if ($codigo == '') {
			$codigo = $numero;
		}
		$forma = "%02d%02d%02d%s%02d%03d%09d%01d%08d";
		$chave = sprintf(
			$forma, $cUF, $ano, $mes, $cnpj, $mod, $serie, $numero, $tpEmis, $codigo
		);
		return $chave . $this->calculaDV($chave);
	}

	private function calculaDV($chave43){
		$multiplicadores = array(2, 3, 4, 5, 6, 7, 8, 9);
		$iCount = 42;
		$somaPonderada = 0;
		while ($iCount >= 0) {
			for ($mCount = 0; $mCount < count($multiplicadores) && $iCount >= 0; $mCount++) {
				$num = (int) substr($chave43, $iCount, 1);
				$peso = (int) $multiplicadores[$mCount];
				$somaPonderada += $num * $peso;
				$iCount--;
			}
		}
		$resto = $somaPonderada % 11;
		if ($resto == '0' || $resto == '1') {
			$cDV = 0;
		} else {
			$cDV = 11 - $resto;
		}
		return (string) $cDV;
	}


	public function cancelar($cteId, $justificativa){

		try {
			$cte = CteOs::
			where('id', $cteId)
			->first();
				// $this->tools->model('55');

			$chave = $cte->chave;
			$response = $this->tools->sefazConsultaChave($chave);
			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			$js = $stdCl->toJson();
			sleep(1);
			$xJust = $justificativa;

			if(!isset($arr['protCTe'])){
				return [
					'erro' => 1,
					'mensagem' => $arr['xMotivo']
				];
			}

			$nProt = $arr['protCTe']['infProt']['nProt'];


			$response = $this->tools->sefazCancela($chave, $xJust, $nProt);

			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();
			// return $json;
			$cStat = $std->infEvento->cStat;

			if(!is_dir(public_path('xml_cte_os_cancelada'))){
				mkdir(public_path('xml_cte_os_cancelada'), 0777, true);
			}

			$public = env('SERVIDOR_WEB') ? 'public/' : '';
			if ($cStat == '101' || $cStat == '135' || $cStat == '155') {
				$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
				// header('Content-type: text/xml; charset=UTF-8');
				file_put_contents($public.'xml_cte_os_cancelada/'.$chave.'.xml',$xml);
				return $json;
			}else{
				return $json;
			}

		} catch (\Exception $e) {
			return $e->getMessage();
    //TRATAR
		}
	}

	public function consultar($id){
		try {
			$cte = CteOs::
			where('id', $id)
			->first();

			$chave = $cte->chave;
			$response = $this->tools->sefazConsultaChave($chave);

			// return $response;
			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();

			// $arr = json_decode($json);
			return json_encode($arr);

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function inutilizar($nInicio, $nFinal, $justificativa){
		try{

			$nSerie = '1';
			$nIni = $nInicio;
			$nFin = $nFinal;
			$xJust = $justificativa;
			$tpAmb = 2;
			$response = $this->tools->sefazInutiliza($nSerie, $nIni, $nFin, $xJust, $tpAmb);

			$stdCl = new Standardize($response);

			$std = $stdCl->toStd();

			$arr = $stdCl->toArray();

			$json = $stdCl->toJson();

			return $arr;

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function cartaCorrecao($id, $grupo, $campo, $valor){
		try {

			$cte = CteOs::
			where('id', $id)
			->first();

			$chave = $cte->chave;

			$nSeqEvento = $cte->sequencia_cce+1;
			$infCorrecao[] = [
				'grupoAlterado' => $grupo,
				'campoAlterado' => $campo,
				'valorAlterado' => $valor,
				'nroItemAlterado' => '01'
			];
			$response = $this->tools->sefazCCe($chave, $infCorrecao, $nSeqEvento);
			sleep(2);

			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();
			$cStat = $std->infEvento->cStat;
			$public = env('SERVIDOR_WEB') ? 'public/' : '';

			if(!is_dir(public_path('xml_cte_os_correcao'))){
				mkdir(public_path('xml_cte_os_correcao'), 0777, true);
			}
			if ($cStat == '101' || $cStat == '135' || $cStat == '155') {
				$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
				file_put_contents($public.'xml_cte_os_correcao/'.$chave.'.xml',$xml);
				$cte->sequencia_cce = $cte->sequencia_cce + 1;
				$cte->save();
				return $json;
			}else{
				 //houve alguma falha no evento 
				return $json;
			}

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getXml($chave){
		// $resp = file_get_contents('ctes.xml');
		try{
			$resp = $this->tools->sefazDistDFe(0,0);
			// file_put_contents("ctes.xml", $resp);

			$dom = new \DOMDocument();
			$dom->loadXML($resp);
			$xMotivo = $dom->getElementsByTagName('xMotivo')->item(0)->nodeValue;

			if($xMotivo == 'Rejeicao: Consumo indevido'){
				echo $xMotivo;
				die;
			}

			$arrayDocs = [];
			$dom = new \DOMDocument();
			$dom->loadXML($resp);
			$node = $dom->getElementsByTagName('retDistDFeInt')->item(0);
			$lote = $node->getElementsByTagName('loteDistDFeInt')->item(0);

			$docs = $lote->getElementsByTagName('docZip');
			foreach ($docs as $doc) {
				$content = gzdecode(base64_decode($doc->nodeValue));
				$xml = simplexml_load_string($content);

				$temp = $xml->CTe->infCte;

				if(isset($temp->emit)){

					$chaveTemp = substr((string)$temp['Id'], 3, strlen((string)$temp['Id']));
					if($chaveTemp == $chave){
						return $content;
					}
				}
			}
		}catch(\Exception $e){
			echo "Erro: " . $e->getMessage();
		}
	}

	public function download($chave){
		try {

			$this->tools->setEnvironment(1);
			$chave = $chave;
			$response = $this->tools->sefazDownload($chave);
			return $response;

		} catch (\Exception $e) {
			echo str_replace("\n", "<br/>", $e->getMessage());
		}
	}

	public function consultaDocumentos(){
		$resp = $this->tools->sefazDistDFe(0,0);
		// file_put_contents("ctes.xml", $resp);
		// $resp = file_get_contents('ctes.xml');
		$dom = new \DOMDocument();
		$dom->loadXML($resp);
		$xMotivo = $dom->getElementsByTagName('xMotivo')->item(0)->nodeValue;
		
		if($xMotivo == 'Rejeicao: Consumo indevido'){
			echo $xMotivo;
			die;
		}

		$arrayDocs = [];
		$dom = new \DOMDocument();
		$dom->loadXML($resp);
		$node = $dom->getElementsByTagName('retDistDFeInt')->item(0);
		$lote = $node->getElementsByTagName('loteDistDFeInt')->item(0);

		$docs = $lote->getElementsByTagName('docZip');

		foreach ($docs as $doc) {
			$content = gzdecode(base64_decode($doc->nodeValue));
			$xml = simplexml_load_string($content);

			$xml = $xml->CTe->infCte;

			if(isset($xml->emit)){
				
				$chave = substr((string)$xml['Id'], 3, strlen((string)$xml['Id']));
				$temp = [
					'documento' => (int)$xml->emit->CNPJ,
					'nome' => (string)$xml->emit->xNome,
					'data_emissao' => (string)$xml->ide->dhEmi,
					'valor' => (float)$xml->vPrest->vTPrest,
					'chave' => $chave,
					'tipo' => 0,
					'sequencia_evento' => 0,
					'empresa_id' => $this->empresa_id
				];

			}

			array_push($arrayDocs, $temp);
		}

		return $arrayDocs;
	}

	public function desacordo($chave, $nSeqEvento, $xJust, $uf){
		try {
			$chNFe = $chave;
			$tpEvento = '610110'; 
			$nSeqEvento = $nSeqEvento;


			$response = $this->tools->sefazManifesta($chNFe, $tpEvento, $xJust, $nSeqEvento, 
				$uf);

			$st = new Standardize($response);

			$arr = $st->toArray();

			return $arr;

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}
}