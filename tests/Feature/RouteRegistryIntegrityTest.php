<?php

namespace Tests\Feature;

use Tests\TestCase;

class RouteRegistryIntegrityTest extends TestCase
{
    public function test_tabela_de_rotas_carrega_sem_controllers_inexistentes(): void
    {
        $this->artisan('route:list')->assertExitCode(0);
    }
}
