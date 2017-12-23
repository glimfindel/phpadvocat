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

define("DETAIL_EXPEND", 1);

/* Get User Account from Session Vars */ 
$user = $_SESSION["dbuser"];
$passwd = $_SESSION["dbpasswd"];

$changecheck="";

/* initialize database */
$db = new www_db;
$db->connect($user, $passwd);

/* import invoice number if transmitted by GET or POST */
if($_POST["number"] !=0) {
  $number = $_POST["number"];
} elseif($_GET["number"] !=0) {
  $number = $_GET["number"];
}



/********** handle updates and inserts begin ********************************/
/****************** begin change data of expends *************************/
/* add an amount */
if($_POST["amountaddbutton"]) {
      /* import POST-VARS */
      $createdate = toisodate($_POST["createdate"],$LOCALE);
      $description = $_POST["description"];
      $exp_account = $_POST["number"];
      $category = $_POST["category"];
      $incomingamount = toisonum($_POST["incomingamount"],$LOCALE);
      $outgoingamount = toisonum($_POST["outgoingamount"],$LOCALE);
      /* set detail for display */
      $detail=DETAIL_EXPEND;
      // echo "<hr> in: ".$incomingamount ." | out: ".$outgoingamount ."<hr>";
      
      /* set vatrate from input else set to 0 */
      

      $querystring = sprintf("insert into phpa_amounts ".
         "(createdate, description, exp_account, exp_category, ".
         "incomingamount, outgoingamount) " .
         "values(%s, '%s', %s, %s, %s, %s)", 
         nullcorr($createdate), $description, $number, $category,
         $incomingamount, $outgoingamount);
      // echo "<hr>".$querystring ."<hr>";

      if (!$db->query($querystring)) {
              $changecheck="Eintrag erstellt";
      }
}
/* delete an expenditure */
elseif($_GET["amountdel"]) { /* use elseif to prevent double call */
      /* import POST-VARS */
      $number = $_GET["number"];
      $exnumber = $_GET["exnumber"];

      /* set detail for display */
      $detail = DETAIL_EXPEND;

      $querystring = sprintf("delete from phpa_amounts  " .
         "where number =%s", $exnumber);
      // echo "<hr>".$querystring."<hr>";
      if (!$db->query($querystring)) {
              $changecheck="Eintrag gel&ouml;scht";
      }
}

/* transfer amaount to another account */
elseif($_POST["transferbutton"]) {
   $createdate = toisodate($_POST["createdate"],$LOCALE);
   $description = $_POST["description"];
   $exp_account = $_POST["number"];
   $toaccount = $_POST["toaccount"];
   $transferamount = toisonum($_POST["transferamount"],$LOCALE);
   
   $querystring=sprintf("insert into phpa_amounts ".
     "(createdate, exp_account, description, outgoingamount) ".
     "values(%s, %s, '%s', %s)",
     nullcorr($createdate), $exp_account, $description, $transferamount);
     
     // echo "<hr>1 ".$querystring."<hr>";
     
   if (!$db->query($querystring)) {
   $querystring=sprintf("insert into phpa_amounts ".
     "(createdate, exp_account, description, incomingamount) ".
     "values(%s, %s, '%s', %s)",
     nullcorr($createdate), $toaccount, $description, $transferamount);

     // echo "<hr>2 ".$querystring."<hr>";

     if (!$db->query($querystring)) {
              $changecheck="Umbuchung erstellt";
     }
   }
  
   
}

/****************** end  change data of expends *************************/
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

/* get account name */
$querystring = 
  sprintf("select * from phpa_accounts where number=%s", $number);
  if(!$db->query($querystring) && $db->next_record()) {
    $accountname=$db->record["description"];
  }

  echo "<HEAD><TITLE>PHPAdvocat - Konto ".$accountname."</TITLE>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-15\">\n";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"include/phpadvocat.css\">";
echo "</HEAD>";
  echo "<BODY BGCOLOR=\"#FFFFFF\" TEXT=\"#000000\">\n";

  echo "<TABLE width=100%><TR><TD width=200 valign=top>";

  /* here comes the menue */

  $phpa_menue->account=$user;
  $phpa_menue->selected = 6;
   array_insert($phpa_menue->contents,
      array('&nbsp;&nbsp;<b>'.$accountname.'</b>'), 6);

  $phpa_menue->draw_menue();


/* display Title */
echo "</TD><TD><CENTER><H1>Bearbeitung Konto ".$accountname."</H1></CENTER>";


/* database connection for drop down lists */
$dblist = new www_db;
$dblist->connect($user, $passwd);


echo "<table width=100%><tr>\n";
echo "<td>" . date("d.m.Y", time()) . "</td>";
/* display status at right side */
echo "<TD ALIGN=RIGHT><b>". $changecheck. "</b></A></TD>";

print "</tr>";


/* **************************** Detail ********************************** */


echo "<hr><a NAME=detail></a>";
if (!$detail) $detail=DETAIL_EXPEND;


/* display details */

