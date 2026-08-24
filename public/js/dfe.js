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
        const nome = textoSeguro(v && v.nome);
        const documento = formataDocumento(textoSeguro(v && v.documento));
        const chave = textoSeguro(v && v.chave).replace(/\D/g, '');
        const valor = numeroSeguro(v && v.valor);

        if (!nome || chave.length !== 44 || valor === null) {
            return;
        }

        html += '<tr>';
        html += '<td>' + escapaHtml(nome) + '</td>';
        html += '<td>' + escapaHtml(documento || 'Não informado') + '</td>';
        html += '<td>' + valor.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'}) + '</td>';
        html += '<td class="text-nowrap">' + escapaHtml(chave) + '</td>';
        html += '</tr>';
    });
    call(html);
}

function textoSeguro(valor) {
    if (valor === null || typeof valor === 'undefined') return '';
    if (typeof valor === 'string' || typeof valor === 'number') return String(valor).trim();
    return '';
}

function numeroSeguro(valor) {
    if (typeof valor === 'number') return Number.isFinite(valor) ? valor : null;
    const texto = textoSeguro(valor).replace(/\s/g, '').replace(',', '.');
    if (!texto) return null;
    const numero = Number(texto);
    return Number.isFinite(numero) ? numero : null;
}

function formataDocumento(valor) {
    const numeros = valor.replace(/\D/g, '');
    if (numeros.length === 14) {
        return numeros.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
    }
    if (numeros.length === 11) {
        return numeros.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');
    }
    return numeros;
}

function escapaHtml(valor) {
    return $('<div>').text(valor).html();
}
