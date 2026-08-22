<?php

namespace App\Services;
use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;

use App\Models\ConfigNota;
use App\Models\Certificado;
use App\Models\Venda;
use App\Models\Compra;
use NFePHP\NFe\Complements;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\Legacy\FilesFolders;
use NFePHP\Common\Soap\SoapCurl;
use App\Models\Tributacao;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

class NFeEntradaService {

	private $config; 
	private $tools;
	protected $empresa_id = null;

	public function __construct($config, $emitente){
		$this->empresa_id = $emitente->empresa_id;

		$this->config = $config;
		$this->tools = new Tools(json_encode($config), Certificate::readPfx($emitente->arquivo, $emitente->senha));
		$this->tools->model(55);
		
	}

	public function gerarNFe($compra){
		
		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first(); // iniciando os dados do emitente NF

		$tributacao = Tributacao::
		where('empresa_id', $this->empresa_id)
		->first(); // iniciando tributos

		$nfe = new Make();
		$stdInNFe = new \stdClass();
		$stdInNFe->versao = '4.00'; 
		$stdInNFe->Id = null; 
		$stdInNFe->pk_nItem = ''; 

		$infNFe = $nfe->taginfNFe($stdInNFe);

		$compraLast = $config->ultimo_numero_nfe;

		$stdIde = new \stdClass();
		$stdIde->cUF = ConfigNota::getCodUF($config->cidade->uf);
		$stdIde->cNF = rand(11111,99999);
		// $stdIde->natOp = $venda->natureza->natureza;
		$stdIde->natOp = $compra->natureza->natureza;

		// $stdIde->indPag = 1; //NÃO EXISTE MAIS NA VERSÃO 4.00 // forma de pagamento

		$stdIde->mod = 55;
		$stdIde->serie = $config->numero_serie_nfe;
		$stdIde->nNF = (int)$compraLast+1;
		$stdIde->dhEmi = date("Y-m-d\TH:i:sP");
		$stdIde->dhSaiEnt = date("Y-m-d\TH:i:sP");
		$stdIde->tpNF = 0; // 0 Entrada;
		$stdIde->idDest = $config->UF != $compra->fornecedor->cidade->uf ? 2 : 1;
		$stdIde->cMunFG = $config->cidade->codigo;
		$stdIde->tpImp = 1;
		$stdIde->tpEmis = 1;
		$stdIde->cDV = 0;
		$stdIde->tpAmb = $config->ambiente;
		$stdIde->finNFe = $compra->natureza->finNFe;
		$stdIde->indFinal = 1;
		$stdIde->indPres = 1;
		$stdIde->procEmi = '0';
		// $stdIde->verProc = '2.0';

		$stdIde->verProc = '3.10.31';
		if($config->ambiente == 2){
			$stdIde->indIntermed = 0;
		}
		// $stdIde->dhCont = null;
		// $stdIde->xJust = null;


		//
		$tagide = $nfe->tagide($stdIde);

		$stdEmit = new \stdClass();
		$stdEmit->xNome = $config->razao_social;
		$stdEmit->xFant = $config->nome_fantasia;

		$ie = str_replace(".", "", $config->ie);
		$ie = str_replace("/", "", $ie);
		$ie = str_replace("-", "", $ie);
		$stdEmit->IE = $ie;
		// $stdEmit->CRT = $tributacao->regime == 0 ? 1 : 3;
		$stdEmit->CRT = ($tributacao->regime == 0 || $tributacao->regime == 2) ? 1 : 3;

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);
		
		if(strlen($cnpj) == 14){
			$stdEmit->CNPJ = $cnpj;
		}else{
			$stdEmit->CPF = $cnpj;
		}

		$emit = $nfe->tagemit($stdEmit);

		// ENDERECO EMITENTE
		$stdEnderEmit = new \stdClass();
		$stdEnderEmit->xLgr = $config->logradouro;
		$stdEnderEmit->nro = $config->numero;
		$stdEnderEmit->xCpl = "";
		$stdEnderEmit->xBairro = $config->bairro;
		$stdEnderEmit->cMun = $config->cidade->codigo;
		$stdEnderEmit->xMun = $config->cidade->nome;
		$stdEnderEmit->UF = $config->cidade->uf;

		$cep = str_replace("-", "", $config->cep);
		$cep = str_replace(".", "", $cep);
		$stdEnderEmit->CEP = $cep;
		$stdEnderEmit->cPais = $config->codPais;
		$stdEnderEmit->xPais = $config->pais;

