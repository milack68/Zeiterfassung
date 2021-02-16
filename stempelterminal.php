<?php
/********************************************************************************
* Small Time - Beispiel für ein einfaches Touch - Screen - Stempelterminal
* bei nichtgebrauch Datei löschen
/*******************************************************************************
* Version 0.9.015
* Author:  IT-Master GmbH
* www.it-master.ch / info@it-master.ch
* Copyright (c), IT-Master GmbH, All rights reserved
*******************************************************************************/

// Helper-Function to debug php code to console
function debug_to_console($data) {
	$output = $data;
	if (is_array($output))
			$output = implode(',', $output);

	echo "<script>console.log('Debug Output: " . $output . "' );</script>";
}


// Helper-Function to find the rfid from the user
function find_rfid($username) {
  $result = array();
  $mitarbeiter = array(); 
  $file = fopen('./Data/users.txt', 'r');
  fgets($file, 4096); // erste Zeile lesen und damit überspringen
  while (! feof($file)) {
    $line = fgets($file, 4096);
    $result[] = $line;
  }
  fclose($file); 
  foreach($result AS $zeile) {
    if(strpos($zeile, $username) !== false) {
      $ergebnis = explode(';', $zeile);
			$rfid = $ergebnis[3];
			$rfid = trim ($rfid, "\n\r\0\x0B");
    } 
  }  
  return $rfid;
}



// Zeitzone setzten, damit die Stunden richtig ausgerechnet werden
date_default_timezone_set("Europe/Paris");
@setlocale(LC_TIME, 'de_DE.UTF-8', 'de_DE@euro', 'de_DE', 'de-DE', 'de', 'ge', 'de_DE.UTF-8', 'German');

$_grpwahl = '2';
$gruppe = 2;
if(isset($_GET['gruppe'])){
	$_grpwahl =  $_GET['gruppe'];
	$gruppe = $_GET['gruppe'];
}
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
include_once ('./include/class_absenz.php');
include_once ('./include/class_user.php');
include_once ('./include/class_group.php');
include_once ('./include/class_login.php');   
include_once ('./include/class_template.php');
include_once ('./include/class_time.php');
include_once ('./include/class_month.php');
include_once ('./include/class_jahr.php');
include_once ('./include/class_feiertage.php');
include_once ('./include/class_filehandle.php');
include_once ('./include/class_rapport.php');
include_once ('./include/class_show.php');
include_once ('./include/class_settings.php');
include ("./include/time_funktionen.php");
$_grpwahl = $_grpwahl-1;
$_group = new time_group($_grpwahl);
if(isset($id)) $_grpwahl = $_group->get_usergroup($id);
$anzMA = count($_group->_array[1][$_grpwahl]);	


foreach($_group->_array[0] as $gruppen){
	//echo $gruppen[0];
}				

