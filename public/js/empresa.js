$(function () {
    $('[data-bs-toggle="popover"]').popover();
});

$('#btn-consulta-cnpj').click(() => {
    $('#btn-consulta-cnpj').addClass('spinner');
    let cnpj = $('#inp-cpf_cnpj').val();
    cnpj = cnpj.replace('.', '');
    cnpj = cnpj.replace('.', '');
    cnpj = cnpj.replace('-', '');
    cnpj = cnpj.replace('/', '');
    if (cnpj.length == 14) {
        $.ajax({
            url: 'https://www.receitaws.com.br/v1/cnpj/' + cnpj,
            type: 'GET',
            crossDomain: true,
            dataType: 'jsonp',
            success: function (data) {
                $('#btn-consulta-cnpj').removeClass('spinner');
                if (data.status == "ERROR") {
                    swal(data.message, "", "error")
                } else {
                    $('#inp-razao_social').val(data.nome)
                    $('#inp-nome_fantasia').val(data.fantasia)
                    $('#inp-rua').val(data.logradouro)
                    $('#inp-numero').val(data.numero)
                    $('#inp-bairro').val(data.bairro)
                    $('#inp-email').val(data.email)
                    let fone = data.telefone.replace("(", "").replace(")", "").replace("/", "")
                    fone = fone.substring(0, 13)
                    $('#inp-telefone').val(fone)
                    $('#inp-cidade').val(data.municipio)
                    $('#inp-email').val(data.email)
                }
            },
            error: function (e) {
                $('#btn-consulta-cnpj').removeClass('spinner');
                console.log(e)
                swal("Alerta", "Nenhum retorno encontrado para este CNPJ, informe manualmente por gentileza", "warning")
            }
        })
    } else {
        swal("Alerta", "Informe corretamente o CNPJ", "warning")
    }
});


var menu = [];
$(function () {
    menu = JSON.parse($('#menus').val())
    setTimeout(() => {
        validaCategoriaCompleta()
    }, 100)
});

$('body').on('change', '.select-all', function () {
    let check = $(this).val() == 1 ? true : false
    console.log(check)
    menu.map((m) => {
        console.log(m.titulo)
        m.titulo = m.titulo.replace(' ', '')
        $('.' + m.titulo).prop('checked', check);
    })
    setTimeout(() => {
        validaCategoriaCompleta()
    }, 100)
});


// $('body').on('click', '.check-all', function () {
//     let titulo = $(this).val()
//     titulo = titulo.replace(' ', '')
//     if ($(this).is(":checked")) {
//         acaoCheck(1, titulo)
//     } else {
//         acaoCheck(0, titulo)
//     }
//     setTimeout(() => {
//         validaCategoriaCompleta()
//     }, 100)
// })

// function acaoCheck(acao, titulo) {
//     menu.map((m) => {
//         if (titulo == m.titulo) {
//             titulo = titulo.replace(' ', '')
//             if (acao) {
//                 $('.' + titulo).prop('checked', 1);
//             } else {
//                 $('.' + titulo).prop('checked', 0);
//             }
//         }
//     })
// }


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
                            $('.' + rt).prop('checked', 1);
                            let titulo = m.titulo.replace(' ', '')
                            // m.titulo = m.titulo.replace(' ', '')
                            $('.' + titulo).prop('checked', 1);
                        }

                    })
                })
            })
            validaCategoriaCompleta();
        }
    })
})


function validaCategoriaCompleta() {
    let temp = true;
    console.clear()
    menu.map((m) => {
        isCheckAll = true;
        titulo = m.titulo.replace(' ', '')
        // $('.' + m.titulo).prop('checked', check);
        // console.log(titulo)
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

// function validaCategoriaCompleta() {
//     let temp = true;
//     // console.clear()
//     menu.map((m) => {
//         isCheckAll = true;
//         let titulo = m.titulo.replace(' ', '')
//         $('.' + titulo).each(function (i, e) {
//             if (!e.checked) {
//                 isCheckAll = false
//             }
//         })
//         if (isCheckAll) {
//             $('.todos_' + titulo).prop('checked', true);
//         } else {
//             $('.todos_' + titulo).prop('checked', false)
//         }
//     });
// }

function desmarcarTudo(call) {
    console.clear();
    menu.map((m) => {
        temp = true;
        m.subs.map((sub) => {
            let rt = sub.rota.replaceAll("/", "")
            $('.' + rt).removeAttr('checked');
            let titulo = m.titulo.replace(' ', '')
            $('.' + titulo).prop('checked', 0);
        });
    });
    call(true)
}


