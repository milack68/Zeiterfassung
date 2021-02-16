<?php
$output = '';
if(isset($_GET['rfid']))
{
	$uri = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'];
	$uri .= $_SERVER['PHP_SELF'];
	$uri    = str_ireplace('terminal.php','', $uri);
	$html   = $uri.'idtime.php?rfid='. $_GET['rfid'];
	$idtime = file_get_contents($html);
	$output .= '-------------------------------------------------------------------'.chr(10);
	$html = $uri.'android.php?rfid='. $_GET['rfid']. '&action=getvar&class=_user&var=_name';
	$output .= file_get_contents($html);
	$output .= '-------------------------------------------------------------------'.chr(10);
	$html = $uri.'android.php?rfid='. $_GET['rfid']. '&action=tag';
	$output .= file_get_contents($html);
	$output .= chr(10).'--------------------------------'. chr(10);
	$html = $uri.'android.php?rfid='. $_GET['rfid'];
	$output .= file_get_contents($html);
	$output .= '-------------------------------------------------------------------'.chr(10);
	$output .= 'Saldo Total: ';
	$html = $uri.'android.php?rfid='. $_GET['rfid']. '&action=getvar&class=_jahr&var=_summe_t';
	$output .= file_get_contents($html);
	$output .= chr(10);
	$output .= '-------------------------------------------------------------------'.chr(10);
}
else
{
	$output = "Fehler!";
}
echo $output;
?>