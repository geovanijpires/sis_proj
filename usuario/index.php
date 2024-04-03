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
           <div  id="div_mensagem" style="display: none"></div>
            <div class="form-group">
                <label for="select_op_tipo">Horas de projetos ou horas administrativas?</label>
                <select id="select_op_tipo" class="form-control">
                    <option value="h_projetos">Horas de projetos</option>
                    <option value="h_adm">Horas administrativas</option>
                </select>
            </div>

            <div id="div_projetos">
                <div class="form-group">
                    <label for="input_datepicker">Data</label>
                    <input type="text" id="input_datepicker" value="<?php echo date("d/m/Y");  ?>" class="form-control" placeholder="Escolha uma data">
                </div>

                <div class="form-group">
                    <label for="select_projeto">Projeto</label>
                    <select  id="select_projeto" class="form-select">
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

                
                
                

                <div class="form-group" id="div_etapa"  >
                    <label for="select_etapa">Etapa</label>
                    <select id="select_etapa" class="form-control" disabled>
                        <option value="default" disabled selected>Selecione a etapa do projeto</option>
                        <?php
                        $query = "Select id,nome from etapa order by id";
                        $consulta = mysqli_query($con,$query);
                        while ($resultado_consulta = mysqli_fetch_array($consulta)) {
                            echo '<option value='.$resultado_consulta['id'].'>'.$resultado_consulta['nome'].'</option>';
                        }
                        ?>

                    </select>
                </div>

                <!-- div subetapa nao despesas -->

                <div class="form-group" id="div_subetapa"  >

                    <label for="select_subetapa">Subetapa</label>
                    <select id="select_subetapa" class="form-control" disabled>
                        <option value="default" disabled selected>Selecione a subetapa do projeto</option>

                    </select>

                </div>

                <!-- div subetapa despesas -->

                <div class="form-group" id="div_subetapa_despesas" style="display: none"  >

                    <label for="label_subetapa">Total da despesa</label>

                    <input type="text" class="form-control" value="" id="total_despesa" placeholder="Separado por ponto ex: 1000.00" required>
                    </br>

                    <div id="div_button_inserir_despesas">
                        <button type="button" id="btn_inserir_despesas" class="btn btn-lg btn-success">Inserir Despesa</button>
                    </div>

                </div>


                </br>

                <div id="resto_nao_despesa" >
                    <form class="form-inline">
                        <div class="form-group">
                            <label for="start_date">Início da atividade </label>
                            <input type="text" class="form-control" value="" id="inicio_atividade" placeholder="Hora inicial da atividade" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">Fim</label>
                            <input type="text" class="form-control" value="" id="fim_atividade" placeholder="Hora final da atividade" required>
                        </div>

                    </form>

                    </br>
                    <div id="div_button_inserir_proj">
                        <button type="button" id="btn_inserir_projeto" class="btn btn-lg btn-success">Inserir Horas</button>
                    </div>

                </div>

            </div>



            <!-- DIV para opção de projetos administrativos-->
           <div id="div_adm" style="display: none;">
               <div class="form-group">
                   <label for="input_datepicker_adm">Data</label>
                   <input type="text" id="input_datepicker_adm" value="<?php echo date("d/m/Y");  ?>" class="form-control" placeholder="Escolha uma data">
               </div>
               <div class="form-group">
                   <label for="select_adm">Horas administrativas</label>
                   <select id="select_adm" class="form-control">
                       <option value="default" disabled selected>Selecione uma opção</option>
                       <?php
                           $query = "Select id,nome from horas_adm order by nome";
                       $consulta = mysqli_query($con,$query);
                       while ($resultado_consulta = mysqli_fetch_array($consulta)) {
                           echo '<option value='.$resultado_consulta['id'].'>'.$resultado_consulta['nome'].'</option>';
                       }
                       ?>

                   </select>
               </div>
                </br>
               <form class="form-inline">
                   <div class="form-group">
                       <label for="start_date">Início da atividade </label>
                       <input type="text" class="form-control" value="" id="inicio_atividade_adm" placeholder="Hora inicial da atividade" required>
                   </div>
                   <div class="form-group">
                       <label for="end_date">Fim</label>
                       <input type="text" class="form-control" value="" id="fim_atividade_adm" placeholder="Hora final da atividade" required>
                   </div>

               </form>
                </br>
               <div id="div_button_inserir_proj">
                   <button type="button" id="btn_inserir_adm" class="btn btn-lg btn-success">Inserir Horas</button>
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