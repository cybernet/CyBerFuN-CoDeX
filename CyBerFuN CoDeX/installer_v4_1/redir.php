<?php
include ('include/bittorrent.php');
dbconn(false);
if (!isset($CURUSER)) die();
// First, grab the request URI which will contain the name of this file and the target URL string
$uri = $_SERVER[REQUEST_URI];
// Strip out the file name and the url= variable ident. Everything after that is considered a redirect.
$url = preg_replace("/.redir.php.url=/", "", $uri);

if (substr($url, 0, 4) == "www.") $url = "http://" . $url;
elseif (substr($url, 0, 7) == "http://") $url = $url;
elseif (substr($url, 0, 8) == "https://") $url = $url;
elseif (substr($url, 0, 6) == "ftp://") $url = $url;
else $url = "http://" . $url;

if (substr($url, 0, 22) == "http://127.0.0.1")
    print("<html><head><meta http-equiv=refresh content='0;url=$url'></head><body>\n");
elseif (substr($url, 0, 23) == "https://127.0.0.1")
    print("<html><head><meta http-equiv=refresh content='0;url=$url'></head><body>\n");
elseif (substr($url, 0, 21) == "ftp://127.0.0.1")
    print("<html><head><meta http-equiv=refresh content='0;url=$url'></head><body>\n");
else
    print("<html><head><meta http-equiv=refresh content='0;url=http://anonym.to/?$url'></head><body>\n");

?>