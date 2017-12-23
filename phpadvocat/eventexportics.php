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
require("./include/dialog.php");

/* Get User Account from Session Vars */
$user = $_SESSION["dbuser"];
$passwd = $_SESSION["dbpasswd"];

$changecheck="";

/* initialize database */
$db = new www_db;
$db->connect($user, $passwd);

// The Function
function dateToCal($time) {
return date('Ymd\This', $time) . 'Z';
}
 
function isotoicaldate($datestring) {
 // $datestring='2013-03-22 12:01:01';
 $icalstring='';

 $year=(int) substr($datestring, 0, 4);
 $month=(int) substr($datestring, 5, 2);
 $day=(int) substr($datestring, 8, 2);
 $hour=(int) substr($datestring, 11, 2);
 $minute=(int) substr($datestring, 14, 2);
 $second=(int) substr($datestring, 17, 2);
 
 if(is_int($year) && is_int($month) && is_int($day) &&
    checkdate($month, $day, $year)) {
        /* generate ICAL-Format timestamp 19971217T073716 */
        $icalstring = sprintf("%04d%02d%02dT%02d%02d%02d",
           $year, $month, $day, $hour, $minute, $second);
 }
 return $icalstring;
} 
 
function isotoicaldate_plus($datestring) {
 // $datestring='2013-03-22 12:01:01';
 $icalstring='';

 $year=(int) substr($datestring, 0, 4);
 $month=(int) substr($datestring, 5, 2);
 $day=(int) substr($datestring, 8, 2);
 $hour=(int) substr($datestring, 11, 2);
 $minute=(int) substr($datestring, 14, 2);
 $second=(int) substr($datestring, 17, 2);
 
 if ($hour <=23) $hour++;
 if(is_int($year) && is_int($month) && is_int($day) &&
    checkdate($month, $day, $year)) {
        /* generate ICAL-Format timestamp 19971217T073716 */
        $icalstring = sprintf("%04d%02d%02dT%02d%02d%02d",
           $year, $month, $day, $hour, $minute, $second);
 }
 return $icalstring;
} 
 


/* import pnumber if transmitted by GET */
if($_POST["enumber"] != 0) {
  $enumber = $_POST["enumber"];
} elseif($_GET["enumber"] != 0) {
  $enumber = $_GET["enumber"];
}
if($enumber <> 0) { 

      $querystring = sprintf("select * from phpa_events  " .
         "where number =%s", $enumber);
      $result = $db->query($querystring);
      if ($db->rows>=1) {
      	$db->next_record();
		//  echo "<hr>". $db->record['description'] . "<hr>\n";
		
		// Fetch vars
		$event = array(
		'id' => '20130200000000' . trim($db->record['number']),
		'title' => $db->record['description'],
		'address' => $db->record['location'],
		'description' => $db->record['description'],
		'datestart' => $db->record['eventstart'],
 		'dateend' => $db->record['eventend'],
		// 'address' => $db->record['stage']
		);
		 
		// iCal date format: yyyymmddThhiissZ
		// PHP equiv format: Ymd\This
      }
}


 
// Build the ics file
$ical = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//phpadvocat/handcal//NONSGML v1.0//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTEND:' . isotoicaldate($event['dateend']) . '
UID:' . $event['id'] . '
DTSTAMP:' . time() . '
LOCATION:' . addslashes($event['address']) . '
DESCRIPTION:' . addslashes($event['description']) . '
SUMMARY:' . addslashes($event['title']) . '
DTSTART:' . isotoicaldate($event['datestart']) . '
END:VEVENT
END:VCALENDAR';

// Original UID:
// UID:' . md5($event['id']) . '

 
//set correct content-type-header
if($event['id']){
header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=phpadvocat-event.ics');
echo $ical;
} else {
// If $id isn't set, then kick the user back to home. Do not pass go, and do not collect $200.
// header('Location: /');
}