<?php
/**
 * Progetto e3g - Equogest/GestiGAS
 *   Software gestionali per l'economia solidale
 *   <http://www.progettoe3g.org>
 *
 * Copyright (C) 2003-2012
 *   Andrea Piazza <http://www.andreapiazza.it>
 *   Marco Munari  <http://www.marcomunari.it>
 *
 * @package Progetto e3g - Equogest/GestiGAS
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * 
 * Questo  programma e' software  libero; e' lecito redistribuirlo  o
 * modificarlo secondo i termini  della Licenza Pubblica Generica GNU
 * come  pubblicata dalla Free  Software  Foundation; o la versione 2
 * della licenza o (a propria scelta) una versione successiva.
 * 
 * Questo programma e' distribuito nella  speranza che sia  utile, ma
 * SENZA  ALCUNA GARANZIA;  senza  neppure la  garanzia implicita  di
 * NEGOZIABILITA' o di APPLICABILITA' PER  UN PARTICOLARE  SCOPO.  Si
 * veda la Licenza Pubblica Generica GNU per avere maggiori dettagli.
 * 
 * Questo  programma deve  essere  distribuito assieme  ad una  copia
 * della Licenza Pubblica Generica GNU.
*/

require_once( dirname(__FILE__) . '/../config.php' );
require_once( dirname(__FILE__) . '/../libraries/phpmailer/class.phpmailer.php' );
require_once( dirname(__FILE__) . '/../libraries/e3g_utils.php' );

//------------------------------------------------------------------------------
// Funzioni per l'esportazione ArrayToCsv per la griglia Utenti/Fornitori 
//------------------------------------------------------------------------------

function export_array_to_csv($inarray, $colonne ="")  {
    $sendback = "";                              
      
      if ($colonne != "")
      { 
        // se ho indicato le colonne creo la prima riga con le intestazioni
        while (list ($key1, $val1) = each ($colonne))  {
           $sendback .= $val1 .";" ; //string value
        }
        $sendback = substr($sendback, 0, -1); //chop last ,
        $sendback .= "\n"; 
      }
      
      while (list ($key1, $val1) = each ($inarray))  {
          if ($colonne != "")
          {
            foreach ($colonne as $kcol => $vcol) {
                if (is_numeric($val1[$kcol]))  {
                  $sendback .= str_replace(".", ",", $val1[$kcol]).";"; //numeric value
                }else{
                  $sendback .= $val1[$kcol].";" ; //string value
                }
            }
          }
          else
          {
            // NON ho l'intestazione delle colonne quindi nessun stampo tutte le colonne
            while (list ($key, $val) = each ($val1)) {        
              if (is_numeric($val))  {
                $sendback .= str_replace(".", ",", $val).";"; //numeric value
              }else{
                //$sendback .= "'".$val ."';" ; //string value
                $sendback .= $val .";" ; //string value
              }//fi
            }
          }
        $sendback = substr($sendback, 0, -1); //chop last ,
        $sendback .= "\n";
      }//End of while
      return ($sendback);
}  // end function


function send_file_to_client($filename, $data) {
    $charset = "utf8";
    $mime = "text/html";
    
    header("Cache-control: private");
    header("Content-Type: text/comma-separated-values; charset=" . $charset);
    header("Content-Disposition: attachment; filename=" . $filename);
    header("Content-Length: " . strlen($data));
    
    //header("Content-type: application/ofx");
    //header("Content-Disposition: attachment; filename=$filename");
    echo $data;
    die;
}


function send_file_to_client_2($filename, $data) {
    // required for IE, otherwise Content-disposition is ignored
    if ( ini_get("zlib.output_compression") )
        ini_set( "zlib.output_compression", "Off" );
        
    $file_extension = strtolower(substr(strrchr($filename,"."),1));
    
    //Start condition switch( $file_extension )
    switch( $file_extension ) {
        case "csv": $ctype="application/force-download";break;
        //case "csv": $ctype="application/octet-stream";break;
        //case "pdf": $ctype="application/pdf"; break;
        //case "exe": $ctype="application/octet-stream"; break;
        //case "zip": $ctype="application/zip"; break;
        //case "doc": $ctype="application/msword"; break;
        //case "xls": $ctype="application/vnd.ms-excel"; break;
        //case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
        //case "gif": $ctype="image/gif"; break;
        //case "png": $ctype="image/png"; break;
        //case "jpeg":
        //case "jpg": $ctype="image/jpg"; break;
        //default: $ctype="application/force-download";
        default: echo "<html><title>This file cannot be downloaded</title> <body>ERROR: This file cannot be downloaded</body></html>";exit;
    }  //End condition switch( $file_extension )
    
    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false); // required for certain browsers
    header("Content-Type: ".$ctype."; charset=utf-8");
    header("Content-Disposition: attachment; filename='".basename($filename)."';" );
    header("Content-Transfer-Encoding: binary");
    
    echo $data;
    exit;
}
//------------------------------------------------------------------------------
// Fine delle funzioni per l'esportazione ArrayToCsv per la griglia Utenti/Fornitori 
//------------------------------------------------------------------------------


