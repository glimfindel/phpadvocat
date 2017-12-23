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
require("./include/latex.php");


/* Define Constants for Detail display */
define("DETAIL_EVENT", 1);
define("DETAIL_EXPEND", 2);
define("DETAIL_INVOICE", 3);
define("DETAIL_FILE", 4);

define("DEFAULT_PHPA_ACCOUNT",1);

/* Get User Account from Session Vars */ 
$user = $_SESSION["dbuser"];
$passwd = $_SESSION["dbpasswd"];

$changecheck="";

/* initialize database */
$db = new www_db;
$db->connect($user, $passwd);

/* import pnumber if transmitted by GET */
if($_POST["pnumber"] != 0) {
  $pnumber = $_POST["pnumber"];
  $detail = $_POST["detail"];
  $address = $_POST["address"];
} elseif($_GET["pnumber"] != 0) {
  $pnumber = $_GET["pnumber"];
  $detail = $_GET["detail"];
  $address = $_GET["address"];
}

function sortByChangedate($a, $b) {
  return $b["changedate"] - $a["changedate"];
}

/********** handle updates and inserts begin ********************************/

/****************** begin change data of pfile *************************/
if($_POST["pfileeditbutton"]) {
      /* import POST-VARS */
      $pnumber = $_POST["pnumber"];
      $processregister = $_POST["processregister"];
      $subject = $_POST["subject"];
      $value = toisonum($_POST["value"],$LOCALE);
      $partner = $_POST["partner"];
      $opposing = $_POST["opposing"];
      $opposing_rep = $_POST["opposing_rep"];
      $court = $_POST["court"];
      $createdate = toisodate($_POST["createdate"],$LOCALE);
      $enddate = toisodate($_POST["enddate"],$LOCALE);
      $endnumber = $_POST["endnumber"];
        $querystring = sprintf("update phpa_pfiles set subject='%s', " .
                "processregister='%s', partner=%s, opposing=%s, opposing_rep=%s, ".
                "court=%s, value=%s, ".
                "createdate=%s, enddate=%s, endnumber='%s' " .
                " where number=%s",
                $subject, $processregister, $partner, $opposing, $opposing_rep,
                $court,  nullcorr($value), 
                nullcorr($createdate), 
                nullcorr($enddate),
                $endnumber,
		$pnumber);
	     // echo "<hr>". $querystring ."<hr>";

        if (!$db->query($querystring)) {
                $changecheck="Eintrag ge&auml;ndert";
        }
}
/****************** end  change data of pfile *************************/

/****************** begin change data of details ***********************/
/* **************** begin handle events *******************************/ 

/* add an event */
if($_POST["eventaddbutton"]) {
      /* import POST-VARS */
      $pnumber = $_POST["pnumber"];
      $start_day = $_POST["start_day"];
      $start_month = $_POST["start_month"];
      $start_year = $_POST["start_year"];
      $start_hour = $_POST["start_hour"];
      $start_minute = $_POST["start_minute"];
      $description = $_POST["description"];
      $location = $_POST["location"];
      /* set detail for display */
      $detail=DETAIL_EVENT;
      /* check parameters */
      if(checkdate($start_month, $start_day, $start_year) &&
         ($start_minute < 60) && ($start_hour < 24)) {

         /* generate ISO-Format timestamp 1997-12-17 07:37:16-08 */
         $eventstart = sprintf("%s-%s-%s %s:%s:00-00",
	   $start_year, $start_month, $start_day, $start_hour, $start_minute);
	 // echo "<hr>". time() . "|" . $eventstart ."<hr>";
	 
	 /* simply add an hour for duration */
	   $eventend = adddatetime($eventstart, '0000-00-00 01:00:00');
         $querystring = sprintf("insert into phpa_events ".
           "(pfile, eventstart, eventend, description, location) " .
           "values(%s, '%s', '%s', '%s', '%s')", 
	   $pnumber, $eventstart, $eventend, $description, $location);
	// echo "<hr>". $querystring. "<hr>";
         if (!$db->query($querystring)) {
              $changecheck="Termin erstellt";
         }
      } else {
            $changecheck="Zeit/Datum nicht korrekt";
      }
}

/* delete an event */
elseif($_GET["eventdel"]) { /* use elseif to prevent double call */
      /* import POST-VARS */
      $pnumber = $_GET["pnumber"];
      $enumber = $_GET["enumber"];

      /* set detail for display */
      $detail = $_GET["detail"];

      $querystring = sprintf("delete from phpa_events  " .
         "where number =%s", $enumber);
      if (!$db->query($querystring)) {         
              $changecheck="Termin gel&ouml;scht";
      }
}

if($_POST["deadlineaddbutton"]) {
      /* import POST-VARS */
      $pnumber = $_POST["pnumber"];
      $eventnumber = $_POST["eventnumber"];
      $start_day = $_POST["date_day"]; /*date=20050219*/
      $start_month = $_POST["date_month"];
      $start_year = $_POST["date_year"];
      $description = $_POST["description"];
      $type = $_POST["dtype"];
      $eventdel = $_POST["eventdel"];

      /* set detail for display */
      $detail=1;
      /* check parameters */
      if(checkdate($start_month, $start_day, $start_year)) {
          
         /* new date variable */
         $date=sprintf("%04.0f%02.0f%02.0f",$start_year, $start_month, $start_day);
          
         /* generate ISO-Format timestamp 1997-12-17 07:37:16-08 */
         $eventstart = sprintf("%s-%s-%s 00:00:00-00",
          $start_year, $start_month, $start_day);
	   
         /* if eventnumer has not been set, then insert new event, */
         if($eventnumber == 0) {
           $querystring = sprintf("insert into phpa_deadlines ".
             "(pfile, eventday, description, type) " .
             "values(%s, '%s', '%s', '%s')", 
             $pnumber, $eventstart, $description, $type);
           $changecheck="Frist erstellt";
         } else { /* if number has been set, update event */
           if ($eventdel == "on") {
              $querystring = sprintf("delete from phpa_deadlines ".
                "where number=%s", $eventnumber);
              $changecheck="Frist geloescht";
           } else {
              $querystring = sprintf("update phpa_deadlines set ".
                "pfile=%s, eventday= '%s', ".
                "description='%s', type='%s' " .
                "where number=%s", 
                $pnumber, $eventstart, $description, $type,
                $eventnumber);
              $changecheck="Frist gespeichert";
           }
         }

         if (!$db->query($querystring)) {
              // $changecheck="Termin erstellt";
         } else {
              $changecheck="Datenbankfehler";
         }
      } else {
            $changecheck="Zeit/Datum nicht korrekt";
      }
}

