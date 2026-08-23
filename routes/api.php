<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\API\GraficoController;
use App\Http\Controllers\InterPixController;
use App\Http\Controllers\API\VendaCaixaController;
use App\Http\Controllers\VendaPixController;

use App\Http\Controllers\BlingController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which is
| assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('graficos/curvaABC', [GraficoController::class, 'curvaABC']);

Route::prefix('bling')->group(function() {
    Route::get('token/{empresa_id}', [BlingController::class, 'getToken']);
    Route::post('token/save', [BlingController::class, 'saveToken']);
    Route::get('token/refresh/{empresa_id}', [BlingController::class, 'refreshToken']);
});

Route::post('/webhook/whatsapp-status', [WebhookController::class, 'handle']);
Route::get('/interpix/token', [InterPixController::class, 'autenticar']);
Route::get('/venda-pix/gerar-pix/{id}', [VendaPixController::class, 'gerarPix']);

Route::post('/api/venda-caixa/aprovar/{id}', [VendaCaixaController::class, 'aprovarPedido']);

Route::middleware(['hashEmpresa'])->group(function () {

     Route::group(['prefix' => 'nfe'], function () {
        Route::get('/consultaCadastro', 'API\\NFeController@consultaCadastro');
        Route::post('/transmitir', 'API\\NFeController@transmitir')->middleware('limiteNFe');
        Route::post('/consulta-nfe', 'API\\NFeController@consultarNfe');
        Route::post('/cancelar-nfe', 'API\\NFeController@cancelar');
        Route::post('/corrigir-nfe', 'API\\NFeController@corrigir');
        Route::post('/inutiliza-nfe', 'API\\NFeController@inutiliza');
        Route::post('/consulta-status-sefaz', 'API\\NFeController@consultaStatusSefaz');
    });

    Route::group(['prefix' => 'nferemessa'], function () {
        Route::post('/transmitir', 'API\\NFeRemessaController@transmitir');
        Route::post('/corrigir-nfe', 'API\\NFeRemessaController@corrigir');
        Route::post('/consulta-nfe', 'API\\NFeRemessaController@consultarNfe');
        Route::post('/cancelar-nfe', 'API\\NFeRemessaController@cancelar');
        Route::post('/inutiliza-nfe', 'API\\NFeRemessaController@inutilizar');
    });

    Route::group(['prefix' => 'nfce'], function () {
        Route::post('/transmitir', 'API\\NFCeController@transmitir')->middleware('limiteNFCe');
        Route::post('/consultar', 'API\\NFCeController@consultar');
        Route::post('/cancelar', 'API\\NFCeController@cancelar');
        Route::post('/inutilizar', 'API\\NFCeController@inutilizar');
        Route::post('/consulta-status-sefaz', 'API\\NFCeController@consultaStatusSefaz');
    });

    Route::group(['prefix' => 'nfe_entrada'], function () {
        Route::post('/transmitir', 'API\\NFeEntradaController@transmitir');
        Route::post('/consultar', 'API\\NFeEntradaController@consultar');
        Route::post('/cancelar', 'API\\NFeEntradaController@cancelar');
        Route::post('/corrigir', 'API\\NFeEntradaController@corrigir');
    });

    Route::group(['prefix' => 'devolucao'], function () {
        Route::post('/transmitir', 'API\\DevolucaoController@transmitir');
        Route::post('/consultar', 'API\\DevolucaoController@consultar');
        Route::post('/cancelar', 'API\\DevolucaoController@cancelar');
        Route::post('/corrigir', 'API\\DevolucaoController@corrigir');
    });

    Route::group(['prefix' => 'dfe'], function () {
        Route::post('/novos-documentos', 'API\\DfeController@novosDocumentos');
    });

    Route::group(['prefix' => 'controleCozinha'], function () {
        Route::get('/buscar', 'API\\ControleCozinhaController@buscar')->name('controleCozinha.buscar');
    });


    Route::group(['prefix' => 'agendamentos'], function () {
        Route::get('/all', 'API\\AgendamentoController@all');
    });

    Route::group(['prefix' => 'servicos'], function () {
        Route::get('/find/{id}', 'API\\ServicoController@find');
    });

    Route::group(['prefix' => 'pedidos'], function () {
        Route::get('/comanda/{id}', 'API\\PedidoController@comanda');
        Route::get('/comandaHtml/{id}', 'API\\PedidoController@comandaHtml');
        Route::get('/findAdicional/{id}', 'API\\PedidoController@findAdicional');
    });

    Route::group(['prefix' => 'cte'], function () {
        Route::post('/transmitir', 'API\\CteController@transmitir')->middleware('limiteCTe');
        Route::post('/consultar', 'API\\CteController@consultar');
        Route::post('/cancelar', 'API\\CteController@cancelar');
        Route::post('/corrigir', 'API\\CteController@corrigir');
        Route::post('/inutiliza', 'API\\CteController@inutiliza');
    });

    Route::group(['prefix' => 'cteOs'], function () {
        Route::post('/transmitir', 'API\\CteOsController@transmitir');
        Route::post('/consultar', 'API\\CteOsController@consultar');
        Route::post('/cancelar', 'API\\CteOsController@cancelar');
        Route::post('/corrigir', 'API\\CteOsController@corrigir');
        Route::post('/inutiliza', 'API\\CteOsController@inutiliza');
    });

    Route::group(['prefix' => 'mdfe'], function () {
        Route::post('/transmitir', 'API\\MdfeController@transmitir')->middleware('limiteMDFe');
        Route::post('/consultar', 'API\\MdfeController@consultar');
        Route::post('/cancelar', 'API\\MdfeController@cancelar');
    });

    Route::group(['prefix' => 'nfse'], function () {
        Route::post('/transmitir', 'API\\NfseController@transmitir');
        Route::post('/consultar', 'API\\NfseController@consultar');
        Route::post('/cancelar', 'API\\NfseController@cancelar');
    });

    Route::group(['prefix' => 'graficos'], function () {
    Route::get('/vendasAnual', 'API\\GraficoController@vendasAnual');
    Route::get('/produtos', 'API\\GraficoController@produtos');
    Route::get('/contasReceber', 'API\\GraficoController@contasReceber');
    Route::get('/contasPagar', 'API\\GraficoController@contasPagar');
    Route::get('/boxConsulta', 'API\\GraficoController@boxConsulta');
    Route::get('/getDataCards', 'API\\GraficoController@getDataCards');
    Route::get('/bairrosMaisPedidos', 'API\\GraficoController@bairrosMaisPedidos');
    Route::get('/origemVisitas', 'API\\GraficoController@origemVisitas');
    Route::get('/origemVendas', 'API\\GraficoController@origemVendas');
    Route::get('/opcao_deliveryMaisPedidos', 'API\\GraficoController@opcao_deliveryMaisPedidos');
    Route::get('/resumo_pedidosMaisPedidos', 'API\\GraficoController@resumo_pedidosMaisPedidos');
    Route::get('/categoriasConta', 'API\\GraficoController@categoriasConta');

    // **Fluxo anual no mesmo padr«ªo**
    Route::get('/fluxoAnual', 'API\\GraficoController@fluxoAnual');
});

Route::get('/fluxoAnual', [GraficoController::class, 'fluxoAnual']);

    Route::group(['prefix' => 'notificacoes'], function () {
        Route::get('/', 'API\\NotificacaoController@index');
    });

    Route::get('/payment-consulta/{code}', 'API\\HelperController@consultaPagamentoPix');
    Route::get('/conta-bancaria-get/{id}', 'API\\HelperController@contaBancaria');

    Route::group(['prefix' => 'produtosDelivery'], function () {
        Route::get('/filtroCategoria', 'API\\ProdutoDeliveryController@filtroCategoria');
        Route::get('/filtroAdicionais', 'API\\ProdutoDeliveryController@filtroAdicionais');
        Route::get('/tamanhosPizza', 'API\\ProdutoDeliveryController@tamanhosPizza');
        Route::get('/sabores', 'API\\ProdutoDeliveryController@sabores');
        Route::get('/valorPizza', 'API\\ProdutoDeliveryController@valorPizza');
    });

});

