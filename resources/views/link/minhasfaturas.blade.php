<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Faturas - {{ $empresa->razao_social ?? 'Empresa' }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #ffffff; /* fundo branco */
            color: #000000; /* texto preto */
        }
        .navbar, .footer {
            background-color: #000000 !important; /* navbar preta */
        }
        .navbar .navbar-brand, .footer small {
            color: #ffffff !important; /* texto da navbar/rodapé branco */
        }
        .card {
            border: 1px solid #000000; /* borda preta nos cards */
        }
        .card .card-body {
            background-color: #ffffff; /* interior branco */
        }
        .table thead {
            background-color: #f8f9fa; /* cabeçalho levemente cinza claro */
        }
        .table, .table td, .table th {
            border-color: #000000; /* bordas pretas na tabela */
        }
        .btn-primary {
            background-color: #000000;
            border-color: #000000;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #333333;
            border-color: #333333;
        }
        .badge.bg-success {
            background-color: #000000;
        }
        .badge.bg-secondary {
            background-color: #555555;
        }
        .alert {
            border: 1px solid #000000;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Minhas Faturas</a>
        </div>
    </nav>

    <div class="container py-5">

        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h1 class="card-title h3">Consulta de Faturas — {{ $empresa->razao_social }}</h1>
                <p><strong>Link da empresa:</strong> {{ $currentNomeLink }}</p>

                <!-- Formulário -->
                <form method="GET" action="{{ route('link.minhasfaturas', ['nomeLink' => $currentNomeLink]) }}" class="row g-3">
                    <div class="col-md-6">
                        <label for="cpf_cnpj" class="form-label">CPF ou CNPJ</label>
                        <input type="text" id="cpf_cnpj" name="cpf_cnpj" class="form-control" value="{{ request('cpf_cnpj') }}" placeholder="Digite CPF ou CNPJ">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Buscar Faturas</button>
                    </div>
                </form>
            </div>
        </div>

        @if($consultaRealizada)
            @if($cliente)
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h2 class="h5">Cliente encontrado</h2>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Nome:</strong> {{ $cliente->nome_fantasia ?? $cliente->razao_social }}</li>
                            <li><strong>CPF/CNPJ:</strong> {{ $cliente->cpf_cnpj }}</li>
                            <li><strong>Telefone:</strong> {{ $cliente->telefone }}</li>
                            <li><strong>Email:</strong> {{ $cliente->email }}</li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="h5">Faturas</h3>
                        
                        @if($faturas->isEmpty())
                            <p class="text-muted">Não foram encontradas faturas para este cliente.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Data de Vencimento</th>
                                            <th>Valor</th>
                                            <th>Status / Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($faturas as $fatura)
                                            <tr>
                                                <td>{{ $fatura->id }}</td>
                                                <td>{{ \Carbon\Carbon::parse($fatura->data_vencimento)->format('d/m/Y') }}</td>
                                                <td>R$ {{ number_format($fatura->valor_integral, 2, ',', '.') }}</td>
                                                <td>
                                                    @if($fatura->status == 1)
                                                        <span class="badge bg-success">Pago</span>
                                                    @elseif($fatura->status == 0)
                                                        <button 
                                                            class="btn btn-sm btn-primary btn-pagar-modal"
                                                            data-url="https://checkout.mixksolutions.com.br/conta/?item={{ $fatura->id }}"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalPagamento"
                                                        >
                                                            Pagar
                                                        </button>
                                                    @else
                                                        <span class="badge bg-secondary">Status desconhecido</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="alert alert-warning mt-4">
                    Nenhum cliente encontrado com os dados informados.
                </div>
            @endif
        @else
            <div class="alert alert-info mt-4">
                Use o formulário acima para consultar suas faturas por CPF ou CNPJ.
            </div>
        @endif

    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalPagamento" tabindex="-1" aria-labelledby="modalPagamentoLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalPagamentoLabel">Pagamento da Fatura</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <div class="modal-body" style="height:80vh;">
            <iframe id="iframePagamento" src="" style="width:100%; height:100%; border:none;"></iframe>
          </div>
        </div>
      </div>
    </div>

    <footer class="footer text-center py-3 mt-5">
        <div class="container">
            <small>&copy; {{ date('Y') }} — Consulta de Faturas</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalPagamento = document.getElementById('modalPagamento');
        const iframePagamento = document.getElementById('iframePagamento');

        modalPagamento.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const url = button.getAttribute('data-url');
            if (url) {
                iframePagamento.setAttribute('src', url);
            }
        });
        modalPagamento.addEventListener('hidden.bs.modal', function (event) {
            iframePagamento.setAttribute('src', '');
        });
    });
    </script>
</body>
</html>
