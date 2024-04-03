/**
 * Created by gpires on 11/10/2017.
 */
$(function() {

    $('form[id="form_login"]').submit(function(){

        var enviar = $.post('php/login.php',"&usuario="+$('#input_login').val()+"&senha="+$('#input_senha').val());

        enviar.done(function(resultado){
            if (resultado == 1) { //gerente
                window.location.replace("./gerente/index.php");
            }else if (resultado == 2 || resultado == 3) { //user
                window.location.replace("./usuario/index.php");
            }else {
                alert('usuário ou senha incorreto!!');
            }

        });
        enviar.fail( function(){
            alert('erro ajax consulte o admin!');
        });
        console.log(enviar);
        return false;

    })



});
