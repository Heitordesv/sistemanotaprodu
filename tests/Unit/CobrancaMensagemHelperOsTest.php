<?php

namespace Tests\Unit;

use App\Helpers\CobrancaMensagemHelper;
use Tests\TestCase;

class CobrancaMensagemHelperOsTest extends TestCase
{
    public function test_mensagem_personalizada_da_os_remove_linhas_com_links(): void
    {
        $ordem = (object) [
            'id' => 10,
            'empresa_id' => 1,
            'numero_sequencial' => 123,
            'estado' => 'pronto',
            'descricao' => 'Troca de tela',
            'valor' => 150,
        ];
        $cliente = (object) [
            'razao_social' => 'Cliente Teste',
            'nome_fantasia' => null,
            'nome' => null,
        ];

        $mensagem = CobrancaMensagemHelper::osAtualizacao(
            $ordem,
            $cliente,
            "Olá, {cliente}!\n\nSua OS #{os} está pronta.\nAcompanhe em https://sistema.test/os/10\n\nPode vir buscá-la."
        );

        $this->assertStringContainsString('Olá, Cliente Teste!', $mensagem);
        $this->assertStringContainsString('Sua OS #123 está pronta.', $mensagem);
        $this->assertStringContainsString('Pode vir buscá-la.', $mensagem);
        $this->assertStringNotContainsString('https://', $mensagem);
        $this->assertStringNotContainsString('Acompanhe em', $mensagem);
    }

    public function test_mensagem_da_os_remove_placeholder_de_link_antigo(): void
    {
        $ordem = (object) [
            'id' => 11,
            'empresa_id' => 1,
            'numero_sequencial' => null,
            'estado' => 'pendente',
            'descricao' => null,
            'valor' => 0,
        ];
        $cliente = (object) [
            'razao_social' => null,
            'nome_fantasia' => null,
            'nome' => 'Maria',
        ];

        $mensagem = CobrancaMensagemHelper::osAtualizacao(
            $ordem,
            $cliente,
            "Olá, {cliente}!\nConsulte sua ordem: {link}\nStatus: {status}."
        );

        $this->assertSame("Olá, Maria!\nStatus: pendente.", $mensagem);
    }
}
