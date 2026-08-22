var prot = window.location.protocol;
var host = window.location.host;
var pathname = window.location.pathname;
var totais = []
$(function () {

	window.Mercadopago.getIdentificationTypes()
	totais = JSON.parse($('#totais').val())
	console.log(totais)
	setTimeout(() => {
		let s = $('#docType').html()
		console.log(s)
		$('#docType2').html(s)
		$('#docType3').html(s)
	}, 2000)
});

$('#cardNumber').keyup(() => {

	let cardnumber = $('#cardNumber').val().replaceAll(" ", "");
	if (cardnumber.length >= 6) {
		let bin = cardnumber.substring(0,6);

		window.Mercadopago.getPaymentMethod({
			"bin": bin
		}, setPaymentMethod);
	}
})

function setPaymentMethod(status, response) {
	if (status == 200) {
		let paymentMethod = response[0];
		document.getElementById('paymentMethodId').value = paymentMethod.id;
		// console.log("src card", paymentMethod.thumbnail)
		$('#band-img').attr("src", paymentMethod.thumbnail);
		getIssuers(paymentMethod.id);
	} else {
		alert(`payment method info error: ${response}`);
	}
}

function getIssuers(paymentMethodId) {
	window.Mercadopago.getIssuers(
		paymentMethodId,
		setIssuers
		);
}

function setIssuers(status, response) {
	if (status == 200) {
		let issuerSelect = document.getElementById('issuer');
		$('#issuer').html('');
		response.forEach( issuer => {
			let opt = document.createElement('option');
			opt.text = issuer.name;
			opt.value = issuer.id;
			issuerSelect.appendChild(opt);
		});

		getInstallments(
			document.getElementById('paymentMethodId').value,
			document.getElementById('transactionAmount').value,
			issuerSelect.value
			);
	} else {
		alert(`issuers method info error: ${response}`);
	}
}

function getInstallments(paymentMethodId, transactionAmount, issuerId){
	window.Mercadopago.getInstallments({
		"payment_method_id": paymentMethodId,
		"amount": parseFloat(transactionAmount),
		"issuer_id": parseInt(issuerId)
	}, setInstallments);
}

function setInstallments(status, response){
	if (status == 200) {
		document.getElementById('installments').options.length = 0;
		response[0].payer_costs.forEach( payerCost => {
			console.log(payerCost)
			let opt = document.createElement('option');
			opt.text = payerCost.recommended_message;
			opt.value = payerCost.installments;
			document.getElementById('installments').appendChild(opt);
		});
	} else {
        $('.spinner-border').addClass('d-none')
        $('.cart_button_checkout').prop("disabled", false);
		// alert(`installments method info error: ${response}`);
		swal("Erro", response, "error")

	}
}

doSubmit = false;
document.getElementById('paymentForm').addEventListener('submit', getCardToken);
function getCardToken(event){
	event.preventDefault();
	if(!doSubmit){
		let docNumber = $('#docNumber').val()
		docNumber = docNumber.replace(/[^0-9]/g,'');
		$('#docNumber').val(docNumber)
		let $form = document.getElementById('paymentForm');
		console.clear()
		console.log($form)
		window.Mercadopago.createToken($form, setCardTokenAndPay);
		return false;
	}
};

function setCardTokenAndPay(status, response) {
	if (status == 200 || status == 201) {
		let form = document.getElementById('paymentForm');
		let card = document.createElement('input');
		card.setAttribute('name', 'token');
		card.setAttribute('type', 'hidden');
		card.setAttribute('value', response.id);
		console.log(card)
		form.appendChild(card);
		doSubmit=true;

		form.submit();
	} else {
        $('.spinner-border').addClass('d-none')
        $('.cart_button_checkout').prop("disabled", false);
		swal("Erro", JSON.stringify(response, null, 4), "error")
	}
};

$('#cardExpirationMonth').keyup(() => {
	let c = $('#cardExpirationMonth').val();
	if(c.length == 2){
		$('#cardExpirationYear').focus()
	}	
})

$('#cardExpirationYear').keyup(() => {
	let c = $('#cardExpirationYear').val();
	if(c.length == 2){
		$('#securityCode').focus()
	}	
})

$('#click-cartao').click(() => {
	try{
		$('.total-h2').html('R$ '+totais.total_cartao.replaceAll('.', ','))
	}catch{
		$('.total-h2').html('R$ '+totais.total_cartao.toFixed(2).replaceAll('.', ','))
	}
})

$('#click-pix').click(() => {
	try{
		$('.total-h2').html('R$ '+totais.total_pix.replaceAll('.', ','))
	}catch{
		$('.total-h2').html('R$ '+totais.total_pix.toFixed(2).replaceAll('.', ','))
	}
})

$('#click-boleto').click(() => {
	try{
		$('.total-h2').html('R$ '+totais.total_boleto.replaceAll('.', ','))
	}catch{
		$('.total-h2').html('R$ '+totais.total_boleto.toFixed(2).replaceAll('.', ','))
	}
})