Route::get('/cidadePorNome/{nome}', 'API\\HelperController@cidadePorNome');
Route::get('/cidadePorCodigoIbge/{codigo}', 'API\\HelperController@cidadePorCodigoIbge');
Route::get('/buscaCidades', 'API\\HelperController@buscaCidades');

Route::group(['prefix' => 'fornecedor'], function () {
    Route::get('/pesquisa', 'API\\FornecedorController@pesquisa');
    Route::post('/store', 'API\\FornecedorController@store');
    Route::get('/find/{id}', 'API\\FornecedorController@find');
});

Route::group(['prefix' => 'cliente'], function () {
    Route::get('/pesquisa', 'API\\ClienteController@pesquisa');
    Route::get('/find/{id}', 'API\\ClienteController@find');
    Route::post('/store', 'API\\ClienteController@store');
});


Route::group(['prefix' => 'conta-pagar'], function () {
    Route::get('/recorrencia', 'API\\ContaPagarController@recorrencia');
});

Route::group(['prefix' => 'conta-receber'], function () {
    Route::get('/recorrencia', 'API\\ContaReceberController@recorrencia');
});

Route::group(['prefix' => 'produtos'], function () {
    Route::get('/getBarcode', 'API\\ProdutoController@getBarcode');
    Route::post('/store', 'API\\ProdutoController@store');
    Route::get('/pesquisa', 'API\\ProdutoController@pesquisa');
    Route::get('/find/{id}', 'API\\ProdutoController@find');
    Route::get('/findByBarcode', 'API\\ProdutoController@findByBarcode');
    Route::get('/findByBarcodeReference', 'API\\ProdutoController@findByBarcodeReference');
    Route::get('/linhaProdutoCompra', 'API\\ProdutoController@linhaProdutoCompra');
    Route::get('/linhaParcelaCompra', 'API\\ProdutoController@linhaParcelaCompra');
    Route::post('/storeProdutoRapido', 'API\\ProdutoController@storeProdutoRapido');
    Route::get('/linhaProdutoReceita', 'API\\ProdutoController@linhaProdutoReceita');
    Route::get('/montarGrade', 'API\\ProdutoController@montarGrade');
    Route::get('/findProdRemessa', 'API\\ProdutoController@findProdRemessa');
});

