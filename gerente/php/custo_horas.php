<?php
function custo_horas($horas,$valor){

$time_array = explode(':', $horas);
$hours = (int)$time_array[0];
$minutes = (int)$time_array[1];

$h_em_minutos = $hours*60;
$minutos = $h_em_minutos+$minutes;
$valor_minuto = $valor / 60;
$valor_hora = $minutos * $valor_minuto;

return $valor_hora;

}


?>