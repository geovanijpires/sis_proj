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
                <button type="button" id="btn_cadastrar_proj" class="btn btn-lg btn-success">Cadastrar</button>
            </div>
            <div class="form-group">

                  <div id="div_cad_proj">
                     <table id="tab_cad_proj" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th scope="col">Código</th>
                        <th scope="col">Nome</th>
                        <th scope="col">Metragem</th>
                        <th scope="col">Cômodos</th>
                        <th scope="col">Cômodos realizado</th>
                        <th scope="col">Valor</th>
                        <th scope="col">Taxa Adm</th>
                        <th scope="col">Visitas</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = mysqli_query($con,"SELECT projeto.id,projeto.cod_projeto, projeto.data_fim, projeto.nome, projeto.metragem, projeto.comodos, projeto.comodos_realizados, projeto.valor_proj, projeto.visitas, projeto.taxa_adm, tipo_proj.id as id_tipo, tipo_proj.tipo FROM projeto inner join tipo_proj on projeto.id_tipo = tipo_proj.id order by cod_projeto DESC");
                        while($result = mysqli_fetch_array($query)){
                            if($result['data_fim'] == null) {
                                echo "<tr>";
                                echo "<td>" . $result['cod_projeto'] . "</td>";
                                echo "<td>" . $result['nome'] . "</td>";
                                echo "<td>" . $result['metragem'] . "</td>";
                                echo "<td>" . $result['comodos'] . "</td>";
                                echo "<td>" . $result['comodos_realizados'] . "</td>";
                                echo "<td>" . $result['valor_proj'] . "</td>";
                                echo "<td>" . $result['taxa_adm'] . "</td>";
                                echo "<td>" . $result['visitas'] . "</td>";
                                echo "<td>" . $result['tipo'] . "</td>";
                                echo "<td><button type='button' id='" . $result['id'] . "' class='btn_editar_proj btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Editar'><span class='glyphicon glyphicon-pencil'></span></button>&nbsp&nbsp&nbsp<button type='button' id='" . $result['id'] . "' class='btn_excluir_proj btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Excluir'><span class='glyphicon glyphicon-remove'></span></button>&nbsp&nbsp&nbsp<button type='button' id='" . $result['id'] . "' class='btn_finalizar_proj btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Finalizar'><span class='glyphicon glyphicon-off'></span></button></td>";
                                echo "</tr>";
                            }else {
                                echo "<tr>";
                                echo "<td>" . $result['cod_projeto'] . "</td>";
                                echo "<td>" . $result['nome'] . "</td>";
                                echo "<td>" . $result['metragem'] . "</td>";
                                echo "<td>" . $result['comodos'] . "</td>";
                                echo "<td>" . $result['comodos_realizados'] . "</td>";
                                echo "<td>" . $result['valor_proj'] . "</td>";
                                echo "<td>" . $result['taxa_adm'] . "</td>";
                                echo "<td>" . $result['visitas'] . "</td>";
                                echo "<td>" . $result['tipo'] . "</td>";
                                echo "<td><button type='button' id='" . $result['id'] . "' class='btn_reativar_proj btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Reativar'><span class='glyphicon glyphicon-ok'></span></button></td>";
                                echo "</tr>";


                            }
                        }
                        ?>

                        </div>


                    </tbody>
                    </table>

            </div>

        </div>
        <div id="div_confirma_exclusao_proj" style="display: none">Tem certeza que deseja excluir o projeto?</div>
        <div id="div_confirma_finalizacao_proj" style="display: none">Tem certeza que deseja finalizar o projeto? não será possível visualiza-lo na tela de inserção de hora.</div>
        <div id="div_confirma_reativacao_proj" style="display: none">Tem certeza que deseja reativar o projeto? ele voltará a aparecer na tela de inserção de horas.</div>

        <div id="div_cadastrar_proj" style="display: none" class="form-group">
            <div class="form-group">
                <label for="cod_proj">Código do projeto </label>
                <input type="text" class="form-control" value="" id="cod_proj">
            </div>
            <div class="form-group">
                <label for="nome_proj">Nome</label>
                <input type="text" class="form-control" value="" id="nome_proj" >
            </div>
            <div class="form-group">
                <label for="start_date">Metragem</label>
                <input type="text" class="form-control" value="" id="metragem_proj" placeholder="ex: 10.90">
            </div>
            <div class="form-group">
                <label for="start_date">Quantidade de cômodos </label>
                <input type="number" class="form-control" value="" id="comodos_proj" placeholder="obs: valores somente numéricos">
            </div>
            <div class="form-group">
                <label for="start_date">Valor </label>
                <input type="text" class="form-control" value="" id="valor_proj" placeholder="obs: digite o valor sem vírgula ex: 31000.00">
            </div>
            <div class="form-group">
                <label for="start_date">Taxa administrativa </label>
                <input type="text" class="form-control" value="" id="tx_adm" placeholder="obs: digite o valor sem vírgula ex: 0.60 (para 60%)">
            </div>
            <div class="form-group">
                <label for="start_date">Visitas inclusas </label>
                <input type="text" class="form-control" value="" id="visitas_proj" placeholder="obs: em horas">
            </div>
            <div class="form-group">
                <label for="select_tipo_proj">Tipo</label>
                <select id="select_tipo_proj" class="form-control">
                   <?php
                    $query = "Select id,tipo from tipo_proj order by id";
                    $consulta = mysqli_query($con,$query);
                    while ($resultado_consulta = mysqli_fetch_array($consulta)) {
                        echo '<option value='.$resultado_consulta['id'].'>'.$resultado_consulta['tipo'].'</option>';
                    }
                    ?>

                </select>

            </div>
            <div class="form-group">
                <button type="button" id="btn_insere_projeto" class="btn btn-lg btn-success">Cadastrar</button>
            </div>


        </div>
