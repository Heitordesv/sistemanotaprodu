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

// O frontBox.js legado validava a senha de autorização via GET, colocando o
// segredo na query string. Como theme.js é carregado depois do frontBox.js no
// PDV, substituímos somente esse boundary sem alterar as demais regras do caixa.
(function securePdvPasswordValidation() {
	if (typeof window.validaPass !== 'function') {
		return;
	}

	window.validaPass = function (call) {
		let senhaConfigurada = $('#pass').val();

		if (senhaConfigurada === '' || window.SENHADESBLOQUEADA) {
			call(true);
			return;
		}

		swal({
			title: 'Desconto de item',
			text: 'Informe a senha!',
			content: {
				element: 'input',
				attributes: {
					type: 'password',
					autocomplete: 'off'
				}
			},
			buttons: ['Cancelar', 'Confirmar'],
			dangerMode: true
		}).then(function (senhaInformada) {
			if (!senhaInformada) {
				call(false);
				return;
			}

			let csrfToken = $('meta[name="csrf-token"]').attr('content') || '';
			let basePath = typeof path !== 'undefined'
				? path
				: (typeof path_url !== 'undefined' ? path_url : '/');

			basePath = String(basePath || '/');
			if (basePath.slice(-1) !== '/') {
				basePath += '/';
			}

			$.ajax({
				url: basePath + 'configNF/verificaSenha',
				method: 'POST',
				data: {
					senha: senhaInformada,
					_token: csrfToken
				},
				headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}
			})
			.done(function () {
				window.SENHADESBLOQUEADA = true;
				call(true);
			})
			.fail(function (xhr) {
				swal.close();

				if (xhr && xhr.status === 429) {
					swal('Atenção', 'Muitas tentativas de senha. Aguarde um momento e tente novamente.', 'warning')
						.then(function () { call(false); });
					return;
				}

				swal('Erro', 'Senha incorreta', 'error')
					.then(function () { call(false); });
			});
		});
	};
})();
