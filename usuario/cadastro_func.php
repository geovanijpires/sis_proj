<?php

include_once("../php/conexao.php");
mysqli_set_charset($con,'utf8');
ini_set('default_charset','UTF-8');
session_start();
if (isset($_SESSION["login"])){
    if($_SESSION["id_nivel"] == 3) {
        include('../layout/cabecalho_user.php');
        ?>

        <div id="teste" class="container">

            <div class="form-group">
                <button type="button" id="btn_cadastrar_func" class="btn btn-lg btn-success">Cadastrar</button>
            </div>
            <div class="form-group">

                  <div id="div_cad_func">
                     <table id="tab_cad_func" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th scope="col">Login</th>
                        <th scope="col">Nome</th>                        
                        <th scope="col">Faz hora extra?</th>

                    </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = mysqli_query($con,"SELECT funcionario.id,funcionario.login, funcionario.nome, cargo.cargo, funcionario.horas_diarias, funcionario.valor_hora, funcionario.salario, nivel_func.nivel, funcionario.hora_extra FROM funcionario inner join nivel_func on funcionario.id_nivel = nivel_func.id inner join cargo on funcionario.id_cargo = cargo.id order by funcionario.nome")or die(mysqli_error());
                        while($result = mysqli_fetch_array($query)){
                            echo "<tr>";
                            echo "<td>".$result['login']."</td>";
                            echo "<td>".$result['nome']."</td>";
                            
                            if($result['hora_extra'] == 0){
                                echo "<td>Não</td>";
                            }else{
                                echo "<td>Sim</td>";
                            }
                            
                            echo "<td><button type='button' id='".$result['id']."' class='btn_editar_func btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Editar'><span class='glyphicon glyphicon-pencil'></span></button></td>";

                            echo "</tr>";
                        }
                        ?>

                        </div>


                    </tbody>
                    </table>

            </div>

        </div>
       


        <!-- edit func -->

        <div id="div_edit_func" style="display: none" class="form-group">
            <input type="hidden" class="form-control" value="" id="id_func_edit">
           
            <div class="form-group" >
                <label for="select_hextra_func_edit">Faz hora extra?</label>
                <select id="select_hextra_func_edit" class="form-control">

                </select>

            </div>
            <div class="form-group">
                <button type="button" id="btn_edit_func" class="btn btn-lg btn-success">Atualizar</button>
            </div>

        </div>

        <!-- edit historico de dados -->


        


        <?php
        include('../layout/rodape.php');
    }else {
        header('Location: ../login.php');
    }
}else {
    header('Location: ../login.php');
}

?>