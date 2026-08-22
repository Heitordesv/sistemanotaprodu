$('.click-theme').click(function () {
	console.log("val", $(this).val())
	let usuario_id = $('#usuario_id').val()
	$.post(path_url + "api/usuarios/set-theme", {
		tema: $(this).val(),
		usuario_id: usuario_id
	})
		.done((success) => {
			if ($(this).val() == 'minimaltheme') {
				location.reload()
			}
		})
		.fail((err) => {
			console.log(err)
		})
})

function setHeaderColor(color) {
	let usuario_id = $('#usuario_id').val()
	$.post(path_url + "api/usuarios/set-theme", {
		cabecalho: color,
		usuario_id: usuario_id
	})
		.done((success) => {
			console.log(success)
			location.reload()
		})
		.fail((err) => {
			console.log(err)
		})
}

function setSidebar(color) {
	let usuario_id = $('#usuario_id').val()
	$.post(path_url + "api/usuarios/set-theme", {
		plano_fundo: color,
		usuario_id: usuario_id
	})
		.done((success) => {
			console.log(success)
		})
		.fail((err) => {
			console.log(err)
		})
}


function avisoSonoro(som) {
	let usuario_id = $('#usuario_id').val()
	$.post(path_url + "api/usuarios/avisoSonoro", {
		aviso_sonoro: som,
		usuario_id: usuario_id
	})
		.done((success) => {
			console.log(success)
			location.reload()
		})
		.fail((err) => {
			console.log(err)
		})
}

