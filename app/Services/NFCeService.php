<?php
namespace App\Services;

use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use App\Models\VendaCaixa;
use App\Models\ConfigNota;
use App\Models\Certificado;
use NFePHP\NFe\Complements;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\Legacy\FilesFolders;
use NFePHP\Common\Soap\SoapCurl;
use App\Models\Tributacao;
use App\Models\PedidoDelivery;
use App\Models\IBPT;
use App\Models\Contigencia;
use NFePHP\NFe\Factories\Contingency;

error_reporting(E_ALL);
ini_set('display_errors', 'On');


class NFCeService{

	private $config; 
	private $tools;
	protected $empresa_id = null;

	public function __construct($config, $emitente){
		
		$this->empresa_id = $emitente->empresa_id;

		$this->config = $config;
		$this->tools = new Tools(json_encode($config), Certificate::readPfx($emitente->arquivo, $emitente->senha));
		$soapCurl = new SoapCurl();
		$soapCurl->httpVersion('1.1');
		$contigencia = $this->getContigencia();

		if($contigencia != null){
			$contingency = new Contingency($contigencia->status_retorno);
			$this->tools->contingency = $contingency;
		}
		$this->tools->loadSoapClass($soapCurl);
		$this->tools->model(65);
		
	}

	private function getContigencia(){
		$active = Contigencia::
		where('empresa_id', $this->empresa_id)
		->where('status', 1)
		->where('documento', 'NFCe')
		->first();
		return $active;
	}
public function gerarNFCe($venda){

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		$tributacao = Tributacao::
		where('empresa_id', $this->empresa_id)
		->first(); 

		$nfe = new Make();
		$stdInNFe = new \stdClass();
		$stdInNFe->versao = '4.00'; //versão do layout
		$stdInNFe->Id = null; //se o Id de 44 digitos não for passado será gerado automaticamente
		$stdInNFe->pk_nItem = ''; //deixe essa variavel sempre como NULL

		$infNFe = $nfe->taginfNFe($stdInNFe);

		//IDE
		$stdIde = new \stdClass();
		$stdIde->cUF = ConfigNota::getCodUF($config->cidade->uf);
		$stdIde->cNF = rand(11111111, 99999999);
		$stdIde->natOp = $config->natureza->natureza;

		$vendaLast = VendaCaixa::lastNFCe($this->empresa_id);
		$lastNumero = $vendaLast;

		$stdIde->mod = 65;
		$stdIde->serie = $config->numero_serie_nfce;
		$stdIde->nNF = (int)$lastNumero+1; 
		$stdIde->dhEmi = date("Y-m-d\TH:i:sP");
		$stdIde->dhSaiEnt = date("Y-m-d\TH:i:sP");
		$stdIde->tpNF = 1;
		$stdIde->idDest = 1;
		$stdIde->cMunFG = $config->cidade->codigo;
		$stdIde->tpImp = 4;
		$stdIde->tpEmis = 1;
		$stdIde->cDV = 0;
		$stdIde->tpAmb = $config->ambiente;
		$stdIde->finNFe = 1;
		$stdIde->indFinal = 1;
		$stdIde->indPres = 1;
		if($config->ambiente == 2){
			$stdIde->indIntermed = 0;
		}
		$stdIde->procEmi = '0';
		$stdIde->verProc = '3.10.31';
		
		$tagide = $nfe->tagide($stdIde);

		$stdEmit = new \stdClass();
		$stdEmit->xNome = $config->razao_social;
		$stdEmit->xFant = $config->nome_fantasia;

		$ie = preg_replace('/[^0-9]/', '', $config->ie);
		$stdEmit->IE = $ie;
		$stdEmit->CRT = ($tributacao->regime == 0 || $tributacao->regime == 2) ? 1 : 3;

		$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
		$stdEmit->CNPJ = $cnpj; 

		$emit = $nfe->tagemit($stdEmit);

		// ENDERECO EMITENTE
		$stdEnderEmit = new \stdClass();
		$stdEnderEmit->xLgr = $config->logradouro;
		$stdEnderEmit->nro = $config->numero;
		$stdEnderEmit->xCpl = $config->complemento;
		$stdEnderEmit->xBairro = $config->bairro;
		$stdEnderEmit->cMun = $config->cidade->codigo;
		$stdEnderEmit->xMun = $config->cidade->nome;
		$stdEnderEmit->UF = $config->cidade->uf;

		$cep = str_replace("-", "", $config->cep);
		$stdEnderEmit->CEP = $cep;
		$stdEnderEmit->cPais = '1058';
		$stdEnderEmit->xPais = 'Brasil';

		$fone = preg_replace('/[^0-9]/', '', $config->fone);
		$stdEnderEmit->fone = $fone;

		$enderEmit = $nfe->tagenderEmit($stdEnderEmit);

		// DESTINATARIO
		if($venda->cliente_id != null || $venda->cpf != null){
			$stdDest = new \stdClass();
			if($venda->cliente_id != null){
				$stdDest->xNome = $venda->cliente->razao_social;
				$stdDest->indIEDest = "1";

				$cnpj_cpf = str_replace(".", "", $venda->cliente->cpf_cnpj);
				$cnpj_cpf = str_replace("/", "", $cnpj_cpf);
				$cnpj_cpf = str_replace("-", "", $cnpj_cpf);

				if(strlen($cnpj_cpf) == 14) $stdDest->CNPJ = $cnpj_cpf;
				else $stdDest->CPF = $cnpj_cpf;

				$dest = $nfe->tagdest($stdDest);

				$stdEnderDest = new \stdClass();
				$stdEnderDest->xLgr = $venda->cliente->rua;
				$stdEnderDest->nro = $venda->cliente->numero;
				$stdEnderDest->xCpl = "";
				$stdEnderDest->xBairro = $venda->cliente->bairro;
				$stdEnderDest->cMun = $venda->cliente->cidade->codigo;
				$stdEnderDest->xMun = strtoupper($venda->cliente->cidade->nome);
				$stdEnderDest->UF = $venda->cliente->cidade->uf;

				$cep = str_replace("-", "", $venda->cliente->cep);
				$stdEnderDest->CEP = $cep;
				$stdEnderDest->cPais = "1058";
				$stdEnderDest->xPais = "BRASIL";
				$enderDest = $nfe->tagenderDest($stdEnderDest);

			}
			if($venda->cpf != null){
				$cpf = str_replace(".", "", $venda->cpf);
				$cpf = str_replace("/", "", $cpf);
				$cpf = str_replace("-", "", $cpf);
				$cpf = str_replace(" ", "", $cpf);

				if($venda->nome) $stdDest->xNome = $venda->nome;
				$stdDest->indIEDest = "9";
				if(strlen($cpf) == 14) $stdDest->CNPJ = $cpf;
				else $stdDest->CPF = $cpf;
				$dest = $nfe->tagdest($stdDest);
			}
		}

		$somaProdutos = 0;
		$somaICMS = 0;
		$somaPIS = 0;
		$somaCOFINS = 0;
		$somaIBS = 0;
		$somaCBS = 0;
		$somaIS = 0;

		//PRODUTOS
		$itemCont = 0;
		$totalItens = count($venda->itens);
		$taxaEntrega = (float) ($venda->taxa_entrega ?? 0);
		$rateioAjustes = (new NFCeItemAdjustmentRateioService())->ratear(
			$venda->itens->map(function ($item) {
				return (float) $item->quantidade * (float) $item->valor;
			})->values()->all(),
			(float) $venda->desconto,
			(float) $venda->acrescimo,
			$taxaEntrega
		);
		$VBC = 0;

		$somaFederal = 0;
		$somaEstadual = 0;
		$somaMunicipal = 0;
		$somaTotTrib = 0;

		$fontesIbpt = [];
		$ufIbpt = (new FiscalIssuerUfService())->resolve($config);

		foreach($venda->itens as $i){
			$itemCont++;

			$stdProd = new \stdClass();
			$stdProd->item = $itemCont;

			$cod = $this->validate_EAN13Barcode($i->produto->codBarras);

			$stdProd->cEAN = $cod ? $i->produto->codBarras : 'SEM GTIN';
			$stdProd->cEANTrib = $cod ? $i->produto->codBarras : 'SEM GTIN';
			$stdProd->cProd = $i->produto->id;
			if($i->produto->referencia != ''){
				$stdProd->cProd = $i->produto->referencia;
			}

			$stdProd->xProd = $i->produto->nome;
			if($i->produto->CST_CSOSN == '500' || $i->produto->CST_CSOSN == '60'){
				$stdProd->cBenef = 'SEM CBENEF';
			}

			$ncm = $i->produto->NCM;
			$ncm = str_replace(".", "", $ncm);
			$stdProd->NCM = $ncm;
			$ibpt = $i->produto->ibpt ?? IBPT::getIBPT($ufIbpt, $ncm);

			$stdProd->CFOP = $i->produto->CFOP_saida_estadual;

			if($config->natureza->sobrescreve_cfop == 0){
				$stdProd->CFOP = $i->produto->CFOP_saida_estadual;
			}else{
				$stdProd->CFOP = $config->natureza->CFOP_saida_estadual;
			}

			$cest = $i->produto->CEST;
			$cest = str_replace(".", "", $cest);
			$stdProd->CEST = $cest;
			$stdProd->uCom = $i->produto->unidade_venda;
			$stdProd->qCom = $i->quantidade;
			$stdProd->vUnCom = $this->format($i->valor, $config->casas_decimais);
			$stdProd->vProd = $this->format($i->quantidade * $i->valor, $config->casas_decimais);
			
			if($i->produto->unidade_tributavel == ''){
				$stdProd->uTrib = $i->produto->unidade_venda;
			}else{
				$stdProd->uTrib = $i->produto->unidade_tributavel;
			}
			
			if($i->produto->quantidade_tributavel == 0){
				$stdProd->qTrib = $i->quantidade;
			}else{
				$stdProd->qTrib = $i->produto->quantidade_tributavel * $i->quantidade;
			}
			$stdProd->vUnTrib = $this->format($i->valor, $config->casas_decimais);
			$stdProd->indTot = 1;

			$ajusteFiscalItem = $rateioAjustes[$itemCont - 1];

			if($ajusteFiscalItem['frete'] > 0){
				$stdProd->vFrete = $this->format($ajusteFiscalItem['frete']);
			}

			if($ajusteFiscalItem['acrescimo'] > 0){
				$stdProd->vOutro = $this->format($ajusteFiscalItem['acrescimo']);
			}

			if($ajusteFiscalItem['desconto'] > 0){
				$stdProd->vDesc = $this->format($ajusteFiscalItem['desconto']);
			}

			// Pedidos delivery antigos não persistiam a taxa separadamente.
			// Mantém o fallback legado somente quando não há ajustes explícitos novos.
			if(
				$venda->pedido_delivery_id > 0 &&
				$taxaEntrega <= 0 &&
				(float) $venda->acrescimo <= 0
			){
				$pedido = PedidoDelivery::find($venda->pedido_delivery_id);
				$somaItens = $pedido->somaItensSemFrete();
				$totalVenda = $venda->valor_total;
				if($somaItens < $totalVenda){
					$vAcr = $totalVenda - $somaItens;
					$stdProd->vOutro = $this->format(
						$vAcr * (($i->quantidade * $i->valor) / max($somaItens, 0.01))
					);
				}
			}

			$somaProdutos += $i->quantidade * $i->valor;

			$prod = $nfe->tagprod($stdProd);

			$stdImposto = new \stdClass();
			$stdImposto->item = $itemCont;

			$vTotTribItem = 0;

			if($ibpt != null){
				$vProd = $stdProd->vProd;
				$aliqNacional = $ibpt->nacional ?? $ibpt->nacional_federal ?? 0;
				$aliqEstadual = $ibpt->estadual ?? 0;
				$aliqMunicipal = $ibpt->municipal ?? 0;

				$federal = $this->format(($vProd * ($aliqNacional / 100)), 2);
				$somaFederal += $federal;

				$estadual = $this->format(($vProd * ($aliqEstadual / 100)), 2);
				$somaEstadual += $estadual;

				$municipal = $this->format(($vProd * ($aliqMunicipal / 100)), 2);
				$somaMunicipal += $municipal;

				$vTotTribItem = $federal + $estadual + $municipal;
				$somaTotTrib += $vTotTribItem;

				$fonteIbpt = trim((string) ($ibpt->fonte ?? $ibpt->versao ?? ''));
				if($fonteIbpt !== ''){
					$fontesIbpt[] = $fonteIbpt;
				}
			}

			$imposto = $nfe->tagimposto($stdImposto, $this->format($vTotTribItem));

			if($tributacao->regime == 1){ // regime normal
				$stdICMS = new \stdClass();
				$stdICMS->item = $itemCont; 
				$stdICMS->orig = 0;
				$stdICMS->CST = $i->produto->CST_CSOSN;
				$stdICMS->modBC = 0;
				$stdICMS->vBC = $this->format($i->valor * $i->quantidade);
				$stdICMS->pICMS = $this->format($i->produto->perc_icms);
				$stdICMS->vICMS = $stdICMS->vBC * ($stdICMS->pICMS/100);

				if($i->produto->CST_CSOSN == '500' || $i->produto->CST_CSOSN == '60'){
					$stdICMS->pRedBCEfet = 0.00;
					$stdICMS->vBCEfet = 0.00;
					$stdICMS->pICMSEfet = 0.00;
					$stdICMS->vICMSEfet = 0.00;
				}else{
					$VBC += $stdProd->vProd;
					$somaICMS += $stdICMS->vICMS;
				}

				$ICMS = $nfe->tagICMS($stdICMS);

			}else{ // regime simples
				$stdICMS = new \stdClass();
				$stdICMS->item = $itemCont; 
				$stdICMS->orig = 0;
				$stdICMS->CSOSN = $i->produto->CST_CSOSN;
				$stdICMS->pCredSN = $this->format($i->produto->perc_icms);
				$stdICMS->vCredICMSSN = $this->format($i->produto->perc_icms);
				$ICMS = $nfe->tagICMSSN($stdICMS);

				$somaICMS = 0; 
			}

			// PIS
			$valorPISItem = $this->format(($stdProd->vProd) * ($i->produto->perc_pis/100));
			$somaPIS += $valorPISItem;

			$stdPIS = new \stdClass();
			$stdPIS->item = $itemCont; 
			$stdPIS->CST = $i->produto->CST_PIS;
			$stdPIS->vBC = $this->format($i->produto->perc_pis) > 0 ? $stdProd->vProd : 0.00;
			$stdPIS->pPIS = $this->format($i->produto->perc_pis);
			$stdPIS->vPIS = $valorPISItem;
			$PIS = $nfe->tagPIS($stdPIS);

			// COFINS
			$valorCofinsItem = $this->format(($stdProd->vProd) * ($i->produto->perc_cofins/100));
			$somaCOFINS += $valorCofinsItem;

			$stdCOFINS = new \stdClass();
			$stdCOFINS->item = $itemCont; 
			$stdCOFINS->CST = $i->produto->CST_COFINS;
			$stdCOFINS->vBC = $this->format($i->produto->perc_cofins) > 0 ? $stdProd->vProd : 0.00;
			$stdCOFINS->pCOFINS = $this->format($i->produto->perc_cofins);
			$stdCOFINS->vCOFINS = $valorCofinsItem;
			$COFINS = $nfe->tagCOFINS($stdCOFINS);

			if($i->produto->derivado_petroleo == 1){
				$stdComb = new \stdClass();
				$stdComb->item = $itemCont; 
				$stdComb->cProdANP = $i->produto->codigo_anp;
				$stdComb->descANP = $i->produto->getDescricaoAnp(); 

				if($i->produto->perc_glp > 0){
					$stdComb->pGLP = $this->format($i->produto->perc_glp);
				}
				if($i->produto->perc_gnn > 0){
					$stdComb->pGNn = $this->format($i->produto->perc_gnn);
				}
				if($i->produto->perc_gni > 0){
					$stdComb->pGNi = $this->format($i->produto->perc_gni);
				}

				$stdComb->vPart = $this->format($i->produto->valor_partida);
				$stdComb->UFCons = $venda->cliente ? $venda->cliente->cidade->uf : $config->UF;

				$nfe->tagcomb($stdComb);
			}

			$cest = $i->produto->CEST;
			$cest = str_replace(".", "", $cest);
			$stdProd->CEST = $cest;
			if(strlen($cest) > 0){
				$std = new \stdClass();
				$std->item = $itemCont; 
				$std->CEST = $cest;
				$nfe->tagCEST($std);
			}
		}

		// ICMS TOTAL E TOTAIS GERAIS
		$stdICMSTot = new \stdClass();
		$stdICMSTot->vBC = $this->format($VBC);
		$stdICMSTot->vICMS = $this->format($somaICMS);
		$stdICMSTot->vICMSDeson = 0.00;
		$stdICMSTot->vBCST = 0.00;
		$stdICMSTot->vST = 0.00;
		$stdICMSTot->vProd = $this->format($somaProdutos);
		$stdICMSTot->vFrete = $this->format($taxaEntrega);
		$stdICMSTot->vSeg = 0.00;
		$stdICMSTot->vDesc = $this->format($venda->desconto);
		$stdICMSTot->vII = 0.00;
		$stdICMSTot->vIPI = 0.00;
		$stdICMSTot->vPIS = $this->format($somaPIS);
		$stdICMSTot->vCOFINS = $this->format($somaCOFINS);
		$stdICMSTot->vOutro = $this->format($venda->acrescimo);
		$stdICMSTot->vNF = $this->format($venda->valor_total);
		$stdICMSTot->vTotTrib = $this->format($somaTotTrib);

		$stdICMSTot->vIBS = $this->format($somaIBS);
		$stdICMSTot->vCBS = $this->format($somaCBS);
		$stdICMSTot->vIS  = $this->format($somaIS);
		
		$ICMSTot = $nfe->tagICMSTot($stdICMSTot);

		// TRANSPORTADORA
		$stdTransp = new \stdClass();
		$stdTransp->modFrete = 9;
		$transp = $nfe->tagtransp($stdTransp);
		
		$stdPag = new \stdClass();
		if ($venda->tipo_pagamento != '99') {
			if($venda->tipo_pagamento == '01'){
				$stdPag->vTroco = $this->format($venda->troco); 
			}

			if($venda->troco == 0 && ($venda->valor_total != $venda->dinheiro_recebido)){
				if($venda->tipo_pagamento == '01'){
					$stdPag->vTroco = $this->format($venda->dinheiro_recebido - $venda->valor_total);
				} 
			}
		}

		$pag = $nfe->tagpag($stdPag);

		// RESPONSÁVEL TÉCNICO
		$stdResp = new \stdClass();
		$stdResp->CNPJ = env('RESP_CNPJ'); 
		$stdResp->xContato= env('RESP_NOME');
		$stdResp->email = env('RESP_EMAIL'); 
		$stdResp->fone = env('RESP_FONE'); 
		$nfe->taginfRespTec($stdResp);

		// DETALHE PAGAMENTO
		if ($venda->tipo_pagamento != '99') {
			$stdDetPag = new \stdClass();
			$stdDetPag->tPag = $venda->tipo_pagamento; 
			if($venda->tipo_pagamento == '06'){
				$stdDetPag->tPag = '05'; 
			}

			if($venda->tipo_pagamento == '03' || $venda->tipo_pagamento == '04' || $venda->tipo_pagamento == '17'){
				$stdDetPag->tBand = $venda->bandeira_cartao;
				if($venda->cAut_cartao != ""){
					$stdDetPag->cAut = $venda->cAut_cartao;
				}
				if($venda->cnpj_cartao != ""){
					$cnpj = str_replace(".", "", $venda->cnpj_cartao);
					$cnpj = str_replace("/", "", $cnpj);
					$cnpj = str_replace("-", "", $cnpj);
					$stdDetPag->CNPJ = $cnpj;
				}

				$stdDetPag->tpIntegra = 2;
				$stdDetPag->vPag = $this->format($venda->valor_total);

			}else{
				if($venda->tipo_pagamento == '01'){
					$stdDetPag->vPag = $this->format($venda->dinheiro_recebido);
				}else{
					$stdDetPag->vPag = $this->format($venda->valor_total);	
				}
			}

			$detPag = $nfe->tagdetPag($stdDetPag);
		} else {
			foreach($venda->fatura as $f){
				$stdDetPag = new \stdClass();
				if($f->forma_pagamento == '06'){
					$stdDetPag->tPag = '05'; 
				}
				$stdDetPag->tPag = $f->forma_pagamento; 

				$stdDetPag->vPag = $this->format($f->valor);
				if($f->forma_pagamento == '03' || $f->forma_pagamento == '04' || $f->forma_pagamento == '17'){
					$stdDetPag->tBand = '99';
					$stdDetPag->tpIntegra = 2;
				}

				$detPag = $nfe->tagdetPag($stdDetPag);				
			}
		}

		// INFORMAÇÃO ADICIONAL EXIBIDA NO BLOCO DE TRIBUTOS DO DANFC-e.
		$stdInfoAdic = new \stdClass();
		$stdInfoAdic->infCpl = (new NFCeTaxReceiptTextService())->formatar([
			'federal' => $somaFederal,
			'estadual' => $somaEstadual,
			'municipal' => $somaMunicipal,
			'total' => $somaTotTrib,
			'icms' => $somaICMS,
			'pis' => $somaPIS,
			'cofins' => $somaCOFINS,
			'ibs' => $somaIBS,
			'cbs' => $somaCBS,
			'is' => $somaIS,
		], $fontesIbpt);
		$infoAdic = $nfe->taginfAdic($stdInfoAdic);

		try{
			$nfe->monta();
			$arr = [
				'chave' => $nfe->getChave(),
				'xml' => $nfe->getXML(),
				'nNf' => $stdIde->nNF,
				'modelo' => $nfe->getModelo()
			];
			return $arr;
		}catch(\Exception $e){
			return [
				'erros_xml' => $nfe->getErrors()
			];
		}
	}


