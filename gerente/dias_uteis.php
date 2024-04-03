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
                <label >Selecione os dias úteis de trabalho</label>
                <?php
                   $mes_atual = date("Y-m");
                   $data_input = '';
                   $query = mysqli_query($con,"SELECT dia FROM dias_uteis where dia like '$mes_atual%' order by dia")or die(mysqli_error());
                   while($result = mysqli_fetch_array($query)){
                       $result['dia'] = explode("-", $result['dia']);
                       list($ano,$mes,$dia) = $result['dia'];
                       $end_data_show = "$dia/$mes/$ano";

                       $data_input = $data_input.$end_data_show.', ';
                   }
                   
                ?>

                <input type="text" id="datepicker_dias_uteis" class="form-control" value="<?php echo $data_input; ?>" placeholder="Escolha os dias úteis">
            </div>

            <div class="form-group">
                <button type="button" id="btn_cadastrar_dias_uteis" class="btn btn-lg btn-success">Cadastrar dias úteis</button>
            </div>

            <div id="div_confirma_dias_uteis"></div>
            <div class="form-group">

                <?php
                $mes_atual = date("Y-m");
                $query_total_dias_uteis = mysqli_query($con,"select COUNT(dia) as total_dias_uteis FROM `dias_uteis` where dia like '$mes_atual%' ");
                $result_total_dias_uteis = mysqli_fetch_array($query_total_dias_uteis);
                echo '
                    <table class="table table-striped table-bordered" width="10%" >
                    <thead>
                        <tr><th>Dias úteis deste mês: ';
                             echo $result_total_dias_uteis['total_dias_uteis'];
                        echo '</th>
                        </tr>
                    </thead>
                    <tbody>
                    
                    ';

                $query = mysqli_query($con,"SELECT dia FROM dias_uteis where dia like '$mes_atual%' order by dia")or die(mysqli_error());
                while($result = mysqli_fetch_array($query)){

                        $result['dia'] = explode("-", $result['dia']);
                        list($ano,$mes,$dia) = $result['dia'];
                        $end_data_show = "$dia/$mes/$ano";

                    echo "<tr>";
                    echo "<td>".$end_data_show."</td>";
                    echo "</tr>";

                }

                echo '

                </tbody>
                </table>';
                ?>



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