<?php

namespace App\Helpers;

use App\Models\Estoque;
use App\Models\Produto;
use Illuminate\Support\Str;
use App\Models\AlteracaoEstoque;
use App\Models\ProdutoEcommerce;
use App\Models\ImagemProdutoEcommerce;
use App\Helpers\StockMove;
use App\Models\CategoriaProdutoDelivery;
use App\Models\CategoriaProdutoEcommerce;
use App\Models\ConfigNota;
use App\Models\ImagensProdutoDelivery;
use App\Models\ProdutoDelivery;
use App\Services\IbptService;
use App\Models\ProdutoIbpt;
use App\Utils\UploadUtil;
use App\Utils\Util;

class ProdutoGrade
{

	public function store($request, $file_name, $randUpdate = null)
	{
		if ($randUpdate == null) {
			$rand = Str::random(20);
		} else {
			$rand = $randUpdate;
		}

		for ($i = 0; $i < sizeof($request->tamanho_grade); $i++) {
			$locais = json_encode($request->local);
			if ($request->local == null) {
				$locais = "[-1]";
			}
			$request->merge([
				'valor_venda' => str_replace(",", ".", $request->valor_grade[$i]),
				'codBarras' => __convert_value_bd($request->codigo_barras_grade[$i]) ? __convert_value_bd($request->codigo_barras_grade[$i]) : 'SEM GTIN',
				'referencia_grade' => $rand,
				'grade' => true,
				'referencia' => $request->referencia ?? '',
				'imagem' => $file_name,
				'str_grade' => $request->tamanho_grade[$i],
				'CEST' => $request->CEST ?? '',
				'unidade_tributavel' => $request->unidade_tributavel != '',
				'quantidade_tributavel' => $request->quantidade_tributavel != '' ? __convert_value_bd($request->quantidade_tributavel) : '',
				'renavam' => $request->renavam ?? '',
				'placa' => $request->placa ?? '',
				'chassi' => $request->chassi ?? '',
				'combustivel' => $request->combustivel ?? '',
				'ano_modelo' => $request->ano_modelo ?? '',
				'cor_veiculo' => $request->cor_veiculo ?? '',
				'CST_CSOSN_EXP' => $request->input('CST_CSOSN_EXP') ?? '',
				'cBenef' => $request->cBenef ? $request->cBenef : '',
				'estoque_minimo' => $request->estoque_minimo ?? 0,
				'limite_maximo_desconto' => $request->limite_maximo_desconto ?? 0,
				'alerta_vencimento' => $request->alerta_vencimento ?? 0,
				'referencia_balanca' => $request->referencia_balanca ?? 0,
				'perc_comissao' => $request->perc_comissao ?? 0,
				'tipo_dimensao' => $request->tipo_dimensao ?? '',
				'perc_glp' => $request->perc_glp ?? 0,
				'perc_gnn' => $request->perc_gnn ?? 0,
				'perc_gni' => $request->perc_gni ?? 0,
				'valor_partida' => $request->valor_partida ?? 0,
				'largura' => $request->largura ?? 0,
				'altura' => $request->altura ?? 0,
				'comprimento' => $request->comprimento ?? 0,
				'peso_liquido' => $request->peso_liquido ?? 0,
				'peso_bruto' => $request->peso_bruto ?? 0,
				'lote' => $request->lote ?? 0,
				'vencimento' => $request->vencimento ?? '',
				'perc_ipi' => $request->perc_ipi ?? 0,
				'perc_iss' => $request->perc_iss ?? 0,
				'perc_icms_interestadual' => $request->perc_icms_interestadual ?? 0,
				'perc_icms_interno' => $request->perc_icms_interno ?? 0,
				'perc_fcp_interestadual' => $request->perc_fcp_interestadual ?? 0,
				'info_tecnica_composto' => $request->info_tecnica_composto ?? '',
				'tela_pedido_id' => $request->tela_pedido_id ?? 0,
				'valor_locacao' => $request->valor_locacao ?? 0,
				'locais' => $locais
			]);

			$produto = Produto::create($request->all());

			AlteracaoEstoque::create([
				'produto_id' => $produto->id,
				'usuario_id' => get_id_user(),
				'quantidade' => $request->quantidade_grade[$i],
				'tipo' => 'incremento',
				'observacao' => '',
				'empresa_id' => $request->empresa_id
			]);

			$stockMove = new StockMove();
			$result = $stockMove->pluStock(
				$produto->id,
				__convert_value_bd($request->quantidade_grade[$i]),
			);

			if ($request->delivery) {
				$this->salvarProdutoNoDelivery($request, $produto, $file_name);
			}

			if ($request->ecommerce) {
				$this->salvarProdutoEcommerce($request, $produto, $file_name);
			}
		}
		return "ok";
	}

