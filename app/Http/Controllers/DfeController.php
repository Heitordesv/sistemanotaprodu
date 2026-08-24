<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use App\Models\ConfigNota;
use App\Models\Cidade;
use App\Models\Categoria;
use App\Models\CategoriaConta;
use App\Models\Produto;
use App\Models\Compra;
use App\Models\ContaPagar;
use App\Models\DivisaoGrade;
use App\Models\Fornecedor;
use App\Models\ItemCompra;
use App\Models\ItemDfe;
use App\Models\ManifestaDfe;
use App\Models\ManifestoDia;
use App\Models\Devolucao;
use App\Models\Transportadora;
use App\Models\NaturezaOperacao;
use App\Models\Tributacao;
use App\Services\DFeService;
use Illuminate\Http\Request;
use NFePHP\NFe\Common\Standardize;
use App\Models\TelaPedido;
use NFePHP\DA\NFe\Danfe;
use App\Helpers\StockMove;

class DfeController extends Controller
{

	public function __construct()
	{
		if (!is_dir(public_path('xml_dfe'))) {
			mkdir(public_path('xml_dfe'), 0777, true);
		}
	}

	public function index(Request $request)
	{
		$config = ConfigNota::where('empresa_id', $request->empresa_id)
		->first();

		if ($config == null) {
			session()->flash('flash_sucesso', 'Configure o Emitente');
			return redirect('configNF');
		}

		if ($config->arquivo == null) {
			session()->flash('flash_erro', 'Configure o Certificado');
			return redirect('configNF');
		}

		$start_date = $request->get('start_date');
		$end_date = $request->get('end_date');
		$tipo = $request->get('tipo');
		$data = ManifestaDfe::where('empresa_id', $request->empresa_id)
		->when(!empty($start_date), function ($query) use ($start_date) {
			return $query->whereDate('data_emissao', '>=', $start_date);
		})
		->when(!empty($end_date), function ($query) use ($end_date) {
			return $query->whereDate('data_emissao', '<=', $end_date);
		})
		->orderBy('data_emissao', 'desc')
		->paginate(env("PAGINACAO"));

		return view('dfe.index', compact('data'));
	}

	public function novaConsulta(Request $request)
	{
		$d1 = date("Y-m-d");
		$d2 = date('Y-m-d', strtotime('+1 day'));
		$consultas = ManifestoDia::whereBetween('created_at', [
			$d1,
			$d2
		])
		->where('empresa_id', $request->empresa_id)
		->get();
		return view('dfe.nova_consulta');
	}

	public function getDocumentosNovos(Request $request)
	{
		try {
			$local = $request->local;
			$config = ConfigNota::where('empresa_id', $request->empresa_id)
			->first();
			$isFilial = null;
			if ($local > 0) {
				$config = Filial::findOrFail($local);
				$isFilial = $local;
			}
			$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
			$dfe_service = new DFeService([
				"atualizacao" => date('Y-m-d h:i:s'),
				"tpAmb" => 1,
				"razaosocial" => $config->razao_social,
				"siglaUF" => $config->UF,
				"cnpj" => $cnpj,
				"schemes" => "PL_009_V4",
				"versao" => "4.00",
				"tokenIBPT" => "AAAAAAA",
				"CSC" => $config->csc,
				"CSCid" => $config->csc_id,
				"is_filial" => $isFilial
			], $config);

			$manifesto = ManifestaDfe::where('empresa_id', $request->empresa_id)
			->when($local > 0, function ($query) use ($local) {
				return $query->where('filial_id', $local);
			})
			->orderBy('nsu', 'desc')->first();

			if ($manifesto == null) $nsu = 0;
			else $nsu = $manifesto->nsu;
			$docs = $dfe_service->novaConsulta($nsu);
			$novos = [];

			if (!isset($docs['erro'])) {

				$novos = [];
				foreach ($docs as $d) {
					if ($this->validaNaoInserido($d['chave'])) {
						if ($d['valor'] > 0 && $d['nome']) {
							$d['filial_id'] = $local > 0 ? $local : null;
							ManifestaDfe::create($d);
							array_push($novos, $d);
						}
					}
				}

				ManifestoDia::create([
					'empresa_id' => $request->empresa_id
				]);
				return response()->json($novos, 200);
			} else {
				return response()->json($docs, 401);
			}
		} catch (\Exception $e) {
			return response()->json($e->getMessage(), 403);
			__saveLogError($e, request()->empresa_id);
		}
	}

