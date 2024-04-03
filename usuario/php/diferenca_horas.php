<?php
function diferenca_horas($second1,$second2)
{
    $time_array1 = explode(':', $second1); //05:00 04:45
    $hours1 = (int)$time_array1[0]; //05
    $minutes1 = (int)$time_array1[1]; //00

    $time_array2 = explode(':', $second2);
    $hours2 = (int)$time_array2[0]; //04
    $minutes2 = (int)$time_array2[1]; //45

    $total_seconds1 = ($hours1 * 3600) + ($minutes1 * 60); //(05 *3600) + (00 * 60) 18.000
    $total_seconds2 = ($hours2 * 3600) + ($minutes2 * 60); //(04 *3600) + (45 *60) 17.100

    if($total_seconds1 > $total_seconds2){

        $seconds = ($total_seconds1 - $total_seconds2); // 18000 - 17.100 = 900
    }else {

        $seconds = ($total_seconds2 - $total_seconds1);
    }
    $hours = floor($seconds / 3600); //0
    $seconds -= $hours * 3600; //900
    $minutes = floor($seconds / 60); //15
    $seconds -= ($minutes * 60); // 900

    if($total_seconds1 > $total_seconds2){

        return sprintf('%02s:%02d:%02d', "-".$hours, $minutes, $seconds);
    }else {
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

}

?>