switch ($detail) {
   case (DETAIL_EXPEND):
       
       $querystring = sprintf("select acc.number, acc.createdate, ".
         "acc.description, acc.exp_category, ".
         "acc.incomingamount, acc.outgoingamount, cat.description as category ".
         "from phpa_amounts as acc, phpa_exp_categories as cat ".
         "where acc.exp_account=%s and acc.exp_category=cat.number ".
         "order by createdate, number", 
         $number);
       // echo "<hr>" . $querystring . "<hr>";
       $db->query($querystring);
   
       printf("<table class=listtable>\n");
       /* Display header */
       printf("<tr><th>Datum/Beleg</th>".
              "<th>Beschreibung</th>".
              "<th>Kategorie</th>".
              "<th>Eingang</th>".
              "<th>Ausgang</th>".
              "<th>Gesamt</th><th></th></tr>");
              
       /* set sum variables */       
       $incomingamountsum = 0.0;
       $incomingvatsum = 0.0;
       $outgingamountsum = 0.0;
       $outgingvatsum = 0.0;
       $allamountsum = 0.0;
   
       /* Display all attached expenditures */
       while($db->next_record()) {
         printf("<tr BGCOLOR=white>");
         printf("<td>%s/%s</td>", tolocaldate($db->record["createdate"],$LOCALE), $db->record["number"]);
         printf("<td>%s</td>", $db->record["description"]);
         printf("<td>[%s] %s</td>", $db->record["exp_category"], $db->record["category"]);
         printf("<td align=right>%s</td>", tolocalnum($db->record["incomingamount"],$LOCALE));
         printf("<td align=right>%s</td>", tolocalnum($db->record["outgoingamount"],$LOCALE));
         /* compute sum of all */
         $amountsum= $db->record["incomingamount"]-$db->record["outgoingamount"];
         printf("<td align=right><b>%s</b></td>", 
                tolocalnum(sprintf('%.2f',$amountsum), $LOCALE));

        $incomingamountsum += $db->record["incomingamount"];
        $outgingamountsum += $db->record["outgoingamount"];
        $allamountsum += $amountsum;


         /* delete row */
         printf("<td><a href=\"$PHP_SELF?number=%s&exnumber=%s" .
         "&amountdel=1&detail=%s#detail\" " .
         "onClick=\"return confirm('Eintrag loeschen?')\">" .
         "<img alt=Del src=\"images/trash-x.png\" border=0>".
         "</a></td></tr>\n",
         $number, $db->record["number"], $detail);

       }
       /* display sum of all */
       printf("<tr>");
       printf("<td colspan=3><b>Gesamtsummen</b></td>");
       printf("<td align=right><b>%s</b></td>", 
              tolocalnum(sprintf('%.2f',$incomingamountsum),$LOCALE));
       printf("<td align=right><b>%s</b></td>", 
              tolocalnum(sprintf('%.2f',$outgingamountsum),$LOCALE));
       printf("<td align=right><b>%s</b></td>", 
              tolocalnum(sprintf('%.2f',$allamountsum),$LOCALE));
       printf("</tr>");
   
       /* last row is an input for new invoices */
       printf("<tr><FORM METHOD=POST ACTION=\"$PHP_SELF\">");
       printf("<td><input name=number type=hidden value=%s>".
              "<input name=detail type=hidden value=%s>",
               $number, $detail);
       printf("<input name=createdate type=text size=10 value='%s'></td>\n",
               date("d.m.Y", time()));
       printf("<td><input name=description type=text size=40></td>\n");

       $querystring="select * from phpa_exp_categories";
       $dblist->query($querystring);

       printf("<td><select name=category>\n");
         while($dblist->next_record()) {
            printf("<option value=%s>%04.0f, %s</option>\n", 
               $dblist->record["number"],
               $dblist->record["number"],
               $dblist->record["description"]);
         }
       printf("</select></td>\n");
      
       printf("<td><input name=incomingamount type=float size=10 value=0,00></td>\n");
       printf("<td><input name=outgoingamount type=float size=10 value=0,00></td>\n");
       
       printf("<td><input name=amountaddbutton type=submit value=Neu></td>");
       printf("</FORM></tr>");
   
       printf("</table><hr>\n");
       
       
       /********** one line for transferring ***********************/
       printf("<center>\n");
       printf("<h2>Umbuchungen</h2>\n");

       printf("<table  class=listtable>\n");
       printf("<th>Datum</th><th>Beschreibung</th><th>Zielkonto</th>".
              "<th>Betrag</th><th></th>");
       printf("<tr><FORM METHOD=POST ACTION=\"$PHP_SELF\">");
       printf("<td><input name=number type=hidden value=%s>".
              "<input name=detail type=hidden value=%s>",
               $number, $detail);
       printf("<input name=createdate type=text size=10 value='%s'></td>\n",
               date("d.m.Y", time()));
       printf("<td><input name=description type=text size=40></td>\n");
       
       $querystring="select * from phpa_accounts";
       $dblist->query($querystring);

       printf("<td><select name=toaccount>\n");
         while($dblist->next_record()) {
            printf("<option value=%s>%s</option>\n", 
               $dblist->record["number"],$dblist->record["description"]);
         }
       printf("</select></td>\n");
   
       printf("<td><input name=transferamount type=float size=10 value=0,00></td>\n");
       
       printf("<td><input name=transferbutton type=submit value=Neu></td>");
       printf("</FORM></tr>");
   
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