//------------------------------------------------------------------------------
// Crea PDF con la griglia Utenti/Fornitori per il controllo delle consegne
//------------------------------------------------------------------------------
function tabella_pagamenti_pdf()
{
	$p4a =& p4a::singleton();
	$db =& p4a_db::singleton();
		
	
	$utenti = $db->queryAll(
        "SELECT doc.codutente, ana.descrizione AS desc_utente, " .
        "       FORMAT( SUM( doc.prezzo * ( doc.quantita ) ) , 2 ) AS importo, art.centrale " .
        "  FROM " . $p4a->e3g_prefix . "docr doc, " . $p4a->e3g_prefix . "articoli art, " . $p4a->e3g_prefix . "anagrafiche ana " .
        " WHERE doc.codtipodoc = '" . $p4a->e3g_azienda_gg_cod_doc_ordine . "' " .
        "   AND (doc.estratto <> 'S' OR ISNULL(doc.estratto)) " .
        "   AND doc.codice = art.codice " .
        "   AND doc.codutente = ana.codice " .
      "GROUP BY doc.codutente, art.centrale " .
      "ORDER BY desc_utente, centrale" );
	$num_utenti = count($utenti);
	
	$fornitori = $db->queryAll(
        "SELECT DISTINCT ana.descrizione AS desc_centrale, art.centrale " .
        "  FROM " . $p4a->e3g_prefix . "docr doc, " . $p4a->e3g_prefix . "articoli art, " . $p4a->e3g_prefix . "anagrafiche ana " .
        " WHERE doc.codtipodoc = '" . $p4a->e3g_azienda_gg_cod_doc_ordine . "' " .
        "   AND doc.codice = art.codice " .
        "   AND art.centrale = ana.codice" );
	$num_forn = count($fornitori);
	
	$campi = array();
	$campi["Famiglia"] = "Famiglia";
		
	$riga = 0 ;
	while($riga < $num_forn) {
		$campi[$fornitori[$riga]["centrale"]] = $fornitori[$riga]["centrale"];
		$riga++;
	}	
	$campi["Totale"] = "Totale";
	

	$arr = array();
	$riga = 0 ; 
	$oldutente = "";
	$myrow = -1;
	while($riga < $num_utenti) {
		$centrale = $utenti[$riga]["centrale"];
		//$desccentrale = $db->queryOne("SELECT descrizione FROM ".$p4a->e3g_prefix."anagrafiche WHERE codice = '".$centrale."' ");
		
		$des_ute = $utenti[$riga]["desc_utente"];
		
		if ($oldutente == $utenti[$riga]["desc_utente"]) {	
			// sono sullo stesso utente aggiungo importo centrale 
			$centrale = $utenti[$riga]["centrale"];
			$arr[$myrow][$centrale] = $utenti[$riga]["importo"];
			$arr[$myrow]["Totale"] = $arr[$myrow]["Totale"] + $utenti[$riga]["importo"];		
		}
		else {	
			// diverso utente metto la descr utente e l'importo del primo forn. 
			$myrow++;
			$arr[$myrow]["Famiglia"] = $utenti[$riga]["desc_utente"];
			$i = 0 ;
			while($i < $num_forn) {
				$centrale = $fornitori[$i]["centrale"];
				$arr[$myrow][$centrale] = 0;
				$i++;
			}
			$arr[$myrow]["Totale"] = 0;
			
			$centrale = $utenti[$riga]["centrale"];
			$arr[$myrow][$centrale] = $utenti[$riga]["importo"];
			$arr[$myrow]["Totale"] = $utenti[$riga]["importo"];				
		}
		
		$oldutente = $utenti[$riga]["desc_utente"];
		$riga++;
	}	
	
	// calcolo il totale per fornitore
	$tot = array();
	$riga = 0 ;
	$i = 0 ;
	while($i < $num_forn) {
		$centrale = $fornitori[$i]["centrale"];
		$tot[$centrale] = 0 ;
		$i++;
	}
		
	// scorro gli utenti
	while($riga < count($arr)) {
		// scorro i fornitori per ogni utente
		$i = 0 ;
		while($i < $num_forn) {
			$centrale = $fornitori[$i]["centrale"];
			$tot[$centrale] = $tot[$centrale] + $arr[$riga][$centrale] ;
			$i++;
		}
		$riga++;
	}	
	$myrow++;		
	$arr[$myrow]["Famiglia"] = "Totale";
	$i = 0 ;
	$totale_generale = 0;
	while ($i < $num_forn) {
		$centrale = $fornitori[$i]["centrale"];
		$arr[$myrow][$centrale] = $tot[$centrale] ;
		$totale_generale = $totale_generale + $tot[$centrale];
		$i++;
	}
	$arr[$myrow]["Totale"] = $totale_generale;
	
	require("class.report.php");
	$pdf = new Creport('a4','landscape');
			
	$pdf->stampareport( $arr, $campi, "Consegna a utenti","Consegna a utenti" );
}


//------------------------------------------------------------------------------
// Crea CSV con la griglia Utenti/Fornitori per il controllo delle consegne
//------------------------------------------------------------------------------
function tabella_pagamenti_csv()
{
	$p4a =& p4a::singleton();
	$db =& p4a_db::singleton();
		
	
	$utenti = $db->queryAll(
        "SELECT doc.codutente, ana.descrizione AS desc_utente, " .
        "       FORMAT( SUM( (doc.prezzo + doc.delta_prezzo) * ( doc.quantita ) ) , 2 ) AS importo, art.centrale " .
        "  FROM " . $p4a->e3g_prefix . "docr doc, " . $p4a->e3g_prefix . "articoli art, " . $p4a->e3g_prefix . "anagrafiche ana " .
        " WHERE doc.codtipodoc = '" . $p4a->e3g_azienda_gg_cod_doc_ordine . "' " .
        "   AND (doc.estratto <> 'S' OR ISNULL(doc.estratto)) " .
        "   AND doc.codice = art.codice " .
        "   AND doc.codutente = ana.codice " .
      "GROUP BY doc.codutente, art.centrale " .
      "ORDER BY desc_utente, centrale" );
	$num_utenti = count($utenti);
	
	$fornitori = $db->queryAll(
        "SELECT DISTINCT ana.descrizione AS desc_centrale, art.centrale " .
        "  FROM " . $p4a->e3g_prefix . "docr doc, " . $p4a->e3g_prefix . "articoli art, " . $p4a->e3g_prefix . "anagrafiche ana " .
        " WHERE doc.codtipodoc = '" . $p4a->e3g_azienda_gg_cod_doc_ordine . "' " .
        "   AND doc.codice = art.codice " .
        "   AND art.centrale = ana.codice" );
	$num_forn = count($fornitori);
	
	$campi = array();
	$campi["Famiglia"] = "Famiglia";
		
	$riga = 0 ;
	
  $arr = array();
	$i = 0 ;
		
	while($riga < $num_forn) {
		$campi[$fornitori[$riga]["centrale"]] = $fornitori[$riga]["centrale"];
		$arr[-1]["Famiglia"] = "";
		while($i < $num_forn) {
			$centrale = $fornitori[$i]["centrale"];
			$arr[-1][$centrale] = $fornitori[$i]["desc_centrale"];;
			$i++;
		}
		$arr[-1]["Totale"] = "Totale";
  	
        $riga++;    
	}	
	$campi["Totale"] = "Totale";
	

	$riga = 0 ; 
	$oldutente = "";
	$myrow = -1;
	
	while($riga < $num_utenti) {
		$centrale = $utenti[$riga]["centrale"];
		//$desccentrale = $db->queryOne("SELECT descrizione FROM ".$p4a->e3g_prefix."anagrafiche WHERE codice = '".$centrale."' ");
		
		$des_ute = $utenti[$riga]["desc_utente"];
		
		if ($oldutente == $utenti[$riga]["desc_utente"]) {	
			// sono sullo stesso utente aggiungo importo centrale 
			$centrale = $utenti[$riga]["centrale"];
			$arr[$myrow][$centrale] = $utenti[$riga]["importo"];
			$arr[$myrow]["Totale"] = $arr[$myrow]["Totale"] + $utenti[$riga]["importo"];		
		}
		else {	
			// diverso utente metto la descr utente e l'importo del primo forn. 
			$myrow++;
			$arr[$myrow]["Famiglia"] = $utenti[$riga]["desc_utente"];
			$i = 0 ;
			while($i < $num_forn) {
				$centrale = $fornitori[$i]["centrale"];
				$arr[$myrow][$centrale] = 0;
				$i++;
			}
			$arr[$myrow]["Totale"] = 0;
			
			$centrale = $utenti[$riga]["centrale"];
			$arr[$myrow][$centrale] = $utenti[$riga]["importo"];
			$arr[$myrow]["Totale"] = $utenti[$riga]["importo"];				
		}
		
		$oldutente = $utenti[$riga]["desc_utente"];
		$riga++;
	}	
	
	
	// calcolo il totale per fornitore
	$tot = array();
	$riga = 0 ;
	$i = 0 ;
	while($i < $num_forn) {
		$centrale = $fornitori[$i]["centrale"];
		$tot[$centrale] = 0 ;
		$i++;
	}
		
	// scorro gli utenti
	while($riga < count($arr)) {
	  //echo $riga."<br>";
    	if ($riga >= 0 && $riga< count($arr) - 1)  {
      // scorro i fornitori per ogni utente
  		$i = 0 ;
  		while($i < $num_forn) {
  			$centrale = $fornitori[$i]["centrale"];
  			//echo count($arr)." - ".$riga." - ".$centrale." - ".$arr[$riga][$centrale]."<br>";
  			$tot[$centrale] = $tot[$centrale] + $arr[$riga][$centrale] ;
  			$i++;
  		}
    }
    $riga++;
	}	
	$myrow++;		
	$arr[$myrow]["Famiglia"] = "Totale";
	$i = 0 ;
	$totale_generale = 0;
	while ($i < $num_forn) {
		$centrale = $fornitori[$i]["centrale"];
		$arr[$myrow][$centrale] = $tot[$centrale] ;
		$totale_generale = $totale_generale + $tot[$centrale];
		$i++;
	}
	$arr[$myrow]["Totale"] = $totale_generale;

    $nome_file = "Totale_Utenti_Fornitori";
    
    send_file_to_client($nome_file . ".csv", export_array_to_csv($arr));
        
}


