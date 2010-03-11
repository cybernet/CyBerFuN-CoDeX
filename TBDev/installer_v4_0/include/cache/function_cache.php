<?php

///======cache this page //==sir_snugglebunny
function cache_start($age,$file_name)
{
    global $cache_file_name;
    $cache_file_name = __FILE__ . '_cache_'. $file_name;

    // default cache age
    if (empty($age)) $age = 600;

    // if cache exists is data still valid ?
    if (@filemtime($cache_file_name) + $age > time()) {
        //echo"$age $file_name $cache_file_name";    //===== uncomment for testing to see if the value is really passed
        readfile($cache_file_name);
        unset($cache_file_name);
        exit;
    }
    // cache empty, or  too old
    ob_start();
}

function cache_end()
{
    global $cache_file_name;

    if (empty($cache_file_name)) return;
    $str = ob_get_clean();
    echo $str;

    fwrite(fopen($cache_file_name.'_tmp', "w"), $str);
    rename($cache_file_name.'_tmp', $cache_file_name);
}
//===end  cache
?>