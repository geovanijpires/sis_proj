$(function() {

    jQuery.ajaxSetup({
        beforeSend: function() {
            showLoading({

                name: 'circle-turn'

            });
        },
        complete: function(){
            hideLoading();
        },
        success: function() {}
    });

    //MASCARAS DE CAMPOS

    $('#inicio_atividade').mask("99:99");
    $('#fim_atividade').mask("99:99");
    $('#h_inicial').mask("99:99");
    $('#h_final').mask("99:99");
    $('#inicio_atividade_adm').mask("99:99");
    $('#fim_atividade_adm').mask("99:99");
    $('#input_datepicker').mask("99/99/9999");
    $('#input_datepicker_adm').mask("99/99/9999");
    $('#inicio_atividade_adm_edit').mask("99:99");
    $('#fim_atividade_adm_edit').mask("99:99");


    //DATE PICKERS AQUI
    $('#input_datepicker').datepicker({

        dateFormat: 'dd/mm/yy',
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
        dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
        monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior'

    });

    $('#input_datepicker_edit').datepicker({

        dateFormat: 'dd/mm/yy',
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
        dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
        monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior'

    });

   
    $('#input_datepicker_adm').datepicker({

        dateFormat: 'dd/mm/yy',
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
        dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
        monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior'

    });
    $('#input_datepicker_adm_edit').datepicker({

        dateFormat: 'dd/mm/yy',
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
        dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
        monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior'

    });
    $('#start_date').datepicker({

        dateFormat: 'dd/mm/yy',
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
        dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
        monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior'

    });
    $('#end_date').datepicker({

        dateFormat: 'dd/mm/yy',
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
        dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
        monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior'

    });

    $('#input_datepicker_ferias_inicio').mask("99/99/9999");
    $('#input_datepicker_ferias_inicio').datepicker({

        dateFormat: 'dd/mm/yy',
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
        dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
        monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior'

    });
    $('#input_datepicker_ferias_fim').mask("99/99/9999");
    $('#input_datepicker_ferias_fim').datepicker({

        dateFormat: 'dd/mm/yy',
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
        dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
        monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior'

    });
    $('#input_datepicker_atestado').mask("99/99/9999");
    $('#input_datepicker_atestado').datepicker({

        dateFormat: 'dd/mm/yy',
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
        dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
        monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior'

    });
    $('#input_datepicker_atestado_inicio').mask("99/99/9999");
    $('#input_datepicker_atestado_inicio').datepicker({

        dateFormat: 'dd/mm/yy',
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
        dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
        monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior'

    });
    $('#input_datepicker_atestado_fim').mask("99/99/9999");
    $('#input_datepicker_atestado_fim').datepicker({

        dateFormat: 'dd/mm/yy',
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
        dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
        monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior'

    });

    $('#input_edit_ferias_inicio').datepicker({

        dateFormat: 'dd/mm/yy',
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
        dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
        monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior'

    });

    $('#input_edit_ferias_fim').datepicker({

        dateFormat: 'dd/mm/yy',
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
        dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
        monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior'

    });

      //SELECTS AQUI
   


    $('#select_ferias_atestados').change(function () {
        
            $('#div_content_ferias').css('display', 'block');
                   

    });

    
    /* $('#select_op_tipo').select2({
        //placeholder: 'Selecione uma opção', // Texto exibido quando nenhum valor é selecionado
        allowClear: true, // Permite que o usuário limpe a seleção
        width: '100%'
      }); */

    $('#select_projeto').select2({
        //placeholder: 'Selecione uma opção', // Texto exibido quando nenhum valor é selecionado
        allowClear: false, // Permite que o usuário limpe a seleção
        width: '100%'
      });

      $('#select_etapa').select2({
        //placeholder: 'Selecione uma opção', // Texto exibido quando nenhum valor é selecionado
        allowClear: false, // Permite que o usuário limpe a seleção
        width: '100%'
      });  

      $('#select_subetapa').select2({
        //placeholder: 'Selecione uma opção', // Texto exibido quando nenhum valor é selecionado
        allowClear: false, // Permite que o usuário limpe a seleção
        width: '100%'
      }); 

      
      $('#select_adm').select2({
        //placeholder: 'Selecione uma opção', // Texto exibido quando nenhum valor é selecionado
        allowClear: false, // Permite que o usuário limpe a seleção
        width: '100%'
      });
      
      /* $('#select_projeto_edit').select2({
        //placeholder: 'Selecione uma opção', // Texto exibido quando nenhum valor é selecionado
        allowClear: true, // Permite que o usuário limpe a seleção
        width: '100%'
      });
      
      $('#select_etapa_edit').select2({
        //placeholder: 'Selecione uma opção', // Texto exibido quando nenhum valor é selecionado
        allowClear: true, // Permite que o usuário limpe a seleção
        width: '100%'
      });
      $('#select_subetapa_edit').select2({
        //placeholder: 'Selecione uma opção', // Texto exibido quando nenhum valor é selecionado
        allowClear: true, // Permite que o usuário limpe a seleção
        width: '100%'
      }); */
     

});

