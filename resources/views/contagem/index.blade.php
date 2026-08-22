@extends('default.layout', ['title' => 'Contagem de Produtos'])

@section('content')
<div class="container mt-5">
    <h2>Contagem de Produtos</h2>
<div id="resultado"></div>
<div id="historico"></div>

<form id="formBip" method="POST" action="{{ route('contagem.bipar') }}">
    @csrf
    <input type="text" name="codBarras" id="codBarras" placeholder="Bipe o código">
      <input type="hidden" value="{{session('user_logged')['empresa']}}" id="empresa_id">
    <button type="submit">Enviar</button>
</form>

<script>
document.getElementById('codBarras').addEventListener('keypress', function(e) {
    if(e.key === 'Enter') {
        e.preventDefault();

        let codBarras = this.value;
        let empresa_id = document.getElementById('empresa_id').value;
        let filial_id = document.getElementById('filial_id').value;

        fetch("{{ route('contagem.bipar') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ codBarras: codBarras, empresa_id: empresa_id })
        })
        .then(response => response.json())
        .then(data => {
            const resultado = document.getElementById('resultado');
            const historico = document.getElementById('historico');

            if(data.error){
                resultado.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
            } else {
                resultado.innerHTML = '<div class="alert alert-success">Produto: ' + data.produto + ' | Código: ' + data.codBarras + ' | Quantidade: ' + data.quantidade_atual + '</div>';

                // Histórico
                let item = document.createElement('div');
                item.className = 'alert alert-info p-2 mb-1';
                item.innerHTML = 'Produto: ' + data.produto + ' | Código: ' + data.codBarras + ' | Quantidade: ' + data.quantidade_atual;
                historico.prepend(item);
            }

            setTimeout(() => {
                document.getElementById('codBarras').value = '';
                document.getElementById('codBarras').focus();
            }, 100);
        })
        .catch(err => {
            document.getElementById('resultado').innerHTML = '<div class="alert alert-danger">Erro ao processar o bip.</div>';
        });
    }
});
</script>

@endsection
