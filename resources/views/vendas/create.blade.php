@extends('default.layout', ['title' => 'Nova Venda'])
@section('content')
<div class="page-content">
    <div class="card">
        {!! Form::open()
        ->post()
        ->route('vendas.store')
        ->id('form-venda')
        ->multipart() 
        !!}
        <div class="pl-lg-4">
            @include('vendas._forms')
        </div>
        {!! Form::close() !!}
    </div>
</div>

@section('js')

<script>
    function validarDadosObrigatorios(t) {
        const faltando = [];

        const clienteId = $('#inp-cliente_id').val();
        const naturezaId = $('#inp-natureza_id').val();
        const quantidadeItens = $('.table-itens tbody tr').length;

        if (!clienteId) {
            faltando.push('Cliente');
        }

        if (!naturezaId) {
            faltando.push('Natureza de operação');
        }

        if (quantidadeItens === 0) {
            faltando.push('Pelo menos um produto');
        }

        if (t === 'venda') {
            const formaPagamento = $('#inp-forma_pagamento').val();
            const tipoPagamento = $('#inp-tipo_pagamento').val();
            const quantidadePagamentos = $('.table-payment tbody tr').length;
            const tiposPagamentoAdicionados = $('input[name="tipo_pagamentos[]"]').length;
            const valoresParcelas = $('input[name="valor_parcela[]"]').length;

            if (!formaPagamento) {
                faltando.push('Forma de pagamento');
            }

            if (!tipoPagamento) {
                faltando.push('Tipo de pagamento');
            }

            if (
                quantidadePagamentos === 0 ||
                tiposPagamentoAdicionados === 0 ||
                valoresParcelas === 0
            ) {
                faltando.push('Pagamento da venda');
            }
        }

        if (faltando.length === 0) {
            return true;
        }

        const itens = faltando
            .filter((item, indice, lista) => lista.indexOf(item) === indice)
            .map(item => '<li style="text-align:left">' + item + '</li>')
            .join('');

        const mensagem = '<div style="text-align:left">' +
            '<p>Para continuar, preencha os seguintes dados:</p>' +
            '<ul style="margin-bottom:0">' + itens + '</ul>' +
            '</div>';

        if (typeof swal === 'function') {
            swal({
                title: 'Dados obrigatórios faltando',
                content: {
                    element: 'div',
                    attributes: {
                        innerHTML: mensagem
                    }
                },
                icon: 'warning',
                button: 'Entendi'
            });
        } else {
            alert('Não foi possível continuar. Dados faltando: ' + faltando.join(', ') + '.');
        }

        if (
            t === 'venda' &&
            (
                faltando.includes('Forma de pagamento') ||
                faltando.includes('Tipo de pagamento') ||
                faltando.includes('Pagamento da venda')
            )
        ) {
            selectDiv2('pagamento');
        }

        return false;
    }

    function salvar(t) {
        if (!validarDadosObrigatorios(t)) {
            return;
        }

        $('#type').val(t);
        $('#form-venda').submit();
    }

    function selectDiv2(ref) {
        $('.btn-outline-primary').removeClass('active');

        if (ref === 'transporte') {
            $('.div-transporte').removeClass('d-none');
            $('.div-itens').addClass('d-none');
            $('.div-pagamento').addClass('d-none');
            $('.btn-transporte').addClass('active');
        } else if (ref === 'itens') {
            $('.div-transporte').addClass('d-none');
            $('.div-itens').removeClass('d-none');
            $('.div-pagamento').addClass('d-none');
            $('.btn-itens').addClass('active');
        } else {
            $('.div-transporte').addClass('d-none');
            $('.div-itens').addClass('d-none');
            $('.div-pagamento').removeClass('d-none');
            $('.btn-pagamento').addClass('active');
        }
    }
</script>

<script type="text/javascript" src="/js/client.js"></script>
<script type="text/javascript" src="{{ asset('js/vendas.js') }}?v={{ filemtime(public_path('js/vendas.js')) }}"></script>
<script type="text/javascript" src="/js/product.js"></script>
<script type="text/javascript" src="/js/transportadora.js"></script>

@endsection

@include('modals._ref-nfe', ['not_submit' => true])
@include('modals._produto', ['not_submit' => true])
@include('modals._client', ['not_submit' => true])
@include('modals._transportadora', ['not_submit' => true])
@include('modals._pagamento_personalizado', ['not_submit' => true])
@include('vendas.partials.modal_edit_item')

@endsection