	private function salvarProdutoNoDelivery($request, $produto, $file_name)
    {
        $categoria = CategoriaProdutoDelivery::where('empresa_id', $request->empresa_id)->first();
        $valor = __convert_value_bd($request->valor_venda);
        $produtoDelivery = [
            'status' => 1,
            'produto_id' => $produto->id,
            'descricao' => $request->descricao ?? '',
            'ingredientes' => '',
            'limite_diario' => -1,
            'categoria_id' => $categoria->id,
            'valor' => $valor,
            'valor_anterior' => 0,
            'referencia' => '',
            'empresa_id' => $request->empresa_id
        ];
        $result = ProdutoDelivery::create($produtoDelivery);
        $produtoDelivery = ProdutoDelivery::find($result->id);

        if ($result) {
            $this->salveImagemProdutoDelivery($file_name, $produtoDelivery);
        }
    }

    private function salveImagemProdutoDelivery($file_name, $produtoDelivery)
    {
        if ($file_name != "") {
            copy(public_path('uploads/products/') . $file_name, public_path('uploads/produtoDelivery/') . $file_name);

            ImagensProdutoDelivery::create(
                [
                    'produto_id' => $produtoDelivery->id,
                    'path' => $file_name
                ]
            );
        } else {
        }
    }


	private function salvarProdutoEcommerce($request, $produto, $file_name)
    {
        $categoriaFirst =  CategoriaProdutoEcommerce::where('empresa_id', $request->empresa_id)
            ->first();
        $produtoEcommerce = [
            'produto_id' => $produto->id,
            'categoria_id' => $request->categoria_ecommerce_id ? $request->categoria_ecommerce_id : $categoriaFirst->id,
            'empresa_id' => $request->empresa_id,
            'descricao' => $request->descricao ?? '',
            'controlar_estoque' => $request->input('ecommerce_controlar_estoque') ? true : false,
            'status' => $request->input('status') ? true : false,
            'valor' => $request->valor_ecommerce ? __convert_value_bd($request->valor_ecommerce) : __convert_value_bd($request->valor_venda),
            'destaque' => $request->input('destaque') ? true : false
        ];
        if ($produto->ecommerce) {
            $result = $produto->ecommerce;
            $result->fill($produtoEcommerce)->save();
        } else {
            $result = ProdutoEcommerce::create($produtoEcommerce);
        }
        $produtoEcommerce = ProdutoEcommerce::find($result->id);
        if ($result) {
            $this->salveImagemProdutoEcommerce($file_name, $produtoEcommerce);
        }
    }


    private function salveImagemProdutoEcommerce($file_name, $produtoEcommerce)
    {
        if ($file_name != "") {
            copy(public_path('uploads/products/') . $file_name, public_path('uploads/produtoEcommerce/') . $file_name);

            ImagemProdutoEcommerce::create(
                [
                    'produto_id' => $produtoEcommerce->id,
                    'path' => $file_name
                ]
            );
        } else {
        }
    }

	
	public function update($request, $file_name, $randUpdate = null)
	{
		if ($randUpdate == null) {
			$rand = Str::random(20);
		} else {
			$rand = $randUpdate;
		}
		$locais = json_encode($request->local);
		if ($request->local == null) {
			$locais = "[-1]";
		}
		$combinacoes = json_decode($request->combinacoes);
		if (!$combinacoes) return "erro";

		foreach ($combinacoes as $key => $comb) {
			if ($key > 0 && $randUpdate != null) {
				$request->merge([
					'valor_venda' => __convert_value_bd($request->valor_venda),
					'valor_compra' =>  __convert_value_bd($request->valor_compra),
					'referencia' => $request->referencia ?? '',
					'estoque_inicial' => $request->estoque_inicial ?? 0,
					'estoque_minimo' => $request->estoque_minimo ?? 0,
					'cor' => $request->cor ?? 0,
					'valor_livre' => $request->valor_livre ?? false,
					'cListServ' => $request->cListServ ?? '',
					'descricao_anp' => $request->descricao_anp ?? '',
					'imagem' => $file_name,
					'info_tecnica_composto' => $request->info_tecnica_composto ?? '',
					'limite_maximo_desconto' => $request->limite_maximo_desconto ?? 0,
					'alerta_vencimento' => $request->alerta_vencimento ?? 0,
					'CEST' => $request->CEST ?? '',
					'referencia_balanca' => $request->referencia_balanca ?? 0,
					'perc_comissao' => $request->perc_comissao ?? 0,
					'tipo_dimensao' => $request->tipo_dimensao ?? '',
					'perc_glp' => $request->perc_glp ?? 0,
					'perc_gnn' => $request->perc_gnn ?? 0,
					'perc_gni' => $request->perc_gni ?? 0,
					'valor_partida' => $request->valor_partida ?? 0,
					'unidade_tributavel' => $request->unidade_tributavel ?? '',
					'quantidade_tributavel' => $request->quantidade_tributavel ?? 0,
					'largura' => $request->largura ?? 0,
					'altura' => $request->altura ?? 0,
					'comprimento' => $request->comprimento ?? 0,
					'peso_liquido' => $request->peso_liquido ?? 0,
					'peso_bruto' => $request->peso_bruto ?? 0,
					'lote' => $request->lote ?? 0,
					'vencimento' => $request->vencimento ?? '',
					'renavam' => $request->renavam ?? '',
					'placa' => $request->placa ?? '',
					'chassi' => $request->chassi ?? '',
					'combustivel' => $request->combustivel ?? '',
					'ano_modelo' => $request->ano_modelo ?? '',
					'cor_veiculo' => $request->cor_veiculo ?? '',
					'perc_ipi' => $request->perc_ipi ?? 0,
					'codBarras' => $request->codBarras ?? 0,
					'perc_iss' => $request->perc_iss ?? 0,
					'cBenef' => $request->cBenef ?? 0,
					'perc_icms_interestadual' => $request->perc_icms_interestadual ?? 0,
					'perc_icms_interno' => $request->perc_icms_interno ?? 0,
					'perc_fcp_interestadual' => $request->perc_fcp_interestadual ?? 0,
					'locais' => $locais
				]);

				try {
					$produto = Produto::create($request->all());
					if ($request->ecommerce) {
						$this->storeProdutoEcommerce($request, $produto, $file_name);
					}
					$estoque = __convert_value_bd($comb->quantidade);

					if ($estoque > 0) {
						$data = [
							'produto_id' => $produto->id,
							'usuario_id' => get_id_user(),
							'quantidade' => $estoque,
							'tipo' => 'incremento',
							'observacao' => '',
							'empresa_id' => $request->empresa_id
						];
						AlteracaoEstoque::create($data);
						$stockMove = new StockMove();
						$result = $stockMove->pluStock(
							$produto->id,
							$estoque,
							str_replace(",", ".", $produto->valor_venda)
						);
					}
				} catch (\Exception $e) {
					echo $e->getMessage();
					// die;
					return $e->getMessage();
				}
			}
		}
		return "ok";
	}