Route::group(['prefix' => 'categorias'], function () {
    Route::post('/storeCategoria', 'API\\CategoriaController@storeCategoria');
    Route::post('/storesubCategoria', 'API\\CategoriaController@storesubCategoria');
    Route::get('/buscarSubCategoria', 'API\\CategoriaController@buscarSubCategoria');
});

Route::group(['prefix' => 'marcas'], function () {
    Route::post('/store', 'API\\MarcaController@store');
});

Route::group(['prefix' => 'usuarios'], function () {
    Route::post('/set-theme', 'API\\UsuarioController@setTheme');
    Route::post('/avisoSonoro', 'API\\UsuarioController@avisoSonoro');
});

Route::group(['prefix' => 'transportadora'], function () {
    Route::post('/store', 'API\\TransportadoraController@store');
});

Route::group(['prefix' => 'categoriaEcommerce'], function () {
    Route::post('/storeCate goria', 'API\\CategoriaEcommerceController@storeCategoria');
    Route::post('/storesubCategoria', 'API\\CategoriaEcommerceController@storesubCategoria');
    Route::get('/buscarSubCategoria', 'API\\CategoriaEcommerceController@buscarSubCategoria');
});

Route::group(['prefix' => 'vendas'], function () {
    Route::get('/linhaProdutoVenda', 'API\\VendaController@linhaProdutoVenda');
    Route::get('/linhaParcelaVenda', 'API\\VendaController@linhaParcelaVenda')->name('api.vendas.linhaParcelaVenda');
    Route::get('/linhaParcelaVendaPersonalizado', 'API\\VendaController@linhaParcelaVendaPersonalizado')->name('api.vendas.linhaParcelaVendaPersonalizado');
    Route::get('/linhaProdutoOrcamento', 'API\\VendaController@linhaProdutoOrcamento');
});

Route::group(['prefix' => 'pdv'], function () {
    Route::post('/store', 'API\\VendaCaixaController@store');
});

Route::group(['prefix' => 'produtosEcommerce'], function () {
    Route::post('/store', 'API\\ProdutoController@store');
});

