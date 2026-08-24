<?php

namespace App\Services;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use App\Models\Certificado;
use NFePHP\NFe\Common\Standardize;

class DFeService{

	private $config; 
	private $tools;
	protected $empresa_id = null;

	public function __construct($config, $emitente){
		if($emitente->arquivo == null){
			abort(403, "realize o upload do certificado");
		}
		$this->empresa_id = $emitente->empresa_id;

		$this->config = $config;
		$this->tools = new Tools(json_encode($config), Certificate::readPfx($emitente->arquivo, $emitente->senha));
		$this->tools->model(55);
		$this->tools->setEnvironment(1);
		
	}

	public function novaConsulta($nsu){

		$ultNSU = $nsu;
		$maxNSU = $ultNSU;
		$loopLimit = 5;
		$iCount = 0;
		//executa a busca de DFe em loop
		$last = "";
		$imprime = false;
		$arrayDocs = [];
		$respostas = [];
		$ultimoStatus = null;
		$ultimoMotivo = '';
		while ($ultNSU <= $maxNSU) {
			$iCount++;
			if ($iCount >= $loopLimit) {
				break;
			}
			try {

				$resp = $this->tools->sefazDistDFe($ultNSU);

				// echo "<pre>";
				// print_r($resp);
				// echo "</pre>";

				array_push($respostas, $resp);
				
				$dom = new \DOMDocument();
				$dom->loadXML($resp);

				$node = $dom->getElementsByTagName('retDistDFeInt')->item(0);
				if (!$node) {
					throw new \RuntimeException('A SEFAZ retornou uma resposta de distribuição inválida.');
				}
				$tpAmb = $node->getElementsByTagName('tpAmb')->item(0)->nodeValue;
				$verAplic = $node->getElementsByTagName('verAplic')->item(0)->nodeValue;
				$cStat = $node->getElementsByTagName('cStat')->item(0)->nodeValue;
				$xMotivo = $node->getElementsByTagName('xMotivo')->item(0)->nodeValue;
				$ultimoStatus = (string) $cStat;
				$ultimoMotivo = trim((string) $xMotivo);
				$dhResp = $node->getElementsByTagName('dhResp')->item(0)->nodeValue;
				$ultNSU = $node->getElementsByTagName('ultNSU')->item(0)->nodeValue;
				$maxNSU = $node->getElementsByTagName('maxNSU')->item(0)->nodeValue;
				$lote = $node->getElementsByTagName('loteDistDFeInt')->item(0);


				if (empty($lote)) {
        //lote vazio
					continue;
				}
				if($last != $ultNSU){
					
					$last = $ultNSU;
					if (empty($lote)) {
        			//lote vazio
						continue;
					}
    				//essas tags irão conter os documentos zipados
					$docs = $lote->getElementsByTagName('docZip');

					foreach ($docs as $doc) {

						$numnsu = $doc->getAttribute('NSU');
						$schema = $doc->getAttribute('schema');

						$content = gzdecode(base64_decode((string) $doc->nodeValue, true));
						if (!is_string($content) || $content === '') {
							continue;
						}

						$temp = self::normalizaDocumentoDistribuido(
							$content,
							$schema,
							$numnsu,
							(int) $this->empresa_id
						);
						if ($temp !== null) {
							$arrayDocs[] = $temp;
						}
						
						$tipo = substr($schema, 0, 6);

					}
					sleep(2);
				}
			} catch (\Throwable $e) {
				report($e);
				return [
					"erro" => 1,
					"message" => 'A resposta da SEFAZ não pôde ser processada.'
				];
			}

		}

		if(sizeof($arrayDocs) > 0){
			// Guarda no último documento o cursor confirmado pela SEFAZ. Assim,
			// eventos ignorados na tela não fazem a próxima consulta repetir o lote.
			$arrayDocs[array_key_last($arrayDocs)]['nsu'] = (int) $ultNSU;
			return $arrayDocs;
		}

		// 137 significa que a consulta foi aceita, mas não existem documentos novos.
		// Isso não é erro e precisa chegar ao controller como uma lista vazia.
		if ($ultimoStatus === '137') {
			return [];
		}else{

			$search1 = 'Consumo Indevido';
			$xMotivo = "";
				// $search2 = 'Rejeicao';
			foreach($respostas as $resp){
				try{
					if(preg_match("/{$search1}/i", $resp)) {
						$dom = new \DOMDocument();
						$dom->loadXML($resp);
						$xMotivo = $dom->getElementsByTagName('xMotivo')->item(0)->nodeValue;

						return [
							"erro" => 1,
							"message" => $xMotivo
						];
					}else{
						$dom = new \DOMDocument();
						$dom->loadXML($resp);
						$xMotivo = $dom->getElementsByTagName('xMotivo')->item(0)->nodeValue;
					}
				}catch(\Exception $e){

				}
			}

			return [
				"erro" => 1,
				"message" => $xMotivo ?: ($ultimoMotivo ?: 'Não foi possível consultar os documentos na SEFAZ.')
			];
		}

	}

