<?php

use App\Http\Controllers\EcommerceAddressSecurityController;
use App\Http\Controllers\EcommerceCheckoutSecurityController;
use App\Http\Controllers\EcommerceLegacyPaymentBlockController;
use App\Http\Controllers\EcommerceMercadoPagoConfigController;
use App\Http\Controllers\EcommercePaymentPrepareController;
use App\Http\Controllers\EcommercePaymentSecurityController;
use App\Http\Controllers\EcommerceSecurityController;
use App\Http\Controllers\EcommerceStorefrontController;
use App\Http\Middleware\EcommerceStorefrontSeo;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Loja pública e checkout seguro
|--------------------------------------------------------------------------
| Este arquivo contém somente rotas da vitrine pública, checkout,
| pagamento e integrações relacionadas ao e-commerce.
| As rotas administrativas do lojista ficam em ecommerce_admin.php.
*/

Route::group([
    'prefix' => 'loja',
    'middleware' => ['web', 'validaEcommerce', EcommerceStorefrontSeo::class],
], function () {
    Route::get('/{link}', [EcommerceStorefrontController::class, 'index']);
    Route::get('/{link}/categorias', [EcommerceStorefrontController::class, 'categorias']);
    Route::get('/{link}/blog', [EcommerceStorefrontController::class, 'blog']);
    Route::get('/{link}/contato', [EcommerceStorefrontController::class, 'contato']);
    Route::get('/{link}/carrinho', [EcommerceStorefrontController::class, 'carrinho']);
    Route::get('/{link}/curtidas', [EcommerceStorefrontController::class, 'curtidas']);
    Route::get('/{link}/checkout', [EcommerceStorefrontController::class, 'checkout']);
    Route::get('/{link}/endereco', [EcommerceAddressSecurityController::class, 'endereco']);
    Route::get('/{link}/pagamento', [EcommercePaymentPrepareController::class, 'mostrar']);
    Route::get('/{link}/pesquisa', [EcommerceStorefrontController::class, 'pesquisa']);
    Route::get('/{link}/pesquisa/sugestoes', [EcommerceStorefrontController::class, 'sugestoesPesquisa']);

    Route::get('/{link}/{id}/categorias', [EcommerceSecurityController::class, 'produtosDaCategoria']);
    Route::get('/{link}/{id}/subcategoria', [EcommerceSecurityController::class, 'produtosDaSubCategoria']);
    Route::get('/{link}/{id}/verPost', [EcommerceSecurityController::class, 'verPost']);
    Route::get('/{link}/{id}/verProduto', [EcommerceSecurityController::class, 'verProduto']);

    Route::post('/{link}/addProduto', [EcommerceSecurityController::class, 'addProduto']);
    Route::get('/{link}/{id}/deleteItemCarrinho', [EcommerceSecurityController::class, 'deleteItemCarrinho']);
    Route::post('/{link}/atualizaItem', [EcommerceSecurityController::class, 'atualizaItem']);

    Route::get('/{link}/login', [EcommerceSecurityController::class, 'login']);
    Route::post('/{link}/login', [EcommerceSecurityController::class, 'loginPost']);
    Route::get('/{link}/logoff', [EcommerceSecurityController::class, 'logoff']);
    Route::post('/{link}/esquecisenha', [EcommerceSecurityController::class, 'esquecisenhaPost']);

    Route::get('/{link}/{id}/curtirProduto', [EcommerceSecurityController::class, 'curtirProduto']);
    Route::get('/{link}/pedido_detalhe/{id}', [EcommerceSecurityController::class, 'pedidoDetalhe']);

    Route::post('/{link}/ecommerceUpdateCliente', [EcommerceSecurityController::class, 'ecommerceUpdateCliente']);
    Route::post('/{link}/ecommerceUpdateSenha', [EcommerceSecurityController::class, 'ecommerceUpdateSenha']);
    Route::post('/{link}/ecommerceSaveEndereco', [EcommerceSecurityController::class, 'ecommerceSaveEndereco']);

    Route::post('/{link}/checkout', [EcommerceCheckoutSecurityController::class, 'checkoutStore']);
    Route::match(['get', 'post'], '/{link}/calcularFrete', [EcommerceCheckoutSecurityController::class, 'calculaFrete']);
    Route::post('/{link}/setaFrete', [EcommerceCheckoutSecurityController::class, 'setaFrete']);
    Route::post('/{link}/buscaCupomEcommerce', [EcommerceCheckoutSecurityController::class, 'buscaCupom']);
    Route::post('/{link}/pagamento', [EcommercePaymentPrepareController::class, 'pagamento']);

    Route::post('/{link}/pagar/pix', [EcommercePaymentSecurityController::class, 'pix'])
        ->name('ecommerce.secure.pay.pix');

    Route::post('/{link}/pagar/boleto', [EcommercePaymentSecurityController::class, 'boleto'])
        ->name('ecommerce.secure.pay.boleto');

    Route::post('/{link}/pagar/cartao', [EcommercePaymentSecurityController::class, 'cartao'])
        ->name('ecommerce.secure.pay.cartao');

    Route::get('/{link}/pix/{pedidoId}', [EcommercePaymentSecurityController::class, 'showPix'])
        ->name('ecommerce.secure.pix');

    Route::get('/{link}/pagamento/status/{pedidoId}', [EcommercePaymentSecurityController::class, 'status'])
        ->name('ecommerce.secure.status');

    Route::get('/{link}/pedido-finalizado/{hash}', [EcommercePaymentSecurityController::class, 'finalizado'])
        ->name('ecommerce.secure.finalizado');
});

Route::post('/webhooks/mercadopago/ecommerce/{configId}', [EcommercePaymentSecurityController::class, 'webhook'])
    ->name('ecommerce.secure.webhook');

// Endpoints legados de pagamento permanecem bloqueados.
Route::post('/ecommercePay/pix', [EcommerceLegacyPaymentBlockController::class, 'blocked']);
Route::post('/ecommercePay/boleto', [EcommerceLegacyPaymentBlockController::class, 'blocked']);
Route::post('/ecommercePay/cartao', [EcommerceLegacyPaymentBlockController::class, 'blocked']);
Route::get('/ecommercePay/consulta/{transacao_id}', [EcommerceLegacyPaymentBlockController::class, 'blocked']);
Route::get('/ecommercePay/finalizado/{hash}', [EcommerceLegacyPaymentBlockController::class, 'blocked']);

Route::middleware('web')->group(function () {
    Route::get('/integracoes/ecommerce/mercadopago', [EcommerceMercadoPagoConfigController::class, 'index'])
        ->name('ecommerce.mercadopago.security');

    Route::post('/integracoes/ecommerce/mercadopago', [EcommerceMercadoPagoConfigController::class, 'update'])
        ->name('ecommerce.mercadopago.security.update');

    Route::post('/ecommerceUpdateCliente', [EcommerceSecurityController::class, 'ecommerceUpdateCliente']);
    Route::post('/ecommerceUpdateSenha', [EcommerceSecurityController::class, 'ecommerceUpdateSenha']);
    Route::post('/ecommerceSaveEndereco', [EcommerceSecurityController::class, 'ecommerceSaveEndereco']);
});