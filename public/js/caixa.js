// function abrirCaixa(){

// 	let valor = $('#inp-valor').val();

// 	valor = parseFloat($('#inp-valor').val().replace(',', '.'));
// 	if(parseFloat(valor) >= 0){
//         let js = {
//             empresa_id: $('#empresa_id').val(),
//             valor: valor,
//             _token: '{{ csrf_token() }}'
//         }
//         $.post(path_url + 'aberturaCaixa/storeCaixa', js)
//         .done((data) => {
//             //console.log(data)
//             $('#inp-valor')
//             var newOption = new Option(data.valor, data.id, false, false);
//             $('#inp-valor').append(newOption).trigger('change');
//             $('#modal-abrir_caixa').modal('hide')
//         }).fail((err) => {
//             console.log(err)
//         })
//     } else {
//         swal("Erro", "Informe um valor", "warning")
//     }
// }

