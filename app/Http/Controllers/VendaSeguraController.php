<?php

namespace App\Http\Controllers;

use App\Services\VendaTenantGuardService;
use Illuminate\Http\Request;

class VendaSeguraController extends VendaController
{
    public function __construct(private VendaTenantGuardService $tenantGuard)
    {
    }

    public function store(Request $request)
    {
        if ((string) $request->input('type') === 'venda') {
            $this->tenantGuard->validar($request);
        }

        return parent::store($request);
    }

    public function update(Request $request, $id)
    {
        if ((string) $request->input('type') === 'venda') {
            $this->tenantGuard->validar($request, (int) $id);

            // O vínculo da venda com a sessão de caixa é imutável após a criação.
            // Mesmo estando no $fillable para permitir o store sob lock, um PUT/PATCH
            // nunca pode mover uma venda histórica para outra abertura.
            $request->request->remove('abertura_caixa_id');
        }

        return parent::update($request, $id);
    }
}
