<?php

namespace Tests\Feature;

use Illuminate\Routing\Route;
use Tests\TestCase;

class RouteRegistryIntegrityTest extends TestCase
{
    public function test_tabela_de_rotas_carrega_sem_controllers_inexistentes(): void
    {
        $this->artisan('route:list')->assertExitCode(0);
    }

    public function test_rotas_nomeadas_nao_possuem_nomes_duplicados(): void
    {
        $nomes = [];

        foreach (app('router')->getRoutes() as $route) {
            $nome = $route->getName();

            if ($nome === null) {
                continue;
            }

            if (array_key_exists($nome, $nomes)) {
                $this->fail(
                    "Nome de rota duplicado: {$nome} ({$nomes[$nome]} e {$this->descricao($route)})"
                );
            }

            $nomes[$nome] = $this->descricao($route);
        }
    }

    private function descricao(Route $route): string
    {
        return implode('|', $route->methods()) . ' ' . $route->uri();
    }
}
