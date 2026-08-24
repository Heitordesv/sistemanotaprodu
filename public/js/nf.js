$(function () {
	$('.btn-action').attr('disabled', true)
	validLineSelect()
})

/* ===============================
   CHECKBOX (seleção única)
================================ */
$('.checkbox').on('click', function () {
	let value = this.value

	$('.checkbox').each(function () {
		this.checked = (this.value === value)
	})

	let email = $(this).data('email')
	if (email) $('#inp-email').val(email)

	validLineSelect()
})

/* ===============================
   HABILITA BOTÕES POR STATUS
================================ */
function validLineSelect() {
	$('.btn-action').attr('disabled', true)

	$('.checkbox:checked').each(function () {
		let status = $(this).data('status')

		if (status === 'novo' || status === 'rejeitado') {
			$('#btn-enviar, #btn-danfe-temp').removeAttr('disabled')
		}

		if (status === 'aprovado') {
			$('#btn-imprimir, #btn-imprimir-cce, #btn-consultar, #btn-cancelar, #btn-corrigir, #btn-baixar-xml, #btn-enviar-email')
				.removeAttr('disabled')
		}

		if (status === 'cancelado') {
			$('#btn-imprimir-cancela').removeAttr('disabled')
		}
	})
}

/* ===============================
   HELPERS
================================ */
function getChecked(callback) {
	let id = null
	$('.checkbox:checked').each(function () {
		id = this.value
	})
	callback(id)
}

function getCheckedElement(callback) {
	let el = null
	$('.checkbox:checked').each(function () {
		el = $(this)
	})
	callback(el)
}

/* ===============================
   CONSULTA STATUS SEFAZ
================================ */
$('.btn-consulta-status').click(() => {
	let empresa_id = $("#empresa_id").val()

	$.post(path_url + 'fiscal/nfe/status-sefaz', { empresa_id })
	.done(res => {
		let msg = `cStat: ${res.cStat}\nMotivo: ${res.xMotivo}\nAmbiente: ${res.tpAmb == 2 ? "Homologação" : "Produção"}\nverAplic: ${res.verAplic}`
		swal("Sucesso", msg, "success")
	})
	.fail(() => swal("Erro", "Falha ao consultar SEFAZ", "error"))
})

/* ===============================
   TRANSMITIR
================================ */
$('#btn-enviar').click(() => {
	getChecked(id => {
		if (!id) return swal("Alerta", "Selecione uma venda!", "warning")

		let empresa_id = $("#empresa_id").val()

		$.post(path_url + "fiscal/nfe/transmitir", { id, empresa_id })
		.done(() => {
			swal("Sucesso", "NFe emitida", "success").then(() => {
				window.open(path_url + 'nfe/imprimir/' + id, "_blank")
				location.reload()
			})
		})
		.fail(err => swal("Erro", err.responseJSON || "Erro ao transmitir", "error"))
	})
})

/* ===============================
   IMPRESSÕES / DOWNLOADS
================================ */
$('#btn-imprimir').click(() => getChecked(id => window.open(path_url + 'nfe/imprimir/' + id)))
$('#btn-imprimir-cce').click(() => getChecked(id => window.open(path_url + 'nfe/imprimir-cce/' + id)))
$('#btn-imprimir-cancela').click(() => getChecked(id => window.open(path_url + 'nfe/imprimir-cancela/' + id)))
$('#btn-baixar-xml').click(() => getChecked(id => window.open(path_url + 'nfe/baixar-xml/' + id)))
$('#btn-danfe-temp').click(function () {
	getChecked(id => window.open($(this).data('href') + "/" + id))
})

/* ===============================
   CONSULTAR NFE
================================ */
$('#btn-consultar').click(() => {
	getChecked(id => {
		if (!id) return swal("Alerta", "Selecione uma venda!", "warning")

		let empresa_id = $("#empresa_id").val()

		$.post(path_url + "fiscal/nfe/consultar", { id, empresa_id })
		.done(res => {
			if (res.protNFe) {
				let p = res.protNFe.infProt
				swal("Sucesso", `[${p.chNFe}] ${p.xMotivo}`, "success")
			} else {
				swal("Alerta", res.xMotivo, "warning")
			}
		})
	})
})

/* ===============================
   CANCELAR NFE
================================ */
$('#btn-cancelar').click(() => {
	getCheckedElement(el => {
		if (!el) return
		$('.numero_nfe').text(el.data('numero_nfe'))
		$('#modal-cancelar').modal('show')
	})
})

$('#btn-cancelar-send').click(() => {
	getChecked(id => {
		if (!id) return swal("Alerta", "Selecione uma venda!", "warning")

		let empresa_id = $("#empresa_id").val()
		let motivo = $('#inp-motivo-cancela').val().trim()

		if (motivo.length < 15)
			return swal("Alerta", "Informe no mínimo 15 caracteres", "warning")

		$.post(path_url + "fiscal/nfe/cancelar", { id, empresa_id, motivo })
		.done(res => {
			let inf = res.retEvento.infEvento
			swal("Sucesso", `[${inf.cStat}] ${inf.xMotivo}`, "success").then(() => {
				window.open(path_url + 'nfe/imprimir-cancela/' + id)
				location.reload()
			})
		})
		.fail(err => swal("Erro", err.responseJSON?.retEvento?.infEvento?.xMotivo || "Erro ao cancelar", "error"))
	})
})

/* ===============================
   CORRIGIR (CC-e)
================================ */
$('#btn-corrigir').click(() => {
	getCheckedElement(el => {
		if (!el) return
		$('.numero_nfe').text(el.data('numero_nfe'))
		$('#modal-corrigir').modal('show')
	})
})

$('#btn-corrige-send').click(() => {
	getChecked(id => {
		if (!id) return swal("Alerta", "Selecione uma venda!", "warning")

		let empresa_id = $("#empresa_id").val()
		let motivo = $('#inp-motivo-corrige').val().trim()

		if (motivo.length < 15)
			return swal("Alerta", "Informe no mínimo 15 caracteres", "warning")

		$.post(path_url + "fiscal/nfe/corrigir", { id, empresa_id, motivo })
		.done(res => {
			let inf = res.retEvento.infEvento
			swal("Sucesso", `[${inf.cStat}] ${inf.xMotivo}`, "success").then(() => {
				window.open(path_url + 'nfe/imprimir-cce/' + id)
				$('#modal-corrigir').modal('hide')
			})
		})
		.fail(() => swal("Erro", "Erro ao corrigir NFe", "error"))
	})
})

/* ===============================
   INUTILIZAR
================================ */
$('#btn-inutilizar').click(() => $('#modal-inutilizar').modal('show'))

$('#btn-inutiliza-send').click(() => {
	let empresa_id = $("#empresa_id").val()
	let motivo = $('#inp-motivo-inutiliza').val().trim()

	if (motivo.length < 15)
		return swal("Alerta", "Informe no mínimo 15 caracteres", "warning")

	$.post(path_url + "fiscal/nfe/inutilizar", {
		empresa_id,
		motivo,
		numero_inicial: $('#inp-numero_inicial').val(),
		numero_final: $('#inp-numero_final').val()
	})
	.done(res => {
		let inf = res.infInut
		swal(inf.cStat == 102 ? "Sucesso" : "Erro", `[${inf.cStat}] ${inf.xMotivo}`, inf.cStat == 102 ? "success" : "error")
	})
})