	/**
	 * Converte qualquer documento retornado na distribuição em valores escalares.
	 * A biblioteca SimpleXML não pode ser enviada diretamente ao navegador, pois
	 * seria serializada como objeto e exibida como "[object Object]".
	 */
	public static function normalizaDocumentoDistribuido(
		string $conteudo,
		string $schema,
		string $nsu,
		int $empresaId
	): ?array {
		libxml_use_internal_errors(true);
		$xml = simplexml_load_string($conteudo);
		libxml_clear_errors();
		if ($xml === false) {
			return null;
		}

		$valorXpath = static function (\SimpleXMLElement $documento, string $nome): string {
			$resultado = $documento->xpath('//*[local-name()="' . $nome . '"][1]');
			return isset($resultado[0]) ? trim((string) $resultado[0]) : '';
		};

		$chave = preg_replace('/\D+/', '', $valorXpath($xml, 'chNFe'));
		if (strlen($chave) !== 44) {
			$atributos = $xml->xpath('//*[local-name()="infNFe"][1]/@Id');
			$id = isset($atributos[0]) ? (string) $atributos[0] : '';
			$chave = preg_replace('/\D+/', '', $id);
		}

		$documento = preg_replace('/\D+/', '', $valorXpath($xml, 'CNPJ'));
		if ($documento === '') {
			$documento = preg_replace('/\D+/', '', $valorXpath($xml, 'CPF'));
		}

		$nome = $valorXpath($xml, 'xNome');
		$valor = str_replace(',', '.', $valorXpath($xml, 'vNF'));
		$dataEmissao = $valorXpath($xml, 'dhEmi') ?: $valorXpath($xml, 'dEmi');
		$protocolo = $valorXpath($xml, 'nProt');
		$nsuNumerico = preg_replace('/\D+/', '', $nsu);

		// Eventos e documentos de outros modelos também podem vir no lote.
		if (strlen($chave) !== 44 || $nome === '' || !is_numeric($valor)) {
			return null;
		}

		return [
			'documento' => $documento,
			'nome' => $nome,
			'data_emissao' => $dataEmissao,
			'valor' => number_format((float) $valor, 2, '.', ''),
			'num_prot' => $protocolo,
			'chave' => $chave,
			'nsu' => $nsuNumerico === '' ? 0 : (int) $nsuNumerico,
			'tipo' => 0,
			'fatura_salva' => false,
			'sequencia_evento' => 0,
			'empresa_id' => $empresaId,
		];
	}

