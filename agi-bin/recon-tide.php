#!/usr/bin/php -q
<?
 ob_implicit_flush(false);
 error_reporting(0);
 set_time_limit(300);

//   Nerd Vittles Weather by Weather Underground ver. 5.1, (c) Copyright Ward Mundy, 2007-2012. All rights reserved.

//                    This software is licensed under the GPL2 license.
//
//   Material alteration of the spoken content provided by this application is strictly prohibited.
//
//   For a copy of license, visit http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
//
//    For additional information, contact us: http://pbxinaflash.com/about/comment.php

/************** FreePBX Tide By City Module **************
Additions and alterations made to the original Nerdvittles file are commented with #module
PBX Open Source Software Alliance
26 September 2012
******Updates and changed to POSSA weather-by-zip module forked by RECONWIRELESS******
************* FreePBX Tide By City Module **************/

//-------- DON'T CHANGE ANYTHING ABOVE THIS LINE ----------------
//*** start code added for #module compatibility
$bootstrap_settings['freepbx_auth'] = false;
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
include_once('/etc/asterisk/freepbx.conf');
}
// get user data from module
$date = tideoptions_getconfig();
//*** end code added for #module compatibility
//-------- DON'T CHANGE ANYTHING ABOVE THIS LINE ----------------

// #module  following line is changed to get the API key from the GUI
// $apikey ="12345" ;   //old nv line
$apikey = $date[1] ;
$city = $wcity[1];
$state = $wstate[1];


 $debug = 1;
 $newlogeachdebug = 1;
 $emaildebuglog = 0;
 $email = "yourname@yourdomain" ;
//-------- DON'T CHANGE ANYTHING BELOW THIS LINE ----------------

