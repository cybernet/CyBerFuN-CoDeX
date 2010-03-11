<?php
// cache function// - posted by rightthere==//
function query_wphpfile ($fileinformation)
{
    $clause = $fileinformation[table];

    if (isset($fileinformation[where]))
        $clause .= " WHERE " . $fileinformation[where];

    if (isset($fileinformation[orderby]))
        $clause .= " ORDER BY " . $fileinformation[orderby];

    $file_stringdata = '';
    $file_string = '<?php' . "\n\n";

    $file_string .= '/////////////////////////////////////////////////////////////' . "\n" . '/////////////////////// DO NOT EDIT! ///////////////////////' . "\n" . '////////////// File Automatically Generated //////////////' . "\n" . '/////////////////////////////////////////////////////////' . "\n\n";

    $file_string .= '$' . "{$fileinformation['arrayname']}\t=\t" . 'array (' . "\n";

    $res = sql_query("SELECT * FROM " . $clause);

    while ($arr = mysql_fetch_assoc($res)) {
        $file_stringdata .= "\t\t\t\t\t" . 'array (' . "\n";

        foreach ($arr as $k => $v) {
            // Protect serailized arrays..
            $v = stripslashes($v);
            $v = addslashes($v);

            $file_stringdata .= "\t\t\t\t\t\t\t'$k'\t\t\t=>\t'$v'" . ',' . "\n";
        }

        $file_stringdata = substr($file_stringdata, 0, -2) . "\n\t\t\t\t\t),\n";
    }
    $file_string .= substr($file_stringdata, 0, -2) . "\n\t\t\t);\n" . '?>';

    if ($fh = fopen('include/cache/' . "{$fileinformation['filename']}" . '.php', 'w')) {
        fwrite($fh, $file_string, strlen($file_string));
        fclose($fh);
        print('File written');
    } else
        print('Can\'t write file');
}

?>