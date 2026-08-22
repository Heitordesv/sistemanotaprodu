function editLancamento(lancamento){
	$('#modal-edit').modal('show')

	$('#titulo').html(lancamento.nome)
	$('#nome-edit').val(lancamento.nome)
	$('#valor').val(convertFloatToMoeda(lancamento.valor))
	$('#lancamento_id').val(lancamento.id)
}

function addLancamento(categoria){
	$('#modal-new').modal('show')
	$('#titulo-new').html(categoria.nome)
	$('#categoria_id').val(categoria.id)

}