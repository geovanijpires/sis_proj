<?php

function sum_the_time2($time1,$time2) {
    //$TIMES1
    $seconds1 = 0;

        list($hour1,$minute1,$second1) = explode(':', $time1);
        $seconds1 += $hour1*3600;
        $seconds1 += $minute1*60;
        $seconds1 += $second1;

    $hours1 = floor($seconds1/3600);
    $seconds1 -= $hours1*3600;
    $minutes1  = floor($seconds1/60);
    $seconds1 -= $minutes1*60;

    //$TIMES2
    $seconds2 = 0;

        list($hour2,$minute2,$second2) = explode(':', $time2);
        $seconds2 += $hour2*3600;
        $seconds2 += $minute2*60;
        $seconds2 += $second2;

    $hours2 = floor($seconds2/3600);
    $seconds2 -= $hours2*3600;
    $minutes2  = floor($seconds2/60);
    $seconds2 -= $minutes2*60;


    $hfinal = $hour1+$hour2;
    $minutesfinal = $minute1+$minute2;
    $secondsfinal = $second1+$second2;

    return sprintf('%02d:%02d:%02d', $hfinal, $minutesfinal, $secondsfinal); // Thanks to Patrick
}

?>