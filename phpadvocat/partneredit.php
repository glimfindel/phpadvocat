<?php
  /**************************************************************************\
  * PHPAdvocat                                                               *
  * http://phpadvocat.sourceforge.net                                        *
  * By Burkhard Obergoeker <phpadvocat@obergoeker.de>                       *
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

/* import pnumber if transmitted by GET */
if($_POST["number"] != 0) {
  $number = $_POST["number"];
} elseif($_GET["number"] != 0) {
  $number = $_GET["number"];
} else {
  $number=0;
}

/* initialize database */
$db = new www_db;
$db->connect($user, $passwd);

/* if partner number is 0 the create a new record with empty fields */
if($number==0) {
   $querystring = "insert into phpa_partner (type, title) ".
    "values ('Person', 'Frau')";
   // echo "<hr>". $querystring . "<hr>";
   if (!$db->query($querystring)) {
      $changecheck="Neue Adresse";
   }
   /* evaluate new number */
   $querystring = "select max(number) as maxnum from phpa_partner";
   if (!$db->query($querystring) && $db->next_record()) {
      $number = $db->record["maxnum"];
      /* if file is called due to file creation, use another submit button */
      $next2createfile=1;
   }
} /* end creation of new record */

/********** handle updates and inserts begin ********************************/
if($_POST["partnereditbutton"]) {
  $type=$_POST["type"];
  $title=$_POST["title"];
  $name=$_POST["name"];
  $prename=$_POST["prename"];
  $organization=$_POST["organization"];
  $number=$_POST["number"];
  $street=$_POST["street"];
  $zip=$_POST["zip"];
  $city=$_POST["city"];
  $phone=$_POST["phone"];
  $mobile=$_POST["mobile"];
  $fax=$_POST["fax"];
  $email=$_POST["email"];
  $insurance_id=$_POST["insurance_id"];
  $insurance_number=$_POST["insurance_number"];

   $querystring = sprintf("update phpa_partner set " .
      "title='%s', ".
      "type='%s', ".
      "name='%s', ".
      "prename='%s', ".
      "organization='%s', ".
      "street='%s', ".
      "zip='%s', ".
      "city='%s', ".
      "phone='%s', ".
      "mobile='%s', ".
      "fax='%s', ".
      "email='%s', ".
      "insurance_id='%s', ".
      "insurance_number=%s ".
      "where number=%s",
       $title,
       $type,
       $name, 
       $prename, 
       $organization,
       $street,
       $zip,
       $city,
       $phone,
       $mobile,
       $fax,
       $email,
       $insurance_id,
       $insurance_number,
       $number);

   if (!$db->query($querystring)) {
        $changecheck="Eintrag ge&auml;ndert";
   }
}
/********** handle updates and inserts end *********************************/



?>
<HTML>
<script language="JavaScript">
<!--
        function heute()
        {
                jetzt = new Date();
                var tag = jetzt.getDate();
                var monat = jetzt.getMonth();
                var jahr = jetzt.getYear();
                if(jahr < 1000) jahr+=1900;
                monat+=1;
                var datum = tag + "." + monat + "." + jahr;
                return datum;
        }
//-->
</script>
<?php
 /* begin HTML page */
echo "<HEAD><TITLE>PHPAdvocat - Adressen</TITLE>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-15\">\n";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"include/phpadvocat.css\">";
echo "</HEAD>";
echo "<BODY BGCOLOR=\"#FFFFFF\" TEXT=\"#000000\">\n";

/* begin table framework */
echo "<TABLE width=100%><TR><TD width=200 valign=top>\n";

  /* here comes the menue */
  $phpa_menue->account=$user;
  $phpa_menue->selected = 3;
   array_insert($phpa_menue->contents,
      array('&nbsp;&nbsp;&nbsp;&nbsp;<b>Adresse &auml;ndern</b>'), 3);

  $phpa_menue->draw_menue();

echo "</TD><TD>";

/* display Title */
echo "<CENTER><H1>Bearbeitung Adresse</H1></CENTER>";


