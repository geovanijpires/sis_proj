<?php

include_once("../php/conexao.php");
mysqli_set_charset($con,'utf8');
ini_set('default_charset','UTF-8');
session_start();
if (isset($_SESSION["login"])){
    if($_SESSION["id_nivel"] == 1) {
        include('../layout/cabecalho_gerente.php');
        ?>

        <div id="teste" class="container">

            <div class="form-group" >
                <label for="select_cad_subetapa">Escolha a etapa</label>
                <select id="select_cad_subetapa" class="form-control">
                    <?php
                    $query = "Select id,nome from etapa";
                    $consulta = mysqli_query($con,$query);
                    echo '<option value="default" disabled selected>Selecione uma etapa para listar suas subetapas</option>';
                    while ($resultado_consulta = mysqli_fetch_array($consulta)) {
                        echo '<option value='.$resultado_consulta['id'].'>'.$resultado_consulta['nome'].'</option>';
                    }
                    ?>

                </select>

            </div>
            <div id="div_botao_cad_subetapa" class="form-group" style="display: none">
                <button type="button" id="btn_cadastrar_subetapa" class="btn btn-lg btn-success">Cadastrar</button>
            </div>
        <div id="div_cad_subetapa">


        </div>

        </div>

        <div id="div_confirma_exclusao_subetapa" style="display: none">Tem certeza que deseja excluir a subetapa?</div>

        <div id="div_cadastrar_subetapa" style="display: none" class="form-group">
            <div class="form-group">
                <label for="nome_etapa">Nome da subetapa </label>
                <input type="text" class="form-control" value="" id="nome_subetapa">
            </div>

            <div class="form-group">
                <button type="button" id="btn_insere_subetapa" class="btn btn-lg btn-success">Cadastrar</button>
            </div>

        </div>

        <div id="div_editar_subetapa" style="display: none" class="form-group">
            <input type="hidden" class="form-control" value="" id="id_subetapa_edit">
            <div class="form-group">
                <label for="nome_etapa">Nome da subetapa </label>
                <input type="text" class="form-control" value="" id="nome_subetapa_edit">
            </div>

            <div class="form-group">
                <button type="button" id="btn_edit_subetapa" class="btn btn-lg btn-success">Atualizar</button>
            </div>

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