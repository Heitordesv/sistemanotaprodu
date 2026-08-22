<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EcommerceLegacyPaymentBlockController extends Controller
{
    public function blocked(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Endpoint de pagamento desativado. Utilize o checkout seguro da loja.',
            ], 410);
        }

        abort(410, 'Este endpoint de pagamento foi desativado. Utilize o checkout seguro da loja.');
    }
}