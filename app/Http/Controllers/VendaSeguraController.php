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
            $this->tenantGuard->prepararUpdate($request, (int) $id);
        }

        return parent::update($request, $id);
    }
}