elseif($_GET["deadlinedel"]) {
      /* import POST-VARS */
     $pnumber = $_GET["pnumber"];
      $enumber = $_GET["deadlinenumber"];

      /* set detail for display */
      $detail = $_GET["detail"];

      $querystring = sprintf("delete from phpa_deadlines  " .
         "where number =%s", $enumber);
      if (!$db->query($querystring)) {         
              $changecheck="Frist gel&ouml;scht";
      }
}

/* **************** end handle events   *******************************/ 
/* **************** begin handle expenditures *************************/ 

/* add an expenditure */
if($_POST["expenditureaddbutton"]) {
      /* import POST-VARS */
      $pnumber = $_POST["pnumber"];
      $createdate = toisodate($_POST["createdate"],$LOCALE);
      $description = $_POST["description"];
      $expendituretype = $_POST["expendituretype"];
      $incomingamount = toisonum($_POST["incomingamount"],$LOCALE);
      $incomingvat = 0.0;
      $incomingvatinc =$_POST["incomingvatinc"];
      $outgoingamount = toisonum($_POST["outgoingamount"],$LOCALE);
      $outgoingvat = 0.0;
      $outgoingvatinc =$_POST["outgoingvatinc"];
      /* set detail for display */
      $detail=DETAIL_EXPEND;
      // echo "<hr> in: ".$incomingamount ." | out: ".$outgoingamount ."<hr>";
      
      /* get vat from eypendituretype */
      $querystring = sprintf("select * from phpa_expendituretypes ".
         "where number = %s", $expendituretype);
      $vat=0.0;
      if (!$db->query($querystring) && $db->next_record() && ($db->record["vat"] != "")) {
          $vat=$db->record["vat"];
          $vat_category=$db->record["vat_category"];
          $exp_category=$db->record["category"];
      }
      /* echo "<hr> Vat:".$vat ."ex: ".$expendituretype ."<hr>"; */
      
      /* if checkbox checked assume that amount is meant as brutto */
      /* if checkbox ist not checked vat is additional to amount */
      $incomingvat=0;
      if(! is_numeric($incomingamount)) $incomingamount =0;
      if(($vat != 0) && ($incomingamount != 0)) {
         if($incomingvatinc) {
           $incomingvat = ($incomingamount / (100+$vat))*$vat;
           $incomingamount-=$incomingvat;
         } else {
           $incomingvat = $incomingamount * $vat/100;
         }
      }
      $outgoingvat=0;
      if(! is_numeric($outgoingamount)) $outgoingamount =0;
      if(($vat != 0) && ($outgoingamount != 0)) {
         if($outgoingvatinc) {
           $outgoingvat = ($outgoingamount / (100+$vat))*$vat;
           $outgoingamount-=$outgoingvat;
         } else {
           $outgoingvat = $outgoingamount * $vat/100;
         }
      }

      $amount_id = 'NULL';
      /* first create the amounts */
      if(($incomingamount <> 0) || ($outgoingamount <>0)) {
         $querystring = sprintf("insert into phpa_amounts ".
            "(createdate, exp_account, exp_category, description, ".
            "incomingamount, outgoingamount) " .
            "values(%s, %s, %s, '%s', %s, %s)", 
             nullcorr($createdate),	DEFAULT_PHPA_ACCOUNT, 
             $exp_category, $description,
            $incomingamount, $outgoingamount);
//  echo "<hr>".$querystring ."<hr>";
          $returnval = $db->query($querystring);
          /* get new amount id if successful */
          if(!$returnval) {
            $querystring = "select max(number) as amountid from phpa_amounts";
            if(!$db->query($querystring) &&  $db->next_record()) {
               $amount_id = $db->record["amountid"];
            }
          } /* returnval */
       } /* ($incomingamount <> 0) || ($outgoingamount <>0) */

      $vat_id = 'NULL';
      /* then create the vat-records */
      if(($incomingvat <> 0) || ($outgoingvat <> 0)) {
         $querystring = sprintf("insert into phpa_amounts ".
            "(createdate, exp_account, exp_category, description, ".
            "incomingamount, outgoingamount) " .
            "values(%s, %s, %s, '%s', %s, %s)", 
             nullcorr($createdate),	DEFAULT_PHPA_ACCOUNT, 
             $vat_category, $description,
            $incomingvat, $outgoingvat);
//  echo "<hr>".$querystring ."<hr>";
          $returnval = $db->query($querystring);
          /* get new amount id if successful */
          if(!$returnval) {
            $querystring = "select max(number) as amountid from phpa_amounts";
            if(!$db->query($querystring) &&  $db->next_record()) {
               $vat_id = $db->record["amountid"];
            }
          } /* returnval */
       } /* ($incomingamount <> 0) || ($outgoingamount <>0) */


      /* last create the expend row */
      $querystring = sprintf("insert into phpa_expenditures ".
         "(pfile, createdate, description, expendituretype, ".
         "amount, vatamount) " .
         "values(%s, %s, '%s', %s, %s, %s)", 
         $pnumber, nullcorr($createdate),
         $description, $expendituretype, 
         $amount_id, $vat_id);
//  echo "<hr>".$querystring ."<hr>";

      if (!$db->query($querystring)) {
              $changecheck="Eintrag erstellt";
      }
}
/* delete an expenditure */
elseif($_GET["expendituredel"]) { /* use elseif to prevent double call */
      /* import POST-VARS */
      $pnumber = $_GET["pnumber"];
      $exnumber = $_GET["exnumber"];

      /* set detail for display */
      $detail = $_GET["detail"];

      /* first delete the amounts */
      $querystring = sprintf("delete from phpa_amounts  " .
         "where number = ".
           "(select amount from phpa_expenditures where number=%s) ".
         "or number = ".
           "(select vatamount from phpa_expenditures where number=%s)",
          $exnumber, $exnumber);
//  echo "<hr>".$querystring."<hr>";
      if (!$db->query($querystring)) { /* delete of amount successful */
        /* then delete the expend */
        $querystring = sprintf("delete from phpa_expenditures  " .
         "where number =%s", $exnumber);
//  echo "<hr>".$querystring."<hr>";
        if (!$db->query($querystring)) {
              $changecheck="Eintrag gel&ouml;scht";
        }
      } /* delete of amount successful */
}