		$enderEmit = $nfe->tagenderEmit($stdEnderEmit);

		// DESTINATARIO
		$stdDest = new \stdClass();
		$stdDest->xNome = $compra->fornecedor->razao_social;

		// if($compra->fornecedor->ie_rg == 'ISENTO'){
		// 	$stdDest->indIEDest = "2";
		// }else{
		// 	$stdDest->indIEDest = "1";
		// }

		if($compra->fornecedor->contribuinte){
			if($compra->fornecedor->ie_rg == 'ISENTO'){
				$stdDest->indIEDest = "2";
			}else{
				$stdDest->indIEDest = "1";
			}

		}else{
			$stdDest->indIEDest = "9";
		}

		$cnpj_cpf = str_replace(".", "", $compra->fornecedor->cpf_cnpj);
		$cnpj_cpf = str_replace("/", "", $cnpj_cpf);
		$cnpj_cpf = str_replace("-", "", $cnpj_cpf);

		if(strlen($cnpj_cpf) == 14){
			$stdDest->CNPJ = $cnpj_cpf;
			$ie = str_replace(".", "", $compra->fornecedor->ie_rg);
			$ie = str_replace("/", "", $ie);
			$ie = str_replace("-", "", $ie);
			$stdDest->IE = $ie;

		}
		else{
			// $stdDest->CPF = $cnpj_cpf;
			// $stdDest->indIEDest = "9";
			$stdDest->CPF = $cnpj_cpf;
			$ie = str_replace(".", "", $compra->fornecedor->ie_rg);
			$ie = str_replace("/", "", $ie);
			$ie = str_replace("-", "", $ie);
			if(strtolower($ie) != "isento" && $compra->fornecedor->contribuinte)
				$stdDest->IE = $ie;
			$pFisica = true;

		} 

		$dest = $nfe->tagdest($stdDest);

		$stdEnderDest = new \stdClass();
		$stdEnderDest->xLgr = $compra->fornecedor->rua;
		$stdEnderDest->nro = $compra->fornecedor->numero;
		$stdEnderDest->xCpl = "";
		$stdEnderDest->xBairro = $compra->fornecedor->bairro;
		$stdEnderDest->cMun = $compra->fornecedor->cidade->codigo;
		$stdEnderDest->xMun = $this->retiraAcentos($compra->fornecedor->cidade->nome);
		$stdEnderDest->UF = $compra->fornecedor->cidade->uf;

		$cep = str_replace("-", "", $compra->fornecedor->cep);
		$cep = str_replace(".", "", $cep);
		$stdEnderDest->CEP = $cep;
		$stdEnderDest->cPais = "1058";
		$stdEnderDest->xPais = "BRASIL";

		$enderDest = $nfe->tagenderDest($stdEnderDest);

		$somaProdutos = 0;
		$somaICMS = 0;
		//PRODUTOS
		$itemCont = 0;

		$totalItens = count($compra->itens);
		$somaFrete = 0;
		$p = null;
		$somaDesconto = 0;

		foreach($compra->chaves as $r){
			$std = new \stdClass();
			$std->refNFe = $r->chave;
			$nfe->tagrefNFe($std);
		}

