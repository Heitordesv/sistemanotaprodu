var menu = [];
$(function () {
	menu = JSON.parse($('#menus').val())
	validaCategoriaCompleta()
});
function marcarTudo(titulo) {
	titulo = titulo.replace(" ", "_")
	let marked = $('#todos_' + titulo).is(':checked')
	if (!marked) {
		acaoCheck(false, titulo)
	} else {
		acaoCheck(true, titulo)
	}
}

function acaoCheck(acao, titulo) {
	menu.map((m) => {
		let t = m.titulo.replace(" ", "_")
		if (titulo == t) {
			m.subs.map((sub) => {
				let rt = sub.rota.replaceAll("/", "")
				rt = rt.replaceAll(".", "_")
				rt = rt.replaceAll(":", "_")

				if (acao) {
					$('#sub_' + rt).attr('checked', true);
				} else {
					$('#sub_' + rt).removeAttr('checked');
				}
			})
		}
	})
}

function validaCategoriaCompleta() {
	let temp = true;
	menu.map((m) => {
		temp = true;
		m.subs.map((sub) => {
			let rt = sub.rota.replaceAll("/", "")
			rt = rt.replaceAll(".", "_")
			rt = rt.replaceAll(":", "_")
			let marked = $('#sub_' + rt).is(':checked')
			if (!marked && sub.nome != "NFS-e") temp = false;
		})
		let t = m.titulo.replace(" ", "_")
		if (temp) {
			$('#todos_' + t).prop('checked', true);
		} else {

			$('#todos_' + t).prop('checked', false)
		}
	});
}

$('.check-sub').click(() => {
	validaCategoriaCompleta()
})