<!-- editar projeto -->
        <div id="div_editar_proj" style="display: none" class="form-group">
            <input type="hidden" class="form-control" value="" id="id_proj_edit">
            <div class="form-group">
                <label for="cod_proj_edit">Código do projeto </label>
                <input type="text" class="form-control" value="" id="cod_proj_edit">
            </div>
            <div class="form-group">
                <label for="nome_proj">Nome</label>
                <input type="text" class="form-control" value="" id="nome_proj_edit" >
            </div>
            <div class="form-group">
                <label for="start_date">Metragem</label>
                <input type="text" class="form-control" value="" id="metragem_proj_edit" placeholder="ex: 10.90">
            </div>
            <div class="form-group">
                <label for="start_date">Quantidade de cômodos </label>
                <input type="number" class="form-control" value="" id="comodos_proj_edit" placeholder="obs: valores somente numéricos">
            </div>
            <div class="form-group">
                <label for="start_date">Cômodos realizados </label>
                <input type="number" class="form-control" value="" id="comodosr_proj_edit" placeholder="obs: valores somente numéricos">
            </div>
            <div class="form-group">
                <label for="start_date">Valor </label>
                <input type="text" class="form-control" value="" id="valor_proj_edit" placeholder="obs: digite o valor sem vírgula ex: 31000.00">
            </div>
            <div class="form-group">
                <label for="start_date">Taxa administrativa </label>
                <input type="text" class="form-control" value="" id="tx_adm_edit" placeholder="obs: digite o valor sem vírgula ex: 0.60 (para 60%)">
            </div>
            <div class="form-group">
                <label for="start_date">Visitas inclusas </label>
                <input type="text" class="form-control" value="" id="visitas_proj_edit" placeholder="obs: em horas">
            </div>
            <div class="form-group">
                <label for="select_tipo_proj_edit">Tipo</label>
                <select id="select_tipo_proj_edit" class="form-control">

                </select>

            </div>
            <div class="form-group">
                <button type="button" id="btn_edit_projeto" class="btn btn-lg btn-success">Atualizar</button>
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