	private function validate_EAN13Barcode($ean)
	{

		$sumEvenIndexes = 0;
		$sumOddIndexes  = 0;

		$eanAsArray = array_map('intval', str_split($ean));

		if (!$this->has13Numbers($eanAsArray)) {
			return false;
		};

		for ($i = 0; $i < count($eanAsArray)-1; $i++) {
			if ($i % 2 === 0) {
				$sumOddIndexes  += $eanAsArray[$i];
			} else {
				$sumEvenIndexes += $eanAsArray[$i];
			}
		}

		$rest = ($sumOddIndexes + (3 * $sumEvenIndexes)) % 10;

		if ($rest !== 0) {
			$rest = 10 - $rest;
		}

		return $rest === $eanAsArray[12];
	}

	private function has13Numbers(array $ean)
	{
		return count($ean) === 13;
	}

	public function sign($xml){
		return $this->tools->signNFe($xml);
	}

	public function transmitir($signXml, $chave){
		try{
			$idLote = str_pad(100, 15, '0', STR_PAD_LEFT);

			$resp = $this->tools->sefazEnviaLote([$signXml], $idLote, 1);
			sleep(4);
			$st = new Standardize();
			$std = $st->toStd($resp);

			if ($std->cStat != 103 && $std->cStat != 104) {

				return [
					'erro' => 1,
					'error' => "[$std->cStat] - $std->xMotivo"
				];
			}
			sleep(1);

			try {
				$xml = Complements::toAuthorize($signXml, $resp);
				file_put_contents(public_path('xml_nfce/').$chave.'.xml',$xml);

				return [
					'erro' => 0,
					'success' => $std->protNFe->infProt->nProt
				];
			} catch (\Exception $e) {
				return [
					'erro' => 1,
					'error' => $st->toArray($resp)
				];
			}
		} catch(\Exception $e){
			return [
				'erro' => 1,
				'error' => $e->getMessage()
			];
		}

	}