Route::group(['prefix' => 'cotacao'], function () {
    Route::get('/linhaProduto', 'API\\CotacaoController@linhaProduto');
});

Route::group(['prefix' => 'cte'], function () {
    Route::get('/linhaInformacoes', 'API\\CteController@linhaInformacoes');
});

Route::group(['prefix' => 'mdfe'], function () {
    Route::get('/linhaInfoDescarregamento', 'API\\MdfeController@linhaInfoDescarregamento');
    Route::get('/vendasAprovadas', 'API\\MdfeController@vendasAprovadas');
});

Route::group(['prefix' => 'aberturaCaixa'], function () {
    Route::get('/verificaHoje', 'API\\AberturaCaixaController@verificaHoje');
});

Route::group(['prefix' => 'frenteCaixa'], function () {
    Route::get('/linhaProdutoVenda', 'API\\FrontBoxController@linhaProdutoVenda');
    Route::get('/linhaParcelaVenda', 'API\\FrontBoxController@linhaParcelaVenda');
});

Route::group(['prefix' => 'ordemServico'], function () {
    Route::get('/linhaServico', 'API\\OrderController@linhaServico');
    Route::get('/find/{id}', 'API\\OrderController@find');
    Route::get('/findFuncionario/{id}', 'API\\OrderController@findFuncionario');
    Route::get('/linhaFuncionario', 'API\\OrderController@linhaFuncionario');
});

//rotas de delivery
Route::middleware(['authDelivery'])->group(function () {
    Route::group(['prefix' => 'delivery'], function(){
        Route::get('/categorias', 'Delivery\\ProdutoController@all');
        Route::get('/produto/{id}', 'Delivery\\ProdutoController@find');
        Route::get('/config', 'Delivery\\ConfigController@index');
        Route::get('/cupom', 'Delivery\\ConfigController@cupom');

        Route::post('/endereco-save', 'Delivery\\ClienteController@enderecoSave');
        Route::post('/endereco-update', 'Delivery\\ClienteController@enderecoUpdate');
        Route::post('/update-endereco-padrao', 'Delivery\\ClienteController@updateEnderecoPadrao');

        Route::post('/login', 'Delivery\\ClienteController@login');
        Route::post('/send-code', 'Delivery\\ClienteController@sendCode');
        Route::post('/refresh-code', 'Delivery\\ClienteController@refreshCode');
        Route::post('/cliente-save', 'Delivery\\ClienteController@clienteSave');
        Route::post('/cliente-update', 'Delivery\\ClienteController@clienteUpdate');
        Route::post('/cliente-update-senha', 'Delivery\\ClienteController@clienteUpdateSenha');
        Route::get('/find-cliente', 'Delivery\\ClienteController@findCliente');
        Route::post('/pedido-save', 'Delivery\\PedidoController@save');

        Route::get('/adicionais', 'Delivery\\ProdutoController@adicionais');
        Route::get('/carrossel', 'Delivery\\ProdutoController@carrossel');
        Route::get('/bairros', 'Delivery\\ConfigController@bairros');
        Route::post('/gerar-qrcode', 'Delivery\\PedidoController@gerarQrcode');
        Route::post('/status-pix', 'Delivery\\PedidoController@consultaPix');
        Route::post('/ultimo-pedido-confirmar', 'Delivery\\PedidoController@ultimoPedidoParaConfirmar');
        Route::post('/consulta-pedido-lido', 'Delivery\\PedidoController@consultaPedidoLido');

    });
});

//rotas de delivery cardapio
Route::middleware(['authDelivery'])->group(function () {
    Route::group(['prefix' => 'cardapio'], function(){
        Route::post('/open-table', 'Cardapio\\PedidoController@openTable');
        Route::post('/get-pedido', 'Cardapio\\PedidoController@getPedido');
        Route::post('/pedido-save', 'Cardapio\\PedidoController@save');

        Route::get('/mesas', 'Cardapio\\PedidoController@mesas');
    });
});

//rotas app

