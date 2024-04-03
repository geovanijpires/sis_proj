$(function() {

     //select tipo horas projetos ou adm
     $('#select_op_tipo').change(function(){
        if($('#select_op_tipo').val() == 'h_projetos'){
            $('#div_projetos').css('display','block');
            $('#div_adm').css('display','none');
        }else if ($('#select_op_tipo').val() == 'h_adm'){
            $('#div_projetos').css('display','none');
            $('#div_adm').css('display','block');
        }
    });
    //select projeto somente para habilitar os outros
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

            //validar se pode fazer horas extras
            var enviar = $.post('php/funcoes.php', "&acao=valida_hora_extra&data=" + data + "&inicio_atividade=" + inicio_atividade + "&fim_atividade=" + fim_atividade);
            enviar.done(function (res) {
                if(JSON.parse(res).status == 'false'){
                    alert('Você não pode lançar mais que '+JSON.parse(res).msg_hdiaria+' horas diárias entre projeto e horas administrativas.');               
                }else {

                    //Inserir registro
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


                }
                
                
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });

            
            //fecha validar se pode fazer horas extras       
            

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

            //validar se pode fazer horas extras
            var enviar = $.post('php/funcoes.php', "&acao=valida_hora_extra&data=" + data + "&inicio_atividade=" + inicio_atividade_adm + "&fim_atividade=" + fim_atividade_adm);
            enviar.done(function (res) {
                if(JSON.parse(res).status == 'false'){
                    alert('Você não pode lançar mais que '+JSON.parse(res).msg_hdiaria+' horas diárias entre projeto e horas administrativas.');            
                }else {

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


                }
                
                
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });

            
            //fecha validar se pode fazer horas extras 


            

        }
    })

    $('#btn_gerar_rel_user').click(function () {

        var start_data = $('#start_date').val();
        var end_data = $('#end_date').val();

        if ($('#select_rel_tipo').val() == 'rel_projetos') {

            var enviar = $.post('php/funcoes.php', "&acao=relatorio_proj&start_data=" + start_data + "&end_data=" + end_data);
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

            var enviar = $.post('php/funcoes.php', "&acao=relatorio_adm&start_data=" + start_data + "&end_data=" + end_data);

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

            var enviar = $.post('php/funcoes.php', "&acao=relatorio_ferias&start_data=" + start_data + "&end_data=" + end_data );

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

            //validar se pode fazer horas extras
            var enviar = $.post('php/funcoes.php', "&acao=valida_hora_extra&data=" + data + "&inicio_atividade=" + h_inicial + "&fim_atividade=" + h_final);
            enviar.done(function (res) {
                if(JSON.parse(res).status == 'false'){
                    alert('Você não pode lançar mais que '+JSON.parse(res).msg_hdiaria+' horas diárias entre projeto e horas administrativas.');               
                }else {
                    console.log(res);
                    //Inserir registro
                    var enviar = $.post('php/funcoes.php', "&acao=edit_new_reg&id_reg=" + id_reg + "&data=" + data + "&id_func=" + id_func +  "&id_projeto=" + select_projeto_edit + "&id_etapa=" + select_etapa_edit + "&id_subetapa=" + select_subetapa_edit + "&h_inicial=" + h_inicial + "&h_final=" + h_final );
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
                    //fecha inserir registro


                }
                
                
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });

            
            //fecha validar se pode fazer horas extras 


            
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

            //validar se pode fazer horas extras
            var enviar = $.post('php/funcoes.php', "&acao=valida_hora_extra&data=" + data + "&inicio_atividade=" + inicio_atividade_adm + "&fim_atividade=" + fim_atividade_adm);
            enviar.done(function (res) {
                if(JSON.parse(res).status == 'false'){
                    alert('Você não pode lançar mais que '+JSON.parse(res).msg_hdiaria+' horas diárias entre projeto e horas administrativas.');               
                }else {

                    //inserir
                    var enviar = $.post('php/funcoes.php', "&acao=edit_new_adm&id=" + id +"&data=" + data + "&id_func=" + id_func + "&adm=" + adm + "&inicio_atividade_adm=" + inicio_atividade_adm + "&fim_atividade_adm=" + fim_atividade_adm);
                    enviar.done(function (resultado) {
                        if (resultado == 0) {
                            alert('Registro atualizado com sucesso!!!');
                            window.location.reload();

                        } else if(resultado == 2){
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
                    //fecha inserir


                }
                
                
            });
            enviar.fail(function () {
                alert('erro ajax consulte o admin!');
            });

            
            //fecha validar se pode fazer horas extras 

            
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

    $('#btn_edit_func').click(function () {
        var id_func = $('#id_func_edit').val();
        var login_func = $('#login_func_edit').val();
        var senha_func = $('#senha_func_edit').val();
        var senha_func_confirm = $('#senha_func_confirm_edit').val();
        var nome_func = $('#nome_func_edit').val();


        if(login_func == ''){
            alert('Por favor preencha o campo login!');
            $('#login_func_edit').focus();
        }else if(senha_func_confirm != senha_func) {
            alert('O campo senha e confirmação de senha deve ser iguais!');
            $('#senha_func_confirm_edit').focus();

        }else if(nome_func == ''){
            alert('Por favor preencha o campo nome!');
            $('#nome_func_edit').focus();
        }else {

            var enviar = $.post('php/funcoes.php', "&acao=edit_new_func&id_func=" + id_func + "&login_func=" + login_func +  "&senha_func=" + senha_func + "&nome_func=" + nome_func);
            enviar.done(function (resultado) {
                
                if (resultado == 0) {
                    alert('Funcionário atualizado com sucesso!');
                    window.location.reload();
                } else if (resultado == 1) {
                    alert('Erro ao atualizar o funcionário, verifique as informações!');
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

    $('#btn_edit_func').click(function () {
        var id_func = $('#id_func_edit').val();
        var hora_extra = $('#select_hextra_func_edit').val();

        var enviar = $.post('php/funcoes.php', "&acao=edit_new_func&id_func=" + id_func + "&hora_extra=" + hora_extra);
        enviar.done(function (resultado) {
            
            //$('#div_media').html(resultado);
            if (resultado == 0) {
                alert('Atualizado realizada com sucesso!');
                window.location.reload();
            } else if (resultado == 1) {
                alert('Erro ao atualizar o funcionário, verifique as informações!');
            }
        });
        enviar.fail(function () {
            alert('erro ajax consulte o admin!');
        });
        console.log(enviar);

    })



});
