var DIVISOES = [];
var SUBDIVISOES = [];
var DIVISOESSELECIONADAS = [];
var SUBDIVISOESSELECIONADAS = [];
var COMBINACOES = [];

function prepara() {
    let oldComb = $('#combinacoes').val()
    if (oldComb) {
        oldComb = JSON.parse(oldComb)
    }
    console.log(oldComb)
    console.log(DIVISOES)
    for (let i = 0; i < DIVISOES.length; i++) {
        if (oldComb) {
            let marca = false
            oldComb.map((o) => {
                if (o.combinacao == DIVISOES[i].id) {
                    marca = true
                }
            })
            DIVISOES[i].selecionado = marca;
        } else {
            DIVISOES[i].selecionado = false;
        }
    }
    for (let i = 0; i < SUBDIVISOES.length; i++) {
        SUBDIVISOES[i].selecionado = false;
    }
    console.log(DIVISOES)
    console.log(SUBDIVISOES)
    montaDivisoes()
    montaSubDivisoes()
}

$('#inp-grade').change(() => {
    isGrade()
})

$(document).ready(function () {
    $('#inp-grade').change(() => {
        isGrade()
    });
    $(document).on('show.bs.modal', '.modal', function (event) {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function () {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });
});



function isGrade() {
    let is = $('#inp-grade').val()
    if (is == 1) {
        $('#modal-grade').modal('show')
    } else {
        $('#combinacoes').val('')
    }
}

function montaDivisoes() {
    let html = '';
    DIVISOES.map((rs) => {
        let cor = rs.selecionado ? 'success' : 'light'
        html += '<a style="margin-left: 4px;" class="btn btn-' + cor + '" onclick="selectDivisao(' + rs.id + ')">';
        html += rs.nome
        html += '</a>'
    })
    $('.divisoes').html(html)
}

function montaSubDivisoes() {
    let html = '';
    SUBDIVISOES.map((rs) => {
        let cor = rs.selecionado ? 'info' : 'light'
        html += '<a style="margin-left: 4px;" class="btn btn-' + cor + '" onclick="selectSubDivisao(' + rs.id + ')">';
        html += rs.nome
        html += '</a>'
    })
    $('.subDivisoes').html(html)
}

$(function () {
    try {
        DIVISOES = JSON.parse($('#divisoes').val());
        SUBDIVISOES = JSON.parse($('#subDivisoes').val());
        prepara()
    } catch { }
})

function selectDivisao(id) {
    for (let i = 0; i < DIVISOES.length; i++) {
        if (DIVISOES[i].id == id) {
            DIVISOES[i].selecionado = !DIVISOES[i].selecionado;
        }
    }
    setTimeout(() => {
        montaDivisoes();
    }, 100)
}

function selectSubDivisao(id) {
    for (let i = 0; i < SUBDIVISOES.length; i++) {
        if (SUBDIVISOES[i].id == id) {
            SUBDIVISOES[i].selecionado = !SUBDIVISOES[i].selecionado;
        }
    }
    setTimeout(() => {
        montaSubDivisoes();
    }, 100)
}

function escolhaDivisao() {
    DIVISOESSELECIONADAS = DIVISOES.filter((x) => {
        if (x.selecionado) return x;
    })
    SUBDIVISOESSELECIONADAS = SUBDIVISOES.filter((x) => {
        if (x.selecionado) return x;
    })
    if (DIVISOESSELECIONADAS.length > 0 || SUBDIVISOESSELECIONADAS.length > 0) {
        $('#modal-grade').modal('hide')
        divisoesMontadas(DIVISOESSELECIONADAS)
        divisoesMontadas(SUBDIVISOESSELECIONADAS)
    } else {
        swal("Erro", "Selecione ao menos uma divisão ou subdivisão", "error")
    }
}

function divisoesMontadas(DIVISOESSELECIONADAS, SUBDIVISOESSELECIONADAS) {
    console.log(DIVISOESSELECIONADAS)
    let valor = $('#inp-valor_venda').val()
    $.get(path_url + "api/produtos/montarGrade", { divisoes: DIVISOESSELECIONADAS, subDivisoes: SUBDIVISOESSELECIONADAS })
        .done((e) => {
            console.log(e)
            $('#modal-grade2').modal('show')
            $('.combinacoes').html(e)
            $('.valor_grade').val(valor)
            $('.quantidade_grade').val('0')
        })
        .fail((e) => {
            console.log(e);
        });
}
