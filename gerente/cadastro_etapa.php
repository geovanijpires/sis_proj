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

            <div class="form-group">
                <button type="button" id="btn_cadastrar_etapa" class="btn btn-lg btn-success">Cadastrar</button>
            </div>
            <div class="form-group">

                  <div id="div_cad_etapa">
                     <table id="tab_cad_etapa" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th scope="col">Nome da etapa</th>

                    </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = mysqli_query($con,"SELECT id,nome FROM etapa order by id")or die(mysqli_error());
                        while($result = mysqli_fetch_array($query)){
                            echo "<tr>";
                            echo "<td>".$result['nome']."</td>";

                            echo "<td><button type='button' id='".$result['id']."' class='btn_editar_etapa btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Editar'><span class='glyphicon glyphicon-pencil'></span></button>&nbsp&nbsp&nbsp<button type='button' id='".$result['id']."' class='btn_excluir_etapa btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Excluir'><span class='glyphicon glyphicon-remove'></span></button></td>";

                            echo "</tr>";
                        }
                        ?>

                        </div>


                    </tbody>
                    </table>

            </div>

        </div>
        <div id="div_confirma_exclusao_etapa" style="display: none">Tem certeza que deseja excluir a etapa?</div>

        <div id="div_cadastrar_etapa" style="display: none" class="form-group">
            <div class="form-group">
                <label for="nome_etapa">Nome da etapa </label>
                <input type="text" class="form-control" value="" id="nome_etapa">
            </div>

            <div class="form-group">
                <button type="button" id="btn_insere_etapa" class="btn btn-lg btn-success">Cadastrar</button>
            </div>

        </div>

        <!--edit tarefa -->
        <div id="div_editar_etapa" style="display: none" class="form-group">
            <input type="hidden" class="form-control" value="" id="id_etapa_edit">
            <div class="form-group">
                <label for="nome_etapa_edit">Nome da etapa </label>
                <input type="text" class="form-control" value="" id="nome_etapa_edit">
            </div>

            <div class="form-group">
                <button type="button" id="btn_edit_etapa" class="btn btn-lg btn-success">Atualizar</button>
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