<?php
include_once("../../php/conexao.php");
include_once("soma_horas.php");
include_once("soma_horas2.php");
include_once("multiplicar_horas.php");
include_once("dividir_horas.php");
include_once("diferenca_horas.php");
include_once("diferenca_ferias.php");
include_once("custo_horas.php");

mysqli_set_charset($con,'utf8');
ini_set('default_charset','UTF-8');

function data_manipulation($date, $op){
    if($op == 0){//convert db to normal
        $date_l = explode("-", $date);
        list($ano,$mes,$dia) = $date_l;
        return "$dia/$mes/$ano";        
        
    }else if($op == 1) {//convert normnal to db
        $date_l = explode("/", $date);
        list($dia,$mes,$ano) = $date_l;
        return "$ano-$mes-$dia";
    } 

}

switch ($_POST['acao']) {

    case "busca_subetapa":
        echo "<option value='default' disabled selected>Selecione a subetapa do projeto</option>";
        $select_etapa = $_POST['etapa'];
        $id_projeto = $_POST['id_projeto'];

        //logica das visitas para desabilitar subetapa
        if($select_etapa == 6) {

            $query_busca_visitas_projeto = mysqli_query($con,"select visitas from projeto where id = '$id_projeto'");
            $result_visitas = mysqli_fetch_array($query_busca_visitas_projeto);
            $visitas_projeto = $result_visitas['visitas'];

            $array_soma_horas_visitas = array('00:00');
            $query_soma_visitas = mysqli_query($con,"SELECT hora_total FROM folha_proj WHERE id_proj = '$id_projeto' AND id_etapa = '$select_etapa' AND id_subetapa = 19 ");
            while ($result_soma_visitas = mysqli_fetch_array($query_soma_visitas)) {
                $array_soma_horas_visitas . array_push($array_soma_horas_visitas, $result_soma_visitas['hora_total']);
            }
            $soma_horas_visitas = sum_the_time($array_soma_horas_visitas);

            $query = ("Select id,nome,id_etapa from sub_etapa where id_etapa = '$select_etapa'") or die(mysqli_error());
            $consulta = mysqli_query($con,$query);

            while ($array = mysqli_fetch_array($consulta)) {
                if($array['id'] == 19){
                    if($soma_horas_visitas < $visitas_projeto){
                        echo "<option value='" . $array['id'] . "'>" . $array['nome'] . " (definidas: ".$visitas_projeto.", utilizadas: ".$soma_horas_visitas.")". " </option>";
                    }else {
                        echo "<option value='" . $array['id'] . "' disabled >" . $array['nome'] . "(definidas:".$visitas_projeto.", utilizadas: ".$soma_horas_visitas.")". "</option>";
                    }
                }else {
                    echo "<option value='" . $array['id'] . "'>" . $array['nome'] . "</option>";
                }
            }


        }else {
            $query = ("Select id,nome,id_etapa from sub_etapa where id_etapa = '$select_etapa'") or die(mysqli_error());
            $consulta = mysqli_query($con,$query);

            while ($array = mysqli_fetch_array($consulta)) {
                echo "<option value='" . $array['id'] . "'>" . $array['nome'] . "</option>";
            }
        }
        break;

        case "inserir_folha_proj":

            session_start();
            $id_func = $_SESSION["id_func"];
    
            //pegar id_cargo
            $query_pega_cargo_insere = mysqli_query($con,"select id_cargo, valor_hora, salario from funcionario where id = '$id_func'")or die(mysqli_error());
            $result_cargo_insere = mysqli_fetch_array($query_pega_cargo_insere);
            $id_cargo_insere = $result_cargo_insere['id_cargo'];
            $valor_hora = $result_cargo_insere['valor_hora'];
            $salario = $result_cargo_insere['salario'];
    
                $data = $_POST['data'];
                $data = explode("/", $data);
                list($dia,$mes,$ano) = $data;
            $data_invertida = "$ano-$mes-$dia"; //inverter data pro banco
    
            //busca se esta de férias atestado dayoff 
            
            $query_busca_exists = mysqli_query($con,"SELECT data_inicio, data_fim,id_func FROM ferias_atestados where '$data_invertida' between data_inicio and data_fim and id_func = '$id_func' ");
            $result_busca_exists = mysqli_num_rows($query_busca_exists);
            if($result_busca_exists == 0) {
    
    
                    $projeto = $_POST['projeto'];
                    $etapa = $_POST['etapa'];
                    $subetapa = $_POST['subetapa'];
    
                    $inicio_atividade = $_POST['inicio_atividade'];
                    $fim_atividade = $_POST['fim_atividade'];
                    //calculo horas totais para banco
                    $hora_inicial = date_create_from_format('H:i', $inicio_atividade);
    
                    $hora_final = date_create_from_format('H:i', $fim_atividade);
    
                    $intervalo = $hora_inicial->diff($hora_final);
    
                    $hora_total = $intervalo->format('%H:%I');
    
                    $data_hoje = date("Y-m-d");
    
                    //verifica se existe conflito de horas
                    $exist_conflito = 0;
    
                    //testa horas de projetos
                    $query_valida_insercao = mysqli_query($con,"SELECT h_inicial, h_final FROM folha_proj WHERE id_func = '$id_func' and data = '$data_invertida'");
                    while ($result_valida_isercao = mysqli_fetch_array($query_valida_insercao)){
                        $h_inicial_banco = strtotime($result_valida_isercao['h_inicial']);
                        $h_final_banco = strtotime($result_valida_isercao['h_final']);
                        if($h_inicial_banco >= strtotime($inicio_atividade) && $h_final_banco <= strtotime($fim_atividade) || strtotime($inicio_atividade) >= $h_inicial_banco && strtotime($inicio_atividade) < $h_final_banco || strtotime($fim_atividade) > $h_inicial_banco && strtotime($fim_atividade) <= $h_final_banco){
                            $exist_conflito ++;
                        }
    
                    }
                    //testa horas administrativas
                    $query_valida_insercao = mysqli_query($con,"SELECT h_inicial, h_final FROM folha_adm WHERE id_func = '$id_func' and data = '$data_invertida'");
                    while ($result_valida_isercao = mysqli_fetch_array($query_valida_insercao)){
                        $h_inicial_banco = strtotime($result_valida_isercao['h_inicial']);
                        $h_final_banco = strtotime($result_valida_isercao['h_final']);
                        if($h_inicial_banco >= strtotime($inicio_atividade) && $h_final_banco <= strtotime($fim_atividade) || strtotime($inicio_atividade) >= $h_inicial_banco && strtotime($inicio_atividade) < $h_final_banco || strtotime($fim_atividade) > $h_inicial_banco && strtotime($fim_atividade) <= $h_final_banco){
                            $exist_conflito ++;
                        }
    
                    }
                    //testa horas despesas
                    $query_valida_insercao = mysqli_query($con,"SELECT h_inicial, h_final FROM folha_desp WHERE id_func = '$id_func' and data = '$data_invertida'");
                    while ($result_valida_isercao = mysqli_fetch_array($query_valida_insercao)){
                        $h_inicial_banco = strtotime($result_valida_isercao['h_inicial']);
                        $h_final_banco = strtotime($result_valida_isercao['h_final']);
                        if($h_inicial_banco >= strtotime($inicio_atividade) && $h_final_banco <= strtotime($fim_atividade) || strtotime($inicio_atividade) >= $h_inicial_banco && strtotime($inicio_atividade) < $h_final_banco || strtotime($fim_atividade) > $h_inicial_banco && strtotime($fim_atividade) <= $h_final_banco){
                            $exist_conflito ++;
                        }
    
                    }
    
                    if($exist_conflito == 0) {
    
                        //calcula taxa administrativa (hora_total * valor_hora)+(hora_total * valor_hora)*tx_adm
                        $query_busca_tx_adm_proj = mysqli_query($con,"select taxa_adm from projeto where id='$projeto'")or die(mysqli_error());
                        while($result_busca_tx_adm_proj = mysqli_fetch_array($query_busca_tx_adm_proj)){
                            $tx_adm_proj = $result_busca_tx_adm_proj['taxa_adm'];
                        }
    
                        $valor_tot = custo_horas($hora_total,$valor_hora);
    
                        $calc_tx_adm = $valor_tot + ($valor_tot*$tx_adm_proj);
    
                        //insere no banco
                        $query = mysqli_query($con,"insert into folha_proj (id_func,id_cargo, data, id_proj, id_etapa, id_subetapa, h_inicial, h_final, hora_total, valor_hora,val_tx_adm, salario, data_insercao) values ('$id_func','$id_cargo_insere','$data_invertida','$projeto','$etapa','$subetapa','$inicio_atividade','$fim_atividade','$hora_total','$valor_hora','$calc_tx_adm','$salario', '$data_hoje')") or die(mysqli_error());
                        if ($query) {
    
                            echo "0"; // retorna sucesso
                        } else {
                            echo "1";
                        }
                    }else {
                        echo "2";
                    }
            }else {
                echo "3";
    
            }        
            break;

    case "inserir_folha_desp":

        session_start();
        $id_func = $_SESSION["id_func"];

        //pegar id_cargo
        $query_pega_cargo_insere = mysqli_query($con,"select id_cargo, valor_hora, salario from funcionario where id = '$id_func'")or die(mysqli_error());
        $result_cargo_insere = mysqli_fetch_array($query_pega_cargo_insere);
        $id_cargo_insere = $result_cargo_insere['id_cargo'];
        $valor_hora = $result_cargo_insere['valor_hora'];
        $salario = $result_cargo_insere['salario'];


        $data = $_POST['data'];
        $data = explode("/", $data);
        list($dia,$mes,$ano) = $data;
        $data_invertida = "$ano-$mes-$dia"; //inverter data pro banco

        $projeto = $_POST['projeto'];
        $etapa = $_POST['etapa'];
        $subetapa = $_POST['subetapa'];
        $inicio_atividade = $_POST['inicio_atividade'];
        $fim_atividade = $_POST['fim_atividade'];
        //calculo horas totais para banco
        $hora_inicial = date_create_from_format('H:i', $inicio_atividade);

        $hora_final = date_create_from_format('H:i', $fim_atividade);

        $intervalo = $hora_inicial->diff($hora_final);

        $hora_total = $intervalo->format('%H:%I');

        $data_hoje = date("Y-m-d");

        //verifica se existe conflito de horas
        $exist_conflito = 0;

        //testa horas de projetos
        $query_valida_insercao = mysqli_query($con,"SELECT h_inicial, h_final FROM folha_proj WHERE id_func = '$id_func' and data = '$data_invertida'");
        while ($result_valida_isercao = mysqli_fetch_array($query_valida_insercao)){
            $h_inicial_banco = strtotime($result_valida_isercao['h_inicial']);
            $h_final_banco = strtotime($result_valida_isercao['h_final']);
            if($h_inicial_banco >= strtotime($inicio_atividade) && $h_final_banco <= strtotime($fim_atividade) || strtotime($inicio_atividade) >= $h_inicial_banco && strtotime($inicio_atividade) < $h_final_banco || strtotime($fim_atividade) > $h_inicial_banco && strtotime($fim_atividade) <= $h_final_banco){
                $exist_conflito ++;
            }

        }
        //testa horas administrativas
        $query_valida_insercao = mysqli_query($con,"SELECT h_inicial, h_final FROM folha_adm WHERE id_func = '$id_func' and data = '$data_invertida'");
        while ($result_valida_isercao = mysqli_fetch_array($query_valida_insercao)){
            $h_inicial_banco = strtotime($result_valida_isercao['h_inicial']);
            $h_final_banco = strtotime($result_valida_isercao['h_final']);
            if($h_inicial_banco >= strtotime($inicio_atividade) && $h_final_banco <= strtotime($fim_atividade) || strtotime($inicio_atividade) >= $h_inicial_banco && strtotime($inicio_atividade) < $h_final_banco || strtotime($fim_atividade) > $h_inicial_banco && strtotime($fim_atividade) <= $h_final_banco){
                $exist_conflito ++;
            }

        }

        //testa horas despesas
        $query_valida_insercao = mysqli_query($con,"SELECT h_inicial, h_final FROM folha_desp WHERE id_func = '$id_func' and data = '$data_invertida'");
        while ($result_valida_isercao = mysqli_fetch_array($query_valida_insercao)){
            $h_inicial_banco = strtotime($result_valida_isercao['h_inicial']);
            $h_final_banco = strtotime($result_valida_isercao['h_final']);
            if($h_inicial_banco >= strtotime($inicio_atividade) && $h_final_banco <= strtotime($fim_atividade) || strtotime($inicio_atividade) >= $h_inicial_banco && strtotime($inicio_atividade) < $h_final_banco || strtotime($fim_atividade) > $h_inicial_banco && strtotime($fim_atividade) <= $h_final_banco){
                $exist_conflito ++;
            }

        }

        if($exist_conflito == 0){


            $valor_tot = custo_horas($hora_total,$valor_hora);


            //insere no banco
            $query = mysqli_query($con,"insert into folha_desp (id_func, data, id_proj, id_etapa, id_subetapa, h_inicial, h_final, hora_total, valor_hora, salario) values ('$id_func','$data_invertida','$projeto','$etapa','$subetapa','$inicio_atividade','$fim_atividade','$hora_total','$valor_hora','$salario')") or die(mysqli_error());
            if ($query) {
                echo "0";
            } else {
                echo "1";
            }
        }else {
            echo "2";
        }



        break;



    case "inserir_folha_adm":
        session_start();
        $id_func = $_SESSION["id_func"];
        $data = $_POST['data'];
        $data = explode("/", $data);
        list($dia,$mes,$ano) = $data;
        $data_invertida = "$ano-$mes-$dia"; //inverter data pro banco
        $inicio_atividade = $_POST['inicio_atividade_adm'];
        $fim_atividade = $_POST['fim_atividade_adm'];
        //calculo horas totais para banco
        $hora_inicial = date_create_from_format('H:i', $inicio_atividade);

        $hora_final = date_create_from_format('H:i', $fim_atividade);

        $intervalo = $hora_inicial->diff($hora_final);

        $hora_total = $intervalo->format('%H:%I');
        $adm = $_POST['adm'];
        $data_hoje = date("Y-m-d");

        //verifica se existe conflito de horas
        $exist_conflito = 0;

        //testa horas de projetos
        $query_valida_insercao = mysqli_query($con,"SELECT h_inicial, h_final FROM folha_proj WHERE id_func = '$id_func' and data = '$data_invertida'");
        while ($result_valida_isercao = mysqli_fetch_array($query_valida_insercao)){
            $h_inicial_banco = strtotime($result_valida_isercao['h_inicial']);
            $h_final_banco = strtotime($result_valida_isercao['h_final']);
            if($h_inicial_banco >= strtotime($inicio_atividade) && $h_final_banco <= strtotime($fim_atividade) || strtotime($inicio_atividade) >= $h_inicial_banco && strtotime($inicio_atividade) < $h_final_banco || strtotime($fim_atividade) > $h_inicial_banco && strtotime($fim_atividade) <= $h_final_banco){
                $exist_conflito ++;
            }

        }
        //testa horas administrativas
        $query_valida_insercao = mysqli_query($con,"SELECT h_inicial, h_final FROM folha_adm WHERE id_func = '$id_func' and data = '$data_invertida'");
        while ($result_valida_isercao = mysqli_fetch_array($query_valida_insercao)){
            $h_inicial_banco = strtotime($result_valida_isercao['h_inicial']);
            $h_final_banco = strtotime($result_valida_isercao['h_final']);
            if($h_inicial_banco >= strtotime($inicio_atividade) && $h_final_banco <= strtotime($fim_atividade) || strtotime($inicio_atividade) >= $h_inicial_banco && strtotime($inicio_atividade) < $h_final_banco || strtotime($fim_atividade) > $h_inicial_banco && strtotime($fim_atividade) <= $h_final_banco){
                $exist_conflito ++;
            }

        }


        if($exist_conflito == 0){
            //insere no banco
            $query = mysqli_query($con,"insert into folha_adm (id_func, data, id_hora_adm, h_inicial, h_final, hora_total, data_insercao) values ('$id_func','$data_invertida','$adm','$inicio_atividade','$fim_atividade','$hora_total','$data_hoje')") or die(mysqli_error());
            if ($query) {
                echo "0";
            } else {
                echo "1";
            }
        }else {
            echo "2";
        }

    break;
    // relatorio projeto toda vida
    case "rel_all_proj":

        $id_projeto = $_POST['id_projeto'];
        //soma total de horas lançadas no projeto
        $query_busca_info_proj = mysqli_query($con,"select projeto.cod_projeto, projeto.nome, projeto.comodos, projeto.metragem, projeto.valor_proj,projeto.taxa_adm, tipo_proj.tipo FROM projeto inner join tipo_proj on projeto.id_tipo = tipo_proj.id where projeto.id = '$id_projeto' ");

        while($result = mysqli_fetch_array($query_busca_info_proj)) {
            $nome_proj = $result['cod_projeto'].'-'.$result['nome'];
            $comodos = $result['comodos'];
            $metragem = $result['metragem'];
            $tipo = $result['tipo'];
            $valor = $result['valor_proj'];
            $tx_adm_atual = $result['taxa_adm'];
        }
        $array_soma_horas_tot = array('00:00');
        $tot_custo_geral = 0;
        $tot_tx_adm_all = 0;
        $query_soma_horas = mysqli_query($con,"select folha_proj.hora_total, folha_proj.valor_hora, folha_proj.val_tx_adm FROM folha_proj where folha_proj.id_proj = '$id_projeto'");
        while($result_soma_horas = mysqli_fetch_array($query_soma_horas)){
            $array_soma_horas_tot.array_push($array_soma_horas_tot,$result_soma_horas['hora_total']);
            $custo_total = custo_horas($result_soma_horas['hora_total'],$result_soma_horas['valor_hora']);
            $tot_custo_geral += $custo_total;
            //somatorio tx adm
            $tot_tx_adm_all += $result_soma_horas['val_tx_adm'];
        }

        $result_sum_time_horas = sum_the_time($array_soma_horas_tot);

        //Despesas
        $query_busca_despesas = mysqli_query($con,"SELECT sum(despesa) as despesa FROM despesas where id_proj = '$id_projeto'");
        while($result_busca_despesas = mysqli_fetch_array($query_busca_despesas)){
            $despesa = $result_busca_despesas['despesa'];
        }


        echo '<table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Projeto</th>
                      <th>Tipo</th>
                      <th>Cômodos</th>
                      <th>Média por cômodo</th>
                      <th>Metragem</th>
                      <th>Média por metro</th>
                      <th>Valor</th>
                      <th>Valor cômodo</th>
                      <th>Valor metro</th>
                      <th>Total de horas</th>
                      <th>Custo</th>
                      <th>Despesas</th>
                       <th>Taxa administrativa</th>
                      <th>Custo + Tx adm</th>
                      <th>Custo + Tx adm - despesa</th>
                    </tr>
                  </thead>
                  <tbody>';

            $row = divisao_horas($result_sum_time_horas,$comodos);

            $row_metragem = divisao_horas($result_sum_time_horas,$metragem);
            //calculo total por comodo e m2

            echo "<td>".$nome_proj."</td>";
            echo "<td>".$tipo."</td>";
            echo "<td>".$comodos."</td>";
            echo "<td>".$row."</td>";
            echo "<td>".$metragem."</td>";
            echo "<td>".$row_metragem."</td>";
            echo "<td>"."R$".$valor."</td>";
            if($comodos > 0){
            $valor_por_comodo = ($valor / $comodos);
            }
            echo "<td>"."R$".number_format($valor_por_comodo, 2, '.', '')."</td>";
            if($metragem > 0){
            $valor_por_metro = ($valor / $metragem);
            }
            echo "<td>"."R$".number_format($valor_por_metro, 2, '.', '')."</td>";
            echo "<td>".$result_sum_time_horas."</td>";
            echo "<td>"."R$".number_format($tot_custo_geral, 2, '.', '')."</td>";
            echo "<td>"."R$".number_format($despesa, 2, '.', '')."</td>";


            //verificar se existe historico de taxa adm no periodo
            $query_busca_historico_tx_adm = mysqli_query($con,"select * from last_tx_adm where id_projeto='$id_projeto'")or die(mysqli_error());
            $string_tx_adm_info = '';
            if(mysqli_num_rows($query_busca_historico_tx_adm) != 0) {

                while ($result_busca_historico_tx_adm = mysqli_fetch_array($query_busca_historico_tx_adm)) {
                    $tx_adm_history = $result_busca_historico_tx_adm['tx_adm_antigo'];
                    $data_ate_history = $result_busca_historico_tx_adm['data_ate'];
                    //converte data
                    $data_ate_history = explode("-", $data_ate_history);
                    list($ano, $mes, $dia) = $data_ate_history;
                    $data_ate_history = "$dia/$mes/$ano";

                    $string_tx_adm_info = $string_tx_adm_info + $tx_adm_history . " até " . $data_ate_history;

                }

                echo "<td>" . $string_tx_adm_info . "</br> Atual: " . $tx_adm_atual . "</td>";
            }else{
                echo "<td>". $tx_adm_atual . "</td>";
            }

            //calculo das tx adm no folha_proj
            echo "<td>"."R$".number_format($tot_tx_adm_all, 2, '.', '')."</td>";

            //custo + tx - despesa
            $cust_tx_desp = $tot_tx_adm_all - $despesa;
            echo "<td>"."R$".number_format($cust_tx_desp, 2, '.', '')."</td>";

        echo '</tbody>
                </table>';

        //visitas

        echo '<table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Visitas Inclusas</th>
                      <th>Visitas Pagas</th>
                      <th>Visitas não pagas</th>
                    </tr>
                  </thead>
                  <tbody>';

        $query_busca_visitas_projeto = mysqli_query($con,"select visitas from projeto where id = '$id_projeto'");
        $result_visitas = mysqli_fetch_array($query_busca_visitas_projeto);
        $visitas_projeto = $result_visitas['visitas'];//visitas inclusas do projeto

        //visitas inclusas
        $array_soma_horas_visitas = array('00:00');
        $query_soma_visitas = mysqli_query($con,"SELECT hora_total FROM folha_proj WHERE id_proj = '$id_projeto' AND id_etapa = 6 AND id_subetapa = 19 ");
        while ($result_soma_visitas = mysqli_fetch_array($query_soma_visitas)) {
            $array_soma_horas_visitas . array_push($array_soma_horas_visitas, $result_soma_visitas['hora_total']);
        }
        $soma_horas_visitas = sum_the_time($array_soma_horas_visitas); // visitas inclusas utilizadas

        //visitas pagas
        $array_soma_horas_visitas_pagas = array('00:00');
        $query_soma_visitas_pagas = mysqli_query($con,"SELECT hora_total FROM folha_proj WHERE id_proj = '$id_projeto' AND id_etapa = 6 AND id_subetapa = 20 ");
        while ($result_soma_visitas_pagas = mysqli_fetch_array($query_soma_visitas_pagas)) {
            $array_soma_horas_visitas_pagas . array_push($array_soma_horas_visitas_pagas, $result_soma_visitas_pagas['hora_total']);
        }
        $soma_horas_visitas_pagas = sum_the_time($array_soma_horas_visitas_pagas); // visitas pagas

        //visitas npagas
        $array_soma_horas_visitas_npagas = array('00:00');
        $query_soma_visitas_npagas = mysqli_query($con,"SELECT hora_total FROM folha_proj WHERE id_proj = '$id_projeto' AND id_etapa = 6 AND id_subetapa = 21 ");
        while ($result_soma_visitas_npagas = mysqli_fetch_array($query_soma_visitas_npagas)) {
            $array_soma_horas_visitas_npagas . array_push($array_soma_horas_visitas_npagas, $result_soma_visitas_npagas['hora_total']);
        }
        $soma_horas_visitas_npagas = sum_the_time($array_soma_horas_visitas_npagas); // visitas npagas


        echo "<tr>";
        echo "<td>" ."Definidas: ". $visitas_projeto ."  Utilizadas: ". $soma_horas_visitas. "</td>";
        echo "<td>" ."Utilizadas: ". $soma_horas_visitas_pagas. "</td>";
        echo "<td>" ."Utilizadas: ". $soma_horas_visitas_npagas. "</td>";
        echo "</tr>";


        echo '</tbody>
                </table>';

        //fecha visitas

        //despesas
        echo '<table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Despesa</th>
                      <th>Data</th>
                      <th>Inserido por</th>
                    </tr>
                  </thead>
                  <tbody>';


        $query_busca_info_despesas = mysqli_query($con,"SELECT despesas.despesa, despesas.data, funcionario.nome FROM despesas inner join funcionario on despesas.id_func = funcionario.id where despesas.id_proj = '$id_projeto' order by funcionario.nome");
        while($result_busca_info_despesas = mysqli_fetch_array($query_busca_info_despesas)){
            $despesa_info = $result_busca_info_despesas['despesa'];
            $data_info = $result_busca_info_despesas['data'];
            $data_info_ex = explode("-", $data_info);
            list($ano,$mes,$dia) = $data_info_ex;
            $data_info_invertida = "$dia/$mes/$ano"; //inverter data pro banco
            $funcionario_info = $result_busca_info_despesas['nome'];

            echo "<tr>";
            echo "<td>".$despesa_info."</td>";
            echo "<td>".$data_info_invertida."</td>";
            echo "<td>".$funcionario_info."</td>";
            echo "</tr>";
        }



        echo '</tbody>
                </table>';



        //fecha despesas


        //busca total etapas


        echo '<table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Etapas</th>
                      <th>Média por cômodo</th>
                      <th>Total de horas</th>
                      <th>Custo</th>
                      <th>Custo + Taxa administrativa</th>
                      
                    </tr>
                  </thead>
                  <tbody>';

            $query_busca_etapas = "select id,nome from etapa order by id";
            $exec_query_busca_etaps = mysqli_query($con,$query_busca_etapas);

            while ($result_etapas = mysqli_fetch_array($exec_query_busca_etaps)){

                $array_soma_horas_etapa = array('00:00');
                $tot_custo = 0;
                $tot_tx_adm_all = 0;
                $query_soma_etapas = mysqli_query($con,"select folha_proj.hora_total, folha_proj.valor_hora, folha_proj.val_tx_adm  FROM folha_proj where folha_proj.id_etapa = ".$result_etapas['id']." and folha_proj.id_proj = '$id_projeto' ");
                 while($result_soma_etapas = mysqli_fetch_array($query_soma_etapas)){
                     $array_soma_horas_etapa.array_push($array_soma_horas_etapa,$result_soma_etapas['hora_total']);
                     $val_func = custo_horas($result_soma_etapas['hora_total'],$result_soma_etapas['valor_hora']); //custo sao horas totais x valor hora
                     $tot_custo += $val_func;

                     //somatorio tx adm
                     $tot_tx_adm_all += $result_soma_etapas['val_tx_adm'];
                 }

                $result_sum_time_etapas = sum_the_time($array_soma_horas_etapa);

                if($result_sum_time_etapas != '00:00:00'){
                    echo "<tr>";
                    echo "<td>" . $result_etapas['nome'] . "</td>"; //etapa
                    $row = divisao_horas($result_sum_time_etapas, $comodos);
                    echo "<td>" .$row. "</td>"; //media por comodos
                    echo "<td>" .$result_sum_time_etapas."</td>"; //total de horas
                    echo "<td>"."R$".number_format($tot_custo, 2, '.', '')."</td>"; //custo

                    //calculo das tx adm no folha_proj
                    echo "<td>"."R$".number_format($tot_tx_adm_all, 2, '.', '')."</td>";

                    echo "</tr>";


                    //subetapas
                    $query_busca_subetapa_geral = ("select id, nome,id_etapa from sub_etapa where id_etapa = ".$result_etapas['id']."")or die(mysqli_error());
                    $exec_busca_subetapa_geral = mysqli_query($con,$query_busca_subetapa_geral);

                    while($result_busca_subetapa_geral = mysqli_fetch_array($exec_busca_subetapa_geral)){

                        $array_soma_horas_subetapa = array('00:00');
                        $tot_custo_sub = 0;
                        $tot_tx_adm_periodo_all_sub = 0;
                        $query_soma_subetapa_geral = mysqli_query($con,"select folha_proj.hora_total, folha_proj.valor_hora, folha_proj.val_tx_adm  FROM folha_proj where folha_proj.id_etapa = ".$result_etapas['id']." and folha_proj.id_proj = '$id_projeto' and folha_proj.id_subetapa = ".$result_busca_subetapa_geral['id']." ")or die(mysqli_error());
                        while($result_soma_subetapas = mysqli_fetch_array($query_soma_subetapa_geral)){
                            $array_soma_horas_subetapa.array_push($array_soma_horas_subetapa,$result_soma_subetapas['hora_total']);
                            $val_func_sub = custo_horas($result_soma_subetapas['hora_total'],$result_soma_subetapas['valor_hora']);
                            $tot_custo_sub += $val_func_sub;

                            //somatorio tx adm
                            $tot_tx_adm_periodo_all_sub += $result_soma_subetapas['val_tx_adm'];

                        }
                        $result_sum_time_subetapas = sum_the_time($array_soma_horas_subetapa);
                        if($result_sum_time_subetapas != '00:00:00'){

                            $row = divisao_horas($result_sum_time_subetapas, $comodos);
                            echo "<tr>";
                            echo "<td class='text-right'>".$result_busca_subetapa_geral['nome']."</td>";
                            echo "<td>".$row."</td>";
                            echo "<td>".$result_sum_time_subetapas."</td>";
                            echo "<td>"."R$".number_format($tot_custo_sub, 2, '.', '')."</td>";
                            echo "<td>"."R$".number_format($tot_tx_adm_periodo_all_sub, 2, '.', '')."</td>";
                            echo "</tr>";

                        }

                    }

                }


                    //fecha soma subetapa realtorio geral
            }

        echo '</tbody>
                </table>';

        $query_busca_users = "select funcionario.id,funcionario.nome,cargo.cargo,funcionario.valor_hora from funcionario inner join cargo on funcionario.id_cargo = cargo.id order by nome";
        echo "<b>Funcionários</b>";
        echo "</br>";
        $exec_query_busca_users = mysqli_query($con,$query_busca_users);
            while($result_busca_users = mysqli_fetch_array($exec_query_busca_users)){

                  $id_funcionario = $result_busca_users['id'];
                $array_soma_horas_user = array('00:00');
				$tot_custo_by_user = 0;
                $tot_tx_adm_all_user = 0;
                $query_total_users = "select  hora_total, valor_hora, val_tx_adm  FROM folha_proj where id_proj='$id_projeto' and id_func = '$id_funcionario'";
                $exec_query_total_users = mysqli_query($con,$query_total_users);
                    while($result_soma_user = mysqli_fetch_array($exec_query_total_users)){
                        $array_soma_horas_user.array_push($array_soma_horas_user,$result_soma_user['hora_total']);
						 $val_by_user = custo_horas($result_soma_user['hora_total'],$result_soma_user['valor_hora']);
						 $tot_custo_by_user += $val_by_user;

                        //somatorio tx adm
                        $tot_tx_adm_all_user += $result_soma_user['val_tx_adm'];
					}

                    $result_sum_time_user = sum_the_time($array_soma_horas_user);
                    if($result_sum_time_user != '00:00:00') {

                        echo '<div class="panel panel-default">
                                             <div class="panel-body">';

                        //table cabeçalho usuário


                        echo '<table class="table table-bordered table-striped">
                                          <thead>
                                            <tr>
                                              <th>Nome</th>
                                              <th>Cargo</th>
                                              <th>Valor hora</th>
											   <th>Horas totais</th>
											  <th>Custo</th>
											  <th>Custo + Taxa administrativa</th>
                                            </tr>
                                          </thead>
                                          <tbody>';

                        echo "<tr>";
                        echo "<td>" . $result_busca_users['nome'] . "</td>";
                        echo "<td>" . $result_busca_users['cargo'] . "</td>";

                        //verifica se existe valor hora e salary antigo tabela last_salary e mostra
                        $query_busca_salary_antigo = mysqli_query($con,"select * from last_salary where id_login = '$id_funcionario'")or die(mysqli_error());
                        $result_count_salary_antigo = mysqli_num_rows($query_busca_salary_antigo);

                        if($result_count_salary_antigo == 0){
                            echo "<td>" . $result_busca_users['valor_hora'] . "</td>";

                        }else{
                            $string_vh_antigo = "";

                            while($result_salary_antigo = mysqli_fetch_array($query_busca_salary_antigo)){
                                $data_ate_vh_antigo = $result_salary_antigo['data_ate'];
                                $data_ate_vh_antigo = explode("-", $data_ate_vh_antigo);
                                list($ano,$mes,$dia) = $data_ate_vh_antigo;
                                $data_ate_vh_antigo = "$dia/$mes/$ano";


                                $string_vh_antigo = $string_vh_antigo .' '.$result_salary_antigo['vh_antigo']." até ".$data_ate_vh_antigo."";

                            }

                            echo "<td>" . $string_vh_antigo ."</br> Atual ".$result_busca_users['valor_hora']. "</td>";

                        }



                        echo "<td>" . $result_sum_time_user . "</td>";
                        echo "<td>"."R$". number_format($tot_custo_by_user, 2, '.', '') . "</td>";
                        echo "<td>"."R$". number_format($tot_tx_adm_all_user, 2, '.', '') . "</td>";
                        echo "</tr>";


                        echo '</tbody>
                     </table>';


                        //fecha table cabeçalho usuário

                        echo '<table class="table table-bordered table-striped">
                                          <thead>
                                            <tr>
                                              <th>Etapas</th>
                                              <th>Média por cômodo</th>
                                              <th>Total de horas</th>
                                              <th>Custo</th>
                                            </tr>
                                          </thead>
                                          <tbody>';

                        //total etapa
                        $query_busca_etapas_user = "select id, nome from etapa order by id";
                        $exec_query_busca_etaps_user = mysqli_query($con,$query_busca_etapas_user);
                        while ($result_etapas_user = mysqli_fetch_array($exec_query_busca_etaps_user)){

                            $array_soma_horas_user_etapa = array('00:00');
							$tot_custo_by_user_etapa = 0;
                            $query_soma_user_etapa = mysqli_query($con,"select hora_total, valor_hora FROM folha_proj where folha_proj.id_etapa = ".$result_etapas_user['id']." and folha_proj.id_proj = '$id_projeto' and folha_proj.id_func = ".$result_busca_users['id']."  ");
                            while($result_soma_user_etapa = mysqli_fetch_array($query_soma_user_etapa)){
                                $array_soma_horas_user_etapa.array_push($array_soma_horas_user_etapa,$result_soma_user_etapa['hora_total']);
								$val_by_user_etapa = custo_horas($result_soma_user_etapa['hora_total'],$result_soma_user_etapa['valor_hora']);
								$tot_custo_by_user_etapa += $val_by_user_etapa;
							}

                            $result_sum_time_user_etapas = sum_the_time($array_soma_horas_user_etapa);
                            if($result_sum_time_user_etapas != '00:00:00'){
                                echo "<tr>";
                                echo "<td>" . $result_etapas_user['nome'] . "</td>";
                                $row = divisao_horas($result_sum_time_user_etapas, $comodos);
                                echo "<td>" .$row. "</td>";
                                echo "<td>" .$result_sum_time_user_etapas."</td>";
                                echo "<td>" ."R$".number_format($tot_custo_by_user_etapa, 2, '.', '')."</td>";
                                echo "</tr>";



                                //subetapas
                                $query_busca_subetapa_geral_user = ("select id, nome,id_etapa from sub_etapa where id_etapa = ".$result_etapas_user['id']."")or die(mysqli_error());
                                $exec_busca_subetapa_geral_user = mysqli_query($con,$query_busca_subetapa_geral_user);

                                while($result_busca_subetapa_geral_user = mysqli_fetch_array($exec_busca_subetapa_geral_user)){

                                    $array_soma_horas_subetapa_user = array('00:00');
									$tot_custo_by_user_subetapa = 0;
                                    $query_soma_subetapa_geral_user = mysqli_query($con,"select hora_total, valor_hora FROM folha_proj where folha_proj.id_etapa = ".$result_etapas_user['id']." and folha_proj.id_proj = '$id_projeto' and folha_proj.id_func = ".$id_funcionario." and folha_proj.id_subetapa = ".$result_busca_subetapa_geral_user['id']." ")or die(mysqli_error());
                                    while($result_soma_subetapas_user = mysqli_fetch_array($query_soma_subetapa_geral_user)){
                                        $array_soma_horas_subetapa_user.array_push($array_soma_horas_subetapa_user,$result_soma_subetapas_user['hora_total']);
										$val_by_user_subetapa = custo_horas($result_soma_subetapas_user['hora_total'],$result_soma_subetapas_user['valor_hora']);
										$tot_custo_by_user_subetapa += $val_by_user_subetapa;
									}
                                    $result_sum_time_subetapas_user = sum_the_time($array_soma_horas_subetapa_user);
                                    if($result_sum_time_subetapas_user != '00:00:00'){

                                        $row = divisao_horas($result_sum_time_subetapas_user, $comodos);
                                        echo "<tr>";
                                        echo "<td class='text-right'>".$result_busca_subetapa_geral_user['nome']."</td>";
                                        echo "<td>".$row."</td>";
                                        echo "<td>".$result_sum_time_subetapas_user."</td>";
                                        echo "<td>" ."R$".number_format($tot_custo_by_user_subetapa, 2, '.', '')."</td>";
                                        echo "</tr>";

                                    }

                                }


                        }



                    }

                        echo '</tbody>
                                    </table>';
                        echo "</div></div>";

            }

            }


    break;

    //relatorio projeto por intervalo
    case "rel_date_proj":
        $id_projeto = $_POST['id_projeto'];
        $start_date_p = $_POST['start_date_p'];
            $start_date_p = explode("/", $start_date_p);
            list($dia,$mes,$ano) = $start_date_p;
            $start_date_p = "$ano-$mes-$dia"; //inverter data pro banco
        $end_date_p = $_POST['end_date_p'];
            $end_date_p = explode("/", $end_date_p);
            list($dia,$mes,$ano) = $end_date_p;
            $end_date_p = "$ano-$mes-$dia"; //inverter data pro banco


        $query_busca_info_proj = mysqli_query($con,"select projeto.cod_projeto, projeto.nome, projeto.comodos, projeto.metragem, projeto.valor_proj,projeto.taxa_adm , tipo_proj.tipo FROM projeto inner join tipo_proj on projeto.id_tipo = tipo_proj.id where projeto.id = '$id_projeto' ");

        while($result = mysqli_fetch_array($query_busca_info_proj)) {
            $nome_proj = $result['cod_projeto'].'-'.$result['nome'];
            $comodos = $result['comodos'];
            $metragem = $result['metragem'];
            $tipo = $result['tipo'];
            $valor = $result['valor_proj'];
            $tx_adm_atual = $result['taxa_adm'];
        }

        $array_soma_horas_tot = array('00:00');
        $query_soma_horas = mysqli_query($con,"select folha_proj.hora_total, folha_proj.valor_hora, folha_proj.val_tx_adm FROM folha_proj where folha_proj.id_proj = '$id_projeto' and folha_proj.data BETWEEN '$start_date_p' and '$end_date_p'");
        $tot_custo_geral = 0;
        $tot_tx_adm_periodo = 0;
		while($result_soma_horas = mysqli_fetch_array($query_soma_horas)){
            $array_soma_horas_tot.array_push($array_soma_horas_tot,$result_soma_horas['hora_total']);
			 $custo_total = custo_horas($result_soma_horas['hora_total'],$result_soma_horas['valor_hora']);
             $tot_custo_geral += $custo_total;
             //somatorio tx adm
             $tot_tx_adm_periodo += $result_soma_horas['val_tx_adm'];

        }
        $result_sum_time_horas = sum_the_time($array_soma_horas_tot);

        //Despesas
        $query_busca_despesas = mysqli_query($con,"SELECT sum(despesa) as despesa FROM despesas where id_proj = '$id_projeto' and data between '$start_date_p' and '$end_date_p'");
        while($result_busca_despesas = mysqli_fetch_array($query_busca_despesas)){
            $despesa = $result_busca_despesas['despesa'];
        }


        echo '<table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Projeto</th>
                      <th>Tipo</th>
                      <th>Cômodos</th>
                      <th>Média por cômodo</th>
                      <th>Metragem</th>
                      <th>Média por metro</th>
                      <th>Valor</th>
                      <th>Valor cômodo</th>
                      <th>Valor metro</th>
                      <th>Total de horas</th>
                      <th>Custo</th>
                      <th>Despesa</th>
                      <th>Tx adm</th>
                      <th>Custo + Tx adm</th>
                      <th>Custo + Tx adm - despesas</th>
                    </tr>
                  </thead>
                  <tbody>';

        $row = divisao_horas($result_sum_time_horas,$comodos);

        $row_metragem = divisao_horas($result_sum_time_horas,$metragem);
        //calculo total por comodo e m2

        echo "<td>".$nome_proj."</td>";
        echo "<td>".$tipo."</td>";
        echo "<td>".$comodos."</td>";
        echo "<td>".$row."</td>";
        echo "<td>".$metragem."</td>";
        echo "<td>".$row_metragem."</td>";
        echo "<td>"."R$".$valor."</td>";
        if($comodos > 0){
        $valor_por_comodo = ($valor / $comodos);
        }
        echo "<td>"."R$".number_format($valor_por_comodo, 2, '.', '')."</td>";
        if($metragem > 0){
        $valor_por_metro = ($valor / $metragem);
        }
        echo "<td>"."R$".number_format($valor_por_metro, 2, '.', '')."</td>";
        echo "<td>".$result_sum_time_horas."</td>";
		echo "<td>"."R$".number_format($tot_custo_geral, 2, '.', '')."</td>";
        echo "<td>"."R$".number_format($despesa, 2, '.', '')."</td>";

        //verificar se existe historico de taxa adm no periodo
        $query_busca_historico_tx_adm = mysqli_query($con,"select * from last_tx_adm where id_projeto='$id_projeto' and data_ate BETWEEN '$start_date_p' and '$end_date_p' ")or die(mysqli_error());
        $string_tx_adm_info = 0;
        if(mysqli_num_rows($query_busca_historico_tx_adm) != 0) {

            while ($result_busca_historico_tx_adm = mysqli_fetch_array($query_busca_historico_tx_adm)) {
                $tx_adm_history = $result_busca_historico_tx_adm['tx_adm_antigo'];
                $data_ate_history = $result_busca_historico_tx_adm['data_ate'];
                //converte data
                $data_ate_history = explode("-", $data_ate_history);
                list($ano, $mes, $dia) = $data_ate_history;
                $data_ate_history = "$dia/$mes/$ano";

                $string_tx_adm_info = $string_tx_adm_info + $tx_adm_history . " até " . $data_ate_history;

            }

            echo "<td>" . $string_tx_adm_info . "</br> Atual: " . $tx_adm_atual . "</td>";
        }else{
            echo "<td>". $tx_adm_atual . "</td>";
        }

        //calculo das tx adm no folha_proj
        echo "<td>"."R$".number_format($tot_tx_adm_periodo, 2, '.', '')."</td>";

        //custo + tx - despesa
        $cust_tx_desp = $tot_tx_adm_periodo - $despesa;
        echo "<td>"."R$".number_format($cust_tx_desp, 2, '.', '')."</td>";

        echo '</tbody>
                </table>';

        //visitas

        echo '<table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Visitas Inclusas</th>
                      <th>Visitas Pagas</th>
                      <th>Visitas não pagas</th>
                    </tr>
                  </thead>
                  <tbody>';

        $query_busca_visitas_projeto = mysqli_query($con,"select visitas from projeto where id = '$id_projeto'");
        $result_visitas = mysqli_fetch_array($query_busca_visitas_projeto);
        $visitas_projeto = $result_visitas['visitas'];//visitas inclusas do projeto

        //visitas inclusas
        $array_soma_horas_visitas = array('00:00');
        $query_soma_visitas = mysqli_query($con,"SELECT hora_total FROM folha_proj WHERE id_proj = '$id_projeto' AND id_etapa = 6 AND id_subetapa = 19 ");
        while ($result_soma_visitas = mysqli_fetch_array($query_soma_visitas)) {
            $array_soma_horas_visitas . array_push($array_soma_horas_visitas, $result_soma_visitas['hora_total']);
        }
        $soma_horas_visitas = sum_the_time($array_soma_horas_visitas); // visitas inclusas utilizadas

        //visitas pagas
        $array_soma_horas_visitas_pagas = array('00:00');
        $query_soma_visitas_pagas = mysqli_query($con,"SELECT hora_total FROM folha_proj WHERE id_proj = '$id_projeto' AND id_etapa = 6 AND id_subetapa = 20 ");
        while ($result_soma_visitas_pagas = mysqli_fetch_array($query_soma_visitas_pagas)) {
            $array_soma_horas_visitas_pagas . array_push($array_soma_horas_visitas_pagas, $result_soma_visitas_pagas['hora_total']);
        }
        $soma_horas_visitas_pagas = sum_the_time($array_soma_horas_visitas_pagas); // visitas pagas

        //visitas npagas
        $array_soma_horas_visitas_npagas = array('00:00');
        $query_soma_visitas_npagas = mysqli_query($con,"SELECT hora_total FROM folha_proj WHERE id_proj = '$id_projeto' AND id_etapa = 6 AND id_subetapa = 21 ");
        while ($result_soma_visitas_npagas = mysqli_fetch_array($query_soma_visitas_npagas)) {
            $array_soma_horas_visitas_npagas . array_push($array_soma_horas_visitas_npagas, $result_soma_visitas_npagas['hora_total']);
        }
        $soma_horas_visitas_npagas = sum_the_time($array_soma_horas_visitas_npagas); // visitas npagas


        echo "<tr>";
        echo "<td>" ."Definidas: ". $visitas_projeto ."  Utilizadas: ". $soma_horas_visitas. "</td>";
        echo "<td>" ."Utilizadas: ". $soma_horas_visitas_pagas. "</td>";
        echo "<td>" ."Utilizadas: ". $soma_horas_visitas_npagas. "</td>";
        echo "</tr>";


        echo '</tbody>
                </table>';

        //fecha visitas


        //despesas
        echo '<table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Despesa</th>
                      <th>Data</th>
                      <th>Inserido por</th>
                    </tr>
                  </thead>
                  <tbody>';


        $query_busca_info_despesas = mysqli_query($con,"SELECT despesas.despesa, despesas.data, funcionario.nome FROM despesas inner join funcionario on despesas.id_func = funcionario.id where despesas.id_proj = '$id_projeto' and despesas.data between '$start_date_p' and '$end_date_p' order by funcionario.nome");
        while($result_busca_info_despesas = mysqli_fetch_array($query_busca_info_despesas)){
            $despesa_info = $result_busca_info_despesas['despesa'];
            $data_info = $result_busca_info_despesas['data'];
            $data_info_ex = explode("-", $data_info);
            list($ano,$mes,$dia) = $data_info_ex;
            $data_info_invertida = "$dia/$mes/$ano"; //inverter data pro banco
            $funcionario_info = $result_busca_info_despesas['nome'];

            echo "<tr>";
            echo "<td>".$despesa_info."</td>";
            echo "<td>".$data_info_invertida."</td>";
            echo "<td>".$funcionario_info."</td>";
            echo "</tr>";
        }

        echo '</tbody>
                </table>';



        //fecha despesas



        echo '<table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Etapas</th>
                      <th>Média por cômodo</th>
                      <th>Total de horas</th>
					  <th>Custo</th>
					  <th>Custo + Taxa administrativa</th>
                    </tr>
                  </thead>
                  <tbody>';

        $query_busca_etapas = "select id,nome from etapa order by id";
        $exec_query_busca_etaps = mysqli_query($con,$query_busca_etapas);

        while ($result_etapas = mysqli_fetch_array($exec_query_busca_etaps)){
            $array_soma_horas_etapa = array('00:00');
			$tot_custo = 0;
			$tot_tx_adm_periodo = 0;
            $query_soma_etapas = mysqli_query($con,"select folha_proj.hora_total, folha_proj.valor_hora, folha_proj.val_tx_adm FROM folha_proj where folha_proj.id_etapa = ".$result_etapas['id']." and folha_proj.id_proj = '$id_projeto' and folha_proj.data BETWEEN '$start_date_p' and '$end_date_p'");
            while($result_soma_etapas = mysqli_fetch_array($query_soma_etapas)){
                $array_soma_horas_etapa.array_push($array_soma_horas_etapa,$result_soma_etapas['hora_total']);
				$val_func = custo_horas($result_soma_etapas['hora_total'],$result_soma_etapas['valor_hora']); //custo sao horas totais x valor hora
                $tot_custo += $val_func;

                //somatorio tx adm
                $tot_tx_adm_periodo += $result_soma_etapas['val_tx_adm'];

		   }
            $result_sum_time_etapas = sum_the_time($array_soma_horas_etapa);
            
			if($result_sum_time_etapas != '00:00:00'){
                echo "<tr>";
                echo "<td>" . $result_etapas['nome'] . "</td>";
                $row = divisao_horas($result_sum_time_etapas, $comodos);
                echo "<td>" .$row. "</td>";
                echo "<td>" .$result_sum_time_etapas."</td>";
				echo "<td>"."R$".number_format($tot_custo, 2, '.', '')."</td>"; //custo

                //calculo das tx adm no folha_proj
                echo "<td>"."R$".number_format($tot_tx_adm_periodo, 2, '.', '')."</td>";

                echo "</tr>";

                //subetapas
                $query_busca_subetapa_geral = ("select id, nome,id_etapa from sub_etapa where id_etapa = ".$result_etapas['id']."")or die(mysqli_error());
                $exec_busca_subetapa_geral = mysqli_query($con,$query_busca_subetapa_geral);

                while($result_busca_subetapa_geral = mysqli_fetch_array($exec_busca_subetapa_geral)){

                    $array_soma_horas_subetapa = array('00:00');
					$tot_custo_sub = 0;
					$tot_tx_adm_periodo_sub = 0;
                    $query_soma_subetapa_geral = mysqli_query($con,"select folha_proj.hora_total, folha_proj.valor_hora, folha_proj.val_tx_adm FROM folha_proj  where folha_proj.id_etapa = ".$result_etapas['id']." and folha_proj.id_proj = '$id_projeto' and folha_proj.id_subetapa = ".$result_busca_subetapa_geral['id']." and folha_proj.data BETWEEN '$start_date_p' and '$end_date_p' ")or die(mysqli_error());
                    while($result_soma_subetapas = mysqli_fetch_array($query_soma_subetapa_geral)){
                        $array_soma_horas_subetapa.array_push($array_soma_horas_subetapa,$result_soma_subetapas['hora_total']);
						 $val_func_sub = custo_horas($result_soma_subetapas['hora_total'],$result_soma_subetapas['valor_hora']);
                         $tot_custo_sub += $val_func_sub;
						 
						 //somatorio tx adm
						$tot_tx_adm_periodo_sub += $result_soma_subetapas['val_tx_adm'];
						 
					}
                    $result_sum_time_subetapas = sum_the_time($array_soma_horas_subetapa);
                    if($result_sum_time_subetapas != '00:00:00'){

                        $row = divisao_horas($result_sum_time_subetapas, $comodos);
                        echo "<tr>";
                        echo "<td class='text-right'>".$result_busca_subetapa_geral['nome']."</td>";
                        echo "<td>".$row."</td>";
                        echo "<td>".$result_sum_time_subetapas."</td>";
						echo "<td>"."R$".number_format($tot_custo_sub, 2, '.', '')."</td>";
                        echo "<td>"."R$".number_format($tot_tx_adm_periodo_sub, 2, '.', '')."</td>";
                        echo "</tr>";

                    }

                }

            }


            //fecha soma subetapa realtorio geral
        }

        echo '</tbody>
                </table>';

        //busca por user
        $query_busca_users = "select funcionario.id,funcionario.valor_hora,funcionario.nome,cargo.cargo from funcionario inner join cargo on funcionario.id_cargo = cargo.id order by nome";
        echo "<b>Funcionários</b>";
        echo "</br>";
        $exec_query_busca_users = mysqli_query($con,$query_busca_users);
        while($result_busca_users = mysqli_fetch_array($exec_query_busca_users)){

            $id_funcionario = $result_busca_users['id'];

            $array_soma_horas_user = array('00:00');
            $tot_custo_by_user = 0;
            $tot_tx_adm_periodo_user = 0;
            $query_total_users = "select  hora_total, valor_hora, val_tx_adm FROM folha_proj where id_proj='$id_projeto' and id_func = '$id_funcionario' and folha_proj.data BETWEEN '$start_date_p' and '$end_date_p'";
            $exec_query_total_users = mysqli_query($con,$query_total_users);
            while($result_soma_user = mysqli_fetch_array($exec_query_total_users)){
                $array_soma_horas_user.array_push($array_soma_horas_user,$result_soma_user['hora_total']);
                $val_by_user = custo_horas($result_soma_user['hora_total'],$result_soma_user['valor_hora']);
                $tot_custo_by_user += $val_by_user;

                //somatorio tx adm
                $tot_tx_adm_periodo_user += $result_soma_user['val_tx_adm'];

            }

            $result_sum_time_user = sum_the_time($array_soma_horas_user);
            if($result_sum_time_user != '00:00:00') {

                echo '<div class="panel panel-default">
                <div class="panel-body">';

                //table cabeçalho usuário


                echo '<table class="table table-bordered table-striped">
                                          <thead>
                                            <tr>
                                              <th>Nome</th>
                                              <th>Cargo</th>
                                              <th>Valor hora</th>
											   <th>Horas totais</th>
											  <th>Custo</th>
											  <th>Custo + Taxa administrativa</th>
                                            </tr>
                                          </thead>
                                          <tbody>';

                echo "<tr>";
                echo "<td>" . $result_busca_users['nome'] . "</td>";
                echo "<td>" . $result_busca_users['cargo'] . "</td>";

                //verifica se existe valor hora e salary antigo tabela last_salary e mostra
                $query_busca_salary_antigo = mysqli_query($con,"select * from last_salary where id_login = '$id_funcionario'")or die(mysqli_error());
                $result_count_salary_antigo = mysqli_num_rows($query_busca_salary_antigo);

                    if($result_count_salary_antigo == 0){
                        echo "<td>" . $result_busca_users['valor_hora'] . "</td>";

                    }else{
                        $string_vh_antigo = "";

                        while($result_salary_antigo = mysqli_fetch_array($query_busca_salary_antigo)){
                            $data_ate_vh_antigo = $result_salary_antigo['data_ate'];
                            $data_ate_vh_antigo = explode("-", $data_ate_vh_antigo);
                            list($ano,$mes,$dia) = $data_ate_vh_antigo;
                            $data_ate_vh_antigo = "$dia/$mes/$ano";


                            $string_vh_antigo = $string_vh_antigo .' '.$result_salary_antigo['vh_antigo']." até ".$data_ate_vh_antigo."";

                        }

                        echo "<td>" . $string_vh_antigo ."</br> Atual ".$result_busca_users['valor_hora']. "</td>";

                    }



                echo "<td>" . $result_sum_time_user . "</td>";
                echo "<td>"."R$". number_format($tot_custo_by_user, 2, '.', '') . "</td>";
                echo "<td>"."R$". number_format($tot_tx_adm_periodo_user, 2, '.', '') . "</td>";
                echo "</tr>";


                echo '</tbody>
                     </table>';


                //fecha table cabeçalho usuário

                echo '<table class="table table-bordered table-striped">
                                          <thead>
                                            <tr>
                                              <th>Etapas</th>
                                              <th>Média por cômodo</th>
                                              <th>Total de horas</th>
											  <th>Custo</th>
                                            </tr>
                                          </thead>
                                          <tbody>';

                //total etapa
                $query_busca_etapas_user = "select id, nome from etapa order by id";
                $exec_query_busca_etaps_user = mysqli_query($con,$query_busca_etapas_user);
                while ($result_etapas_user = mysqli_fetch_array($exec_query_busca_etaps_user)){

                    $array_soma_horas_user_etapa = array('00:00');
                    $tot_custo_by_user_etapa = 0;
                    $query_soma_user_etapa = mysqli_query($con,"select hora_total, valor_hora FROM folha_proj where folha_proj.id_etapa = ".$result_etapas_user['id']." and folha_proj.id_proj = '$id_projeto' and folha_proj.id_func = ".$result_busca_users['id']." and folha_proj.data BETWEEN '$start_date_p' and '$end_date_p' ");
                    while($result_soma_user_etapa = mysqli_fetch_array($query_soma_user_etapa)){
                        $array_soma_horas_user_etapa.array_push($array_soma_horas_user_etapa,$result_soma_user_etapa['hora_total']);
                        $val_by_user_etapa = custo_horas($result_soma_user_etapa['hora_total'],$result_soma_user_etapa['valor_hora']);
                        $tot_custo_by_user_etapa += $val_by_user_etapa;

                    }

                    $result_sum_time_user_etapas = sum_the_time($array_soma_horas_user_etapa);
                    if($result_sum_time_user_etapas != '00:00:00'){
                        echo "<tr>";
                        echo "<td>" . $result_etapas_user['nome'] . "</td>";
                        $row = divisao_horas($result_sum_time_user_etapas, $comodos);
                        echo "<td>" .$row. "</td>";
                        echo "<td>" .$result_sum_time_user_etapas."</td>";

                        echo "<td>" ."R$".number_format($tot_custo_by_user_etapa, 2, '.', '')."</td>";
                        echo "</tr>";



                        //subetapas
                        $query_busca_subetapa_geral_user = ("select id, nome,id_etapa from sub_etapa where id_etapa = ".$result_etapas_user['id']."")or die(mysqli_error());
                        $exec_busca_subetapa_geral_user = mysqli_query($con,$query_busca_subetapa_geral_user);

                        while($result_busca_subetapa_geral_user = mysqli_fetch_array($exec_busca_subetapa_geral_user)){

                            $array_soma_horas_subetapa_user = array('00:00');
                            $tot_custo_by_user_subetapa = 0;
                            $query_soma_subetapa_geral_user = mysqli_query($con,"select hora_total, valor_hora FROM folha_proj where folha_proj.id_etapa = ".$result_etapas_user['id']." and folha_proj.id_proj = '$id_projeto' and folha_proj.id_func = ".$id_funcionario." and folha_proj.id_subetapa = ".$result_busca_subetapa_geral_user['id']." and folha_proj.data BETWEEN '$start_date_p' and '$end_date_p'")or die(mysqli_error());
                            while($result_soma_subetapas_user = mysqli_fetch_array($query_soma_subetapa_geral_user)){
                                $array_soma_horas_subetapa_user.array_push($array_soma_horas_subetapa_user,$result_soma_subetapas_user['hora_total']);

                                $val_by_user_subetapa = custo_horas($result_soma_subetapas_user['hora_total'],$result_soma_subetapas_user['valor_hora']);
                                $tot_custo_by_user_subetapa += $val_by_user_subetapa;

                            }
                            $result_sum_time_subetapas_user = sum_the_time($array_soma_horas_subetapa_user);
                            if($result_sum_time_subetapas_user != '00:00:00'){

                                $row = divisao_horas($result_sum_time_subetapas_user, $comodos);
                                echo "<tr>";
                                echo "<td class='text-right'>".$result_busca_subetapa_geral_user['nome']."</td>";
                                echo "<td>".$row."</td>";
                                echo "<td>".$result_sum_time_subetapas_user."</td>";
								
                                echo "<td>" ."R$".number_format($tot_custo_by_user_subetapa, 2, '.', '')."</td>";
                                echo "</tr>";

                            }

                        }


                    }



                }

                echo '</tbody>
                                    </table>';
                echo "</div></div>";

            }

        }


        break;

//relatorio intevalo all administrativo

    case "rel_all_adm":

        $start_data_adm = $_POST['start_data_adm'];
        $start_data_adm = explode("/", $start_data_adm);
        list($dia,$mes,$ano) = $start_data_adm;
        $start_data_adm = "$ano-$mes-$dia"; //inverter data pro banco
        $end_data_adm = $_POST['end_data_adm'];
        $end_data_adm = explode("/", $end_data_adm);
        list($dia,$mes,$ano) = $end_data_adm;
        $end_data_adm = "$ano-$mes-$dia"; //inverter data pro banco


        echo '<table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Total de horas</th>
                    </tr>
                  </thead>
                  <tbody>';

        $array_soma_adm = array('00:00');
        $query_total = mysqli_query($con,"select hora_total from folha_adm where data BETWEEN '$start_data_adm' and '$end_data_adm' ");
        while($result_total = mysqli_fetch_array($query_total)){
            $array_soma_adm.array_push($array_soma_adm, $result_total['hora_total']);

        }
        $total_horas_adm = sum_the_time($array_soma_adm);


        echo "<td>".$total_horas_adm."</td>";

        echo '</tbody>
                </table>';


        echo '<table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Tipo</th>
                      <th>Total de horas</th>
                    </tr>
                  </thead>
                  <tbody>';

        $query_total_tipo = mysqli_query($con,"select id, nome from horas_adm ");
        while($result_total_tipo = mysqli_fetch_array($query_total_tipo)){
            $id_tipo = $result_total_tipo['id'];
            $nome_tipo = $result_total_tipo['nome'];

            $array_tipo_adm = array('00:00');
            $query_soma_tipo = mysqli_query($con,"select hora_total from folha_adm where data BETWEEN '$start_data_adm' and '$end_data_adm' and id_hora_adm = '$id_tipo' ");
            while($result_soma_tipo = mysqli_fetch_array($query_soma_tipo)){
                $array_tipo_adm.array_push($array_tipo_adm, $result_soma_tipo['hora_total']);

            }
            $total_tipo_adm = sum_the_time($array_tipo_adm);

            echo "<tr>";
            echo "<td>".$nome_tipo."</td>";
            echo "<td>".$total_tipo_adm."</td>";
            echo "</tr>";

        }



        echo '</tbody>
                </table>';


        //por user
        $query_busca_users = "select id,nome from funcionario order by nome";
        echo "<b>Funcionários</b>";
        echo "</br>";
        echo "</br>";
        $exec_query_busca_users = mysqli_query($con,$query_busca_users);
        while($result_busca_users = mysqli_fetch_array($exec_query_busca_users)) {

            $id_func = $result_busca_users['id'];
            echo $result_busca_users['nome'];
            echo "</br>";

            $array_soma_adm_user = array('00:00');
            $query_total_user = mysqli_query($con,"select hora_total from folha_adm where data BETWEEN '$start_data_adm' and '$end_data_adm' and id_func = '$id_func' ");
            while($result_total_user = mysqli_fetch_array($query_total_user)){
                $array_soma_adm_user.array_push($array_soma_adm_user, $result_total_user['hora_total']);

            }
            $total_horas_adm_user = sum_the_time($array_soma_adm_user);
            echo 'Total geral: '.$total_horas_adm_user;

            echo '<table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Tipo</th>
                      <th>Total de horas</th>
                    </tr>
                  </thead>
                  <tbody>';

            $query_total_tipo_user = mysqli_query($con,"select id, nome from horas_adm ");
            while($result_total_tipo_user = mysqli_fetch_array($query_total_tipo_user)){
                $id_tipo_user = $result_total_tipo_user['id'];
                $nome_tipo_user = $result_total_tipo_user['nome'];

                $array_tipo_adm_user = array('00:00');
                $query_soma_tipo_user = mysqli_query($con,"select hora_total from folha_adm where data BETWEEN '$start_data_adm' and '$end_data_adm' and id_hora_adm = '$id_tipo_user' and id_func = '$id_func' ");
                while($result_soma_tipo_user = mysqli_fetch_array($query_soma_tipo_user)){
                    $array_tipo_adm_user.array_push($array_tipo_adm_user, $result_soma_tipo_user['hora_total']);

                }
                $total_tipo_adm_user = sum_the_time($array_tipo_adm_user);

                echo "<tr>";
                echo "<td>".$nome_tipo_user."</td>";
                echo "<td>".$total_tipo_adm_user."</td>";
                echo "</tr>";

            }



            echo '</tbody>
                </table>';



        }

        break;



    case "rel_media":
        $id_tipo = $_POST['id_tipo'];

        $query = mysqli_query($con,"SELECT COUNT(projeto.id) AS quantidade FROM projeto where projeto.id_tipo = '$id_tipo'");
        $quant_tipo = mysqli_fetch_array($query);
        if ($quant_tipo['quantidade'] == 0){

            echo "Não existe projeto cadastrado para este tipo.";
        }else {
            echo '<table class="table table-bordered table-striped">
                                          <thead>
                                            <tr>
                                              <th>Quant projetos</th>
                                              <th>Total de horas</th>
                                              <th>Média total</th>
                                              <th>Total cômodos</th>
                                              <th>Média por cômodo</th>
                                            </tr>
                                          </thead>
                                          <tbody>';
           //busca comodos
           $query_busca_proj_com_registros = mysqli_query($con,"select id,comodos from projeto where id_tipo = '$id_tipo'");
           while($result_busca_proj_com_registro = mysqli_fetch_array($query_busca_proj_com_registros)){
               $id_projeto = $result_busca_proj_com_registro['id'];
               $quant_comodos = $result_busca_proj_com_registro['comodos'];

               $query = mysqli_query($con,"select hora_total FROM folha_proj where id_proj = '$id_projeto'");
               $result_row = mysqli_num_rows($query);
                if($result_row > 0){
                    $comodos += $quant_comodos;
                    $quant ++;
               }

           }





           $array_soma_horas = array('00:00');
           $query_soma = mysqli_query($con,"select hora_total FROM folha_proj inner join projeto on folha_proj.id_proj = projeto.id inner join tipo_proj on projeto.id_tipo = tipo_proj.id where tipo_proj.id = '$id_tipo'")or die(mysqli_error());

            while($result_soma_horas = mysqli_fetch_array($query_soma)){
                $array_soma_horas.array_push($array_soma_horas,$result_soma_horas['hora_total']);

            }
            $result_sum_time_horas = sum_the_time($array_soma_horas);

                if ($result_sum_time_horas == '00:00:00'){
                    echo "</br>";
                   echo ("Nenhum registro encontrado neste projeto.");
                }else {

                    echo "<tr>";
                    echo "<td>".$quant."</td>";
                    echo "</br>";

                    $total_h_lancadas = $result_sum_time_horas;//total horas

                    echo "<td>".$total_h_lancadas."</td>";

                    $result_media = divisao_horas($total_h_lancadas,$quant);

                    echo "<td>".$result_media."</td>";

                    echo "<td>".$comodos."</td>";
                    $result_media_comodos = divisao_horas($total_h_lancadas,$comodos);

                    echo "<td>".$result_media_comodos."</td>";

                 echo "</tr>";


                    //BUSCA totais gerais por cargo


                    echo '<table class="table table-bordered table-striped">
                                             <thead>
                                               <tr>
                                                 <th>Cargo</th>
                                                 <th>Total de horas</th>
                                                 <th>Média total</th>
                                                 <th>Média por cômodo</th>
                                               </tr>
                                             </thead>
                                             <tbody>';
                    $query_busca_cargo = mysqli_query($con,"select id,cargo from cargo")or die(mysqli_error());
                    while($result_busca_cargo = mysqli_fetch_array($query_busca_cargo)){
                        $id_cargo = $result_busca_cargo['id'];
                        $cargo = $result_busca_cargo['cargo'];

                        $array_soma_horas_cargo = array('00:00'); //array soma horas por cargo
                        $query_soma_cargo = mysqli_query($con,"select hora_total FROM folha_proj inner join projeto on folha_proj.id_proj = projeto.id inner join tipo_proj on projeto.id_tipo = tipo_proj.id  where tipo_proj.id = '$id_tipo' and folha_proj.id_cargo = '$id_cargo'")or die(mysqli_error());
                            while($result_query_soma_cargo = mysqli_fetch_array($query_soma_cargo)){
                                $array_soma_horas_cargo.array_push($array_soma_horas_cargo,$result_query_soma_cargo['hora_total']);
                            }

                        $result_sum_time_horas_cargo = sum_the_time($array_soma_horas_cargo);
                        if($result_sum_time_horas_cargo != '00:00:00') {
                            echo "<tr>";
                            echo "<td>".$cargo."</td>";
                            echo "<td>".$result_sum_time_horas_cargo."</td>";
                            $result_media_cargo = divisao_horas($result_sum_time_horas_cargo,$quant);
                            echo "<td>".$result_media_cargo."</td>";
                            $result_media_comodos_cargo = divisao_horas($result_sum_time_horas_cargo,$comodos);
                            echo "<td>".$result_media_comodos_cargo."</td>";
                            echo "</tr>";

                        }

                    }

                    echo '</tbody>
                        </table>';


                    //FECHA totais gerais por cargo


                        //inicia divisoes e subdivisoes



                       echo '<table class="table table-bordered table-striped">
                                             <thead>
                                               <tr>
                                                 <th>Etapa</th>
                                                 <th>Quant projetos</th>
                                                 <th>Total cômodos</th>
                                                 <th>Total de horas</th>
                                                 <th>Média total</th>
                                                 <th>Média por cômodo</th>
                                               </tr>
                                             </thead>
                                             <tbody>';



                    //media etapa
                       $query_busca_etapa = mysqli_query($con,"select id, nome from etapa order by id");

                           while($result_busca_etapa = mysqli_fetch_array($query_busca_etapa)){

                               $id_etapa = $result_busca_etapa['id']; //id etapa
                               $comodos_etapa = 0;
                               $quant_etapa  = 0;

                               //pega comodo e quantidade de prjeto com esta etapa
                               $query_busca_proj_com_registros = mysqli_query($con,"select id,comodos from projeto where id_tipo = '$id_tipo'");
                               while($result_busca_proj_com_registro = mysqli_fetch_array($query_busca_proj_com_registros)){
                                   $id_projeto = $result_busca_proj_com_registro['id'];
                                   $quant_comodos = $result_busca_proj_com_registro['comodos'];

                                   $query = mysqli_query($con,"select hora_total FROM folha_proj where id_proj = '$id_projeto' and id_etapa = '$id_etapa'");
                                   $result_row = mysqli_num_rows($query);
                                   if($result_row > 0){
                                       $comodos_etapa += $quant_comodos;
                                       $quant_etapa ++;
                                   }

                               }

                               $array_soma_horas_etapa = array('00:00');
                               $query_soma_etapa = mysqli_query($con,"select hora_total FROM folha_proj inner join projeto on folha_proj.id_proj = projeto.id inner join tipo_proj on projeto.id_tipo = tipo_proj.id where tipo_proj.id = '$id_tipo' and folha_proj.id_etapa = '$id_etapa'")or die(mysqli_error());

                               while($result_soma_horas_etapa = mysqli_fetch_array($query_soma_etapa)){
                                   $array_soma_horas_etapa.array_push($array_soma_horas_etapa,$result_soma_horas_etapa['hora_total']);

                               }
                               $result_sum_time_horas_etapa = sum_the_time($array_soma_horas_etapa);

                                   if($result_sum_time_horas_etapa != '00:00:00'){

                                       $total_h_etapa = $result_sum_time_horas_etapa;//total etapa

                                           echo "<tr>";
                                               echo "<td>".$result_busca_etapa['nome']."</td>";
                                               echo "<td>".$quant_etapa."</td>";
                                               echo "<td>".$comodos_etapa."</td>";
                                               echo "<td>".$total_h_etapa."</td>";

                                          $row = divisao_horas($total_h_etapa,$quant_etapa);

                                          echo "<td>".$row."</td>";

                                          //media comodo
                                          $row2 = divisao_horas($total_h_etapa,$comodos_etapa);

                                          echo "<td>".$row2."</td>";

                                          //busca subetapa
                                          $query_busca_sub = mysqli_query($con,"select id, nome from sub_etapa where id_etapa = '$id_etapa'");
                                          while($result_busca_sub = mysqli_fetch_array($query_busca_sub)){
                                              $id_subetapa = $result_busca_sub['id'];//id subetapa

                                              $comodos_subetapa = 0;
                                              $quant_subetapa  = 0;

                                              //pega comodo e quantidade de prjeto com esta subetapa
                                              $query_busca_proj_com_registros = mysqli_query($con,"select id,comodos from projeto where id_tipo = '$id_tipo'");
                                              while($result_busca_proj_com_registro = mysqli_fetch_array($query_busca_proj_com_registros)){
                                                  $id_projeto = $result_busca_proj_com_registro['id'];
                                                  $quant_comodos = $result_busca_proj_com_registro['comodos'];

                                                  $query = mysqli_query($con,"select hora_total FROM folha_proj where id_proj = '$id_projeto' and id_etapa = '$id_etapa' and id_subetapa = '$id_subetapa'");
                                                  $result_row = mysqli_num_rows($query);
                                                  if($result_row > 0){
                                                      $comodos_subetapa += $quant_comodos;
                                                      $quant_subetapa ++;
                                                  }

                                              }

                                              $array_soma_horas_subetapa = array('00:00');
                                              $query_soma_subetapa = mysqli_query($con,"select hora_total FROM folha_proj inner join projeto on folha_proj.id_proj = projeto.id inner join tipo_proj on projeto.id_tipo = tipo_proj.id where tipo_proj.id = '$id_tipo' and folha_proj.id_etapa = '$id_etapa' and folha_proj.id_subetapa = '$id_subetapa'")or die(mysqli_error());

                                              while($result_total_h_subetapa = mysqli_fetch_array($query_soma_subetapa)){
                                                  $array_soma_horas_subetapa.array_push($array_soma_horas_subetapa,$result_total_h_subetapa['hora_total']);
                                              }
                                              $result_sum_time_horas_subetapa = sum_the_time($array_soma_horas_subetapa);

                                                      if($result_sum_time_horas_subetapa != '00:00:00'){
                                                          $total_subetapa =  $result_sum_time_horas_subetapa;
                                                          echo "<tr>";
                                                          echo "<td class='text-right'>".$result_busca_sub['nome']."</td>";
                                                          echo "<td>".$quant_subetapa."</td>";
                                                          echo "<td>".$comodos_subetapa."</td>";
                                                          echo "<td>".$total_subetapa."</td>";

                                                          $row = divisao_horas($total_subetapa,$quant_subetapa);
                                                          echo "<td>".$row."</td>";

                                                          //media comodo
                                                          $row2 = divisao_horas($total_subetapa,$comodos_subetapa);
                                                          echo "<td>".$row2."</td>";

                                                          echo "</tr>";
                                                      }


                                          }

                                        echo "</tr>";

                                }

                        }
                    echo '</tbody>
                          </table>';


                    //FECHA divisoes subdivisoes

                    //ETAPA E SUBETAPA POR CARGO

                    $query_busca_cargo_etapa_sub = mysqli_query($con,"select id,cargo from cargo")or die(mysqli_error());
                    while($result_busca_cargo_etapa_sub = mysqli_fetch_array($query_busca_cargo_etapa_sub)) {

                        $id_cargo = $result_busca_cargo_etapa_sub['id'];
                        $cargo = $result_busca_cargo_etapa_sub['cargo'];

                        $query_verifica_existe_horasp_cargo = mysqli_query($con,"select hora_total FROM folha_proj inner join projeto on folha_proj.id_proj = projeto.id inner join tipo_proj on projeto.id_tipo = tipo_proj.id where tipo_proj.id = '$id_tipo' and folha_proj.id_cargo = '$id_cargo'") or die(mysqli_error());
                        $result_row_verifica_existe_horasp_cargo = mysqli_num_rows($query_verifica_existe_horasp_cargo);
                        if($result_row_verifica_existe_horasp_cargo > 0) {

                            //painel
                            echo '<div class="panel panel-default">
                                  <div class="panel-heading">' . $cargo . '</div>
                                  <div class="panel-body">';


                            //CODIGO PRINCIPAL

                            echo '<table class="table table-bordered table-striped">
                                             <thead>
                                             
                                               <tr>
                                                 
                                                 <th>Etapa</th>
                                                 <th>Quant projetos</th>
                                                 <th>Total cômodos</th>
                                                 <th>Total de horas</th>
                                                 <th>Média total</th>
                                                 <th>Média por cômodo</th>
                                               </tr>
                                             </thead>
                                             <tbody>';


                            //media etapa
                            $query_busca_etapa = mysqli_query($con,"select id, nome from etapa order by id");

                            while ($result_busca_etapa = mysqli_fetch_array($query_busca_etapa)) {

                                $id_etapa = $result_busca_etapa['id']; //id etapa
                                $comodos_etapa = 0;
                                $quant_etapa = 0;

                                //pega comodo e quantidade de prjeto com esta etapa
                                $query_busca_proj_com_registros = mysqli_query($con,"select id,comodos from projeto where id_tipo = '$id_tipo'");
                                while ($result_busca_proj_com_registro = mysqli_fetch_array($query_busca_proj_com_registros)) {
                                    $id_projeto = $result_busca_proj_com_registro['id'];
                                    $quant_comodos = $result_busca_proj_com_registro['comodos'];

                                    $query = mysqli_query($con,"select hora_total FROM folha_proj where id_proj = '$id_projeto' and id_etapa = '$id_etapa' and id_cargo = '$id_cargo'");
                                    $result_row = mysqli_num_rows($query);
                                    if ($result_row > 0) {
                                        $comodos_etapa += $quant_comodos;
                                        $quant_etapa++;
                                    }

                                }

                                $array_soma_horas_etapa = array('00:00');
                                $query_soma_etapa = mysqli_query($con,"select hora_total FROM folha_proj inner join projeto on folha_proj.id_proj = projeto.id inner join tipo_proj on projeto.id_tipo = tipo_proj.id where tipo_proj.id = '$id_tipo' and folha_proj.id_etapa = '$id_etapa' and folha_proj.id_cargo = '$id_cargo'") or die(mysqli_error());

                                while ($result_soma_horas_etapa = mysqli_fetch_array($query_soma_etapa)) {
                                    $array_soma_horas_etapa . array_push($array_soma_horas_etapa, $result_soma_horas_etapa['hora_total']);

                                }
                                $result_sum_time_horas_etapa = sum_the_time($array_soma_horas_etapa);

                                if ($result_sum_time_horas_etapa != '00:00:00') {

                                    $total_h_etapa = $result_sum_time_horas_etapa;//total etapa

                                    echo "<tr>";
                                    echo "<td>" . $result_busca_etapa['nome'] . "</td>";
                                    echo "<td>" . $quant_etapa . "</td>";
                                    echo "<td>" . $comodos_etapa . "</td>";
                                    echo "<td>" . $total_h_etapa . "</td>";

                                    $row = divisao_horas($total_h_etapa, $quant_etapa);

                                    echo "<td>" . $row . "</td>";

                                    //media comodo
                                    $row2 = divisao_horas($total_h_etapa, $comodos_etapa);

                                    echo "<td>" . $row2 . "</td>";

                                    //busca subetapa
                                    $query_busca_sub = mysqli_query($con,"select id, nome from sub_etapa where id_etapa = '$id_etapa'");
                                    while ($result_busca_sub = mysqli_fetch_array($query_busca_sub)) {
                                        $id_subetapa = $result_busca_sub['id'];//id subetapa

                                        $comodos_subetapa = 0;
                                        $quant_subetapa = 0;

                                        //pega comodo e quantidade de prjeto com esta subetapa
                                        $query_busca_proj_com_registros = mysqli_query($con,"select id,comodos from projeto where id_tipo = '$id_tipo'");
                                        while ($result_busca_proj_com_registro = mysqli_fetch_array($query_busca_proj_com_registros)) {
                                            $id_projeto = $result_busca_proj_com_registro['id'];
                                            $quant_comodos = $result_busca_proj_com_registro['comodos'];

                                            $query = mysqli_query($con,"select hora_total FROM folha_proj where id_proj = '$id_projeto' and id_etapa = '$id_etapa' and id_subetapa = '$id_subetapa' and id_cargo = '$id_cargo'");
                                            $result_row = mysqli_num_rows($query);
                                            if ($result_row > 0) {
                                                $comodos_subetapa += $quant_comodos;
                                                $quant_subetapa++;
                                            }

                                        }

                                        $array_soma_horas_subetapa = array('00:00');
                                        $query_soma_subetapa = mysqli_query($con,"select hora_total FROM folha_proj inner join projeto on folha_proj.id_proj = projeto.id inner join tipo_proj on projeto.id_tipo = tipo_proj.id where tipo_proj.id = '$id_tipo' and folha_proj.id_etapa = '$id_etapa' and folha_proj.id_subetapa = '$id_subetapa' and folha_proj.id_cargo = '$id_cargo'") or die(mysqli_error());

                                        while ($result_total_h_subetapa = mysqli_fetch_array($query_soma_subetapa)) {
                                            $array_soma_horas_subetapa . array_push($array_soma_horas_subetapa, $result_total_h_subetapa['hora_total']);
                                        }
                                        $result_sum_time_horas_subetapa = sum_the_time($array_soma_horas_subetapa);

                                        if ($result_sum_time_horas_subetapa != '00:00:00') {
                                            $total_subetapa = $result_sum_time_horas_subetapa;
                                            echo "<tr>";
                                            echo "<td class='text-right'>" . $result_busca_sub['nome'] . "</td>";
                                            echo "<td>" . $quant_subetapa . "</td>";
                                            echo "<td>" . $comodos_subetapa . "</td>";
                                            echo "<td>" . $total_subetapa . "</td>";

                                            $row = divisao_horas($total_subetapa, $quant_subetapa);
                                            echo "<td>" . $row . "</td>";

                                            //media comodo
                                            $row2 = divisao_horas($total_subetapa, $comodos_subetapa);
                                            echo "<td>" . $row2 . "</td>";

                                            echo "</tr>";
                                        }


                                    }

                                    echo "</tr>";

                                }

                            }
                            echo '</tbody>
                          </table>';
                            //FECHA CODIGO PRINCIPAL

                            echo '</div></div>'; //fecha painel

                        }//fecha if existe registro cargo


                    } //fecha while cargo

                    //FECHA ETAPA E SUBETAPA POR CARGO


                }
            echo '</tbody>
                  </table>';

        }
    break;

    case "excluir_proj":
        $id = $_POST['id'];
        $query = mysqli_query($con,"delete from projeto where id = '$id'")or die(mysqli_error());

    break;


    case "insere_new_project":
        $cod_proj = $_POST['cod_proj'];
         $nome_proj = $_POST['nome_proj'];
         $metragem_proj = $_POST['metragem_proj'];
         $comodos_proj = $_POST['comodos_proj'];
         $valor_proj = $_POST['valor_proj'];
        $tx_adm = $_POST['tx_adm'];
         $visitas_proj = $_POST['visitas_proj'];
         $tipo_proj =  $_POST['tipo_proj'];
        $aux =0;
        $query = mysqli_query($con,"select cod_projeto from projeto");
            while ($result = mysqli_fetch_array($query)){
                if ($cod_proj == $result['cod_projeto']){
                    $aux ++;
                }
            }


        if ($aux > 0){
            echo "2";
        }else {

            $query_insert = mysqli_query($con,"insert into projeto (cod_projeto, nome, metragem, comodos, valor_proj,taxa_adm, visitas, id_tipo) values ('$cod_proj','$nome_proj','$metragem_proj','$comodos_proj','$valor_proj','$tx_adm','$visitas_proj','$tipo_proj')")or die(mysqli_error());

            if($query_insert){
                echo "0";
            }else {
                echo "1";
            }

        }

    break;

    case "excluir_func":
        $id = $_POST['id'];
        $query = mysqli_query($con,"delete from funcionario where id = '$id'");
   break;

    case "insere_new_func":

         $login_func = $_POST['login_func'];
         $senha_func = $_POST['senha_func'];
         $senha_cript = sha1($senha_func);
         $nome_func = $_POST['nome_func'];
         $cargo_func = $_POST['cargo_func'];
         $horas_func = $_POST['horas_func'];
         $valor_func = $_POST['valor_func'];
         $salario_func = $_POST['salario_func'];
         $nivel_func = $_POST['nivel_func'];

        $aux =0;
        $query = mysqli_query($con,"select login from funcionario");
        while ($result = mysqli_fetch_array($query)){
            if ($find_login == $result['login']){
                $aux ++;
            }
        }


        if ($aux > 0){
            echo "2";
        }else {

            $query_insert = mysqli_query($con,"insert into funcionario (login, password, nome, id_cargo, horas_diarias, valor_hora, salario, id_nivel) values ('$login_func','$senha_cript','$nome_func','$cargo_func','$horas_func','$valor_func','$salario_func','$nivel_func')")or die(mysqli_error());

            if($query_insert){
                echo "0";
            }else {
                echo "1";
            }

        }

        break;

     //cargo
    case "excluir_cargo":
        $id = $_POST['id'];
        $query = mysqli_query($con,"delete from cargo where id = '$id'")or die(mysqli_error());
        if($query){
            echo 0;
        }else {
            echo 1;
        }
        break;

    case "insere_new_cargo":

        $cargo_func = $_POST['cargo_func'];


        $aux =0;
        $query = mysqli_query($con,"select cargo from cargo");
        while ($result = mysqli_fetch_array($query)){
            if ($find_cargo == $result['cargo']){
                $aux ++;
            }
        }


        if ($aux > 0){
            echo "2";
        }else {

            $query_insert = mysqli_query($con,"insert into cargo (cargo) values ('$cargo_func')")or die(mysqli_error());

            if($query_insert){
                echo "0";
            }else {
                echo "1";
            }

        }

        break;

    //cargo

    case "excluir_etapa":
        $id = $_POST['id'];
        $query = mysqli_query($con,"delete from etapa where id = '$id'");

    break;

    case "insere_new_etapa":

        $etapa = $_POST['etapa'];


        $aux =0;
        $query = mysqli_query($con,"select nome from etapa");
        while ($result = mysqli_fetch_array($query)){
            if ($etapa == $result['nome']){
                $aux ++;
            }
        }


        if ($aux > 0){
            echo "2";
        }else {

            $query_insert = mysqli_query($con,"insert into etapa (nome) values ('$etapa')")or die(mysqli_error());

            if($query_insert){
                echo "0";
            }else {
                echo "1";
            }

        }

        break;

    case "find_subetapa":
        $id = $_POST['id'];
        $query = mysqli_query($con,"select id, nome from sub_etapa where id_etapa = '$id'");

        echo '<table id="tab_cad_subetapa" class="table table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th scope="col">Nome da subetapa</th>
                    
                                        </tr>
                                        </thead>
                                        <tbody>';
            while($result = mysqli_fetch_array($query)){



                                                echo "<tr>";
                                                echo "<td>".$result['nome']."</td>";

                                                echo "<td><button type='button' id='".$result['id']."' class='btn_editar_subetapa btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Editar'><span class='glyphicon glyphicon-pencil'></span></button>&nbsp&nbsp&nbsp<button type='button' id='".$result['id']."' class='btn_excluir_subetapa btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Excluir'><span class='glyphicon glyphicon-remove'></span></button></td>";

                                                echo "</tr>";

            }

        echo ' </tbody></table>';

    break;

    case "excluir_subetapa":
        $id = $_POST['id'];
        $query = mysqli_query($con,"delete from sub_etapa where id = '$id'");
    break;

    case "insere_new_subetapa":
        $etapa = $_POST['etapa'];
        $subetapa = $_POST['subetapa'];


        $aux =0;
        $query = mysqli_query($con,"select nome from sub_etapa where id_etapa = '$etapa'");
        while ($result = mysqli_fetch_array($query)){
            if ($subetapa == $result['nome']){
                $aux ++;
            }
        }


        if ($aux > 0){
            echo "2";
        }else {

            $query_insert = mysqli_query($con,"insert into sub_etapa (nome,id_etapa) values ('$subetapa','$etapa')")or die(mysqli_error());

            if($query_insert){
                echo "0";
            }else {
                echo "1";
            }

        }

        break;
    
    case "edit_proj":
        $id = $_POST['id'];
        $array_busca = array();

         $query = mysqli_query($con,"select projeto.id, projeto.cod_projeto, projeto.nome, projeto.metragem, projeto.comodos,projeto.comodos_realizados, projeto.valor_proj, projeto.visitas, projeto.id_tipo, projeto.taxa_adm from projeto where projeto.id = '$id' ")or die(mysqli_error());
         while($result = mysqli_fetch_array($query)){
             $array_busca['id'] = $result['id'];
             $array_busca['cod_projeto'] = $result['cod_projeto'];
             $array_busca['nome'] = $result['nome'];
             $array_busca['metragem'] = $result['metragem'];
             $array_busca['comodos'] = $result['comodos'];
             $array_busca['comodos_realizados'] = $result['comodos_realizados'];
             $array_busca['valor_proj'] = $result['valor_proj'];
             $array_busca['visitas'] = $result['visitas'];
             $array_busca['taxa_adm'] = $result['taxa_adm'];
             //dados select tipo proj
             $id_tipo = $result['id_tipo'];
             $array_tipo_proj = array();
                $query_tipo_proj = mysqli_query($con,"select tipo,id from tipo_proj");
                while($result_tipo_proj = mysqli_fetch_array($query_tipo_proj)){

                    if ($result_tipo_proj['id'] == $id_tipo){
                        $array_tipo_proj[] = '<option value='.$result_tipo_proj['id'].' selected>'.$result_tipo_proj['tipo'].'</option>';
                    } else {

                        $array_tipo_proj[] = '<option value='.$result_tipo_proj['id'].'>'.$result_tipo_proj['tipo'].'</option>';

                    }

                }

             $array_busca['select_tipo'] = $array_tipo_proj;

         }

        echo json_encode($array_busca); //encode json para os dados do ajax

    break;

    case "edit_new_project":
        $id_proj = $_POST['id_proj'];
        $cod_proj = $_POST['cod_proj'];
        $nome_proj = $_POST['nome_proj'];
        $metragem_proj = $_POST['metragem_proj'];
        $comodos_proj = $_POST['comodos_proj'];
        $comodosr_proj = $_POST['comodosr_proj'];
        $valor_proj = $_POST['valor_proj'];
        $tx_adm = $_POST['tx_adm'];
        $visitas_proj = $_POST['visitas_proj'];
        $tipo_proj =  $_POST['tipo_proj'];

        //verifica se taxa adm mudou para inserir historico tabela last_tx_adm
        $query_busca_tx_adm_proj_update = mysqli_query($con,"select taxa_adm from projeto where id='$id_proj'")or die(mysqli_error());
        $result_busca_tx_adm_proj_update = mysqli_fetch_array($query_busca_tx_adm_proj_update);
        $last_tx_adm = $result_busca_tx_adm_proj_update['taxa_adm'];

            if($tx_adm == $last_tx_adm){

                $query = mysqli_query($con,"update projeto set cod_projeto='$cod_proj', nome='$nome_proj', metragem='$metragem_proj', comodos='$comodos_proj', comodos_realizados='$comodosr_proj' ,valor_proj='$valor_proj',taxa_adm='$tx_adm',visitas='$visitas_proj',id_tipo='$tipo_proj' where id='$id_proj'")or die(mysqli_error());

                if($query){
                    echo "0";
                }else {
                    echo "1";
                }


            }else if($tx_adm != $last_tx_adm){

                $query = mysqli_query($con,"update projeto set cod_projeto='$cod_proj', nome='$nome_proj', metragem='$metragem_proj', comodos='$comodos_proj', comodos_realizados='$comodosr_proj' ,valor_proj='$valor_proj',taxa_adm='$tx_adm',visitas='$visitas_proj',id_tipo='$tipo_proj' where id='$id_proj'")or die(mysqli_error());

                if($query){

                    $data_ate = date("Y-m-d");

                    $query_input_last_tx_adm = mysqli_query($con,"insert into last_tx_adm (id_projeto,tx_adm_antigo,data_ate) values('$id_proj','$last_tx_adm','$data_ate')")or die(mysqli_error());

                    echo "0";
                }else {
                    echo "1";
                }


            }


        break;

    case "edit_etapa":
        $id = $_POST['id'];
        $array_busca = array();

        $query = mysqli_query($con,"select id,nome from etapa where id = '$id' ")or die(mysqli_error());
        while($result = mysqli_fetch_array($query)){
            $array_busca['id'] = $result['id'];
            $array_busca['nome'] = $result['nome'];
        }

        echo json_encode($array_busca); //encode json para os dados do ajax

    break;

    case "edit_new_etapa":
        $id = $_POST['id'];
        $etapa = $_POST['etapa'];

        $query = mysqli_query($con,"update etapa set nome='$etapa' where id='$id'")or die(mysqli_error());

        if($query){
            echo "0";
        }else {
            echo "1";
        }

        break;

    case "edit_subetapa":
        $id = $_POST['id'];
        $array_busca = array();

        $query = mysqli_query($con,"select id,nome from sub_etapa where id = '$id' ")or die(mysqli_error());
        while($result = mysqli_fetch_array($query)){
            $array_busca['id'] = $result['id'];
            $array_busca['nome'] = $result['nome'];
        }

        echo json_encode($array_busca); //encode json para os dados do ajax

        break;

    case "edit_new_subetapa":
        $id = $_POST['id'];
        $subetapa = $_POST['etapa'];

        $query = mysqli_query($con,"update sub_etapa set nome='$subetapa' where id='$id'")or die(mysqli_error());

        if($query){
            echo "0";
        }else {
            echo "1";
        }

     break;

    case "edit_func":
        $id = $_POST['id'];
        $array_busca = array();

        $query = mysqli_query($con,"select id,login, nome, id_cargo, horas_diarias, valor_hora, salario,id_nivel, hora_extra from funcionario where id = '$id' ")or die(mysqli_error());
        while($result = mysqli_fetch_array($query)){
            $array_busca['id'] = $result['id'];
            $array_busca['login'] = $result['login'];
            $array_busca['nome'] = $result['nome'];

            $array_busca['horas_diarias'] = $result['horas_diarias'];
            $array_busca['valor_hora'] = $result['valor_hora'];
            $array_busca['salario'] = $result['salario'];
            //dados select tipo proj
            $id_nivel = $result['id_nivel'];
            $hora_extra = $result['hora_extra'];

            //preenche select nivel
            $array_nivel_func = array();

            $query_nivel_func = mysqli_query($con,"select id,nivel from nivel_func");
            while($result_nivel_func = mysqli_fetch_array($query_nivel_func)){

                if ($result_nivel_func['id'] == $id_nivel){
                    $array_nivel_func[] = '<option value='.$result_nivel_func['id'].' selected>'.$result_nivel_func['nivel'].'</option>';
                } else {

                    $array_nivel_func[] = '<option value='.$result_nivel_func['id'].'>'.$result_nivel_func['nivel'].'</option>';

                }

            }
            
            $array_busca['select_nivel'] = $array_nivel_func;

            //preenche select faz hora extra
            $array_h_extra_func = array();
            if($hora_extra == 0){
                $array_h_extra_func[] = '<option value='.'0'.' selected>Não</option>';
                $array_h_extra_func[] = '<option value='.'1'.'>Sim</option>';

            }else{

                $array_h_extra_func[] = '<option value='.'0'.'>Não</option>';
                $array_h_extra_func[] = '<option value='.'1'.' selected>Sim</option>';
            }
            $array_busca['hora_extra'] = $array_h_extra_func;

            //preenche select cargo
            $id_cargo = $result['id_cargo'];

            $array_cargo_func = array();

            $query_cargo_func = mysqli_query($con,"select id,cargo from cargo");
            while($result_cargo_func = mysqli_fetch_array($query_cargo_func)){

                if ($result_cargo_func['id'] == $id_cargo){
                    $array_cargo_func[] = '<option value='.$result_cargo_func['id'].' selected>'.$result_cargo_func['cargo'].'</option>';
                } else {

                    $array_cargo_func[] = '<option value='.$result_cargo_func['id'].'>'.$result_cargo_func['cargo'].'</option>';

                }

            }

            $array_busca['select_cargo'] = $array_cargo_func;


        }

        echo json_encode($array_busca); //encode json para os dados do ajax

        break;


    case "edit_cargo":
        $id = $_POST['id'];
        $array_busca = array();

        $query = mysqli_query($con,"select id,cargo from cargo where id = '$id' ")or die(mysqli_error());
        while($result = mysqli_fetch_array($query)){
            $array_busca['id'] = $result['id'];
            $array_busca['cargo'] = $result['cargo'];

        }

        echo json_encode($array_busca); //encode json para os dados do ajax

        break;
        
        

        case "edit_hist_func":
            $id = $_POST['id'];
            $hist_func_choose = $_POST['hist_func_choose']; 
                        
            if($hist_func_choose == 1){
                echo '
                </br></br>                
                <label>Histórico de horas diárias</label> 
                <table id="tab_edit_hist" class="table table-striped table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Horas diárias</th>
                        <th>Até</th>                        
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                
                ';

                $query = mysqli_query($con,"select * from last_hour where id_login = '$id' order by id asc")or die(mysqli_error());
                while($result = mysqli_fetch_array($query)){                
                    #$array_hist.array_push($array_hist,$result);
                    echo "<tr>";
                    echo "<td>".$result['h_antiga']."</td>";                     
                    echo "<td>".data_manipulation($result['data_ate'],0)."</td>";
                    echo "<td><button type='button' id='".$result['id']."' class='btn_editar_registro_hist btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Editar'><span class='glyphicon glyphicon-pencil'></span></button>&nbsp&nbsp&nbsp<button type='button' id='".$result['id']."' class='btn_excluir_hist btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Excluir'><span class='glyphicon glyphicon-remove'></span></button></td>";
                    echo "</tr>";

                }
                echo '        
                </tbody>
                </table>                
                ';
                
                #echo json_encode($array_hist); //encode json para os dados do ajax
                }else 
                if($hist_func_choose == 2){
                    $array_busca = array();
                    echo '
                    </br></br>
                    <label>Histórico de férias / Atestados / Day Off</label>                     
                    <table id="tab_edit_hist" class="table table-striped table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Data Início</th>
                            <th>Data Fim</th>
                            <th>Hora diária</th>                                                   
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    
                    ';

                    $query = mysqli_query($con,"select * from ferias_atestados where id_func = '$id' order by data_inicio asc")or die(mysqli_error());
                   
                    while($result = mysqli_fetch_array($query)){                
                        echo "<tr>";
                        echo "<td>".data_manipulation($result['data_inicio'],0)."</td>"; 
                        echo "<td>".data_manipulation($result['data_fim'],0)."</td>";                                            
                        echo "<td>".$result['hora_diaria']."</td>";
                        echo "<td><button type='button' id='".$result['id']."' class='btn_editar_registro_hist btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Editar'><span class='glyphicon glyphicon-pencil'></span></button>&nbsp&nbsp&nbsp<button type='button' id='".$result['id']."' class='btn_excluir_hist btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Excluir'><span class='glyphicon glyphicon-remove'></span></button></td>";
                        echo "</tr>";
                        //$array_busca.array_push($array_busca,'de':$de);
                    }
                    echo '        
                    </tbody>
                    </table>
                    ';
                    //echo json_encode($array_busca);
                    
                }else if($hist_func_choose == 3){
                    #$array_busca = array();
                    
                    echo '
                    </br></br>
                    <label>Histórico do valor hora</label>                     
                    <table id="tab_edit_hist" class="table table-striped table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>De</th>
                            <th>Até</th>
                            <th>Valor hora</th>                                                   
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    
                    ';


                    $query = mysqli_query($con,"select id_func,min(data) as data_de,max(data) as data_ate,max(valor_hora) as valor_hora from folha_proj where id_func = '$id' GROUP by valor_hora order by data")or die(mysqli_error());

                    while($result = mysqli_fetch_array($query)){                
                        echo "<tr>";
                        echo "<td>".data_manipulation($result['data_de'],0)."</td>"; 
                        echo "<td>".data_manipulation($result['data_ate'],0)."</td>";                                            
                        echo "<td>".$result['valor_hora']."</td>";
                        $id_data = $result['id_func'].','.data_manipulation($result['data_de'],0).','.data_manipulation($result['data_ate'],0).','.$result['valor_hora'];
                        echo "<td><button type='button' id='".$id_data."' class='btn_editar_registro_hist btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Editar'><span class='glyphicon glyphicon-pencil'></span></td>";
                        echo "</tr>";
                        
                    }

                    echo '        
                    </tbody>
                    </table>
                    ';

                  
                }                
            break;
    
    //get lhour data
    case "get_last_hour_data":
        $id_func = $_POST['id_func'];
        $id_registro = $_POST['id_registro'];
        $array_busca = array();

        $query = mysqli_query($con,"select * from last_hour where id = '$id_registro' and id_login = '$id_func'")or die(mysqli_error());
        
        while($result = mysqli_fetch_array($query)){                
           
            $array_busca['id'] =  $result['id']; 
            $array_busca['id_login'] = $result['id_login'];
            $array_busca['h_antiga'] =  $result['h_antiga'];
            $array_busca['data_ate'] = data_manipulation($result['data_ate'],0);            
                
        }
        
        echo json_encode($array_busca);
        break;
    //edit lhour data
    case "edit_last_hour_data":        
        $field_id_func_edit = $_POST['field_id_func_edit'];    
        $field_id_registro_edit = $_POST['field_id_registro_edit'];
        $field_h_antiga_edit = $_POST['field_h_antiga_edit'];    
        $field_ate_edit = data_manipulation($_POST['field_ate_edit'],1);
        
        $query = mysqli_query($con,"update last_hour set h_antiga='$field_h_antiga_edit',data_ate='$field_ate_edit' where id_login=$field_id_func_edit and id=$field_id_registro_edit")or die(mysqli_error());

        if($query){
            echo "0";
        }else {
            echo "1";
        }
        
        break;        
    //exclude lasthour 
    case "exclude_last_hour_data":        
        $field_id_func_edit = $_POST['id_func'];    
        $field_id_registro_edit = $_POST['id_registro'];
        
        $query = mysqli_query($con,"delete from last_hour where id_login=$field_id_func_edit and id=$field_id_registro_edit")or die(mysqli_error());

        if($query){
            echo "0";
        }else {
            echo "1";
        }
        
        break;
    //get ferias data
    case "get_ferias_data":
        $id_func = $_POST['id_func'];
        $id_registro = $_POST['id_registro'];
        $array_busca = array();

        $query = mysqli_query($con,"select * from ferias_atestados where id = '$id_registro' and id_func = '$id_func'")or die(mysqli_error());
        
        while($result = mysqli_fetch_array($query)){                
           
            $array_busca['id'] =  $result['id']; 
            $array_busca['id_func'] = $result['id_func'];
            $array_busca['data_inicio'] =  data_manipulation($result['data_inicio'],0);
            $array_busca['data_fim'] =  data_manipulation($result['data_fim'],0);
            $array_busca['hora_diaria'] = $result['hora_diaria'];            
                
        }
        
        echo json_encode($array_busca);
        break;
   
        
    //edit salary
    case "edit_ferias_data": 
       
        $field_ferias_id_func_edit = $_POST['field_ferias_id_func_edit'];    
        $field_ferias_id_registro_edit = $_POST['field_ferias_id_registro_edit'];
        $field_data_inicio_edit = data_manipulation($_POST['field_data_inicio_edit'],1);    
        $field_data_fim_edit = data_manipulation($_POST['field_data_fim_edit'],1);    
        $field_hora_diaria_edit = $_POST['field_hora_diaria_edit'];
                
        $query = mysqli_query($con,"update ferias_atestados set data_inicio='$field_data_inicio_edit',data_fim='$field_data_fim_edit',hora_diaria='$field_hora_diaria_edit' where id_func=$field_ferias_id_func_edit and id=$field_ferias_id_registro_edit")or die(mysqli_error());

        if($query){
            echo "0";
        }else {
            echo "1";
        }
        
        break; 

    //edit valor hora
    case "edit_valor_hora_data": 
       
        $field_id_func_edit = $_POST['field_id_func_edit'];            
        $field_data_de_edit = data_manipulation($_POST['field_data_de_edit'],1);    
        $field_data_ate_edit = data_manipulation($_POST['field_data_ate_edit'],1);    
        $field_valor_hora_edit = $_POST['field_valor_hora_edit'];
        //update folha_proj set valor_hora = 44.00 where id_func = 14 and data BETWEEN '2015-01-07' and '2018-11-26';
        $query = mysqli_query($con,"update folha_proj set valor_hora = ".$field_valor_hora_edit." where id_func = ".$field_id_func_edit." and data BETWEEN '".$field_data_de_edit."' and '".$field_data_ate_edit."'")or die(mysqli_error());

        if($query){
            echo "0";
        }else {
            echo "1";
        }
        
        break;     
    
    case "exclude_ferias_data":        
        $field_id_func_edit = $_POST['id_func'];    
        $field_id_registro_edit = $_POST['id_registro'];
        
        $query = mysqli_query($con,"delete from ferias_atestados where id_func=$field_id_func_edit and id=$field_id_registro_edit")or die(mysqli_error());

        if($query){
            echo "0";
        }else {
            echo "1";
        }
        
        break;    

    case "edit_new_subetapa":
        $id = $_POST['id'];
        $subetapa = $_POST['etapa'];

        $query = mysqli_query($con,"update sub_etapa set nome='$subetapa' where id='$id'")or die(mysqli_error());

        if($query){
            echo "0";
        }else {
            echo "1";
        }

        break;

    case "edit_new_func":
        $id_func = $_POST['id_func'];
        $login_func = $_POST['login_func'];
        $senha_func = $_POST['senha_func'];
        $senha_cript = sha1($senha_func);
        $nome_func = $_POST['nome_func'];
        $cargo_func = $_POST['cargo_func'];
        $horas_func = $_POST['horas_func'];
        $valor_func = $_POST['valor_func'];
        $salario_func = $_POST['salario_func'];
        $nivel_func = $_POST['nivel_func'];
        $hora_extra = $_POST['hora_extra'];

        $query_busca_h_atual = mysqli_query($con,"select horas_diarias, valor_hora, salario from funcionario where id = '$id_func'")or die(mysqli_error());
        $result_h_atual = mysqli_fetch_array($query_busca_h_atual);

        //validação para salvar valor hora e salario na tabela temporaria
        $valor_hora_func_atual = $result_h_atual['valor_hora'];
        $salario_func_atual = $result_h_atual['salario'];

        if($valor_func != $valor_hora_func_atual || $salario_func != $salario_func_atual){
            //INPUTAR AQUI NA TABELA last_salary
            $data_ate = date("Y-m-d");
            $query_input_salary = mysqli_query($con,"insert into last_salary (id_login, vh_antigo, salary_antigo, data_ate) values ('$id_func','$valor_hora_func_atual','$salario_func_atual','$data_ate')")or die(mysqli_error());

        }


            //validação para mudanças de horas diarias do funcionario na tabela temporaria
            $h_atual = $result_h_atual['horas_diarias']; //pega h diaria atual p colocar na tabela last-hour

            if ($horas_func == $h_atual) {

                if ($senha_func == "") {

                    $query = mysqli_query($con,"update funcionario set login='$login_func', nome='$nome_func',id_cargo='$cargo_func',horas_diarias='$horas_func',valor_hora='$valor_func',salario='$salario_func',id_nivel='$nivel_func',hora_extra='$hora_extra' where id='$id_func'") or die(mysqli_error());
                } else {

                    $query = mysqli_query($con,"update funcionario set login='$login_func', password='$senha_cript', nome='$nome_func',id_cargo='$cargo_func',horas_diarias='$horas_func',valor_hora='$valor_func',salario='$salario_func',id_nivel='$nivel_func',hora_extra='$hora_extra' where id='$id_func'") or die(mysqli_error());
                }

                if ($query) {
                    echo "0";
                } else {
                    echo "1";
                }
            } else if ($horas_func != $h_atual) {

                $today_date = date('Y-m-d');

                //verifica se existe na tabela temporaria horas diarias e salario já imputadas, se existe nâo deixa passar
                $query_busca_h_ja_imputadas_na_temp = mysqli_query($con,"select id_login,h_antiga,data_ate from last_hour where id_login = '$id_func' and data_ate = '$today_date'  ")or die(mysqli_error());
                $result_busca_h_ja_imputadas_na_temp = mysqli_num_rows($query_busca_h_ja_imputadas_na_temp);

                if($result_busca_h_ja_imputadas_na_temp > 0){

                    echo "2";


                }else {

                    if ($senha_func == "") {

                        $query = mysqli_query($con,"update funcionario set login='$login_func', nome='$nome_func',id_cargo='$cargo_func',horas_diarias='$horas_func',valor_hora='$valor_func',salario='$salario_func',id_nivel='$nivel_func',hora_extra='$hora_extra' where id='$id_func'") or die(mysqli_error());
                    } else {

                        $query = mysqli_query($con,"update funcionario set login='$login_func', password='$senha_cript', nome='$nome_func',id_cargo='$cargo_func',horas_diarias='$horas_func',valor_hora='$valor_func',salario='$salario_func',id_nivel='$nivel_func',hora_extra='$hora_extra' where id='$id_func'") or die(mysqli_error());
                    }

                    if ($query) {
                        $data_ate = date("Y-m-d");

                        $query = mysqli_query($con,"Insert Into last_hour (id_login,h_antiga,data_ate) values ('$id_func','$h_atual','$data_ate')  ") or die(mysqli_error());

                        echo "0";
                    } else {
                        echo "1";
                    }
                }

            }



     break;

    //edit cargo
    case "edit_new_cargo":
        $id_cargo = $_POST['id_cargo'];
        $cargo_func = $_POST['cargo_func'];

        $query = mysqli_query($con,"update cargo set cargo='$cargo_func' where id='$id_cargo'")or die(mysqli_error());


        if($query){
            echo "0";
        }else {
            echo "1";
        }

        break;

    //fecha edit_cargo

    //RELATORIO USUARIO
    case "relatorio_proj":
        session_start();
        $id_func = $_SESSION["id_func"];
        $start_data = $_POST["start_data"];
        $end_data = $_POST["end_data"];
        $start_data = explode("/", $start_data);
        list($dia,$mes,$ano) = $start_data;
        $start_data_invertida = "$ano-$mes-$dia"; //inverter data pro banco
        $end_data = explode("/", $end_data);
        list($dia,$mes,$ano) = $end_data;
        $end_data_invertida = "$ano-$mes-$dia"; //inverter data pro banco

        $id_func = $_POST["select_rel_user"];

        //calculos e infos usuário
        echo '
    <table  class="table table-bordered" width="100%" cellspacing="0">
    <thead>
        <tr>
            <th>Funcionário</th>
            <th>Cargo</th>
            <th>Horas diárias</th>
             <th>Dias úteis</th>
             <th>Deveria trabalhar</th>
             <th>Trabalhado</th>
            <th>Projetos</th>
            <th>Administrativo</th>
           <th>Férias/atestados</th>
           <th>Dayoff</th>
            <th>Saldo</th>
        </tr>
    </thead>
    <tbody>

    ';
        $query_sum = mysqli_query($con,"SELECT funcionario.nome, cargo.cargo, funcionario.horas_diarias FROM funcionario inner join cargo on funcionario.id_cargo = cargo.id where funcionario.id = '$id_func' ")or die(mysqli_error());
        $result_sum = mysqli_fetch_array($query_sum);
            echo "<tr>";
            echo "<td>".$result_sum['nome']."</td>";
            echo "<td>".$result_sum['cargo']."</td>";
        $horas_do_usuario = $result_sum['horas_diarias'];


        //teste se foi alterado as horas diárias do funcionario
        $query_busca_horas_d_antigas = mysqli_query($con,"SELECT * FROM last_hour where id_login = '$id_func' order by data_ate asc");
        
        if(mysqli_num_rows($query_busca_horas_d_antigas) == 0){

            echo "<td>".$result_sum['horas_diarias']."</td>";

            //calcula dias úteis no período
            $query_dias_uteis_periodo = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$start_data_invertida' and '$end_data_invertida' ");
            $count_dias = mysqli_fetch_array($query_dias_uteis_periodo);
            echo "<td>".$count_dias['dias_uteis_periodo']."</td>";


            //multiplica horas por dias úteis
            $dias_a_trabalhar = multiplicacao_horas($result_sum['horas_diarias'],$count_dias['dias_uteis_periodo']);

            echo "<td>".$dias_a_trabalhar."</td>";

        }else {

            $query_busca_horas_d_antigas_ver = mysqli_query($con,"SELECT * FROM last_hour where id_login = '$id_func' order by data_ate asc");
            echo "<td>";

            //mostrar alteraçoes feitas nas horas diarias
            while($result_achou_h_antiga_ver = mysqli_fetch_array($query_busca_horas_d_antigas_ver)) {
                //convert data
                $data_ate_antiga = explode("-", $result_achou_h_antiga_ver['data_ate']);
                list($ano,$mes,$dia) = $data_ate_antiga;
                $data_antiga_invertida = "$dia/$mes/$ano";

                echo $result_achou_h_antiga_ver['h_antiga']." áte ".$data_antiga_invertida.".</br>";
            }

            echo $result_sum['horas_diarias']." atual</br>";
            echo "</td>";


            //$query_busca_horas_antigas = mysqli_query($con,"SELECT * FROM last_hour where id_login = '$id_func' order by data_ate asc");

            $primeira_data = $start_data_invertida;
            $ultima_data = $start_data_invertida;
            //$ultima_data = $end_data_invertida;
            $dias_a_trabalhar = "00:00:00";
            $dias_u = 0;
            $h_a_trabalhar = "00:00:00";
            $aux = "FALSE";


            while($result_achou_h_antiga = mysqli_fetch_array($query_busca_horas_d_antigas)){

                if($aux == 'FALSE'){

                    if($result_achou_h_antiga['data_ate'] >= $start_data_invertida && $result_achou_h_antiga['data_ate'] <= $end_data_invertida){

                        $ultima_data = $result_achou_h_antiga['data_ate'];
                        //echo($primeira_data.' '.$ultima_data);
                        //calcula dias úteis no período
                        $query_dias_uteis_periodo = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$primeira_data' and '$ultima_data' ");
                        $count_dias = mysqli_fetch_array($query_dias_uteis_periodo);
                        
                        $dias_u += $count_dias['dias_uteis_periodo'];
                        
                        $day_work = $dias_a_trabalhar;
                        
                        //multiplica horas por dias úteis
                        $h_a_trabalhar = multiplicacao_horas($result_achou_h_antiga['h_antiga'],$count_dias['dias_uteis_periodo']);
                                              
                        $primeira_data = date('Y-m-d', strtotime($ultima_data.' +1 day'));
                            
                        $dias_a_trabalhar = sum_the_time2($day_work,$h_a_trabalhar);
                        

                    }else if($result_achou_h_antiga['data_ate'] > $end_data_invertida){
                        //echo($primeira_data.' '.$end_data_invertida);    

                        $query_dias_uteis_periodo = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$primeira_data' and '$end_data_invertida' ");
                        $count_dias = mysqli_fetch_array($query_dias_uteis_periodo);


                        $primeira_data = $end_data_invertida;

                        $ultima_data = "end";

                        $dias_u += $count_dias['dias_uteis_periodo'];
                        //echo($dias_u);

                        $day_work = $dias_a_trabalhar;
                        
                        //multiplica horas por dias úteis
                        $h_a_trabalhar = multiplicacao_horas($result_achou_h_antiga['h_antiga'],$count_dias['dias_uteis_periodo']);

                        
                        $dias_a_trabalhar = sum_the_time2($day_work,$h_a_trabalhar);
                        
                        $aux = "TRUE";

                    }



                }

              
            }
            

             if($dias_u != 0 && $h_a_trabalhar != '00:00:00'){
                                
                if($ultima_data != "end" ){
                    $ultima_data = date('Y-m-d', strtotime($ultima_data.' +1 day'));
                    //if($ultima_data != $start_data_invertida){
                    //   $ultima_data = date('Y-m-d', strtotime($ultima_data.' +1 day'));
                    // } 
                   
                    $query_dias_uteis_periodo = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$ultima_data' and '$end_data_invertida' ");
                    //$query_dias_uteis_periodo = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$start_data_invertida' and '$end_data_invertida' ");
                    $count_dias = mysqli_fetch_array($query_dias_uteis_periodo);
                    
                    $dias_u += $count_dias['dias_uteis_periodo'];
                    
                    //multiplica horas por dias úteis
                    $dias_a_trabalhar_f = multiplicacao_horas($result_sum['horas_diarias'],$count_dias['dias_uteis_periodo']);
                    
                    $dias_a_trabalhar = sum_the_time2($dias_a_trabalhar,$dias_a_trabalhar_f);
                   
    
                }

            }else {
                if($ultima_data != "end" ){
                
                    //if($ultima_data != $start_data_invertida){
                    //   $ultima_data = date('Y-m-d', strtotime($ultima_data.' +1 day'));
                    // } 
                   
                    $query_dias_uteis_periodo = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$start_data_invertida' and '$end_data_invertida' ");
                    //$query_dias_uteis_periodo = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$start_data_invertida' and '$end_data_invertida' ");
                    $count_dias = mysqli_fetch_array($query_dias_uteis_periodo);
                    
                    $dias_u += $count_dias['dias_uteis_periodo'];
                    
                    //multiplica horas por dias úteis
                    $dias_a_trabalhar_f = multiplicacao_horas($result_sum['horas_diarias'],$count_dias['dias_uteis_periodo']);
                    
                    $dias_a_trabalhar = sum_the_time2($dias_a_trabalhar,$dias_a_trabalhar_f);
                   
    
                }
            }

            


            echo "<td>".$dias_u."</td>";
            echo "<td>".$dias_a_trabalhar."</td>";

        }
        
        //fecha teste se foi alterado as horas diárias do funcionario


        //Soma horas de projetos
        $array_soma_horas_proj = array('00:00');
        $query_h_adm = mysqli_query($con,"SELECT folha_proj.hora_total AS total_h FROM folha_proj where folha_proj.id_func = '$id_func' and folha_proj.data BETWEEN '$start_data_invertida' and '$end_data_invertida' ")or die(mysqli_error());
        while($result_h_adm = mysqli_fetch_array($query_h_adm)){
            $array_soma_horas_proj.array_push($array_soma_horas_proj,$result_h_adm['total_h']);

        }
        $result_sum_time_proj = sum_the_time($array_soma_horas_proj);

         //Soma horas administrativas
        $array_soma_horas = array('00:00');
        $query_sum = mysqli_query($con,"SELECT folha_adm.hora_total AS total_h FROM folha_adm where folha_adm.id_func = '$id_func' and folha_adm.data BETWEEN '$start_data_invertida' and '$end_data_invertida' ")or die(mysqli_error());
        while($result_sum = mysqli_fetch_array($query_sum)){
            $array_soma_horas.array_push($array_soma_horas,$result_sum['total_h']);

        }
        $result_sum_time_adm = sum_the_time($array_soma_horas);

        //trabalhado
        $array_soma_total = array($result_sum_time_proj,$result_sum_time_adm);

        $trabalhado = sum_the_time($array_soma_total);
        echo "<td>".$trabalhado."</td>";
        //em projetos
        echo "<td>".$result_sum_time_proj."</td>";
        //no administrativo
        echo "<td>".$result_sum_time_adm."</td>";


        //ferias e atestados baseados no periodo de busca

        $calculo_h_e_ferias = '00:00:00';
        $result_calculo_h_e_ferias = '00:00:00';

        $query_busca_ferias_atestados_entre = mysqli_query($con,"SELECT data_inicio,data_fim,tipo,hora_diaria FROM ferias_atestados where (id_func = '$id_func' and tipo != 3 and data_inicio BETWEEN '$start_data_invertida' and '$end_data_invertida') or (id_func = '$id_func' and tipo != 3 and data_fim BETWEEN '$start_data_invertida' and '$end_data_invertida')  ");
        $result_row_busca_ferias_atestados_entre = mysqli_num_rows($query_busca_ferias_atestados_entre);

        
        if($result_row_busca_ferias_atestados_entre > 0){
            $d_uteis_ferias = 0;
            $dias_totais = 0;
            while($result_array_ferias_entre = mysqli_fetch_array($query_busca_ferias_atestados_entre)){
                $data_inicio_p_ferias_entre = $result_array_ferias_entre['data_inicio'];
                $data_fim_p_ferias_entre = $result_array_ferias_entre['data_fim'];
                $hora_diaria = $result_array_ferias_entre['hora_diaria'];

              
                //dias totais entre as datas
                $datepicker_inicio = new DateTime($start_data_invertida);
                $datepicker_fim = new DateTime($end_data_invertida);
                $retorno_ferias_inicio = new DateTime($data_inicio_p_ferias_entre);
                $retorno_ferias_fim = new DateTime($data_fim_p_ferias_entre);
                
                
                if (($retorno_ferias_inicio >= $datepicker_inicio) && ($retorno_ferias_fim <= $datepicker_fim) ){
                    //  pega os dias corridos  
                    $dateInterval = $retorno_ferias_inicio->diff($retorno_ferias_fim);
                    $dias_totais += $dateInterval->days+1; 
                                         
                    //calcula dias úteis no período
                    $query_dias_uteis_periodo_pferias = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$data_inicio_p_ferias_entre' and '$data_fim_p_ferias_entre' ");
                    $count_dias_ferias_entre = mysqli_fetch_array($query_dias_uteis_periodo_pferias);
                    
                        
                    $count_dia = $count_dias_ferias_entre['dias_uteis_periodo'];
            
                    $calculo_h_e_ferias = multiplicacao_horas($hora_diaria,$count_dia);
                    
                    $result_calculo_h_e_ferias = sum_the_time2($result_calculo_h_e_ferias,$calculo_h_e_ferias);   

                    $d_uteis_ferias += $count_dias_ferias_entre['dias_uteis_periodo'];

                                          
                    

                }else if(($retorno_ferias_fim > $datepicker_fim) && ($retorno_ferias_inicio >= $datepicker_inicio) && ($retorno_ferias_inicio <= $datepicker_fim)){
                    
                    $dateInterval = $retorno_ferias_inicio->diff($datepicker_fim);
                    $dias_totais = $dias_totais + $dateInterval->days+1;
                    
                     //calcula dias úteis no período
                     $query_dias_uteis_periodo_pferias = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$data_inicio_p_ferias_entre' and '$end_data_invertida' ");
                     $count_dias_ferias_entre = mysqli_fetch_array($query_dias_uteis_periodo_pferias);
                     
                        $count_dia = $count_dias_ferias_entre['dias_uteis_periodo'];
            
                        $calculo_h_e_ferias = multiplicacao_horas($hora_diaria,$count_dia);
                        
                        $result_calculo_h_e_ferias = sum_the_time2($result_calculo_h_e_ferias,$calculo_h_e_ferias);   

                        $d_uteis_ferias += $count_dias_ferias_entre['dias_uteis_periodo'];

                                        
                }else if(($retorno_ferias_inicio < $datepicker_inicio) && ($retorno_ferias_fim <= $datepicker_fim) && ($retorno_ferias_fim >= $datepicker_inicio)){
                    
                    $dateInterval = $datepicker_inicio->diff($retorno_ferias_fim);
                    $dias_totais = $dias_totais + $dateInterval->days+1;
                    
                     //calcula dias úteis no período
                     $query_dias_uteis_periodo_pferias = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$start_data_invertida' and '$data_fim_p_ferias_entre' ");
                     $count_dias_ferias_entre = mysqli_fetch_array($query_dias_uteis_periodo_pferias);
                        
                        $count_dia = $count_dias_ferias_entre['dias_uteis_periodo'];
            
                        $calculo_h_e_ferias = multiplicacao_horas($hora_diaria,$count_dia);
                        
                        $result_calculo_h_e_ferias = sum_the_time2($result_calculo_h_e_ferias,$calculo_h_e_ferias);   

                        $d_uteis_ferias += $count_dias_ferias_entre['dias_uteis_periodo'];
                     
                                        
                }

            }


        }else {
            
            //verifica se periodo escolhido datepicker esta entre algumas ferias
            $query_busca_periodo_entre_encontrado = mysqli_query($con,"SELECT data_inicio,data_fim,tipo,hora_diaria FROM ferias_atestados where (id_func = '$id_func' and tipo != 3 and '$start_data_invertida' between data_inicio and data_fim) or (id_func = '$id_func' and tipo != 3 and '$end_data_invertida' between data_inicio and data_fim) ");
            $result_periodo_entre_encontrado = mysqli_num_rows($query_busca_periodo_entre_encontrado);

            

            if ($result_periodo_entre_encontrado > 0) {
                $d_uteis_ferias = 0;
                $dias_totais = 0;
                while ($result_array_periodo_entre_encontrado = mysqli_fetch_array($query_busca_periodo_entre_encontrado)) {

                    //dias totais entre as datas
                    $data_inicio_tot = new DateTime($result_array_periodo_entre_encontrado['data_inicio']);
                    $data_fim_tot = new DateTime($result_array_periodo_entre_encontrado['data_fim']);

                    $dateInterval = $data_inicio_tot->diff($data_fim_tot);
                    $dias_totais = $dias_totais + $dateInterval->days+1;

                    $hora_diaria = $result_array_periodo_entre_encontrado['hora_diaria'];
                    

                    $query_dias_uteis_periodo_piniciofim = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$start_data_invertida' and '$end_data_invertida' ");
                    $count_dias_ferias_iniciofim = mysqli_fetch_array($query_dias_uteis_periodo_piniciofim);

                    $count_dia = $count_dias_ferias_iniciofim['dias_uteis_periodo'];
            
                    $calculo_h_e_ferias = multiplicacao_horas($hora_diaria,$count_dia);
                    
                    $result_calculo_h_e_ferias = sum_the_time2($result_calculo_h_e_ferias,$calculo_h_e_ferias);   

                    $d_uteis_ferias += $count_dias_ferias_entre['dias_uteis_periodo'];

                }


            } 
            
            
        }


        //tratamento da informação de dayoff

         $dayoff_final = '';

        $query_busca_dayoff = mysqli_query($con,"SELECT data_inicio,data_fim,tipo,hora_diaria FROM ferias_atestados where id_func = '$id_func' and tipo = 3 ");
        $result_busca_dayoff = mysqli_num_rows($query_busca_dayoff);

            if($result_busca_dayoff > 0){

                while($result_encontrou_dayoff = mysqli_fetch_array($query_busca_dayoff)){

                    $data_inicio =  $result_encontrou_dayoff['data_inicio'];

                    $data_iniciol = explode("-",  $data_inicio);
                    list($ano,$mes,$dia) = $data_iniciol;
                    $data_inicio_n = "$dia/$mes/$ano";

                    $data_fim =  $result_encontrou_dayoff['data_fim'];

                    $data_fiml = explode("-",  $data_fim);
                    list($ano,$mes,$dia) = $data_fiml;
                    $data_fim_n = "$dia/$mes/$ano";



                    //dayoff esta entre periodo de busca no datepicker
                    if(($data_inicio >= $start_data_invertida && $data_inicio <= $end_data_invertida) && ($data_fim >= $start_data_invertida && $data_fim <= $end_data_invertida)){

                        if($data_inicio_n == $data_fim_n){

                            $dayoff_final = $dayoff_final.' '.$data_inicio_n.'</br>';

                        }else {

                            $dayoff_final = $dayoff_final.' '.$data_inicio_n." a ".$data_fim_n.'</br>';
                        }

                        

                    }
                    //periodo de busca esta entre dayoff
                    else if(($start_data_invertida >= $data_inicio && $start_data_invertida <= $data_fim) && ($end_data_invertida >= $data_inicio && $end_data_invertida <= $data_fim)){ 
                        
                        if($data_inicio_n == $data_fim_n){

                            $dayoff_final = $dayoff_final.' '.$data_inicio_n.'</br>';

                        }else {

                            $dayoff_final = $dayoff_final.' '.$data_inicio_n." a ".$data_fim_n.'</br>';
                        }

                    }
                    //ou inicio ou fim esta entre o periodo de busca
                    else if(($data_inicio >= $start_data_invertida && $data_inicio <= $end_data_invertida) || ($data_fim >= $start_data_invertida && $data_fim <= $end_data_invertida)){

                        if($data_inicio_n == $data_fim_n){

                            $dayoff_final = $dayoff_final.' '.$data_inicio_n.'</br>';

                        }else {

                            $dayoff_final =  $dayoff_final.' '.$data_inicio_n." a ".$data_fim_n.'</br>';
                        }

                    }     

            }            


        }
        



        //fecha tratamento da informação de dayoff




        if($result_calculo_h_e_ferias == '00:00:00'){
            //saldo
            $saldo = diferenca_horas($dias_a_trabalhar,$trabalhado);

            echo "<td>".$result_calculo_h_e_ferias."</td>";
            echo "<td>".$dayoff_final." </td>";
            echo "<td>".$saldo."</td>";

        }else {

            //saldo
            $trabalhado_menos_ferias = diferenca_horas($result_calculo_h_e_ferias,$dias_a_trabalhar);
            $saldo = diferenca_horas($trabalhado_menos_ferias,$trabalhado);


            echo "<td> Horas: ".$result_calculo_h_e_ferias."</br> Dias úteis: ".$d_uteis_ferias."</td>";
            echo "<td>".$dayoff_final."</td>";
           // $saldo_ferias = $saldo;
            echo "<td>".$saldo."</td>";

        }


        echo "</tr>";


        echo '

        </tbody>
        </table>';

        echo "</br></br>";
        //registros usuário
        echo '
    <table id="tab_rel" class="table table-striped table-bordered" width="100%" cellspacing="0">
    <thead>
        <tr>
            <th>Funcionário</th>
            <th>Data</th>
            <th>Cod projeto</th>
            <th>Nome projeto</th>
            <th>Etapa</th>
            <th>Sub etapa</th>
            <th>Hora inicial</th>
            <th>Hora Final</th>
            <th>Hora total</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
    
    ';

        $query = ("SELECT folha_proj.id, funcionario.nome as funcionario, folha_proj.data, projeto.cod_projeto as cod_projeto, projeto.nome as projeto, etapa.nome as etapa, sub_etapa.nome as subetapa,folha_proj.h_inicial, folha_proj.h_final, folha_proj.hora_total  FROM folha_proj inner join funcionario on folha_proj.id_func = funcionario.id inner join projeto on folha_proj.id_proj = projeto.id inner join etapa on folha_proj.id_etapa = etapa.id inner join sub_etapa on folha_proj.id_subetapa = sub_etapa.id where id_func = '$id_func' and data BETWEEN '$start_data_invertida' and '$end_data_invertida' order by folha_proj.id DESC")or die(mysqli_error());
        $exec_query = mysqli_query($con,$query);
        while($result = mysqli_fetch_array($exec_query)){
            echo "<tr>";
            echo "<td>".$result['funcionario']."</td>";

            $result['data'] = explode("-", $result['data']);
            list($ano,$mes,$dia) = $result['data'];
            $end_data_show = "$dia/$mes/$ano";

            echo "<td>".$end_data_show."</td>";
            echo "<td>".$result['cod_projeto']."</td>";
            echo "<td>".$result['projeto']."</td>";
            echo "<td>".$result['etapa']."</td>";
            echo "<td>".$result['subetapa']."</td>";
            echo "<td>".$result['h_inicial']."</td>";
            echo "<td>".$result['h_final']."</td>";
            echo "<td>".$result['hora_total']."</td>";

            echo "<td><button type='button' id='".$result['id']."' class='btn_editar_registro btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Editar'><span class='glyphicon glyphicon-pencil'></span></button>&nbsp&nbsp&nbsp<button type='button' id='".$result['id']."' class='btn_excluir_registro btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Excluir'><span class='glyphicon glyphicon-remove'></span></button></td>";

            echo "</tr>";
        }

        echo '
        
        </tbody>
        </table>';

        break;

    case "relatorio_adm":
        $start_data = $_POST["start_data"];
        $end_data = $_POST["end_data"];
        $start_data = explode("/", $start_data);
        list($dia,$mes,$ano) = $start_data;
        $start_data_invertida = "$ano-$mes-$dia"; //inverter data pro banco
        $end_data = explode("/", $end_data);
        list($dia,$mes,$ano) = $end_data;
        $end_data_invertida = "$ano-$mes-$dia"; //inverter data pro banco
        $id_func = $_POST["select_rel_user"];
        //calculos e infos usuário
        //calculos e infos usuário
        echo '
    <table  class="table table-bordered" width="100%" cellspacing="0">
    <thead>
        <tr>
            <th>Funcionário</th>
            <th>Cargo</th>
            <th>Horas diárias</th>
             <th>Dias úteis</th>
             <th>Deveria trabalhar</th>
             <th>Trabalhado</th>
            <th>Projetos</th>
            <th>Administrativo</th>
           <th>Férias/atestados</th>
           <th>Dayoff</th>
            <th>Saldo</th>
        </tr>
    </thead>
    <tbody>

    ';
        $query_sum = mysqli_query($con,"SELECT funcionario.nome, cargo.cargo, funcionario.horas_diarias FROM funcionario inner join cargo on funcionario.id_cargo = cargo.id where funcionario.id = '$id_func' ")or die(mysqli_error());
        $result_sum = mysqli_fetch_array($query_sum);
            echo "<tr>";
            echo "<td>".$result_sum['nome']."</td>";
            echo "<td>".$result_sum['cargo']."</td>";
        $horas_do_usuario = $result_sum['horas_diarias'];


        //teste se foi alterado as horas diárias do funcionario
        $query_busca_horas_d_antigas = mysqli_query($con,"SELECT * FROM last_hour where id_login = '$id_func' order by data_ate asc");
        
        if(mysqli_num_rows($query_busca_horas_d_antigas) == 0){

            echo "<td>".$result_sum['horas_diarias']."</td>";

            //calcula dias úteis no período
            $query_dias_uteis_periodo = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$start_data_invertida' and '$end_data_invertida' ");
            $count_dias = mysqli_fetch_array($query_dias_uteis_periodo);
            echo "<td>".$count_dias['dias_uteis_periodo']."</td>";


            //multiplica horas por dias úteis
            $dias_a_trabalhar = multiplicacao_horas($result_sum['horas_diarias'],$count_dias['dias_uteis_periodo']);

            echo "<td>".$dias_a_trabalhar."</td>";

        }else {

            $query_busca_horas_d_antigas_ver = mysqli_query($con,"SELECT * FROM last_hour where id_login = '$id_func' order by data_ate asc");
            echo "<td>";

            //mostrar alteraçoes feitas nas horas diarias
            while($result_achou_h_antiga_ver = mysqli_fetch_array($query_busca_horas_d_antigas_ver)) {
                //convert data
                $data_ate_antiga = explode("-", $result_achou_h_antiga_ver['data_ate']);
                list($ano,$mes,$dia) = $data_ate_antiga;
                $data_antiga_invertida = "$dia/$mes/$ano";

                echo $result_achou_h_antiga_ver['h_antiga']." áte ".$data_antiga_invertida."</br>";
            }

            echo $result_sum['horas_diarias']." atual</br>";
            echo "</td>";


            $query_busca_horas_antigas = mysqli_query($con,"SELECT * FROM last_hour where id_login = '$id_func' order by data_ate asc");

            $primeira_data = $start_data_invertida;
            $ultima_data = $start_data_invertida;
            $dias_a_trabalhar = "00:00:00";
            $dias_u = 0;
            $h_a_trabalhar = "00:00:00";
            $aux = "FALSE";


            while($result_achou_h_antiga = mysqli_fetch_array($query_busca_horas_d_antigas)){

                if($aux == 'FALSE'){

                    if($result_achou_h_antiga['data_ate'] >= $start_data_invertida && $result_achou_h_antiga['data_ate'] <= $end_data_invertida){

                        $ultima_data = $result_achou_h_antiga['data_ate'];
                        
                        //calcula dias úteis no período
                        $query_dias_uteis_periodo = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$primeira_data' and '$ultima_data' ");
                        $count_dias = mysqli_fetch_array($query_dias_uteis_periodo);

                        $dias_u += $count_dias['dias_uteis_periodo'];
                        
                        $day_work = $dias_a_trabalhar;
                        
                        //multiplica horas por dias úteis
                        $h_a_trabalhar = multiplicacao_horas($result_achou_h_antiga['h_antiga'],$count_dias['dias_uteis_periodo']);

                                              
                        $primeira_data = date('Y-m-d', strtotime($ultima_data.' +1 day'));

                        $dias_a_trabalhar = sum_the_time2($day_work,$h_a_trabalhar);

                        


                    }else if($result_achou_h_antiga['data_ate'] > $end_data_invertida){


                        $query_dias_uteis_periodo = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$primeira_data' and '$end_data_invertida' ");
                        $count_dias = mysqli_fetch_array($query_dias_uteis_periodo);


                        $primeira_data = $end_data_invertida;

                        $ultima_data = "end";

                        $dias_u += $count_dias['dias_uteis_periodo'];

                        $day_work = $dias_a_trabalhar;
                        
                        //multiplica horas por dias úteis
                        $h_a_trabalhar = multiplicacao_horas($result_achou_h_antiga['h_antiga'],$count_dias['dias_uteis_periodo']);

                        
                        $dias_a_trabalhar = sum_the_time2($day_work,$h_a_trabalhar);
                        
                        $aux = "TRUE";

                    }



                }

              
            }


            
            
            if($ultima_data != "end"){
                if($ultima_data != $start_data_invertida){
                   $ultima_data = date('Y-m-d', strtotime($ultima_data.' +1 day'));
                }    

                $query_dias_uteis_periodo = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$ultima_data' and '$end_data_invertida' ");
                $count_dias = mysqli_fetch_array($query_dias_uteis_periodo);

                $dias_u += $count_dias['dias_uteis_periodo'];
            

                //multiplica horas por dias úteis
                $dias_a_trabalhar_f = multiplicacao_horas($result_sum['horas_diarias'],$count_dias['dias_uteis_periodo']);

                $dias_a_trabalhar = sum_the_time2($dias_a_trabalhar,$dias_a_trabalhar_f);


            }


            echo "<td>".$dias_u."</td>";
            echo "<td>".$dias_a_trabalhar."</td>";

        }
        
        //fecha teste se foi alterado as horas diárias do funcionario


        //Soma horas de projetos
        $array_soma_horas_proj = array('00:00');
        $query_h_adm = mysqli_query($con,"SELECT folha_proj.hora_total AS total_h FROM folha_proj where folha_proj.id_func = '$id_func' and folha_proj.data BETWEEN '$start_data_invertida' and '$end_data_invertida' ")or die(mysqli_error());
        while($result_h_adm = mysqli_fetch_array($query_h_adm)){
            $array_soma_horas_proj.array_push($array_soma_horas_proj,$result_h_adm['total_h']);

        }
        $result_sum_time_proj = sum_the_time($array_soma_horas_proj);

         //Soma horas administrativas
        $array_soma_horas = array('00:00');
        $query_sum = mysqli_query($con,"SELECT folha_adm.hora_total AS total_h FROM folha_adm where folha_adm.id_func = '$id_func' and folha_adm.data BETWEEN '$start_data_invertida' and '$end_data_invertida' ")or die(mysqli_error());
        while($result_sum = mysqli_fetch_array($query_sum)){
            $array_soma_horas.array_push($array_soma_horas,$result_sum['total_h']);

        }
        $result_sum_time_adm = sum_the_time($array_soma_horas);

        //trabalhado
        $array_soma_total = array($result_sum_time_proj,$result_sum_time_adm);

        $trabalhado = sum_the_time($array_soma_total);
        echo "<td>".$trabalhado."</td>";
        //em projetos
        echo "<td>".$result_sum_time_proj."</td>";
        //no administrativo
        echo "<td>".$result_sum_time_adm."</td>";


        //ferias e atestados baseados no periodo de busca

        $calculo_h_e_ferias = '00:00:00';
        $result_calculo_h_e_ferias = '00:00:00';

        $query_busca_ferias_atestados_entre = mysqli_query($con,"SELECT data_inicio,data_fim,tipo,hora_diaria FROM ferias_atestados where (id_func = '$id_func' and tipo != 3 and data_inicio BETWEEN '$start_data_invertida' and '$end_data_invertida') or (id_func = '$id_func' and tipo != 3 and data_fim BETWEEN '$start_data_invertida' and '$end_data_invertida')  ");
        $result_row_busca_ferias_atestados_entre = mysqli_num_rows($query_busca_ferias_atestados_entre);

        
        if($result_row_busca_ferias_atestados_entre > 0){
            $d_uteis_ferias = 0;
            $dias_totais = 0;
            while($result_array_ferias_entre = mysqli_fetch_array($query_busca_ferias_atestados_entre)){
                $data_inicio_p_ferias_entre = $result_array_ferias_entre['data_inicio'];
                $data_fim_p_ferias_entre = $result_array_ferias_entre['data_fim'];
                $hora_diaria = $result_array_ferias_entre['hora_diaria'];

              
                //dias totais entre as datas
                $datepicker_inicio = new DateTime($start_data_invertida);
                $datepicker_fim = new DateTime($end_data_invertida);
                $retorno_ferias_inicio = new DateTime($data_inicio_p_ferias_entre);
                $retorno_ferias_fim = new DateTime($data_fim_p_ferias_entre);
                
                
                if (($retorno_ferias_inicio >= $datepicker_inicio) && ($retorno_ferias_fim <= $datepicker_fim) ){
                    //  pega os dias corridos  
                    $dateInterval = $retorno_ferias_inicio->diff($retorno_ferias_fim);
                    $dias_totais += $dateInterval->days+1; 
                                         
                    //calcula dias úteis no período
                    $query_dias_uteis_periodo_pferias = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$data_inicio_p_ferias_entre' and '$data_fim_p_ferias_entre' ");
                    $count_dias_ferias_entre = mysqli_fetch_array($query_dias_uteis_periodo_pferias);
                    
                        
                    $count_dia = $count_dias_ferias_entre['dias_uteis_periodo'];
            
                    $calculo_h_e_ferias = multiplicacao_horas($hora_diaria,$count_dia);
                    
                    $result_calculo_h_e_ferias = sum_the_time2($result_calculo_h_e_ferias,$calculo_h_e_ferias);   

                    $d_uteis_ferias += $count_dias_ferias_entre['dias_uteis_periodo'];

                                          
                    

                }else if(($retorno_ferias_fim > $datepicker_fim) && ($retorno_ferias_inicio >= $datepicker_inicio) && ($retorno_ferias_inicio <= $datepicker_fim)){
                    
                    $dateInterval = $retorno_ferias_inicio->diff($datepicker_fim);
                    $dias_totais = $dias_totais + $dateInterval->days+1;
                    
                     //calcula dias úteis no período
                     $query_dias_uteis_periodo_pferias = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$data_inicio_p_ferias_entre' and '$end_data_invertida' ");
                     $count_dias_ferias_entre = mysqli_fetch_array($query_dias_uteis_periodo_pferias);
                     
                        $count_dia = $count_dias_ferias_entre['dias_uteis_periodo'];
            
                        $calculo_h_e_ferias = multiplicacao_horas($hora_diaria,$count_dia);
                        
                        $result_calculo_h_e_ferias = sum_the_time2($result_calculo_h_e_ferias,$calculo_h_e_ferias);   

                        $d_uteis_ferias += $count_dias_ferias_entre['dias_uteis_periodo'];

                                        
                }else if(($retorno_ferias_inicio < $datepicker_inicio) && ($retorno_ferias_fim <= $datepicker_fim) && ($retorno_ferias_fim >= $datepicker_inicio)){
                    
                    $dateInterval = $datepicker_inicio->diff($retorno_ferias_fim);
                    $dias_totais = $dias_totais + $dateInterval->days+1;
                    
                     //calcula dias úteis no período
                     $query_dias_uteis_periodo_pferias = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$start_data_invertida' and '$data_fim_p_ferias_entre' ");
                     $count_dias_ferias_entre = mysqli_fetch_array($query_dias_uteis_periodo_pferias);
                        
                        $count_dia = $count_dias_ferias_entre['dias_uteis_periodo'];
            
                        $calculo_h_e_ferias = multiplicacao_horas($hora_diaria,$count_dia);
                        
                        $result_calculo_h_e_ferias = sum_the_time2($result_calculo_h_e_ferias,$calculo_h_e_ferias);   

                        $d_uteis_ferias += $count_dias_ferias_entre['dias_uteis_periodo'];
                     
                                        
                }

            }


        }else {
            
            //verifica se periodo escolhido datepicker esta entre algumas ferias
            $query_busca_periodo_entre_encontrado = mysqli_query($con,"SELECT data_inicio,data_fim,tipo,hora_diaria FROM ferias_atestados where (id_func = '$id_func' and tipo != 3 and '$start_data_invertida' between data_inicio and data_fim) or (id_func = '$id_func' and tipo != 3 and '$end_data_invertida' between data_inicio and data_fim) ");
            $result_periodo_entre_encontrado = mysqli_num_rows($query_busca_periodo_entre_encontrado);

            

            if ($result_periodo_entre_encontrado > 0) {
                $d_uteis_ferias = 0;
                $dias_totais = 0;
                while ($result_array_periodo_entre_encontrado = mysqli_fetch_array($query_busca_periodo_entre_encontrado)) {

                    //dias totais entre as datas
                    $data_inicio_tot = new DateTime($result_array_periodo_entre_encontrado['data_inicio']);
                    $data_fim_tot = new DateTime($result_array_periodo_entre_encontrado['data_fim']);

                    $dateInterval = $data_inicio_tot->diff($data_fim_tot);
                    $dias_totais = $dias_totais + $dateInterval->days+1;

                    $hora_diaria = $result_array_periodo_entre_encontrado['hora_diaria'];
                    

                    $query_dias_uteis_periodo_piniciofim = mysqli_query($con,"SELECT count(dia) as dias_uteis_periodo FROM dias_uteis where dia BETWEEN '$start_data_invertida' and '$end_data_invertida' ");
                    $count_dias_ferias_iniciofim = mysqli_fetch_array($query_dias_uteis_periodo_piniciofim);

                    $count_dia = $count_dias_ferias_iniciofim['dias_uteis_periodo'];
            
                    $calculo_h_e_ferias = multiplicacao_horas($hora_diaria,$count_dia);
                    
                    $result_calculo_h_e_ferias = sum_the_time2($result_calculo_h_e_ferias,$calculo_h_e_ferias);   

                    $d_uteis_ferias += $count_dias_ferias_entre['dias_uteis_periodo'];

                }


            } 
            
            
        }


        //tratamento da informação de dayoff

         $dayoff_final = '';

        $query_busca_dayoff = mysqli_query($con,"SELECT data_inicio,data_fim,tipo,hora_diaria FROM ferias_atestados where id_func = '$id_func' and tipo = 3 ");
        $result_busca_dayoff = mysqli_num_rows($query_busca_dayoff);

            if($result_busca_dayoff > 0){

                while($result_encontrou_dayoff = mysqli_fetch_array($query_busca_dayoff)){

                    $data_inicio =  $result_encontrou_dayoff['data_inicio'];

                    $data_iniciol = explode("-",  $data_inicio);
                    list($ano,$mes,$dia) = $data_iniciol;
                    $data_inicio_n = "$dia/$mes/$ano";

                    $data_fim =  $result_encontrou_dayoff['data_fim'];

                    $data_fiml = explode("-",  $data_fim);
                    list($ano,$mes,$dia) = $data_fiml;
                    $data_fim_n = "$dia/$mes/$ano";



                    //dayoff esta entre periodo de busca no datepicker
                    if(($data_inicio >= $start_data_invertida && $data_inicio <= $end_data_invertida) && ($data_fim >= $start_data_invertida && $data_fim <= $end_data_invertida)){

                        if($data_inicio_n == $data_fim_n){

                            $dayoff_final = $dayoff_final.' '.$data_inicio_n.'</br>';

                        }else {

                            $dayoff_final = $dayoff_final.' '.$data_inicio_n." a ".$data_fim_n.'</br>';
                        }

                        

                    }
                    //periodo de busca esta entre dayoff
                    else if(($start_data_invertida >= $data_inicio && $start_data_invertida <= $data_fim) && ($end_data_invertida >= $data_inicio && $end_data_invertida <= $data_fim)){ 
                        
                        if($data_inicio_n == $data_fim_n){

                            $dayoff_final = $dayoff_final.' '.$data_inicio_n.'</br>';

                        }else {

                            $dayoff_final = $dayoff_final.' '.$data_inicio_n." a ".$data_fim_n.'</br>';
                        }

                    }
                    //ou inicio ou fim esta entre o periodo de busca
                    else if(($data_inicio >= $start_data_invertida && $data_inicio <= $end_data_invertida) || ($data_fim >= $start_data_invertida && $data_fim <= $end_data_invertida)){

                        if($data_inicio_n == $data_fim_n){

                            $dayoff_final = $dayoff_final.' '.$data_inicio_n.'</br>';

                        }else {

                            $dayoff_final =  $dayoff_final.' '.$data_inicio_n." a ".$data_fim_n.'</br>';
                        }

                    }     

            }            


        }
        



        //fecha tratamento da informação de dayoff




        if($result_calculo_h_e_ferias == '00:00:00'){
            //saldo
            $saldo = diferenca_horas($dias_a_trabalhar,$trabalhado);

            echo "<td>".$result_calculo_h_e_ferias."</td>";
            echo "<td>".$dayoff_final." </td>";
            echo "<td>".$saldo."</td>";

        }else {

            //saldo
            $trabalhado_menos_ferias = diferenca_horas($result_calculo_h_e_ferias,$dias_a_trabalhar);
            $saldo = diferenca_horas($trabalhado_menos_ferias,$trabalhado);


            echo "<td> Horas: ".$result_calculo_h_e_ferias."</br> Dias úteis: ".$d_uteis_ferias."</td>";
            echo "<td>".$dayoff_final."</td>";
           // $saldo_ferias = $saldo;
            echo "<td>".$saldo."</td>";

        }


        echo "</tr>";


        echo '

        </tbody>
        </table>';

        echo "</br></br>";



        echo '
    <table id="tab_rel" class="table table-striped table-bordered" width="100%" cellspacing="0">
    <thead>
        <tr>
            <th>Funcionário</th>
            <th>Data</th>
            <th>Administrativo</th>
            <th>Hora inicial</th>
            <th>Hora Final</th>
            <th>Hora total</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
    
    ';

        $query = "SELECT folha_adm.id, funcionario.nome as funcionario,folha_adm.data as data, horas_adm.nome as adm_nome, folha_adm.h_inicial, folha_adm.h_final, folha_adm.hora_total as hora_total FROM folha_adm INNER JOIN funcionario on folha_adm.id_func = funcionario.id INNER join horas_adm on folha_adm.id_hora_adm = horas_adm.id where folha_adm.data BETWEEN '$start_data_invertida' and '$end_data_invertida' and folha_adm.id_func = '$id_func' ORDER BY folha_adm.id DESC";
        $exec_query = mysqli_query($con,$query);
        while($result = mysqli_fetch_array($exec_query)){
            echo "<tr>";
            echo "<td>".$result['funcionario']."</td>";

            $result['data'] = explode("-", $result['data']);
            list($ano,$mes,$dia) = $result['data'];
            $end_data_show = "$dia/$mes/$ano";

            echo "<td>".$end_data_show."</td>";
            echo "<td>".$result['adm_nome']."</td>";
            echo "<td>".$result['h_inicial']."</td>";
            echo "<td>".$result['h_final']."</td>";
            echo "<td>".$result['hora_total']."</td>";
            echo "<td><button type='button' id='".$result['id']."' class='btn_editar_registro_adm btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Editar'><span class='glyphicon glyphicon-pencil'></span></button>&nbsp&nbsp&nbsp<button type='button' id='".$result['id']."' class='btn_excluir_registro_adm btn btn-default btn-xs  data-toggle='tooltip' data-placement='top' title='Excluir'><span class='glyphicon glyphicon-remove'></span></button></td>";

            echo "</tr>";
        }


        echo '
        
        </tbody>
        </table>';
        break;

    case "excluir_registro_adm":
        $id = $_POST['id'];
        $query = mysqli_query($con,"delete from folha_adm where id = '$id'");
        break;


    case "excluir_registro":
        $id = $_POST['id'];
        $query = mysqli_query($con,"delete from folha_proj where id = '$id'");
        break;

    case "edit_reg":
        $id = $_POST['id'];
        $array_busca = array();

        $query = mysqli_query($con,"select id, id_func, data, id_proj, id_etapa, id_subetapa,h_inicial, h_final, hora_total from folha_proj where id = '$id' ")or die(mysqli_error());
        while($result = mysqli_fetch_array($query)){
            $array_busca['id'] = $result['id'];
            $array_busca['id_func'] = $result['id_func'];
            $result['data'] = explode("-", $result['data']);
            list($ano,$mes,$dia) = $result['data'];
            $end_data_show = "$dia/$mes/$ano";

            $array_busca['data'] = $end_data_show;

            $array_busca['hora_total'] = $result['hora_total'];
            $array_busca['h_inicial'] = $result['h_inicial'];
            $array_busca['h_final'] = $result['h_final'];

            //dados select projetos
            $id_proj = $result['id_proj'];
            $array_proj = array();

            $query_proj = mysqli_query($con,"select id,cod_projeto, nome from projeto");
            while($result_proj = mysqli_fetch_array($query_proj)){

                if ($result_proj['id'] == $id_proj){
                    $array_proj[] = '<option value='.$result_proj['id'].' selected>'.$result_proj['cod_projeto']." ".$result_proj['nome'].'</option>';
                } else {

                    $array_proj[] = '<option value='.$result_proj['id'].'>'.$result_proj['cod_projeto']." ".$result_proj['nome'].'</option>';

                }

            }

            $array_busca['select_proj'] = $array_proj;
            // fecha dados select projetos

            //dados select etapas
            $id_etapa = $result['id_etapa'];
            $array_etapa = array();

                $query_etapa = mysqli_query($con,"select id, nome from etapa");
                while ($result_etapa = mysqli_fetch_array($query_etapa)) {

                    if ($result_etapa['id'] == $id_etapa) {
                        $array_etapa[] = '<option value=' . $result_etapa['id'] . ' selected>' . $result_etapa['nome'] . '</option>';
                    } else {

                        $array_etapa[] = '<option value=' . $result_etapa['id'] . '>' . $result_etapa['nome'] . '</option>';

                    }

                }


            $array_busca['select_etapa'] = $array_etapa;
            // fecha dados select etapas

            //dados select subetapas
            $id_subetapa = $result['id_subetapa'];
            $array_subetapa = array();

                $query_subetapa = mysqli_query($con,"select id, nome from sub_etapa where id_etapa = '$id_etapa'");
                while ($result_subetapa = mysqli_fetch_array($query_subetapa)) {

                    if ($result_subetapa['id'] == $id_subetapa) {
                        $array_subetapa[] = '<option value=' . $result_subetapa['id'] . ' selected>' . $result_subetapa['nome'] . '</option>';
                    } else {

                        $array_subetapa[] = '<option value=' . $result_subetapa['id'] . '>' . $result_subetapa['nome'] . '</option>';

                    }

                }


            $array_busca['select_subetapa'] = $array_subetapa;
            // fecha dados select subetapas


        }

        echo json_encode($array_busca); //encode json para os dados do ajax

        break;

    case "busca_subetapa_edit":
        echo "<option value='default' disabled selected>Selecione a subetapa do projeto</option>";
        $select_etapa = $_POST['etapa'];
        $query = ("Select id,nome,id_etapa from sub_etapa where id_etapa = '$select_etapa'")or die(mysqli_error());
        $consulta = mysqli_query($con,$query);

        while ($array = mysqli_fetch_array($consulta)) {
            echo "<option value='".$array['id']."'>".$array['nome']."</option>";
        }

        break;

    case "edit_new_reg":
       
        $id = $_POST['id_reg'];
        $id_func = $_POST['id_func'];
        //converte data banco
        $data = $_POST['data'];
        $data = explode("/", $data);
        list($dia,$mes,$ano) = $data;
        $data_invertida = "$ano-$mes-$dia";
        $id_projeto = $_POST['id_projeto'];
        $id_etapa = $_POST['id_etapa'];
        $id_subetapa = $_POST['id_subetapa'];
        $h_inicial = $_POST['h_inicial'];
        $h_final = $_POST['h_final'];
        //calculo h inicial e final p banco
        $hora_inicial = date_create_from_format('H:i', $h_inicial);

        $hora_final = date_create_from_format('H:i', $h_final);

        $intervalo = $hora_inicial->diff($hora_final);

        $total_horas = $intervalo->format('%H:%I');

        $data_hoje = date("Y-m-d");

        //verifica se existe conflito de horas
        $exist_conflito = 0;

            //testa horas de projetos
            $query_valida_insercao = mysqli_query($con,"SELECT id, h_inicial, h_final FROM folha_proj WHERE id_func = '$id_func' and data = '$data_invertida'");
            while ($result_valida_isercao = mysqli_fetch_array($query_valida_insercao)) {
               if($result_valida_isercao['id'] != $id) {
                   $h_inicial_banco = strtotime($result_valida_isercao['h_inicial']);
                   $h_final_banco = strtotime($result_valida_isercao['h_final']);
                   if ($h_inicial_banco >= strtotime($h_inicial) && $h_final_banco <= strtotime($h_final) || (strtotime($h_inicial) >= $h_inicial_banco && strtotime($h_inicial) < $h_final_banco) || strtotime($h_final) > $h_inicial_banco && strtotime($h_final) <= $h_final_banco) {
                       $exist_conflito++;
                   }
               }

            }
            //testa horas administrativas
            $query_valida_insercao = mysqli_query($con,"SELECT h_inicial, h_final FROM folha_adm WHERE id_func = '$id_func' and data = '$data_invertida'");
            while ($result_valida_isercao = mysqli_fetch_array($query_valida_insercao)) {
                $h_inicial_banco = strtotime($result_valida_isercao['h_inicial']);
                $h_final_banco = strtotime($result_valida_isercao['h_final']);
                if ($h_inicial_banco >= strtotime($h_inicial) && $h_final_banco <= strtotime($h_final) || strtotime($h_inicial) >= $h_inicial_banco && strtotime($h_inicial) < $h_final_banco || strtotime($h_final) > $h_inicial_banco && strtotime($h_final) <= $h_final_banco) {
                    $exist_conflito++;
                }

            }


        if($exist_conflito == 0) {

            //calcula taxa administrativa (hora_total * valor_hora)+(hora_total * valor_hora)*tx_adm
            $query_busca_tx_adm_proj_update = mysqli_query($con,"select taxa_adm from projeto where id='$id_projeto'")or die(mysqli_error());
            while($result_busca_tx_adm_proj_update = mysqli_fetch_array($query_busca_tx_adm_proj_update)){
                $tx_adm_proj_update = $result_busca_tx_adm_proj_update['taxa_adm'];
            }

            //pega valor_hora vindo do registro da tabela folha_proj
            $query_busca_v_hora_registro = mysqli_query($con,"select valor_hora from folha_proj where id='$id'")or die(mysqli_error());
            $result_busca_v_hora_registro = mysqli_fetch_array($query_busca_v_hora_registro);
            $valor_hora_update = $result_busca_v_hora_registro['valor_hora'];

            $valor_tot_update = custo_horas($total_horas,$valor_hora_update);

            $calc_tx_adm_update = $valor_tot_update + ($valor_tot_update*$tx_adm_proj_update);


            $query = mysqli_query($con,"update folha_proj set data='$data_invertida',id_proj='$id_projeto',id_etapa='$id_etapa',val_tx_adm='$calc_tx_adm_update',id_subetapa='$id_subetapa',h_inicial='$h_inicial',h_final='$h_final',hora_total='$total_horas',data_insercao='$data_hoje' where id='$id'") or die(mysqli_error());

            if ($query) {
                echo "0";
            } else {
                echo "1";
            }
        }else {
            echo "2";
        }

        break;

    case "edit_reg_adm":
        $id = $_POST['id'];
        $array_busca = array();

        $query = mysqli_query($con,"select id, id_func, data, id_hora_adm, h_inicial, h_final, hora_total from folha_adm where id = '$id' ")or die(mysqli_error());
        while($result = mysqli_fetch_array($query)){
            $array_busca['id'] = $result['id'];
            $array_busca['id_func'] = $result['id_func'];
            $result['data'] = explode("-", $result['data']);
            list($ano,$mes,$dia) = $result['data'];
            $end_data_show = "$dia/$mes/$ano";
            $array_busca['data'] = $end_data_show;
            $array_busca['h_inicial'] = $result['h_inicial'];
            $array_busca['h_final'] = $result['h_final'];
            //dados select adm
            $id_adm = $result['id_hora_adm'];
            $array_adm = array();

            $query_adm = mysqli_query($con,"select id, nome from horas_adm");
            while($result_adm = mysqli_fetch_array($query_adm)){

                if ($result_adm['id'] == $id_adm){
                    $array_adm[] = '<option value='.$result_adm['id'].' selected>'.$result_adm['nome'].'</option>';
                } else {

                    $array_adm[] = '<option value='.$result_adm['id'].'>'.$result_adm['nome'].'</option>';

                }

            }

            $array_busca['select_adm'] = $array_adm;
            echo json_encode($array_busca); //encode json para os dados do ajax

        }


        break;

    case "edit_new_adm":
        $id = $_POST['id'];
        $id_func = $_POST['id_func'];
        //converte data banco
        $data = $_POST['data'];
        $data = explode("/", $data);
        list($dia,$mes,$ano) = $data;
        $data_invertida = "$ano-$mes-$dia";
        $id_adm = $_POST['adm'];
        $inicio_atividade_adm = $_POST['inicio_atividade_adm'];
        $fim_atividade_adm = $_POST['fim_atividade_adm'];

        //calculo h inicial e final p banco
        $hora_inicial = date_create_from_format('H:i', $inicio_atividade_adm);

        $hora_final = date_create_from_format('H:i', $fim_atividade_adm);

        $intervalo = $hora_inicial->diff($hora_final);

        $total_horas = $intervalo->format('%H:%I');

        $data_hoje = date("Y-m-d");

        //verifica se existe conflito de horas
        $exist_conflito = 0;

            //testa horas de projetos
            $query_valida_insercao = mysqli_query($con,"SELECT h_inicial, h_final FROM folha_proj WHERE id_func = '$id_func' and data = '$data_invertida'");
            while ($result_valida_isercao = mysqli_fetch_array($query_valida_insercao)) {

                    $h_inicial_banco = strtotime($result_valida_isercao['h_inicial']);
                    $h_final_banco = strtotime($result_valida_isercao['h_final']);
                    if ($h_inicial_banco >= strtotime($inicio_atividade_adm) && $h_final_banco <= strtotime($fim_atividade_adm) || strtotime($inicio_atividade_adm) >= $h_inicial_banco && strtotime($inicio_atividade_adm) < $h_final_banco || strtotime($fim_atividade_adm) > $h_inicial_banco && strtotime($fim_atividade_adm) <= $h_final_banco) {
                        $exist_conflito++;
                    }

            }
            //testa horas administrativas
            $query_valida_insercao = mysqli_query($con,"SELECT id, h_inicial, h_final FROM folha_adm WHERE id_func = '$id_func' and data = '$data_invertida'");
            while ($result_valida_isercao = mysqli_fetch_array($query_valida_insercao)) {
                if($result_valida_isercao['id'] != $id) {
                    $h_inicial_banco = strtotime($result_valida_isercao['h_inicial']);
                    $h_final_banco = strtotime($result_valida_isercao['h_final']);
                    if ($h_inicial_banco >= strtotime($inicio_atividade_adm) && $h_final_banco <= strtotime($fim_atividade_adm) || strtotime($inicio_atividade_adm) >= $h_inicial_banco && strtotime($inicio_atividade_adm) < $h_final_banco || strtotime($fim_atividade_adm) > $h_inicial_banco && strtotime($fim_atividade_adm) <= $h_final_banco) {
                        $exist_conflito++;
                    }
                }
            }


        if($exist_conflito == 0) {

            $query = mysqli_query($con,"update folha_adm set data='$data_invertida',id_hora_adm='$id_adm',h_inicial='$inicio_atividade_adm',h_final='$fim_atividade_adm',hora_total='$total_horas',data_insercao='$data_hoje' where id='$id'") or die(mysqli_error());

            if ($query) {
                echo "0";
            } else {
                echo "1";
            }
        }else {
            echo "2";
        }

        break;

    //FECHA RELATORIO USUARIO

    case "insere_dias_uteis":
        $dias = $_POST['dias'];
        $dias = preg_replace('/\s+/', '', $dias);

        $array_dias = explode(",", $dias);
            $aux_error = 0;

            $mes_atual = date("Y-m");
            $query_remove = mysqli_query($con,"delete from dias_uteis where dia like '$mes_atual%'");
            if ($query_remove){
                for($i = 0; $i < count($array_dias); $i++){
                    $array_dias[$i] = explode("/", $array_dias[$i]);
                    list($dia,$mes,$ano) = $array_dias[$i];
                    $array_dias[$i] = "$ano-$mes-$dia"; //inverter data pro banco
                    $dia_banco = $array_dias[$i];
                    $query = mysqli_query($con,"insert into dias_uteis (dia) values ('$dia_banco')")or die(mysqli_error());

                }
            }

    break;

    case "insert_new_p_ferias":
        session_start();
        $id_func = $_SESSION["id_func"];
        $data_inicio = $_POST['data_inicio'];
        $data_inicio = explode("/", $data_inicio);
        list($dia,$mes,$ano) = $data_inicio;
        $data_invertida_inicio = "$ano-$mes-$dia"; //inverter data pro banco
        $data_fim = $_POST['data_fim'];
        $data_fim = explode("/", $data_fim);
        list($dia,$mes,$ano) = $data_fim;
        $data_invertida_fim = "$ano-$mes-$dia"; //inverter data pro banco
        $select_ferias_atestados = $_POST["select_ferias_atestados"];
        

        $query_busca_hdiariafunc = mysqli_query($con,"select horas_diarias from funcionario where id = '$id_func'");
        $result_busca_hdiariafunc = mysqli_fetch_array($query_busca_hdiariafunc);
        $horas_diarias = $result_busca_hdiariafunc['horas_diarias'];

        
        //busca se existe periodo de ferias etc já cadastrado
        $query_busca_exists = mysqli_query($con,"SELECT data_inicio, data_fim,id_func FROM ferias_atestados where ('$data_invertida_inicio' between data_inicio and data_fim and id_func = '$id_func') or ('$data_invertida_fim' between data_inicio and data_fim and id_func = '$id_func')");
        
        $result_busca_exists = mysqli_num_rows($query_busca_exists);
        if($result_busca_exists == 0) {

            $query_busca_exists2 = mysqli_query($con,"SELECT data_inicio, data_fim,id_func FROM ferias_atestados where (data_inicio between '$data_invertida_inicio' and '$data_invertida_fim' and id_func = '$id_func') or (data_fim between '$data_invertida_inicio' and '$data_invertida_fim' and id_func = '$id_func')");

            $result_busca_exists2 = mysqli_num_rows($query_busca_exists2);

            if($result_busca_exists2 == 0) {

                    $query = mysqli_query($con,"insert into ferias_atestados (data_inicio, data_fim,hora_diaria, id_func, tipo) value ('$data_invertida_inicio','$data_invertida_fim','$horas_diarias','$id_func','$select_ferias_atestados')") or die(mysqli_error());
                    if ($query) {
                        echo "0";
                    } else {
                        echo "1";
                    }
            }else {
                echo "2";
            }        

        }else {
            echo "2";
        }

        break;
    case "insert_new_p_atestado":
        session_start();
        $id_func = $_SESSION["id_func"];
        $data_inicio = $_POST['data_inicio'];
        $data_inicio = explode("/", $data_inicio);
        list($dia,$mes,$ano) = $data_inicio;
        $data_invertida_inicio = "$ano-$mes-$dia"; //inverter data pro banco
        $data_fim = $_POST['data_fim'];
        $data_fim = explode("/", $data_fim);
        list($dia,$mes,$ano) = $data_fim;
        $data_invertida_fim = "$ano-$mes-$dia"; //inverter data pro banco
        
        $query_busca_hdiariafunc = mysqli_query($con,"select horas_diarias from funcionario where id_func = '$id_func'");
        $result_busca_hdiariafunc = mysqli_fetch_array($query_busca_hdiariafunc);
        $horas_diarias = $result_busca_hdiariafunc['horas_diarias'];

        $query_busca_exists = mysqli_query($con,"select data_inicio, data_fim, id_func from ferias_atestados where data_inicio = '$data_invertida_inicio' and data_fim = '$data_invertida_fim' and id_func = '$id_func'");
        $result_busca_exists = mysqli_num_rows($query_busca_exists);
        if($result_busca_exists == 0) {

            $query = mysqli_query($con,"insert into ferias_atestados (data_inicio, data_fim,horas_diarias, id_func,tipo) value ('$data_invertida_inicio','$data_invertida_fim','$horas_diarias','$id_func','2')") or die(mysqli_error());
            if ($query) {
                echo "0";
            } else {
                echo "1";
            }
        }else {
            echo "2";
        }

        break;

    //ferias edit delete
    case "excluir_ferias":
        $id = $_POST['id'];
        $query = mysqli_query($con,"delete from ferias_atestados where id = '$id'");
        break;

  
        case "edit_ferias":
            $id = $_POST['id'];
            $array_busca = array();
    
            $query = mysqli_query($con,"select id, data_inicio, data_fim, tipo from ferias_atestados where id = '$id' ")or die(mysqli_error());
            while($result = mysqli_fetch_array($query)){
                $array_busca['id'] = $result['id'];
    
                $result['data_inicio'] = explode("-", $result['data_inicio']);
                list($ano,$mes,$dia) = $result['data_inicio'];
                $data_inicio = "$dia/$mes/$ano";
    
                $array_busca['data_inicio'] = $data_inicio;
    
                $result['data_fim'] = explode("-", $result['data_fim']);
                list($ano,$mes,$dia) = $result['data_fim'];
                $data_fim = "$dia/$mes/$ano";
    
                $array_busca['data_fim'] = $data_fim;
                $array_busca['tipo'] = $result['tipo'];
    
            }
    
            echo json_encode($array_busca); //encode json para os dados do ajax
        break;

        case "edit_new_ferias":
            session_start();
            $id_func = $_SESSION["id_func"];
            $id = $_POST['id'];
            $data_inicio = $_POST['data_inicio'];
            //converte data banco
            $data = explode("/", $data_inicio);
            list($dia,$mes,$ano) = $data;
            $data_inicio_invertida = "$ano-$mes-$dia";
    
            $data_fim = $_POST['data_fim'];
            //converte data banco
            $data = explode("/", $data_fim);
            list($dia,$mes,$ano) = $data;
            $data_fim_invertida = "$ano-$mes-$dia";
            $tipo = $_POST['tipo'];
    
                //busca se existe periodo de ferias etc já cadastrado
              
                $query_busca_exists = mysqli_query($con,"SELECT data_inicio, data_fim,id_func FROM ferias_atestados where ('$data_inicio_invertida' between data_inicio and data_fim and id_func = '$id_func') or ('$data_fim_invertida' between data_inicio and data_fim and id_func = '$id_func')");
            
                $result_busca_exists = mysqli_num_rows($query_busca_exists);
                if($result_busca_exists == 0) {
        
                    $query_busca_exists2 = mysqli_query($con,"SELECT data_inicio, data_fim,id_func FROM ferias_atestados where (data_inicio between '$data_inicio_invertida' and '$data_fim_invertida' and id_func = '$id_func') or (data_fim between '$data_inicio_invertida' and '$data_fim_invertida' and id_func = '$id_func')");
        
                    $result_busca_exists2 = mysqli_num_rows($query_busca_exists2);
        
                    if($result_busca_exists2 == 0) {
        
                        $query = mysqli_query($con,"update ferias_atestados set data_inicio='$data_inicio_invertida',data_fim='$data_fim_invertida', tipo='$tipo'  where id='$id'") or die(mysqli_error());
                        if ($query) {
                                echo "0";
                            } else {
                                echo "1";
                            }
                    }else {
                        echo "2";
                    }        
        
                }else {
                    echo "2";
                }
    
            break;

    case "relatorio_ferias":
        $start_data = $_POST["start_data"];
        $end_data = $_POST["end_data"];
        $start_data = explode("/", $start_data);
        list($dia,$mes,$ano) = $start_data;
        $start_data_invertida = "$ano-$mes-$dia"; //inverter data pro banco
        $end_data = explode("/", $end_data);
        list($dia,$mes,$ano) = $end_data;
        $end_data_invertida = "$ano-$mes-$dia"; //inverter data pro banco
        $id_func = $_POST["select_rel_user"];
        //calculos e infos usuário
        echo '
    <table  class="table table-bordered" width="100%" cellspacing="0">
    <thead>
        <tr>
            <th>Funcionário</th>
            <th>Data inicio</th>
            <th>Data fim</th>
             <th>Tipo</th>
             
        </tr>
    </thead>
    <tbody>

    ';

        $query = "SELECT ferias_atestados.id, funcionario.nome, tipo_ferias_atestado.tipo, ferias_atestados.data_inicio, ferias_atestados.data_fim FROM ferias_atestados inner Join funcionario on funcionario.id = ferias_atestados.id_func inner join tipo_ferias_atestado on tipo_ferias_atestado.id = ferias_atestados.tipo where ferias_atestados.id_func = '$id_func' and data_inicio BETWEEN '$start_data_invertida' and '$end_data_invertida' or ferias_atestados.id_func = '$id_func' and data_fim BETWEEN '$start_data_invertida' and '$end_data_invertida' ORDER BY data_inicio DESC  ";
        $exec_query = mysqli_query($con,$query);
        while($result = mysqli_fetch_array($exec_query)){
            echo "<tr>";
            echo "<td>".$result['nome']."</td>";

            $result['data_inicio'] = explode("-", $result['data_inicio']);
            list($ano,$mes,$dia) = $result['data_inicio'];
            $data_inicio = "$dia/$mes/$ano";

            echo "<td>".$data_inicio."</td>";

            $result['data_fim'] = explode("-", $result['data_fim']);
            list($ano,$mes,$dia) = $result['data_fim'];
            $data_fim = "$dia/$mes/$ano";

            echo "<td>".$data_fim."</td>";
            echo "<td>".$result['tipo']."</td>";


            echo "</tr>";
        }



    echo '
    
    </tbody>
    </table>';


  break;

    case "finalizar_proj":
        $id = $_POST['id'];
        $data_hoje = date("Y-m-d");
        $query = mysqli_query($con,"update projeto set data_fim = '$data_hoje' where id = '$id' ")or die(mysqli_error());

    break;

    case "reativar_proj":
        $id = $_POST['id'];

        $query = mysqli_query($con,"update projeto set data_fim = null where id = '$id' ")or die(mysqli_error());

        break;

    case "excluir_adm":
        $id = $_POST['id'];
        $query = mysqli_query($con,"delete from horas_adm where id = '$id'");

    break;

    case "insere_new_adm":

        $nome = $_POST['nome'];


        $aux =0;
        $query = mysqli_query($con,"select nome from horas_adm");
        while ($result = mysqli_fetch_array($query)){
            if ($nome == $result['nome']){
                $aux ++;
            }
        }


        if ($aux > 0){
            echo "2";
        }else {

            $query_insert = mysqli_query($con,"insert into horas_adm (nome) values ('$nome')")or die(mysqli_error());

            if($query_insert){
                echo "0";
            }else {
                echo "1";
            }

        }

        break;

    case "edit_adm":

        $id = $_POST['id'];
        $array_busca = array();

        $query = mysqli_query($con,"select id,nome from horas_adm where id = '$id' ")or die(mysqli_error());
        while($result = mysqli_fetch_array($query)){
            $array_busca['id'] = $result['id'];
            $array_busca['nome'] = $result['nome'];
        }

        echo json_encode($array_busca); //encode json para os dados do ajax

        break;

    case "edit_novo_adm":
        $id = $_POST['id'];
        $nome = $_POST['nome'];

        $query = mysqli_query($con,"update horas_adm set nome='$nome' where id='$id'")or die(mysqli_error());

        if($query){
            echo "0";
        }else {
            echo "1";
        }

        break;

//cadastra tipo proj

    case "excluir_tipo_proj":
        $id = $_POST['id'];
        $query = mysqli_query($con,"delete from tipo_proj where id = '$id'")or die(mysqli_error());

        break;

    case "insere_new_tipo_proj":

        $tipo = $_POST['tipo'];


        $aux =0;
        $query = mysqli_query($con,"select tipo from tipo_proj");
        while ($result = mysqli_fetch_array($query)){
            if ($tipo == $result['tipo']){
                $aux ++;
            }
        }


        if ($aux > 0){
            echo "2";
        }else {

            $query_insert = mysqli_query($con,"insert into tipo_proj (tipo) values ('$tipo')")or die(mysqli_error());

            if($query_insert){
                echo "0";
            }else {
                echo "1";
            }

        }

        break;

    case "edit_tipo_proj":

        $id = $_POST['id'];
        $array_busca = array();

        $query = mysqli_query($con,"select id,tipo from tipo_proj where id = '$id' ")or die(mysqli_error());
        while($result = mysqli_fetch_array($query)){
            $array_busca['id'] = $result['id'];
            $array_busca['tipo'] = $result['tipo'];
        }

        echo json_encode($array_busca); //encode json para os dados do ajax

        break;

    case "edit_novo_tipo_proj":
        $id = $_POST['id'];
        $tipo = $_POST['tipo_proj'];

        $query = mysqli_query($con,"update tipo_proj set tipo='$tipo' where id='$id'")or die(mysqli_error());

        if($query){
            echo "0";
        }else {
            echo "1";
        }

        break;

    case "busca_projby_tipo":


        $id_tipo = $_POST['id_tipo'];

        $query_busca_proj_by_tipo = mysqli_query($con,"select id, cod_projeto, nome from projeto where id_tipo = '$id_tipo' and data_fim is null")or die(mysqli_error());

        while ($array = mysqli_fetch_array($query_busca_proj_by_tipo)) {

            echo "<option value='" . $array['id'] . "'>" . $array['cod_projeto'] . " " . $array['nome'] . " </option>";
        }

    break;

    case "rel_resumo_proj":
        $ids_projetos = $_POST['array_id_projeto']; //ids de projetos vindos de array separate por virgula

        $array_ids_projetos = explode(',', $ids_projetos);

        echo '
    <table  class="table table-bordered" width="100%" cellspacing="0">
    <thead>
        <tr>
            <th>PROJETO</th>
            <th>VALOR COBRADO</th>
            <th>CUSTO</th>
            <th>CUSTO COM TAXA ADM</th>
             <th>DESPESAS</th>
             <th>RESULTADO</th>

        </tr>
    </thead>
    <tbody>

    ';


        foreach($array_ids_projetos as $array_de_ids){

            //montar a tela do relatório iterando o $array_de_ids que sao os ides selecionados dos projetos

            $query_busca_info = mysqli_query($con,"select projeto.cod_projeto,projeto.nome,projeto.valor_proj,folha_proj.hora_total, folha_proj.valor_hora, folha_proj.val_tx_adm from folha_proj inner join projeto on projeto.id = folha_proj.id_proj where projeto.id = '$array_de_ids'")or die(mysqli_error());
            //$result_busca_info = mysqli_fetch_array($query_busca_info);

            $soma_tx_adm = '';
            $custo_tot = '';
            while($result_busca = mysqli_fetch_array($query_busca_info)){
                $projeto = $result_busca['cod_projeto'].' '.$result_busca['nome'];
                $valor = $result_busca['valor_proj'];
                $h_total = $result_busca['hora_total'];
                $v_hora = $result_busca['valor_hora'];

                //soma custo
                $custo_tot += custo_horas($h_total,$v_hora);

                //soma tx_adm
                $soma_tx_adm += $result_busca['val_tx_adm'];

            }

            echo "<tr>";
            echo "<td>".$projeto."</td>";

            echo "<td>"."R$".number_format($valor, 2, '.', '')."</td>";

            echo "<td>"."R$".number_format($custo_tot, 2, '.', '')."</td>";

            //soma val taxa adm no folha que ja esta calculado
            echo "<td>"."R$".number_format($soma_tx_adm, 2, '.', '')."</td>";

            //somar despesas

            $query_busca_despesas = mysqli_query($con,"select hora_total, valor_hora from folha_desp where id_proj = '$array_de_ids'")or die(mysqli_error());

            $soma_despesas = 00.0;
            while($result_busca_despesas = mysqli_fetch_array($query_busca_despesas)){
                $hora_total = $result_busca_despesas['hora_total'];
                $valor_hora = $result_busca_despesas['valor_hora'];

                $soma_despesas += custo_horas($hora_total,$valor_hora);

            }

            echo "<td>"."R$".number_format($soma_despesas, 2, '.', '')."</td>";

            $resultado_final = $valor - ($soma_tx_adm + $soma_despesas);

            if($resultado_final < 0){
                echo "<td><font color=\"red\">"."R$ ".number_format($resultado_final, 2, '.', '')."</font></td>";

            }else {
                echo "<td><font color=\"blue\">"."R$".number_format($resultado_final, 2, '.', '')."</font></td>";
            }



            echo "</tr>";


        }


    echo '

    </tbody>
    </table>';


        break;


}




?>