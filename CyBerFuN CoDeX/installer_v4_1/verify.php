<?php
require_once("include/bittorrent.php");
dbconn();

/*
$verifystring = verifystring($string_to_verify,$type);
if($verifystring !== TRUE)
        die($verifystring);
Use:
1. Change $string_to_verify for the variable you are going to verify.
2. Change $type to one of the types supported in this function. Wrong ones will result in an error.
3. Put this code as close above the variable to be verified as possible. Without breaking the existing code though.
If the function returns anything else than TRUE (case sensitive), an error message will display.
The message is not too informative but should provide some clues for the debugger.
*/

function verifystring($string,$type) {
        if(!isset($string))
                return '$string not defined';
        switch($type) {
                case num:
                        $chars = '0123456789';
                        for($i=0;$i<strlen($string); $i++) {
                                if(strpos($chars,$string[$i]) === false)
                                        return $string.' is not a number';
                        }
                break;
                case md5:
                        $chars = 'abcdef1234567890';
                        if(strlen($string) != '32')
                                return('md5 length is not 32');
                        for($i=0;$i < '32';$i++) {
                                if(strpos($chars,$string[$i]) === false)
                                        return 'invalid md5 string';
                        }
                break;
                case email:
                        // Check if an e-mail address has allowed symbols according to RFC

                        // spares some characters in $localchars
                        $string = strtolower($string);

                        $email = explode('@',$string);
                        $domain = explode('.',$email['1']);
                        if(strlen($email['0']) > '64')
                                return 'local-part is too long';
                        if(strlen($email['1']) > '255')
                                return 'domain-part is too long';
                        // Characters allowed to be in the mailbox name
                        $localchars = 'abcdefghijklmnopqrstuvwxyz1234567890,!#$%&*+-/=?^_`{|}~.\'';
                        // Characters allowed in TLDs
                        $tldchars = 'abcdefghijklmnopqrstuvwxyz';
                        $tld = $domain[count($domain)-1];
                        for($i=0;$i < strlen($tld);$i++) {
                            if(strpos($tldchars,$tld[$i]) === false)
                                return 'Invalid TLD - '.$tld;
                        }
                        // The character . is not allowed as the first or last part of the local-part
                        if($email['0']['0'] === '.' || $string[(strlen($email['0'])-1)] === '.')
                                return 'Invalid "." character in e-mail address';
                        for($i=0;$i < strlen($email['0']);$i++) {
                                if(strpos($localchars,$string[$i]) === false)
                                        return 'Invalid e-mail';
                        }
                        // Check if the domain exists
                                // RFC allows the lack of MX records so an extra function is done if FALSE on first one
                                // Functions do not work on Windows platforms
                                if(checkdnsrr($email['1'],'MX') === FALSE) {
                                        if(checkdnsrr($email['1'].'.','A') === FALSE)
                                                return 'No valid mail server records found for domain '.$email['1'];
                                }
                break;
                default:
                        return 'Type not specified';
                break;
        }
    // If the script sees no reason for error, return TRUE.
    return TRUE;
}

stdhead("Staff");
begin_main_frame();
loggedinorreturn();
begin_frame('Verify e-mail addresses');
$sql = mysql_query('SELECT email FROM users');
while($row = mysql_fetch_assoc($sql)) {
    $verifystring = verifystring($row['email'],'email');
    if($verifystring !== TRUE)
        echo $verifystring.'<br />';
}
end_frame();
end_main_frame();
stdfoot();
?>