$querystring = 
  sprintf("select * from phpa_partner where number=%s", $number);
$db->query($querystring);
$db->next_record();

/* database connection for drop down list */
$dblist = new www_db;
$dblist->connect($user, $passwd);


echo "<table width=100%><tr>\n";
echo "<td>" . date("d.m.Y", time()) . "</td>";
echo "<TD ALIGN=RIGHT>". $changecheck ."</A></TD>";
print "</tr></table>\n";

print "<hr><center>";


 /* beginning frame of dialog */
$partnerdialog = new htmldialog;


if(1==$next2createfile) { /* I'm coming to create a new pfile */
   $partnerdialog->stringbegin = 
      "<center><table width=90%% border=1><tr><td>\n".
      "<FORM METHOD=POST ACTION=filesrequest.php><table>\n";
}


/* only show file-nr, hidden detail nr */

$mynumber = $db->record["number"];

$partnerdialog->addinput("Partnernummer:", 
   sprintf("<input name=number type=hidden value=\"%s\">%s".
           "<input name=detail type=hidden value=\"%s\">",
           $mynumber, $mynumber,$detail));


/* select type */
/* get a list of types from database */
$querystring="select * from phpa_partnertypes";
$dblist->query($querystring);
$optionlist ="<select name=type>\n";
 while($dblist->next_record()) {
    if($dblist->record["type"] == $db->record["type"])
       $optionlist .= sprintf("<option selected>%s\n", $dblist->record["type"]); 
    else
       $optionlist .= sprintf("<option>%s\n", $dblist->record["type"]); 
 }
$optionlist .="</select>\n";
$partnerdialog->addinput("Partnertyp:",$optionlist);



/* input title */
$partnerdialog->addinput("Titel:", 
   sprintf("<input name=title type=text size=10 value=\"%s\">",
           $db->record["title"]));



/* input prename */
$partnerdialog->addinput("Vorname:", 
   sprintf("<input name=prename type=text size=20 value=\"%s\">",
           $db->record["prename"]));

/* input name */
$partnerdialog->addinput("Name:", 
   sprintf("<input name=name type=text size=20 value=\"%s\">",
           $db->record["name"]));

/* input organization */
$partnerdialog->addinput("Organisation:", 
   sprintf("<input name=organization type=text size=20 value=\"%s\">",
           $db->record["organization"]));


/* input street */
$partnerdialog->addinput("Strasse/Nr.:", 
   sprintf("<input name=street type=text size=20 value=\"%s\">",
           $db->record["street"]));

/* input zip code and city */
$partnerdialog->addinput("PLZ/Ort:", 
   sprintf("<input name=zip type=text size=7 value=\"%s\">\n",
        $db->record["zip"]) .
   sprintf("<input name=city type=text size=20 value=\"%s\">\n",
        $db->record["city"]));

/* input phone number */
$partnerdialog->addinput("Telefon:", 
   sprintf("<input name=phone type=text size=20 value=\"%s\">\n",
        $db->record["phone"]));
        
$partnerdialog->addinput("Mobil:", 
   sprintf("<input name=mobile type=text size=20 value=\"%s\">\n",
        $db->record["mobile"]));

/* input fax number */
$partnerdialog->addinput("Fax:", 
   sprintf("<input name=fax type=text size=20 value=\"%s\">\n",
        $db->record["fax"]));

/* input email address */
$partnerdialog->addinput("Email:", 
   sprintf("<input name=email type=text size=20 value=\"%s\">\n",
        $db->record["email"]));

/* input insurance-ID only for insurances */
$partnerdialog->addinput("RSV Nr.:", 
   sprintf("<input name=insurance_id type=text size=20 value=\"%s\">\n",
        $db->record["insurance_id"]));




