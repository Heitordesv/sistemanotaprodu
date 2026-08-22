function getFornecedores(data) {
    $.ajax
        ({
            type: 'GET',
            url: path + 'fornecedores/all',
            dataType: 'json',
            success: function (e) {
                data(e)
            }, error: function (e) {
                console.log(e)
            }

        });
}

function getFornecedor(id, data){
	$.ajax
	({
		type: 'GET',
		url: path + 'fornecedor/find/'+id,
		dataType: 'json',
		success: function(data){
            console.log(data)
		}, error: function(e){
			console.log(e)
		}

	});
}