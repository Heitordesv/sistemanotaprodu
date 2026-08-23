$(function(){

})

$('.btn-consulta-status').click(() => {
	let token = $('#_token').val();
	let empresa_id = $("#empresa_id").val();

	$.post(path_url + 'api/nfce/consulta-status-sefaz',{ empresa_id: empresa_id })
	.done((res) => {
		console.log(res)
		let msg = "cStat: " + res.cStat
		msg += "\nMotivo: " + res.xMotivo
		msg += "\nAmbiente: " + (res.tpAmb == 2 ? "Homologação" : "Produção")
		msg += "\nverAplic: " + res.verAplic
		
		swal("Sucesso", msg, "success")
	})
	.fail((err) => {
		console.log(err)
		try{
			swal("Erro", err.responseText, "error")
		}catch{
			swal("Erro", "Algo deu errado", "error")
		}
	})
})
function emitirNFCe(id){
	console.clear()
	let empresa_id = $("#empresa_id").val();
	$.post(path_url + "api/nfce/transmitir", {
		id: id,
		empresa_id: empresa_id,
	})
	.done((success) => {
		console.log(success)
		if(success == 'OFFL'){
			swal("Alerta", "NFCe gerada em contigência!", "success").then(() => {
				window.open(path_url + 'nfce/imprimir/'+vendaId, '_blank');
				location.reload()
			})
		}else{
			swal("Sucesso", "NFCe emitida " + success, "success")
			.then(() => {
				window.open(path_url+'nfce/imprimir/'+id, "_blank")
				setTimeout(() => {
					location.reload()
				}, 100)
			})
		}
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
}

function consultarNFCe(id){
	let empresa_id = $("#empresa_id").val();
	$.post(path_url + "api/nfce/consultar", {
		id: id,
		empresa_id: empresa_id,
	})
	.done((success) => {
		console.log(success)
		if(success.protNFe){
			let infProt = success.protNFe.infProt
			swal("Sucesso", "[" + infProt.chNFe + "] " + infProt.xMotivo, "success")
		}else{
			swal("Erro", "[" + success.chNFe + "] " + success.xMotivo, "error")
		}

	})
	.fail((err) => {
		console.log(err)
		swal("Algo deu errado", err.responseJSON, "error")

	})
}

function modalCancelar(id, numero){
    $('#modal-cancelar').modal('show')
    $('.numero_nfce').text(numero)
    $('#numero_venda').val(id)
    $('#cancelamento_admin_id').val('')
    $('#cancelamento_admin_senha').val('')
}

$('#btn-cancelar-send').click(() => {
    let id = $('#numero_venda').val()

    if(!id){
        swal("Alerta", "Selecione uma venda!", "warning")
        return
    }

    let empresa_id = $("#empresa_id").val()
    let motivo = $('#inp-motivo-cancela').val()
    let admin_id = $('#cancelamento_admin_id').val()
    let admin_senha = $('#cancelamento_admin_senha').val()
    let csrf = $('meta[name="csrf-token"]').attr('content') || $('#_token').val()

    if(motivo.length < 15){
        swal("Alerta", "Informe no mínimo 15 caracteres", "warning")
        return
    }

    if(!window.usuarioDevolucaoEhAdministrador && (!admin_id || !admin_senha)){
        swal("Autorização necessária", "Selecione o administrador e informe a senha.", "warning")
        return
    }

    let botao = $('#btn-cancelar-send')
    botao.prop('disabled', true).text('Autorizando...')

    $.post(path_url + "api/nfce/cancelar", {
        _token: csrf,
        id: id,
        empresa_id: empresa_id,
        motivo: motivo,
        admin_id: admin_id,
        admin_senha: admin_senha
    })
    .done((success) => {
        let infEvento = success.retEvento.infEvento
        let mensagem = "[" + infEvento.cStat + "] " + infEvento.xMotivo

        if(success.pdv_devolucao && success.pdv_devolucao.pendente_financeiro){
            mensagem += "\n\nA NFC-e e o estoque foram cancelados, mas existe uma reconciliação financeira pendente. Não refaça o cancelamento."
        }

        swal("Sucesso", mensagem, "success")
        .then(() => {
            window.open(path_url+'nfe/imprimir-cancela/'+id, "_blank")
            setTimeout(() => {
                location.reload()
            }, 100)
        })
    })
    .fail((err) => {
        let mensagem = typeof mensagemErroDevolucao === 'function'
            ? mensagemErroDevolucao(err)
            : 'Não foi possível concluir a devolução.'

        try{
            if(err.responseJSON.retEvento.infEvento.xMotivo){
                mensagem = err.responseJSON.retEvento.infEvento.xMotivo
            }
        }catch(e){}

        try{
            if(err.responseJSON.message){
                mensagem = err.responseJSON.message
            }
        }catch(e){}

        swal("Não foi possível devolver", mensagem, "error")
    })
    .always(() => {
        botao.prop('disabled', false).text('Confirmar devolução')
        $('#cancelamento_admin_senha').val('')
    })
})

$('#btn-inutilizar-send').click(() => {
	let empresa_id = $("#empresa_id").val();
	let motivo = $('#inp-justificativa').val()
	let numero_serie = $('#inp-numero_serie').val()
	let numero_inicial = $('#inp-numero_nfce_inicial').val()
	let numero_final = $('#inp-numero_nfce_final').val()
	if(motivo.length >= 15){
		$.post(path_url + "api/nfce/inutilizar", {
			empresa_id: empresa_id,
			motivo: motivo,
			numero_inicial: numero_inicial,
			numero_final: numero_final
		})
		.done((success) => {

			console.log(success)
			let infInut = success.infInut
			if(infInut.cStat == "102"){
				$('#modal-inutilizar_nfce').modal('hide')
				swal("Sucesso", "[" + infInut.nProt + "] " + infInut.xMotivo, "success")
			}else{
				swal("Erro", "[" + infInut.cStat + "] " + infInut.xMotivo, "error")
			}

		})
		.fail((err) => {
			console.log(err)
			
			swal("Algo deu errado", err.responseJSON, "error")

		})
	}else{
		swal("Alerta", "Informe no mínimo 15 caracteres", "warning")
	}
})