	private function storeProdutoEcommerce($request, $produto, $nomeImagem)
	{
		// $this->_validateEcommerce($request);

		$produtoEcommerce = [
			'produto_id' => $produto->id,
			'categoria_id' => $request->categoria_ecommerce_id,
			'empresa_id' => $request->empresa_id,
			'descricao' => $request->descricao,
			'controlar_estoque' => $request->input('controlar_estoque') ? true : false,
			'status' => $request->input('status') ? true : false,
			'valor' => __convert_value_bd($request->valor_ecommerce),
			'destaque' => $request->input('destaque') ? true : false
		];

		$result = ProdutoEcommerce::create($produtoEcommerce);
		$produtoEcommerce = ProdutoEcommerce::findOrFail($result->id);
		if ($result) {
			$this->storeImagemProdutoEcommerce($nomeImagem, $produtoEcommerce);
		}
	}

	private function storeImagemProdutoEcommerce($nomeImagem, $produtoEcommerce)
	{

		if ($nomeImagem != "") {

			$extensao = substr($nomeImagem, strlen($nomeImagem) - 4, strlen($nomeImagem));
			$novoNome = Str::random(20) . $extensao;
			copy(public_path('imgs_produtos/') . $nomeImagem, public_path('ecommerce/produtos/') . $novoNome);
			// $upload = $file->move(public_path('ecommerce/produtos'), $nomeImagem);

			ImagemProdutoEcommerce::create(
				[
					'produto_id' => $produtoEcommerce->id,
					'img' => $novoNome
				]
			);
		} else {
		}
	}

	private function storeIbpt($produto, $empresa_id)
	{
		$config = ConfigNota::where('empresa_id', $empresa_id)
			->first();

		if ($config->token_ibpt != "") {
			$ibptService = new IbptService($config->token_ibpt, preg_replace('/[^0-9]/', '', $config->cnpj));
			$data = [
				'ncm' => preg_replace('/[^0-9]/', '', $produto->NCM),
				'uf' => $config->UF,
				'extarif' => 0,
				'descricao' => $produto->nome,
				'unidadeMedida' => $produto->unidade_venda,
				'valor' => number_format(0, $config->casas_decimais),
				'gtin' => $produto->codBarras,
				'codigoInterno' => 0
			];
			$resp = $ibptService->consulta($data);
			if (!isset($resp->httpcode)) {
				$dataIbpt = [
					'produto_id' => $produto->id,
					'codigo' => $resp->Codigo,
					'uf' => $resp->UF,
					'descricao' => $resp->Descricao,
					'nacional' => $resp->Nacional,
					'estadual' => $resp->Estadual,
					'importado' => $resp->Importado,
					'municipal' => $resp->Municipal,
					'vigencia_inicio' => $resp->VigenciaInicio,
					'vigencia_fim' => $resp->VigenciaFim,
					'chave' => $resp->Chave,
					'versao' => $resp->Versao,
					'fonte' => $resp->Fonte
				];
				ProdutoIbpt::create($dataIbpt);
			}
		}
	}
}
