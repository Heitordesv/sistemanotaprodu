@extends('default.layout', ['title' => 'Clientes dos últimos 30 dias'])
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <h6 class="mb-0 text-uppercase">Clientes que compraram nos últimos 30 dias (até ontem)</h6>
            <hr>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if($noVipMessage)
                <p class="alert alert-info">{{ $noVipMessage }}</p>
            @else
                <form action="{{ route('tela-pedido.enviar-whatsapp') }}" method="POST" id="form-enviar-whatsapp">
                    @csrf

                    <h3>Selecione os Clientes e Envie uma Mensagem</h3>

                    {{-- Novos Inputs para Telefones e Nomes --}}
                    <div class="form-group mb-3">
                        <label for="telefones_selecionados">Telefones Selecionados:</label>
                        <input type="text" class="form-control" id="telefones_selecionados" name="telefones_selecionados" readonly>
                        <small class="form-text text-muted">Telefones dos clientes selecionados, separados por vírgula.</small>
                    </div>

                    <div class="form-group mb-3">
                        <label for="nomes_selecionados">Nomes Selecionados:</label>
                        <input type="text" class="form-control" id="nomes_selecionados" name="nomes_selecionados" readonly>
                        <small class="form-text text-muted">Nomes dos clientes selecionados, separados por vírgula.</small>
                    </div>

                    <div class="form-group mb-3">
                        <label for="mensagem">Mensagem a Ser Enviada:</label>
                    <textarea class="form-control" id="mensagem" name="mensagem" rows="8" required>
Olá, [Nome do Cliente]! 👋 Temos uma super novidade para você!

✨ Queremos facilitar sua vida! Faça seu pedido agora mesmo clicando no link:
👉 https://deliveryba.com.br/{{ $nome_empresa_link ?? 'SEU_LINK_PADRAO' }}

@if ($cupomvip)
🎁 E tem mais! Você ganhou um desconto especial. Use o cupom *{{ $cupomvip->codigo }}* no checkout!
@endif

Não perca essa chance! 😉
</textarea>
<small class="form-text text-muted">
    Use **[Nome do Cliente]** como placeholder. O link da empresa e o cupom (se houver) serão adicionados automaticamente.
</small>
                        @error('mensagem')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary mt-3" id="btn-enviar">Enviar Mensagem</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selecionarTodos"></th>
                                <th>Cliente (Nome, Telefone)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clientes as $cliente)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="cliente-checkbox"
                                               data-telefone="{{ $cliente->telefone }}"
                                               data-nome="{{ $cliente->nome }}"
                                               name="clientes_selecionados[]" value="{{ $cliente->telefone }}">
                                    </td>
                                    <td>
                                        {{ $cliente->nome }}, {{ $cliente->telefone }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Exibe os links de paginação --}}
                <div class="d-flex justify-content-center">
                    {{ $clientes->links() }}
                </div>

            @endif

            <hr>

            @if ($cupomvip)
                <p>Cupom VIP Ativo: {{ $cupomvip->codigo }}</p>
            @endif
            @if ($nome_empresa_link)
<p>Link da Empresa: 
    <a href="https://deliveryba.com.br/{{ $nome_empresa_link }}" target="_blank">
        https://deliveryba.com.br/{{ $nome_empresa_link }}
    </a>
</p>
            @endif

        </div>
    </div>
</div>

<script>
    const selecionarTodosCheckbox = document.getElementById('selecionarTodos');
    const clienteCheckboxes = document.querySelectorAll('.cliente-checkbox');
    const telefonesInput = document.getElementById('telefones_selecionados');
    const nomesInput = document.getElementById('nomes_selecionados');
    const formEnviarWhatsapp = document.getElementById('form-enviar-whatsapp'); // Obter o formulário
    const btnEnviar = document.getElementById('btn-enviar'); // Obter o botão de envio

    function atualizarInputsSelecionados() {
        let telefones = [];
        let nomes = [];

        clienteCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                telefones.push(checkbox.dataset.telefone);
                nomes.push(checkbox.dataset.nome);
            }
        });

        telefonesInput.value = telefones.join(', ');
        nomesInput.value = nomes.join(', ');
    }

    // Evento para o checkbox "selecionarTodos"
    selecionarTodosCheckbox.addEventListener('change', function() {
        clienteCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        atualizarInputsSelecionados();
    });

    // Evento para cada checkbox de cliente individual
    clienteCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            atualizarInputsSelecionados();
            selecionarTodosCheckbox.checked = Array.from(clienteCheckboxes).every(cb => cb.checked);
        });
    });

    // Evento de submit do formulário para exibir o alerta
    formEnviarWhatsapp.addEventListener('submit', function(event) {
        // Valida se há algum telefone selecionado antes de mostrar o alerta e enviar
        if (telefonesInput.value.trim() === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Nenhum Cliente Selecionado',
                text: 'Por favor, selecione ao menos um cliente para enviar a mensagem.',
                confirmButtonText: 'Ok'
            });
            event.preventDefault(); // Impede o envio do formulário
            return;
        }

        // Exibir o alerta SweetAlert2
        Swal.fire({
            title: 'Enviando Mensagens...',
            html: 'Por favor, aguarde. Estamos processando o envio das mensagens.<br>Isso pode levar tempo, pois cada mensagem é enviada com um pequeno intervalo para evitar bloqueios. Uma mensagem de sucesso ou erro será exibida ao final do processo.',
            allowOutsideClick: false, // Impede o fechamento ao clicar fora
            allowEscapeKey: false,   // Impede o fechamento com a tecla ESC
            didOpen: () => {
                Swal.showLoading(); // Mostra o ícone de carregamento
            }
        });

        // O formulário será enviado após o alerta ser exibido.
        // Como o processamento é em segundo plano (via Jobs e Queues),
        // o alerta vai desaparecer automaticamente quando a página recarregar
        // com a resposta do controller (sucesso/erro).
    });

    // Inicializa os inputs caso haja alguma seleção prévia (por exemplo, após um erro de validação)
    document.addEventListener('DOMContentLoaded', atualizarInputsSelecionados);
</script>
@endsection