   //  public function transmitirNfce($signXml, $chave){
   //  	try{
   //  		$idLote = str_pad(100, 15, '0', STR_PAD_LEFT);
   //  		$resp = $this->tools->sefazEnviaLote([$signXml], $idLote);
   //  		sleep(2);
   //  		$st = new Standardize();
   //  		$std = $st->toStd($resp);

   //  		if ($std->cStat != 103) {

   //  			return "[$std->cStat] - $std->xMotivo";
   //  		}
   //  		sleep(2);
   //  		$recibo = $std->infRec->nRec; 
   //  		$protocolo = $this->tools->sefazConsultaRecibo($recibo);
   //  		sleep(3);
			// // return $protocolo;

   //  		$public = env('SERVIDOR_WEB') ? 'public/' : '';
   //  		try {
   //  			$xml = Complements::toAuthorize($signXml, $protocolo);
   //  			header('Content-type: text/xml; charset=UTF-8');
   //  			file_put_contents($public.'xml_nfce/'.$chave.'.xml',$xml);
   //  			return $recibo;
			// 	// $this->printDanfe($xml);
   //  		} catch (\Exception $e) {
   //  			return "Erro: " . $st->toJson($protocolo);
   //  		}

   //  	} catch(\Exception $e){
   //  		return "Erro: ".$e->getMessage() ;
   //  	}

