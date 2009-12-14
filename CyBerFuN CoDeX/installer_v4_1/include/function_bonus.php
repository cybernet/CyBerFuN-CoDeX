<?php
// eZER0's mod for bonus contribution
              // Limited this to 3 because of performance reasons and i wanted to go through last 3 events anyway the most we can have
              // is that invites are enabled, double upload is enabled as well as freeleech is enabled! x  3 eZER0
function mysql_fetch_all($query, $default_value = Array())
{
    $r = @mysql_query($query);
    $result = Array();
    if ($err = mysql_error())return $err;
    if (@mysql_num_rows($r))
        while ($row = mysql_fetch_array($r))$result[] = $row;
    if (count($result) == 0)
        return $default_value;
    return $result;
}

function get_user_name($userid){
$res = sql_query("SELECT `username` FROM `users` WHERE `id` = $userid LIMIT 1") or sqlerr(__FILE__, __LINE__);
$username = mysql_fetch_assoc($res);
return $username;
}
    
    $scheduled_events = mysql_fetch_all("SELECT * from `events` ORDER BY `startTime` DESC LIMIT 3;", array());

    if (is_array($scheduled_events)){
        foreach ($scheduled_events as $scheduled_event) {
            if (is_array($scheduled_event) && array_key_exists('startTime', $scheduled_event) &&
                array_key_exists('endTime', $scheduled_event)){
                $startTime = 0;
                $endTime = 0;
                $startTime = $scheduled_event['startTime'];
                $endTime = $scheduled_event['endTime'];

                
                if (time() < $endTime && time() > $startTime){
                    
                    if (array_key_exists('freeleechEnabled', $scheduled_event)) {
                        $freeleechEnabled = $scheduled_event['freeleechEnabled'];
                        if ($scheduled_event['freeleechEnabled']){
                            $freeleech_start_time = $scheduled_event['startTime'];
                            $freeleech_end_time = $scheduled_event['endTime'];
                            $freeleech_enabled = true;
                        }
                    }
                    
                    if (array_key_exists('duploadEnabled', $scheduled_event)){
                        $duploadEnabled = $scheduled_event['duploadEnabled'];
                        if ($scheduled_event['duploadEnabled']){
                            $double_upload_start_time = $scheduled_event['startTime'];
                            $double_upload_end_time = $scheduled_event['endTime'];
                            $double_upload_enabled = true;
                        }
                    }
    
                    if (array_key_exists('hdownEnabled', $scheduled_event)) {
                        $hdownEnabled = $scheduled_event['hdownEnabled'];    
                        if ($scheduled_event['hdownEnabled']){
                            $half_down_start_time = $scheduled_event['startTime'];
                            $half_down_end_time = $scheduled_event['endTime'];
                            $half_down_enabled = true;
                        }
                   
                   }
                
                }
            }
        
        }
    }
    

    $sql = "SELECT `pointspool`, `points` FROM `bonus` WHERE `art` = 'freeleech' OR `art` = 'doubleup' OR `art` = 'halfdown'";
    $res = sql_query($sql)  or print (mysql_error());
    $row = mysql_fetch_assoc($res);
    $row2 = mysql_fetch_assoc($res);
    $row3 = mysql_fetch_assoc($res);

    
    $fpointspool = $row["pointspool"];
    $dpointspool = $row2["pointspool"];
    $hpointspool = $row3["pointspool"];

if($fpoints == 0) $fpoints = 1;
if($dpoints == 0) $dpoints = 1;
if($hpoints == 0) $hpoints = 1;

$free_leech_percentage = round(($fpointspool / $fpoints) / 1000, 0);
    $double_upload_percentage = round(($dpointspool / $dpoints) / 1000, 0);    
    $half_down_open_percentage = round(($hpointspool / $hpoints) / 1000, 0);  
        // make this code more DRY! put it in a function somewhere and then call it for each of the percentages
        if($free_leech_percentage <= 25){
            $fcolor = "red";    
        } elseif($free_leech_percentage <= 50) {
            $fcolor = "#da00e0";
        } else {
            $fcolor = "darkgreen";
        }
        
        if($double_upload_percentage <= 25){
            $dcolor = "red";    
        } elseif($double_upload_percentage <= 50) {
            $dcolor = "#da00e0";
        } else {
            $dcolor = "darkgreen";
        }
        
        if($half_down_open_percentage <= 25){
            $hcolor = "red";    
        } elseif($half_down_open_percentage <= 50) {
            $hcolor = "#da00e0";
        } else {
            $hcolor = "darkgreen";
        }

        
    if($freeleech_enabled){
        $fstatus = "<strong><font color=\"darkgreen\"> ON </font></strong>";
    } else {
        $fstatus = $free_leech_percentage ." %";
    }
    
    if($double_upload_enabled){
        $dstatus = "<strong><font color=\"darkgreen\"> ON </font></strong>";
    } else {
        $dstatus = $double_upload_percentage ." %";
    }
    
    if($half_down_enabled){
        $hstatus = "<strong><font color=\"darkgreen\"> ON </font></strong>";
    } else {
        $hstatus = $half_down_open_percentage ." %";
    }
?>