	public function manifestar(Request $request)
	{
		try {
			$evento = (int) $request->input('tipo');
			$request->validate([
				'tipo' => 'required|integer|in:1,2,3,4',
				'chave' => 'required|string|size:44',
				'justificativa' => ($evento === 4 ? 'required' : 'nullable') . '|string|min:15|max:255',
			]);

			$config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
			if (!$config) {
				throw new \RuntimeException('Configure o emitente antes de manifestar o documento.');
			}

			$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
			$dfe_service = new DFeService([
				"atualizacao" => date('Y-m-d h:i:s'),
				"tpAmb" => 1,
				"razaosocial" => $config->razao_social,
				"siglaUF" => $config->cidade->uf,
				"cnpj" => $cnpj,
				"schemes" => "PL_009_V4",
				"versao" => "4.00",
				"tokenIBPT" => "AAAAAAA",
				"CSC" => $config->csc,
				"CSCid" => $config->csc_id
			], $config);

			$manifestaAnterior = $this->verificaAnterior($request->chave);
			$numEvento = $manifestaAnterior != null ? ((int) $manifestaAnterior->sequencia_evento + 1) : 1;

			if ($manifestaAnterior != null && $manifestaAnterior->tipo != $evento) {
				$numEvento--;
			}
			if ($numEvento == 0) $numEvento++;

			if ($evento === 1) {
				$res = $dfe_service->manifesta($request->chave, $numEvento);
			} elseif ($evento === 2) {
				$res = $dfe_service->confirmacao($request->chave, $numEvento);
			} elseif ($evento === 3) {
				$res = $dfe_service->desconhecimento($request->chave, $numEvento, $request->justificativa);
			} else {
				$res = $dfe_service->operacaoNaoRealizada($request->chave, $numEvento, $request->justificativa);
			}

			if (!is_array($res) || data_get($res, 'retEvento.infEvento.cStat') === null) {
				throw new \RuntimeException('A SEFAZ retornou uma resposta inválida para a manifestação.');
			}

			if ($res['retEvento']['infEvento']['cStat'] == '135') { //sucesso

				$manifesto = ManifestaDfe::where('empresa_id', $request->empresa_id)
				->where('chave', $request->chave)
				->first();
				$manifesto->sequencia_evento = $manifestaAnterior != null ? ($manifestaAnterior->sequencia_evento + 1) : 1;
				$manifesto->tipo = $evento;
				$manifesto->save();

				// ManifestaDfe::create($manifesta);
				session()->flash('flash_sucesso', $res['retEvento']['infEvento']['xMotivo'] . ": " . $request->chave);
			} else {

				// $manifesto = ManifestaDfe::where('empresa_id', $request->empresa_id)
				// ->where('chave', $request->chave)
				// ->first();

				// $manifesto->tipo = $evento;
				// $manifesto->save();

				$erro = "[" . $res['retEvento']['infEvento']['cStat'] . "] " . $res['retEvento']['infEvento']['xMotivo'];

				session()->flash("flash_erro", $erro . " - Chave: " . $request->chave);
			}
			return redirect()->route('dfe.index');
		} catch (\Throwable $e) {
			report($e);
			session()->flash('flash_erro', $e->getMessage());

			return redirect()->route('dfe.index');
		}
	}

	private function verificaAnterior($chave)
	{
		return ManifestaDfe::where('empresa_id', request()->empresa_id)
		->where('chave', $chave)->first();
	}