   //  }	

	public function consultaStatus($tpAmb, $uf){
		try{
			$response = $this->tools->sefazStatus($uf, $tpAmb);
			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			return $arr;
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}
	
public function cancelar($venda, $justificativa){
		try {

			$chave = $venda->chave;
			$response = $this->tools->sefazConsultaChave($chave);
			sleep(1);
			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			$xJust = $justificativa;

			$nProt = $arr['protNFe']['infProt']['nProt'];
			sleep(1);

			$response = $this->tools->sefazCancela($chave, $xJust, $nProt);

			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();

			if ($std->cStat != 128) {

			} else {
				$cStat = $std->retEvento->infEvento->cStat;
				if ($cStat == '101' || $cStat == '135' || $cStat == '155' ) {
            //SUCESSO PROTOCOLAR A SOLICITAÇÂO ANTES DE GUARDAR
					$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
					file_put_contents($public.'xml_nfce_cancelada/'.$chave.'.xml',$xml);

					return $arr;
				} else {
					return ['erro' => true, 'data' => $arr, 'status' => 402];	
				}
			}   

		} catch (\Exception $e) {
			return ['erro' => true, 'data' => $e->getMessage(), 'status' => 402];	
		}
	}

	public function format($number, $dec = 2){
		return number_format((float) $number, $dec, ".", "");
	}

	public function consultar($venda){
		try {

			$this->tools->model('65');

			$chave = $venda->chave;
			$response = $this->tools->sefazConsultaChave($chave);

			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();

			return $arr;

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function inutilizar($config, $nInicio, $nFinal, $justificativa){
		try{

			$nSerie = $config->numero_serie_nfce;
			$nIni = $nInicio;
			$nFin = $nFinal;
			$xJust = $justificativa;
			$response = $this->tools->sefazInutiliza($nSerie, $nIni, $nFin, $xJust);

			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();

			return $arr;

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

}
