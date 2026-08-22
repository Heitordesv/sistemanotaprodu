
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$("#form-password-notify").submit(async function(e){
    e.preventDefault();

    let route = $(this).attr('action');

    showLoading();

    await $.ajax({
        type: "PATCH",
        url: route,
        data: { 
            email: $('#email').val() 
        },
        
        success: function(res) {
            hideLoading();

            if (res.msg == 'success') {
                Swal.fire({
                    icon: 'success',
                    title: '',
                    html: '<div class="text-success">'+ res.text +'</div>',                    
                }).then(function(){
                    window.location.replace(res.route);
                });                
            }
        },

        error: function(error){  
            hideLoading();

            showMessaErro(error);
        }
    });
});

$("#form-password-new").submit(async function(e){
    e.preventDefault();

    let route = $(this).attr('action');

    showLoading();

    await $.ajax({
        type: "PATCH",
        url: route,
        data: { 
            email: $('#email').val(),
            token: $('#token').val(),
            password: $('#password').val(),
            password_confirmation: $('#password_confirmation').val(),
        },
        
        success: function(res) {
            hideLoading();

            if (res.msg == 'success') {
                Swal.fire({
                    icon: 'success',
                    title: '',
                    html: '<div class="text-success">'+ res.text +'</div>',                    
                }).then(function(){
                    window.location.replace(res.route);
                });                
            }
        },

        error: function(error){  
            hideLoading();
                      
            showMessaErro(error);
        }
    });
});

/** 
 * MESSAGES DE ERRO GENÃ‰RICA 
 **/
var showMessaErro = function (erro)
{
    let title = 'Oops!';
    let html = '<div class="text-danger">Aconteceu um erro ao salvar no banco de dados, entre em contato com suporte. <strong>Informa a tela que estÃ¡ dando o erro.</strong></div>';     

    if(erro.responseJSON.errors){
        let errors = erro.responseJSON.errors;
        var errorsHtml = '<div>';
            $.each(errors, function (key, value) {
                errorsHtml += '<div class="text-danger">' + value[0] + '</div>';
            });
        errorsHtml += '</div>';       

        html = errorsHtml;
    } else {
        if(erro.responseJSON.msg){
            html = '<div class="text-danger">'+ erro.responseJSON.msg +'</div>';            
        } 
    }

    Swal.fire({
        icon: 'error',
        title: title,
        html: html,
        confirmButtonColor: '#435EBE',  
        confirmButtonText: 'Fechar'      
    });
}

/** 
 * LOADING 
 **/
function showLoading() {
    $(".loading-page").removeClass('d-none');
}
/** 
 * LOADING HIDE
 **/
function hideLoading() {
    $(".loading-page").addClass('d-none');
}