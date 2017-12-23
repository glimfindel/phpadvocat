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

/* import invoice number if transmitted by GET or POST */
if($_POST["filenum"]) {
  $pfile = $_POST["pfile"];
  $address = $_POST["address"];
  $subject = $_POST["subject"];
  $savebutton = $_POST["savebutton"];
  $pdfbutton = $_POST["pdfbutton"];
  $filenum = $_POST["filenum"];
  $filecontent = $_POST["filecontent"];
} elseif($_GET["filenum"]) {
  $savebutton = "";
  $pfile = $_GET["pfile"];
  $filenum = $_GET["filenum"];
  $address = $_GET["address"];
  $subject = $_GET["subject"];
}

  $changecheck='';

/* check file name to prevent unauthorized acces on system files */
  $filebase = './files';
  $querystring = sprintf("select * from phpa_config where number=%s",1);
  if(!$db->query($querystring) && $db->next_record() && $db->record["filebase"] != '') {
     $filebase = trim($db->record["filebase"]);
     $filebaselen = strlen($filebase);
  }



if ($savebutton != "") {
  $querystring = sprintf("update phpa_dfiles set dfilecontent='%s', address=%s, subject='%s' where number=%s", 
                         base64_encode($filecontent), $address, $subject, $filenum);
  // echo "<hr>".$querystring."<hr>";

  if(!$db->query($querystring) && $db->next_record()) 
     $changecheck = "gesichert!";
}
/* get document from database */
  $strict = true;
  $querystring = sprintf("select * from phpa_dfiles where number=%s", $filenum);
  if(!$db->query($querystring) && $db->next_record()) {
     $filecontent = base64_decode($db->record["dfilecontent"], $strict);
     if (!$strict) $filecontent = $db->record["dfilecontent"];
     $dsubject = $db->record["subject"];
     $pfile = $db->record["pfile"];
  }




  echo "<HTML><HEAD><TITLE>PHPAdvocat - Schriftverkehr</TITLE>";
  echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-15\">\n";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"include/phpadvocat.css\">";
echo "</HEAD>";

  echo "<BODY BGCOLOR=\"#FFFFFF\" TEXT=\"#000000\">\n";

  echo "<TABLE width=100%><TR><TD width=200 valign=top>";


  /* here comes the menue */
  $phpa_menue->account=$user;
  $phpa_menue->selected = 3;
   array_insert($phpa_menue->contents,
      array( sprintf("&nbsp;&nbsp;<b><a href=pfileedit.php?pnumber=%s&detail=4>".
                     "Akte bearbeiten</a></b>",$pfile)), 2);
   array_insert($phpa_menue->contents,
      array('&nbsp;&nbsp;&nbsp;&nbsp;<b>Schriftverkehr</b>'), 3);

  $phpa_menue->draw_menue();


/* display Title */
echo "</TD><TD><CENTER><H1>Schriftverkehr</H1></CENTER>";

echo "<table width=100%><tr>\n";
echo "<td>" . date("d.m.Y", time()) . "</td>";
/* display status at right side */
echo "<TD ALIGN=RIGHT><b>". $changecheck. "</b></A></TD>";
print "</tr></table>\n";
echo "<hr>";


echo "<table  class=inputtable>";

$dfiledialog = new htmldialog;

/* only show file-nr, hidden detail nr */
/* *********************************************************************** */
$dfiledialog->addinput("Schriftnummer:", 
sprintf("<input name=filenum type=hidden value=\"%s\">%s\n".
        "<input name=pfile type=hidden value=\"%s\">\n",
        $db->record["number"], $db->record["number"], $db->record["pfile"]));

/* choose addressee */
/* data for drop down list */
$dblist = new www_db;
$dblist->connect($user, $passwd);
$querystring="select * from phpa_partner order by name, organization";
$dblist->query($querystring);

$optionlist = "<select name=address>\n";
   while($dblist->next_record()) {
      if($dblist->record["number"] == $db->record["address"])
        $optionlist .= sprintf("<option selected value=\"%s\">%s,%s; %s\n",
          $dblist->record["number"],
          $dblist->record["name"],
          $dblist->record["prename"],
          $dblist->record["organization"]);
      else
        $optionlist .= sprintf("<option value=\"%s\">%s,%s; %s\n",
          $dblist->record["number"],
          $dblist->record["name"],
          $dblist->record["prename"],
          $dblist->record["organization"]);
   }
   $dblist->close();
$optionlist .= "</select>\n";
$dfiledialog->addinput("Adresse:",$optionlist);

/* display processregister */
$dfiledialog->addinput("Betreff:", 
   sprintf("<input name=subject type=text size=20 value=\"%s\">\n",
        $db->record["subject"]));

$dfiledialog->addinput("", 
sprintf("<TEXTAREA NAME=filecontent WRAP=VIRTUAL COLS=80 ROWS=25>%s</TEXTAREA>\n",
        base64_decode($db->record["dfilecontent"], $strict)));
        
$dfiledialog->addinput("<input type=submit name=savebutton value=Speichern>",
   "<table width=100%><tr><td align=center><a href=\"letterpdf.php?filenum=".$filenum ."&head=0\" target=_BLANK>Blanco</a></td> ".
   "<td align=center><a href=\"letterpdf.php?filenum=".$filenum ."&head=1\" target=_BLANK>Briefkopf</a></td> ".
   "<td align=center><a href=\"letterpdf.php?filenum=".$filenum ."&head=2\" target=_BLANK>Standard-Kopf</a></td> ".
   "<td align=center><a href=\"letterpdf.php?filenum=".$filenum ."&head=1&mail=1\" target=_BLANK>Email</a></td></tr></table>");
 
        
$dfiledialog->out();
/* *********************************************************************** */

echo "</table>\n";


$db->close();


/* End HTML PAGE */
echo "</td></tr></table></BODY></HTML>";
?>