	public function consulta($data_inicial, $data_final){
		$ultNSU = 0;
		$maxNSU = $ultNSU;
		$loopLimit = 10;
		$iCount = 0;
		//executa a busca de DFe em loop
		$last = "";
		$imprime = false;
		$arrayDocs = [];
		while ($ultNSU <= $maxNSU) {
			$iCount++;
			if ($iCount >= $loopLimit) {
				break;
			}
			try {

				$resp = $this->tools->sefazDistDFe($ultNSU);
				$dom = new \DOMDocument();
				$dom->loadXML($resp);

				$node = $dom->getElementsByTagName('retDistDFeInt')->item(0);
				$tpAmb = $node->getElementsByTagName('tpAmb')->item(0)->nodeValue;
				$verAplic = $node->getElementsByTagName('verAplic')->item(0)->nodeValue;
				$cStat = $node->getElementsByTagName('cStat')->item(0)->nodeValue;
				$xMotivo = $node->getElementsByTagName('xMotivo')->item(0)->nodeValue;
				$dhResp = $node->getElementsByTagName('dhResp')->item(0)->nodeValue;
				$ultNSU = $node->getElementsByTagName('ultNSU')->item(0)->nodeValue;
				$maxNSU = $node->getElementsByTagName('maxNSU')->item(0)->nodeValue;
				$lote = $node->getElementsByTagName('loteDistDFeInt')->item(0);
				if (empty($lote)) {
        //lote vazio
					continue;
				}
				if($last != $ultNSU){
					
					$last = $ultNSU;
					if (empty($lote)) {
        			//lote vazio
						continue;
					}
    				//essas tags irão conter os documentos zipados
					$docs = $lote->getElementsByTagName('docZip');

					

					foreach ($docs as $doc) {

						$numnsu = $doc->getAttribute('NSU');
						$schema = $doc->getAttribute('schema');

						$content = gzdecode(base64_decode($doc->nodeValue));
						$xml = simplexml_load_string($content);
						// print_r($xml);
						// print_r($xml->chNFe);
						$temp = [
							'documento' => $xml->CNPJ,
							'nome' => $xml->xNome,
							'data_emissao' => $xml->dhEmi,
							'valor' => $xml->vNF,
							'num_prot' => $xml->nProt,
							'chave' => $xml->chNFe
						];
						$data_dfe = \Carbon\Carbon::parse($xml->dhEmi)->format('Y-m-d');
						if(strtotime($data_dfe) >= strtotime($data_inicial) && strtotime($data_dfe) <= strtotime($data_final)){
							array_push($arrayDocs, $temp);
						}

						$tipo = substr($schema, 0, 6);

					}
					sleep(2);
				}
			} catch (\Exception $e) {
				return $e->getMessage();
			}

		}
		return $arrayDocs;

	}

	public function manifesta($chave, $nSeqEvento){
		try {

			$chNFe = $chave;
			$tpEvento = '210210'; 
			$xJust = ''; 
			$nSeqEvento = $nSeqEvento;

			$response = $this->tools->sefazManifesta($chNFe, $tpEvento, $xJust = '', $nSeqEvento);

			$st = new Standardize($response);

			$arr = $st->toArray();

			return $arr;

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function download($chave){
		try {

			$this->tools->setEnvironment(1);
			
			$response = $this->tools->sefazDownload($chave);
			return $response;

		} catch (\Exception $e) {
			echo str_replace("\n", "<br/>", $e->getMessage());
		}
	}

	public function confirmacao($chave, $nSeqEvento){
		try {

			$chNFe = $chave;
			$tpEvento = '210200'; 
			$xJust = ''; 
			$nSeqEvento = $nSeqEvento;

			$response = $this->tools->sefazManifesta($chNFe, $tpEvento, $xJust = '', $nSeqEvento);

			$st = new Standardize($response);

			$arr = $st->toArray();

			return $arr;

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function desconhecimento($chave, $nSeqEvento, $justificativa){
		$xJust = trim((string) $justificativa);
		$response = $this->tools->sefazManifesta($chave, '210240', $xJust, $nSeqEvento);

		return (new Standardize($response))->toArray();
	}

	public function operacaoNaoRealizada($chave, $nSeqEvento, $justificativa){
		$xJust = trim((string) $justificativa);
		$response = $this->tools->sefazManifesta($chave, '210220', $xJust, $nSeqEvento);

		return (new Standardize($response))->toArray();
	}

	
}
