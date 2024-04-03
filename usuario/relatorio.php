<?php
include_once("../php/conexao.php");
mysqli_set_charset($con,'utf8');
ini_set('default_charset','UTF-8');
session_start();
if (isset($_SESSION["login"])){
    if($_SESSION["id_nivel"] == 2 || $_SESSION["id_nivel"] == 3) {
       include('../layout/cabecalho_user.php');
?>

    <div class="container">
       <div class="well well-lg">
        <form class="form-inline">
            <div class="form-group">
                <label for="select_rel_tipo">Tipo de relatório</label>
                <select id="select_rel_tipo" class="form-control">
                    <option value="rel_projetos">Horas de projetos</option>
                    <option value="rel_adm">Horas administrativas</option>
                    <option value="rel_ferias">Férias e atestados</option>
                </select>
            </div>
            </br>
            </br>
            <div class="form-group">
                <label for="start_date">Período de </label>
                <input type="text" class="form-control" value="<?php echo date("d/m/Y"); ?>" id="start_date" placeholder="selecione a data inicial">
            </div>
            <div class="form-group">
                <label for="end_date">Até</label>
                <input type="text" class="form-control" value="<?php echo date("d/m/Y"); ?>" id="end_date" placeholder="selecione a data final">
            </div>
            <button type="button" id="btn_gerar_rel_user" class="btn btn-lg btn-success">Gerar relatório</button>
        </form>

       </div>
        <!--DIV CARREGA RESULTADO RELATORIO -->
        <div id="div_confirma_exclusao_registro" style="display: none">Tem certeza que deseja excluir o registro?</div>
        <div id="div_rel"></div>

        <!--DIV edita registro proj-->
        <div id="div_edit_reg" style="display: none" >

                <input type="hidden" id="id_reg_edit" >
                <input type="hidden" id="id_func_edit" >
                    <div class="form-group">
                        <label for="input_datepicker">Data</label>
                        <input type="text" id="input_datepicker_edit" value="" class="form-control" placeholder="Escolha uma data">
                    </div>
                    <div class="form-group">
                        <label for="select_projeto_edit">Projeto</label>
                        <select id="select_projeto_edit" class="form-control">

                        </select>
                    </div>

                    <div class="form-group" id="div_etapa"  >
                        <label for="select_etapa_edit">Etapa</label>
                        <select id="select_etapa_edit" class="form-control">

                        </select>
                    </div>

                    <div class="form-group" id="div_subetapa_edit"  >
                        <label for="select_subetapa_edit">Subetapa</label>
                        <select id="select_subetapa_edit" class="form-control">

                        </select>
                    </div>
                    </br>
            <form class="form-inline">
                <div class="form-group">
                    <label for="start_date">Início da atividade </label>
                    <input type="text" class="form-control" value="" id="h_inicial" placeholder="Hora inicial da atividade" required>
                </div>
                <div class="form-group">
                    <label for="end_date">Fim</label>
                    <input type="text" class="form-control" value="" id="h_final" placeholder="Hora final da atividade" required>
                </div>

            </form>

                    </br>
                    <div id="div_button_editar_reg">
                        <button type="button" id="btn_editar_reg" class="btn btn-lg btn-success">Atualizar</button>
                    </div>


        </div>

        <!--DIV edita registro adm-->
        <div id="div_edit_reg_adm" style="display: none;">
            <input type="hidden" id="id_adm_edit" >
            <input type="hidden" id="id_func_adm_edit" >
            <div class="form-group">
                <label for="input_datepicker_adm">Data</label>
                <input type="text" id="input_datepicker_adm_edit" class="form-control" >
            </div>
            <div class="form-group">
                <label for="select_adm_edit">Horas administrativas</label>
                <select id="select_adm_edit" class="form-control">

                </select>
            </div>
            </br>
            <form class="form-inline">
                <div class="form-group">
                    <label for="start_date">Início da atividade </label>
                    <input type="text" class="form-control" value="" id="inicio_atividade_adm_edit" >
                </div>
                <div class="form-group">
                    <label for="end_date">Fim</label>
                    <input type="text" class="form-control" value="" id="fim_atividade_adm_edit" >
                </div>

            </form>
            </br>
            <div id="div_button_edit_adm">
                <button type="button" id="btn_editar_adm" class="btn btn-lg btn-success">Atualizar</button>
            </div>

        </div>

    </div>

<?php
include('../layout/rodape.php');
   }else{
        header('Location: ../login.php');
    }
}else {
    header('Location: ../login.php');
}

?>
