<?php
  /**************************************************************************\
  * PHPAdvocat                                                               *
  * http://phpadvocat.sourceforge.net                                        *
  * By Burkhard Obergoeker <phpadvocat@obergoeker.de>                        *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

require("./include/phpadvocat.inc.php");

$user = $_SESSION["dbuser"];
$passwd = $_SESSION["dbpasswd"];

/* initialize database */

$db = new www_db;
$db->connect($user, $passwd);

$querystring="select filebase, pdf_command from phpa_config";
$db->query($querystring);

$filebase = "./files";
if ($db->next_record() && $db->record["filebase"] != "") 
	$filebase = $db->record["filebase"];

$pdf_command = $db->record["pdf_command"];

$db->close();

if($pdf_command == "") {
	echo "No PDF command set!\n";
	die();
}

$file_raw = $filebase . "/" . $_REQUEST["file"] . "";

if($file_raw == "") {
	echo "FAIL!\n";
	die();
}

$filebase_full = realpath($filebase);
$file_full = realpath($file_raw);

if(strpos($file_full, $filebase_full) !== 0) {
	echo "Path must point to a file within the filepath tree, but it is not." . $file_full . "<br>";
	die();
}

$name = escapeshellarg(basename($file_raw));
$file = escapeshellarg($file_raw);
$path = escapeshellarg(dirname($file_raw));

$out = array();
$ret_var = -1;

$cmd = str_replace("%p", $path, $pdf_command);
$cmd = str_replace("%n", $name, $cmd);
$cmd = str_replace("%f", $file, $cmd);

echo "Converting $file via command \"$cmd\"...<br>";

//exec("lowriter --headless --convert-to pdf $file --outdir $path", $out, $ret_var);
exec($cmd, $out, $ret_var);
echo "Return value: $ret_var <br>";
if( count($out) > 0) {
	foreach($out as $line) {
		echo $line . "<br>";
	}
	//construct output name
	$outname = preg_replace('"\.[^\.]+$"', '.pdf', $file_raw);
	
	echo "Redirecting to $outname <br>";
	sleep(1);
	if (file_exists($outname)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($outname));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($outname));
		ob_clean();
		flush();
		readfile($outname);
		exit;
	}
	else {
		echo "Failed! <br>";
	}
} else {
	echo "Fail! Could not create PDF.<br>";
	die();
}

?>

