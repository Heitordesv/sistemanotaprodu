

@extends('default.layout', ['title' => 'Clientes dos últimos 30 dias'])
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <h6 class="mb-0 text-uppercase">Clientes INATIVOS</h6>
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

@if ($cupomvip && $cupomvip->vip == 1)  
🎁 **Presente Especial:**  
- **{{ $cupomvip->porcentagem }}% de desconto!**  
- **Código:** {{ $cupomvip->ativacao }}  
- **Válido por apenas 48h!** ⏳  

Não perca essa chance de matar a saudade dos seus favoritos! 🍔😍  
@else  
Ainda não temos cupons disponíveis no momento, mas fique de olho!  
Em breve, teremos mais surpresas incríveis para você! 🎉  
@endif  

A gente adora te ver por aqui! Qualquer dúvida, estamos prontos para te atender. 💬💖  

🚀 **Peça agora e aproveite:**  
👉 [Clique aqui](https://deliveryba.com.br/{{ $nome_empresa_link }})  
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
                            @foreach($clientesInativos as $cliente)
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
                    {{ $clientesInativos->links() }}
                </div>


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

    selecionarTodosCheckbox.addEventListener('change', function() {
        clienteCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        atualizarInputsSelecionados();
    });

    clienteCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            atualizarInputsSelecionados();
            selecionarTodosCheckbox.checked = Array.from(clienteCheckboxes).every(cb => cb.checked);
        });
    });

    formEnviarWhatsapp.addEventListener('submit', function(event) {
        if (telefonesInput.value.trim() === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Nenhum Cliente Selecionado',
                text: 'Por favor, selecione ao menos um cliente para enviar a mensagem.',
                confirmButtonText: 'Ok'
            });
            event.preventDefault();
            return;
        }

        Swal.fire({
            title: 'Enviando Mensagens...',
            html: 'Por favor, aguarde. Estamos processando o envio das mensagens.<br>Isso pode levar tempo, pois cada mensagem é enviada com um pequeno intervalo para evitar bloqueios. Uma mensagem de sucesso ou erro será exibida ao final do processo.',
            allowOutsideClick: false, 
            allowEscapeKey: false,  
            didOpen: () => {
                Swal.showLoading(); 
            }
        });

        
    });

    document.addEventListener('DOMContentLoaded', atualizarInputsSelecionados);
</script>
@endsection
