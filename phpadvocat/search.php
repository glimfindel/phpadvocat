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

/* Get User Account from Session Vars */ 
$user = $_SESSION["dbuser"];
$passwd = $_SESSION["dbpasswd"];

/* initiate database */
$db = new www_db;
$db->connect($user, $passwd);

$q = trim(pg_escape_string($_POST["query"]));




/* Begin HTML page */
echo "<HTML><HEAD>";
echo "<TITLE>PHPAdvocat - Suche</TITLE>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-15\">\n";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"include/phpadvocat.css\">";
echo "</HEAD>";
echo "<BODY BGCOLOR=\"#FFFFFF\" TEXT=\"#000000\">\n";

/* create frame; left side for menu */
echo "<TABLE width=100%><TR><TD width=200 valign=\"top\">\n";

/* here comes the navigation menue */
$phpa_menue->account=$user;
$phpa_menue->selected=0;
$phpa_menue->draw_menue();

echo "</TD><TD>\n";

/* display title */
echo "<CENTER><H1>Suche nach \"" . $q . "\"</H1></CENTER>\n";

echo "<table width=100%><tr>\n";
echo "<td>" . date("d.m.Y", time()) . "</td>";
print "</tr></table>\n";

print "<hr>";

$q = "%" . strtolower($q) . "%";

$querystring = sprintf(
	"SELECT pf.number as pnumber, " .
	" pf.enddate as edate, " . 
	" pf.processregister as processregister, " . 
	" pf.createdate as cdate, " . 
	" pf.subject as subject, " . 
	" p.name as name, " .
	" p.number as number " . 
	"FROM phpa_pfiles pf, phpa_partner p " . 
	"WHERE p.number = pf.partner AND ". 
	" (pf.processregister LIKE '%s' " . 
	" OR pf.endnumber LIKE '%s' " . 
	" OR LOWER(pf.subject) LIKE '%s' " . 
	" OR LOWER(p.name) LIKE '%s' " . 
	" OR LOWER(p.prename) LIKE '%s' " . 
	" OR LOWER(p.prename || ' ' || p.name) LIKE '%s') " . 
	"ORDER BY pf.processregister", $q, $q, $q, $q, $q, $q);
// echo "<hr>".$querystring."<hr>";

$db->query($querystring);

printf("<h3>Akten</h3><table class=listtable><tbody>\n");

printf("<th>Register</th>");
printf("<th>Datum</th>");
printf("<th>Mandant</th>");
printf("<th>Bezeichnung</th>");

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
	printf("<td><a href=\"partneredit.php?number=%s\">%s</a></td>", $db->record["number"], $db->record["name"]);
	printf("<td>%s</td>", $db->record["subject"]);
	printf("</tr>\n");
}

printf("</table>");


$querystring = sprintf(
	"SELECT * " . 
	"FROM phpa_partner " . 
	"WHERE (LOWER(name) LIKE '%s' " . 
	" OR LOWER(prename) LIKE '%s' " . 
	" OR LOWER(prename || ' ' || name) LIKE '%s') " . 
	"ORDER BY name", $q, $q, $q);
$db->query($querystring);

printf("<br><h3>Adressen</h3>");
printf("<table class=listtable><tbody class=listtable>\n");

/* table header */
echo "<th>Nummer</th>";
echo "<th>Typ</th>";
echo "<th>Titel</th>";
echo "<th>Name</th>";
echo "<th>Vorname</th>";
echo "<th>Organisation</th>";


while($db->next_record()) {
	printf("<tr>");
	/* printf("<td>%s</td>", $db->row); */
	printf("<td><a href=\"partneredit.php?number=%s\">%05.0f</a></td>",
		 $db->record["number"], $db->record["number"]);
	printf("<td>%s</td>", $db->record["type"]);
	printf("<td>%s</td>", $db->record["title"]);
	printf("<td>%s</td>", $db->record["name"]);
	printf("<td>%s</td>", $db->record["prename"]);
	printf("<td>%s</td>", $db->record["organization"]);

	printf("</tr>\n");
}
printf("</tbody></table>\n"); 

  $querystring = sprintf(
   "select d.number, d.pfile, d.createdate, d.subject, p.name, p.prename, p.organization, pf.processregister ".
   "from phpa_dfiles as d, ".
   "phpa_partner as p, ".
   "phpa_pfiles as pf ".
   "where d.address=p.number ".
   "and d.pfile=pf.number ".
	"and (pf.processregister LIKE '%s' " . 
	" OR pf.endnumber LIKE '%s' " . 
	" OR LOWER(d.subject) LIKE '%s' " . 
	" OR LOWER(p.name) LIKE '%s' " . 
	" OR LOWER(p.prename) LIKE '%s' " . 
	" OR LOWER(p.prename || ' ' || p.name) LIKE '%s') " . 
	"ORDER BY pf.processregister", $q, $q, $q, $q, $q, $q);
  $db->query($querystring);

printf("<br><h3>Schriftst&uuml;cke</h3>");
printf("<table class=listtable><tbody class=listtable>\n");

/* table header */
echo "<th>Nummer</th>";
echo "<th>Akte</th>";
echo "<th>Betreff</th>";
echo "<th>Name</th>";
echo "<th>Vorname</th>";
echo "<th>Organisation</th>";

while($db->next_record()) {
	printf("<tr>");
	/* printf("<td>%s</td>", $db->row); */
	printf("<td><a href=\"dfileedit.php?filenum=%s\">%05.0f</a></td>",
		 $db->record["number"], $db->record["number"]);
	printf("<td>%s</td>", $db->record["processregister"]);
	printf("<td>%s</td>", $db->record["subject"]);
	printf("<td>%s</td>", $db->record["name"]);
	printf("<td>%s</td>", $db->record["prename"]);
	printf("<td>%s</td>", $db->record["organization"]);

	printf("</tr>\n");
}
printf("</tbody></table>\n"); 



$db->close();

echo "<hr></TD></TR></TABLE></BODY></HTML>";

