
$('body').on('blur', '.value', function() {
	var value = convertMoedaToFloat($(this).val());
	var $subtotal = $(this).closest('td').next().next().find('input');
	var $qtd = $(this).closest('td').prev().find('input');

	let qtd = convertMoedaToFloat($qtd.val())

	$subtotal.val(convertFloatToMoeda(value*qtd));
	calcTotal()
})

function calcTotal(){
	var total = 0
	$(".subtotal").each(function () {
		total += convertMoedaToFloat($(this).val())
	})
	setTimeout(() => {
		$('.total').html("R$ " + convertFloatToMoeda(total))
	}, 100)
}

function convertMoedaToFloat(value) {
	if (!value) {
		return 0;
	}

	var number_without_mask = value.replaceAll(".", "").replaceAll(",", ".");
	return parseFloat(number_without_mask.replace(/[^0-9\.]+/g, ""));
}

function convertFloatToMoeda(value) {
	value = parseFloat(value)
	return value.toLocaleString("pt-BR", {
		minimumFractionDigits: 2,
		maximumFractionDigits: 2
	});
}