	public function download($id)
	{
		$naturezaPadrao = NaturezaOperacao::where('empresa_id', request()->empresa_id)->first();

		if($naturezaPadrao == null){
			session()->flash('flash_erro', 'Cadastre uma naturezaz de operação!');
			return redirect()->route('naturezas.index');
		}
		$divisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)
		->where('sub_divisao', false)
		->get();
		$subDivisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)
		->where('sub_divisao', true)
		->get();
		$config = ConfigNota::where('empresa_id', request()->empresa_id)
		->first();
		$dfe = ManifestaDfe::findOrFail($id);
		$chave = $dfe->chave;
		$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
		$dfe_service = new DFeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => 1,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->cidade->uf,
			"cnpj" => $cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => "AAAAAAA",
			"CSC" => $config->csc,
			"CSCid" => $config->csc_id
		], $config);
		try {
			$file_exists = false;
			if (file_exists(public_path('xml_dfe/') . $chave . '.xml')) {
				$file_exists = true;
			}
			if (!$file_exists) {
				$response = $dfe_service->download($chave);
				$stz = new Standardize($response);
				$std = $stz->toStd();
			} else {
				$std = null;
			}
			if ($std != null && ($std->cStat != 138)) {
				session()->flash("flash_erro", "Documento não retornado. [$std->cStat] $std->xMotivo!");
				return redirect()->back();
			} else {
				if (!$file_exists) {
					$zip = $std->loteDistDFeInt->docZip;
					$xml = gzdecode(base64_decode($zip));
					file_put_contents(public_path('xml_dfe/') . $chave . '.xml', $xml);
				} else {
					$xml = file_get_contents(public_path('xml_dfe/') . $chave . '.xml');
				}
				if (strlen($xml) < 1000) {
					unlink(public_path('xml_dfe/') . $chave . '.xml');
				}
				$nfe = simplexml_load_string($xml);
				$nNF = $nfe->NFe->infNFe->ide->nNF;
				$dfe->nNF = $nNF;
				// dd($dfe);
				$dfe->save();
				if (!$nfe) {
					session()->flash('flash_erro', 'Erro ao ler XML');
					return redirect('/dfe');
				} else {
					if (!isset($nfe->NFe->infNFe->emit->xNome)) {
						session()->flash('flash_erro', 'Isso não é uma NFe');
						return redirect('/dfe');
					}
					$fornecedor = $this->getFornecedorXML($nfe);
					$itens = $this->getItensDaNFe($nfe);
					// dd($itens);
					$infos = $this->getInfosDaNFe($nfe);
					// dd($infos);
					$fatura = $this->getFaturaDaNFe($nfe);
					// dd($fatura);
					$forn = Fornecedor::where('cpf_cnpj', $this->formataCnpj($fornecedor['cnpj']))
					->first();
					//caregar view

					$categorias = Categoria::where('empresa_id', request()->empresa_id)
					->get();
					$unidadesDeMedida = Produto::unidadesMedida();
					$listaCSTCSOSN = Produto::listaCSTCSOSN();
					$listaCST_PIS_COFINS = Produto::listaCST_PIS_COFINS();
					$listaCST_IPI = Produto::listaCST_IPI();
					$config = ConfigNota::where('empresa_id', request()->empresa_id)
					->first();

					$manifesto = ManifestaDfe::where('empresa_id', request()->empresa_id)
					->where('chave', $chave)->first();

					$compra = Compra::where('chave', $chave)
					->where('empresa_id', request()->empresa_id)
					->first();

					$vDesc = $nfe->NFe->infNFe->total->ICMSTot->vDesc;
					$nNf = $nfe->NFe->infNFe->ide->nNF;
					$anps = Produto::lista_ANP();
					$compraFiscal = $compra != null ? true : false;
					$fatura_salva = $manifesto == null ? false : $manifesto->fatura_salva;

					$telasPedido = TelaPedido::where('empresa_id', request()->empresa_id)->get();
					$tributacao = Tributacao::where('empresa_id', request()->empresa_id)
					->first();
					return view('dfe.show', compact(
						'fornecedor',
						'chave',
						'tributacao',
						'naturezaPadrao',
						'divisoes',
						'subDivisoes',
						'itens',
						'vDesc',
						'anps',
						'telasPedido',
						'nNf',
						'infos',
						'forn',
						'compraFiscal',
						'fatura',
						'dfe',
						'listaCSTCSOSN',
						'listaCST_PIS_COFINS',
						'listaCST_IPI',
						'categorias',
						'config',
						'fatura_salva',
						'unidadesDeMedida',
					));
				}
			}
		} catch (\Exception $e) {
			echo $e->getMessage() . '<br>' . $e->getLine();
			die;
			echo "Erro de soap:<br>";
			echo $e->getMessage();
		}
	}

	private function getFornecedorXML($xml)
	{
		$cidade = Cidade::getCidadeCod($xml->NFe->infNFe->emit->enderEmit->cMun);
		$fornecedor = [
			'cpf' => $xml->NFe->infNFe->emit->CPF,
			'cnpj' => $xml->NFe->infNFe->emit->CNPJ,
			'razaoSocial' => $xml->NFe->infNFe->emit->xNome,
			'nomeFantasia' => $xml->NFe->infNFe->emit->xFant ?? $xml->NFe->infNFe->emit->xNome,
			'logradouro' => $xml->NFe->infNFe->emit->enderEmit->xLgr,
			'numero' => $xml->NFe->infNFe->emit->enderEmit->nro,
			'bairro' => $xml->NFe->infNFe->emit->enderEmit->xBairro,
			'cep' => $xml->NFe->infNFe->emit->enderEmit->CEP,
			'fone' => $xml->NFe->infNFe->emit->enderEmit->fone,
			'ie' => $xml->NFe->infNFe->emit->IE,
			'cidade_id' => $cidade->id
		];
		$fornecedorEncontrado = $this->verificaFornecedor($xml->NFe->infNFe->emit->CNPJ);
		if ($fornecedorEncontrado) {
			$fornecedor['novo_cadastrado'] = false;
		} else {
			$fornecedor['novo_cadastrado'] = true;
			$idFornecedor = $this->cadastrarFornecedor($fornecedor);
		}
		return $fornecedor;
	}

	private function verificaFornecedor($cnpj)
	{
		$forn = Fornecedor::verificaCadastrado($this->formataCnpj($cnpj));
		return $forn;
	}

	private function formataCnpj($cnpj)
	{
		$temp = substr($cnpj, 0, 2);
		$temp .= "." . substr($cnpj, 2, 3);
		$temp .= "." . substr($cnpj, 5, 3);
		$temp .= "/" . substr($cnpj, 8, 4);
		$temp .= "-" . substr($cnpj, 12, 2);
		return $temp;
	}

	private function formataCep($cep)
	{
		$temp = substr($cep, 0, 5);
		$temp .= "-" . substr($cep, 5, 3);
		return $temp;
	}

	private function formataTelefone($fone)
	{
		$temp = substr($fone, 0, 2);
		$temp .= " " . substr($fone, 2, 4);
		$temp .= "-" . substr($fone, 4, 4);
		return $temp;
	}

	private function cadastrarFornecedor($fornecedor)
	{
		$result = Fornecedor::create([
			'razao_social' => $fornecedor['razaoSocial'],
			'nome_fantasia' => $fornecedor['nomeFantasia'],
			'rua' => $fornecedor['logradouro'],
			'numero' => $fornecedor['numero'],
			'bairro' => $fornecedor['bairro'],
			'cep' => $this->formataCep($fornecedor['cep']),
			'cpf_cnpj' => $this->formataCnpj($fornecedor['cnpj']),
			'ie_rg' => $fornecedor['ie'],
			'celular' => '*',
			'telefone' => $this->formataTelefone($fornecedor['fone']),
			'email' => '*',
			'cidade_id' => $fornecedor['cidade_id'],
			'empresa_id' => request()->empresa_id
		]);
		return $result->id;
	}

	private function getItensDaNFe($xml)
	{
		$itens = [];
		foreach ($xml->NFe->infNFe->det as $item) {
			$produto = Produto::verificaCadastrado(
				(string)$item->prod->cEAN,
				(string)$item->prod->xProd,
				(string)$item->prod->cProd
			);
			$produtoNovo = !$produto ? true : false;
			$tp = null;
			$vVenda = 0;
			if ($produto != null) {
				$tp = ItemDfe::where('produto_id', $produto->id)
				->where('numero_nfe', $xml->NFe->infNFe->ide->nNF)
				->where('empresa_id', request()->empresa_id)
				->first();
				$vVenda = $item->prod->vUnCom +
				(($item->prod->vUnCom * $produto->percentual_lucro) / 100);
			}
			$nomeProduto = $item->prod->xProd;
			if ($produto != null && $nomeProduto != $produto->nome) {
				$nomeProduto .= " ($produto->nome)";
			}

			$item = [
				'codigo' => $item->prod->cProd,
				'xProd' => $nomeProduto,
				'NCM' => $item->prod->NCM,
				'CEST' => $item->prod->CEST,
				'CFOP' => $item->prod->CFOP,
				'uCom' => $item->prod->uCom,
				'vUnCom' => $item->prod->vUnCom,
				'vUnVenda' => $vVenda,
				'qCom' => $item->prod->qCom,
				'codBarras' => $item->prod->cEAN,
				'produtoNovo' => $produtoNovo,
				'produto_id' => $produtoNovo ? null : $produto->id,
				'produtoSetadoEstoque' => $tp != null ? true : false,
				'produtoId' => $produtoNovo ? '0' : $produto->id,
				'conversao_unitaria' => $produtoNovo ? '' : $produto->conversao_unitaria
			];
			array_push($itens, $item);
		}
		return $itens;
	}

	private function getInfosDaNFe($xml)
	{
		$chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
		$vFrete = number_format(
			(float) $xml->NFe->infNFe->total->ICMSTot->vFrete,
			2,
			",",
			"."
		);
		$vDesc = number_format((float) $xml->NFe->infNFe->total->ICMSTot->vDesc, 2, ",", ".");
		return [
			'chave' => $chave,
			'vProd' => $xml->NFe->infNFe->total->ICMSTot->vProd,
			'indPag' => $xml->NFe->infNFe->ide->indPag,
			'nNf' => $xml->NFe->infNFe->ide->nNF,
			'vFrete' => $vFrete,
			'vDesc' => $vDesc
		];
	}

	private function getFaturaDaNFe($xml)
	{
		if (!empty($xml->NFe->infNFe->cobr->dup)) {
			$fatura = [];
			$cont = 1;
			foreach ($xml->NFe->infNFe->cobr->dup as $dup) {
				$titulo = $dup->nDup;
				$vencimento = $dup->dVenc;
				$vencimento = explode('-', $vencimento);
				$vencimento = $vencimento[2] . "/" . $vencimento[1] . "/" . $vencimento[0];
				$vlr_parcela = number_format((float) $dup->vDup, 2, ",", ".");

				$parcela = [
					'numero' => $titulo,
					'vencimento' => $vencimento,
					'valor_parcela' => $vlr_parcela,
					'referencia' => $xml->NFe->infNFe->ide->nNF . "/" . $cont
				];
				array_push($fatura, $parcela);
				$cont++;
			}
			return $fatura;
		}
		return [];
	}

