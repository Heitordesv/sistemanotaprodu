<?php

namespace App\Http\Controllers\AppFiscal;

use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\Tributacao;
use App\Models\ConfigNota;

class ProdutoController extends Controller
{
	public function all(Request $request){
		$produtos = Produto::
		where('empresa_id', $request->empresa_id)
		->where('inativo', 0)
		->get();

		$config = ConfigNota::
		where('empresa_id', $request->empresa_id)
		->first();

		foreach($produtos as $p){
			$p->categoria;
			$p->listaPreco;
			$p->quantidade_vendas = $p->somaVendas();

			$p->valor_venda = number_format($p->valor_venda, $config->casas_decimais);
			$p->valor_compra = number_format($p->valor_compra, $config->casas_decimais);
		}

		$ps = $produtos->SortByDesc('quantidade_vendas');
		$temp = [];
		foreach($ps as $s){
			array_push($temp, $s);
		}
		return response()->json($temp, 200);
	}

	public function salvar(Request $request){
		
		if($request->id > 0){
			$produto = Produto::find($request->id);

			$produto->nome = $request->nome;
			$produto->categoria_id = $request->categoria_id;
			$produto->cor = $request->cor;
			$produto->valor_venda = __replace($request->valor_venda);
			$produto->NCM = $request->NCM;
			$produto->CST_CSOSN = $request->CST_CSOSN;
			$produto->CST_PIS = $request->CST_PIS;
			$produto->CST_IPI = $request->CST_IPI;
			$produto->CST_COFINS = $request->CST_COFINS;
			$produto->unidade_compra = $request->unidade_compra;
			$produto->unidade_venda = $request->unidade_venda;
			$produto->codBarras = $request->codBarras;
			$produto->valor_livre = $request->valor_livre ?? false;
			$produto->perc_icms = $request->perc_icms;
			$produto->perc_pis = $request->perc_pis;
			$produto->perc_cofins = $request->perc_cofins;
			$produto->referencia = $request->referencia;
			$produto->perc_ipi = $request->perc_ipi;
			$produto->CFOP_saida_estadual = $request->CFOP_saida_estadual;
			$produto->CFOP_saida_inter_estadual = $request->CFOP_saida_inter_estadual;
			$produto->alerta_vencimento = $request->alerta_vencimento;
			$produto->valor_compra = $request->valor_compra;
			$produto->valor_compra = __replace($request->valor_compra);
			$produto->estoque_minimo = $request->estoque_minimo ?? false;


			$produto->largura = $request->largura;
			$produto->comprimento = $request->comprimento;
			$produto->altura = $request->altura;
			$produto->peso_liquido = $request->peso_liquido;
			$produto->peso_bruto = $request->peso_bruto;
			$produto->limite_maximo_desconto = $request->limite_maximo_desconto;


			$res = $produto->save();
		}else{
			$data = [
				'nome' => $request->nome,
				'categoria_id' => $request->categoria_id,
				'cor' => $request->cor ?? '',
				'valor_venda' => __replace($request->valor_venda),
				'NCM' => $request->NCM,
				'CST_CSOSN' => $request->CST_CSOSN,
				'CST_PIS' => $request->CST_PIS,
				'CST_COFINS' => $request->CST_COFINS,
				'CST_IPI' => $request->CST_IPI,
				'unidade_compra' => $request->unidade_compra,
				'unidade_venda' => $request->unidade_venda,
				'composto' => false,
				'codBarras' => $request->codBarras ?? 'SEM GTIN',
				'referencia' => $request->referencia ?? '',
				'conversao_unitaria' => 1,
				'valor_livre' => $request->valor_livre ?? false,
				'perc_icms' => $request->perc_icms,
				'perc_pis' => $request->perc_pis,
				'perc_cofins' => $request->perc_cofins,
				'perc_ipi' => $request->perc_ipi,
				'CFOP_saida_estadual' => $request->CFOP_saida_estadual,
				'CFOP_saida_inter_estadual' => $request->CFOP_saida_inter_estadual,
				'codigo_anp' => '',
				'descricao_anp' => '',
				'perc_iss' => 0,
				'cListServ' => '',
				'imagem' => '',
				'alerta_vencimento' => $request->alerta_vencimento ?? 0,
				'valor_compra' => __replace($request->valor_compra),
				'gerenciar_estoque' => $request->gerenciar_estoque ?? false,
				'estoque_minimo' => $request->estoque_minimo ?? 0,

				'empresa_id' => $request->empresa_id,
				'largura' => $request->largura ?? 0,
				'comprimento' => $request->comprimento ?? 0,
				'altura' => $request->altura ?? 0,
				'peso_liquido' => $request->peso_liquido ?? 0,
				'peso_bruto' => $request->peso_bruto ?? 0,
				'limite_maximo_desconto' => $request->limite_maximo_desconto ?? 0,

				'pRedBC' => 0,
				'cBenef' => '',
				'percentual_lucro' => $request->percentual_lucro ?? 0,
				'CST_CSOSN_EXP' => '',

				'referencia_grade' => '',
				'grade' => 0,
				'str_grade' => '',
				'perc_glp' => 0,
				'perc_gnn' => 0,
				'perc_gni' => 0,
				'valor_partida' => 0,
				'unidade_tributavel' => '',
				'quantidade_tributavel' => 0,
				'perc_icms_interestadual' => 0,
				'perc_icms_interno' => 0,
				'perc_fcp_interestadual' => 0,
				'inativo' => 0, 
				'CEST' => ''

			];
			$res = Produto::create($data);
		}

		return response()->json($res, 200);
	}

