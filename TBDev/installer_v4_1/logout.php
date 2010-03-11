<?php
require_once("include/bittorrent.php");
dbconn();
maxcoder();
logoutcookie();
Header("Location: $DEFAULTBASEURL/");

?>