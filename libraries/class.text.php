<?php
/**
 * Progetto e3g - Equogest/GestiGAS
 *   Software gestionali per l'economia solidale
 *   <http://www.progettoe3g.org>
 *
 * Copyright (C) 2003-2009
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


require_once(dirname(__FILE__) . '/../config.php');


// Classe per la generazione dei documenti in HTML
//   define a clas extension to allow the use of a callback to get the table of 
//   contents, and to put the dots in the toc
class FText{
	
	
	function doc2text($filename, $testata, $testata2,$corpo, $totali1, $totali2)
	{
		// filename : nome e path del file output
		// testata : intestazione del documento 
		// testata2 : seconda tabella per testata con pagamento e banca
		// corpo : intestazione del corpo e righe del corpo documento
		// totali1 : primo gruppo dei totali 
		// totali2 : secondo gruppo dei totali 
		// coda : contiene dati come Porto Vettore Firme
		
		
		// da migliorare 
		// scrivo la testata  senza convertirla perchè già OK 
		$cont_testata = $this->matrix2table($testata,"width='350' height='75' border='0'");
		$handle = fopen($filename, 'w');
		fwrite($handle, $cont_testata);
		fclose($handle);

		// scrivo testata 2 con dati pagamento e banca
		$cont_testata2 = $this->matrix2table($testata2,"width='350' height='75' border='0'");
		$handle = fopen($filename, 'a');
		fwrite($handle, $cont_testata2);
		fclose($handle);
	
		// scrivo il corpo		
		$cont_corpo = $this->matrix2table($corpo, "width='100%' border='0'");
		$handle = fopen($filename, 'a');
		fwrite($handle, "<br>");
		fwrite($handle, $cont_corpo);
		fclose($handle);

		// scrivo il totale 1
		$cont_tot1 = $this->matrix2table($totali1,"width='400' border='0'");
		$handle = fopen($filename, 'a');
		fwrite($handle, "<br><br>");
		fwrite($handle, $cont_tot1);
		fclose($handle);

		// scrivo il totale 2 
		$cont_tot2 = $this->matrix2table($totali2,"width='400' border='0'");
		$handle = fopen($filename, 'a');
		fwrite($handle, "<br><br>");
		fwrite($handle, $cont_tot2);
		fclose($handle);

		// scrivo la coda 
		$cont_coda = $this->matrix2table($coda,"width='100%' border='0'");
		$handle = fopen($filename, 'a');
		fwrite($handle, "<br><br>");
		fwrite($handle, $cont_coda);
		fclose($handle);
		
	}
	
	function matrix2table($arr,$tbattrs = "width='100%' border='1'", $clattrs="valign='top' align='left'")
	{
	    $maxX = $maxY = 1;
	    for ($x=0;$x<100;$x++){
	        for ($y=0;$y<100;$y++){
	            if ($arr[$x][$y]!=""){
	                if ($maxX < $x) $maxX = $x;
	                if ($maxY < $y) $maxY = $y;
	            }
	        }
	    }
	    $retval = "<table $tbattrs>\n";
	    for ($x=1;$x<=$maxX;$x++){
	        $retval.=" <tr>\n";
	        for ($y=1;$y<=$maxY;$y++){
	            $retval.= (isset($arr[$x][$y]))
	            ?"  <td $clattrs>".$arr[$x][$y]."</td>\n"
	            :"  <td $clattrs>&nbsp;</td>\n";
	        }
	        $retval.=" </tr>\n";
	    }    
	    return $retval."</table>\n";
	}	

	function elabora_array($myarray)
	{
		$table = array();
		$riga = 1; 
		$colonna = 1; 
		
		foreach ($myarray as $arr_riga) {
			while (list($chiave,$valore) = each($arr_riga))
			{
				$table[$riga][$colonna] = $valore;
				$colonna++;
			}
			$riga++;
			$colonna = 1; 
		}
		
		return $table;
	}	

}
?>