<?php

namespace App\Http\Controllers;

use App\Services\StoneService;

class StoneController extends Controller
{
    protected $stone;

    public function __construct(StoneService $stone)
    {
        $this->stone = $stone;
    }

    public function listarBoletos()
    {
        $boletos = $this->stone->listarBoletos([
            'limit' => 10,
            'status' => 'REGISTERED'
        ]);

        return response()->json($boletos);
    }
}