/* **************** end handle expenditures *************************/ 
/* **************** begin handle invoices *****************************/ 
/* add an invoice */
if($_POST["invoiceaddbutton"]) {
      /* import POST-VARS */
      $pnumber = $_POST["pnumber"];
      $createdate = toisodate($_POST["createdate"],$LOCALE);
      /* set detail for display */
      $detail=DETAIL_INVOICE;

      /* get value and partner from pfiles */
      $querystring= sprintf("select value, partner from phpa_pfiles where number=%s", $pnumber);
      $db->query($querystring);
      $value=0;
      $address=0;
      if($db->next_record()) { 
          $value = $db->record["value"];
          if($value=='') $value=0;
          /* partner ist the default addressee */
          $address = $db->record["partner"];
      }


      /* get charge */
      $charge=0;
      if($value>0) {
         $querystring= sprintf("select max(rvgcharge) as maxcharge ".
             "from phpa_rvgcharges where rvgvalue <= %s", $value);
         $db->query($querystring);
         if($db->next_record() && ($db->record["maxcharge"] !='')) {
             $charge = $db->record["maxcharge"];
             if($charge=="") $charge=0;
         }
       }


      /* compute new invoice number */
      $invoiceid=0; /* start with zero */
      /* get invoice basenumber if available */
      $querystring="select invoice_base from phpa_config";
         $db->query($querystring);
         /* set invoiceid to base number only if its set */
         if($db->next_record() && ($db->record["invoice_base"] !='')) 
             $invoiceid = $db->record["invoice_base"];

      /* only if max invoicenumber is greater than the base number */  
      $querystring= sprintf("select max(invoiceid) as maxinvoiceid ".
             "from phpa_invoices");
         $db->query($querystring);
         if($db->next_record() && ($db->record["maxinvoiceid"] !='') && ($db->record["maxinvoiceid"] > $invoiceid)) {
             $invoiceid = $db->record["maxinvoiceid"];
         }
      /* at last increase invoice number by 1 */
      $invoiceid++;


      /* create new invoice */
      $querystring = sprintf("insert into phpa_invoices ".
         "(pfile, invoiceid, address, createdate, pfilevalue, charge) " .
         "values(%s, %s, %s, %s, %s, %s)", 
         $pnumber, $invoiceid, nullcorr($address), 
         nullcorr($createdate), $value, $charge);
      if (!$db->query($querystring)) {
              $changecheck="Rechnung angelegt";
      }
}
/* delete an invoice */
elseif($_GET["invoicedel"]) { /* use elseif to prevent double call */
      /* import POST-VARS */
      $pnumber = $_GET["pnumber"];
      $inumber = $_GET["inumber"];

      /* set detail for display */
      $detail = $_GET["detail"];

      $querystring = sprintf("delete from phpa_invoices  " .
         "where number =%s", $inumber);
      if (!$db->query($querystring)) {
              $changecheck="Rechnung gel&ouml;scht";
      }
}
/* **************** end handle invoices *******************************/ 
/* **************** begin handle documents *****************************/ 
/* add a data file (document) */
if($_POST["newfilebutton"]) {
      /* import POST-VARS */
      $pnumber = $_POST["pnumber"];
      $filetemplate = $_POST["filetemplate"];
      $dsubject = $_POST["dsubject"];
      $content="";
      $createdate = toisodate($_POST["createdate"],$LOCALE);
      /* set detail for display */
      $detail=DETAIL_FILE;

      /* create new file from template */
      if(subst_letter_vars($filetemplate, "", $content, $pnumber, $address)) {
           $changecheck="Dokumentinhalt erzeugt";
      } else {
         $changecheck="Dokument nicht OK.";
      } /* test correctness of file */

      /* create new document */
      $querystring = sprintf("insert into phpa_dfiles ".
         "(pfile, createdate, address, subject, dfilecontent) " .
         "values(%s, %s, %s, '%s', '%s')", 
         $pnumber, nullcorr($createdate), $address, $dsubject, base64_encode($content));

      if (!$db->query($querystring)) 
              $changecheck .="Dokument angelegt";
      //echo "<hr> das hier muss weg!!<hr>\n";

}

/* delete a data file (document) */
elseif($_GET["dfiledel"]==1) { /* use elseif to prevent double call */
      /* import POST-VARS */
      $pnumber = $_GET["pnumber"];
      $dnumber = $_GET["dnumber"];
      $querystring = sprintf("delete from phpa_dfiles ".
         "where number=%s", $dnumber);
      //echo "<hr> del1!". $querystring."<hr>\n";
      if (!$db->query($querystring))
              $changecheck="Dokument geloescht";

}
/* **************** end handle documents *****************************/ 

/****************** end  change data of details *************************/
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
  echo "<HEAD><TITLE>PHPAdvocat - Liste Akten</TITLE>";
  echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-15\">\n";
  echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"include/phpadvocat.css\">";
  echo "</HEAD>";
  echo "<BODY BGCOLOR=\"#FFFFFF\" TEXT=\"#000000\">\n";
  

  echo "<TABLE width=100%><TR><TD width=200 valign=top>";

  /* here comes the menue */

  $phpa_menue->account=$user;
  $phpa_menue->selected = 2;
   array_insert($phpa_menue->contents,
      array('&nbsp;&nbsp;&nbsp;&nbsp;<b>Akte bearbeiten</b>'), 2);

  $phpa_menue->draw_menue();


/* get record from database */
$querystring = 
  sprintf("select * from phpa_pfiles where number=%s", $pnumber);
$db->query($querystring);
$db->next_record();

/* database connection for drop down list */
$dblist = new www_db;
$dblist->connect($user, $passwd);

