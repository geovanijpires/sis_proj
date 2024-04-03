$(function() {

    /* $('#select_projeto').autocomplete({
        
        minLength: 3,
        
        source: function(request, response) {
            $.ajax({
              url: 'check_name.php',
              type: 'get',
              dataType: 'json',
              data: {
                name_startsWith: request.term,
                row_num: 1
              },
              success: function(data) {
                response(
                  $.map(data, function(item) {
                    var code = item.split('|');
                    console.log('CODE', code);
                    return {
                      label: code[0],
                      value: code[0],
                      data: item
                    };
                  })
                );
              }
            });
          },  
              
        
          
        
    }); 
    */

    
        


    $('#select_projeto').change(function() {
        $('#select_etapa').prop('selectedIndex',0);
        $('#select_subetapa').prop('selectedIndex',0);

        $('#select_etapa').prop('disabled', false);
        $('#select_subetapa').prop('disabled', false);
    });

    //SELECT BUSCA SUBETAPA
    $('#select_etapa').change(function(){

        var id_projeto = $('#select_projeto').val();
        var etapa = $('#select_etapa').val();
        var etapa_text = $('#select_etapa option:selected').text();

        //teste se diferente de despesas na subetapa
        if(etapa_text != "Despesas") {

            $('#div_subetapa').css('display', 'block');
            $('#div_subetapa_despesas').css('display', 'none');
            $('#resto_nao_despesa').css('display', 'block');


            var enviar = $.post('php/funcoes.php', "&acao=busca_subetapa&id_projeto=" + id_projeto + "&etapa=" + etapa);
            enviar.done(function (resultado) {
                $('#select_subetapa').html(resultado);
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
            return false;

        }else if(etapa_text == "Despesas"){

            $('#div_subetapa').css('display', 'none');
            $('#resto_nao_despesa').css('display', 'none');
            $('#div_subetapa_despesas').css('display', 'block');


        }

    });

    $('#btn_inserir_projeto').click(function () {
          var data = $('#input_datepicker').val();
          var projeto = $('#select_projeto').val();
          var etapa = $('#select_etapa').val();
          var subetapa = $('#select_subetapa').val();
        var inicio_atividade = $('#inicio_atividade').val();
        var fim_atividade = $('#fim_atividade').val();

            var partesData = data.split("/");
            var validate_data = new Date(partesData[2], partesData[1] - 1, partesData[0]);

    //VALIDAÇÔES
    if(data == ''){
        $('#div_mensagem').html('Por favor selecione uma data!!!');
        $('#div_mensagem').attr('class', 'alert alert-danger');
        $('#div_mensagem').css('display', 'block');
        $('#input_datepicker').focus();
    }else if(validate_data > new Date()){
        $('#div_mensagem').html('A data selecionada e maior que a data atual!!!');
        $('#div_mensagem').attr('class', 'alert alert-danger');
        $('#div_mensagem').css('display', 'block');
        $('#data').focus();
    }else  if(projeto == null){
            $('#div_mensagem').html('Por favor selecione o projeto!!!');
            $('#div_mensagem').attr('class', 'alert alert-danger');
            $('#div_mensagem').css('display', 'block');
            $('#select_projeto').focus();
    }else if(etapa == null){
        $('#div_mensagem').html('Por favor selecione a etapa do projeto!!!');
        $('#div_mensagem').attr('class', 'alert alert-danger');
        $('#div_mensagem').css('display', 'block');
        $('#select_etapa').focus();
    }else if(subetapa == null){
        $('#div_mensagem').html('Por favor selecione uma subetapa!!!');
        $('#div_mensagem').attr('class', 'alert alert-danger');
        $('#div_mensagem').css('display', 'block');
        $('#select_subetapa').focus();
    }else if(inicio_atividade == '' || fim_atividade == ''){
        $('#div_mensagem').html('Por favor insira o inicio e fim das atividades!!!');
        $('#div_mensagem').attr('class', 'alert alert-danger');
        $('#div_mensagem').css('display', 'block');
        $('#inicio_atividade').focus();
    }
     else {


            var enviar = $.post('php/funcoes.php', "&acao=inserir_folha_proj&data=" + data + "&projeto=" + projeto + "&etapa=" + etapa + "&subetapa=" + subetapa + "&inicio_atividade=" + inicio_atividade + "&fim_atividade=" + fim_atividade);
            enviar.done(function (resultado) {
                if (resultado == 0) {
                    alert('Registro inserido com sucesso!!!');
                    window.location.reload();

                } else if (resultado == 2) {
                    alert("Existe um conflito de horas com registros anteriores deste dia, por favor defina outro inicio e fim para a atividade.\n\nObs: Você pode consulta nos relatórios as horas já inseridas e que estão em conflito.");
                }else if (resultado == 3){
                    alert('Você tem algum período de férias / atestados ou Dayoff nesta data, verifique ou selecione outra data!!!');
                }
                else {
                    alert('Erro ao inserir registro, verifique as informações!!!');

                }


            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });




        console.log(enviar);
        return false;
    }
        })


    //btn insere despesas

    $('#btn_inserir_despesas').click(function () {

        var data = $('#input_datepicker').val();
        var projeto = $('#select_projeto').val();
        var despesa = $('#total_despesa').val();
        var partesData = data.split("/");
        var validate_data = new Date(partesData[2], partesData[1] - 1, partesData[0]);


        //VALIDAÇÔES
        if(data == ''){
            $('#div_mensagem').html('Por favor selecione uma data!!!');
            $('#div_mensagem').attr('class', 'alert alert-danger');
            $('#div_mensagem').css('display', 'block');
            $('#input_datepicker').focus();
        }else if(validate_data > new Date()){
            $('#div_mensagem').html('A data selecionada e maior que a data atual!!!');
            $('#div_mensagem').attr('class', 'alert alert-danger');
            $('#div_mensagem').css('display', 'block');
            $('#data').focus();
        }else  if(projeto == null){
            $('#div_mensagem').html('Por favor selecione o projeto!!!');
            $('#div_mensagem').attr('class', 'alert alert-danger');
            $('#div_mensagem').css('display', 'block');
            $('#select_projeto').focus();
        }else if(despesa == ''){
            $('#div_mensagem').html('Por favor insira o valor da despesa!!!');
            $('#div_mensagem').attr('class', 'alert alert-danger');
            $('#div_mensagem').css('display', 'block');
            $('#total_despesa').focus();
        }
        else {

            var enviar = $.post('php/funcoes.php', "&acao=inserir_despesa&data=" + data + "&projeto=" + projeto + "&despesa=" + despesa);
            enviar.done(function (resultado) {

                if (resultado == 0) {
                    alert('Registro inserido com sucesso!!!');
                    window.location.reload();

                }
                else {
                    alert('Erro ao inserir registro, verifique as informações!!!');

                }

            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });

            console.log(enviar);

        }
    })



    $('#btn_inserir_adm').click(function () {
        var data = $('#input_datepicker_adm').val();
        var adm = $('#select_adm').val();
        var inicio_atividade_adm = $('#inicio_atividade_adm').val();
        var fim_atividade_adm = $('#fim_atividade_adm').val();

        var partesData = data.split("/");
        var validate_data = new Date(partesData[2], partesData[1] - 1, partesData[0]);

        if(data == ''){
            $('#div_mensagem').html('Por favor selecione uma data!!!');
            $('#div_mensagem').attr('class', 'alert alert-danger');
            $('#div_mensagem').css('display', 'block');
            $('#input_datepicker_adm').focus();
        }else if(validate_data > new Date()){
            $('#div_mensagem').html('A data selecionada e maior que a data atual!!!');
            $('#div_mensagem').attr('class', 'alert alert-danger');
            $('#div_mensagem').css('display', 'block');
            $('#data').focus();
        }else if(adm == null){
            $('#div_mensagem').html('Por favor selecione as horas administrativas!!!');
            $('#div_mensagem').attr('class', 'alert alert-danger');
            $('#div_mensagem').css('display', 'block');
            $('#select_adm').focus();
        }else if(inicio_atividade_adm == '' || fim_atividade_adm == ''){
            $('#div_mensagem').html('Por favor insira o inicio e o fim das atividades!!!');
            $('#div_mensagem').attr('class', 'alert alert-danger');
            $('#div_mensagem').css('display', 'block');
            $('#inicio_atividade_adm').focus();
        }

        else {

            var enviar = $.post('php/funcoes.php', "&acao=inserir_folha_adm&data=" + data + "&adm=" + adm + "&inicio_atividade_adm=" + inicio_atividade_adm + "&fim_atividade_adm=" + fim_atividade_adm);
            enviar.done(function (resultado) {
                if (resultado == 0) {
                    alert('Registro inserido com sucesso!!!');
                    window.location.reload();

                }else if(resultado == 2){
                    alert("Existe um conflito de horas com registros anteriores deste dia, por favor defina outro inicio e fim para a atividade.\n\nObs: Você pode consulta nos relatórios as horas já inseridas e que estão em conflito.");
                }

                else {
                    alert('Erro ao inserir registro, verifique as informações!!!');

                }

            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
            return false;
        }
    })

    $('#select_rel_periodo').change(function () {
        if($('#select_rel_periodo').val() == 'rel_intervalo'){
            $('#div_h_proj_datepicker').css('display','block');
        }else {
            $('#div_h_proj_datepicker').css('display','none');
        }
    })


    //ajax gera relatorio do projeto
    $('#btn_gerar_rel_ger_proj').click(function () {
        var id_projeto = $('#select_ger_projeto').val();
        
        if($('#select_rel_periodo').val() == 'rel_all'){

            var enviar = $.post('php/funcoes.php', "&acao=rel_all_proj&id_projeto="+id_projeto);
            enviar.done(function (resultado) {
                $('#div_rel').html(resultado);

                $('#div_rel').dialog({
                    modal: true,
                    width:'95%'
                });
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);


        }else if($('#select_rel_periodo').val() == 'rel_intervalo'){
            var start_date_p = $('#start_date_p').val();
            var end_date_p = $('#end_date_p').val();

            var enviar = $.post('php/funcoes.php', "&acao=rel_date_proj&id_projeto="+id_projeto+"&start_date_p="+start_date_p+"&end_date_p="+end_date_p);
            enviar.done(function (resultado) {
                $('#div_rel').html(resultado);

                $('#div_rel').dialog({
                    modal: true,
                    width:'80%'
                });
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
            
        }
        
        
    })

    $('#btn_gerar_rel_ger_adm').click(function () {

        var start_data_adm = $('#start_date_padm').val();
        var end_data_adm = $('#end_date_padm').val();

        if(start_data_adm == ''){
            alert('Por favor selecione uma data de início!!');
            $('#start_date_padm').focus();
        }else if(end_data_adm == ''){
            alert('Por favor selecione uma data de fim!!');
            $('#end_date_padm').focus();
        }else {

            var enviar = $.post('php/funcoes.php', "&acao=rel_all_adm&start_data_adm=" + start_data_adm + "&end_data_adm=" + end_data_adm);
            enviar.done(function (resultado) {
                $('#div_rel').html(resultado);

                $('#div_rel').dialog({
                    modal: true,
                    width: '95%'
                });
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
        }

    })



    $('#btn_gerar_media').click(function () {
        var id_tipo = $('#select_ger_media').val();
        var enviar = $.post('php/funcoes.php', "&acao=rel_media&id_tipo="+id_tipo);
        enviar.done(function (resultado) {
            $('#div_media').html(resultado);

        });
        enviar.fail(function () {
            alert('erro ajax consulte o admin!');
        });
        console.log(enviar);

    })

    $('#btn_cadastrar_proj').click(function () {

        $('#div_cadastrar_proj').css('display','block');
        $('#div_cadastrar_proj').dialog({
            height: 'auto',
            width: '50%',
            modal: true
        })


    })


    $("#div_cad_proj").on("click",".btn_editar_proj", function(){
        var id_editar = $(this).attr('id');


    })

    $("#div_cad_proj").on("click",".btn_excluir_proj", function(){
        var id_excluir = $(this).attr('id');

        $('#div_confirma_exclusao_proj').dialog({

            height: 'auto',
            modal: true,
            buttons: {
                "Sim": function() {

                    var enviar = $.post('php/funcoes.php',"&acao=excluir_proj&id="+id_excluir);
                    enviar.done(function(resultado){
                        $('#div_confirma_exclusao_proj').css('display','block');
                           
                        window.location.reload();
                    });

                    enviar.fail( function(){ //se der erro, esta na funcao
                        alert('erro ao validar no ajax!');

                    });

                },

                "Cancelar": function(){

                    $( this ).dialog( "close" );
                }

            }

        })


    })

$('#btn_insere_projeto').click(function () {
    var cod_proj = $('#cod_proj').val();
    var nome_proj = $('#nome_proj').val();
    var metragem_proj = $('#metragem_proj').val();
    var comodos_proj = $('#comodos_proj').val();
    var valor_proj = $('#valor_proj').val();
    var tx_adm = $('#tx_adm').val();
    var visitas_proj = $('#visitas_proj').val();
    var tipo_proj = $('#select_tipo_proj').val();

    if(cod_proj == ''){
        alert('Por favor preencha o campo código do projeto!');
        $('#cod_proj').focus();
    }else if(nome_proj == ''){
        alert('Por favor preencha o campo nome do projeto!');
        $('#nome_proj').focus();
    }else if(metragem_proj == ''){
        alert('Por favor preencha o campo metragem do projeto!');
        $('#metragem_proj').focus();
    }else if(comodos_proj == ''){
        alert('Por favor preencha o campo cômodos do projeto!');
        $('#comodos_proj').focus();
    }else if(valor_proj == ''){
        alert('Por favor preencha o campo valor do projeto!');
        $('#valor_proj').focus();
    }else if(tx_adm == ''){
        alert('Por favor preencha o campo taxa administrativa!');
        $('#tx_adm').focus();
    }else if(visitas_proj == ''){
        alert('Por favor preencha o campo visitas do projeto!');
        $('#visitas_proj').focus();
    }else {

        var enviar = $.post('php/funcoes.php', "&acao=insere_new_project&cod_proj=" + cod_proj + "&nome_proj=" + nome_proj + "&metragem_proj=" + metragem_proj + "&comodos_proj=" + comodos_proj + "&valor_proj=" + valor_proj + "&tx_adm=" + tx_adm + "&visitas_proj=" + visitas_proj + "&tipo_proj=" + tipo_proj);
        enviar.done(function (resultado) {
            //$('#div_media').html(resultado);
            if (resultado == 0) {
                alert('Projeto cadastrado com sucesso!');
                window.location.reload();
            } else if (resultado == 1) {
                alert('Erro ao cadastrar o projeto, verifique as informações!');
            } else if (resultado == 2) {
                alert('Código do projeto já esta em uso, favor defina outro!');
            }
            //
        });
        enviar.fail(function () {
            alert('erro ajax consulte o admin!');
        });
        console.log(enviar);
    }

})


    $('#btn_cadastrar_func').click(function () {

        $('#div_cadastrar_func').css('display','block');
        $('#div_cadastrar_func').dialog({
            height: 'auto',
            width: '50%',
            modal: true
        })


    })

    //excluir funcionario

    $("#div_cad_func").on("click",".btn_excluir_func", function(){
        var id_excluir = $(this).attr('id');

        $('#div_confirma_exclusao_func').dialog({

            height: 'auto',
            modal: true,
            buttons: {
                "Sim": function() {

                    var enviar = $.post('php/funcoes.php',"&acao=excluir_func&id="+id_excluir);
                    enviar.done(function(resultado){
                        $('#div_confirma_exclusao_func').css('display','block');

                        window.location.reload();
                    });

                    enviar.fail( function(){ //se der erro, esta na funcao
                        alert('erro ao validar no ajax!');

                    });

                },

                "Cancelar": function(){

                    $( this ).dialog( "close" );
                }

            }

        })


    })


    $('#btn_insere_func').click(function () {
        var login_func = $('#login_func').val();
        var senha_func = $('#senha_func').val();
        var senha_func_confirm = $('#senha_func_confirm').val();
        var nome_func = $('#nome_func').val();
        var cargo_func = $('#select_cargo_func').val();
        var horas_func = $('#horas_func').val();
        var valor_func = $('#valor_func').val();
        var salario_func = $('#salario_func').val();
        var nivel_func = $('#select_nivel_func').val();

        if(login_func == ''){
            alert('Por favor preencha o campo login!');
            $('#login_func').focus();
        }else if(senha_func == ''){
            alert('Por favor preencha o campo senha!');
            $('#senha_func').focus();
        }else if(senha_func_confirm == ''){
            alert('Por favor preencha o campo confirmação de senha!');
            $('#senha_func_confirm').focus();
        }else if(senha_func_confirm != senha_func){
            alert('O campo confirmação de senha não confere com a senha digitada');
            $('#senha_func_confirm').focus();
        }else if(nome_func == ''){
            alert('Por favor preencha o campo nome!');
            $('#nome_func').focus();
        }else if(horas_func == ''){
            alert('Por favor preencha o campo horas!');
            $('#horas_func').focus();
        }else if(valor_func == ''){
            alert('Por favor preencha o campo valor hora!');
            $('#valor_func').focus();
        }else if(salario_func == ''){
            alert('Por favor preencha o campo salário!');
            $('#salario_func').focus();
        }else {

            var enviar = $.post('php/funcoes.php', "&acao=insere_new_func&login_func=" + login_func + "&senha_func=" + senha_func + "&nome_func=" + nome_func + "&cargo_func=" + cargo_func + "&horas_func=" + horas_func + "&valor_func=" + valor_func + "&salario_func=" + salario_func+ "&nivel_func=" + nivel_func);
            enviar.done(function (resultado) {
                //$('#div_media').html(resultado);
                if (resultado == 0) {
                    alert('Funcionário cadastrado com sucesso!');
                    window.location.reload();
                } else if (resultado == 1) {
                    alert('Erro ao cadastrar o funcionário, verifique as informações!');
                } else if (resultado == 2) {
                    alert('Login já existe, favor defina outro!');
                }
                //
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
        }

    })

    //GRID CARGO
    $('#btn_cadastrar_cargo').click(function () {

        $('#div_cadastrar_cargo').css('display','block');
        $('#div_cadastrar_cargo').dialog({
            height: 'auto',
            width: '50%',
            modal: true
        })


    })

    //excluir funcionario

    $("#div_cad_cargo").on("click",".btn_excluir_cargo", function(){
        var id_excluir = $(this).attr('id');

        $('#div_confirma_exclusao_cargo').dialog({

            height: 'auto',
            modal: true,
            buttons: {
                "Sim": function() {

                    var enviar = $.post('php/funcoes.php',"&acao=excluir_cargo&id="+id_excluir);
                    enviar.done(function(resultado){
                        if(resultado == 0){


                            window.location.reload();

                        }else {

                            alert('Ocorreu um erro ao excluir o cargo, verifique se existem funcionários ou registros vinculados a este cargo!!');
                            window.location.reload();
                        }

                    });

                    enviar.fail( function(){ //se der erro, esta na funcao
                        alert('erro ao validar no ajax!');

                    });

                },

                "Cancelar": function(){

                    $( this ).dialog( "close" );
                }

            }

        })


    })


    $('#btn_insere_cargo').click(function () {
        var cargo_func = $('#cargo_func').val();


        if(cargo_func == ''){
            alert('Por favor preencha o campo cargo!');
            $('#cargo_func').focus();

        }else {

            var enviar = $.post('php/funcoes.php', "&acao=insere_new_cargo&cargo_func=" + cargo_func );
            enviar.done(function (resultado) {

                if (resultado == 0) {
                    alert('Cargo cadastrado com sucesso!');
                    window.location.reload();
                } else if (resultado == 1) {
                    alert('Erro ao cadastrar o cargo, verifique as informações!');
                } else if (resultado == 2) {
                    alert('Cargo já existe, favor defina outro!');
                }
                //
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
        }

    })


    //FECHA GRID CARGO



//cadastrar etapa
    $('#btn_cadastrar_etapa').click(function () {

        $('#div_cadastrar_etapa').css('display','block');
        $('#div_cadastrar_etapa').dialog({
            height: 'auto',
            width: '50%',
            modal: true
        })


    })

    $("#div_cad_etapa").on("click",".btn_excluir_etapa", function(){
        var id_excluir = $(this).attr('id');

        $('#div_confirma_exclusao_etapa').dialog({

            height: 'auto',
            modal: true,
            buttons: {
                "Sim": function() {

                    var enviar = $.post('php/funcoes.php',"&acao=excluir_etapa&id="+id_excluir);
                    enviar.done(function(resultado){
                        $('#div_confirma_exclusao_etapa').css('display','block');

                        window.location.reload();
                    });

                    enviar.fail( function(){ //se der erro, esta na funcao
                        alert('erro ao validar no ajax!');

                    });

                },

                "Cancelar": function(){

                    $( this ).dialog( "close" );
                }

            }

        })


    })

    $('#btn_insere_etapa').click(function () {
        var etapa = $('#nome_etapa').val();


        if(etapa == ''){
            alert('Por favor preencha o campo etapa!');
            $('#nome_etapa').focus();

        }else {

            var enviar = $.post('php/funcoes.php', "&acao=insere_new_etapa&etapa=" + etapa);
            enviar.done(function (resultado) {
                //$('#div_media').html(resultado);
                if (resultado == 0) {
                    alert('Etapa cadastrada com sucesso!');
                    window.location.reload();
                } else if (resultado == 1) {
                    alert('Erro ao cadastrar etapa, verifique as informações!');
                } else if (resultado == 2) {
                    alert('Etapa já existe, favor defina outra!');
                }
                //
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
        }

    })

    $('#select_cad_subetapa').change(function () {

        var id = $('#select_cad_subetapa').val();

        var enviar = $.post('php/funcoes.php', "&acao=find_subetapa&id=" + id);
        enviar.done(function (resultado) {
            $('#div_botao_cad_subetapa').css('display','block');
            $('#div_cad_subetapa').html(resultado);


        });
        enviar.fail(function () {
            alert('erro ajax consulte o admin!');
        });
        console.log(enviar);


    })

    //subtarefa
    $("#div_cad_subetapa").on("click",".btn_excluir_subetapa", function(){
        var id_excluir = $(this).attr('id');

        $('#div_confirma_exclusao_subetapa').dialog({

            height: 'auto',
            modal: true,
            buttons: {
                "Sim": function() {

                    var enviar = $.post('php/funcoes.php',"&acao=excluir_subetapa&id="+id_excluir);
                    enviar.done(function(resultado){
                        $('#div_confirma_exclusao_subetapa').css('display','block');

                        window.location.reload();
                    });

                    enviar.fail( function(){ //se der erro, esta na funcao
                        alert('erro ao validar no ajax!');

                    });

                },

                "Cancelar": function(){

                    $( this ).dialog( "close" );
                }

            }

        })


    })

    //subetapa
    $('#btn_cadastrar_subetapa').click(function () {

        $('#div_cadastrar_subetapa').css('display','block');
        $('#div_cadastrar_subetapa').dialog({
            height: 'auto',
            width: '50%',
            modal: true
        })


    })

    $('#btn_insere_subetapa').click(function () {
        var subetapa = $('#nome_subetapa').val();
        var id_etapa = $('#select_cad_subetapa').val();

        if(subetapa == ''){
            alert('Por favor preencha o campo subetapa!');
            $('#nome_subetapa').focus();

        }else {

            var enviar = $.post('php/funcoes.php', "&acao=insere_new_subetapa&subetapa=" + subetapa + "&etapa=" + id_etapa);
            enviar.done(function (resultado) {
                //$('#div_media').html(resultado);
                if (resultado == 0) {
                    alert('Subetapa cadastrada com sucesso!');
                    window.location.reload();
                } else if (resultado == 1) {
                    alert('Erro ao cadastrar subetapa, verifique as informações!');
                } else if (resultado == 2) {
                    alert('Subetapa já existe, favor defina outra!');
                }
                //
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
        }

    })

    //edit projeto
    $("#div_cad_proj").on("click",".btn_editar_proj", function(){
        var id_excluir = $(this).attr('id');

        $.ajax({
            url: "php/funcoes.php", //URL de destino
            data: "&acao=edit_proj&id=" + id_excluir,
            type: "POST",
            dataType: "json", //Tipo de Retorno
            success: function(result){ //Se ocorrer tudo certo
                $('#id_proj_edit').val(result.id);
                $('#cod_proj_edit').val(result.cod_projeto);
                $('#nome_proj_edit').val(result.nome);
                $('#metragem_proj_edit').val(result.metragem);
                $('#comodos_proj_edit').val(result.comodos);
                $('#comodosr_proj_edit').val(result.comodos_realizados);
                $('#valor_proj_edit').val(result.valor_proj);
                $('#tx_adm_edit').val(result.taxa_adm);
                $('#visitas_proj_edit').val(result.visitas);
                $('#select_tipo_proj_edit').html(result.select_tipo);

              $('#div_editar_proj').css('display','block');
                $('#div_editar_proj').dialog({
                    height: 'auto',
                    width: '50%',
                    modal: true
                })

            },
            error: function(result){
                 alert('Erro ajax');
            }
        });

    })

    $('#btn_edit_projeto').click(function () {
        var id_proj_edit = $('#id_proj_edit').val();
        var cod_proj_edit = $('#cod_proj_edit').val();
        var nome_proj_edit = $('#nome_proj_edit').val();
        var metragem_proj_edit = $('#metragem_proj_edit').val();
        var comodos_proj_edit = $('#comodos_proj_edit').val();
        var comodosr_proj_edit = $('#comodosr_proj_edit').val();
        var valor_proj_edit = $('#valor_proj_edit').val();
        var tx_adm_edit = $('#tx_adm_edit').val();
        var visitas_proj_edit = $('#visitas_proj_edit').val();
        var tipo_proj_edit = $('#select_tipo_proj_edit').val();

        if(cod_proj_edit == ''){
            alert('Por favor preencha o campo código do projeto!');
            $('#cod_proj_edit').focus();
        }else if(nome_proj_edit == ''){
            alert('Por favor preencha o campo nome do projeto!');
            $('#nome_proj_edit').focus();
        }else if(metragem_proj_edit == ''){
            alert('Por favor preencha o campo metragem do projeto!');
            $('#metragem_proj_edit').focus();
        }else if(comodos_proj_edit == ''){
            alert('Por favor preencha o campo cômodos do projeto!');
            $('#comodos_proj_edit').focus();
        }else if(valor_proj_edit == ''){
            alert('Por favor preencha o campo valor do projeto!');
            $('#valor_proj_edit').focus();
        }else if(tx_adm_edit == ''){
            alert('Por favor preencha o campo Taxa administrativa!');
            $('#tx_adm_edit').focus();
        }else if(visitas_proj_edit == ''){
            alert('Por favor preencha o campo visitas do projeto!');
            $('#visitas_proj_edit').focus();
        }else {
            var enviar = $.post('php/funcoes.php', "&acao=edit_new_project&id_proj=" + id_proj_edit + "&cod_proj=" + cod_proj_edit + "&nome_proj=" + nome_proj_edit + "&metragem_proj=" + metragem_proj_edit + "&comodos_proj=" + comodos_proj_edit + "&comodosr_proj=" + comodosr_proj_edit + "&valor_proj=" + valor_proj_edit + "&tx_adm=" + tx_adm_edit + "&visitas_proj=" + visitas_proj_edit + "&tipo_proj=" + tipo_proj_edit);
            enviar.done(function (resultado) {

                if (resultado == 0) {
                    alert('Projeto atualizado com sucesso!');
                    window.location.reload();
                } else if (resultado == 1) {
                    alert('Erro ao atualizar o projeto, verifique as informações!');
                }                //
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);


        }
    })

    //edit etapa
    $("#div_cad_etapa").on("click",".btn_editar_etapa", function(){
        var id_editar = $(this).attr('id');

        $.ajax({
            url: "php/funcoes.php", //URL de destino
            data: "&acao=edit_etapa&id=" + id_editar,
            type: "POST",
            dataType: "json", //Tipo de Retorno
            success: function(result){ //Se ocorrer tudo certo
                $('#id_etapa_edit').val(result.id);
                $('#nome_etapa_edit').val(result.nome);

                $('#div_editar_etapa').css('display','block');
                $('#div_editar_etapa').dialog({
                    height: 'auto',
                    width: '50%',
                    modal: true
                })

            },
            error: function(result){
                alert('Erro ajax');
            }
        });

    })

    $('#btn_edit_etapa').click(function () {
        var id = $('#id_etapa_edit').val();
        var etapa = $('#nome_etapa_edit').val();

        if(etapa == ''){
            alert('Por favor preencha o campo etapa!');
            $('#nome_etapa_edit').focus();

        }else {

            var enviar = $.post('php/funcoes.php', "&acao=edit_new_etapa&id=" + id + "&etapa=" + etapa);
            enviar.done(function (resultado) {
                //$('#div_media').html(resultado);
                if (resultado == 0) {
                    alert('Etapa atualizada com sucesso!');
                    window.location.reload();
                } else if (resultado == 1) {
                    alert('Erro ao atualizar etapa, verifique as informações!');
                }
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
        }

    })

    //edit subetapa

    $("#div_cad_subetapa").on("click",".btn_editar_subetapa", function(){
        var id_editar = $(this).attr('id');

        $.ajax({
            url: "php/funcoes.php", //URL de destino
            data: "&acao=edit_subetapa&id=" + id_editar,
            type: "POST",
            dataType: "json", //Tipo de Retorno
            success: function(result){ //Se ocorrer tudo certo
                $('#id_subetapa_edit').val(result.id);
                $('#nome_subetapa_edit').val(result.nome);

                $('#div_editar_subetapa').css('display','block');
                $('#div_editar_subetapa').dialog({
                    height: 'auto',
                    width: '50%',
                    modal: true
                })

            },
            error: function(result){
                alert('Erro ajax');
            }
        });

    })


    $('#btn_edit_subetapa').click(function () {
        var id = $('#id_subetapa_edit').val();
        var etapa = $('#nome_subetapa_edit').val();

        if(etapa == ''){
            alert('Por favor preencha o campo etapa!');
            $('#nome_subetapa_edit').focus();

        }else {

            var enviar = $.post('php/funcoes.php', "&acao=edit_new_subetapa&id=" + id + "&etapa=" + etapa);
            enviar.done(function (resultado) {
                //$('#div_media').html(resultado);
                if (resultado == 0) {
                    alert('Subetapa atualizada com sucesso!');
                    window.location.reload();
                } else if (resultado == 1) {
                    alert('Erro ao atualizar subetapa, verifique as informações!');
                }
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
        }

    })

    //edit func
    $("#div_cad_func").on("click",".btn_editar_func", function(){
        var id_editar = $(this).attr('id');

        $.ajax({
            url: "php/funcoes.php", //URL de destino
            data: "&acao=edit_func&id=" + id_editar,
            type: "POST",
            dataType: "json", //Tipo de Retorno
            success: function(result){ //Se ocorrer tudo certo
                $('#id_func_edit').val(result.id);
                $('#login_func_edit').val(result.login);
                $('#nome_func_edit').val(result.nome);
                $('#select_cargo_func_edit').html(result.select_cargo);
                $('#horas_func_edit').val(result.horas_diarias);
                $('#valor_func_edit').val(result.valor_hora);
                $('#salario_func_edit').val(result.salario);
                $('#select_nivel_func_edit').html(result.select_nivel);                
                $('#select_hextra_func_edit').html(result.hora_extra);
                $('#div_edit_func').css('display','block');
                $('#div_edit_func').dialog({
                    height: 'auto',
                    width: '50%',
                    modal: true
                })

            },
            error: function(result){
                alert('Erro ajax');
            }
        });

    })

    //edit hist func
    $("#div_cad_func").on("click",".btn_edit_hist", function(){
        var id_func = $(this).attr('id');
        $('#select_hist_func_choose').prop('selectedIndex',0);
        $('#div_hist_func_edit').html('');
        $('#id_func_hist').val(id_func);
        $('#div_edit_hist').css('display','block');
                    $('#div_edit_hist').dialog({
                        height: 'auto',
                        width: '50%',
                        modal: true
                    })
      
    })

    $("#div_hist_func_edit").on("click",".btn_editar_registro_hist", function(){
        var id_func = $('#id_func_hist').val();    
        var id_registro = $(this).attr('id');
        var select_choose = $('#select_hist_func_choose').val();
               
        //verify whats choose 1 2 3
        if(select_choose == 1){
            //to last_hour
            $.ajax({
                url: "php/funcoes.php", //URL de destino
                data: "&acao=get_last_hour_data&id_func=" + id_func +"&id_registro="+id_registro,
                type: "POST",
                dataType: "json", //Tipo de Retorno
                success: function(result){ //Se ocorrer tudo certo
                    
                    $('#field_id_registro_editlh').val(result.id);
                    $('#field_id_func_editlh').val(result.id_login);
                    $('#field_h_antiga_edit').val(result.h_antiga);
                    $('#field_ate_edit').val(result.data_ate);
                    
                    $('#div_edit_last_hour').css('display','block');
                        $('#div_edit_last_hour').dialog({
                            height: 'auto',
                            width: '20%',
                            modal: true
                        })               

                },
                error: function(result){
                    alert('Erro ajax',result);
                }
            });

        }else if(select_choose == 2){
        //to last salary        
        $.ajax({
            url: "php/funcoes.php", //URL de destino
            data: "&acao=get_ferias_data&id_func=" + id_func+"&id_registro="+id_registro,
            type: "POST",
            dataType: "json", //Tipo de Retorno
            success: function(result){ //Se ocorrer tudo certo
                
                $('#field_ferias_id_func_edit').val(result.id_func);
                $('#field_ferias_id_registro_edit').val(result.id);
                $('#field_data_inicio_edit').val(result.data_inicio);
                $('#field_data_fim_edit').val(result.data_fim);
                $('#field_hora_diaria_edit').val(result.hora_diaria);
                                
                $('#div_edit_ferias').css('display','block');
                    $('#div_edit_ferias').dialog({
                        height: 'auto',
                        width: '20%',
                        modal: true
                    })               

            },
            error: function(result){
                alert('Erro ajax',result);
            }
        });    
        }else if(select_choose == 3){
            data = id_registro; //id_func+data_de+data_ate+valorhora separado por virgula
            
            $('#field_id_func_edit').val(data.split(',')[0]);
            $('#field_data_de_edit').val(data.split(',')[1]);
            $('#field_data_ate_edit').val(data.split(',')[2]);
            $('#field_valor_hora_edit').val(data.split(',')[3]);            
                            
            $('#div_edit_valor_hora').css('display','block');
                $('#div_edit_valor_hora').dialog({
                    height: 'auto',
                    width: '20%',
                    modal: true
                })   
           

        }
    })

    //edit last_hour data
    $('#btn_edit_last_hour').click(function () {
        var field_id_func_editlh = $('#field_id_func_editlh').val();
        var field_id_registro_editlh = $('#field_id_registro_editlh').val();
        var field_h_antiga_edit = $('#field_h_antiga_edit').val();    
        var field_ate_edit = $('#field_ate_edit').val(); 

        if(field_h_antiga_edit == '' || field_ate_edit == ''){
            alert('Por favor preencha todos os campos!');
            
        }else {
            
            var enviar = $.post('php/funcoes.php',"&acao=edit_last_hour_data&field_id_func_edit=" + field_id_func_editlh + "&field_id_registro_edit=" + field_id_registro_editlh + "&field_h_antiga_edit=" + field_h_antiga_edit + "&field_ate_edit=" + field_ate_edit);
            enviar.done(function(resultado){
                alert('Edição realizada com sucesso!!');
                window.location.reload();
                
            });
            enviar.fail( function(){
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
            return false;

        }        
        

      
    })

    //exclude last_hour data 
    $("#div_hist_func_edit").on("click",".btn_excluir_hist", function(){
        var id_func = $('#id_func_hist').val();    
        var id_registro = $(this).attr('id');
        var select_choose = $('#select_hist_func_choose').val();
        console.log(id_func,id_registro,select_choose);
         //verify whats choose 1 2 3
         if(select_choose == 1){
            //to last_hour
            $('#div_confirma_exclusao_lasthour').dialog({

                height: 'auto',
                modal: true,
                buttons: {
                    "Sim": function() {
                        var enviar = $.post('php/funcoes.php',"&acao=exclude_last_hour_data&id_func=" + id_func+"&id_registro="+id_registro);
                        enviar.done(function(resultado){
                            if(resultado == 0){
                                alert('Histórico de horas diárias excluído com sucesso!!');
                                                               
                                window.location.reload();
                                
                            }else {
                                alert('Erro ao excluir histórico de horas diárias!!');    
                            }
                            
                        });

                    enviar.fail( function(){ //se der erro, esta na funcao
                        alert('erro ao validar no ajax!');

                    });
                        
                    },
    
                    "Cancelar": function(){
    
                        $( this ).dialog( "close" );
                    }
    
                }
    
            })
            
            
            
           
        }else if(select_choose == 2){
        //to ferias  
        $('#div_confirma_exclusao_lasthour').dialog({

            height: 'auto',
            modal: true,
            buttons: {
                "Sim": function() {
                    var enviar = $.post('php/funcoes.php',"&acao=exclude_ferias_data&id_func=" + id_func+"&id_registro="+id_registro);
                    enviar.done(function(resultado){
                        if(resultado == 0){
                            alert('Registro excluído com sucesso!!');
                                                           
                            window.location.reload();
                            
                        }else {
                            alert('Erro ao excluir histórico de horas diárias!!');    
                        }
                        
                    });

                enviar.fail( function(){ //se der erro, esta na funcao
                    alert('erro ao validar no ajax!');

                });
                    
                },

                "Cancelar": function(){

                    $( this ).dialog( "close" );
                }

            }

        }) 
        }

    })    

    //edit ferias data
    $('#btn_edit_ferias').click(function () {
        var field_ferias_id_func_edit = $('#field_ferias_id_func_edit').val();    
        var field_ferias_id_registro_edit = $('#field_ferias_id_registro_edit').val();
        var field_data_inicio_edit = $('#field_data_inicio_edit').val();    
        var field_data_fim_edit = $('#field_data_fim_edit').val();
        var field_hora_diaria_edit = $('#field_hora_diaria_edit').val(); 
        //need blank validation        
        
        if(field_data_inicio_edit == '' || field_data_fim_edit == '' || field_hora_diaria_edit == ''){
            alert('Por favor preencha todos os campos!');
        }else {
            var enviar = $.post('php/funcoes.php',"&acao=edit_ferias_data&field_ferias_id_func_edit=" + field_ferias_id_func_edit + "&field_ferias_id_registro_edit=" + field_ferias_id_registro_edit + "&field_data_inicio_edit=" + field_data_inicio_edit + "&field_data_fim_edit=" + field_data_fim_edit+ "&field_hora_diaria_edit="+ field_hora_diaria_edit);
            enviar.done(function(resultado){
                if(resultado == 0){
                    alert('Edição realizada com sucesso!!');
                    window.location.reload();
                }else{
                    alert('Algum problema ocorreu ao editar este registro!!');
                }
                
                
            });
            enviar.fail( function(){
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
            return false;

        }    

        

      
    })

     //edit valor hora
     $('#btn_edit_valor_hora').click(function () {
        var field_id_func_edit = $('#field_id_func_edit').val();    
        var field_data_de_edit = $('#field_data_de_edit').val();
        var field_data_ate_edit = $('#field_data_ate_edit').val();    
        var field_valor_hora_edit = $('#field_valor_hora_edit').val();                
        
        //validação data de maior que data ate.
        var partesDatade = field_data_de_edit.split("/");
        var datade = new Date(partesDatade[2], partesDatade[1] - 1, partesDatade[0]);

        var partesDataate = field_data_ate_edit.split("/");
        var dataate = new Date(partesDataate[2], partesDataate[1] - 1, partesDataate[0]);

        
        if(field_data_inicio_edit == '' || field_data_fim_edit == '' || field_hora_diaria_edit == ''){
            alert('Por favor preencha todos os campos!');
        }else if(datade > dataate){
            alert('A data de não pode ser maior que a data até!');

        }else {
            
            var enviar = $.post('php/funcoes.php',"&acao=edit_valor_hora_data&field_id_func_edit=" + field_id_func_edit + "&field_data_de_edit=" + field_data_de_edit + "&field_data_ate_edit=" + field_data_ate_edit + "&field_valor_hora_edit=" + field_valor_hora_edit);
            enviar.done(function(resultado){
                if(resultado == 0){
                    alert('Edição realizada com sucesso!!');
                    window.location.reload();
                }else{
                    alert('Algum problema ocorreu ao editar este registro!!');
                }
                
                
            });
            enviar.fail( function(){
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
            return false;

        }    

        

      
    })


    $('#select_hist_func_choose').change(function(){
        var id_func = $('#id_func_hist').val();
        var hist_func_choose = $('#select_hist_func_choose').val();

        var enviar = $.post('php/funcoes.php',"&acao=edit_hist_func&id="+id_func+"&hist_func_choose="+hist_func_choose);
        enviar.done(function(resultado){
            $('#div_hist_func_edit').html(resultado);
            console.log(resultado);            
            
        });
        enviar.fail( function(){
            alert('erro ajax consulte o admin!');
        });
        console.log(enviar);
        return false;
        
    });

    $("#div_cad_cargo").on("click",".btn_editar_cargo", function(){
        var id_editar = $(this).attr('id');

        $.ajax({
            url: "php/funcoes.php", //URL de destino
            data: "&acao=edit_cargo&id=" + id_editar,
            type: "POST",
            dataType: "json", //Tipo de Retorno
            success: function(result){ //Se ocorrer tudo certo
                $('#id_cargo_edit').val(result.id);
                $('#cargo_func_edit').val(result.cargo);


               $('#div_edit_cargo').css('display','block');
                $('#div_edit_cargo').dialog({
                    height: 'auto',
                    width: '50%',
                    modal: true
                })

            },
            error: function(result){
                alert('Erro ajax');
            }
        });

    })

    $('#btn_edit_func').click(function () {
        var id_func = $('#id_func_edit').val();
        var login_func = $('#login_func_edit').val();
        var senha_func = $('#senha_func_edit').val();
        var senha_func_confirm = $('#senha_func_confirm_edit').val();
        var nome_func = $('#nome_func_edit').val();
        var cargo_func = $('#select_cargo_func_edit').val();
        var horas_func = $('#horas_func_edit').val();
        var valor_func = $('#valor_func_edit').val();
        var salario_func = $('#salario_func_edit').val();
        var nivel_func = $('#select_nivel_func_edit').val();
        var hora_extra = $('#select_hextra_func_edit').val();

        if(login_func == ''){
            alert('Por favor preencha o campo login!');
            $('#login_func_edit').focus();
        }else if(senha_func_confirm != senha_func) {
                  alert('O campo senha e confirmação de senha deve ser iguais!');
                  $('#senha_func_confirm_edit').focus();

        }else if(nome_func == ''){
            alert('Por favor preencha o campo nome!');
            $('#nome_func_edit').focus();

        }else if(horas_func == ''){
            alert('Por favor preencha o campo horas!');
            $('#horas_func_edit').focus();
        }else if(valor_func == ''){
            alert('Por favor preencha o campo valor hora!');
            $('#valor_func_edit').focus();
        }else if(salario_func == ''){
            alert('Por favor preencha o campo salário!');
            $('#salario_func_edit').focus();
        }else {

            var enviar = $.post('php/funcoes.php', "&acao=edit_new_func&id_func=" + id_func + "&login_func=" + login_func +  "&senha_func=" + senha_func + "&nome_func=" + nome_func + "&cargo_func=" + cargo_func + "&horas_func=" + horas_func + "&valor_func=" + valor_func + "&salario_func=" + salario_func+ "&nivel_func=" + nivel_func+ "&hora_extra=" + hora_extra);
            enviar.done(function (resultado) {
                //$('#div_media').html(resultado);
                if (resultado == 0) {
                    alert('Funcionário atualizado com sucesso!');
                    window.location.reload();
                } else if (resultado == 1) {
                    alert('Erro ao atualizar o funcionário, verifique as informações!');
                }else if (resultado == 2) {
                    alert('Já existe um cadastro de horas diárias para este funcionário no dia de hoje, você só poderá altera-la a partir do próximo dia.');
                }
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
        }

    })

    //edit cargo
    $('#btn_edit_cargo').click(function () {
        var id_cargo = $('#id_cargo_edit').val();
        var cargo_func = $('#cargo_func_edit').val();


        if(cargo_func == ''){
            alert('Por favor preencha o campo cargo!');
            $('#cargo_func_edit').focus();

        }else {

            var enviar = $.post('php/funcoes.php', "&acao=edit_new_cargo&id_cargo=" + id_cargo + "&cargo_func=" + cargo_func);
            enviar.done(function (resultado) {

                if (resultado == 0) {
                    alert('Cargo atualizado com sucesso!');
                    window.location.reload();
                } else if (resultado == 1) {
                    alert('Erro ao atualizar o cargo, verifique as informações!');
                }
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
        }

    })

    //fecha edit cargo

    //RELATORIOS DE USUARIOS
    $('#btn_gerar_rel_user').click(function () {

        var start_data = $('#start_date').val();
        var end_data = $('#end_date').val();
        var select_rel_user = $('#select_rel_user').val();

        if ($('#select_rel_tipo').val() == 'rel_projetos') {

            var enviar = $.post('php/funcoes.php', "&acao=relatorio_proj&start_data=" + start_data + "&end_data=" + end_data + "&select_rel_user=" + select_rel_user);
            enviar.done(function (resultado) {
                $('#div_rel').html(resultado);

                $('#tab_rel').dataTable({
                    "pagingType": "full_numbers",
                    "oLanguage": {
                        "sProcessing": "Processando...",
                        "sLengthMenu": "Mostrar _MENU_ registros",
                        "sZeroRecords": "Não foram encontrados resultados",
                        "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                        "sInfoEmpty": "Mostrando de 0 até 0 de 0 registros",
                        "sInfoFiltered": "",
                        "sInfoPostFix": "",
                        "sSearch": "Buscar:",
                        "sUrl": "",
                        "oPaginate": {
                            "sFirst": "Primeiro",
                            "sPrevious": "Anterior",
                            "sNext": "Próximo",
                            "sLast": "Último"
                        },
                        "oAria": {
                            "sSortAscending": ": Ordenar colunas de forma ascendente",
                            "sSortDescending": ": Ordenar colunas de forma descendente"
                        }
                    }

                });

            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
            return false;

        }else if ($('#select_rel_tipo').val() == 'rel_adm') {

            var enviar = $.post('php/funcoes.php', "&acao=relatorio_adm&start_data=" + start_data + "&end_data=" + end_data + "&select_rel_user=" + select_rel_user);

            enviar.done(function (resultado) {
                $('#div_rel').html(resultado);

                $('#tab_rel').dataTable({
                    "pagingType": "full_numbers",
                    "oLanguage": {
                        "sProcessing": "Processando...",
                        "sLengthMenu": "Mostrar _MENU_ registros",
                        "sZeroRecords": "Não foram encontrados resultados",
                        "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                        "sInfoEmpty": "Mostrando de 0 até 0 de 0 registros",
                        "sInfoFiltered": "",
                        "sInfoPostFix": "",
                        "sSearch": "Buscar:",
                        "sUrl": "",
                        "oPaginate": {
                            "sFirst": "Primeiro",
                            "sPrevious": "Anterior",
                            "sNext": "Próximo",
                            "sLast": "Último"
                        },
                        "oAria": {
                            "sSortAscending": ": Ordenar colunas de forma ascendente",
                            "sSortDescending": ": Ordenar colunas de forma descendente"
                        }
                    }

                });

            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
            return false;
        }else if ($('#select_rel_tipo').val() == 'rel_ferias') {

            var enviar = $.post('php/funcoes.php', "&acao=relatorio_ferias&start_data=" + start_data + "&end_data=" + end_data + "&select_rel_user=" + select_rel_user);

            enviar.done(function (resultado) {
                $('#div_rel').html(resultado);

                $('#tab_rel').dataTable({
                    "pagingType": "full_numbers",
                    "oLanguage": {
                        "sProcessing": "Processando...",
                        "sLengthMenu": "Mostrar _MENU_ registros",
                        "sZeroRecords": "Não foram encontrados resultados",
                        "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                        "sInfoEmpty": "Mostrando de 0 até 0 de 0 registros",
                        "sInfoFiltered": "",
                        "sInfoPostFix": "",
                        "sSearch": "Buscar:",
                        "sUrl": "",
                        "oPaginate": {
                            "sFirst": "Primeiro",
                            "sPrevious": "Anterior",
                            "sNext": "Próximo",
                            "sLast": "Último"
                        },
                        "oAria": {
                            "sSortAscending": ": Ordenar colunas de forma ascendente",
                            "sSortDescending": ": Ordenar colunas de forma descendente"
                        }
                    }

                });

            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
            return false;


        }
    })

    $("#div_rel").on("click",".btn_excluir_registro", function(){
        var id_excluir = $(this).attr('id');

        $('#div_confirma_exclusao_registro').dialog({

            height: 'auto',
            modal: true,
            buttons: {
                "Sim": function() {

                    var enviar = $.post('php/funcoes.php',"&acao=excluir_registro&id="+id_excluir);
                    enviar.done(function(resultado){
                        $('#div_confirma_exclusao_registro').css('display','block');

                        window.location.reload();
                    });

                    enviar.fail( function(){ //se der erro, esta na funcao
                        alert('erro ao validar no ajax!');

                    });

                },

                "Cancelar": function(){

                    $( this ).dialog( "close" );
                }

            }

        })


    })

    $("#div_rel").on("click",".btn_excluir_registro_adm", function(){
        var id_excluir = $(this).attr('id');

        $('#div_confirma_exclusao_registro').dialog({

            height: 'auto',
            modal: true,
            buttons: {
                "Sim": function() {

                    var enviar = $.post('php/funcoes.php',"&acao=excluir_registro_adm&id="+id_excluir);
                    enviar.done(function(resultado){
                        $('#div_confirma_exclusao_registro').css('display','block');

                        window.location.reload();
                    });

                    enviar.fail( function(){ //se der erro, esta na funcao
                        alert('erro ao validar no ajax!');

                    });

                },

                "Cancelar": function(){

                    $( this ).dialog( "close" );
                }

            }

        })


    })

    //edit registro proj
    $("#div_rel").on("click",".btn_editar_registro", function(){
        var id_edit = $(this).attr('id');

        $.ajax({
            url: "php/funcoes.php", //URL de destino
            data: "&acao=edit_reg&id=" + id_edit,
            type: "POST",
            dataType: "json", //Tipo de Retorno
            success: function(result){ //Se ocorrer tudo certo
                $('#id_reg_edit').val(result.id);
                $('#id_func_edit').val(result.id_func);
                $('#input_datepicker_edit').val(result.data);
                $('#h_inicial').val(result.h_inicial);
                $('#h_final').val(result.h_final);
                $('#select_projeto_edit').html(result.select_proj);
                $('#select_etapa_edit').html(result.select_etapa);
                $('#select_subetapa_edit').html(result.select_subetapa);

                $('#div_edit_reg').css('display','block');
                $('#div_edit_reg').dialog({
                    height: 'auto',
                    width: '50%',
                    modal: true
                })



            },
            error: function(result){
                alert('Erro ajax');
            }
        });

    })

    $('#select_etapa_edit').change(function(){
        var etapa = $('#select_etapa_edit').val();

        var enviar = $.post('php/funcoes.php',"&acao=busca_subetapa_edit&etapa="+etapa);
        enviar.done(function(resultado){
            $('#select_subetapa_edit').html(resultado);
        });
        enviar.fail( function(){
            alert('erro ajax consulte o admin!');
        });
        console.log(enviar);
        return false;

    });

    $('#btn_editar_reg').click(function () {
        var id_reg = $('#id_reg_edit').val();
        var id_func = $('#id_func_edit').val();
        var data = $('#input_datepicker_edit').val();
        var select_projeto_edit = $('#select_projeto_edit').val();
        var select_etapa_edit = $('#select_etapa_edit').val();
        var select_subetapa_edit = $('#select_subetapa_edit').val();
        var h_inicial = $('#h_inicial').val();
        var h_final = $('#h_final').val();

        if(data == ''){
            alert('Por favor preencha o campo data!');
            $('#input_datepicker_edit').focus();
        }else if(select_subetapa_edit == null) {
            alert('Por favor selecione uma subetapa!');
            $('#select_subetapa_edit').focus();

        }else if(h_inicial == ''){
            alert('Por favor preencha o campo inicio da atividade!');
            $('#h_inicial').focus();
        }else if(h_final == ''){
            alert('Por favor preencha o campo fim da atividade!');
            $('#h_final').focus();
        }else {

            var enviar = $.post('php/funcoes.php', "&acao=edit_new_reg&id_reg=" + id_reg + "&data=" + data + "&id_func=" + id_func  +  "&id_projeto=" + select_projeto_edit + "&id_etapa=" + select_etapa_edit + "&id_subetapa=" + select_subetapa_edit + "&h_inicial=" + h_inicial + "&h_final=" + h_final );
            enviar.done(function (resultado) {

                if (resultado == 0) {
                    alert('Registro atualizado com sucesso!');
                    window.location.reload();
                }else if(resultado == 2){
                    alert("Existe um conflito de horas com registros anteriores deste dia, por favor defina outro inicio e fim para a atividade.\n\nObs: Você pode consulta nos relatórios as horas já inseridas e que estão em conflito.");
                }
                else if (resultado == 1) {
                    alert('Erro ao atualizar registro, verifique as informações!');
                }

            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
        }

    })

    //edit registro adm
    $("#div_rel").on("click",".btn_editar_registro_adm", function(){
        var id_edit = $(this).attr('id');

        $.ajax({
            url: "php/funcoes.php", //URL de destino
            data: "&acao=edit_reg_adm&id=" + id_edit,
            type: "POST",
            dataType: "json", //Tipo de Retorno
            success: function(result){ //Se ocorrer tudo certo
                $('#id_adm_edit').val(result.id);
                $('#id_func_adm_edit').val(result.id_func);
                $('#input_datepicker_adm_edit').val(result.data);
                $('#select_adm_edit').html(result.select_adm);
                $('#inicio_atividade_adm_edit').val(result.h_inicial);
                $('#fim_atividade_adm_edit').val(result.h_final);

                $('#div_edit_reg_adm').css('display','block');
                $('#div_edit_reg_adm').dialog({
                    height: 'auto',
                    width: '50%',
                    modal: true
                })



            },
            error: function(result){
                alert('Erro ajax');
            }
        });

    })


    $('#btn_editar_adm').click(function () {
        var id = $('#id_adm_edit').val();
        var id_func = $('#id_func_adm_edit').val();
        var data = $('#input_datepicker_adm_edit').val();
        var adm = $('#select_adm_edit').val();
        var inicio_atividade_adm = $('#inicio_atividade_adm_edit').val();
        var fim_atividade_adm = $('#fim_atividade_adm_edit').val();

        var partesData = data.split("/");
        var validate_data = new Date(partesData[2], partesData[1] - 1, partesData[0]);

        if(data == ''){
            alert('Por favor selecione uma data!!!');
            $('#input_datepicker_adm_edit').focus();
        }else if(validate_data > new Date()){
            alert('A data selecionada e maior que a data atual!!!');

            $('#input_datepicker_adm_edit').focus();
        }else if(adm == null){
            alert('Por favor selecione as horas administrativas!!!');

            $('#select_adm_edit').focus();
        }else if(inicio_atividade_adm == '' || fim_atividade_adm == ''){
            alert('Por favor insira o inicio e o fim das atividades!!!');

            $('#inicio_atividade_adm_edit').focus();
        }

        else {

            var enviar = $.post('php/funcoes.php', "&acao=edit_new_adm&id=" + id +"&data=" + data + "&id_func=" + id_func + "&adm=" + adm + "&inicio_atividade_adm=" + inicio_atividade_adm + "&fim_atividade_adm=" + fim_atividade_adm);
            enviar.done(function (resultado) {
                if (resultado == 0) {
                    alert('Registro atualizado com sucesso!!!');
                    window.location.reload();

                }else if(resultado == 2){
                    alert("Existe um conflito de horas com registros anteriores deste dia, por favor defina outro inicio e fim para a atividade.\n\nObs: Você pode consulta nos relatórios as horas já inseridas e que estão em conflito.");
                }
                else {
                    alert('Erro ao atualizar registro, verifique as informações!!!');

                }

            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);

        }
    })

    //FECHA RELATORIO USUARIOS

    //cadastrar dias uteis
    $('#btn_cadastrar_dias_uteis').click(function () {

        if($('#datepicker_dias_uteis').val() == ''){
            alert('Você precisa selecionar alguns dias!!');
        }else {

            $('#div_confirma_dias_uteis').html('Confirma os dias? </br></br>' + $('#datepicker_dias_uteis').val());

            $('#div_confirma_dias_uteis').dialog({

                height: 'auto',
                modal: true,
                buttons: {
                    "Sim": function () {
                        var dias = $('#datepicker_dias_uteis').val();

                        var enviar = $.post('php/funcoes.php', "&acao=insere_dias_uteis&dias=" + dias);
                        enviar.done(function (resultado) {
                          if (resultado == 0) {
                                alert('Dias cadastrados com sucesso!!!');
                                window.location.reload();

                            } else {
                                alert('Erro ao cadastrar dias, verifique as informações!!!');
                                return false;
                            }
                            console.log(resultado);


                        });
                        enviar.fail(function () {
                            alert('erro ajax consulte o admin!');
                        });
                        console.log(enviar);

                    },

                    "Cancelar": function () {

                        $(this).dialog("close");
                    }

                }

            })

        }

    })

    //logica de ferias atestados

    //insere periodos de ferias
    $('#btn_inserir_ferias').click(function () {

        var select_ferias_atestados = $('#select_ferias_atestados').val();
        var data_inicio = $('#input_datepicker_ferias_inicio').val();
        var data_fim = $('#input_datepicker_ferias_fim').val();

        if(data_inicio == ''){
            alert('Por favor selecione uma data de inicio!!!');
            $('#input_datepicker_ferias_inicio').focus();
        }else if(data_fim == ''){
            alert('Por favor selecione uma data de fim!!!');
            $('#input_datepicker_ferias_fim').focus();
        }

        else {

            
            var enviar = $.post('php/funcoes.php', "&acao=insert_new_p_ferias&data_inicio=" + data_inicio +"&data_fim=" + data_fim + "&select_ferias_atestados=" + select_ferias_atestados);
            enviar.done(function (resultado) {
                if(resultado == 0){
                    alert("Registro efetuado com sucesso!!");
                    $('#input_datepicker_ferias_fim').val('');
                }else if(resultado == 1){
                    alert("Erro ao inserir registro, por favor verifique os dados!!");
                    $('#input_datepicker_ferias_fim').val('');
                }else if(resultado == 2){
                    alert("Já existe um registro neste intervalo de datas!!");
                    $('#input_datepicker_ferias_fim').val('');
                }

            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
              
        }
    })

    //insere periodos de atestado
    $('#btn_inserir_p_atestado').click(function () {

        var data_inicio = $('#input_datepicker_atestado_inicio').val();
        var data_fim = $('#input_datepicker_atestado_fim').val();

        if(data_inicio == ''){
            alert('Por favor selecione uma data de inicio!!!');
            $('#input_datepicker_atestado_inicio').focus();
        }else if(data_fim == ''){
            alert('Por favor selecione uma data de fim!!!');
            $('#input_datepicker_atestado_fim').focus();
        }

        else {

            var enviar = $.post('php/funcoes.php', "&acao=insert_new_p_atestado&data_inicio=" + data_inicio +"&data_fim=" + data_fim);
            enviar.done(function (resultado) {
                if(resultado == 0){
                    alert("Registro efetuado com sucesso!!");
                    $('#input_datepicker_atestado_fim').val('');
                }else if(resultado == 1){
                    alert("Erro ao inserir registro, por favor verifique os dados!!");
                    $('#input_datepicker_atestado_fim').val('');
                }else if(resultado == 2){
                    alert("Já existe um registro neste intervalo de datas!!");
                    $('#input_datepicker_atestado_fim').val('');
                }

            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);

        }
    })

    //fecha logica de ferias atestados

    //edit ferias
    $("#div_edit_ferias").on("click",".btn_excluir_ferias", function(){
        var id_excluir = $(this).attr('id');

        $('#div_confirma_exclusao_ferias').dialog({

            height: 'auto',
            modal: true,
            buttons: {
                "Sim": function() {

                    var enviar = $.post('php/funcoes.php',"&acao=excluir_ferias&id="+id_excluir);
                    enviar.done(function(resultado){
                        $('#div_confirma_exclusao_ferias').css('display','block');

                        window.location.reload();
                    });

                    enviar.fail( function(){ //se der erro, esta na funcao
                        alert('erro ao validar no ajax!');

                    });

                },

                "Cancelar": function(){

                    $( this ).dialog( "close" );
                }

            }

        })


    })

    $("#div_edit_ferias").on("click",".btn_editar_ferias", function(){
        var id_edit = $(this).attr('id');

        $.ajax({
            url: "./php/funcoes.php", //URL de destino
            data: "&acao=edit_ferias&id=" + id_edit,
            type: "POST",
            dataType: "json", //Tipo de Retorno
            success: function(result){ //Se ocorrer tudo certo
                $('#id_ferias_edit').val(result.id);
                $('#input_edit_ferias_inicio').val(result.data_inicio);
                $('#input_edit_ferias_fim').val(result.data_fim);
                $('#select_editferias_atestados').val(result.tipo);
                

                $('#div_edit_reg_ferias').css('display','block');
                $('#div_edit_reg_ferias').dialog({
                    height: 'auto',
                    width: '50%',
                    modal: true
                })



            },
            error: function(result){
                alert('Erro ajax');
            }
        });

    })

    $('#btn_edit_reg_ferias').click(function () {
        var id = $('#id_ferias_edit').val();
        var data_inicio = $('#input_edit_ferias_inicio').val();
        var data_fim = $('#input_edit_ferias_fim').val();
        var tipo = $('#select_editferias_atestados').val();

        if(data_inicio == ''){
            alert('Por favor selecione uma data de inicio!!!');
            $('#input_edit_ferias_inicio').focus();
        }else if(data_fim == ''){
            alert('Por favor selecione uma data de fim!!!');
            $('#input_edit_ferias_fim').focus();
        }
        else {

            var enviar = $.post('php/funcoes.php', "&acao=edit_new_ferias&id=" + id +"&data_inicio=" + data_inicio + "&data_fim=" + data_fim + "&tipo=" + tipo);
            enviar.done(function (resultado) {
                if (resultado == 0) {
                    alert('Registro atualizado com sucesso!!!');
                    window.location.reload();

                }else if(resultado == 2){
                    alert('Já existe registros entre as datas escolhidas, por favor escolha outro intervalo de datas!!!');

                }
                else {
                    alert('Erro ao atualizar registro, verifique as informações!!!');

                }

            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);

        }
    })

//finalizar proj

    $("#div_cad_proj").on("click",".btn_finalizar_proj", function(){
        var id_finaliza = $(this).attr('id');

        $('#div_confirma_finalizacao_proj').dialog({

            height: 'auto',
            modal: true,
            buttons: {
                "Sim": function() {

                    var enviar = $.post('php/funcoes.php',"&acao=finalizar_proj&id="+id_finaliza);
                    enviar.done(function(resultado){
                        $('#div_confirma_finalizacao_proj').css('display','block');

                        window.location.reload();
                    });

                    enviar.fail( function(){ //se der erro, esta na funcao
                        alert('erro ao validar no ajax!');

                    });

                },

                "Cancelar": function(){

                    $( this ).dialog( "close" );
                }

            }

        })


    })

    $("#div_cad_proj").on("click",".btn_reativar_proj", function(){
        var id_reativar = $(this).attr('id');

        $('#div_confirma_reativacao_proj').dialog({

            height: 'auto',
            modal: true,
            buttons: {
                "Sim": function() {

                    var enviar = $.post('php/funcoes.php',"&acao=reativar_proj&id="+id_reativar);
                    enviar.done(function(resultado){
                        $('#div_confirma_reativacao_proj').css('display','block');

                        window.location.reload();
                    });

                    enviar.fail( function(){ //se der erro, esta na funcao
                        alert('erro ao validar no ajax!');

                    });

                },

                "Cancelar": function(){

                    $( this ).dialog( "close" );
                }

            }

        })


    })

    //cadastrar adm
    $('#btn_cadastrar_adm').click(function () {

        $('#div_cadastrar_adm').css('display','block');
        $('#div_cadastrar_adm').dialog({
            height: 'auto',
            width: '50%',
            modal: true
        })


    })

    $("#div_cad_adm").on("click",".btn_excluir_adm", function(){
        var id_excluir = $(this).attr('id');

        $('#div_confirma_exclusao_adm').dialog({

            height: 'auto',
            modal: true,
            buttons: {
                "Sim": function() {

                    var enviar = $.post('php/funcoes.php',"&acao=excluir_adm&id="+id_excluir);
                    enviar.done(function(resultado){
                        $('#div_confirma_exclusao_adm').css('display','block');

                        window.location.reload();
                    });

                    enviar.fail( function(){ //se der erro, esta na funcao
                        alert('erro ao validar no ajax!');

                    });

                },

                "Cancelar": function(){

                    $( this ).dialog( "close" );
                }

            }

        })


    })

    $('#btn_insere_adm').click(function () {
        var nome = $('#nome_adm').val();


        if(nome == ''){
            alert('Por favor preencha o campo nome!');
            $('#nome_nome').focus();

        }else {

            var enviar = $.post('php/funcoes.php', "&acao=insere_new_adm&nome=" + nome);
            enviar.done(function (resultado) {
                //$('#div_media').html(resultado);
                if (resultado == 0) {
                    alert('Cadastro efetuado com sucesso!');
                    window.location.reload();
                } else if (resultado == 1) {
                    alert('Erro ao cadastrar , verifique as informações!');
                } else if (resultado == 2) {
                    alert('Etapa já existe, favor defina outro!');
                }
                //
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
        }

    })

//edit adm

    $("#div_cad_adm").on("click",".btn_editar_adm", function(){
        var id_editar = $(this).attr('id');

        $.ajax({
            url: "php/funcoes.php", //URL de destino
            data: "&acao=edit_adm&id=" + id_editar,
            type: "POST",
            dataType: "json", //Tipo de Retorno
            success: function(result){ //Se ocorrer tudo certo
                $('#id_adm_edit').val(result.id);
                $('#nome_adm_edit').val(result.nome);

                $('#div_editar_adm').css('display','block');
                $('#div_editar_adm').dialog({
                    height: 'auto',
                    width: '50%',
                    modal: true
                })

            },
            error: function(result){
                alert('Erro ajax');
            }
        });

    })

    $('#btn_edit_adm').click(function () {
        var id = $('#id_adm_edit').val();
        var nome = $('#nome_adm_edit').val();

        if(nome == ''){
            alert('Por favor preencha o campo nome!');
            $('#nome_adm_edit').focus();

        }else {

            var enviar = $.post('php/funcoes.php', "&acao=edit_novo_adm&id=" + id + "&nome=" + nome);
            enviar.done(function (resultado) {
                //$('#div_media').html(resultado);
                if (resultado == 0) {
                    alert('Atualizada realizada com sucesso!');
                    window.location.reload();
                } else if (resultado == 1) {
                    alert('Erro ao atualizar, verifique as informações!');
                }
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
        }

    })

 //cadastrar   tipo de projeto

    //cadastrar adm
    $('#btn_cadastrar_tipo_proj').click(function () {

        $('#div_cadastrar_tipo_proj').css('display','block');
        $('#div_cadastrar_tipo_proj').dialog({
            height: 'auto',
            width: '50%',
            modal: true
        })


    })

    $("#div_cad_tipo_proj").on("click",".btn_excluir_tipo_proj", function(){
        var id_excluir = $(this).attr('id');

        $('#div_confirma_exclusao_tipo_proj').dialog({

            height: 'auto',
            modal: true,
            buttons: {
                "Sim": function() {

                    var enviar = $.post('php/funcoes.php',"&acao=excluir_tipo_proj&id="+id_excluir);
                    enviar.done(function(resultado){
                        $('#div_confirma_exclusao_tipo_proj').css('display','block');

                        window.location.reload();
                    });

                    enviar.fail( function(){ //se der erro, esta na funcao
                        alert('erro ao validar no ajax!');

                    });

                },

                "Cancelar": function(){

                    $( this ).dialog( "close" );
                }

            }

        })


    })

    $('#btn_insere_tipo_proj').click(function () {
        var tipo_proj = $('#tipo_proj').val();


        if(tipo_proj == ''){
            alert('Por favor preencha o campo tipo!');
            $('#tipo_proj').focus();

        }else {

            var enviar = $.post('php/funcoes.php', "&acao=insere_new_tipo_proj&tipo=" + tipo_proj);
            enviar.done(function (resultado) {
                //$('#div_media').html(resultado);
                if (resultado == 0) {
                    alert('Cadastro efetuado com sucesso!');
                    window.location.reload();
                } else if (resultado == 1) {
                    alert('Erro ao cadastrar , verifique as informações!');
                } else if (resultado == 2) {
                    alert('Etapa já existe, favor defina outro!');
                }
                //
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
        }

    })

//edit adm

    $("#div_cad_tipo_proj").on("click",".btn_editar_tipo_proj", function(){
        var id_editar = $(this).attr('id');

        $.ajax({
            url: "php/funcoes.php", //URL de destino
            data: "&acao=edit_tipo_proj&id=" + id_editar,
            type: "POST",
            dataType: "json", //Tipo de Retorno
            success: function(result){ //Se ocorrer tudo certo
                $('#id_tipo_proj_edit').val(result.id);
                $('#text_tipo_proj_edit').val(result.tipo);

                $('#div_editar_tipo_proj').css('display','block');
                $('#div_editar_tipo_proj').dialog({
                    height: 'auto',
                    width: '50%',
                    modal: true
                })

            },
            error: function(result){
                alert('Erro ajax');
            }
        });

    })

    $('#btn_edit_tipo_proj').click(function () {
        var id = $('#id_tipo_proj_edit').val();
        var tipo = $('#text_tipo_proj_edit').val();

        if(tipo == ''){
            alert('Por favor preencha o campo nome!');
            $('#tipo_proj_edit').focus();

        }else {

            var enviar = $.post('php/funcoes.php', "&acao=edit_novo_tipo_proj&id=" + id + "&tipo_proj=" + tipo);
            enviar.done(function (resultado) {
                //$('#div_media').html(resultado);
                if (resultado == 0) {
                    alert('Atualizada realizada com sucesso!');
                    window.location.reload();
                } else if (resultado == 1) {
                    alert('Erro ao atualizar, verifique as informações!');
                }
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });
            console.log(enviar);
        }

    })

//multiselect para relatorio resumo



    $('#select_ger_resumo_tipo').change(function(){

        $('#select_ger_resumo_projeto').css('display','block');


        var id_tipo = $('#select_ger_resumo_tipo').val();

        var enviar = $.post('php/funcoes.php',"&acao=busca_projby_tipo&id_tipo="+id_tipo);
        enviar.done(function(resultado){
            $('#select_ger_resumo_projeto').chosen('destroy');
            $('#select_ger_resumo_projeto').html(resultado).chosen();

        });
        enviar.fail( function(){
            alert('erro ajax consulte o admin!');
        });
        console.log(enviar);
        return false;

    });

$('#btn_gerar_rel_resumo').click(function(){

    if($('#select_ger_resumo_projeto').val() == ''){
        alert('Por favor selecione pelo menos um projeto');
    }else {

        var array_id_projetos = $('#select_ger_resumo_projeto').val();

        var enviar = $.post('php/funcoes.php', "&acao=rel_resumo_proj&array_id_projeto=" + array_id_projetos );
        enviar.done(function (resultado) {

           $('#div_rel_resumo').html(resultado);


        });
        enviar.fail(function () {
            alert('erro ajax consulte o admin!');
        });
        console.log(enviar);



    }



})



});
