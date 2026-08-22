var menu = [];
$(function () {
    menu = JSON.parse($('#menus').val())
    setTimeout(() => {
        validaCategoriaCompleta()
    }, 100)
});

function marcarTudo() {
    console.log($(this))
}

$('body').on('change', '.select-all', function () {
    let check = $(this).val() == 1 ? true : false
    console.log(check)
    menu.map((m) => {
        console.log(m.titulo)
        $('.' + m.titulo).prop('checked', check);
    })
    setTimeout(() => {
        validaCategoriaCompleta()
    }, 100)
});

$('body').on('click', '.check-all', function () {
    let titulo = $(this).val()
    if ($(this).is(":checked")) {
        acaoCheck(1, titulo)
    } else {
        acaoCheck(0, titulo)
    }
})

function acaoCheck(acao, titulo) {
    menu.map((m) => {
        if (titulo == m.titulo) {
            titulo = titulo.replace(' ', '')
            if (acao) {
                $('.' + titulo).prop('checked', 1);
            } else {
                $('.' + titulo).prop('checked', 0);
            }
        }
    })
}

function validaCategoriaCompleta() {
    let temp = true;
    console.clear()
    menu.map((m) => {
        isCheckAll = true;
        let titulo = m.titulo.replace(" ", "_")
        titulo = titulo.replace("_", '')
        console.log(titulo)
        $('.' + titulo).each(function (i, e) {
            if (!e.checked) {
                isCheckAll = false
            }
        })
        console.log(isCheckAll)
        if (isCheckAll) {
            $('.todos_' + titulo).prop('checked', 1);
        } else {
            $('.todos_' + titulo).prop('checked', 0)
        }
        
    });
}

$('#adm').click(() => {

    if ($('#adm').is(':checked')) {

        marcarTodos(true);
    } else {
        desmarcarTodos();
    }
})

function marcarTodos() {
    menu.map((m) => {
        temp = true;
        m.subs.map((sub) => {
            let rt = sub.rota.replaceAll("/", "")
            $('#sub_' + rt).prop('checked', 1);
        });
    });
    setTimeout(() => {
        validaCategoriaCompleta()
    }, 200)
}

function desmarcarTodos() {
    menu.map((m) => {
        temp = true;
        m.subs.map((sub) => {
            let rt = sub.rota.replaceAll("/", "")
            $('#sub_' + rt).removeAttr('checked');
        });
    });
    setTimeout(() => {
        validaCategoriaCompleta()
    }, 200)
}

$('.check-sub').click(() => {
    validaCategoriaCompleta()
})