/* save processregister for files */
$processregister = trim($db->record["processregister"]);

/* display Title */
echo "</TD><TD><CENTER><H1>Bearbeitung Akte ". $processregister."</H1></CENTER>";

echo "<table width=100%><tr>\n";
echo "<td>" . date("d.m.Y", time()) . "</td>";
/* display status at right side */
echo "<TD ALIGN=RIGHT><b>". $changecheck. "</b></A></TD>";

print "</tr></table>\n";

print "<hr><center>";


 /* beginning frame of dialog */
$pfiledialog = new htmldialog;

/* only show file-nr, hidden detail nr */
$pfiledialog->addinput("Aktennummer:", 
   sprintf("<input name=pnumber type=hidden value=\"%s\">%s".
           "<input name=detail type=hidden value=\"%s\">",
           $db->record["number"], $db->record["number"],$detail));

/* display processregister */
$pfiledialog->addinput("Prozessregister:", 
   sprintf("<input name=processregister type=text size=10 value=\"%s\">\n",
        $db->record["processregister"]));

/* input subject */
$pfiledialog->addinput("Betreff:", 
   sprintf("<input name=subject type=text size=50 value=\"%s\">\n",
        $db->record["subject"]));


/* input value */
$pfiledialog->addinput("Gegenstandwert:", 
   sprintf("<input name=value type=text size=12 value=\"%s\" align=right>\n",
        tolocalnum($db->record["value"],$LOCALE)));


/* choose client */
/* get a list of partners from database */
$querystring="select * from phpa_partner";
$dblist->query($querystring);
/* if not set define partner for later use in detail lists */
$partner=$db->record["partner"];

$optionlist ="<select name=partner>\n";
 while($dblist->next_record()) {
    if($dblist->record["number"] == $db->record["partner"])
       $optionlist .= sprintf("<option selected value=\"%s\">%s, %s\n",
                       $dblist->record["number"], 
                       $dblist->record["name"], $dblist->record["prename"]);
    else
       $optionlist .= sprintf("<option value=\"%s\">%s, %s\n",
                        $dblist->record["number"],
                        $dblist->record["name"],
                        $dblist->record["prename"]);
 }
$optionlist .="</select>\n";
$pfiledialog->addinput("Mandant:",$optionlist);


/* choose opposing */
$dblist->seek(0); /* we used this query already, so move to top */
$optionlist ="<select name=opposing>\n";

$optionlist .= sprintf("<option value=NULL>- unbekannt -\n");
  while($dblist->next_record()) {
        if($dblist->record["number"] == $db->record["opposing"])
           $optionlist .= sprintf("<option selected value=\"%s\">%s, %s\n",
                        $dblist->record["number"],
                        $dblist->record["name"],
                        $dblist->record["prename"]);
         else
           $optionlist .= sprintf("<option value=\"%s\">%s, %s\n",
                        $dblist->record["number"],
                        $dblist->record["name"],
                        $dblist->record["prename"]);
 }
$optionlist .="</select>\n";
$pfiledialog->addinput("Gegner:",$optionlist);


/* choose opposing representative */
/* get a list of partners from database */
$querystring="select * from phpa_partner where type='gegn. PB'";
$dblist->query($querystring);

$optionlist ="<select name=opposing_rep>\n";
$optionlist .= sprintf("<option value=NULL>- unbekannt -\n");
 while($dblist->next_record()) {
    if($dblist->record["number"] == $db->record["opposing_rep"])
       $optionlist .= sprintf("<option selected value=\"%s\">%s, %s\n",
                       $dblist->record["number"], 
                       $dblist->record["name"], $dblist->record["prename"]);
    else
       $optionlist .= sprintf("<option value=\"%s\">%s, %s\n",
                        $dblist->record["number"],
                        $dblist->record["name"],
                        $dblist->record["prename"]);
 }
$optionlist .="</select>\n";
$pfiledialog->addinput("Gegn. Vertreter:",$optionlist);

/* choose court */
/* get a list of partners from database */
$querystring="select * from phpa_partner where type='Gericht'";
$dblist->query($querystring);

$optionlist ="<select name=court>\n";
$optionlist .= sprintf("<option value=NULL>- unbekannt -\n");
 while($dblist->next_record()) {
    if($dblist->record["number"] == $db->record["court"])
       $optionlist .= sprintf("<option selected value=\"%s\">%s, %s\n",
                       $dblist->record["number"], 
                       $dblist->record["name"], $dblist->record["organization"]);
    else
       $optionlist .= sprintf("<option value=\"%s\">%s, %s\n",
                        $dblist->record["number"],
                        $dblist->record["name"],
                        $dblist->record["organization"]);
 }
$optionlist .="</select>\n";
$pfiledialog->addinput("Gericht:",$optionlist);


/* input starting date */
$pfiledialog->addinput("Einleitung:",
   sprintf("<input name=createdate type=text size=10 value=\"%s\" ".
        "onDblClick=\"this.value=heute()\" >(TT.MM.JJJJ)\n",
        tolocaldate($db->record["createdate"],$LOCALE)));

/* input ending date */
$pfiledialog->addinput("Weglegung:",
    sprintf("<input name=enddate type=text size=10 value=\"%s\" ".
        "onDblClick=\"this.value=heute()\" >(TT.MM.JJJJ)\n",
        tolocaldate($db->record["enddate"],$LOCALE)));
        
/* display endnumber */
$pfiledialog->addinput("Ablagenummer:", 
   sprintf("<input name=endnumber type=text size=10 value=\"%s\">\n",
        $db->record["endnumber"]));


/* display demands */
/* first we assume to have no demands */
$demand = 0.0;
$querystring=sprintf("select sum(am.incomingamount)-sum(am.outgoingamount) as sum ".
                     "from phpa_expenditures as ex, phpa_amounts as am ". 
                     "where (ex.amount = am.number or ex.vatamount = am.number) ".
                     "and ex.pfile=%s", $pnumber);
$dblist->query($querystring);

/* fill $demand only if database query is not null */
if($dblist->next_record() && ($dblist->record["sum"] != '')) {
   $demand = $dblist->record["sum"];
} /* end query */

