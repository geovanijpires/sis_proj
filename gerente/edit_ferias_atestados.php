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

                <div id="div_edit_ferias">

                    <table id="tab_edit_ferias" class="table table-striped table-bordered">
                        <thead>
                        <tr>

                            <th scope="col">Nome</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Início</th>
                            <th scope="col">Fim</th>
                           
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $id_func = $_SESSION["id_func"];
                     
                        $query = mysqli_query($con,"SELECT ferias_atestados.id, funcionario.nome, tipo_ferias_atestado.tipo, ferias_atestados.data_inicio, ferias_atestados.data_fim FROM ferias_atestados inner Join funcionario on funcionario.id = ferias_atestados.id_func inner join tipo_ferias_atestado on tipo_ferias_atestado.id = ferias_atestados.tipo where ferias_atestados.id_func = '$id_func' ORDER BY data_inicio DESC ")or die(mysqli_error());
                        while($result = mysqli_fetch_array($query)){
                            echo "<tr>";
                            echo "<td>".$result['nome']."</td>";
                            echo "<td>".$result['tipo']."</td>";
                            //inverte datas
                            $result['data_inicio'] = explode("-", $result['data_inicio']);
                            list($ano,$mes,$dia) = $result['data_inicio'];
                            $data_inicio = "$dia/$mes/$ano";
                            $result['data_fim'] = explode("-", $result['data_fim']);
                            list($ano,$mes,$dia) = $result['data_fim'];
                            $data_fim = "$dia/$mes/$ano";

                            echo "<td>".$data_inicio."</td>";
                            echo "<td>".$data_fim."</td>";

                            echo "<td><button type='button' id='".$result['id']."' class='btn_editar_ferias btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Editar'><span class='glyphicon glyphicon-pencil'></span></button>&nbsp&nbsp&nbsp<button type='button' id='".$result['id']."' class='btn_excluir_ferias btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Excluir'><span class='glyphicon glyphicon-remove'></span></button></td>";

                            echo "</tr>";
                        }
                        ?>

                </div>


                </tbody>
                </table>

            </div>

            <div id="div_confirma_exclusao_ferias" style="display: none">Tem certeza que deseja excluir ?</div>


           <!--edit -->
           <div id="div_edit_reg_ferias" style="display: none" class="form-group">
            <input type="hidden" class="form-control" value="" id="id_ferias_edit">

            <div class="form-group">
                <label for="input_edit_ferias_inicio">Data Início</label>
                <input type="text" id="input_edit_ferias_inicio" value="" class="form-control" placeholder="Escolha uma data de início">
            </div>
            <div class="form-group">
                <label for="input_edit_ferias_fim">Data Fim</label>
                <input type="text" id="input_edit_ferias_fim" value="" class="form-control" placeholder="Escolha uma data final">
            </div>

            <div class="form-group">
                <label for="select_editferias_atestados">Férias / Atestado / Day off</label>
                <select id="select_editferias_atestados" class="form-control">
                                        
                    <?php
                        $query = "Select id,tipo from tipo_ferias_atestado order by id";
                        $consulta = mysqli_query($con,$query);
                        while ($resultado_consulta = mysqli_fetch_array($consulta)) {
                            echo '<option value='.$resultado_consulta['id'].'>'.$resultado_consulta['tipo'].'</option>';
                        }
                        ?>

                </select>
            </div>            


            <div class="form-group">
                <button type="button" id="btn_edit_reg_ferias" class="btn btn-lg btn-success">Atualizar</button>
            </div>


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