	public function delete(Request $request){
		$produto = Produto::find($request->id);
		$delete = $produto->delete();
		return response()->json($delete, 200);
	}

	public function dadosParaCadastro(){
		$data = [
			'unidades_medida' => Produto::unidadesMedida(),
			'listaCSTCSOSN' => $this->itetable(Produto::listaCSTCSOSN()),
			'listaCST_PIS_COFINS' => $this->itetable(Produto::listaCST_PIS_COFINS()),
			'listaCST_IPI' => $this->itetable(Produto::listaCST_IPI()),
			'lista_ANP' => Produto::lista_ANP()
		];
		return response()->json($data, 200);
	}

	private function itetable($array){
		$temp = [];
		foreach($array as $key => $a){
			$t = [
				'cod' => $key,
				'value' => $a
			];
			array_push($temp, $t);
		}
		return $temp;
	}

	public function tributosPadrao(Request $request){
		try{
			$tributos = Tributacao::
			where('empresa_id', $request->empresa_id)
			->first();
			$config = ConfigNota::
			where('empresa_id', $request->empresa_id)
			->first();
			$natureza = Produto::
			firstNatureza($request->empresa_id);

			if($config != null){
				$tributos->CST_CSOSN_padrao = $config->CST_CSOSN_padrao;
				$tributos->CST_COFINS_padrao = $config->CST_COFINS_padrao;
				$tributos->CST_PIS_padrao = $config->CST_PIS_padrao;
				$tributos->CST_IPI_padrao = $config->CST_IPI_padrao;
				$tributos->percentual_lucro_padrao = $config->percentual_lucro_padrao;
			}

			if($natureza != null){
				$tributos->CFOP_saida_estadual = $natureza->CFOP_saida_estadual;
				$tributos->CFOP_saida_inter_estadual = $natureza->CFOP_saida_inter_estadual;
			}

			return response()->json($tributos, 200);
		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}
	}

	public function salvarImagem(Request $request){
		try{
			$imagem = $request->file;

			$produto_id = $request->produto_id;
			$public = env('SERVIDOR_WEB') ? 'public/' : '';

			$nome = md5(rand(100000, 99999999999));

			$produto = Produto::find($produto_id);

			if($produto->imagem != ''){
				if(file_exists($public.'imgs_produtos/'.$usuario->imagem)){
					unlink($public.'imgs_produtos/'.$usuario->imagem);
				}
			}

			$imgData = str_replace('data:image/jpeg;base64,', '', $imagem);
			$imgData = str_replace('data:image/jpg;base64,', '', $imgData);
			$imgData = str_replace(' ', '+', $imgData);
			$imgData = base64_decode($imgData);

			$produto->imagem = $nome.'.jpg';
			$produto->save();
			file_put_contents($public.'imgs_produtos/'.$nome.'.jpg', $imgData);

			return response()->json($nome.'.jpg', 201);
		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}
	}
}