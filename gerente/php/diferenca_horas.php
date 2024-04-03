<?php
function diferenca_horas($second1,$second2) //1480 1485:25
{
    $time_array1 = explode(':', $second1);
    $hours1 = (int)$time_array1[0];
    $minutes1 = (int)$time_array1[1];

    $time_array2 = explode(':', $second2);
    $hours2 = (int)$time_array2[0];
    $minutes2 = (int)$time_array2[1];

    $total_seconds1 = ($hours1 * 3600) + ($minutes1 * 60);
    $total_seconds2 = ($hours2 * 3600) + ($minutes2 * 60);

    if($total_seconds1 > $total_seconds2){

        $seconds = ($total_seconds1 - $total_seconds2); //se e negativo

        $hours = floor($seconds / 3600);
        $seconds -= $hours * 3600;
        $minutes = floor($seconds / 60);
        $seconds -= ($minutes * 60);


        return sprintf('%02s:%02d:%02d', "-".$hours, $minutes, $seconds);

    }else {

        $seconds = ($total_seconds2 - $total_seconds1); // se e positivo

        $hours = floor($seconds / 3600);
        $seconds -= $hours * 3600;
        $minutes = floor($seconds / 60);
        $seconds -= ($minutes * 60);

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

    }

}

?>