public function storeFatura(Request $request)
{
    $dfe = ManifestaDfe::findOrFail($request->dfe_id);

    $categorias = CategoriaConta::where('empresa_id', $request->empresa_id)
        ->where('tipo', 'pagar')
        ->first();

    try {
        for ($i = 0; $i < count($request->vencimento); $i++) {

            // Converte data para formato Y-m-d
            $dataVencimento = \Carbon\Carbon::createFromFormat('d/m/Y', $request->vencimento[$i])->format('Y-m-d');

            ContaPagar::create([
                'compra_id' => null,
                'data_vencimento' => $dataVencimento,
                'data_pagamento' => $dataVencimento,
                'valor_integral' => floatval(str_replace(',', '.', $request->valor_parcela[$i])),
                'valor_pago' => 0,
                'referencia' => '',
                'categoria_id' => $categorias->id,
                'status' => '',
                'empresa_id' => $request->empresa_id,
                'fornecedor_id' => $request->fornecedor_id,
                'tipo_pagamento' => ''
            ]);
        }

        $dfe->fatura_salva = 1;
        $dfe->save();

        session()->flash('flash_sucesso', 'Fatura adicionada com sucesso!');
    } catch (\Exception $e) {
        session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
    }

    return redirect()->back();
}

public function storeCompra(Request $request)
{
    try {
        // Busca fornecedor e DFE
        $fornecedor = Fornecedor::findOrFail($request->fornecedor_id);
        $dfe = ManifestaDfe::findOrFail($request->dfe_id);

        // Cria registro da compra
        $result = Compra::create([
            'fornecedor_id' => $fornecedor->id,
            'usuario_id' => get_id_user(),
            'numero_nfe' => $request->nNf,
            'observacao' => '',
            'total' => floatval(str_replace(',', '.', $request->valor_total)),
            'desconto' => floatval(str_replace(',', '.', $request->vDesc ?? 0)),
            'xml_path' => '',
            'estado' => 'aprovado',
            'numero_emissao' => 0,
            'chave' => $request->chave,
            'empresa_id' => $request->empresa_id
        ]);

        $stockMove = new StockMove();

        // Loop pelos produtos
        for ($i = 0; $i < count($request->produto_id); $i++) {
            $produto = Produto::findOrFail((int)$request->produto_id[$i]);

            // Converte valores para float
            $quantidade = floatval(str_replace(',', '.', $request->quantidade[$i]));
            $valor_unitario = floatval(str_replace(',', '.', $request->valor_unitario[$i]));
            $conversao = floatval($produto->conversao_unitaria ?? 1); // Se nulo, assume 1

            // Cria item de compra
            ItemCompra::create([
                'compra_id' => $result->id,
                'produto_id' => (int) $request->produto_id[$i],
                'quantidade' => $quantidade,
                'valor_unitario' => $valor_unitario,
                'unidade_compra' => $request->unidade_compra[$i],
                'cfop_entrada' => $request->cfop[$i],
                'codigo_siad' => ''
            ]);

            // Atualiza estoque
            $stockMove->pluStock(
                (int) $request->produto_id[$i],
                __convert_value_bd($quantidade * $conversao),
                __convert_value_bd($valor_unitario)
            );
        }

        // Atualiza referência no DFE
        $dfe->compra_id = $result->id;
        $dfe->save();

        session()->flash('flash_sucesso', 'Salvo em compras');
    } catch (\Exception $e) {
        // Mostra mensagem de erro detalhada
        echo "Erro: " . $e->getMessage() . "<br>Linha: " . $e->getLine();
        die;
        session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
    }

    return redirect()->back();
}

	public function devolucao($id){
		$item = ManifestaDfe::findOrFail($id);

		$config = ConfigNota::where('empresa_id', request()->empresa_id)
		->first();

		$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

		$dfe_service = new DFeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => 1,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->cidade->uf,
			"cnpj" => $cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => "AAAAAAA",
			"CSC" => $config->csc,
			"CSCid" => $config->csc_id
		], $config);

		// $response = $dfe_service->download($chave);

		$chave = $item->chave;
		$file_exists = false;
		if (file_exists(public_path('xml_dfe/') . $chave . '.xml')) {
			$file_exists = true;
		}
		if(!$file_exists){
			$response = $dfe_service->download($chave);
			$stz = new Standardize($response);
			$std = $stz->toStd();
		}else{
			$std = null;
		}
		// print_r($response);
		try {
			if(!$file_exists){
				$zip = $std->loteDistDFeInt->docZip;
				$xml = gzdecode(base64_decode($zip));

				file_put_contents(public_path('xml_dfe/').$chave.'.xml', $xml);
			}else{
				$xml = file_get_contents(public_path('xml_dfe/').$chave.'.xml');
			}

			if ($std != null && $std->cStat != 138) {
				echo "Documento não retornado. [$std->cStat] $std->xMotivo" . ", aguarde alguns instantes e atualize a pagina!";  
				die;
			}

			$view = $this->viewXml($xml);

			$item->devolucao = 1;
			$item->save();
			return $view;

		} catch (InvalidArgumentException $e) {
			echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
		}  


	}

	public function danfe($id){
		$item = ManifestaDfe::findOrFail($id);

		$config = ConfigNota::where('empresa_id', request()->empresa_id)
		->first();

		$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

		$dfe_service = new DFeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => 1,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->cidade->uf,
			"cnpj" => $cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => "AAAAAAA",
			"CSC" => $config->csc,
			"CSCid" => $config->csc_id
		], $config);

		// $response = $dfe_service->download($chave);

		$chave = $item->chave;
		$file_exists = false;
		if (file_exists(public_path('xml_dfe/') . $chave . '.xml')) {
			$file_exists = true;
		}
		if(!$file_exists){
			$response = $dfe_service->download($chave);
			$stz = new Standardize($response);
			$std = $stz->toStd();
		}else{
			$std = null;
		}
		// print_r($response);
		try {
			if(!$file_exists){
				$zip = $std->loteDistDFeInt->docZip;
				$xml = gzdecode(base64_decode($zip));

				file_put_contents(public_path('xml_dfe/').$chave.'.xml', $xml);
			}else{
				$xml = file_get_contents(public_path('xml_dfe/').$chave.'.xml');
			}

			if ($std != null && $std->cStat != 138) {
				echo "Documento não retornado. [$std->cStat] $std->xMotivo" . ", aguarde alguns instantes e atualize a pagina!";  
				die;
			}    
			$dfe = ManifestaDfe::where('chave', $chave)->first();
			$nfe = simplexml_load_string($xml);
			$nNF = $nfe->NFe->infNFe->ide->nNF;
			$dfe->nNF = $nNF;
			$dfe->save();

			file_put_contents(public_path('xml_dfe/').$chave.'.xml',$xml);

			$danfe = new Danfe($xml);
			// $id = $danfe->monta();
			$pdf = $danfe->render();
			header('Content-Type: application/pdf');
			// echo $pdf;
			return response($pdf)
			->header('Content-Type', 'application/pdf');
		} catch (InvalidArgumentException $e) {
			echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
		}  


	}

	public function downloadXml($chave){

		$dfe = ManifestaDfe::where('empresa_id', request()->empresa_id)->where('chave', $chave)->first();
		$chave = $dfe->chave; 
		$public = env('SERVIDOR_WEB') ? 'public/' : '';
		if(file_exists($public.'xml_dfe/'.$chave.'.xml'))
			return response()->download($public.'xml_dfe/'.$chave.'.xml');
		else echo "Erro ao baixar XML, arquivo não encontrado!";
	}

	public function viewXml($xml)
	{

		$xml = simplexml_load_string($xml);
		if (!isset($xml->NFe->infNFe)) {
			session()->flash('flash_erro', 'Este xml não é uma NFe');
			return redirect()->route('devolucao.create');
		}
		
		$cidade = Cidade::getCidadeCod($xml->NFe->infNFe->emit->enderEmit->cMun);
		$dadosEmitente = [
			'cpf' => $xml->NFe->infNFe->emit->CPF,
			'cnpj' => $xml->NFe->infNFe->emit->CNPJ,
			'razaoSocial' => $xml->NFe->infNFe->emit->xNome,
			'nomeFantasia' => $xml->NFe->infNFe->emit->xFant,
			'logradouro' => $xml->NFe->infNFe->emit->enderEmit->xLgr,
			'numero' => $xml->NFe->infNFe->emit->enderEmit->nro,
			'bairro' => $xml->NFe->infNFe->emit->enderEmit->xBairro,
			'cep' => $xml->NFe->infNFe->emit->enderEmit->CEP,
			'fone' => $xml->NFe->infNFe->emit->enderEmit->fone,
			'ie' => $xml->NFe->infNFe->emit->IE,
			'cidade_id' => $cidade->id
		];
		$transportadora = null;
		$transportadoraDoc = null;
		if ($xml->NFe->infNFe->transp) {
			$transp = $xml->NFe->infNFe->transp->transporta;
			$veic = $xml->NFe->infNFe->transp->veicTransp;
			$transportadoraDoc = (int)$transp->CNPJ;
			$vol = $xml->NFe->infNFe->transp->vol;
			$modFrete = $xml->NFe->infNFe->transp;
			$transportadora = [
				'transportadora_nome' => (string)$transp->xNome,
				'transportadora_cidade' => (string)$transp->xMun,
				'transportadora_uf' => (string)$transp->UF,
				'transportadora_cpf_cnpj' => (string)$transp->CNPJ,
				'transportadora_ie' => (int)$transp->IE,
				'transportadora_endereco' => (string)$transp->xEnder,
				'frete_quantidade' => (float)$vol->qVol,
				'frete_especie' => (string)$vol->esp,
				'frete_marca' => '',
				'frete_numero' => 0,
				'frete_tipo' => (int)$modFrete,
				'veiculo_placa' => (string)$veic->placa,
				'veiculo_uf' => (string)$veic->UF,
				'frete_peso_bruto' => (float)$vol->pesoB,
				'frete_peso_liquido' => (float)$vol->pesoL,
				'despesa_acessorias' => (float)$xml->NFe->infNFe->total->ICMSTot->vOutro
			];
		}
		$vFrete = number_format(
			(float) $xml->NFe->infNFe->total->ICMSTot->vFrete,
			2,
			",",
			"."
		);
		$vDesc = number_format((float) $xml->NFe->infNFe->total->ICMSTot->vDesc, 2, ",", ".");
		$idFornecedor = 0;
		$fornecedorEncontrado = $this->verificaFornecedor($dadosEmitente['cnpj'] == '' ? $dadosEmitente['cpf'] : $dadosEmitente['cnpj']);
		$dadosAtualizados = [];
		if ($fornecedorEncontrado) {
			$idFornecedor = $fornecedorEncontrado->id;
		} else {
			array_push($dadosAtualizados, "Fornecedor cadastrado com sucesso");
			$idFornecedor = $this->cadastrarFornecedor($dadosEmitente);
		}

		$idTransportadora = 0;
		if ($transportadoraDoc != null) {
			$transportadoraEncontrada = $this->verificaTransportadora($transportadoraDoc);
			if ($transportadoraEncontrada) {
				$idTransportadora = $transportadoraEncontrada->id;
			} else {
				array_push(
					$dadosAtualizados,
					"Transportadora cadastrada com sucesso"
				);
				$idTransportadora = $this->cadastrarTransportadora($transportadora);
			}
		}
		$seq = 0;
		$itens = [];
		$contSemRegistro = 0;
		$config = ConfigNota::where('empresa_id', request()->empresa_id)
		->first();
		$tributacao = Tributacao::where('empresa_id', request()->empresa_id)
		->first();
		foreach ($xml->NFe->infNFe->det as $item) {
			$trib = Devolucao::getTrib($item->imposto);
			$item = [
				'codigo' => $item->prod->cProd,
				'xProd' => $item->prod->xProd,
				'ncm' => $item->prod->NCM,
				'vFrete' => $item->prod->vFrete ?? 0,
				'cfop' => $item->prod->CFOP,
				'unidade_medida' => $item->prod->uCom,
				'vUnCom' => $item->prod->vUnCom,
				'qCom' => $item->prod->qCom,
				'codBarras' => $item->prod->cEAN ?? '',
				'CEST' => $item->prod->CEST ?? 0,
				'cst_csosn' => $trib['cst_csosn'],
				'cst_pis' => $trib['cst_pis'],
				'cst_cofins' => $trib['cst_cofins'],
				'cst_ipi' => $trib['cst_ipi'],
				'perc_icms' => $trib['pICMS'],
				'perc_pis' => $trib['pPIS'],
				'perc_cofins' => $trib['pCOFINS'],
				'perc_ipi' => $trib['pIPI'],
				'pRedBC' => $trib['pRedBC'],
				'modBCST' => $trib['modBCST'],
				'vBCST' => $trib['vBCST'],
				'pICMSST' => $trib['pICMSST'],
				'vICMSST' => $trib['vICMSST'],
				'pMVAST' => $trib['pMVAST'],
				'codigo_anp' => $trib['codigo_anp'] ?? 0,
				'valor_partida' => $trib['valor_partida'] ?? 0,
				'perc_glp' => $trib['perc_glp'] ?? 0,
				'perc_gnn' => $trib['perc_gnn'] ?? 0,
				'perc_gni' => $trib['perc_gni'] ?? 0,
			];
			array_push($itens, $item);
		}
		$chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
		$dadosNf = [
			'chave' => $chave,
			'vProd' => $xml->NFe->infNFe->total->ICMSTot->vProd,
			'indPag' => $xml->NFe->infNFe->ide->indPag,
			'nNf' => $xml->NFe->infNFe->ide->nNF,
			'vFrete' => $vFrete,
			'vDesc' => $vDesc,
		];
		$fatura = [];
		if (!empty($xml->NFe->infNFe->cobr->dup)) {
			foreach ($xml->NFe->infNFe->cobr->dup as $dup) {
				$titulo = $dup->nDup;
				$vencimento = $dup->dVenc;
				$vencimento = explode('-', $vencimento);
				$vencimento = $vencimento[2] . "/" . $vencimento[1] . "/" . $vencimento[0];
				$vlr_parcela = number_format((float) $dup->vDup, 2, ",", ".");
				$parcela = [
					'numero' => $titulo,
					'vencimento' => $vencimento,
					'valor_parcela' => $vlr_parcela
				];
				array_push($fatura, $parcela);
			}
		}
		$config = ConfigNota::where('empresa_id', request()->empresa_id)
		->first();
		$naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)
		->get();
		$transportadoras = Transportadora::where('empresa_id', request()->empresa_id)
		->get();

		$nameArchive = $chave . ".xml";
		$pathXml = $chave . ".xml";
		file_put_contents($chave . ".xml", $xml);

		$tipoFrete = 0;
		if ($transportadora != null) {
			$tipoFrete = $transportadora['frete_tipo'];
		}
		return view('devolucao.view_xml', compact(
			'fatura',
			'tipoFrete',
			'dadosNf',
			'naturezas',
			'config',
			'cidade',
			'transportadora',
			'dadosEmitente',
			'transportadoras',
			'dadosAtualizados',
			'itens',
			'idTransportadora',
			'idFornecedor',
			'pathXml',
			'nameArchive'
		));
	}

	private function verificaTransportadora($cnpj)
	{
		$transp = Transportadora::verificaCadastrado($cnpj);
		return $transp;
	}

	private function cadastrarTransportadora($transp)
	{
		$cidade = Cidade::where('nome', $transp['transportadora_cidade'])
		->first();
		if ($cidade == null) {
			$cidade = Cidade::where('uf', $transp['transportadora_uf'])
			->first();
		}
		$result = Transportadora::create([
			'razao_social' => $transp['transportadora_nome'],
			'cnpj_cpf' => $transp['transportadora_cpf_cnpj'],
			'logradouro' => $transp['transportadora_endereco'],
			'cidade_id' => $cidade == null ? 1 : $cidade->id,
			'empresa_id' => request()->empresa_id
		]);
		return $result->id;
	}
}
