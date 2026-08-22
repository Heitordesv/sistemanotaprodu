$(function(){
	
})


$('#btn-enviar').click(() => {
	console.clear()
	let id = $('#compra_id').val()
	$("#btn-consulta-cnpj span").removeClass("d-none");
	let empresa_id = $("#empresa_id").val();

	$.post(path_url + "api/nfe_entrada/transmitir", {
		id: id,
		empresa_id: empresa_id,
	})
	.done((success) => {
		console.log(success)

		swal("Sucesso", "NFe emitida " + success, "success")
		.then(() => {
			window.open(path_url+'compras-danfe/'+id, "_blank")
			setTimeout(() => {
				location.reload()
			}, 100)
		})
	})
	.fail((err) => {
		console.log(err)
		if(err.status == 403){
			try{
				let infProt = err.responseJSON.protNFe.infProt
				swal("Algo deu errado", infProt.cStat + " - " + infProt.xMotivo, "error")
			}catch{
				swal("Algo deu errado", err.responseJSON, "error")
			}
		}else{
			swal("Algo deu errado", err.responseJSON, "error")
		}
	})

})

$('#btn-consultar').click(() => {
	console.clear()

	let id = $('#compra_id').val()
	let empresa_id = $("#empresa_id").val();

	$.post(path_url + "api/nfe_entrada/consultar", {
		id: id,
		empresa_id: empresa_id,
	})
	.done((success) => {
		console.log(success)
		let infProt = success.protNFe.infProt
		swal("Sucesso", "[" + infProt.chNFe + "] " + infProt.xMotivo, "success")

	})
	.fail((err) => {
		console.log(err)
		swal("Algo deu errado", err.responseJSON, "error")

	})
})
$('#btn-cancelar').click(() => {
	console.clear()
	let numero_nfe = $('#numero_nfe').val()
	if(numero_nfe > 0){
		$('.numero_nfe').text(numero_nfe)
		$('#modal-cancelar').modal('show')
	}
})

$('#btn-cancelar-send').click(() => {
	
	let empresa_id = $("#empresa_id").val();
	let motivo = $('#inp-motivo-cancela').val()
	let id = $('#compra_id').val()

	if(motivo.length >= 15){
		$.post(path_url + "api/nfe_entrada/cancelar", {
			id: id,
			empresa_id: empresa_id,
			motivo: motivo
		})
		.done((success) => {
			console.log(success)
			let infEvento = success.retEvento.infEvento
			swal("Sucesso", "[" + infEvento.cStat + "] " + infEvento.xMotivo, "success")
			.then(() => {
				window.open(path_url+'compras-imprimir-cancela/'+id, "_blank")
				setTimeout(() => {
					location.reload()
				}, 100)
			})

		})
		.fail((err) => {
			console.log(err)
			try{
				swal("Algo deu errado", err.responseJSON.retEvento.infEvento.xMotivo, "error")
			}catch{
				swal("Algo deu errado", err.responseJSON, "error")
			}
		})
	}else{
		swal("Alerta", "Informe no mínimo 15 caracteres", "warning")
	}

})

$('#btn-corrigir').click(() => {
	console.clear()

	let numero_nfe = $('#numero_nfe').val()
	if(numero_nfe > 0){
		$('.numero_nfe').text(numero_nfe)
		$('#modal-corrigir').modal('show')
	}

})

$('#btn-corrige-send').click(() => {
	let empresa_id = $("#empresa_id").val();
	let motivo = $('#inp-motivo-corrige').val()
	let id = $('#compra_id').val()

	if(motivo.length >= 15){
		$.post(path_url + "api/nfe_entrada/corrigir", {
			id: id,
			empresa_id: empresa_id,
			motivo: motivo
		})
		.done((success) => {
			console.log(success)
			let infEvento = success.retEvento.infEvento
			swal("Sucesso", "[" + infEvento.cStat + "] " + infEvento.xMotivo, "success")
			.then(() => {
				window.open(path_url+'compras-imprimir-cce/'+id, "_blank")
				setTimeout(() => {
					location.reload()
				}, 100)
			})

		})
		.fail((err) => {
			console.log(err)
			try{
				swal("Algo deu errado", err.responseJSON.retEvento.infEvento.xMotivo, "error")
			}catch{
				swal("Algo deu errado", err.responseJSON, "error")
			}
		})
	}else{
		swal("Alerta", "Informe no mínimo 15 caracteres", "warning")
	}

})


