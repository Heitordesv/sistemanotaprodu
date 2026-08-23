<?php

namespace Tests\Integration;

use App\Services\NFCeItemAdjustmentRateioService;
use App\Services\NFCeApproximateTaxService;
use DOMDocument;
use NFePHP\Common\Validator;
use NFePHP\NFe\Make;
use Tests\TestCase;

class NFCeFiscalAdjustmentXmlTest extends TestCase
{
    public function test_xml_nfce_fecha_rateios_e_valida_no_schema_nfephp(): void
    {
        $valores = [50.00, 50.00];
        $rateio = (new NFCeItemAdjustmentRateioService())->ratear(
            $valores,
            90.00,
            10.00,
            20.00
        );
        $tributos = new NFCeApproximateTaxService();
        $totalTributosCentavos = 0;

        $make = new Make();

        $std = (object) [
            'versao' => '4.00',
            'Id' => 'NFe43211105730928000145650010000002401717268120',
            'pk_nItem' => '',
        ];
        $make->taginfNFe($std);

        $make->tagide((object) [
            'cUF' => 43,
            'cNF' => '71726812',
            'natOp' => 'Venda',
            'mod' => 65,
            'serie' => 1,
            'nNF' => 240,
            'dhEmi' => '2021-11-11T18:56:55-03:00',
            'dhSaiEnt' => null,
            'tpNF' => 1,
            'idDest' => 1,
            'cMunFG' => 4322608,
            'tpImp' => 4,
            'tpEmis' => 1,
            'cDV' => 0,
            'tpAmb' => 2,
            'finNFe' => 1,
            'indFinal' => 1,
            'indPres' => 1,
            'procEmi' => 0,
            'verProc' => 'NFeNotas-test',
        ]);

        $make->tagemit((object) [
            'CNPJ' => '42530613000180',
            'xNome' => 'NF-E EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL',
            'xFant' => 'Empresa Teste',
            'IE' => '9999999999',
            'CRT' => 1,
        ]);

        $make->tagenderEmit((object) [
            'xLgr' => 'Rua Teste',
            'nro' => '100',
            'xBairro' => 'Centro',
            'cMun' => 4322608,
            'xMun' => 'Venancio Aires',
            'UF' => 'RS',
            'CEP' => '95800000',
            'cPais' => '1058',
            'xPais' => 'Brasil',
            'fone' => '5199999999',
        ]);

        foreach ($valores as $indice => $valorProduto) {
            $item = $indice + 1;
            $ajuste = $rateio[$indice];
            $tributosItem = $tributos->calcularItem($valorProduto, 12.34, 17.89, 0.57);
            $totalTributosCentavos += $tributosItem['total_centavos'];

            $make->tagprod((object) [
                'item' => $item,
                'cProd' => 'P' . $item,
                'cEAN' => 'SEM GTIN',
                'xProd' => 'PRODUTO TESTE ' . $item,
                'NCM' => '64042000',
                'CFOP' => '5102',
                'uCom' => 'UN',
                'qCom' => '1.0000',
                'vUnCom' => number_format($valorProduto, 4, '.', ''),
                'vProd' => number_format($valorProduto, 2, '.', ''),
                'cEANTrib' => 'SEM GTIN',
                'uTrib' => 'UN',
                'qTrib' => '1.0000',
                'vUnTrib' => number_format($valorProduto, 4, '.', ''),
                'vFrete' => number_format($ajuste['frete'], 2, '.', ''),
                'vDesc' => number_format($ajuste['desconto'], 2, '.', ''),
                'vOutro' => number_format($ajuste['acrescimo'], 2, '.', ''),
                'indTot' => 1,
            ]);

            $make->tagimposto((object) [
                'item' => $item,
                'vTotTrib' => $tributos->formatarCentavos($tributosItem['total_centavos']),
            ]);
            $make->tagICMSSN((object) [
                'item' => $item,
                'orig' => 0,
                'CSOSN' => '102',
            ]);
            $make->tagPIS((object) [
                'item' => $item,
                'CST' => '99',
                'vBC' => '0.00',
                'pPIS' => '0.00',
                'vPIS' => '0.00',
            ]);
            $make->tagCOFINS((object) [
                'item' => $item,
                'CST' => '99',
                'vBC' => '0.00',
                'pCOFINS' => '0.00',
                'vCOFINS' => '0.00',
            ]);
        }

        $make->tagICMSTot((object) [
            'vBC' => '0.00',
            'vICMS' => '0.00',
            'vICMSDeson' => '0.00',
            'vBCST' => '0.00',
            'vST' => '0.00',
            'vProd' => '100.00',
            'vFrete' => '20.00',
            'vSeg' => '0.00',
            'vDesc' => '90.00',
            'vII' => '0.00',
            'vIPI' => '0.00',
            'vPIS' => '0.00',
            'vCOFINS' => '0.00',
            'vOutro' => '10.00',
            'vNF' => '40.00',
            'vTotTrib' => $tributos->formatarCentavos($totalTributosCentavos),
        ]);

        $make->tagtransp((object) ['modFrete' => 9]);
        $make->tagpag(new \stdClass());
        $make->tagdetPag((object) [
            'tPag' => '01',
            'vPag' => '40.00',
        ]);

        $xml = $this->completarFixtureNfce($make->getXML());
        $schema = $this->schemaNFe400();

        $this->assertTrue(Validator::isValid($xml, $schema));

        $dom = new DOMDocument();
        $dom->loadXML($xml);

        $this->assertSame(
            '90.00',
            $this->somaTagsDosItens($dom, 'vDesc')
        );
        $this->assertSame(
            '10.00',
            $this->somaTagsDosItens($dom, 'vOutro')
        );
        $this->assertSame(
            '20.00',
            $this->somaTagsDosItens($dom, 'vFrete')
        );
        $this->assertSame(
            $tributos->formatarCentavos($totalTributosCentavos),
            $this->somaTagsDosItens($dom, 'vTotTrib')
        );

        $totais = $dom->getElementsByTagName('ICMSTot')->item(0);
        $vProd = (float) $totais->getElementsByTagName('vProd')->item(0)->nodeValue;
        $vFrete = (float) $totais->getElementsByTagName('vFrete')->item(0)->nodeValue;
        $vOutro = (float) $totais->getElementsByTagName('vOutro')->item(0)->nodeValue;
        $vDesc = (float) $totais->getElementsByTagName('vDesc')->item(0)->nodeValue;
        $vNF = (float) $totais->getElementsByTagName('vNF')->item(0)->nodeValue;

        $this->assertSame(
            $vNF,
            round($vProd + $vFrete + $vOutro - $vDesc, 2)
        );
    }

