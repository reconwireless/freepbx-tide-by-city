#!/usr/bin/php -q
<?
 ob_implicit_flush(false);
 error_reporting(0);
 set_time_limit(300);

$bootstrap_settings['freepbx_auth'] = false;
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
include_once('/etc/asterisk/freepbx.conf');
}
// get user data from module
$date = tideoptions_getconfig();
$wcity = tideoptions_getconfig();
$wstate = tideoptions_getconfig();


// get api key
$apikey = $date[1] ;
$city = $wcity[1];
$state = $wstate[1];


 $debug = 1;
 $newlogeachdebug = 1;
 $emaildebuglog = 0;
 $email = "pmcnair@reconwireless.com" ;


$log = "/var/log/asterisk/tide-wunderground.txt" ;
if ($debug and $newlogeachdebug) :
 if (file_exists($log)) :
  unlink($log) ;
 endif ;
endif ;

 $stdlog = fopen($log, 'a'); 
 $stdin = fopen('php://stdin', 'r'); 
 $stdout = fopen( 'php://stdout', 'w' ); 

if ($debug) :
  fputs($stdlog, "Tide Weather Underground.\n\n" . date("F j, Y - H:i:s") . "  *** New session ***\n\n" ); 
endif ;

function read() {  
 global $stdin;  
 $input = str_replace("\n", "", fgets($stdin, 4096));  
 dlog("read: $input\n");  
 return $input;  
}  

function write($line) {  
 dlog("write: $line\n");  
 echo $line."\n";  
}  

function dlog($line) { 
 global $debug, $stdlog; 
 if ($debug) fputs($stdlog, $line); 
} 

function execute_agi( $command ) 
{ 
GLOBAL $stdin, $stdout, $stdlog, $debug; 
 
fputs( $stdout, $command . "\n" ); 
fflush( $stdout ); 
if ($debug) 
fputs( $stdlog, $command . "\n" ); 
 
$resp = fgets( $stdin, 4096 ); 
 
if ($debug) 
fputs( $stdlog, $resp ); 
 
if ( preg_match("/^([0-9]{1,3}) (.*)/", $resp, $matches) )  
{ 
if (preg_match('/result=([-0-9a-zA-Z]*)(.*)/', $matches[2], $match))  
{ 
$arr['code'] = $matches[1]; 
$arr['result'] = $match[1]; 
if (isset($match[3]) && $match[3]) 
$arr['data'] = $match[3]; 
return $arr; 
}  
else  
{ 
if ($debug) 
fputs( $stdlog, "Couldn't figure out returned string, Returning code=$matches[1] result=0\n" );  
$arr['code'] = $matches[1]; 
$arr['result'] = 0; 
return $arr; 
} 
}  
else  
{ 
if ($debug) 
fputs( $stdlog, "Could not process string, Returning -1\n" ); 
$arr['code'] = -1; 
$arr['result'] = -1; 
return $arr; 
} 
}  

while ( !feof($stdin) )  
{ 
$temp = fgets( $stdin ); 
 
if ($debug) 
fputs( $stdlog, $temp ); 
 
// Strip off any new-line characters 
$temp = str_replace( "\n", "", $temp ); 
 
$s = explode( ":", $temp ); 
$agivar[$s[0]] = trim( $s[1] ); 
if ( ( $temp == "") || ($temp == "\n") ) 
{ 
break; 
} 
}  

if ($apikey==null) :
 $msg=chr(34)."Sorry but You first must configure Tide by City with your weather underground key: then try again. ".chr(34);
 execute_agi("SET VARIABLE TIDE $msg");
 exit;
endif ;

$tides="Here is the latest tide report for $city. Brought to you by Weather Underground. ";



//$query = "http://api.wunderground.com/api/$apikey/tide/q/$state/$city.json"; 
$query = "http://api.wunderground.com/api/135cffa972280695/tide/q/SC/bluffton.json";

$query = trim(str_replace( " ", "_", $query));

#echo $city ;
#echo chr(10).chr(10);
#echo $state ;
#echo chr(10).chr(10);
#echo $query;
#echo chr(10).chr(10);


$fd = fopen($query, "r");
if (!$fd) {
 echo "<p>Unable to open web connection. \n";
 $msg=chr(34)."I'm sorry. No tide information currently is available for $city. Please try again later.".chr(34);
 execute_agi("SET VARIABLE TIDE $msg");
 exit;
}
$value = "";
while(!feof($fd)){
        $value .= fread($fd, 4096);
}
fclose($fd);

if ($value=="") :
 $msg=chr(34)."I'm sorry. No tide information currently is available for $city. Please try again later.".chr(34);
 execute_agi("SET VARIABLE TIDE $msg");
 exit;
endif ;



$tides = $tides . "Here is the tide report. "; 

$i = 1 ;

while ($i <= 8) :

$thetext=chr(34)."tideSummary:date:pretty".chr(34).":".chr(34);
$endtext=",";
$start= strpos($value, $thetext);
$tmptext = substr($value,$start+strlen($thetext));
$end=strpos($tmptext, $endtext);
$theday = substr($tmptext,0,$end-1);
$value = substr($value,$start+strlen($thetext)+$end);

$tides = $tides . $theday . ": ";

#$thetext=chr(34)."type".chr(34).":".chr(34);
#$endtext=chr(34).",";
#$thetext=chr(34)."height".chr(34).":".chr(34);
#$endtext=chr(34).",";
#$start= strpos($value, $thetext);
#//echo $start . chr(10);
#$tmptext = substr($value,$start+strlen($thetext));
#//echo $start+strlen($thetext)+1;
#//echo chr(10);
#$end=strpos($tmptext, $endtext);
#//echo $end . chr(10);
#$thedata = substr($tmptext,0,$end-1);
#//echo $thedata;
#$value = substr($value,$start+strlen($thetext)+$end);



$tides = $tides . $thedata . ". ";





$i++;
endwhile;


#echo $tides ;
#echo chr(10).chr(10);
#exit;

$msg= chr(34).$tides. "Have a nice day. Good bye.".chr(34);
$msg = str_replace( ",", " ", $msg );

if ($debug) :
fputs($stdlog, "Tide: " . $msg . "\n" );
endif ;

execute_agi("SET VARIABLE TIDE $msg");


if ($emaildebuglog) :
 system("mime-construct --to $email --subject " . chr(34) . "Tide by Weather Underground Session Log" . chr(34) . " --attachment $log --type text/plain --file $log") ;
endif ;

// clean up file handlers etc.
fclose($stdin);
fclose($stdout);
fclose($stdlog);
exit;

?>