		foreach($compra->itens as $i){
			$itemCont++;
			$p = $i;

			$stdProd = new \stdClass();
			$stdProd->item = $itemCont;
			$stdProd->cEAN = strlen($i->produto->codBarras) > 3 ? $i->produto->codBarras : 'SEM GTIN';
			$stdProd->cEANTrib = strlen($i->produto->codBarras) > 3  ? $i->produto->codBarras : 'SEM GTIN';
			$stdProd->cProd = $i->produto->id;
			$stdProd->xProd = $this->retiraAcentos($i->produto->nome);
			$ncm = $i->produto->NCM;
			$ncm = str_replace(".", "", $ncm);
			$stdProd->NCM = $ncm;

			if($compra->natureza->sobrescreve_cfop == 0){

				$stdProd->CFOP = $config->UF != $compra->fornecedor->cidade->uf ?
				$i->produto->CFOP_entrada_inter_estadual : $i->produto->CFOP_entrada_estadual;
			}else{
				$stdProd->CFOP = $config->UF != $compra->fornecedor->cidade->uf ?
				$compra->natureza->CFOP_entrada_inter_estadual : $compra->natureza->CFOP_entrada_estadual;
			}

			$stdProd->uCom = $i->produto->unidade_compra;
			$stdProd->qCom = $i->quantidade;
			$stdProd->vUnCom = $this->format($i->valor_unitario, $config->casas_decimais);
			$stdProd->vProd = $this->format(($i->quantidade * $i->valor_unitario));
			$stdProd->uTrib = $i->produto->unidade_compra;
			$stdProd->qTrib = $i->quantidade;
			$stdProd->vUnTrib = $this->format($i->valor_unitario, $config->casas_decimais);
			$stdProd->indTot = 1;
			$somaProdutos += ($i->quantidade * $i->valor_unitario);

			// if($compra->desconto > 0){
			// 	if($itemCont < sizeof($compra->itens)){
			// 		$stdProd->vDesc = $this->format($compra->desconto/$totalItens);
			// 		$somaDesconto += $compra->desconto/$totalItens;
			// 	}else{
			// 		$stdProd->vDesc = $compra->desconto - $somaDesconto;
			// 	}
			// }

			$totalCompra = $compra->valor + $compra->desconto;

			if($compra->desconto > 0.01 && $somaDesconto < $compra->desconto){

				if($itemCont < sizeof($compra->itens)){

					$media = (((($stdProd->vProd - $totalCompra)/$totalCompra))*100);
					$media = 100 - ($media * -1);

					$tempDesc = ($compra->desconto*$media)/100;

					if($tempDesc > 0.01){
						$stdProd->vDesc = $this->format($tempDesc);
					}else{
						$stdProd->vDesc = $this->format($somaDesconto);
					}

				}else{
					if(($compra->desconto - $somaDesconto) > 0.01){
						$stdProd->vDesc = $this->format($compra->desconto - $somaDesconto, $config->casas_decimais);
					}
				}
				$somaDesconto += $this->format($stdProd->vDesc);

			}

			if($compra->valor_frete > 0){
				if($itemCont < sizeof($compra->itens)){
					$somaFrete += $vFt = 
					number_format($compra->valor_frete/$totalItens, 2);
					$stdProd->vFrete = $this->format($vFt);
				}else{
					$stdProd->vFrete = $this->format(($compra->valor_frete-$somaFrete), 2);
				}
			}

			$prod = $nfe->tagprod($stdProd);

		//TAG IMPOSTO

			$stdImposto = new \stdClass();
			$stdImposto->item = $itemCont;

			$imposto = $nfe->tagimposto($stdImposto);

			// ICMS
			if($tributacao->regime == 1){ // regime normal

				//$venda->produto->CST  CST
				
				$stdICMS = new \stdClass();
				$stdICMS->item = $itemCont; 
				$stdICMS->orig = 0;
				$stdICMS->CST = $i->produto->CST_CSOSN_entrada;
				$stdICMS->modBC = 0;
				$stdICMS->vBC = $stdProd->vProd;
				$stdICMS->pICMS = $this->format($i->produto->perc_icms);
				$stdICMS->vICMS = $this->format(($i->valor_unitario * $i->quantidade) 
					* ($stdICMS->pICMS/100));

				$somaICMS += $stdICMS->vICMS;
				$ICMS = $nfe->tagICMS($stdICMS);

			}else{ // regime simples

				//$venda->produto->CST CSOSN

				$stdICMS = new \stdClass();
				
				$stdICMS->item = $itemCont; 
				$stdICMS->orig = 0;
				$stdICMS->CSOSN = $i->produto->CST_CSOSN_entrada;
				$stdICMS->pCredSN = $this->format($i->produto->perc_icms);
				$stdICMS->vCredICMSSN = $this->format($i->produto->perc_icms);
				$ICMS = $nfe->tagICMSSN($stdICMS);

				$somaICMS = 0;
			}

			
			$stdPIS = new \stdClass();//PIS
			$stdPIS->item = $itemCont; 
			$stdPIS->CST = $i->produto->CST_PIS_entrada;
			$stdPIS->vBC = $this->format($i->produto->perc_pis) > 0 ? $stdProd->vProd : 0.00;
			$stdPIS->pPIS = $this->format($i->produto->perc_pis);
			$stdPIS->vPIS = $this->format(($stdProd->vProd * $i->quantidade) * 
				($i->produto->perc_pis/100));
			$PIS = $nfe->tagPIS($stdPIS);


			$stdCOFINS = new \stdClass();//COFINS
			$stdCOFINS->item = $itemCont; 
			$stdCOFINS->CST = $i->produto->CST_COFINS_entrada;
			$stdCOFINS->vBC = $this->format($i->produto->perc_cofins) > 0 ? $stdProd->vProd : 0.00;
			$stdCOFINS->pCOFINS = $this->format($i->produto->perc_cofins);
			$stdCOFINS->vCOFINS = $this->format(($stdProd->vProd * $i->quantidade) * 
				($i->produto->perc_cofins/100));
			$COFINS = $nfe->tagCOFINS($stdCOFINS);


			$std = new \stdClass();//IPI
			$std->item = $itemCont; 
			$std->clEnq = null;
			$std->CNPJProd = null;
			$std->cSelo = null;
			$std->qSelo = null;
			$std->cEnq = '999'; //999 – para tributação normal IPI
			$std->CST = $i->produto->CST_IPI_entrada;
			$std->vBC = $this->format($i->produto->perc_ipi) > 0 ? $stdProd->vProd : 0.00;
			$std->pIPI = $this->format($i->produto->perc_ipi);
			$std->vIPI = $stdProd->vProd * $this->format(($i->produto->perc_ipi/100));
			$std->qUnid = null;
			$std->vUnid = null;

			$nfe->tagIPI($std);
		}

