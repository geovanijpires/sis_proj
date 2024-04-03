<?php

include_once("../php/conexao.php");
mysqli_set_charset($con,'utf8');
ini_set('default_charset','UTF-8');
session_start();
if (isset($_SESSION["login"])){
    if($_SESSION["id_nivel"] == 1) {
        include('../layout/cabecalho_gerente.php');
?>

    <div class="container">
        <div class="well well-lg">
            <form class="form-group">
                <div class="form-group">
                    <label for="select_rel_tipo">Tipo de relatório</label>
                    <select id="select_rel_tipo" class="form-control">
                        <option value="default" disabled selected>Selecione uma opção</option>
                        <option value="rel_projetos">Horas de projetos</option>
                        <option value="rel_adm">Horas Administrativas</option>
                    </select>
                </div>
                <div id="div_h_projetos" style="display: none">

                    <div class="form-group">
                        <label for="select_ger_projeto">Projeto</label>
                        <select id="select_ger_projeto" class="form-control">
                            <option value="default" disabled selected>Selecione o projeto</option>
                            <?php
                            $query = "Select id,cod_projeto,nome from projeto where data_fim is null order by cod_projeto";
                            $consulta = mysqli_query($con,$query);
                            while ($resultado_consulta = mysqli_fetch_array($consulta)) {
                                echo '<option value='.$resultado_consulta['id'].'>'.$resultado_consulta['cod_projeto'].'   '.$resultado_consulta['nome'].'</option>';
                            }
                            ?>

                        </select>
                    </div>

                    <div class="form-group">
                        <label for="select_rel_periodo">Período</label>
                        <select id="select_rel_periodo" class="form-control">
                            <option value="default" disabled selected>Escolha o período para geração do relatório</option>
                            <option value="rel_all">Toda a vida do projeto</option>
                            <option value="rel_intervalo">Escolher intervalo</option>
                        </select>
                    </div>
                    <div id="div_h_proj_datepicker" style="display: none">

                            <div class="form-group">
                                <label for="start_date_p">Período de </label>
                                <input type="text" style="width: auto" class="form-control" value="<?php echo date("d/m/Y"); ?>" id="start_date_p" placeholder="selecione a data inicial">
                            </div>
                            <div class="form-group">
                                <label for="end_date_p">Até</label>
                                <input type="text" style="width: auto" class="form-control" value="<?php echo date("d/m/Y"); ?>" id="end_date_p" placeholder="selecione a data final">
                            </div>
                    </div>
                    <button type="button" id="btn_gerar_rel_ger_proj" class="btn btn-lg btn-success">Gerar relatório</button>

                </div>
                <div id="div_h_adm" style="display: none">
                    <div class="form-group">
                        <label for="start_date_padm">Período de </label>
                        <input type="text" style="width: auto" class="form-control" value="<?php echo date("d/m/Y"); ?>" id="start_date_padm" placeholder="selecione a data inicial">
                    </div>
                    <div class="form-group">
                        <label for="end_date_p">Até</label>
                        <input type="text" style="width: auto" class="form-control" value="" id="end_date_padm" placeholder="selecione a data final">
                    </div>

                    <button type="button" id="btn_gerar_rel_ger_adm" class="btn btn-lg btn-success">Gerar relatório</button>
                </div>

            </form>

        </div>
        <!--DIV CARREGA RESULTADO RELATORIO -->
        <div id="div_rel"></div>

    </div>

<?php
     include('../layout/rodape.php');
}else {
     header('Location: ../login.php');
}
}else {
    header('Location: ../login.php');
}

?>