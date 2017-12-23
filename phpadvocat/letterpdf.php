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
error_reporting(0);
require("./include/phpadvocat.inc.php");
require("./fpdf/fpdf.php");
require("./fpdf/fpdi.php");

/* Get User Account from Session Vars */ 
$user = $_SESSION["dbuser"];
$passwd = $_SESSION["dbpasswd"];

$changecheck="";

/* initialize database */
$db = new www_db;
$db->connect($user, $passwd);


// Mode == 0: Draw no header at all, mode == 1: draw letterhead,
//   mode == 2: draw default header
$headmode = 0;
if ($_REQUEST["head"] != 0) {
  $headmode = $_REQUEST["head"];
}

/* import invoice number if transmitted by GET or POST */
if($_POST["filenum"] !=0) {
  $number = $_POST["filenum"];
  $mail = $_POST["mail"];
} elseif($_GET["filenum"] !=0) {
  $number = $_GET["filenum"];
  $mail = $_GET["mail"];
}



/* get pfile number */
$querystring=sprintf("select * ".
               "from phpa_dfiles where number=%s", $number);

 //echo "<hr>0".$querystring."<hr>";
if((!$db->query($querystring)) && $db->next_record()) { 
  $pnumber=$db->record["pfile"];
  $address=$db->record["address"];
  $dfilesubject=$db->record["subject"];

  $filecontent = base64_decode($db->record["dfilecontent"], true);
}  

/* get partner number */
$querystring=sprintf("select * from phpa_pfiles where number=%s", $pnumber);
// echo "<hr>1".$querystring."<hr>";
if((!$db->query($querystring)) && $db->next_record()) { 
  $partner=$db->record["partner"];
  $court=$db->record["court"];
  $processregister=$db->record["processregister"];
  $pfilecreatedate=tolocaldate($db->record["createdate"],$LOCALE);
    
  $pfilesubject=$db->record["subject"];
}  

/* get address  */
$querystring=sprintf("select * from phpa_partner where number=%s", $address);
// echo "<hr>2".$querystring."<hr>";
if((!$db->query($querystring)) && $db->next_record()) { 
  $partnertype=$db->record["type"];
  $partnertitle=$db->record["title"];
  $partnername=$db->record["name"];
  $partnerprename=$db->record["prename"];
  $partnerorganization=$db->record["organization"];
  $partnerstreet=$db->record["street"];
  $partnerzip=$db->record["zip"];
  $partnercity=$db->record["city"];
  $partneremail=$db->record["email"];
}

/* get court  */
if($court != 0) {
	$querystring=sprintf("select organization from phpa_partner where number=%s", $court);
	// echo "<hr>3".$querystring."<hr>";
	if((!$db->query($querystring)) && $db->next_record()) { 
		$courtorganization=$db->record["organization"];
	}
}


$querystring=sprintf("select * from phpa_config where number=1");
// echo "<hr>4".$querystring."<hr>";
if((!$db->query($querystring)) && $db->next_record()) { 
  $configtitle=$db->record["title"];
  $configname=$db->record["name"];
  $configprename=$db->record["prename"];
  $configorganization=$db->record["organization"];
  $configstreet=$db->record["street"];
  $configzip=$db->record["zip"];
  $configcity=$db->record["city"];
  $configbank=trim($db->record["bank"]).
    ', BLZ:'.trim($db->record["bank_id"].
    ', Konto:'.trim($db->record["account"]));
  $configvat_id=$db->record["vat_id"];
  $configemail=$db->record["email"];
}


/* set letter phrases */
// $subject = sprintf("Nummer: %d\n", (int)$number + $invoice_base);
$subject = sprintf("Nummer: %d\n", $invoiceid);
$subject .= sprintf("Akte %s \n\n", $processregister);
$subject .= sprintf("In Sachen %s \n", $pfilesubject);
// $subject .= sprintf("Leistungsgegenstand: %s \n", $object_services);
// $subject .= sprintf(" %s \n\n", $courtorganization);


$closing = "Mit freundlichen Grüßen ";
$signature = $configprename.' '. $configname;

/* start PDF definitions */
  