Route::group(['prefix' => 'appFiscal'],function(){

    Route::group(['prefix' => 'clientes'],function(){
        Route::get('/', 'AppFiscal\\ClienteController@clientes')->middleware('authApp');
        Route::post('/salvar', 'AppFiscal\\ClienteController@salvar');
        Route::post('/delete', 'AppFiscal\\ClienteController@delete');
        Route::get('/consultaCnpj', 'AppFiscal\\ClienteController@consultaCnpj');
    });

    Route::group(['prefix' => 'fornecedores'],function(){
        Route::get('/', 'AppFiscal\\FornecedorController@fornecedores')->middleware('authApp');
        Route::post('/salvar', 'AppFiscal\\FornecedorController@salvar');
        Route::post('/delete', 'AppFiscal\\FornecedorController@delete');
    });

    Route::group(['prefix' => 'usuario'],function(){
        Route::post('/', 'AppFiscal\\UsuarioController@index');
        Route::post('/salvarImagem', 'AppFiscal\\UsuarioController@salvarImagem');
    });

    Route::group(['prefix' => 'configEmitente'],function(){
        Route::get('/', 'AppFiscal\\ConfigEmitenteController@index')->middleware('authApp');
        Route::get('/dadosCertificado', 'AppFiscal\\ConfigEmitenteController@dadosCertificado')->middleware('authApp');
        Route::post('/salvar', 'AppFiscal\\ConfigEmitenteController@salvar');
        Route::post('/salvarCertificado', 'AppFiscal\\ConfigEmitenteController@salvarCertificado');
    });

    Route::get('/cidades', 'AppFiscal\\ClienteController@cidades')->middleware('authApp');
    Route::get('/ufs', 'AppFiscal\\ClienteController@ufs')->middleware('authApp');

    Route::group(['prefix' => 'categorias'],function(){
        Route::get('/', 'AppFiscal\\CategoriaController@all')->middleware('authApp');
        Route::get('/isDelivery', 'AppFiscal\\CategoriaController@isDelivery')->middleware('authApp');
        Route::post('/salvar', 'AppFiscal\\CategoriaController@salvar');
        Route::post('/delete', 'AppFiscal\\CategoriaController@delete');
    });

    Route::group(['prefix' => 'produtos'],function(){
        Route::get('/', 'AppFiscal\\ProdutoController@all')->middleware('authApp');
        Route::post('/salvar', 'AppFiscal\\ProdutoController@salvar');
        Route::post('/delete', 'AppFiscal\\ProdutoController@delete');
        Route::get('/dadosParaCadastro', 'AppFiscal\\ProdutoController@dadosParaCadastro')->middleware('authApp');
        Route::get('/tributosPadrao', 'AppFiscal\\ProdutoController@tributosPadrao')->middleware('authApp');
        Route::post('/salvarImagem', 'AppFiscal\\ProdutoController@salvarImagem');
        
    });

    Route::group(['prefix' => 'naturezas'],function(){
        Route::get('/', 'AppFiscal\\NaturezaController@index')->middleware('authApp');
    });

    Route::group(['prefix' => 'transportadoras'],function(){
        Route::get('/', 'AppFiscal\\TransportadoraController@index')->middleware('authApp');
    });

    Route::group(['prefix' => 'vendas'],function(){
        Route::get('/', 'AppFiscal\\VendaController@index')->middleware('authApp');
        Route::get('/orcamentos', 'AppFiscal\\VendaController@orcamentos')->middleware('authApp');
        Route::get('/find/{id}', 'AppFiscal\\VendaController@getVenda')->middleware('authApp');
        Route::post('/filtroVendas', 'AppFiscal\\VendaController@filtroVendas');
        Route::get('/tiposDePagamento', 'AppFiscal\\VendaController@tiposDePagamento')->middleware('authApp');
        Route::get('/listaDePrecos', 'AppFiscal\\VendaController@listaDePrecos')->middleware('authApp');
        Route::post('/salvar', 'AppFiscal\\VendaController@salvar');
        Route::post('/salvarOrcamento', 'AppFiscal\\VendaController@salvarOrcamento');
        
        Route::post('/delete', 'AppFiscal\\VendaController@delete');
        Route::post('/deleteOrcamento', 'AppFiscal\\VendaController@deleteOrcamento');

        Route::get('/renderizarDanfe/{id}', 'AppFiscal\\VendaController@renderizarDanfe')->middleware('authApp');
        Route::get('/renderizarXml/{id}', 'AppFiscal\\VendaController@renderizarXml')->middleware('authApp');
        Route::get('/ambiente', 'AppFiscal\\VendaController@ambiente')->middleware('authApp');
    });

    Route::group(['prefix' => 'notaFiscal'],function(){
        Route::post('/transmitir', 'AppFiscal\\NotaFiscalAppController@transmitir')->middleware('limiteNFe');
        Route::post('/cancelar', 'AppFiscal\\NotaFiscalAppController@cancelar');
        Route::post('/corrigir', 'AppFiscal\\NotaFiscalAppController@corrigir');
        Route::post('/consultar', 'AppFiscal\\NotaFiscalAppController@consultar');
        Route::get('/imprimir/{id}', 'AppFiscal\\NotaFiscalAppController@imprimir')->middleware('authApp');
        Route::get('/imprimirCorrecao/{id}', 'AppFiscal\\NotaFiscalAppController@imprimirCorrecao')->middleware('authApp');
        Route::get('/imprimirCancelada/{id}', 'AppFiscal\\NotaFiscalAppController@imprimirCancelada')->middleware('authApp');
        Route::get('/getXml/{id}', 'AppFiscal\\NotaFiscalAppController@getXml')->middleware('authApp');
        Route::get('/renderizarDanfe/{id}', 'AppFiscal\\NotaFiscalAppController@renderizarDanfe');

    });

    Route::group(['prefix' => 'vendasCaixa'],function(){
        Route::get('/', 'AppFiscal\\VendaCaixaController@index')->middleware('authApp');
        Route::post('/salvar', 'AppFiscal\\VendaCaixaController@salvar');
        Route::get('/find/{id}', 'AppFiscal\\VendaCaixaController@getVenda')->middleware('authApp');
        Route::get('/renderizarDanfe/{id}', 'AppFiscal\\VendaCaixaController@renderizarDanfe')->middleware('authApp');
        Route::get('/ambiente', 'AppFiscal\\VendaCaixaController@ambiente')->middleware('authApp');
        Route::post('/filtroVendas', 'AppFiscal\\VendaCaixaController@filtroVendas');
        Route::post('/delete', 'AppFiscal\\VendaCaixaController@delete');
        Route::get('/cupomNaoFiscal/{id}', 'AppFiscal\\VendaCaixaController@cupomNaoFiscal')->middleware('authApp');


        Route::get('/teste', 'AppFiscal\\VendaCaixaController@teste');
        Route::get('/caixaAberto', 'AppFiscal\\VendaCaixaController@caixaAberto');
        Route::post('/abrirCaixa', 'AppFiscal\\VendaCaixaController@abrirCaixa');


    });

    Route::group(['prefix' => 'nfce'],function(){
        Route::post('/transmitir', 'AppFiscal\\NfceAppController@transmitir')->middleware('limiteNFCe');
        Route::get('/imprimir/{id}', 'AppFiscal\\NfceAppController@imprimir')->middleware('authApp');
        Route::post('/cancelar', 'AppFiscal\\NfceAppController@cancelar');
        Route::post('/consultar', 'AppFiscal\\NfceAppController@consultar');
        Route::get('/imprimir/{id}', 'AppFiscal\\NfceAppController@imprimir')->middleware('authApp');
        Route::get('/getXml/{id}', 'AppFiscal\\NfceAppController@getXml')->middleware('authApp');

    });

    Route::group(['prefix' => 'dfe'],function(){
        Route::get('/', 'AppFiscal\\DFeController@index')->middleware('authApp');
        Route::post('/manifestar', 'AppFiscal\\DFeController@manifestar');
        Route::get('/novosDocumentos', 'AppFiscal\\DFeController@novosDocumentos')->middleware('authApp');
        Route::post('/filtroManifestos', 'AppFiscal\\DFeController@filtroManifestos');
        Route::get('/renderizarDanfe/{id}', 'AppFiscal\\DFeController@renderizarDanfe')->middleware('authApp');
        Route::get('/find/{id}', 'AppFiscal\\DFeController@find')->middleware('authApp');
    });

    Route::group(['prefix' => 'inventarios'],function(){
        Route::get('/', 'AppFiscal\\InventarioController@index')->middleware('authApp');
        Route::get('/getItens/{id}', 'AppFiscal\\InventarioController@getItens');
        Route::get('/estados', 'AppFiscal\\InventarioController@estados')
        ->middleware('authApp');
        Route::post('/salvarItem', 'AppFiscal\\InventarioController@salvarItem')->middleware('authApp');
        Route::post('/itemJaIncluso', 'AppFiscal\\InventarioController@itemJaIncluso')->middleware('authApp');
        Route::post('/removeItem', 'AppFiscal\\InventarioController@removeItem')->middleware('authApp');
    });

    Route::group(['prefix' => 'home'],function(){
        Route::get('/dadosGrafico', 'AppFiscal\\HomeController@dadosGrafico')->middleware('authApp');
    });

    Route::group(['prefix' => 'contasReceber'],function(){
        Route::get('/categoriasConta', 'AppFiscal\\ContaReceberController@categoriasConta')->middleware('authApp');
        Route::get('/', 'AppFiscal\\ContaReceberController@contas')->middleware('authApp');
        Route::get('/filtro', 'AppFiscal\\ContaReceberController@filtro')->middleware('authApp');
        Route::post('/salvar', 'AppFiscal\\ContaReceberController@salvar');
        Route::post('/receber', 'AppFiscal\\ContaReceberController@receber');
        Route::post('/delete', 'AppFiscal\\ContaReceberController@delete');
    });

    Route::group(['prefix' => 'contasPagar'],function(){
        Route::get('/categoriasConta', 'AppFiscal\\ContaPagarController@categoriasConta')->middleware('authApp');
        Route::get('/', 'AppFiscal\\ContaPagarController@contas')->middleware('authApp');
        Route::get('/filtro', 'AppFiscal\\ContaPagarController@filtro')->middleware('authApp');
        Route::post('/salvar', 'AppFiscal\\ContaPagarController@salvar');
        Route::post('/pagar', 'AppFiscal\\ContaPagarController@pagar');
        Route::post('/delete', 'AppFiscal\\ContaPagarController@delete');
    });


    Route::group(['prefix' => 'caixa'],function(){
        Route::get('/', 'AppFiscal\\CaixaController@index')->middleware('authApp');
        Route::post('/suprimento', 'AppFiscal\\CaixaController@suprimento');
        Route::post('/sangria', 'AppFiscal\\CaixaController@sangria');
        Route::post('/fechar', 'AppFiscal\\CaixaController@fechar');
    });

});

