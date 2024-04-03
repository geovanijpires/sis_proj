<?php
function divisao_horas($horas,$dias){

$time_array = explode(':', $horas);
$hours = (int)$time_array[0];
$minutes = (int)$time_array[1];

$total_seconds = ($hours * 3600) + ($minutes * 60);

if($dias > 0){
$seconds = ($total_seconds / $dias);
} //operacao

$hours = floor($seconds / 3600);
$seconds -= $hours * 3600;
$minutes = floor($seconds / 60);
$seconds -= ($minutes * 60);

$result = "$hours:$minutes:$seconds";

return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

}


?>