/* choosing insurance */
$querystring="select * from phpa_partner where type = 'Versicherung'";
$dblist->query($querystring);
$optionlist ="<select name=insurance_number>\n";
$optionlist .= sprintf("<option value=NULL>- Keine Versicherung -\n");
 while($dblist->next_record()) {
    if($dblist->record["number"] == $db->record["insurance_number"])
       $optionlist .= sprintf("<option selected value=%s>%s\n", $dblist->record["number"],$dblist->record["name"]); 
    else
       $optionlist .= sprintf("<option value=%s>%s\n", $dblist->record["number"], $dblist->record["name"]); 
 }
$optionlist .="</select>\n";

/* make fild caption clickable when an assurance has been given */
$caption="Versicherung:";
if ($db->record["insurance_number"] >0)
    $caption=sprintf('<a href=%s?number=%s>Versicherung:',$PHP_SELF,$db->record["insurance_number"]);

$partnerdialog->addinput($caption,$optionlist);


/* 
$partnerdialog->addinput("RSV Name:",
   sprintf("<input disabled name=insurance_name type=text size=20 value=\"%s\">\n",
        $dblist->record["name"]));
        
$partnerdialog->addinput("RSV Strasse:", 
   sprintf("<input disabled name=insurance_street type=text size=20 value=\"%s\">\n",
        $dblist->record["street"]));
        
$partnerdialog->addinput("RSV PLZ/Ort:", 
   sprintf("<input disabled name=insurance_zip type=text size=7 value=\"%s\">\n",
        $dblist->record["zip"]) .
   sprintf("<input disabled name=insurance_city type=text size=20 value=\"%s\">\n",
        $dblist->record["city"]));
        
$partnerdialog->addinput("RSV Nr.:", 
   sprintf("<input disabled name=insurance_id type=text size=20 value=\"%s\">\n",
        $dblist->record["insurance_id"]));

*/

/* display button depending of form before */
if(1==$next2createfile) { /* I'm coming to create a new pfile */
   $partnerdialog->addinput(
     "<input name=partnereditbutton type=submit value=\"Zur Akte\">\n","");
} else { /* just save the record */
   $partnerdialog->addinput(
     "<input name=partnereditbutton type=submit value=\"Sichern\">\n","");
}

$partnerdialog->out(); /* End of display framework */

printf("</table></form></center><hr><center>");
echo "<CENTER><H3>Zugeh&ouml;rige Akten</H3></CENTER>\n";

$querystring = 
  "select p.number as pnumber, ".
  "p.processregister as processregister, " .
  "p.createdate as cdate, " .
  "p.enddate as edate, " .
  "p.subject as subject " .
  "from phpa_pfiles p " .
  "where p.partner=$mynumber ";


/* Sort by table header */
switch ($_GET["fsort"]) {

  case "number"  :$querystring .= "order by processregister";
                  break;
  case "name"    :$querystring .= "order by name";
                  break;
  case "subject" :$querystring .= "order by subject";
                  break;

  default        :$querystring .= "order by createdate desc";
}

$db->query($querystring);

printf("<table class=listtable>\n");
printf("<th><a href=$PHP_SELF?fsort=number>Register</a></th>");
printf("<th><a href=$PHP_SELF?fsort=cdate>Datum</a></th>");
printf("<th><a href=$PHP_SELF?fsort=subject>Bezeichnung</a></th>");

while($db->next_record()) {
   if($db->record["edate"] == '') {
     printf("<tr>");
   } else {
     /* make row grey if file is closed */
     printf("<tr bgcolor=grey>");
   }
	/* printf("<td>%s</td>", $db->row); */
	printf("<td><a href=\"pfileedit.php?pnumber=%s\">%s (%s)</a></td>",
		 $db->record["pnumber"], $db->record["processregister"], 
		 $db->record["pnumber"]);
	printf("<td>%s</td>", tolocaldate($db->record["cdate"],'DE'));
	printf("<td>%s</td>", $db->record["subject"]);
	printf("</tr>\n");
}

printf("</table><hr></center>");

$db->close();

/* end table framework */
echo "</TD></TR></TABLE>";

/* end HTML page */
echo "</BODY></HTML>";

?>