//rotas pdv java
Route::group(['prefix' => 'pdv'], function(){

    Route::post('/teste', 'Pdv\\ConfigController@teste');

    Route::group(['prefix' => '/login'], function(){
        Route::post('/', 'Pdv\\LoginController@login')->middleware('throttle:10,1');
    });

    Route::group(['prefix' => '/produtos'], function(){
        Route::get('/', 'Pdv\\ProdutoController@index')->middleware('authPdv');
        Route::get('/limit', 'Pdv\\ProdutoController@limit')->middleware('authPdv');
        Route::get('/count', 'Pdv\\ProdutoController@count')->middleware('authPdv');
    });

    Route::group(['prefix' => '/config'], function(){
        Route::get('/', 'Pdv\\ConfigController@index')->middleware('authPdv');
    });

    Route::group(['prefix' => '/clientes'], function(){
        Route::get('/', 'Pdv\\ClienteController@index')->middleware('authPdv');
    });

    Route::group(['prefix' => '/vendedores'], function(){
        Route::get('/', 'Pdv\\VendedorController@index')->middleware('authPdv');
    });

    Route::group(['prefix' => '/pedidos'], function(){
        Route::get('/', 'Pdv\\PedidoController@index')->middleware('authPdv');
        Route::get('/setImpresso', 'Pdv\\PedidoController@setImpresso')->middleware('authPdv');
    });

    Route::group(['prefix' => '/vendas'], function(){
        Route::post('/salvar', 'Pdv\\VendaController@salvar')->middleware('authPdv');
        Route::get('/rascunhos', 'Pdv\\VendaController@rascunhos')->middleware('authPdv');
        Route::get('/teste', 'Pdv\\VendaController@teste');
    });

    Route::group(['prefix' => '/caixa'], function(){
        Route::get('/{usuario_id}', 'Pdv\\CaixaController@index')->middleware('authPdv');
        Route::post('/abrir', 'Pdv\\CaixaController@abrir')->middleware('authPdv');

    });

});