if(isset($_GET['json'])){
	//-------------------------------------------------------------------------------------------------------------
	// Anwesenheitsliste in ein JSON laden
	//-------------------------------------------------------------------------------------------------------------
	$tmparr = array();

	for($x=0; $x<$anzMA ;$x++){
		$tmparr[$x]['gruppe'] = trim($_group->_array[0][$_grpwahl][$x]);		
		$tmparr[$x]['mitarbeiterid'] = trim($_group->_array[1][$_grpwahl][$x]);	
		$tmparr[$x]['loginname'] = trim($_group->_array[2][$_grpwahl][$x]);	
		$tmparr[$x]['pfad'] = trim($_group->_array[3][$_grpwahl][$x]);
		$tmparr[$x]['username'] = trim($_group->_array[4][$_grpwahl][$x]);	
		$tmparr[$x]['rfid'] = find_rfid(trim($_group->_array[2][$_grpwahl][$x]));	
		//Anwesend oder nicht
		$tmparr[$x]['anwesend'] = (count($_group->_array[5][$_grpwahl][$x]))%2;	
		if($tmparr[$x]['anwesend']){
			$tmparr[$x]['status'] = 'Anwesend';
		}else{
			$tmparr[$x]['status'] = 'Abwesend';
		}				
		// Mitarbeiter - Bild anzeigen
		if(file_exists("./Data/".$_group->_array[2][$_grpwahl][$x]."/img/bild.jpg")){		
			$tmparr[$x]['bild'] = "./Data/".$_group->_array[2][$_grpwahl][$x]."/img/bild.jpg";	
		}else{
			$tmparr[$x]['bild'] = "./images/ico/user-icon.png";	
		}	
		// Mitarbeiter letzte Buchung bzw. alle Tagesbuchungen anzeigen
		if(isset($_group->_array[5][$_grpwahl][$x][count($_group->_array[5][$_grpwahl][$x])-1])){
			$tmparr[$x]['lasttime'] = $_group->_array[5][$_grpwahl][$x][count($_group->_array[5][$_grpwahl][$x])-1];	
			$tmparr[$x]['alltime'] = implode(" - ", $_group->_array[5][$_grpwahl][$x]);	
		}else{
			$tmparr[$x]['lasttime'] = '';
			$tmparr[$x]['alltime'] = '';
		}	
		// Mitarbeiter Passwort
		$tmparr[$x]['passwort'] = trim($_group->_array[7][$_grpwahl][$x]); 
	

 		$idtime_secret = 'CHANGEME';
		// stempeln über idtime
		//http://localhost:88/Kunden/time.repmo.ch/idtime.php?id=1864f9f71f65975b
		$hash = sha1($tmparr[$x]['pfad'].$tmparr[$x]['passwort'].crypt($tmparr[$x]['pfad'], '$2y$04$'.substr($idtime_secret.$tmparr[$x]['passwort'], 0, 22)));
		$tmparr[$x]['idtime'] = substr($hash, 0, 16);
	}	
	echo json_encode($tmparr);
}else{
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<meta charset="utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
			<!--<meta http-equiv="refresh" content="10">-->
			<title>SmallTime - Touch - Screen - Stempelterminal</title>
			<link href='https://fonts.googleapis.com/css?family=Rambla:400,700' rel='stylesheet' type='text/css'>
			<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
			<link href='https://fonts.googleapis.com/css?family=Ubuntu+Mono:400,400italic,700italic,700' rel='stylesheet' type='text/css'>		
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.js"></script>		
			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.8/css/materialize.min.css">
			<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.8/js/materialize.min.js"></script>		
			<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">	
			<script src="https://cdnjs.cloudflare.com/ajax/libs/mustache.js/2.3.0/mustache.js"></script>		
			<style>
				body{
					background-color: #70858f;
					font-size: 0.8em;
				}
				.alert-danger, .alert-error {
					background-color: #e9c7c7;
					border-color: #da9e9e;
				}
				.alert-success {
					background-color: #d1e9c7;
					border-color: #A6C48D;
				}
				.alert {
					padding: 6px 6px 6px 6px;
					margin-bottom: 20px;
					text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
					/*background-color: #fcf8e3;*/
					border: 1px solid #fbeed5;
					-webkit-border-radius: 0px;
					-moz-border-radius: 0px;
					border-radius: 0px;
					color: #000000;
				}
				table{
					margin-top: 2em;
				}
				.mitarbeiter{
					display: -ms-flex; 
					display: -webkit-flex; 
					display: flex;
					margin: 10px;
				}
				.bild{
					-webkit-flex: 1;
					flex: 1;
					-webkit-order: 1;
					order: 1;
				}
				.bild img{
					        width: 100%;
				}
				.name{
					-webkit-flex: 2;
					flex: 2;
					-webkit-order: 2;
					order: 2;
				}
				.row{
				}
				nav ul li.active {
					background-color: rgba(0, 0, 0, 0.6);
				}
				.container .row {
					margin-left: 0;
					margin-right: 0;
				}
				@media only screen and (min-width: 1200px){
					.container{
						width: 80%;
						max-width: 1600px;
					}
				}
				@media only screen and (min-width: 993px){
					.container{
						width: 95%;
					}	
				}
				@media only screen and (min-width: 601px){
					.container{
						width: 95%;
					}	
				}
			</style>
			<script>
				(function($)
					{
						$(function()
							{	
								$(".button-collapse").sideNav();
									
							
							}); // End Document Ready
					})(jQuery); // End of jQuery name space 

				<!-- encryptfunction sha1 -->	
							/**
						* Secure Hash Algorithm (SHA1)
						* http://www.webtoolkit.info/
						**/
						function SHA1(msg) {
						function rotate_left(n,s) {
						var t4 = ( n<<s ) | (n>>>(32-s));
						return t4;
						};
						function lsb_hex(val) {
						var str='';
						var i;
						var vh;
						var vl;
						for( i=0; i<=6; i+=2 ) {
						vh = (val>>>(i*4+4))&0x0f;
						vl = (val>>>(i*4))&0x0f;
						str += vh.toString(16) + vl.toString(16);
						}
						return str;
						};
						function cvt_hex(val) {
						var str='';
						var i;
						var v;
						for( i=7; i>=0; i-- ) {
						v = (val>>>(i*4))&0x0f;
						str += v.toString(16);
						}
						return str;
						};
						function Utf8Encode(string) {
						string = string.replace(/\r\n/g,'\n');
						var utftext = '';
						for (var n = 0; n < string.length; n++) {
						var c = string.charCodeAt(n);
						if (c < 128) {
						utftext += String.fromCharCode(c);
						}
						else if((c > 127) && (c < 2048)) {
						utftext += String.fromCharCode((c >> 6) | 192);
						utftext += String.fromCharCode((c & 63) | 128);
						}
						else {
						utftext += String.fromCharCode((c >> 12) | 224);
						utftext += String.fromCharCode(((c >> 6) & 63) | 128);
						utftext += String.fromCharCode((c & 63) | 128);
						}
						}
						return utftext;
						};
						var blockstart;
						var i, j;
						var W = new Array(80);
						var H0 = 0x67452301;
						var H1 = 0xEFCDAB89;
						var H2 = 0x98BADCFE;
						var H3 = 0x10325476;
						var H4 = 0xC3D2E1F0;
						var A, B, C, D, E;
						var temp;
						msg = Utf8Encode(msg);
						var msg_len = msg.length;
						var word_array = new Array();
						for( i=0; i<msg_len-3; i+=4 ) {
						j = msg.charCodeAt(i)<<24 | msg.charCodeAt(i+1)<<16 |
						msg.charCodeAt(i+2)<<8 | msg.charCodeAt(i+3);
						word_array.push( j );
						}
						switch( msg_len % 4 ) {
						case 0:
						i = 0x080000000;
						break;
						case 1:
						i = msg.charCodeAt(msg_len-1)<<24 | 0x0800000;
						break;
						case 2:
						i = msg.charCodeAt(msg_len-2)<<24 | msg.charCodeAt(msg_len-1)<<16 | 0x08000;
						break;
						case 3:
						i = msg.charCodeAt(msg_len-3)<<24 | msg.charCodeAt(msg_len-2)<<16 | msg.charCodeAt(msg_len-1)<<8 | 0x80;
						break;
						}
						word_array.push( i );
						while( (word_array.length % 16) != 14 ) word_array.push( 0 );
						word_array.push( msg_len>>>29 );
						word_array.push( (msg_len<<3)&0x0ffffffff );
						for ( blockstart=0; blockstart<word_array.length; blockstart+=16 ) {
						for( i=0; i<16; i++ ) W[i] = word_array[blockstart+i];
						for( i=16; i<=79; i++ ) W[i] = rotate_left(W[i-3] ^ W[i-8] ^ W[i-14] ^ W[i-16], 1);
						A = H0;
						B = H1;
						C = H2;
						D = H3;
						E = H4;
						for( i= 0; i<=19; i++ ) {
						temp = (rotate_left(A,5) + ((B&C) | (~B&D)) + E + W[i] + 0x5A827999) & 0x0ffffffff;
						E = D;
						D = C;
						C = rotate_left(B,30);
						B = A;
						A = temp;
						}
						for( i=20; i<=39; i++ ) {
						temp = (rotate_left(A,5) + (B ^ C ^ D) + E + W[i] + 0x6ED9EBA1) & 0x0ffffffff;
						E = D;
						D = C;
						C = rotate_left(B,30);
						B = A;
						A = temp;
						}
						for( i=40; i<=59; i++ ) {
						temp = (rotate_left(A,5) + ((B&C) | (B&D) | (C&D)) + E + W[i] + 0x8F1BBCDC) & 0x0ffffffff;
						E = D;
						D = C;
						C = rotate_left(B,30);
						B = A;
						A = temp;
						}
						for( i=60; i<=79; i++ ) {
						temp = (rotate_left(A,5) + (B ^ C ^ D) + E + W[i] + 0xCA62C1D6) & 0x0ffffffff;
						E = D;
						D = C;
						C = rotate_left(B,30);
						B = A;
						A = temp;
						}
						H0 = (H0 + A) & 0x0ffffffff;
						H1 = (H1 + B) & 0x0ffffffff;
						H2 = (H2 + C) & 0x0ffffffff;
						H3 = (H3 + D) & 0x0ffffffff;
						H4 = (H4 + E) & 0x0ffffffff;
						}
						var temp = cvt_hex(H0) + cvt_hex(H1) + cvt_hex(H2) + cvt_hex(H3) + cvt_hex(H4);

						return temp.toLowerCase();
						}
				<!-- ende encryptfunction sha1 -->


				function start(){
					uebersicht('?gruppe=<?php echo $gruppe; ?>&json');
					//uebersicht('repmo_json.php?group=<?php echo $_grpwahl; ?>&json');
				}

			
				function mastempeln(ma_id, passwort, rfid){
					//console.log(ma_id);
					//console.log(passwort);
					//console.log(rfid);
					var pw = window.prompt("Bitte Passwort eingeben: ");
					//console.log(pw);
					pw = SHA1(pw);
					//console.log(pw);
					if(pw == passwort) {
						idtime(ma_id);
						link = 'http://zeit/zeit/android.php?rfid=' + rfid;
						information = window.open(link,'_blank','resizeable=false,top=200,width=300,height=300',3000);
						setTimeout(function(){information.close()},8000);
					}
					else {
						window.alert("Falsches Passwort eingegeben.")
					}
				}

				function idtime(id)
				{
					$.ajax(
						{
							url: 'idtime.php?id=' + id + '&w=no',
							//url: url,
							type: 'get',
							dataType: 'text',
							async: true,
							success: function(response)
							{
								console.log("Response ID Time")
								console.log(response);
								uebersicht('?gruppe=<?php echo $gruppe; ?>&json');
							}
						});
				}		
				function uebersicht(url)
				{
					$.ajax(
						{
							//url: 'idtime.php?id=' + id + '&w=no',
							url: url,
							type: 'get',
							dataType: 'json',
							async: true,
							success: function(response)
							{
								console.log(response);
								$('#maanzeige').html('');
								//$('#matemplate').show();
								var panel = $('#matemplate').clone();
								$('#matemplate').hide();
								for (i = 0; i < response.length; i++) {
									var new_panel = panel.clone();
									// Tabelle farblich unterscheiden
									if (response[i].anwesend == 1) {
										new_panel.find('.mitarbeiter').addClass('green lighten-1');
									} else {
										new_panel.find('.mitarbeiter').addClass('deep-orange accent-2');
									}
									//<img src="{{bild}}" alt="{{username}}" />
									new_panel.find('#img').html('<img src="'+ response[i].bild+'" alt="'+ response[i].username+'" />');
									var html_for_mustache = new_panel.html();
									var html = Mustache.to_html(html_for_mustache, response[i]);
									$('#maanzeige').append(html);
								};
							}
						});
				}
			</script>
		</head>	
		<body onload="start();" >
			<!--  NAVIGATION !-->
			<nav class="navbar blue-grey darken-3" role="navigation">
				<div class="nav-wrapper container">
					<a id="logo-container" href="?" class="brand-logo"><span class="fa fa-fw fa-home fa-1x"></span>Home</a>
					<a href="#" data-activates="mobile-demo" class="button-collapse"><i class="material-icons">menu</i></a>
					<ul class="right hide-on-med-and-down">
						<?php 
						$i=2;
						foreach($_group->_array[0] as $gruppen){
							echo '<li ';
							if(intval($gruppe)==$i)  echo 'class="active" '; 
							echo 'id="menue'.$i.'" ><a href="?gruppe='.$i.'" id="seite'.$i.'"><i class="fa fa-users fa-2"></i> '. $gruppen[0]. '</a></li>';
							$i++;
						}
						echo '<li ><a href="index.php" target="_new"><i class="fa fa-home fa-2"></i> Index.php</a></li>';
						echo '<li ><a href="admin.php" target="_new"><i class="fa fa-lock fa-2"></i> Admin.php</a></li>';
						echo '</ul><ul class="side-nav" id="mobile-demo">';
						$i=2;
						foreach($_group->_array[0] as $gruppen){
							echo '<li ';
							if(intval($gruppe)==$i) echo 'class="active" '; 
							echo 'id="mobmenue'.$i.'"><a href="?gruppe='.$i.'" id="mobseite'.$i.'"><i class="fa fa-users fa-2"></i> '. $gruppen[0].'</a></li>';
							$i++;
						}
						echo '<li ><a href="index.php" target="_new"><i class="fa fa-home fa-2"></i> Index.php</a></li>';
						echo '<li ><a href="admin.php" target="_new"><i class="fa fa-lock fa-2"></i> Admin.php</a></li>';
						?>
					</ul>
				</div>
			</nav>
			<!--  CONTENT  !-->
			<div class="container" id="ContentHTML">
				<div class="container">
					<div id="maanzeige" class="row"></div>
					<div id="matemplate"  style="visibility: hidden">
						<div class="col s12 m6 l4">
							<div class=" mitarbeiter " onclick="mastempeln('{{idtime}}','{{passwort}}','{{rfid}}')" >
								<!-- <div class="bild" id="img"><img src="{{bild}}" alt="{{username}}"  /></div> -->
								<div class="name"><h5 style="margin-left: 15px;">{{username}}</h5>
									<!-- 
										<p>
									{{alltime}}
									<hr>
									{{status}} seit: {{lasttime}}</p> 
									-->
								</div>
							</div>
						</div>
						
					</div>
				</div>
			</div>
		</body>
	</html> 
	<?php
}
?>