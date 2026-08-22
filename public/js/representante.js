
var menu = [];
$(function () {
    menu = JSON.parse($('#menus').val())
    setTimeout(() => {
        validaCategoriaCompleta()
    }, 100)
});


$('#perfil-select').change(() => {
    desmarcarTudo((cl) => {
        let perfil = $('#perfil-select').val();
        if (perfil != '0') {
            perfil = JSON.parse(perfil)
            let permissao = JSON.parse(perfil.permissao)
            permissao.map((p) => {
                menu.map((m) => {
                    m.subs.map((sub) => {
                        if (sub.rota == p) {
                            let rt = sub.rota.replaceAll('/', '_')
                            rt = rt.replaceAll(':', '')
                            $('.' + rt).attr('checked', true);
                        }
                    })
                })
            })
            validaCategoriaCompleta();
        }
    })

})

$('body').on('click', '.check-all', function () {
    let titulo = $(this).val()
    if ($(this).is(":checked")) {
        acaoCheck(1, titulo)
    } else {
        acaoCheck(0, titulo)
    }
    setTimeout(() => {
        validaCategoriaCompleta()
    }, 100)
})

function acaoCheck(acao, titulo) {
    menu.map((m) => {
        if (titulo == m.titulo) {
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
    // console.clear()
    menu.map((m) => {
        isCheckAll = true;
        let titulo = m.titulo.replace(" ", "_")

        $('.' + titulo).each(function (i, e) {
            if (!e.checked) {
                isCheckAll = false
            }
        })
        if (isCheckAll) {
            $('.todos_' + titulo).prop('checked', true);
        } else {
            $('.todos_' + titulo).prop('checked', false)
        }
    });
}



function desmarcarTudo(call) {
    console.clear();
    menu.map((m) => {
        let t = m.titulo.replace(" ", "_")
        $('#todos' + t).removeAttr('checked');
        m.subs.map((sub) => {
            let rt = sub.rota.replaceAll("/", "")
            rt = rt.replaceAll(".", "_")
            rt = rt.replaceAll(":", "_")
            // $('#sub_'+rt).attr('checked', false);
            $('#sub_' + rt).removeAttr('checked');
        })
    })
    call(true)
}