    private function completarFixtureNfce(string $xml): string
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        $namespace = 'http://www.portalfiscal.inf.br/nfe';
        $suplemento = $dom->createElementNS($namespace, 'infNFeSupl');
        $qrCode = $dom->createElementNS($namespace, 'qrCode');
        $qrCode->appendChild($dom->createCDATASection(
            'https://www.sefaz.rs.gov.br/NFCE/NFCE-COM.aspx?p=43211105730928000145650010000002409717268120|3|2|11|40.00|1|000001|YWJjZA=='
        ));
        $suplemento->appendChild($qrCode);
        $suplemento->appendChild($dom->createElementNS(
            $namespace,
            'urlChave',
            'https://www.sefaz.rs.gov.br/NFCE/NFCE-COM.aspx'
        ));
        $dom->documentElement->appendChild($suplemento);

        // A validação é estrutural. A assinatura criptográfica real permanece
        // responsabilidade do fluxo de transmissão com o certificado da empresa.
        $ds = 'http://www.w3.org/2000/09/xmldsig#';
        $signature = $dom->createElementNS($ds, 'Signature');
        $signedInfo = $signature->appendChild(
            $dom->createElementNS($ds, 'SignedInfo')
        );

        $canonicalization = $signedInfo->appendChild(
            $dom->createElementNS($ds, 'CanonicalizationMethod')
        );
        $canonicalization->setAttribute(
            'Algorithm',
            'http://www.w3.org/TR/2001/REC-xml-c14n-20010315'
        );

        $signatureMethod = $signedInfo->appendChild(
            $dom->createElementNS($ds, 'SignatureMethod')
        );
        $signatureMethod->setAttribute(
            'Algorithm',
            'http://www.w3.org/2000/09/xmldsig#rsa-sha1'
        );

        $reference = $signedInfo->appendChild(
            $dom->createElementNS($ds, 'Reference')
        );
        $reference->setAttribute(
            'URI',
            '#NFe43211105730928000145650010000002401717268120'
        );
        $transforms = $reference->appendChild(
            $dom->createElementNS($ds, 'Transforms')
        );
        foreach ([
            'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
            'http://www.w3.org/TR/2001/REC-xml-c14n-20010315',
        ] as $algorithm) {
            $transform = $transforms->appendChild(
                $dom->createElementNS($ds, 'Transform')
            );
            $transform->setAttribute('Algorithm', $algorithm);
        }

        $digestMethod = $reference->appendChild(
            $dom->createElementNS($ds, 'DigestMethod')
        );
        $digestMethod->setAttribute(
            'Algorithm',
            'http://www.w3.org/2000/09/xmldsig#sha1'
        );
        $reference->appendChild(
            $dom->createElementNS($ds, 'DigestValue', 'YWJjZA==')
        );
        $signature->appendChild(
            $dom->createElementNS($ds, 'SignatureValue', 'YWJjZA==')
        );
        $keyInfo = $signature->appendChild(
            $dom->createElementNS($ds, 'KeyInfo')
        );
        $x509Data = $keyInfo->appendChild(
            $dom->createElementNS($ds, 'X509Data')
        );
        $x509Data->appendChild(
            $dom->createElementNS($ds, 'X509Certificate', 'YWJjZA==')
        );

        $dom->documentElement->appendChild($signature);

        return $dom->saveXML();
    }

    private function schemaNFe400(): string
    {
        $schemas = glob(
            base_path('vendor/nfephp-org/sped-nfe/schemes/*/nfe_v4.00.xsd')
        );

        $this->assertNotEmpty($schemas, 'Schema NFe 4.00 não encontrado.');
        sort($schemas);

        return $schemas[0];
    }

    private function somaTagsDosItens(DOMDocument $dom, string $tag): string
    {
        $soma = 0.0;

        foreach ($dom->getElementsByTagName('det') as $det) {
            $elemento = $det->getElementsByTagName($tag)->item(0);
            $soma += $elemento ? (float) $elemento->nodeValue : 0;
        }

        return number_format($soma, 2, '.', '');
    }
}