/* mark positive amount as green */
if($demand < 0) {
   $color="red";
} else {
   $color="green";
}
/* display it */
$pfiledialog->addinput("Stand Kostenblatt:", 
        tolocalnum(sprintf('%.2f',$demand),$LOCALE).' &euro;', '', $color);


/* button for saving data */
$pfiledialog->addinput("<input name=pfileeditbutton type=submit value=Sichern>","");

$pfiledialog->out(); /* End of display framework */

echo "</td></tr></table>";

/* **************************** Detail ********************************** */


echo "<hr><a NAME=detail></a>";

/* if detail ist not set, assume Events */
if (!$detail) $detail=DETAIL_EVENT;


/* menue for choosing detail types */

printf("<center><table border=0><tr>\n");
if ($detail==DETAIL_EVENT) {
        echo "<td BGCOLOR=\"#d0d0d0\">Termine</td>\n";
        } else{
        printf("<td><A HREF=\"$PHP_SELF?pnumber=%s", $pnumber);
                printf("&detail=%s#detail\">Termine</td>\n", DETAIL_EVENT);
        }
if ($detail==DETAIL_EXPEND) {
        echo "<td BGCOLOR=\"#d0d0d0\">Kostenblatt</td>\n";
        } else{
        printf("<td><A HREF=\"$PHP_SELF?pnumber=%s", $pnumber);
                printf("&detail=%s#detail\">Kostenblatt</td>\n", DETAIL_EXPEND);
        }
if ($detail==DETAIL_INVOICE) {
        echo "<td BGCOLOR=\"#d0d0d0\">Rechnungen</td>\n";
        } else{
        printf("<td><A HREF=\"$PHP_SELF?pnumber=%s", $pnumber);
                printf("&detail=%s#detail\">Rechnungen</td>\n", DETAIL_INVOICE);
        }
if ($detail==DETAIL_FILE) {
        echo "<td BGCOLOR=\"#d0d0d0\">Schriftverkehr</td>\n";
        } else{
        printf("<td><A HREF=\"$PHP_SELF?pnumber=%s", $pnumber);
                printf("&detail=%s#detail\">Schriftverkehr</td>\n", DETAIL_FILE);
        }

echo "</table>\n";


/* display details */

