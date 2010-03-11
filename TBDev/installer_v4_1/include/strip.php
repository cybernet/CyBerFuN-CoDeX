<?php
if(!defined('PUBLIC_ACCESS')) die('Fuck off - You cant access scripts directly fool !');   

function trim_ml (&$descr, $extras = false ) {
    $lines = array();
    foreach( explode( "\n", $descr ) as  $line ) {
        $lines[] = trim( $line, "\x00..\x1F.,-+=\t ~" );
    }
    $descr = implode( "\n", $lines );
}
function trim_regex( $pattern, $replacement, $subject ) {
    trim_ml( $subject );
    return preg_replace( $pattern, $replacement, $subject );
}
function strip( &$descr ) {
    $descr = trim_regex( '/[^\\x20-\\x7e\\x0a\\x0d]/', '', $descr );
    $descr = trim_regex( '/ +/', ' ', $descr );
    $descr = trim_regex( "/\n[-\s]+\n/", "\n\n", $descr );
    $descr = trim_regex( "/\n[a-zA-Z][ ~]+[a-zA-Z]\n/", '' ,$descr );
    $descr = trim_regex( "/\n[A-Z] ?~+/i", "\n", $descr );
    $descr = trim_regex( "/~+ ?[A-Z]\n/i", "\n", $descr );
    $descr = trim_regex( '/([^\s.])[\s.:]+:+[\s.:]+([^\s.])/', '$1: $2', $descr );
    $descr = trim_regex( "/\s+\[(.+)\]/", ' [$1]', $descr );
    $descr = trim_regex( "/\n\n\n+/", "\n\n", $descr );
    $descr = trim_regex( "/\\[ \\] [^[\n]+/", '', $descr );
    $descr = str_replace( "[ ]\n", "\n", $descr );
    $descr = trim_regex( "/(: \[[^\]]\] [^\n]+)\n\n/", "\$1\n", $descr );
    $descr = trim_regex( "/([^\n]+:[^\n]+)\n\n([^\n]+:[^\n]+)/", "$1\n$2", $descr );
    $descr = trim_regex( "/ \[x\]/i", ',', $descr );
    $descr = trim_regex( "/ \[(\d+)\]/", ', $1', $descr );
    $descr = str_replace( ':,', ':', $descr );
    $descr = trim_regex( "/\[ ([^\]]+) \]/", '$1', $descr );
    $descr = trim_regex( "/\n\n\n+/", "\n\n", $descr );
    trim_ml( $descr, $extra );
    $descr = trim( $descr );
}


?>
