$(function () {
    buscarDocumentos();
});

function buscarDocumentos() {
    const endpoint = window.dfeEndpoints && window.dfeEndpoints.novosDocumentos;
    if (!endpoint) {
        swal("Erro", "A rota de consulta de documentos não foi configurada.", "error");
        return;
    }

    $('#aguarde').removeClass('d-none');
    $('#btn-enviar').addClass('disabled').attr('aria-disabled', 'true');

    $.get(endpoint)
    .done((res) => {
        // Se retornar uma lista de notas (Array)
        if (Array.isArray(res) && res.length > 0) {
            montaTabela(res, (html) => {
                $('table tbody').html(html);
                $('#table').css('display', 'block');
                $('#sem-resultado').css('display', 'none');
            });
            swal("Sucesso", "Encontramos " + res.length + " novas notas!", "success");
        } 
        // Se retornar o aviso de 1 hora
        else if (res.message) {
            swal("Aviso", res.message, "info");
            $('#sem-resultado').css('display', 'block');
            $('#table').css('display', 'none');
        } 
        else {
            swal("Tudo Atualizado", "Nenhuma nota nova encontrada.", "success");
            $('#sem-resultado').css('display', 'block');
        }
    })
    .fail((err) => {
        let msg = (err.responseJSON && err.responseJSON.message)
            ? err.responseJSON.message
            : "Não foi possível consultar a SEFAZ.";
        swal("Erro", msg, "error");
    })
    .always(() => {
        $('#aguarde').addClass('d-none');
        $('#btn-enviar').removeClass('disabled').removeAttr('aria-disabled');
    });
}

function montaTabela(array, call) {
    let html = '';
    array.forEach(v => {
        html += '<tr>';
        html += '<td>' + v.nome + '</td>';
        html += '<td>' + v.documento + '</td>';
        html += '<td>' + parseFloat(v.valor).toLocaleString('pt-br', {style: 'currency', currency: 'BRL'}) + '</td>';
        html += '<td>' + v.chave + '</td>';
        html += '</tr>';
    });
    call(html);
}
