<?php
//
//
//This program is free software; you can redistribute it and/or
//modify it under the terms of the GNU General Public License
//as published by the Free Software Foundation; either version 2
//of the License, or (at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.


//tts_findengines()
if(count($_POST)){
	tideoptions_saveconfig();
}
	$date = tideoptions_getconfig();
	$selected = ($date[0]);

//  Get current featurecode from FreePBX registry
$fcc = new featurecode('tidecity', 'tidecity');
$featurecode = $fcc->getCodeActive(); 

?>
<form method="POST" action="">
	<br><h2><?php echo _("U.S. Tide by City")?><hr></h5></td></tr>
Tide by City allows you to listen to local tide information from any touchtone phone using nothing more than your PBX connected to the Internet. <br><br>
Current tide conditions and/or forecast for the chosen US City, State will then will be retrieved from the selected service using the selected text-to-speech engine. <br><br>
The feature code to access this service is currently set to <b><?PHP print $featurecode; ?></b>.  This value can be changed in Feature Codes. <br>

<br><h5>User Data:<hr></h5>
Select the Text To Speech engine and Forecast source combination you wish the Tide by City program to use.<br>The module does not check to see if the selected TTS engine is present, ensure to choose an engine that is installed on the system.<br>Data courtesy of Weather Underground, Inc. (WUI) is subject to the Weather Underground API Terms and Conditions of Use. The author of this software is not affiliated with WUI, and the software is neither sponsored nor endorsed by WUI.<br>
<a href="#" class="info">Choose a service and engine:<span>Choose from the list of supported TTS engines and Tide services</span></a>

<select size="1" name="engine">
<?php
echo "<option".(($date[0]=='tide-wunderground-flite')?' selected':'').">tide-wunderground-flite</option>\n";
echo "<option".(($date[0]=='tide-wunderground-swift')?' selected':'').">tide-wunderground-swift</option>\n";
echo "<option".(($date[0]=='recon-tide-flite')?' selected':'').">recon-tide-flite</option>\n";
?>
</select>
<br><a href="#" class="info">Wunderground API KEY:<span>Input free API key from registration with http://wunderground.com weather service</span></a>
<input type="text" name="wgroundkey" size="27" value="<?php echo $date[1]; ?>">  <a href="javascript: return false;" > 
<br><a href="#" class="info">Tide City:<span>Input US City</span></a>
<input type="text" name="wgroundcity" size="27" value="<?php echo $date[2]; ?>">  <a href="javascript: return false;" > 
<br><a href="#" class="info">Tide State:<span>Input two digit state abbreviation</span></a>
<input type="text" name="wgroundstate" size="02" value="<?php echo $date[3]; ?>">  <a href="javascript: return false;" > 


		
<br><br><input type="submit" value="Submit" name="B1"><br>


