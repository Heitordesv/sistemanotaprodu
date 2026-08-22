var Login = function () {
	
	var handleForgetPassword = function () {		
		
		$('#forget-password').on('click', function(){		
			$('#form-login').addClass('hide');
			$('.div-recuperar-senha-sicok').removeClass('hide');									
		});
		
		$('#back-btn').on('click', function(){						
			$('.div-recuperar-senha-sicok').addClass('hide');
			$('#form-login').removeClass('hide');									
		});
		
	}
	
	var recuperarSenhaSicok = function () {
		
		$('.btn-repuerar-senha-sicok').on('click', function(){		
			
			var email = $('.input-email-recuperar-senha-sicok').val();
			
			var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;			
			
			if (re.test(email) == false) {
				Login.alertSicok('Erro!', 'E-mail nÃ£o Ã© vÃ¡lido,', 'red');	
				return false;
			}
			
			var jc = $.dialog({
				icon: 'fa fa-spinner fa-spin',
				title: 'Aguarde um instante...',
				content: '',															
				theme: 'modern',									                    
				type: 'dark',
				closeIcon: false				
			});
			
			$.ajax({
				type: 'post',
				url: url_app +'recuperar',
				data: {email:email}
			}).then(function(data){						
				jc.close();
				if(data.erro == 0){																
					
					$.alert({
						title: 'Sucesso!',
						content: data.msg,
						icon: 'fa fa-check-circle-o',					
						theme: 'modern',									
						closeIcon: true,                    
						type: 'green',										
						buttons: {							
							login: {
								text: 'Fazer login',
								btnClass: 'btn-success',
								action: function (){
									$('.div-recuperar-senha-sicok').addClass('hide');
									$('#form-login').removeClass('hide');	
								}
							}
						}				
					});
					
				} else{																	
					Login.alertSicok('Erro!', data.msg, 'red');					
				}											
			}, function(){
				jc.close();				
				Login.alertSicok('Erro!', 'Ocorreu um erro tente novamente por favor, ou entre em contato com nosso suporte.', 'red');		
			})
		});
	}
	
	// return    
    return {
        //main function to initiate the module
        init: function () {     

			handleForgetPassword();			
			recuperarSenhaSicok();

            // init background slide images
		    $.backstretch([
		        "/assets/images/img1.jpg",
		        "/assets/images/img2.png",
		        "/assets/images/img3.png",
		        
		        ], {
		          fade: 1000,
		          duration: 8000,
		    	}
        	);
			
			// $('.selecionado_check').iCheck({
			// 	  checkboxClass: 'icheckbox_square-blue',
			// 	  radioClass: 'iradio_square-blue',
			// 	  increaseArea: '20%' // optional
			// });
			
        },
		
		alertSicok: function (titulo, conteudo, tipo){
			
			$.alert({
				title: titulo,
				content: conteudo,
				icon: 'fa fa-exclamation-triangle',					
				theme: 'modern',									
				closeIcon: true,                    
				type: tipo,										
				buttons: {
					close: {
						text: 'Fechar',
						btnClass: 'btn-primary',
					}					                        
				}				
			});
			
		}
    };

}();

jQuery(document).ready(function() {
    Login.init();
});