		$stdICMSTot = new \stdClass();
		// $stdICMSTot->vBC = 0.00;
		$stdICMSTot->vICMS = $this->format($somaICMS);
		$stdICMSTot->vICMSDeson = 0.00;
		$stdICMSTot->vBCST = 0.00;
		$stdICMSTot->vST = 0.00;
		$stdICMSTot->vProd = $this->format($somaProdutos);

		$stdICMSTot->vFrete = 0.00;
		$stdICMSTot->vFrete = $this->format($compra->valor_frete);
		$stdICMSTot->vSeg = 0.00;
		$stdICMSTot->vDesc = $this->format($compra->desconto);
		$stdICMSTot->vII = 0.00;
		$stdICMSTot->vIPI = 0.00;
		$stdICMSTot->vPIS = 0.00;
		$stdICMSTot->vCOFINS = 0.00;
		$stdICMSTot->vOutro = 0.00;

		// if($venda->frete){
		// 	$stdICMSTot->vNF = 
		// 	$this->format(($somaProdutos+$venda->frete->valor)-$venda->desconto);
		// } 
		$stdICMSTot->vNF = $this->format($compra->total+$compra->valor_frete-$compra->desconto);

		$stdICMSTot->vTotTrib = 0.00;
		$ICMSTot = $nfe->tagICMSTot($stdICMSTot);


		$stdTransp = new \stdClass();
		$stdTransp->modFrete = '9';

		$transp = $nfe->tagtransp($stdTransp);


		$stdTransp = new \stdClass();
		$stdTransp->modFrete = $compra->tipo ?? '9';

		$transp = $nfe->tagtransp($stdTransp);

		if($compra->transportadora){
			$std = new \stdClass();
			$std->xNome = $compra->transportadora->razao_social;

			$std->xEnder = $compra->transportadora->logradouro;
			$std->xMun = $this->retiraAcentos($compra->transportadora->cidade->nome);
			$std->UF = $compra->transportadora->cidade->uf;


			$cnpj_cpf = $compra->transportadora->cnpj_cpf;
			$cnpj_cpf = str_replace(".", "", $compra->transportadora->cnpj_cpf);
			$cnpj_cpf = str_replace("/", "", $cnpj_cpf);
			$cnpj_cpf = str_replace("-", "", $cnpj_cpf);

			if(strlen($cnpj_cpf) == 14) $std->CNPJ = $cnpj_cpf;
			else $std->CPF = $cnpj_cpf;

			$nfe->tagtransporta($std);
		}

		$placa = str_replace("-", "", $compra->placa);
		$std = new \stdClass();
		$std->placa = strtoupper($placa);
		$std->UF = $compra->uf;

			// if($config->UF == $venda->cliente->cidade->uf){
		if($compra->placa != "" && $compra->uf && $stdIde->idDest != 2){
			$nfe->tagveicTransp($std);
		}

