var prot = window.location.protocol;
var host = window.location.host;
var pathname = window.location.pathname;
var soma = 0;
$(function () {
	soma = parseFloat($('#soma_hidden').val())
});
$('.qtd').keyup((e) => {
	let quantidade = e.target.value
	let id = e.target.id
	setQuantidade(id, quantidade)

});

$('.qtd').click((e) => {
	let quantidade = e.target.value
	let id = e.target.id
	setQuantidade(id, quantidade)

});

$('.pro-qty').click((e) => {
	let value = e.currentTarget.childNodes[2]

	let quantidade = value.value
	let id = value.id

	setQuantidade(id, quantidade)

});

function setQuantidade(id, quantidade){
	let path = prot + "//" + host + pathname;
	$.get(path + '/atualizaItem', {id: id, quantidade: quantidade})
	.done((success) => {
	}).fail((err) => {
	})

}
$('#btn-calcular-frete').click(() => {

	
	let cep = $('#cep').val()
	if(cep.length == 9){
		$('.spinner-border').css('display', 'inline-block')
		$('#total').html(formatReal(soma))

		$('#btn-calcular-frete').attr('disabled', true)
		let pedido_id = $('#pedido_id').val()
		$.get(prot + "//" + host + '/ecommerceCalculaFrete', 
		{
			cep: cep,
			pedido_id: pedido_id
		})
		.done((success) => {
			$('.spinner-border').css('display', 'none')
			console.log(success)
			$('#btn-calcular-frete').removeAttr('disabled')
			let html = ''
			if(parseFloat(success.preco_sedex) > 0){
				html = '<input onclick="setValorFrete(\'sedex\','+parseFloat(success.preco_sedex.replace(",", "."))+')" type="radio" value="'+success.preco_sedex+'" name="tipo_frete">'
				html += '<label style="margin-left: 5px;"> SEDEX R$ '+success.preco_sedex + ' - '+ success.prazo_sedex +' Dias</label><br>'
			}
			if(parseFloat(success.preco) > 0){
				html += '<input onclick="setValorFrete(\'pac\','+parseFloat(success.preco.replace(",", "."))+')" type="radio" value="'+success.preco+'" name="tipo_frete">'
				html += '<label style="margin-left: 5px;"> PAC R$ '+success.preco + ' - ' + success.prazo +' Dias</label>'
			}

			if(success.frete_gratis){
				html += '<br><input onclick="setValorFrete(\'gratis\',0)" type="radio" value="0" name="tipo_frete">'
				html += '<label style="margin-left: 5px;"> FRETE GRATIS '+ ' - ' + success.prazo +' Dias</label>'
			}
			if(success.habilitar_retirada){
				html += '<br><input onclick="setValorFrete(\'retirada\',0)" type="radio" value="0" name="tipo_frete">'
				html += '<label style="margin-left: 5px;">IREI RETIRAR NA LOJA</label>'
			}
			$('.frete').html(html)

		}).fail((err) => {
			$('.spinner-border').css('display', 'none')
			console.log(err)
			$('#btn-calcular-frete').removeAttr('disabled')

		})
	}else{
		swal("Erro", "Informe um CEP válido", "warning")
	}
})

function setarFrete(tipo, valor){
	let pedido_id = $('#pedido_id').val();
	let token = $('#token').val();
	let cep = $('#cep').val();
	$('#tp_frete').val(tipo)
	$.post(prot + "//" + host + '/ecommerceSetaFrete',
	{
		_token: token,
		pedido_id: pedido_id,
		tipo: tipo,
		cep: cep,
		valor: valor
	})
	.done((success) => {
		console.log(success)

	}).fail((err) => {
		console.log(err)
	})
}

function setValorFrete(tipo, valor){
	let total = valor + soma
	$('#total').html(formatReal(total))
	setarFrete(tipo, valor)
}

function formatReal(v){
	return v.toLocaleString('pt-br',{style: 'currency', currency: 'BRL'});
}
