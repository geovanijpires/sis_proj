<?php
include_once("../../php/conexao.php");
include_once("soma_horas.php");
include_once("soma_horas2.php");
include_once("multiplicar_horas.php");
include_once("diferenca_horas.php");
include_once("diferenca_ferias.php");
include_once("custo_horas.php");
mysqli_set_charset($con,'utf8');
ini_set('default_charset','UTF-8');

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

    case "valida_hora_extra":        
        
        session_start();
        $id_func = $_SESSION["id_func"];
        $horas_diarias = $_SESSION["horas_diarias"];
        $hora_extra = $_SESSION["hora_extra"];

        $data = $_POST['data'];        
        $data = explode("/", $data);
        list($dia,$mes,$ano) = $data;
        $data_invertida = "$ano-$mes-$dia";
        $inicio_atividade = $_POST['inicio_atividade'];        
        $fim_atividade = $_POST['fim_atividade'];        
        $array_tot = array();
        
        //verifica se usuário pode ou não fazer horas extras, se não entra na verificação diária

        if($hora_extra == 0){//busca no banco retorna false (não pode fazer horas extras)
        
            //Busca horas da folha proj
            
            $query = ("select SEC_TO_TIME(SUM(TIME_TO_SEC(hora_total))) as h_total FROM folha_proj where id_func = '$id_func' and data = '$data_invertida'")or die(mysqli_error());
            $consulta = mysqli_query($con,$query);
            $result_vhextra = mysqli_fetch_array($consulta);
            if($result_vhextra['h_total'] == null){
                $htotal_proj = '00:00:00';
            }else{
                $htotal_proj = $result_vhextra['h_total'];
            }
            
            
            $query = ("select SEC_TO_TIME(SUM(TIME_TO_SEC(hora_total))) as h_total FROM folha_adm where id_func = '$id_func' and data = '$data_invertida'")or die(mysqli_error());
            $consulta = mysqli_query($con,$query);
            $result_vhextra = mysqli_fetch_array($consulta);
            if($result_vhextra['h_total'] == null){
                $htotal_adm = '00:00:00';
            }else{
                $htotal_adm = $result_vhextra['h_total'];
            }
            
            $total_hhoje = diferenca_horas($inicio_atividade.':00',$fim_atividade.':00');
            $total_proj_adm = sum_the_time2($htotal_proj,$htotal_adm);
            
            $tot_hj_e_ja_lancado = sum_the_time2($total_proj_adm, $total_hhoje);
            
            $array_tot['totjalancado'] = $total_proj_adm;
            
            if($tot_hj_e_ja_lancado > $horas_diarias){
                $array_tot['status'] = 'false';
                $array_tot['msg_hdiaria'] = $horas_diarias;
                echo json_encode($array_tot); //validaçao falsa não pode lancar
            }else{
                $array_tot['status'] = 'true';
                echo json_encode($array_tot); //validaçao true pode lancar
            }

        }else if($hora_extra == 1){//busca no banco retorna true (pode fazer horas extras)
            $array_tot['status'] = 'true';
            echo json_encode($array_tot); //validaçao true pode lancar

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

    case "inserir_despesa":

        session_start();
        $id_func = $_SESSION["id_func"];

        $data = $_POST['data'];
        $data = explode("/", $data);
        list($dia,$mes,$ano) = $data;
        $data_invertida = "$ano-$mes-$dia"; //inverter data pro banco

        $projeto = $_POST['projeto'];
        $despesa = $_POST['despesa'];

             //insere no banco
            $query = mysqli_query($con,"insert into despesas (id_func, data, id_proj, despesa) values ('$id_func','$data_invertida','$projeto','$despesa')") or die(mysqli_error());
            if ($query) {
                echo "0";
            } else {
                echo "1";
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

        if($exist_conflito == 0) {
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
        $query_sum = mysqli_query($con,"SELECT funcionario.nome, cargo.cargo, funcionario.horas_diarias FROM funcionario inner join cargo on funcionario.id_cargo = cargo.id  where funcionario.id = '$id_func' ")or die(mysqli_error());
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
        $query_sum = mysqli_query($con,"SELECT funcionario.nome, cargo.cargo, funcionario.horas_diarias FROM funcionario inner join cargo on funcionario.id_cargo = cargo.id  where funcionario.id = '$id_func' ")or die(mysqli_error());
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
            while($result_etapa = mysqli_fetch_array($query_etapa)){

                if ($result_etapa['id'] == $id_etapa){
                    $array_etapa[] = '<option value='.$result_etapa['id'].' selected>'.$result_etapa['nome'].'</option>';
                } else {

                    $array_etapa[] = '<option value='.$result_etapa['id'].'>'.$result_etapa['nome'].'</option>';

                }

            }

            $array_busca['select_etapa'] = $array_etapa;
            // fecha dados select etapas

            //dados select subetapas
            $id_subetapa = $result['id_subetapa'];
            $array_subetapa = array();

            $query_subetapa = mysqli_query($con,"select id, nome from sub_etapa where id_etapa = '$id_etapa'");
            while($result_subetapa = mysqli_fetch_array($query_subetapa)){

                if ($result_subetapa['id'] == $id_subetapa){
                    $array_subetapa[] = '<option value='.$result_subetapa['id'].' selected>'.$result_subetapa['nome'].'</option>';
                } else {

                    $array_subetapa[] = '<option value='.$result_subetapa['id'].'>'.$result_subetapa['nome'].'</option>';

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
        while ($result_valida_isercao = mysqli_fetch_array($query_valida_insercao)){
            if($result_valida_isercao['id'] != $id) {
                $h_inicial_banco = strtotime($result_valida_isercao['h_inicial']);
                $h_final_banco = strtotime($result_valida_isercao['h_final']);
                if ($h_inicial_banco >= strtotime($h_inicial) && $h_final_banco <= strtotime($h_final) || strtotime($h_inicial) >= $h_inicial_banco && strtotime($h_inicial) < $h_final_banco || strtotime($h_final) > $h_inicial_banco && strtotime($h_final) <= $h_final_banco) {
                    $exist_conflito++;
                }
            }
        }
        //testa horas administrativas
        $query_valida_insercao = mysqli_query($con,"SELECT h_inicial, h_final FROM folha_adm WHERE id_func = '$id_func' and data = '$data_invertida'");
        while ($result_valida_isercao = mysqli_fetch_array($query_valida_insercao)){
            $h_inicial_banco = strtotime($result_valida_isercao['h_inicial']);
            $h_final_banco = strtotime($result_valida_isercao['h_final']);
            if($h_inicial_banco >= strtotime($h_inicial) && $h_final_banco <= strtotime($h_final) || strtotime($h_inicial) >= $h_inicial_banco && strtotime($h_inicial) < $h_final_banco || strtotime($h_final) > $h_inicial_banco && strtotime($h_final) <= $h_final_banco){
                $exist_conflito ++;
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
        while ($result_valida_isercao = mysqli_fetch_array($query_valida_insercao)){
            $h_inicial_banco = strtotime($result_valida_isercao['h_inicial']);
            $h_final_banco = strtotime($result_valida_isercao['h_final']);
            if($h_inicial_banco >= strtotime($inicio_atividade_adm) && $h_final_banco <= strtotime($fim_atividade_adm) || strtotime($inicio_atividade_adm) >= $h_inicial_banco && strtotime($inicio_atividade_adm) < $h_final_banco || strtotime($fim_atividade_adm) > $h_inicial_banco && strtotime($fim_atividade_adm) <= $h_final_banco){
                $exist_conflito ++;
            }

        }
        //testa horas administrativas
        $query_valida_insercao = mysqli_query($con,"SELECT id, h_inicial, h_final FROM folha_adm WHERE id_func = '$id_func' and data = '$data_invertida'");
        while ($result_valida_isercao = mysqli_fetch_array($query_valida_insercao)){
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

    

  case "edit_func":
    $id = $_POST['id'];
    $array_busca = array();

    $query = mysqli_query($con,"select id, hora_extra from funcionario where id = '$id' ")or die(mysqli_error());
    while($result = mysqli_fetch_array($query)){
        $array_busca['id'] = $result['id'];
               
        $hora_extra = $result['hora_extra'];

        
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

      
    }

    echo json_encode($array_busca); //encode json para os dados do ajax

    break;

    case "edit_new_func":
        $id_func = $_POST['id_func'];        
        $hora_extra = $_POST['hora_extra'];
        
        $query = mysqli_query($con,"update funcionario set hora_extra='$hora_extra' where id='$id_func'") or die(mysqli_error());
        if ($query) {
            echo "0";
        } else {
            echo "1";
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
         session_start();
        $id_func = $_SESSION["id_func"];;
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

}



?>