		if($compra->qtdVolumes > 0 && $compra->peso_liquido > 0
			&& $compra->peso_bruto > 0){
			$stdVol = new \stdClass();
			$stdVol->item = 1;
			$stdVol->qVol = $compra->qtdVolumes;
			$stdVol->esp = $compra->especie;

			$stdVol->nVol = $compra->numeracaoVolumes;
			$stdVol->pesoL = $compra->peso_liquido;
			$stdVol->pesoB = $compra->peso_bruto;
			$vol = $nfe->tagvol($stdVol);
		}

	//Fatura
		if($compra->tipoPagamento != '90'){

			$stdFat = new \stdClass();
			$stdFat->nFat = $stdIde->nNF;
			$stdFat->vOrig = $this->format($compra->total);
			$stdFat->vDesc = $this->format(0.00);
			$stdFat->vLiq = $this->format($compra->total);

			$fatura = $nfe->tagfat($stdFat);
		}


	//Duplicata

		if($compra->tipo_pagamento != '90'){
			if(sizeof($compra->fatura) > 0){
				$contFatura = 1;
				foreach($compra->fatura as $ft){
					$stdDup = new \stdClass();
					$stdDup->nDup = "00".$contFatura;
					$stdDup->dVenc = substr($ft->data_vencimento, 0, 10);
					$stdDup->vDup = $this->format($ft->valor_integral);

					$nfe->tagdup($stdDup);
					$contFatura++;
				}
			}else{
				$stdDup = new \stdClass();
				$stdDup->nDup = '001';
				$stdDup->dVenc = Date('Y-m-d');
				$stdDup->vDup =  $this->format($compra->total);

				$nfe->tagdup($stdDup);
			}
		}

		$stdPag = new \stdClass();
		$pag = $nfe->tagpag($stdPag);

		$stdDetPag = new \stdClass();

		$stdDetPag->tPag = $compra->tipo_pagamento;
		$stdDetPag->vPag = $compra->tipo_pagamento == '90' ? 0.00 : $this->format($compra->total); 
		$stdDetPag->indPag = '0'; 

		$detPag = $nfe->tagdetPag($stdDetPag);

		$stdInfoAdic = new \stdClass();
		$obs = $this->retiraAcentos($compra->observacao);

		if($p->produto->renavam != ''){
			$veiCpl = 'RENAVAM ' . $p->produto->renavam;
			if($p->produto->placa != '') $veiCpl .= ', PLACA ' . $p->produto->placa;
			if($p->produto->chassi != '') $veiCpl .= ', CHASSI ' . $p->produto->chassi;
			if($p->produto->combustivel != '') $veiCpl .= ', COMBUSTÍVEL ' . $p->produto->combustivel;
			if($p->produto->ano_modelo != '') $veiCpl .= ', ANO/MODELO ' . $p->produto->ano_modelo;
			if($p->produto->cor_veiculo != '') $veiCpl .= ', COR ' . $p->produto->cor_veiculo;

			$obs .= $veiCpl;
		}

		$stdInfoAdic->infCpl = $this->retiraAcentos($obs);
		$infoAdic = $nfe->taginfAdic($stdInfoAdic);

		$std = new \stdClass();
		$std->CNPJ = env('RESP_CNPJ'); //CNPJ da pessoa jurídica responsável pelo sistema utilizado na emissão do documento fiscal eletrônico
		$std->xContato= env('RESP_NOME'); //Nome da pessoa a ser contatada
		$std->email = env('RESP_EMAIL'); //E-mail da pessoa jurídica a ser contatada
		$std->fone = env('RESP_FONE'); //Telefone da pessoa jurídica/física a ser contatada
		$nfe->taginfRespTec($std);
		
		if(env("AUTXML")){
			$std = new \stdClass();
			$std->CNPJ = env("AUTXML"); 
			$std->CPF = null;
			$nfe->tagautXML($std);
		}

		// if($nfe->montaNFe()){
		// 	$arr = [
		// 		'chave' => $nfe->getChave(),
		// 		'xml' => $nfe->getXML(),
		// 		'nNf' => $stdIde->nNF
		// 	];
		// 	return $arr;
		// } else {
		// 	throw new Exception("Erro ao gerar NFe");
		// }