//marktplace

Route::group(['prefix' => '/marktplace'], function(){
    Route::get('/lojas', 'MP\\LojaController@lojas');
    Route::get('/search', 'MP\\LojaController@search');
    Route::get('/banners', 'MP\\LojaController@banners');
    Route::get('/cupons', 'MP\\LojaController@cupons');
    Route::get('/loja', 'MP\\LojaController@getLoja');
    Route::get('/categoriasDeProduto/{loja_id}', 'MP\\ProdutoController@categorias');
    Route::get('/adicionaisDeProduto', 'MP\\ProdutoController@adicionaisDeProduto');
    Route::post('/login', 'MP\\LoginController@login');

    Route::get('/avaliacoes', 'MP\\LojaController@avaliacoes');

    Route::group(['prefix' => '/loja'], function(){
        Route::post('/like', 'MP\\LojaController@like');
    });

    Route::group(['prefix' => '/cliente'], function(){
        Route::post('/cadastrar', 'MP\\LoginController@cadastrar');
        Route::post('/atualizar', 'MP\\LoginController@atualizar');
        Route::post('/salvarEndereco', 'MP\\LoginController@salvarEndereco');
        Route::post('/atualizarEndereco', 'MP\\LoginController@atualizarEndereco');
        Route::get('/find/{cliente_id}', 'MP\\LoginController@find');
        Route::post('/salvarImagem', 'MP\\LoginController@salvarImagem');
        
    });

    Route::group(['prefix' => '/pedidos'], function(){
        Route::post('/gerarPix', 'MP\\PedidoController@gerarPix');
        Route::post('/gerarPedido', 'MP\\PedidoController@gerarPedido');
        Route::post('/gerarPedidoCartao', 'MP\\PedidoController@gerarPedidoCartao');
        Route::get('/consultaPix/{id}', 'MP\\PedidoController@consultaPix');
        Route::get('/consultaPedidoLido/{id}', 'MP\\PedidoController@consultaPedidoLido');
        Route::get('/ultimoPedidoParaConfirmar/{user_id}', 'MP\\PedidoController@ultimoPedidoParaConfirmar');
        Route::get('/countPedidos/{user_id}', 'MP\\PedidoController@countPedidos');
        Route::get('/all', 'MP\\PedidoController@all');
        Route::post('/avaliar', 'MP\\PedidoController@avaliar');
    });
});

