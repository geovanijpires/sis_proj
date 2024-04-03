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
                <label for="select_ferias_atestados">Férias ou atestados?</label>
                <select id="select_ferias_atestados" class="form-control">
                    <option value="default" disabled selected>Selecione uma opção</option>
                    <?php
                        $query = "Select id,tipo from tipo_ferias_atestado order by id";
                        $consulta = mysqli_query($con,$query);
                        while ($resultado_consulta = mysqli_fetch_array($consulta)) {
                            echo '<option value='.$resultado_consulta['id'].'>'.$resultado_consulta['tipo'].'</option>';
                        }
                        ?>

                </select>
            </div>

            <!-- div para as ferias -->
           <div id="div_content_ferias" style="display: none">
           <div class="form-group">
                <label for="input_datepicker_ferias_inicio">Data Início</label>
                <input type="text" id="input_datepicker_ferias_inicio" value="<?php echo date("d/m/Y");  ?>" class="form-control" placeholder="Escolha uma data de início">
            </div>
           <div class="form-group">
               <label for="input_datepicker_ferias_fim">Data Fim</label>
               <input type="text" id="input_datepicker_ferias_fim" value="" class="form-control" placeholder="Escolha uma data final">
           </div>

               <div id="div_button_inserir_ferias">
                   <button type="button" id="btn_inserir_ferias" class="btn btn-lg btn-success">Inserir</button>
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