switch ($detail) {
        case DETAIL_EVENT: /* events */
		$querystring = sprintf("select * ".
                  "from phpa_deadlines where pfile=%s ".
                  "order by eventday", $pnumber);
                /* echo "<hr>" . $querystring . "<hr>"; */
                $db->query($querystring);

                printf("<h3>Fristen</h3><table class=listtable>\n");
                /* Display header */
                printf("<tr><th>Datum</th><th class=listtable>Typ</th>".
                       "<th>Beschreibung</th>".
                       "<th></th></tr>");
                       
                /* Display all attached deadlines */
                while($db->next_record()) {
			//find out if it is expired!
			$expired = false;
			$today = false;
			$t1 = strtotime($db->record["eventday"]);
			$t2 = time();
			if($t2 - $t1 > 60*60*24) { //expired!
				$expired = true;
			}
			elseif($t2 - $t1 >= 0) {
				$today = true;
			}
			
			$dtype = $db->record["type"];
			
                        printf("<tr bgcolor=\"" . ($expired? "#CCCCCC" : ($today ? ($dtype == 0 ? "#FFFF00" : "#FF0000") : ($dtype == 0 ? "#FFFF99" : "#FF9999"))) . "\"><td>%s</td>", 
                          tolocaldate(substr($db->record["eventday"],0,10),$LOCALE));
                        printf("<td>%s</td>",
                          ($db->record["type"] == 0 ? "Vorfrist" : "Fristablauf"));
                        printf("<td>%s</td>", 
                          $db->record["description"]);
                        printf("<td><a href=\"$PHP_SELF?pnumber=%s&deadlinenumber=%s" .
                        "&deadlinedel=1&detail=%s#detail\" " .
                        "onClick=\"return confirm('Eintrag loeschen?')\">" .
                        "<img alt=Del src=\"images/trash-x.png\" border=0>".
                        "</a></td></tr>\n",
                        $pnumber, $db->record["number"], $detail);

                } /* end while */
                                /* last row is an input for new deadlines */
                printf("<tr class=input><FORM METHOD=POST ACTION=\"$PHP_SELF\">");
                printf("<td><input name=pnumber type=hidden value=%s>", $pnumber);
                printf("<input name=detail type=hidden value=%s>", $detail);
                
                $nowday = date("d", time());
                printf("<select name=date_day>\n");
                for($oday=1;$oday<=31;$oday++){
                   if ($nowday == $oday){
                     printf("<option selected>%02.0f\n</option>",$oday);
                   } else {
                     printf("<option>%02.0f\n</option>",$oday);
                   }
                }
                printf("</select>\n");
							
                $nowmonth = date("m", time());
                printf("<select name=date_month>\n");
                for($omonth=1;$omonth<=12;$omonth++){
                   if ($nowmonth == $omonth){
                     printf("<option selected>%02.0f\n</option>",$omonth);
                   } else {
                     printf("<option>%02.0f\n</option>",$omonth);
                   }
                }
                printf("</select>\n");

                $nowyear = date("Y", time());
                printf("<select name=date_year>\n");
                for($oyear=($nowyear-10);$oyear<=($nowyear+10);$oyear++){
                   if ($nowyear == $oyear){
                     printf("<option selected>%.0f\n</option>",$oyear);
                   } else {
                     printf("<option>%.0f\n</option>",$oyear);
                   }
                }
                printf("</select>\n");
		
//       printf("<input name=startdate type=text size=10 value='%s'>".
//			"</td>\n", date("d.m.Y", time()));

                printf("</td>\n");
                printf("<td>\n");
                
                printf("<select name=dtype>\n");
                printf("<option selected value=0>Vorfrist</option>\n");
                printf("<option value=1>Fristablauf</option>\n");
                printf("</select>\n");

//   printf("<td><input name=starttime type=text size=10 ".
//	  "value='%s'></td>\n", date("H:00", time()));
                printf("</td>\n");

                printf("<td><input name=description type=text size=30></td>\n");
                printf("<td><input name=deadlineaddbutton type=submit value=Neu></td>");
                printf("</FORM></tr>");

                printf("</table>\n");
		
		
		
                $querystring = sprintf("select * ".
                  "from phpa_events where pfile=%s ".
                  "order by eventstart", $pnumber);
                /* echo "<hr>" . $querystring . "<hr>"; */
                $db->query($querystring);

                printf("<h3>Termine</h3><table class=listtable>\n");
                /* Display header */
                printf("<tr><th>Datum</th><th class=listtable>Zeit</th>".
                       "<th>Beschreibung</th><th class=listtable>Ort</th>".
                       "<th></th></tr>");
                       
                /* Display all attached events */
                while($db->next_record()) {
                        printf("<tr><td>%s</td>", 
                          tolocaldate(substr($db->record["eventstart"],0,10),$LOCALE));
                        printf("<td>%s</td>",
                          substr($db->record["eventstart"],11,5));
                        printf("<td><a href=\"eventexportics.php?enumber=%s\" target=_blank>%s</a></td>", 
                          $db->record["number"],
                          $db->record["description"]);
                        printf("<td>%s</td>", $db->record["location"]);
                        printf("<td><a href=\"$PHP_SELF?pnumber=%s&enumber=%s" .
                        "&eventdel=1&detail=%s#detail\" " .
                        "onClick=\"return confirm('Eintrag loeschen?')\">" .
                        "<img alt=Del src=\"images/trash-x.png\" border=0>".
                        "</a></td></tr>\n",
                        $pnumber, $db->record["number"], $detail);

                } /* end while */
                                /* last row is an input for new events */
                printf("<tr class=input><FORM METHOD=POST ACTION=\"$PHP_SELF\">");
                printf("<td><input name=pnumber type=hidden value=%s>", $pnumber);
                printf("<input name=detail type=hidden value=%s>", $detail);
                
                $nowday = date("d", time());
                printf("<select name=start_day>\n");
                for($oday=1;$oday<=31;$oday++){
                   if ($nowday == $oday){
                     printf("<option selected>%02.0f\n</option>",$oday);
                   } else {
                     printf("<option>%02.0f\n</option>",$oday);
                   }
                }
                printf("</select>\n");
							
                $nowmonth = date("m", time());
                printf("<select name=start_month>\n");
                for($omonth=1;$omonth<=12;$omonth++){
                   if ($nowmonth == $omonth){
                     printf("<option selected>%02.0f\n</option>",$omonth);
                   } else {
                     printf("<option>%02.0f\n</option>",$omonth);
                   }
                }
                printf("</select>\n");

                $nowyear = date("Y", time());
                printf("<select name=start_year>\n");
                for($oyear=($nowyear-10);$oyear<=($nowyear+10);$oyear++){
                   if ($nowyear == $oyear){
                     printf("<option selected>%.0f\n</option>",$oyear);
                   } else {
                     printf("<option>%.0f\n</option>",$oyear);
                   }
                }
                printf("</select>\n");
		
//       printf("<input name=startdate type=text size=10 value='%s'>".
//			"</td>\n", date("d.m.Y", time()));

                printf("</td>\n");
                printf("<td>\n");
                
                $nowhour = date("H", time());
                printf("<select name=start_hour>\n");
                for($ohour=0;$ohour<=23;$ohour++){
                   if ($nowhour == $ohour){
                     printf("<option selected>%02.0f\n</option>",$ohour);
                   } else {
                     printf("<option>%02.0f\n</option>",$ohour);
                   }
                }
                printf("</select>\n");
                

                printf("<select name=start_minute>\n");
                for($ominute=0;$ominute<=30;$ominute+=30){
                   printf("<option>%02.0f\n</option>",$ominute);
                }
                printf("</select>\n");

//   printf("<td><input name=starttime type=text size=10 ".
//	  "value='%s'></td>\n", date("H:00", time()));
                printf("</td>\n");

                printf("<td><input name=description type=text size=30></td>\n");
                printf("<td><input name=location type=text size=15></td>\n");
                printf("<td><input name=eventaddbutton type=submit value=Neu></td>");
                printf("</FORM></tr>");

                printf("</table></center>\n");

                break;

        case DETAIL_EXPEND: /* expenditures */
                $querystring = sprintf("select ".
                  "ex.number as number, ".
                  "ex.createdate as createdate, ".
                  "ex.description as description, ".
                  "et.description as expendituretype, ".
                  "(select sum(am.incomingamount) from phpa_amounts as am where am.number = ex.amount) as incomingamount, ".
                  "(select sum(am.incomingamount) from phpa_amounts as am where am.number = ex.vatamount) as incomingvat, ".
                  "(select sum(am.outgoingamount) from phpa_amounts as am where am.number = ex.amount) as outgoingamount, ".
                  "(select sum(am.outgoingamount) from phpa_amounts as am where am.number = ex.vatamount) as outgoingvat ".
                  "from phpa_expenditures as ex, phpa_expendituretypes as et ".
                  "where ex.expendituretype = et.number ".
                  "and ex.pfile=%s order by ex.createdate, ex.number", $pnumber);
                /* echo "<hr>" . $querystring . "<hr>"; */
                $db->query($querystring);

                printf("<table class=listtable>\n");
                /* Display header */
                printf("<tr><th class=listtable>Datum</th>".
                       "<th class=listtable>Beschreibung</th class=listtable><th>Typ</th>".
                       "<th class=listtable>Eingang</th><th class=listtable>Ust.</th>".
                       "<th class=listtable>Ausgang</th><th class=listtable>Ust.</th>".
                       "<th class=listtable>Gesamt</th><th class=listtable></th></tr>");
                       
                /* Display all attached expenditures */
                while($db->next_record()) {
                        printf("<tr>");
                        printf("<td>%s</td>", tolocaldate($db->record["createdate"],$LOCALE));
                        printf("<td>%s</td>", $db->record["description"]);
                        printf("<td>%s</td>", $db->record["expendituretype"]);
                        printf("<td align=right>%s</td>", tolocalnum($db->record["incomingamount"],$LOCALE));
                        printf("<td align=right>%s</td>", tolocalnum($db->record["incomingvat"],$LOCALE));
                        printf("<td align=right>%s</td>", tolocalnum($db->record["outgoingamount"],$LOCALE));
                        printf("<td align=right>%s</td>", tolocalnum($db->record["outgoingvat"],$LOCALE));
                        /* compute sum of all */
                        $amountsum= ($db->record["incomingamount"]+$db->record["incomingvat"])-
                           ($db->record["outgoingamount"]+$db->record["outgoingvat"]);
                        printf("<td align=right><b>%s</b></td>", tolocalnum(sprintf('%.2f',$amountsum), $LOCALE));
                        /* delete row */
                        printf("<td><a href=\"$PHP_SELF?pnumber=%s&exnumber=%s" .
                        "&expendituredel=1&detail=%s#detail\" " .
                        "onClick=\"return confirm('Eintrag loeschen?')\">" .
                        "<img alt=Del src=\"images/trash-x.png\" border=0>".
                        "</a></td></tr>\n",
                        $pnumber, $db->record["number"], $detail);

                }

                /* last row is an input for a new expend */
                printf("<tr class=input><FORM METHOD=POST ACTION=\"$PHP_SELF\">");
                printf("<td><input name=pnumber type=hidden value=%s>".
                       "<input name=detail type=hidden value=%s>",
                        $pnumber, $detail);
                printf("<input name=createdate type=text size=10 value='%s'></td>\n",
                        date("d.m.Y", time()));
                printf("<td><input name=description type=text size=50></td>\n");

                /* select the type of expenditure and wether or not vax included */
                $querystring="select * from phpa_expendituretypes";
		
                $dblist->query($querystring);
                printf("<td><select name=expendituretype>\n");
                while($dblist->next_record()) {
                    printf("<option value=\"%s\">%s, %s\n",
                         $dblist->record["number"],
                         $dblist->record["description"],
                         $dblist->record["vat"]);
                }
                printf("</select></td>\n");

                printf("<td><input name=incomingamount type=float size=10 value=0,00></td>\n");
                printf("<td><input name=incomingvatinc type=checkbox checked=checked></td>\n");
                printf("<td><input name=outgoingamount type=float size=10 value=0,00></td>\n");
                printf("<td><input name=outgoingvatinc type=checkbox checked=checked></td>\n");
                
                printf("<td><input name=expenditureaddbutton type=submit value=Neu></td>");
                printf("</FORM></tr>");

                printf("</table></center>\n");

                break;


        case DETAIL_INVOICE: /* invoices */
        
	        /*
                $querystring = sprintf("select i.number, i.pfile, ".
		              "i.createdate, i.paydate, ".
			      "sum(ip.amount)+sum(ip.vat) as sum ".
			      "from phpa_invoices as i, phpa_invoicepos as ip ".
			      "where ip.invoice=i.number ".
			      "and i.pfile=%s group by i.number",
                               $pnumber);
	       */
                $querystring = sprintf("select number, pfile, ".
		               "createdate, paydate, ".
		                  "(select sum(amount)+sum(vat) from phpa_invoicepos ".
				  "where invoice=phpa_invoices.number) as sum ".
			       "from phpa_invoices where pfile=%s",
                               $pnumber);
                // echo "<hr>" . $querystring . "<hr>"; 
                $db->query($querystring);

                printf("<table class=listtable>\n");
                /* Display header */
                printf("<tr><th class=listtable>Nummer</th><th class=listtable>Datum</th><th class=listtable>Bezahlt am</th><th class=listtable>Betrag</th>".
                       "<th class=listtable></th></tr>");
                       
                /* Display all invoices */
                while($db->next_record()) {
                        printf("<tr><td><a href=\"invoiceedit.php?pnumber=%s&number=%s\">%05.0f</a></td>",
                                $pnumber, $db->record["number"], $db->record["number"]);
                        printf("<td>%s</td>", tolocaldate($db->record["createdate"],$LOCALE));
                        printf("<td>%s</td>", tolocaldate($db->record["paydate"],$LOCALE));
                        printf("<td align=right>%s</td>", tolocalnum($db->record["sum"],$LOCALE));
                        printf("<td><a href=\"$PHP_SELF?pnumber=%s&inumber=%s" .
                        "&invoicedel=1&detail=%s#detail\" " .
                        "onClick=\"return confirm('Eintrag loeschen?')\">" .
                        "<img alt=Del src=\"images/trash-x.png\" border=0>".
                        "</a></td></tr>\n",
                        $pnumber, $db->record["number"], $detail);

                }

                /* last row is an input for new invoices */
                printf("<tr class=input><FORM METHOD=POST ACTION=\"$PHP_SELF\">");
                printf("<td><input name=pnumber type=hidden value=%s>Neu</td>", $pnumber);
                printf("<td><input name=createdate type=text size=10 value='%s'></td>\n",
                        date("d.m.Y", time()));
                printf("<td align=right colspan=3><input name=invoiceaddbutton type=submit value=Neu></td>");
                printf("</FORM></tr>");

                printf("</table></center>\n");

                break;
                
                
        case DETAIL_FILE: /* scripts and documents */
                  
                $querystring = sprintf("select d.number, d.pfile, d.createdate, d.subject, p.name ".
                    "from phpa_dfiles as d, phpa_partner as p where d.address=p.number and d.pfile=%s",$pnumber);
                $db->query($querystring);

                // echo "<hr>". $querystring."<hr>";
                
                printf("<h3>Interne Dokumente</h3>\n");
                printf("<table class=listtable>\n");
                /* Display header */
                printf("<tr><th class=listtable>Nummer</th><th class=listtable>Betreff</th>".
                       "<th class=listtable>Datum</th><th class=listtable colspan=2 align=left>Adressat</th>".
                       "<th class=listtable>Loeschen</th></tr>\n");
                       

                /* Display all attached documents */
                    while ($db->next_record()) {
                           printf("<tr><td><a href=\"dfileedit.php?pfile=%s&filenum=%s&detail=%s\">%05.0f</a></td>",
                                   $pnumber, $db->record["number"], $detail,$db->record["number"]);
                           printf("<td>%s</td>", $db->record["subject"]);
                           printf("<td>%s</td>", $db->record["createdate"]);
                           printf("<td colspan=2>%s</td>", $db->record["name"]);
                           printf("<td><a href=\"$PHP_SELF?pnumber=%s&dnumber=%s" .
                           "&dfiledel=1&detail=%s#detail\" " .
                           "onClick=\"return confirm('Eintrag loeschen?')\">" .
                           "<img alt=Del src=\"images/trash-x.png\" border=0>".
                           "</a></td></tr>\n",
                           $pnumber, $db->record["number"], $detail);
                        }

                /* last row is an input for new docs */
                printf("<tr class=input><FORM ENCTYPE=multipart/form-data METHOD=POST ACTION=\"$PHP_SELF\">");
                printf("<td><input name=pnumber type=hidden value=%s>-</td>", $pnumber);
                printf("<td><input name=dsubject type=text length=40></td>");
                printf("<td><input name=createdate type=text size=10 value='%s'></td>\n",
                        date("d.m.Y", time()));
                
                  /* get a list of partners from database */
                  $querystring=  "(select number, name, prename, 0 as sorter from phpa_partner where number=" . nullcorr($db->record["partner"])
                               . " UNION select number, name, prename, 1 as sorter from phpa_partner where number=" . nullcorr($db->record["opposing"])
                               . " UNION select number, name, prename, 2 as sorter from phpa_partner where number=" . nullcorr($db->record["opposing_rep"])
                               . " UNION select number, name, prename, 3 as sorter from phpa_partner where number=" . nullcorr($db->record["court"])
                               . " UNION select -1 as number, '---' as name, '' as prename, 4 as sorter" 
			       . " UNION select number, name, prename, 5 as sorter from phpa_partner) ORDER BY sorter, name, prename ASC";
                  $dblist->query($querystring);
                  
                  printf("<td>Adressat: <select name=address>\n");
                   while($dblist->next_record()) {
                      /* use saved partner var form base pfile data */
                      if($dblist->record["number"] == $partner)
                         printf("<option selected value=\"%s\">%s, %s\n",
                             $dblist->record["number"], 
                             $dblist->record["name"], $dblist->record["prename"]);
                      elseif($dblist->record["name"] == "---")
			 printf("<option class=\"select-dash\" disabled=\"disabled\">----");
                      else
                         printf("<option value=\"%s\">%s, %s\n",
                             $dblist->record["number"],
                             $dblist->record["name"],
                             $dblist->record["prename"]);
                   }
                  printf("</select></td>\n");


                /* get a list of telmplates from file system */
                $querystring="select filebase from phpa_config";
                $dblist->query($querystring);
                $filebase = "./files";
                if ($dblist->next_record() && $dblist->record["filebase"] != "") 
                   $filebase = $dblist->record["filebase"];

                printf("<td>Vorlage: <select name=filetemplate>\n");
                if(file_exists($filebase.'/templates')) {
                  $handle=opendir ($filebase.'/templates');
                    while (false !== ($file = readdir ($handle))) {
                       $fullpath = $filebase.'/templates/'.$file;
                       if(is_file($fullpath)) {
                           printf("<option value=\"%s\">%s</option>",
                                   $fullpath, $file);
                       } /* endif is_file $fullpath */
                    } /* end while readdir   */
                } /* endif file_exists templates */

                printf("</select>\n");
                printf("</td>\n");

                printf("<td><input name=newfilebutton type=submit value=Neu></td>");
                printf("</FORM></tr>");
                
                printf("</table>\n");
                printf("<h3>Externe Dokumente</h3>\n");
                printf("<table class=listtable>\n");
                
                printf("<tr><th class=listtable>Nummer</th><th class=listtable>Name</th>".
                       "<th class=listtable>Datum</th><th class=listtable>Speicherort</th>".
                       "<th class=listtable>Anzeigen</th></tr>\n");
                
                //search for all files in $filebase for files containing the register and display them
                
                $processregister_short = substr($processregister, 2);
                
                $Directory = new RecursiveDirectoryIterator($filebase);
                $Iterator = new RecursiveIteratorIterator($Directory);
                //$Regex = new RegexIterator($Iterator, '/^.+\.pdf$/i', RecursiveRegexIterator::GET_MATCH);
                $Regex = new RegexIterator($Iterator, '/^.*' . preg_quote($processregister_short) . '.*$/i', RecursiveRegexIterator::GET_MATCH);
                //print_r($Regex);
                //foreach($Regex as $f) {
                //        printf("<tr><td>0</td><td>" . $f . "</td><td>1.1.1970</td><td>./</td><td>HI</td></tr>\n");
                //}
                $files_arr = array();
                foreach($Regex as $name => $object){
			$basename =  basename($name);
			$dirname = dirname($name);
			$changedate = filemtime($name);
			$new_ent = array();
			$new_ent["basename"] = $basename;
			$new_ent["dirname"] = $dirname;
			$new_ent["changedate"] = $changedate;

                        $files_arr[] = $new_ent;
                }
                
                usort($files_arr, "sortByChangedate");
                
                $idx = 1;
                foreach($files_arr as $ent) {
			$shortname = substr($ent["dirname"], strlen($filebase)+1);
			
			$pdf_avail = false;
			$ext = explode(".",$ent["basename"]);
			if( count($ext) > 1 ) {
				
				$ext = $ext[ count($ext)-1 ];
				if($ext === "txt" or $ext === "ods" or $ext === "odt" or $ext === "xls") {
					$pdf_avail = true;
				}
			}
			
			$pdf_str = "";
			if($pdf_avail) {
				$pdf_str = "<a href=\"convert_to_pdf.php?file=" . htmlentities($shortname . "/" . $ent["basename"]) . "\">click</a>";
				printf("<tr><td>" . $idx++ . "</td><td>" . $ent["basename"] . "</td><td>" . date("d.m.Y \u\m H:i", $ent["changedate"]) . "</td><td>" . $shortname . "</td><td>" . $pdf_str . "</td></tr>\n");
			}
			
			//do not show documents that cannot be converted to pdf!
		}

                printf("</table></center>\n");

                break;

}


$dblist->close();
$db->close();


echo "<hr>";
/* end framework */
echo "</TD></TR></TABLE>";

/* End HTML PAGE */
echo "</BODY></HTML>";
?>