//app comandas

Route::group(['prefix' => '/controle_comandas'], function(){
    Route::get('/', 'ControleComanda\\HomeController@index')->middleware('authAppComanda');
    Route::get('/mesas', 'ControleComanda\\HomeController@mesas')->middleware('authAppComanda');
    Route::get('/tamanhosPizza', 'ControleComanda\\HomeController@tamanhosPizza')->middleware('authAppComanda');
    Route::post('/deleteComanda', 'ControleComanda\\HomeController@deleteComanda');
    Route::post('/deleteItem', 'ControleComanda\\HomeController@deleteItem');
    Route::post('/entregue', 'ControleComanda\\HomeController@entregue');
    Route::post('/abrirComanda', 'ControleComanda\\HomeController@abrirComanda');

    Route::get('/produtos', 'ControleComanda\\ProdutoController@index')->middleware('authAppComanda');
    Route::get('/pizzas', 'ControleComanda\\ProdutoController@pizzas')->middleware('authAppComanda');
    Route::get('/adicionais', 'ControleComanda\\ProdutoController@adicionais')->middleware('authAppComanda');
    Route::get('/categorias', 'ControleComanda\\ProdutoController@categorias')->middleware('authAppComanda');
    Route::post('/salvarItem', 'ControleComanda\\HomeController@salvarItem');

    Route::get('/pedido/{id}', 'ControleComanda\\HomeController@pedido')->middleware('authAppComanda');

});