class PDF extends FPDI
  {
    public $draw_header = false;
    
    //Page header
    function Header()
	 {
       if(!$this->draw_header) {
         $this->Ln(30);
         return;
       }     
	    //Logo
       // $this->Image('logo_pb.png',10,8,33);
	    //Arial bold 15
	    $this->SetFont('Arial','',10);
       //Move to the right
       // $this->Cell(0);
       //Title
       
       $this->Cell(95,5,$GLOBALS["configorganization"],0,0);
       $this->Cell(0,5,$GLOBALS["configstreet"],0,1,'R');

       $this->Cell(95,5,$GLOBALS["configemail"],B,0);
       $this->Cell(0,5,$GLOBALS["configzip"]." ". $GLOBALS["configcity"],B,1,'R');

       //Line break
       $this->Ln(20);
    }
    
    //Page footer
  function Footer()
    {
      //Position at 2.5 cm from bottom
      $this->SetY(-25);

      $this->SetFont('Arial','',8);
      $this->Cell(0,4,$GLOBALS["configbank"],T,1);
      $this->Cell(0,4,"Umsatzsteuer-Nr.:".$GLOBALS["configvat_id"],0,0);

       // $this->Cell(0,5,$GLOBALS["configstreet"],T,1,'R');


      //Arial italic 8
      $this->SetFont('Arial','I',8);
      //Page number
      $this->Cell(0,4,'Seite '.$this->PageNo().'/{nb}',0,0,'R');
    }
}


/* begin new PDF page */
$pdf=new PDF();
$pdf->draw_header = ($headmode == 2);

if($headmode == 1) {
  $pdf->setSourceFile("files/letterhead.pdf");
  $tplidx = $pdf->importPage(1, '/MediaBox');
}

// $pdf->SetSubject(sprintf("Rechnung Nr %d, Akte %s", (int)$number + $invoice_base, $processregister));
$pdf->SetAuthor("PHPAdvocat");


$pdf->AliasNbPages();
$pdf->SetTopMargin(15);
$pdf->SetLeftMargin(25);
$pdf->SetRightMargin(20);
$pdf->AddPage();

if($headmode == 1) { // letterhead
  $pdf->useTemplate($tplidx, 0, 0, 210); 
  $pdf->ln(10);
}
elseif($headmode == 2) { //default head
  /* sender line above adressee */
  $pdf->SetFont('Arial','',6);
  $pline=sprintf("%s, %s, %s %s",
    $configorganization, 
    $configstreet, $configzip, $configcity);
  $pdf->Cell(0,10,$pline ,0,1);
}
else { // no head
  $pdf->ln(10);
}

/* Addressee */
$pdf->SetFont('Times','',12);
$pdf->Cell(0,5,$partnertitle ,0,1);
$pdf->Cell(0,5,$partnerprename. ' ' .$partnername ,0,1);
$pdf->Cell(0,5,$partnerorganization ,0,1);
$pdf->Cell(0,5,$partnerstreet ,0,1);

$pdf->SetFont('Times','B',12);
$pdf->Cell(0,10,$partnerzip.' '.$partnercity ,0,1);

/* display invoice date */
$pdf->Cell(0,10,$createdate ,0,1,'R');

/* here begins the real part of the letter */
/* first all iformations needed as header */
$pdf->SetFont('Times','B',14);
// $pdf->Cell(0,15, 'Kostenrechnung' ,0,1,'C');


/* display subject */
$pdf->SetFont('Times','B',11);
$pdf->MultiCell(0,5, $subject ,0,1);
/* leave space */
$pdf->Cell(0,10, '' ,0,1);

$pdf->SetFont('Times','',11);

$pdf->MultiCell(0,5, $filecontent ,0,1);
  $pdf->SetFont('Times','',11);

  $pdf->Cell(0,15,$closing,0,1);  
  $pdf->Cell(0,10,$signature,0,1);  

// $pdf->AddPage();

if(1 == $mail){ /* output as email */

/*
ini_set("SMTP","localhost" );
ini_set('sendmail_from', $configemail); 

//$attachment= $pdf->Output($dfilesubject.".pdf","S");

mail($partneremail, 
   "In Sachen ".$pfilesubject, 
   $filecontent, 
   "From:".$configemail."\nBCC:".$configemail); //mail command :) 

*/

echo "<html><head><title>Email senden</title>\n";
echo "<body><center><h1>Dokument als Email senden</h1>\n";
echo "<a href=\"mailto:".$partneremail.
   "?subject=In Sachen ".$pfilesubject."&body=".
   $filecontent."\">".$pfilesubject."</a>\n";
echo "</body></html>";

}else { /* output as pdf */
  
  header('Pragma: public');

  $pdf->Output($dfilesubject.".pdf","I"); 

}

?>
