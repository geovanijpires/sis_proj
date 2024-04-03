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
                <button type="button" id="btn_cadastrar_func" class="btn btn-lg btn-success">Cadastrar</button>
            </div>
            <div class="form-group">

                  <div id="div_cad_func">
                     <table id="tab_cad_func" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th scope="col">Login</th>
                        <th scope="col">Nome</th>
                        <th scope="col">Cargo</th>
                        <th scope="col">Horas diárias</th>
                        <th scope="col">Valor hora</th>
                        <th scope="col">Salário</th>
                        <th scope="col">Nível</th>
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
                            echo "<td>".$result['cargo']."</td>";
                            echo "<td>".$result['horas_diarias']."</td>";
                            echo "<td>".$result['valor_hora']."</td>";
                            echo "<td>".$result['salario']."</td>";
                            echo "<td>".$result['nivel']."</td>";
                            if($result['hora_extra'] == 0){
                                echo "<td>Não</td>";
                            }else{
                                echo "<td>Sim</td>";
                            }
                            
                            echo "<td><button type='button' id='".$result['id']."' class='btn_editar_func btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Editar'><span class='glyphicon glyphicon-pencil'></span></button>&nbsp&nbsp&nbsp<button type='button' id='".$result['id']."' class='btn_edit_hist btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Alterar histórico de dados'><span class='glyphicon glyphicon-dashboard'></span></button></button>&nbsp&nbsp&nbsp<button type='button' id='".$result['id']."' class='btn_excluir_func btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Excluir'><span class='glyphicon glyphicon-remove'></span></button></td>";

                            echo "</tr>";
                        }
                        ?>

                        </div>


                    </tbody>
                    </table>

            </div>

        </div>
        <div id="div_confirma_exclusao_func" style="display: none">Tem certeza que deseja excluir o funcionário?</div>

        <div id="div_cadastrar_func" style="display: none" class="form-group">
            <div class="form-group">
                <label for="login_func">Login </label>
                <input type="text" class="form-control" value="" id="login_func">
            </div>
            <div class="form-group">
                <label for="senha_func">Senha</label>
                <input type="password" class="form-control" value="" id="senha_func" >
            </div>
            <div class="form-group">
                <label for="senha_func_confirm">Confirmação de senha</label>
                <input type="password" class="form-control" value="" id="senha_func_confirm" >
            </div>
            <div class="form-group">
                <label for="start_date">Nome</label>
                <input type="text" class="form-control" value="" id="nome_func" >
            </div>
            <div class="form-group">
                <label for="start_date">Cargo </label>

                <select id="select_cargo_func" class="form-control">
                    <?php
                    $query_cargo = "Select id,cargo from cargo order by cargo ASC";
                    $consulta_cargo = mysqli_query($con,$query_cargo);
                    while ($resultado_consulta_cargo = mysqli_fetch_array($consulta_cargo)) {
                        echo '<option value='.$resultado_consulta_cargo['id'].'>'.$resultado_consulta_cargo['cargo'].'</option>';
                    }
                    ?>

                </select>

            </div>
            <div class="form-group">
                <label for="start_date">Horas diárias </label>
                <input type="text" class="form-control" value="" id="horas_func" >
            </div>
            <div class="form-group">
                <label for="start_date">Valor hora </label>
                <input type="text" class="form-control" value="" id="valor_func" placeholder="ex: 80.50">
            </div>
            <div class="form-group">
                <label for="start_date">salário </label>
                <input type="text" class="form-control" value="" id="salario_func" placeholder="ex: 2500.00">
            </div>
            <div class="form-group" >
                <label for="select_nivel_func">Nível de acesso</label>
                <select id="select_nivel_func" class="form-control">
                   <?php
                    $query = "Select id,nivel from nivel_func order by nivel desc";
                    $consulta = mysqli_query($con,$query);
                    while ($resultado_consulta = mysqli_fetch_array($consulta)) {
                        echo '<option value='.$resultado_consulta['id'].'>'.$resultado_consulta['nivel'].'</option>';
                    }
                    ?>

                </select>

            </div>
            <div class="form-group">
                <button type="button" id="btn_insere_func" class="btn btn-lg btn-success">Cadastrar</button>
            </div>


        </div>


        <!-- edit func -->

        <div id="div_edit_func" style="display: none" class="form-group">
            <input type="hidden" class="form-control" value="" id="id_func_edit">
            <div class="form-group">
                <label for="login_func">Login </label>
                <input type="text" class="form-control" value="" id="login_func_edit">
            </div>
            <div class="form-group">
                <label for="senha_func">Senha</label>
                <input type="password" class="form-control" value="" id="senha_func_edit" placeholder="Deixe em branco caso queira manter a senha já cadastrada" >
            </div>
            <div class="form-group">
                <label for="senha_func_confirm">Confirmação de senha</label>
                <input type="password" class="form-control" value="" id="senha_func_confirm_edit" placeholder="Deixe em branco caso queira manter a senha já cadastrada">
            </div>
            <div class="form-group">
                <label for="start_date">Nome</label>
                <input type="text" class="form-control" value="" id="nome_func_edit" >
            </div>
            <div class="form-group">
                <label for="select_cargo_func_edit">Cargo </label>
                <select id="select_cargo_func_edit" class="form-control">

                </select>
            </div>
            <div class="form-group">
                <label for="start_date">Horas diárias </label>
                <input type="text" class="form-control" value="" id="horas_func_edit" >
            </div>
            <div class="form-group">
                <label for="start_date">Valor hora </label>
                <input type="text" class="form-control" value="" id="valor_func_edit" placeholder="ex: 80.50">
            </div>
            <div class="form-group">
                <label for="start_date">salário </label>
                <input type="text" class="form-control" value="" id="salario_func_edit" placeholder="ex: 2500.00">
            </div>
            <div class="form-group" >
                <label for="select_nivel_func_edit">Nível de acesso</label>
                <select id="select_nivel_func_edit" class="form-control">

                </select>

            </div>
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

        <div id="div_edit_hist" style="display: none" class="form-group">
            <input type="hidden" class="form-control" value="" id="id_func_hist">
            <div class="form-group">
                <label>Selecione uma opção para editar o histórico de informações do usuário</label>                
                <select id="select_hist_func_choose" class="form-control">
                    <option value="0" selected disabled>Selecione uma opção</option>
                    <option value="1">Horas diárias</option>
                    <option value="2">Férias/Atestados/Day Off</option>
                    <option value="3">Valor hora</option>                    
                </select>
                <div id="div_hist_func_edit"></div>

            </div>

        </div>

        <!-- modal edit las hour -->

        <div id="div_edit_last_hour" style="display: none" class="form-group">
            <input type="hidden" class="form-control" value="" id="field_id_func_editlh">
            <input type="hidden" class="form-control" value="" id="field_id_registro_editlh">
            
            <div class="form-group">
                <label>Horas diárias </label>
                <input type="text" class="form-control" value="" id="field_h_antiga_edit" >
            </div>
            <div class="form-group">
                <label>Até </label>
                <input type="text" class="form-control" value="" id="field_ate_edit">
            </div>
            
            <div class="form-group">
                <button type="button" id="btn_edit_last_hour" class="btn btn-lg btn-success">Atualizar</button>
            </div>

        </div>

        <div id="div_confirma_exclusao_lasthour" style="display: none">Tem certeza que deseja excluir este histórico de horas diárias ?</div>
        
        <!-- modal edit ferias -->

        <div id="div_edit_ferias" style="display: none" class="form-group">
            <input type="hidden" class="form-control" value="" id="field_ferias_id_func_edit">
            <input type="hidden" class="form-control" value="" id="field_ferias_id_registro_edit">
            
            <div class="form-group">
                <label>Data Inícial </label>
                <input type="text" class="form-control" value="" id="field_data_inicio_edit" >
            </div>
            <div class="form-group">
                <label>Data Final </label>
                <input type="text" class="form-control" value="" id="field_data_fim_edit" >
            </div>
            <div class="form-group">
                <label>Horas diárias </label>
                <input type="text" class="form-control" value="" id="field_hora_diaria_edit">
            </div>
            
            <div class="form-group">
                <button type="button" id="btn_edit_ferias" class="btn btn-lg btn-success">Atualizar</button>
            </div>

        </div>

        <div id="div_edit_valor_hora" style="display: none" class="form-group">
            <input type="hidden" class="form-control" value="" id="field_id_func_edit">
                        
            <div class="form-group">
                <label>De </label>
                <input type="text" class="form-control" value="" id="field_data_de_edit" >
            </div>
            <div class="form-group">
                <label>Até </label>
                <input type="text" class="form-control" value="" id="field_data_ate_edit" >
            </div>
            <div class="form-group">
                <label>Valor Hora </label>
                <input type="text" class="form-control" value="" id="field_valor_hora_edit">
            </div>
            
            <div class="form-group">
                <button type="button" id="btn_edit_valor_hora" class="btn btn-lg btn-success">Atualizar</button>
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