$states_name  = array('AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa",  'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland", 'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma", 'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'DC'=>"Washington D.C.",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming",'AB'=>"Alberta",'BC'=>"British Columbia",'MB'=>"Manitoba",'NB'=>"New Brunswick",'WY'=>"Wyoming",'NL'=>"Newfoundland",'WY'=>"Wyoming",'NT'=>"Northwest Territories",'NS'=>"Nova Scotia",'NU'=>"Nunavut",'ON'=>"Ontario",'PE'=>"Prince Edward Island",'QC'=>"Quebec",'SK'=>"Saskatchewan",'YT'=>"Yukon");
$states_abbr = array();
foreach ($states_name as $abbr => $state) {
    $states_abbr[$state] = $abbr ;
}
$day_of_week = array('Sunday'=>"Sun",'Monday'=>"Mon",'Tuesday'=>"Tue",'Wednesday'=>"Wed",'Thursday'=>"Thu",'Friday'=>"Fri",'Saturday'=>"Sat");


function fulldow($val) {
global $day_of_week;
$value = array_keys($day_of_week,$val);
$val= $value[0] ;
return $val ;
}

function state($val) {
global $states_name, $states_abbr;
$value = array_keys($states_abbr,$val);
$val= $value[0] ;
return $val ;
}


$log = "/var/log/asterisk/nv-tide-underground.txt" ;
if ($debug and $newlogeachdebug) :
 if (file_exists($log)) :
  unlink($log) ;
 endif ;
endif ;

 $stdlog = fopen($log, 'a'); 
 $stdin = fopen('php://stdin', 'r'); 
 $stdout = fopen( 'php://stdout', 'w' ); 

if ($debug) :
  fputs($stdlog, "Nerd Vittles Weather by Weather Underground ver. 5.1 (c) Copyright 2007-2012, Ward Mundy. All Rights Reserved.\n\n" . date("F j, Y - H:i:s") . "  *** New session ***\n\n" ); 
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

// ------ Code execution begins here
// parse agi headers into array  
//while ($env=read()) {  
// $s = split(": ",$env);  
// $agi[str_replace("agi_","",$s0)] = trim($s1); 
// if (($env == "") || ($env == "\n")) {  
//   break;  
// }  
//}  

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

$zip = $_SERVER["argv"][1];
$zip=trim($zip);

if ($debug) :
fputs($stdlog, "Location: " . $zip . "\n" );
endif ;


$place = $zip;

$zip=str_replace("south carolina","SC",$zip);
$zip=str_replace("new hampshire","NH",$zip);
$zip=str_replace("new york","NY",$zip);
$zip=str_replace("new jersey","NJ",$zip);
$zip=str_replace("new mexico","NM",$zip);
$zip=str_replace("north carolina","NC",$zip);
$zip=str_replace("north dakota","ND",$zip);
$zip=str_replace("rhode island","RI",$zip);
$zip=str_replace("south dakota","SD",$zip);
$zip=str_replace("west virginia","WV",$zip);
$zip=str_replace("district of columbia","DC",$zip);
$zip=str_replace("american samoa","american_samoa",$zip);
$zip=str_replace("cape verde","cape_verde",$zip);
$zip=str_replace("cayman islands","cayman_islands",$zip);
$zip=str_replace("costa rica","costa_rica",$zip);
$zip=str_replace("czech republic","czech_republic",$zip);
$zip=str_replace("dominican republic","dominican_republic",$zip);
$zip=str_replace("el salvador","el_salvador",$zip);
$zip=str_replace("hong kong","hong_kong",$zip);
$zip=str_replace("south korea","south_korea",$zip);
$zip=str_replace("new zealand","new_zealand",$zip);
$zip=str_replace("puerto rico","PR",$zip);
$zip=str_replace("russian federation","russian_federation",$zip);
$zip=str_replace("saint kitts","saint_kitts",$zip);
$zip=str_replace("saint lucia","saint_lucia",$zip);
$zip=str_replace("saudi arabia","saudi_arabia",$zip);
$zip=str_replace("south africa","south_africa",$zip);
$zip=str_replace("united arab emirates","united_arab_emirates",$zip);
$zip=str_replace("united states","united_states",$zip);
$zip=str_replace("united kingdom","united_kingdom",$zip);
$zip=str_replace("virgin islands","virgin_islands",$zip);

$sp1=strrpos($zip," ");

$city = trim(substr($zip,0,$sp1));
$city = trim(str_replace( " ", "_", $city));

$state = trim(substr($zip,$sp1+1));

if ($apikey=="12345") :
 $msg=chr(34)."Sorry but You first must configure N V weather google dot P-H-P with your weather underground key: then try again. ".chr(34);
 execute_agi("SET VARIABLE TIDE $msg");
 exit;
endif ;

$tides="Here is the latest tide report for $place. Brought to you by Weather Underground and Nerd Vittles. ";



$query = "http://api.wunderground.com/api/$apikey/tide/q/$state/$city.json";


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
 $msg=chr(34)."I'm sorry. No tide information currently is available for $place. Please try again later.".chr(34);
 execute_agi("SET VARIABLE TIDE $msg");
 exit;
}
$value = "";
while(!feof($fd)){
        $value .= fread($fd, 4096);
}
fclose($fd);

if ($value=="") :
 $msg=chr(34)."I'm sorry. No tide information currently is available for $place. Please try again later.".chr(34);
 execute_agi("SET VARIABLE TIDE $msg");
 exit;
endif ;



$tides = $tides . "Here is the tide report. "; 

$i = 1 ;

while ($i <= 8) :

$thetext=chr(34)."tideSummary:date:pretty".chr(34).":".chr(34);
$endtext=",";
$start= strpos($value, $thetext);
//echo $start . chr(10);
$tmptext = substr($value,$start+strlen($thetext));
//echo $start+strlen($thetext)+1;
//echo chr(10);
$end=strpos($tmptext, $endtext);
//echo $end . chr(10);
$theday = substr($tmptext,0,$end-1);
//echo $theday;
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

//echo $msg;
//echo chr(10);
//echo chr(10);

if ($emaildebuglog) :
 system("mime-construct --to $email --subject " . chr(34) . "Nerd Vittles Weather by Weather Underground ver. 5.1 Session Log" . chr(34) . " --attachment $log --type text/plain --file $log") ;
endif ;

// clean up file handlers etc.
fclose($stdin);
fclose($stdout);
fclose($stdlog);
exit;

?>