// -----------------------------------------------------------------------------
// Funzione per la generazione del file ODT per i documenti
// -----------------------------------------------------------------------------
function genera_stampa_odt( $numerodoc, $iddoc, $codtipodoc, $codclifor, $codtipopag )
{
    $p4a =& p4a::singleton();
    $db =& p4a_db::singleton();
    
    if ( $numerodoc != "" ) {
        $p4a->build("p4a_db_source", "ds_doct");
        $p4a->ds_doct->setTable($p4a->e3g_prefix."doct");
        $p4a->ds_doct->setPk("iddoc");
        $p4a->ds_doct->setWhere("iddoc=".$iddoc);
        $p4a->ds_doct->addOrder("data");
        $p4a->ds_doct->addOrder("iddoc");
        $p4a->ds_doct->addOrder("numdocum");
        $p4a->ds_doct->load();
        $p4a->ds_doct->firstRow();
        
        require("class.text.php");
        $doc = new FText();
        
        // intestazione documento
        $query = "SELECT descrizione, indirizzo, localita, cap, piva, cf FROM ".$p4a->e3g_prefix."anagrafiche WHERE codice='".$codclifor."'";
        $scheda_clifor = $db->queryRow($query);
        $clifor = $scheda_clifor["descrizione"];
        $indirizzo = $scheda_clifor["indirizzo"];
        $localita = $scheda_clifor["localita"];
        $cap = $scheda_clifor["cap"];
        $piva = $scheda_clifor["piva"];
        $cf = $scheda_clifor["cf"];
        
        $desdocumento = $db->queryOne("SELECT descrizione FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$codtipodoc."'");
        
        $testata = array();
        $testata[1][1] = strtoupper($desdocumento);
        $testata[1][2] = "DATA";
        $testata[1][3] = "P.IVA Cliente";
        $testata[1][4] = "<b>".strtoupper($clifor)."</b>";
        $testata[2][1] = $p4a->ds_doct->fields->numdocum->getnewValue();
        $testata[2][2] = e3g_format_mysql_data( $p4a->ds_doct->fields->data->getnewValue());
        $testata[2][3] = strtoupper($piva);
        $testata[2][4] = strtoupper($indirizzo);
        $testata[3][1] = "";
        $testata[3][2] = "";
        $testata[3][3] = "";
        $testata[3][4] = strtoupper($cap)." ".strtoupper($localita);
        
    
        $despagamento = $db->queryOne("SELECT descrizione FROM ".$p4a->e3g_prefix."pagamenti WHERE codice='".$p4a->ds_doct->fields->codtipopag->getNewValue()."'");
        $dati_banca = $db->queryRow("SELECT abi, cab, cin, conto_corrente, banca, agenzia FROM _aziende WHERE prefix='".$p4a->e3g_prefix."'");
                            
        $testata2 = array();
        $testata2[1][1] = "Pagamento";
        $testata2[1][2] = $despagamento;
        $testata2[2][1] = "Banca";
        $testata2[2][2] = "ABI ".$dati_banca['abi']." - CAB ".$dati_banca['cab']." - CIN ".$dati_banca['cin'];
        $testata2[3][1] = "";
        $testata2[3][2] = $dati_banca['conto_corrente'];
        $testata2[4][1] = "";
        $testata2[4][2] = $dati_banca['banca']." ".$dati_banca['agenzia'];
        
        
        $p4a->build("p4a_db_source", "ds_campi");
        $p4a->ds_campi->setTable($p4a->e3g_prefix."doccampireport");
        $p4a->ds_campi->setPk("idtable");
        $p4a->ds_campi->setWhere("codtipodoc='".$codtipodoc."'");
        $p4a->ds_campi->addOrder("ordine");
        $p4a->ds_campi->load();
        $p4a->ds_campi->firstRow();
        
        // aggiungo intestazione campi nella riga 1 dell'array
        $selectcampi = "";
        $numcampi = 1;
        $riga = 1 ;
        $colonna = 1; 
        $corpo = array();
        $stampaprezzi = "N"; 
        
        while ($numcampi <= $p4a->ds_campi->getNumRows()) {
            $corpo[$riga][$colonna] = "<b>".$p4a->ds_campi->fields->nomecampo->getNewValue()."</b>";
            if ($selectcampi == "")
                $selectcampi = $p4a->ds_campi->fields->campo->getNewValue();
            else 
                $selectcampi .= ", ".$p4a->ds_campi->fields->campo->getNewValue();
                
            // setto il flag STAMPA PREZZI per passarlo alla routine di stampa
            if (strtoupper($p4a->ds_campi->fields->campo->getNewValue()) == "PREZZO") {
                $stampaprezzi = "S"; 
            }
            
            $p4a->ds_campi->nextRow();
            $numcampi++;
            $colonna++;
        }   
        
        // aggiungo le righe del corpo documento 
        $p4a->build("p4a_db_source", "ds_docr");
        $p4a->ds_docr->setTable($p4a->e3g_prefix."docr d");
        $p4a->ds_docr->setSelect("iddocr, nriga, visibile, ".$selectcampi);
        $p4a->ds_docr->setPk("iddocr");
        $p4a->ds_docr->setWhere("iddocr=".$p4a->ds_doct->fields->iddoc->getNewValue()." AND visibile='S'");
        $p4a->ds_docr->addOrder("iddocr");
        $p4a->ds_docr->addOrder("nriga");
        $p4a->ds_docr->load();
        $p4a->ds_docr->firstRow();

        
        $numcampi = 1;
        $riga = 2; 
        $colonna = 1;           
        foreach ($p4a->ds_docr->getAll() as $arr_riga) {
            while (list($chiave,$valore) = each($arr_riga))
            {
                $pos = strpos(strtolower($selectcampi), strtolower($chiave));
                if ($pos === false) {
                    // stringa non trovata 
                } else {
                    // campo trovato 
                    $corpo[$riga][$colonna] = $valore;
                    $colonna++; 
                }
            }
            $riga++;
            $colonna = 1; 
        }
    
        // aggiungo intestazione campi totale 1 
        $selectcampi = "imponibile, imposta, totale, iva";
        $totale1 = array();
        $totale1[1][1] = "<b>Imponibile</b>";
        $totale1[1][2] = "<b>Imposta</b>";
        $totale1[1][3] = "<b>Totale</b>";
        $totale1[1][4] = "<b>Iva</b>";
        
        // ricavo il totale raggruppato per codici iva
        if ( E3G_TIPO_GESTIONE == 'G' ) {
            $query = 
                "SELECT ROUND(SUM(imponibile),".$p4a->e3g_azienda_n_decimali_prezzi.") AS imponibile, ROUND(SUM(imposta),".$p4a->e3g_azienda_n_decimali_prezzi.") AS imposta, ROUND(SUM(totale),".$p4a->e3g_azienda_n_decimali_prezzi.") AS totale , codiva AS iva " .
                "  FROM ".$p4a->e3g_prefix."docr " .
                " WHERE visibile='S' AND iddocr=".$p4a->ds_doct->fields->iddoc->getNewValue()." AND codiva <> '' " .
                "GROUP BY codiva ORDER BY codiva";  
        }
        else {
            $query = 
                "SELECT ROUND(SUM(imponibile),".$p4a->e3g_azienda_n_decimali_prezzi.") AS imponibile, ROUND(SUM(imposta),".$p4a->e3g_azienda_n_decimali_prezzi.") AS imposta, ROUND(SUM(totale),".$p4a->e3g_azienda_n_decimali_prezzi.") AS totale , codiva AS iva " .
                "  FROM ".$p4a->e3g_prefix."docr " .
                " WHERE iddocr=".$p4a->ds_doct->fields->iddoc->getNewValue()." AND codiva <> '' " .
                "GROUP BY codiva ORDER BY codiva";  
        }
        
        
        $p4a->build("p4a_db_source", "ds_tot1");
        $p4a->ds_tot1->setTable($p4a->e3g_prefix."docr");
        $p4a->ds_tot1->setQuery($query);
        $p4a->ds_tot1->load();
        $p4a->ds_tot1->firstRow();

        // aggiungo le righe dell'array totale 1 
        $numcampi = 1;
        $riga = 2; 
        $colonna = 1;           
        foreach ($p4a->ds_tot1->getAll() as $arr_riga) {
            while (list($chiave,$valore) = each($arr_riga)) {
                $pos = strpos(strtolower($selectcampi), strtolower($chiave));
                if ($pos === false) {
                    // stringa non trovata 
                } else {
                    // campo trovato 
                    $totale1[$riga][$colonna] = $valore;
                    $colonna++; 
                }
            }
            $riga++;
            $colonna = 1; 
        }

        // aggiungo intestazione campi totale 2 GENERALE
        $selectcampi = "imponibile, imposta, spese, totale";
        $totale2 = array();
        $totale2[1][1] = "<b>Imponibile</b>";
        $totale2[1][2] = "<b>Imposta</b>";
        $totale2[1][3] = "<b>Spese</b>";
        $totale2[1][4] = "<b>Totale</b>";
    

        // ricavo il totale generale
        if ( E3G_TIPO_GESTIONE == 'G' ) {
            $query = 
                "SELECT ROUND(imponibile,".$p4a->e3g_azienda_n_decimali_prezzi.") as Imponibile, ROUND(imposta,".$p4a->e3g_azienda_n_decimali_prezzi.") as Imposta, ROUND(spesevarie,".$p4a->e3g_azienda_n_decimali_prezzi.") as Spese, ROUND(imponibile+imposta+spesevarie,".$p4a->e3g_azienda_n_decimali_prezzi.") as totale " .
                "  FROM ".$p4a->e3g_prefix."doct " .
                " WHERE iddoc = ".$p4a->ds_doct->fields->iddoc->getNewValue(); 
            $stampaiva = "N";
        }
        else {
            $query = 
                "SELECT ROUND(imponibile,".$p4a->e3g_azienda_n_decimali_prezzi.") as Imponibile, ROUND(imposta,".$p4a->e3g_azienda_n_decimali_prezzi.") as Imposta, ROUND(spesevarie,".$p4a->e3g_azienda_n_decimali_prezzi.") as Spese, ROUND(imponibile+imposta+spesevarie,".$p4a->e3g_azienda_n_decimali_prezzi.") as totale " .
                "  FROM ".$p4a->e3g_prefix."doct " .
                " WHERE iddoc = ".$p4a->ds_doct->fields->iddoc->getNewValue(); 
            $stampaiva = "S";
        }
                    
        $p4a->build("p4a_db_source", "ds_tot2");
        $p4a->ds_tot2->setTable($p4a->e3g_prefix."docr");
        $p4a->ds_tot2->setQuery($query);
        $p4a->ds_tot2->load();
        $p4a->ds_tot2->firstRow();

        // aggiungo le righe dell'array totale 2 GENERALE
        $numcampi = 1;
        $riga = 2; 
        $colonna = 1;           
        foreach ($p4a->ds_tot2->getAll() as $arr_riga) {
            while (list($chiave,$valore) = each($arr_riga))
            {
                $pos = strpos(strtolower($selectcampi), strtolower($chiave));
                if ($pos === false) {
                    // stringa non trovata 
                } else {
                    // campo trovato 
                    $totale2[$riga][$colonna] = $valore;
                    $colonna++; 
                }
            }
            $riga++;
            $colonna = 1; 
        }

        // coda per Porto, vettore, data trasp firma...
        $coda = array();
        $coda[1][1] = "Porto: ";
        $coda[1][2] = "Aspetto Beni: ";
        $coda[2][1] = "Vettore: ";
        $coda[2][2] = "Data e ora Trasp. : ";
        $coda[3][1] = "Firma Conducente";
        $coda[3][2] = "Firma Destinatario";
        
        
        $nomefile = "e3g_document.htm";
    
        $doc->doc2text($p4a->e3g_azienda_path_documento.$nomefile, $testata, $testata2, $corpo, $totale1,$totale2,$coda);
                        
        // scarico il file odt
        // tipo MIME:
        $file_mime     = "application/odt";

        //Collocazione effettiva del file sul server:
        $file_path     = $p4a->e3g_azienda_path_documento."e3g_document.odt";
        $file_name = "e3g_document.odt";
        

        header("Content-Type: ".$file_mime);
        header("Content-Disposition: attachment; filename=".$file_name);
        header("Content-Length: " . filesize($file_path));
        readfile($file_path);
    }                   
}


// -----------------------------------------------------------------------------
// Funzione per la generazione del file pdf per i documenti
//   $righe_det_per_utente -> 0: righe raggruppate per articolo
//                            1: righe dettagliate per utente
// -----------------------------------------------------------------------------
function genera_stampa_pdf( $numerodoc, $iddoc, $codtipodoc, $codclifor, $codtipopag, $righe_det_per_utente=0 )
{
    $p4a =& p4a::singleton();
    $db =& p4a_db::singleton();
     

    if ( $numerodoc != "" ) {
        $p4a->build( "p4a_db_source", "ds_doct" );
        $p4a->ds_doct->setTable( $p4a->e3g_prefix . "doct" );
        $p4a->ds_doct->setWhere( "iddoc = " . $iddoc );
        $p4a->ds_doct->addOrder( "data" );
        $p4a->ds_doct->addOrder( "iddoc" );
        $p4a->ds_doct->addOrder( "numdocum" );
        $p4a->ds_doct->setPk( "iddoc" );
        $p4a->ds_doct->load();
        $p4a->ds_doct->firstRow();

        //-------------------------------------------- DB source righe dettaglio
        $p4a->build( "p4a_db_source", "ds_docr" );
        $p4a->ds_docr->setTable( $p4a->e3g_prefix . "docr AS d" );

        if ( E3G_TIPO_GESTIONE == 'G' )  // GestiGAS, con [art.um_qta] 
        {
<<<<<<< e3g_doc_routines.php
        	if ( E3G_TIPO_GESTIONE == 'G' ) 
            {
            	// Gestigas con [art.um_qta]
          		$p4a->ds_docr->setSelect("d.codice, ROUND(d.prezzo + d.delta_prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.") as prezzo, d.quantita, ROUND(d.totale,".$p4a->e3g_azienda_n_decimali_prezzi.") as totale, d.codiva, CONCAT_WS(' ', d.descrizione, '[', art.um_qta, art.um, ']') AS descrizione, d.visibile, d.nriga, d.iddocr, ana.descrizione as fornitore, d.quantita2, d.sconto, IF(d.visibile='N',ana2.descrizione,'') as utente, ROUND(d.prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.") as prezzo_originale, ROUND(d.delta_prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.") as delta_prezzo");
            }
=======
            $sql_select = 
                    "d.iddocr, d.codice, d.quantita, d.codiva, d.visibile, d.nriga, d.quantita2, d.sconto, " .
                    "CONCAT_WS( ' ', d.descrizione, '[', art.um_qta, art.um, ']' ) AS descrizione, " .
                    "ROUND( d.prezzo,"       . $p4a->e3g_azienda_n_decimali_prezzi . " ) AS prezzo_originale, " .
                    "ROUND( d.delta_prezzo," . $p4a->e3g_azienda_n_decimali_prezzi . " ) AS delta_prezzo, " .
                    "anag_for.descrizione AS fornitore, ";

            if ( $righe_det_per_utente == 1 )  // Dettaglio articoli per utente   
                $sql_select .=
                    "ROUND( d.prezzo + d.delta_prezzo," . $p4a->e3g_azienda_n_decimali_prezzi . " ) AS prezzo, " .
                    "IF ( d.quantita = 0, 0, ROUND( d.totale," . $p4a->e3g_azienda_n_decimali_prezzi . " ) ) AS totale, " .
                    "IF ( d.visibile = 'N' , anag_ute.descrizione, '-' ) AS utente ";
>>>>>>> 1.23
            else
                $sql_select .=
                    "ROUND( d.prezzo + d.delta_prezzo, " . $p4a->e3g_azienda_n_decimali_prezzi . " ) AS prezzo, " .
                    "ROUND( d.totale, " . $p4a->e3g_azienda_n_decimali_prezzi . " ) AS totale ";

            $p4a->ds_docr->setSelect( $sql_select );
        }
        else  // Equogest, senza [art.um_qta]
        {
            if ( $righe_det_per_utente == 1 )
                $p4a->ds_docr->setSelect( "d.codice, IF(d.visibile='N','', ROUND(d.prezzo + d.delta_prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.")) as prezzo, " .
                    "d.quantita, IF(d.visibile='N','', ROUND(d.totale,".$p4a->e3g_azienda_n_decimali_prezzi.")) as totale, d.codiva, " .
                    "d.descrizione, " .
                    "d.visibile, d.nriga, d.iddocr, anag_for.descrizione as fornitore, d.quantita2, d.sconto, IF(d.visibile='N',anag_ute.descrizione,'') as utente, " .
                    "ROUND(d.prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.") as prezzo_originale, ROUND(d.delta_prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.") as delta_prezzo");         
            else
                $p4a->ds_docr->setSelect("d.codice, ROUND(d.prezzo + d.delta_prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.") as prezzo, " .
                    "d.quantita, ROUND(d.totale,".$p4a->e3g_azienda_n_decimali_prezzi.") as totale, d.codiva, " .
                    "d.descrizione, " .
                    "d.visibile, d.nriga, d.iddocr, anag_for.descrizione as fornitore, d.quantita2, d.sconto, " .
                    "ROUND(d.prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.") as prezzo_originale, ROUND(d.delta_prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.") as delta_prezzo");
        }

        $p4a->ds_docr->addJoin( $p4a->e3g_prefix . "articoli AS art", "d.codice = art.codice", "LEFT" );        
        $p4a->ds_docr->addJoin( $p4a->e3g_prefix . "anagrafiche AS anag_for", "anag_for.codice = art.centrale", "LEFT" );
        if ( $righe_det_per_utente == 1 )
           	$p4a->ds_docr->addJoin( $p4a->e3g_prefix . "anagrafiche AS anag_ute", "anag_ute.codice = d.codutente", "LEFT" );

        if ( E3G_TIPO_GESTIONE == 'G' ) 
        {
            // AP commentato il 28.10.08 per consentire di vedere in stampa le righe con qta = 0 
            // andrà migliorato (in che modo?) con la visualizzazione della Qta Aggiunta
            //$p4a->ds_docr->setWhere("iddocr=".$iddoc." AND visibile='S' AND quantita > 0 ");
            if ($righe_det_per_utente == 1)
				$p4a->ds_docr->setWhere( "iddocr = " . $iddoc );            	
			else
				$p4a->ds_docr->setWhere( "iddocr = " . $iddoc . " AND visibile = 'S' " );            	
        }           
        else {
           // tutte le righe di Equogest sono Visibili per default (non esiste la gestione Visibili/Invisibili)
           $p4a->ds_docr->setWhere( "iddocr=".$iddoc." AND visibile='S'" ); 
        }           
        
        if ( $righe_det_per_utente == 1 )
        {   
			$p4a->ds_docr->addOrder( "utente" ); 
          	$p4a->ds_docr->addOrder( "codice" );
        }
        else {
          	$p4a->ds_docr->addOrder( "iddocr" );
          	$p4a->ds_docr->addOrder( "nriga" );
        }		
        $p4a->ds_docr->setPk("iddocr");
        $p4a->ds_docr->load();
        //$p4a->ds_docr->firstRow();

    
        require("class.report.php");
        $pdf = new Creport('a4','portrait');
        
        $desdocumento = $db->queryOne( "SELECT descrizione FROM " . $p4a->e3g_prefix . "doctipidoc WHERE codice = '" . $codtipodoc. "'" );
        $pagamento    = $db->queryOne( "SELECT descrizione FROM " . $p4a->e3g_prefix . "pagamenti WHERE codice = '" . $codtipopag . "'" );
        
        $p4a->build( "p4a_db_source", "ds_campi" );
        $p4a->ds_campi->setTable( $p4a->e3g_prefix . "doccampireport" );
        $p4a->ds_campi->setWhere( "codtipodoc = '" . $codtipodoc . "'" );
        $p4a->ds_campi->addOrder( "ordine" );
        $p4a->ds_campi->setPk( "idtable" );
        $p4a->ds_campi->load();
        $p4a->ds_campi->firstRow();
        
        // ricavo i campi del documento
        $riga = 1 ;
        $arr = array();
        if ( $righe_det_per_utente == 1 )
            $arr["utente"] = "Nome Utente";
        
        $stampaprezzi = "N"; 
        while ( $riga <= $p4a->ds_campi->getNumRows() ) {
            $arr[ strtolower($p4a->ds_campi->fields->campo->getNewValue()) ] = $p4a->ds_campi->fields->nomecampo->getNewValue();
            
            // setto il flag STAMPA PREZZI per passarlo alla routine di stampa
            if ( strtoupper($p4a->ds_campi->fields->campo->getNewValue()) == "PREZZO" ) 
                $stampaprezzi = "S"; 
            
            $p4a->ds_campi->nextRow();
            $riga++;
        }   
        
        // ricavo il totale raggruppato per codici iva
        if ( E3G_TIPO_GESTIONE == 'G' ) {
            $query = 
                "SELECT ROUND( SUM(imponibile), " . $p4a->e3g_azienda_n_decimali_prezzi . " ) AS imponibile, " .
                "       ROUND( SUM(imposta), " . $p4a->e3g_azienda_n_decimali_prezzi . " ) AS imposta, " .
                "       ROUND( SUM(totale), " . $p4a->e3g_azienda_n_decimali_prezzi . " ) AS totale , codiva AS iva " .
                "  FROM " . $p4a->e3g_prefix . "docr " .
                " WHERE visibile='S' AND iddocr = " . $iddoc . " AND codiva <> '' " .
            "  GROUP BY codiva ORDER BY codiva";  
        }
        else {
            $query = 
                "SELECT ROUND( SUM(imponibile),".$p4a->e3g_azienda_n_decimali_prezzi." ) AS imponibile, " .
                "       ROUND( SUM(imposta),".$p4a->e3g_azienda_n_decimali_prezzi." ) AS imposta, " .
                "       ROUND( SUM(totale),".$p4a->e3g_azienda_n_decimali_prezzi." ) AS totale , codiva AS iva " .
                "  FROM " . $p4a->e3g_prefix . "docr " .
                " WHERE iddocr = " . $iddoc . " AND codiva <> '' " .
            "  GROUP BY codiva ORDER BY codiva";  
        }
        
        // ricavo il totale raggruppato per codici iva
        $arrtot1 = array(); 
        $p4a->build("p4a_db_source", "ds_tot1");
        if ( E3G_TIPO_GESTIONE == 'G' ) {           
            $p4a->ds_tot1->setTable($p4a->e3g_prefix."docr");
            $p4a->ds_tot1->setSelect("codiva , ROUND(SUM(imponibile),".$p4a->e3g_azienda_n_decimali_prezzi.") AS imponibile, " .
                "ROUND(SUM(imposta),".$p4a->e3g_azienda_n_decimali_prezzi.") AS imposta, " .
                "ROUND(SUM(totale),".$p4a->e3g_azienda_n_decimali_prezzi.") AS totale ");
            $p4a->ds_tot1->setPk("codiva");
            $p4a->ds_tot1->setWhere("visibile='S' AND iddocr=".$iddoc." AND codiva <> '' ");
            $p4a->ds_tot1->addGroup("codiva");
            $p4a->ds_tot1->addOrder("codiva");
            $arrtot1["codiva"] = "Iva";
            $arrtot1["imponibile"] = "Imponibile";
            $arrtot1["imposta"] = "Imposta";
            $arrtot1["totale"] = "Totale";
        }
        else {               
            $p4a->ds_tot1->setTable($p4a->e3g_prefix."docr");
            $p4a->ds_tot1->setSelect("codiva, ROUND(SUM(imponibile),".$p4a->e3g_azienda_n_decimali_prezzi.") AS imponibile, " .
                "ROUND(SUM(imposta),".$p4a->e3g_azienda_n_decimali_prezzi.") AS imposta");
            $p4a->ds_tot1->setPk("codiva");
            $p4a->ds_tot1->setWhere("iddocr=".$iddoc." AND codiva <> '' ");
            $p4a->ds_tot1->addGroup("codiva");
            $p4a->ds_tot1->addOrder("codiva");
            $arrtot1["codiva"] = "Iva";
            $arrtot1["imponibile"] = "Imponibile";
            $arrtot1["imposta"] = "Imposta";
        }
        $p4a->ds_tot1->load();
        $p4a->ds_tot1->firstRow();
        

        $p4a->build("p4a_db_source", "ds_tot2");
        $p4a->ds_tot2->setTable($p4a->e3g_prefix."docr");
        // ricavo il totale generale
        $arrtot2 = array(); 
        if ( E3G_TIPO_GESTIONE == 'G' ) {
            $stampaiva = "N";
            
            $p4a->ds_tot2->setTable($p4a->e3g_prefix."doct");
            $p4a->ds_tot2->setSelect("ROUND(imponibile,".$p4a->e3g_azienda_n_decimali_prezzi.") as Imponibile, ROUND(imposta,".$p4a->e3g_azienda_n_decimali_prezzi.") as Imposta, ROUND(spesevarie,".$p4a->e3g_azienda_n_decimali_prezzi.") as Spese, ROUND(imponibile+imposta+spesevarie,".$p4a->e3g_azienda_n_decimali_prezzi.") as totale ");
            $p4a->ds_tot2->setWhere("iddoc = ".$iddoc);
            $arrtot2["Imponibile"] = "Imponibile";
            $arrtot2["Imposta"]    = "Imposta";
            $arrtot2["Spese"]      = "Spese";
            $arrtot2["totale"]     = "Totale";
        }
        else {
            $stampaiva = "S";
            $p4a->ds_tot2->setTable($p4a->e3g_prefix."docr");
            $p4a->ds_tot2->setSelect("SUM(imponibile) as imponibile, SUM(imposta) as imposta, SUM(totale) as totale ");
            $p4a->ds_tot2->setWhere("iddocr=".$iddoc." AND codiva <> '' ");
        }
        $p4a->ds_tot2->load();
        $p4a->ds_tot2->firstRow();
        
        if ( E3G_TIPO_GESTIONE == 'G' ) {
            $arr_coda = array();

            $arr_tot = array();
    
            $arr_tot[0]["colonna1"] = "Spese";
            $arr_tot[0]["colonna2"] = number_format($p4a->ds_tot2->fields->Spese->getValue(),2);
            
            $arr_tot[0]["colonna3"] = "Imponibile";
            $arr_tot[0]["colonna4"] = number_format($p4a->ds_tot2->fields->Imponibile->getValue(),2);
            
            $arr_tot[1]["colonna1"] = "Spese Trasporto";
            $arr_tot[1]["colonna2"] = number_format($p4a->ds_doct->fields->spesetrasporto->getValue(), 2);
            
            
            $arr_tot[1]["colonna3"] = "Imposta";
            $arr_tot[1]["colonna4"] = number_format($p4a->ds_tot2->fields->Imposta->getValue(),2);
            
            $arr_tot[2]["colonna1"] = "";
            $arr_tot[2]["colonna2"] = "";

            $arr_tot[2]["colonna3"] = "Totale";
            $arr_tot[2]["colonna4"] = number_format($p4a->ds_tot2->fields->totale->getValue() ,2);
        }
        else {
            $arr_coda = array();
            $arr_coda[0]["colonna1"] = "Pagamento";
            $arr_coda[0]["colonna2"] = $pagamento;
            $arr_coda[1]["colonna1"] = "Banca";
            $arr_coda[1]["colonna2"] = $p4a->e3g_banca." ".$p4a->e3g_agenzia;
            if ( $p4a->e3g_iban == "" ) {
                $arr_coda[2]["colonna1"] = "C/C";
                $arr_coda[2]["colonna2"] = $p4a->e3g_conto_corrente;
                $arr_coda[3]["colonna1"] = "";
                $arr_coda[3]["colonna2"] = "ABI: ".$p4a->e3g_abi." CAB: ".$p4a->e3g_cab." CIN: ".$p4a->e3g_cin;
            }
            else {
                $arr_coda[2]["colonna1"] = "IBAN: ";
                $arr_coda[2]["colonna2"] = $p4a->e3g_iban;
            }
            $arr_tot = array();
            
            $arr_tot[0]["colonna1"] = "Spese Varie";
            $arr_tot[0]["colonna2"] = number_format($p4a->ds_doct->fields->spesevarie->getValue(),2);
            
            $arr_tot[0]["colonna3"] = "Imponibile";
            $arr_tot[0]["colonna4"] = number_format($p4a->ds_tot2->fields->imponibile->getValue(),2);
            
            $arr_tot[1]["colonna1"] = "Spese Trasporto";
            $arr_tot[1]["colonna2"] = number_format($p4a->ds_doct->fields->spesetrasporto->getValue(), 2);
            
            
            $arr_tot[1]["colonna3"] = "Imposta";
            $arr_tot[1]["colonna4"] = number_format($p4a->ds_tot2->fields->imposta->getValue(),2);
            
            $arr_tot[2]["colonna1"] = "";
            $arr_tot[2]["colonna2"] = "";

            $arr_tot[2]["colonna3"] = "Totale";
            $arr_tot[2]["colonna4"] = number_format($p4a->ds_tot2->fields->totale->getValue() + $p4a->ds_doct->fields->spesetrasporto->getValue() + $p4a->ds_doct->fields->spesevarie->getValue(),2);
        }

        $result = $db->queryRow( 
            "SELECT descrizione, piva, cf, indirizzo, cap, localita " .
            "  FROM " . $p4a->e3g_prefix . "anagrafiche WHERE codice = '" . $codclifor . "' " );
        $desc_clifor = $result[ "descrizione" ];
        $piva        = $result[ "piva" ];
        $cf          = $result[ "cf" ];
        $indirizzo   = $result[ "indirizzo" ];
        $cap         = $result[ "cap" ];
        $localita    = $result[ "localita" ];

