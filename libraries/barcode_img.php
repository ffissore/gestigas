<?php
	
require_once(dirname(__FILE__) . "/../config.php");
require_once( P4A_ROOT_DIR . "/p4a/include.php");
require_once("Image/Barcode.php");

$num     = '15101967';
$type    = 'int25';
$imgtype = 'png';

$num = isset($_REQUEST) && is_array($_REQUEST) && isset($_REQUEST['num']) ? $_REQUEST['num'] : $num;
$type = isset($_REQUEST) && is_array($_REQUEST) && isset($_REQUEST['type']) ? $_REQUEST['type'] : $type;
$imgtype = isset($_REQUEST) && is_array($_REQUEST) && isset($_REQUEST['imgtype']) ? $_REQUEST['imgtype'] : $imgtype;

$numcols = 3;
$numrows = 2;
$ystart = 0;
$xstart = 0;

    $arr[] = "8000000184566";
    $arr[] = "8000000184542";
    $arr[] = "8000000184559";
    $arr[] = "8000000165213";
    $arr[] = "8000000170415";
	$arr[] = "8000000168436";
	
	
	$Array = array();
	for ($riga = 0; $riga < 6; $riga++) {
//		$codice = $db->queryOne("SELECT codice FROM articoli WHERE barcode='".$arr[$riga]."'"); 
//		$descrizione = $db->queryOne("SELECT descrizione FROM articoli WHERE barcode='".$arr[$riga]."'");;
//		$prezzo = $db->queryOne("SELECT prezzoven FROM articoli WHERE barcode='".$arr[$riga]."'");; 
//		$iva = $db->queryOne("SELECT codiva FROM articoli WHERE barcode='".$arr[$riga]."'");; 
//		$paese = $db->queryOne("SELECT paese FROM articoli WHERE barcode='".$arr[$riga]."'");; 

		$codice = "codice"."  ".$riga;
		$descrizione = "descrizione"."  ".$riga;
		$prezzo = "prezzo"."  ".$riga;
		$iva = "Iva"."  ".$riga;
		$paese = "paese"."  ".$riga;
		
		$Array[$riga]=array("codice" => $codice , "descrizione" => $descrizione, "prezzo" => $prezzo, "Iva" => $iva, "Paese" => $paese);		
	}
	
			
	Image_Barcode::draw($num, $type, $imgtype, $numcols , $numrows , $ystart , $xstart, $arr, $Array);

?>
