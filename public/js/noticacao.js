$(function(){
	$('.loading-class').removeClass('modal-loading')

	const sessionHashInput = $('#notification_session_hash')

	// Usuário comum não recebe nem tenta carregar notificações.
	// O campo abaixo só existe no header quando adm = 1.
	if (!sessionHashInput.length) {
		$('.loading-class').addClass('modal-loading')
		return
	}

	const usuarioId = $('#usuario_id').val()
	const sessaoHash = sessionHashInput.val()

	// O backend valida usuário + sessão ativa + adm + empresa.
	$.get(path_url+'api/notificacoes', {
		hash: hash,
		usuario_id: usuarioId,
		sessao_hash: sessaoHash
	})
	.done((success) => {
		$('.loading-class').addClass('modal-loading')
		$('.header-notifications-list').html(success)
		setTimeout(() => {
			$('.alert-count').text($('.alert-item').length)
		}, 10)
	}).fail((err) => {
		console.log(err)
		$('.loading-class').addClass('modal-loading')
		$('.header-notifications-list').html('')
		$('.alert-count').text('0')
	})
})