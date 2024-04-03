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

            <div class="form-group">
                <label for="start_date">Relatório resumo </label>
                </br></br>
                <label for="select_ger_resumo_tipo">Tipo de projeto</label>
                <select id="select_ger_resumo_tipo" class="form-control">
                    <option value="default" disabled selected>Selecione o tipo de projeto</option>
                    <?php
                    $query = "Select id, tipo from tipo_proj order by tipo";
                    $consulta = mysqli_query($con,$query);
                    while ($resultado_consulta = mysqli_fetch_array($consulta)) {
                        echo '<option value='.$resultado_consulta['id'].'>'.$resultado_consulta['tipo'].'</option>';
                    }
                    ?>

                </select>

            </div>

            <div class="form-group">

                <select id="select_ger_resumo_projeto" style="display: none" data-placeholder="Selecione um ou mais projetos" class="form-control chosen-select" multiple tabindex="4" >

                </select>

            </div>


            </br>
            <button type="button" id="btn_gerar_rel_resumo" class="btn btn-lg btn-success">Gerar relatório</button>

        </div></br></br>
        <!--DIV CARREGA RESULTADO RELATORIO -->
        <div id="div_rel_resumo" class="container"></div>

        <?php
        include('../layout/rodape.php');
    }else {
        header('Location: ../login.php');
    }
}else {
    header('Location: ../login.php');
}

?>