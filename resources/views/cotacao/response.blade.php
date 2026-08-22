<!DOCTYPE html>
<html>
<head>
    <title>Resposta de Cotação</title>
    <meta name="viewport" content="width = device-width, initial-scale = 1">

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">

</head>
<body>
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand">
                @if($logo != '')
                <img src="/logos/{{$logo}}" width="120">
                @else
                {{ $config->razao_social }}
                @endif
            </a>
        </div>
    </nav>
    <main class="py-4">
        <div class="container">
            <form class="col-lg-12" method="post" action="{{ route('catacaoResponse.store') }}">
                @csrf
                <input type="hidden" name="cotacao_id" value="{{ $cotacao->id }}">
                <h3 class="w-100 text-center">COTAÇÃO N° <strong>{{ $cotacao->id }}</strong></h3>
                <div class="row">
                    <div class="col-lg-8 col-12">
                        <h5>SOLICITANTE: <strong>{{ $config->razao_social }}</strong></h5>
                    </div>
                    <div class="col-lg-4 col-12">
                        <h5>FONE: <strong>{{ $config->fone }}</strong></h5>
                    </div>

                    <div class="col-lg-8 col-12">
                        <h5>FORNECEDOR: <strong>{{strtoupper($cotacao->fornecedor->razao_social)}}</strong></h5>
                    </div>
                    <div class="col-lg-4 col-12">
                        <h5>CIDADE: <strong>{{ $cotacao->fornecedor->cidade->nome }}
                                - {{ strtoupper($cotacao->fornecedor->cidade->uf) }}</strong>
                        </h5>
                    </div>
                    <div class="col-lg-3 col-12">
                        <h5>CNPJ: <strong>{{ $cotacao->fornecedor->cpf_cnpj }}</strong></h5>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 style="font-weight: bold;">Itens da Cotação</h5>
                    </div>

                    <div class="card-body">
                        <p class="text-danger">* Campos obrigatórios</p>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Quantidade*</th>
                                        <th>Valor Unit.*</th>
                                        <th>Observação</th>
                                        <th>Subtotal*</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach($cotacao->itens as $linha => $p)
                                    <tr>
                                        <input type="hidden" class="" name="item_id[]" value="{{ $p->id }}">
                                        <td>
                                            <input disabled type="tel" class="form-control" name="produto_nome[]" value="{{ $p->produto->nome }}">
                                        </td>
                                        <td id="quantity">
                                            <input required readonly type="tel" class="form-control moeda" value="{{ $p->quantidade }}" name="quantidade[]">
                                        </td>

                                        <td>
                                            <input required type="tel" class="form-control moeda value" id="value" name="valor[]">
                                        </td>

                                        <td>
                                            <input type="text" name="observacao_item[]" class="form-control">
                                        </td>

                                        <td>
                                            <input required readonly type="text" name="sub_total[]" class="form-control subtotal">
                                        </td>
                                    </tr>
                                    @endforeach

                                </tbody>
                            </table>

                        </div>
                        <h5>Valor Total <strong class="total">R$ 0,00</strong></h5>
                        <div class="row">
                            <div class="form-group col-lg-6 col-12">
                                <label>Forma de pagamento</label>
                                <input type="text" name="forma_pagamento" class="form-control">
                            </div>
                            <div class="form-group col-lg-4 col-12">
                                <label>Resposável*</label>
                                <input required type="text" name="responsavel" class="form-control">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-lg-2 col-6">
                                <button type="submit" class="btn btn-success btn-lg">Salvar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.10/jquery.mask.min.js"></script>
    <script type="text/javascript">
        $('.moeda').mask('000.000.000.000.000,00', {
            reverse: true
        });

    </script>
    <script src="/js/quotes.js"></script>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
</body>
</html>
