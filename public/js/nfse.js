var EMITINDO = false;

$('#btn-enviar').click(() => {
	console.clear()
	if(!EMITINDO){
		EMITINDO = true;
		getChecked((id) => {
			$("#btn-consulta-cnpj span").removeClass("d-none");
			let empresa_id = $("#empresa_id").val();

			$.post(path_url + "api/nfse/transmitir", {
				id: id,
				empresa_id: empresa_id,
			})
			.done((e) => {
				console.log(e)
				EMITINDO = false;

				swal("Sucesso", "NFSe gerada com sucesso, código de verificação: " + e.codigo_verificacao, "success")
				.then(() => {
					window.open(e.pdf_nfse)
					setTimeout(() => {
						location.reload()
					}, 100)
				})
			})
			.fail((e) => {

				EMITINDO = false;
				console.log(e)

				if(e.status == 401){
					let json = e.responseJSON
					let link_xml = json.xml
					console.log("link_xml", link_xml)

					let motivo = Array.isArray(json.motivo) ? json.motivo[0] : json.motivo

					let icon = "error"
					let title = "Algo deu errado"
					if(motivo == "Lote enviado para processamento"){
						icon = "warning"
						title = "Aguarde"
					}
					swal({
						title: title,
						text: motivo,
						icon: icon,
						buttons: ["Fechar", 'Ver XML'],
						dangerMode: true,
					})
					.then((v) => {
						if (v) {
							if(link_xml){
								window.open(link_xml, '_blank');
							}else{
								swal("Erro", "Não existe nenhum XML para visualizar", "error")
							}
						} else {
						}
						location.reload()

					});
				}else{
					swal("Algo deu errado", e.responseJSON, "error")

				}
			})
		})
	}
})

$('#btn-imprimir').click(function () {
	getChecked((id) => {
		window.open(path_url + 'nfse/imprimir/' + id, "_blank")
	})
})

$('#btn-consultar').click(() => {
	console.clear()
	getChecked((id) => {
		let empresa_id = $("#empresa_id").val();
		$.post(path_url + "api/nfse/consultar", {
			id: id,
			empresa_id: empresa_id,
		})
		.done((e) => {
			let js = JSON.parse(e)
			console.log(js)

			swal("Sucesso", js.motivo, "success").then(() => {
				location.reload()
			})
		})
		.fail((e) => {
			console.log(e)
			try{
				swal("Erro", e.responseJSON, "error").then(() => {
					location.reload()
				})
			}catch{
				swal("Erro", "Erro consulte o console", "error")
			}
		})
	})
})

$('#btn-cancelar').click(() => {
	console.clear()
	getChecked((id) => {

		if (id > 0) {

			$('#modal-cancelar').modal('show')
		}
	})
})

$('#btn-cancelar-send').click(() => {
	getChecked((id) => {
		let empresa_id = $("#empresa_id").val();
		let motivo = $("#inp-motivo").val();

		$.post(path_url + "api/nfse/cancelar", {
			id: id,
			empresa_id: empresa_id,
			motivo: motivo,
		})
		.done((e) => {
			console.log(e)
			try{
				let js = JSON.parse(e);
				if(js.msg == 'Erro ao cancelar NFS-e'){
					console.log(js)
					swal("Algo deu errado", js.errors[0], "error")
				}else{
					swal("Sucesso", js.motivo, "success").then(() => {
						location.reload()
					})
				}
			}catch{
				swal("Algo deu errado", e, "error")
			}
		})
		.fail((e) => {
			console.log(e)
			swal("Algo deu errado", e, "error")
		})
	})
})

function getChecked(call) {
	let id = null
	$('.checkbox').each(function (i, e) {
		if (e.checked) {
			id = e.value
		}
	})
	if(id == null){
		swal("Alerta", "selecione um item", "warning")
	}
	call(id)
}

function getCheckedElement(call) {
	$el = null
	$('.checkbox').each(function (i, e) {
		if (e.checked) {
			$el = $(this)
		}
	})
	call($el)
}