// TODO Rendere il nome file parametrico e impostabile dall'amministratore (campi: nome GAS, nome doc, data doc, tipo doc, nome utente, nome fornitore ecc.)
        
        // Modello nome file (mantenerlo uguale a quello dell'esportazione CSV più sotto)
        //   "NomeGasBottega_TipoDoc_DataDoc_DescUtente/Fornitore.ext"
        $nome_file = P4A_Get_Valid_File_Name(
            $p4a->e3g_azienda_rag_soc . "_" . 
            $codtipodoc . "_" .
            $p4a->ds_doct->fields->data->getValue() . "_" .
            $desc_clifor );

        $pdf->stampadoc(
            e3g_format_mysql_data( $p4a->ds_doct->fields->data->getValue() ),
            $desdocumento,
            $numerodoc,
            $desc_clifor, $piva, $indirizzo, $cap, $localita,
            $p4a->ds_docr->getAll(), $arr,
            $p4a->ds_tot1->getAll(),
            $arr_tot,
            $stampaprezzi, $nome_file . ".pdf", $stampaiva, $pagamento, $arr_coda, $arrtot1, $cf, $p4a->ds_doct->fields->note->getValue() );               
    }               
}


// -----------------------------------------------------------------------------
// Funzione per l'esportazione come CSV (foglio elettronico) dei documenti
//   $righe_det_per_utente -> 0: righe raggruppate per articolo
//                            1: righe dettagliate per utente
// -----------------------------------------------------------------------------
function genera_stampa_csv( $numerodoc, $iddoc, $codtipodoc, $codclifor, $codtipopag, $righe_det_per_utente=0 )
{
    $p4a =& p4a::singleton();
    $db =& p4a_db::singleton();
    
    if ( $numerodoc != "" ) {
		
		$stringa_select = "d.iddocr, d.visibile, d.nriga"; // stringa utilizzata per la select della table docr
		
		// apro la testa dei documenti
        $p4a->build("p4a_db_source", "ds_doct");
        $p4a->ds_doct->setTable($p4a->e3g_prefix."doct");
        $p4a->ds_doct->setPk("iddoc");
        $p4a->ds_doct->setWhere("iddoc=".$iddoc);
        $p4a->ds_doct->addOrder("data");
        $p4a->ds_doct->addOrder("iddoc");
        $p4a->ds_doct->addOrder("numdocum");
        $p4a->ds_doct->load();
        $p4a->ds_doct->firstRow();

        // ricavo i campi del documento        
        $p4a->build("p4a_db_source", "ds_campi");
        $p4a->ds_campi->setTable($p4a->e3g_prefix."doccampireport");
        $p4a->ds_campi->setPk("idtable");
        $p4a->ds_campi->setWhere("codtipodoc='".$codtipodoc."'");
        $p4a->ds_campi->addOrder("ordine");
        $p4a->ds_campi->load();
        $p4a->ds_campi->firstRow();
        
        $riga = 1 ;
        $arr = array();
        $stampaprezzi = "N";
        
        if ( $righe_det_per_utente == 1 )
        {
          	$arr["codice"] = "Cod. Articolo";
	        $arr["descrizione"] = "Articolo";
          	$arr["prezzo"] = "Prezzo";
	        $arr["prezzo_originale"] = "Prezzo Orig.";
	        $arr["delta_prezzo"] = "Var. Prezzo";
	        $arr["quantita"] = "Q.ta";
	        $arr["totale"] = "Importo";
	        $arr["utente"] = "Nome Utente";
        }
        else
        { 
	        while ($riga <= $p4a->ds_campi->getNumRows()) {
	            $arr[strtolower($p4a->ds_campi->fields->campo->getNewValue())] = $p4a->ds_campi->fields->nomecampo->getNewValue();
				
				// compongo la stringa per la SELECT sui campi della table docr
				// in questo modo i campi sono nel giusto ordinamento
				// problema riscontrato 29.01.09: il getasCSV non prende l'ordinamento dei campi dell'array ma utilizza quello del db_source
				$nome_campo = "";
				switch (strtolower($p4a->ds_campi->fields->campo->getNewValue())) {
				    case "prezzo":
				    	$nome_campo = "ROUND(d.prezzo + d.delta_prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.") as prezzo";
				        break;
				    case "totale":
				        $nome_campo = "ROUND(d.totale,".$p4a->e3g_azienda_n_decimali_prezzi.") as totale";
				        break;
				    case "descrizione":
				        $nome_campo = "CONCAT_WS(' ', d.descrizione, '[', art.um_qta, art.um, ']') AS descrizione"; 		        		
				        break;
				    case "fornitore":
				        $nome_campo = "ana.descrizione as fornitore";
				        break; 		        		
				    case "prezzo_originale":
				        $nome_campo = "ROUND(d.prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.") as prezzo_originale";
				        break; 		        		
				    case "delta_prezzo":
				        $nome_campo = "ROUND(d.delta_prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.") as delta_prezzo";
				        break; 		        		
				    default:
				    	$nome_campo = "d.".strtolower($p4a->ds_campi->fields->campo->getNewValue());            
				    	break;
				}
									
				if ( $stringa_select == "" )
					$stringa_select = $nome_campo;
				else
					$stringa_select .= ", " . $nome_campo;
			            
	            // setto il flag STAMPA PREZZI per passarlo alla routine di stampa
	            if (strtoupper($p4a->ds_campi->fields->campo->getNewValue()) == "PREZZO") {
	                $stampaprezzi = "S"; 
	            }
	            
	            $p4a->ds_campi->nextRow();
	            $riga++;
	      }                           
		}

		// apro il corpo del documento 
        $p4a->build("p4a_db_source", "ds_docr");
        $p4a->ds_docr->setTable($p4a->e3g_prefix."docr d");
        if ( E3G_TIPO_GESTIONE == 'G' ) {
            //$p4a->ds_docr->setSelect("d.codice,ROUND(d.prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.") as prezzo, d.quantita,ROUND(d.totale,".$p4a->e3g_azienda_n_decimali_prezzi.") as totale, d.codiva, CONCAT_WS(' ', d.descrizione, '[', art.um_qta, art.um, ']') AS descrizione, d.visibile, d.nriga, d.iddocr, ana.descrizione as fornitore, d.quantita2, d.sconto");
<<<<<<< e3g_doc_routines.php
	        if ( $righe_det_per_utente == 1 )
	        {
	          	$p4a->ds_docr->setSelect("d.idriga, d.codice, CONCAT_WS(' ', d.descrizione, '[', art.um_qta, art.um, ']') AS descrizione, ROUND(d.prezzo + d.delta_prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.") as prezzo, ROUND(d.prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.") as prezzo_originale, ROUND(d.delta_prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.") as delta_prezzo, d.quantita, ROUND(d.totale,".$p4a->e3g_azienda_n_decimali_prezzi.") as totale, IF(d.visibile='N',ana2.descrizione,'') as utente");
              $p4a->ds_docr->addJoin( $p4a->e3g_prefix . "articoli art", "d.codice = art.codice", "LEFT" );        
              $p4a->ds_docr->addJoin( $p4a->e3g_prefix . "anagrafiche ana2", "ana2.codice = d.codutente", "LEFT" );
=======
	        if ( $righe_det_per_utente == 1 ) {
	          	$p4a->ds_docr->setSelect("d.idriga, d.codice, CONCAT_WS(' ', d.descrizione, '[', art.um_qta, art.um, ']') AS descrizione, IF(d.visibile='N','',ROUND(d.prezzo + d.delta_prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.")) as prezzo, IF(d.visibile='N','',ROUND(d.prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.")) as prezzo_originale, IF(d.visibile='N','',ROUND(d.delta_prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.")) as delta_prezzo, d.quantita, IF(d.visibile='N','',ROUND(d.totale,".$p4a->e3g_azienda_n_decimali_prezzi.")) as totale, IF(d.visibile='N',ana2.descrizione,'') as utente");
                $p4a->ds_docr->addJoin( $p4a->e3g_prefix . "articoli art", "d.codice = art.codice", "LEFT" );        
                $p4a->ds_docr->addJoin( $p4a->e3g_prefix . "anagrafiche ana2", "ana2.codice = d.codutente", "LEFT" );
>>>>>>> 1.23
	        }
	        else {
            	$p4a->ds_docr->setSelect($stringa_select);
	          	$p4a->ds_docr->addJoin( $p4a->e3g_prefix . "articoli art", "d.codice = art.codice", "LEFT" );        
                $p4a->ds_docr->addJoin( $p4a->e3g_prefix . "anagrafiche ana", "ana.codice = art.centrale", "LEFT" );
          }
        }
        else {
            $p4a->ds_docr->setSelect("d.codice,ROUND(d.prezzo,".$p4a->e3g_azienda_n_decimali_prezzi.") as prezzo, d.quantita,ROUND(d.totale,".$p4a->e3g_azienda_n_decimali_prezzi.") as totale, d.codiva, d.descrizione AS descrizione, d.visibile, d.nriga, d.iddocr, ana.descrizione as fornitore, d.quantita2, d.sconto, ROUND(d.imponibile,".$p4a->e3g_azienda_n_decimali_prezzi.") as imponibile, ROUND(d.imposta,".$p4a->e3g_azienda_n_decimali_prezzi.") as imposta ");          
  	        $p4a->ds_docr->addJoin( $p4a->e3g_prefix . "articoli art", "d.codice = art.codice", "LEFT" );        
            $p4a->ds_docr->addJoin( $p4a->e3g_prefix . "anagrafiche ana", "ana.codice = art.centrale", "LEFT" );
      }           
        
        
        if ( $righe_det_per_utente == 1 )
          $p4a->ds_docr->setPk("idriga");
        else
          $p4a->ds_docr->setPk("iddocr");
        
        if ( E3G_TIPO_GESTIONE == 'G' ) {
            if ( $righe_det_per_utente == 1 ) {
              $p4a->ds_docr->setWhere("iddocr=".$iddoc);
            }
			else {
	            // AP commentato il 28.10.08 per consentire di vedere in stampa le righe con qta = 0 
	            // andr� migliorato (in che modo?) con la visualizzazione della Qta Aggiunta
	            //$p4a->ds_docr->setWhere("iddocr=".$iddoc." AND visibile='S' AND quantita > 0 ");
	            $p4a->ds_docr->setWhere("iddocr=".$iddoc." AND visibile='S' "); 
            }        	             
        }           
        else {
            $p4a->ds_docr->setWhere("iddocr=".$iddoc." AND visibile='S'"); 
        }           
                               
        if ($righe_det_per_utente == 1)
        {   
            // ordinamento per Utente e poi per Cod. Articolo
	        $p4a->ds_docr->addOrder("utente", "DESC");
          	$p4a->ds_docr->addOrder("codice");
        }
        else {
        	 // ordinamento di Default
			 $p4a->ds_docr->addOrder("iddocr");
	         $p4a->ds_docr->addOrder("nriga");
        }
        $p4a->ds_docr->load();
        //$p4a->ds_docr->firstRow();
    
        
