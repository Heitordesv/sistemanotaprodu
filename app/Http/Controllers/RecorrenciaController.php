<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RecorrenciaController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = (int) ($request->empresa_id ?: session('user_logged.empresa'));

        abort_unless($empresaId > 0, 401, 'Empresa não identificada na sessão.');

        // Há uma rota legada /payment/finish apontando para este controller.
        // Não redirecionamos para payment.finish novamente, pois isso cria loop.
        // Apenas garantimos o empresa_id e delegamos para o controller de pagamento.
        $request->merge(['empresa_id' => $empresaId]);

        return app(PaymentController::class)->finish();
    }
}