		try{
			$nfe->montaNFe();
			$arr = [
				'chave' => $nfe->getChave(),
				'xml' => $nfe->getXML(),
				'nNf' => $stdIde->nNF
			];
			return $arr;
		}catch(\Exception $e){
			return [
				'erros_xml' => $nfe->getErrors()
			];
		}

	}

	private function retiraAcentos($texto){
		return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/", "/(ç)/"),explode(" ","a A e E i I o O u U n N c"),$texto);
	}

	public function format($number, $dec = 2){
		return number_format((float) $number, $dec, ".", "");
	}

	public function sign($xml){
		return $this->tools->signNFe($xml);
	}

	public function transmitir($signXml, $chave){
		try{
			$idLote = str_pad(100, 15, '0', STR_PAD_LEFT);
			$resp = $this->tools->sefazEnviaLote([$signXml], $idLote);

			$st = new Standardize();
			$std = $st->toStd($resp);
			sleep(2);
			if ($std->cStat != 103) {

				// return "[$std->cStat] - $std->xMotivo";
				return [
					'erro' => 1,
					'error' => "[$std->cStat] - $std->xMotivo"
				];
			}
			sleep(3);
			$recibo = $std->infRec->nRec; 
			
			$protocolo = $this->tools->sefazConsultaRecibo($recibo);

			try {
				$xml = Complements::toAuthorize($signXml, $protocolo);
				file_put_contents(public_path('xml_entrada_emitida/').$chave.'.xml',$xml);
				// return $recibo;
				return [
					'erro' => 0,
					'success' => $recibo
				];
			} catch (\Exception $e) {
				// return "Erro: " . $st->toJson($protocolo);
				return [
					'erro' => 1,
					'error' => $st->toArray($protocolo)
				];
			}

		} catch(\Exception $e){
			return [
				'erro' => 1,
				'error' => $e->getMessage()
			];
		}

	}	

	public function cancelar($compra, $justificativa){
		try {
			
			if(!is_dir(public_path('xml_nfe_entrada_cancelada'))){
				mkdir(public_path('xml_nfe_entrada_cancelada'), 0777, true);
			}
			$chave = $compra->chave;
			$response = $this->tools->sefazConsultaChave($chave);
			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			sleep(1);
				// return $arr;
			$xJust = $justificativa;


			$nProt = $arr['protNFe']['infProt']['nProt'];

			$response = $this->tools->sefazCancela($chave, $xJust, $nProt);
			sleep(2);
			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();

			if ($std->cStat != 128) {
        //TRATAR
			} else {
				$cStat = $std->retEvento->infEvento->cStat;
				if ($cStat == '101' || $cStat == '135' || $cStat == '155' ) {
            //SUCESSO PROTOCOLAR A SOLICITAÇÂO ANTES DE GUARDAR
					$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
					file_put_contents(public_path('xml_nfe_entrada_cancelada/').$chave.'.xml',$xml);

					return $arr;
				} else {
					return ['erro' => true, 'data' => $arr, 'status' => 402];		
				}
			}    
		} catch (\Exception $e) {
			return ['erro' => true, 'data' => $e->getMessage(), 'status' => 402];	
		}
	}

	public function cartaCorrecao($compra, $correcao){
		try {

			if(!is_dir(public_path('xml_nfe_entrada_correcao'))){
				mkdir(public_path('xml_nfe_entrada_correcao'), 0777, true);
			}
			$chave = $compra->chave;
			$xCorrecao = $correcao;
			$nSeqEvento = $compra->sequencia_cce+1;
			$response = $this->tools->sefazCCe($chave, $xCorrecao, $nSeqEvento);
			sleep(2);

			$stdCl = new Standardize($response);

			$std = $stdCl->toStd();

			$arr = $stdCl->toArray();

			$json = $stdCl->toJson();

			if ($std->cStat != 128) {
        //TRATAR
			} else {
				$cStat = $std->retEvento->infEvento->cStat;
				if ($cStat == '135' || $cStat == '136') {
            //SUCESSO PROTOCOLAR A SOLICITAÇÂO ANTES DE GUARDAR
					$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
					file_put_contents(public_path('xml_nfe_entrada_correcao/').$chave.'.xml',$xml);

					$compra->sequencia_cce = $compra->sequencia_cce + 1;
					$compra->save();
					return $arr;

				} else {
            //houve alguma falha no evento 
					return ['erro' => true, 'data' => $arr, 'status' => 402];	
            //TRATAR
				}
			}    
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function consultar($compra){
		try {
			
			$this->tools->model('55');

			$chave = $compra->chave;
			$response = $this->tools->sefazConsultaChave($chave);

			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			return $arr;

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

}