<<<<<<< e3g_doc_routines.php
        // Modello: "NomeGasBottega_DocTipoXXX_DataDoc_NumDoc.ext"
        //   (mantenerlo uguale a quello dell'esportazione PDF pi� sopra)
        $nome_file = P4A_Get_Valid_File_Name( $p4a->e3g_azienda_rag_soc . 
            "_DOC_Tipo" . $codtipodoc . "_Data" . $p4a->ds_doct->fields->data->getValue() . "_N" . $numerodoc );
=======
        $result = $db->queryRow( 
            "SELECT descrizione " .
            "  FROM " . $p4a->e3g_prefix . "anagrafiche WHERE codice = '" . $codclifor . "' " );
        $desc_clifor = $result[ "descrizione" ];
       
// TODO Rendere il nome file parametrico e impostabile dall'amministratore (campi: nome GAS, nome doc, data doc, tipo doc, nome utente, nome fornitore ecc.)
        
        // Modello nome file (mantenerlo uguale a quello dell'esportazione PDF più sopra)
        //   "NomeGasBottega_TipoDoc_DataDoc_DescUtente/Fornitore.ext"
        $nome_file = P4A_Get_Valid_File_Name(
            $p4a->e3g_azienda_rag_soc . "_" . 
            $codtipodoc . "_" .
            $p4a->ds_doct->fields->data->getValue() . "_" .
            $desc_clifor );
>>>>>>> 1.23

                
        //$p4a->ds_docr->exportToCsv( $nome_file . ".csv", ";", $arr );
        e3g_db_source_exportToCsv( $p4a->ds_docr, $arr, $nome_file . ".csv" );
    }               
    
}
?>