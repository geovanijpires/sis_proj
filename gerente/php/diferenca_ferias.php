<?php
function diferenca_horas_ferias($saldo,$saldo_ferias)
{


    $time_array1 = explode(':', $saldo);
    $hours1 = (int)$time_array1[0];
    $minutes1 = (int)$time_array1[1];

    $time_array2 = explode(':', $saldo_ferias);
    $hours2 = (int)$time_array2[0];
    $minutes2 = (int)$time_array2[1];

    $saldo_total = ($hours1 * 3600) + ($minutes1 * 60);
    $saldo_total_ferias = ($hours2 * 3600) + ($minutes2 * 60);

    $seconds = ($saldo_total + $saldo_total_ferias);

    $hours = floor($seconds / 3600);
    $seconds -= $hours * 3600;
    $minutes = floor($seconds / 60);
    $seconds -= ($minutes * 60);

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);


}

?>