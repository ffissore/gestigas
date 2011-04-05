<?php
//===================================================================================================
// this is the php file which creates the readme.pdf file, this is not seriously
// suggested as a good way to create such a file, nor a great example of prose,
// but hopefully it will be useful
//
// adding ?d=1 to the url calling this will cause the pdf code itself to ve echoed to the
// browser, this is quite useful for debugging purposes.
// there is no option to save directly to a file here, but this would be trivial to implement.
//
// note that this file comprisises both the demo code, and the generator of the pdf documentation
//
//===================================================================================================


// don't want any warnings turning up in the pdf code if the server is set to 'anal' mode.
//error_reporting(7);
//error_reporting(E_ALL);
//set_time_limit(1800);


include dirname(__FILE__) . '/class.ezpdf.php';
require_once(dirname(__FILE__) . '/../config.php');


// define a clas extension to allow the use of a callback to get the table of contents, and to put the dots in the toc
class Creport extends Cezpdf {

	var $reportContents = array();

	function Creport($p,$o){
	  $this->Cezpdf($p,$o);
	}

	function rf($info){
	  // this callback records all of the table of contents entries, it also places a destination marker there
	  // so that it can be linked too
	  $tmp = $info['p'];
	  $lvl = $tmp[0];
	  $lbl = rawurldecode(substr($tmp,1));
	  $num=$this->ezWhatPageNumber($this->ezGetCurrentPageNumber());
	  $this->reportContents[] = array($lbl,$num,$lvl );
	  $this->addDestination('toc'.(count($this->reportContents)-1),'FitH',$info['y']+$info['height']);
	}

	function dots($info){
	  // draw a dotted line over to the right and put on a page number
	  $tmp = $info['p'];
	  $lvl = $tmp[0];
	  $lbl = substr($tmp,1);
	  $xpos = 520;

	  switch($lvl){
		case '1':
		  $size=16;
		  $thick=1;
		  break;
		case '2':
		  $size=12;
		  $thick=0.5;
		  break;
	  }

	  $this->saveState();
	  $this->setLineStyle($thick,'round','',array(0,10));
	  $this->line($xpos,$info['y'],$info['x']+5,$info['y']);
	  $this->restoreState();
	  $this->addText($xpos+5,$info['y'],$size,$lbl);
	}


	//--------------------------------------------------------------------------
	// Prepara l'intestazione della stampa
	//--------------------------------------------------------------------------
	function set_pdf_header( $titolo_stampa )
	{
		$p4a =& p4a::singleton();

		$this->ezSetDy(180);
		
		if ( E3G_TIPO_GESTIONE == 'G' )
			$logo = "./images/gestigas_02.jpg";
		else
			$logo = "./images/equogest_02.jpg";
		// TODO In futuro fare in modo di poter usare anche un logo indicato 
		// dall'admin e specificato in ..._azienda.logopath (o altro modo)
		
		if ( file_exists($logo) )
			$this->addJpegFromFile( $logo, 50, $this->y-100, 200, 0 );
        else {
			//img = ImageCreatefromjpeg('http://www.ros.co.nz/pdf/ros.jpg');
  			//$this-> addImage($img,199,$this->y-100,200,0);
        }

		$this->ezSetDy(-100);
		
		$this->restoreState();
		$this->closeObject();
		
		$this->ezText( $titolo_stampa, 18, array('justification'=>'centre', 'left'=>'1' ) );
		$this->ezText( 
			$p4a->e3g_azienda_rag_soc, 
			12, array('justification'=>'centre', 'left'=>'1' ) );
		$this->ezText( 
			"data di stampa: " . date( "j F Y - H:i" ) . " - Utente: $p4a->e3g_utente_desc",
			9, array('justification'=>'centre', 'left'=>'1' ) );
		$this->ezText( "", 10, array('justification'=>'centre', 'left'=>'1' ) );
	}
	

	//--------------------------------------------------------------------------
	// Prepara il piè di pagina della stampa
	//--------------------------------------------------------------------------
	function set_pdf_footer()
	{
		$this->ezText( "", 8, array('justification'=>'centre', 'left'=>'1' ));
		$this->ezText( e3g_get_text_footer(), 8, array('justification'=>'centre', 'left'=>'1' ) );
	}

		
	function stampareport( $corpo, $colonne, $titolo, $nomefile )
	{
		$this-> ezSetMargins(200,70,50,50);

		// put a line top and bottom on all the pages
		$all = $this->openObject();
		$this->saveState();
		$this->setStrokeColor(0,0,0,1);
		$this->line(20,40,578,40);
		$this->line(20,822,578,822);

		//$mainFont = './fonts/Helvetica.afm';
		//$mainFont = './fonts/Times-Roman.afm';
		$mainFont = dirname(__FILE__).'/fonts/Times-Roman.afm';		
		//$codeFont = './fonts/Courier.afm';
		$codeFont = dirname(__FILE__).'/fonts/Courier.afm';
 
		// select a font
		$this->selectFont($mainFont);

		$this->ezStartPageNumbers(500,28,10,'','',1);
		
		$this->set_pdf_header( $titolo );

		// note that object can be told to appear on just odd or even pages by changing 'all' to 'odd'
		// or 'even'.
		$this->addObject($all,'all');

		// CORPO
		$this->ezTable( $corpo,$colonne,'',array('width'=>'500'));
		
		$this->set_pdf_footer();

		//$this->openHere('Fit');

		$this->ezStopPageNumbers(1,1);
		
		//if (isset($d) && $d){
		//  $pdfcode = $this->ezOutput(1);
		//  $pdfcode = str_replace("\n","\n<br>",htmlspecialchars($pdfcode));
		//  echo '<html><body>';
		//  echo trim($pdfcode);
		//  echo '</body></html>';
		//} else {
		//  $this->ezStream();
		//}
        
        $filename = P4A_Get_Valid_File_Name( $nomefile . date( "_Y-m-d_H-i" ) . ".pdf" );
        
		$output = $this->ezOutput(1);
	
		$p4a =& p4a::singleton();

        header("Cache-control: private");
        header("Content-Type: text/comma-separated-values; charset=" .
        $p4a->i18n->getCharset());
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Content-Length: " . strlen($output));
		echo $output;
		die();
	}

	
	function schedaarticolo($art, $prod, $nomefile)
	{
		$this-> ezSetMargins(200,70,50,50);

		// put a line top and bottom on all the pages
		$all = $this->openObject();
		$this->saveState();
		$this->setStrokeColor(0,0,0,1);
		$this->line(20,40,578,40);
		$this->line(20,822,578,822);

		//$mainFont = './fonts/Helvetica.afm';
		//$mainFont = './fonts/Times-Roman.afm';
		//$mainFont  = './fonts/Courier.afm';
		$mainFont = dirname(__FILE__).'/fonts/Times-Roman.afm';		
		$codeFont = dirname(__FILE__).'/fonts/Courier.afm';

		$mainFont  = dirname(__FILE__)."/fonts/Courier.afm";
		// select a font
		$this->selectFont($mainFont);

		
		$this->set_pdf_header( $titolo );
		

		// note that object can be told to appear on just odd or even pages by changing 'all' to 'odd'
		// or 'even'.
		$this->addObject($all,'all');

		// CORPO		
		$this->ezText($art[0]['descrizione'], 16,array('justification'=>'centre','left'=>'1' ));
		$this->ezText("[".$art[0]['codice']."]", 14,array('justification'=>'centre','left'=>'1' ));
		$this->ezText("", 14,array('justification'=>'centre','left'=>'1' ));
		$this->ezText("", 14,array('justification'=>'centre','left'=>'1' ));
		$this->ezText("", 14,array('justification'=>'centre','left'=>'1' ));
		$this->ezText("", 14,array('justification'=>'centre','left'=>'1' ));

		$this->ezText("                prezzo : ".$art[0]['prezzoven'], 12,array('justification'=>'left','left'=>'1' ));
		$this->ezText("            Unità Mis. : ".$art[0]['um'], 12,array('justification'=>'left','left'=>'1' ));
		$this->ezText("            Pz. x Conf : ".$art[0]['pzperconf'], 12,array('justification'=>'left','left'=>'1' ));
		$this->ezText("  Q.tà min. per utente : ".$art[0]['pzperconf'], 12,array('justification'=>'left','left'=>'1' ));
		$this->ezText("", 12,array('justification'=>'left','left'=>'1' ));
		$this->ezText("             Categoria : ".$art[0]['tipo'], 12,array('justification'=>'left','left'=>'1' ));
		$this->ezText("       Sotto-Categoria : ".$art[0]['catmerce'], 12,array('justification'=>'left','left'=>'1' ));
						
		$this->ezText("", 12,array('justification'=>'left','left'=>'1' ));
		$this->ezText("", 12,array('justification'=>'left','left'=>'1' ));

		$this->ezText("            Produttore : ".$prod[0]['descrizione'], 12,array('justification'=>'left','left'=>'1' ));
		$this->ezText("             indirizzo : ".$prod[0]['indirizzo'], 12,array('justification'=>'left','left'=>'1' ));
		$this->ezText("                 Città : ".$prod[0]['localita'], 12,array('justification'=>'left','left'=>'1' ));
		$this->ezText("             provincia : ".$prod[0]['provincia'], 12,array('justification'=>'left','left'=>'1' ));
		$this->ezText("", 12,array('justification'=>'left','left'=>'1' ));
		$this->ezText("                  note : ".$prod[0]['note'], 12,array('justification'=>'left','left'=>'1' ));

		$this->ezText("              telefono : ".$prod[0]['telefono'], 12,array('justification'=>'left','left'=>'1' ));
		$this->ezText("                   fax : ".$prod[0]['fax'], 12,array('justification'=>'left','left'=>'1' ));
		$this->ezText("                E-mail : ".$prod[0]['email'], 12,array('justification'=>'left','left'=>'1' ));
		$this->ezText("              Sito web : ".$prod[0]['www'], 12,array('justification'=>'left','left'=>'1' ));

		
		$this->set_pdf_footer();
		
		
		//$this->openHere('Fit');

		$this->ezStopPageNumbers(1,1);
		

		if ($nomefile == "" )
			$filename = "Scheda_articolo.pdf";
		else 
			$filename = $nomefile;

		$output = $this->ezOutput(1);
	
		$p4a =& p4a::singleton();

        header("Cache-control: private");
        header("Content-Type: text/comma-separated-values; charset=" .
        $p4a->i18n->getCharset());
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Content-Length: " . strlen($output));
		echo $output;
		die();
	}
	

	function stampadoc( $data, $desdoc, $clifor, $piva, $indirizzo, $localita, $corpo, $colonne, $tot1, $tot2, $stampaprezzi, $nomefile, $stampaiva, $pagamento, $coda, $coltot1, $cf, $note)
	{
		$p4a =& p4a::singleton();
		$myline = "";
		$myline = str_pad("", 80, "_", STR_PAD_LEFT);  

		$this-> ezSetMargins(200,58,50,50);


		// put a line top and bottom on all the pages
		$all = $this->openObject();
		$this->saveState();
		$this->setStrokeColor(0,0,0,1);
		$this->line(20,50,578,50);
		$this->line(20,822,578,822);

		//$mainFont = './fonts/Helvetica.afm';
		$mainFont = dirname(__FILE__).'/fonts/Times-Roman.afm';
		$codeFont = dirname(__FILE__).'/fonts/Courier.afm';
		// select a font
		$this->selectFont($mainFont);


		$this->ezSetDy(180);

		if ( E3G_TIPO_GESTIONE == 'G' )
			$logo = "./images/gestigas_02.jpg";
		else
			$logo = "./images/equogest_02.jpg";
			
		$logo = $p4a->e3g_azienda_path_logo;
			
		// In futuro fare in modo di poter usare anche un logo indicato 
		// dall'admin e specificato in ..._azienda.logopath (o altro modo)
		
       	if (file_exists($logo))
        {
          $this->addJpegFromFile($logo,20,$this->y-120,300);
        } 
        else 
        {
			if ( E3G_TIPO_GESTIONE == 'G' )
				$logo = "./images/gestigas_02.jpg";
			else
				$logo = "./images/equogest_02.jpg";
				
			if (file_exists($logo))
        	{
          		$this->addJpegFromFile($logo,20,$this->y-120,300);
        	} 
        
		}

		
		$this->ezStartPageNumbers(500,28,10,'','',1);
		
		
		// DATI TESTA 
		//$this->ezText("", 14,array('justification'=>'center','left'=>'1' ));
		$this->ezText($desdoc, 14,array('justification'=>'right','left'=>'1' ));
		$this->ezText("del ".$data, 14,array('justification'=>'right','left'=>'1' ));
		$this->ezText("", 14,array('justification'=>'center','left'=>'1' ));
		
		$this->ezText("", 14,array('justification'=>'center','left'=>'1' ));
		$this->ezText($clifor, 12,array('justification'=>'right','left'=>'1' ));
		$this->ezText($indirizzo, 12,array('justification'=>'right','left'=>'1' ));
		$this->ezText($localita, 12,array('justification'=>'right','left'=>'1' ));
		if ($piva!="")
		{
        	$this->ezText("P.IVA: ".$piva, 12,array('justification'=>'right','left'=>'1' ));
		} 
		else
		{
        	$this->ezText("", 12,array('justification'=>'right','left'=>'1' ));
		} 
		
		if ($cf!="")
		{
        	$this->ezText("Cod.Fisc.: ".$cf, 12,array('justification'=>'right','left'=>'1' ));
		} 
		else
		{
        	$this->ezText("", 12,array('justification'=>'right','left'=>'1' ));
		} 
        
		if ($note!="")
		{
        	$this->ezText("Note: ".$note, 12,array('justification'=>'right','left'=>'1' ));
		} 
		else
		{
        	//$this->ezText("", 12,array('justification'=>'right','left'=>'1' ));
		} 
		
		// Intestazione azienda
		/*
		$this->ezText( "", 10, array('justification'=>'left','left'=>'1' ));
		$this->ezText( $p4a->e3g_azienda_rag_soc, 12, array('justification'=>'left','left'=>'1' ));
		$this->ezText( $p4a->e3g_azienda_indirizzo." ".$p4a->e3g_azienda_cap . " " . $p4a->e3g_azienda_localita . " (" . $p4a->e3g_azienda_provincia . ")", 10, array('justification'=>'left','left'=>'1' ));
		//$this->ezText($p4a->e3g_azienda_cap . " " . $p4a->e3g_azienda_localita . " (" . $p4a->e3g_azienda_provincia . ")",10, array('justification'=>'left','left'=>'1' ));
		//$this->ezText( "", 10, array('justification'=>'left','left'=>'1' ));
		$this->ezText( "Tel. " . $p4a->e3g_azienda_telefono."   ".$p4a->e3g_azienda_email, 10, array('justification'=>'left','left'=>'1' ));
		//$this->ezText( $p4a->e3g_azienda_email, 10, array('justification'=>'left','left'=>'1' ));
		if ( $p4a->e3g_azienda_piva != "")
		{
				$this->ezText( "P.IVA: ".$p4a->e3g_azienda_piva, 10, array('justification'=>'left','left'=>'1' ));
		} 
		$this->ezText( "", 10, array('justification'=>'left','left'=>'1' ));
		*/
		
			
		//$this->ezSetDy(-100);
		$this->ezSetDy(-10);
		$this->line(45,$this->y,540,$this->y);
		$this->ezSetDy(-10);
		
		//$this-> ezSetMargins(50,70,50,50);
		$privacy = "Ai sensi del Dlgs. 196/2003 si informa che, in base ai rapporti commerciali in essere, deteniamo i Vs. dati anagrafici e fiscali strettamente necessari"; 
		$privacy2 = "ai fini dell'espletamento degli adempimenti di legge e per esclusiva finalita' commerciale";
		$this->addText(30,44,7,$privacy);
		$this->addText(30,38,7,$privacy2);
		$this->addText(30,28,8,e3g_get_text_footer());

		
		$this->restoreState();
		$this->closeObject();
		// note that object can be told to appear on just odd or even pages by changing 'all' to 'odd'
		// or 'even'.
		$this->addObject($all,'all');


		
		// CORPO
		$this->ezTable( $corpo,$colonne,'',array('width'=>'500', 'shaded'=>'0', 'showLines'=>'2', 'fontSize'=>'9'));
		//array('descrizione'=>'descrizione','dataLancio'=>'dataLancio','Ritorno'=>'Ritorno','dataRitorno'=>'dataRitorno','dataRitorno'=>'dataRitorno','note'=>'note')

		//$this->ezSetDy(-100);
		// modified to use the local file if it can

		//$this->openHere('Fit');

			
	   	$this->ezText("", 10,array('justification'=>'right','left'=>'1' ));
		// Totali e Banca
		//$this->ezText("TOTALI DOCUMENTO", 12,array('justification'=>'left','left'=>'1' ));
		$this->ezText("", 12,array('justification'=>'right','left'=>'1' ));
		$YPagamenti = $this->y;
		$this->ezTable( $coda,'','',array('xPos'=>'left', 'xOrientation'=>'right','showHeadings'=>'0', 'shaded'=>'0', 'showLines'=>'1', 'fontSize'=>'9'));
		//$this->ezTable( $coda,'','',array('xPos'=>'right', 'xOrientation'=>'left','showHeadings'=>'0'));

		//$this->ezText("", 12,array('justification'=>'right','left'=>'1' ));
		//$this->line(45,$this->y,540,$this->y);
		
	 	if ($stampaprezzi == "S")
		{
    		//$this->ezText("", 12,array('justification'=>'right','left'=>'1' ));
    	
    		// CASTELLETTO TOTALI
    		//$this->ezText("TOTALI DOCUMENTO", 12,array('justification'=>'left','left'=>'1' ));
    		//$this->ezText("", 8,array('justification'=>'right','left'=>'1' ));
    		//$this->ezTable( $tot2,'','',array('width'=>'500'));
			//$this->ezSetY($myY);
			$this->ezSetY($YPagamenti);
			$this->ezTable( $tot2,'','',array('xPos'=>'right', 'xOrientation'=>'left', 'shaded'=>'0', 'showLines'=>'1', 'showHeadings'=>'0', 'fontSize'=>'9'));
			
	      	if ($stampaiva =="S")
			{	
	    	// Castelletto TOTALI per iva	    		
	    		//$this->ezText("", 12,array('justification'=>'right','left'=>'1' ));
	    		//$this->ezText("", 12,array('justification'=>'right','left'=>'1' ));		
	    		//$this->ezText("TOTALI per iva", 12,array('justification'=>'left','left'=>'1' ));
	    		//$this->ezText("", 8,array('justification'=>'right','left'=>'1' ));
	    		//$this->ezTable( $tot1,'','',array('width'=>'500'));
				$myY = $this->y;
				$this->ezText("", 9,array('justification'=>'right','left'=>'1' ));
				$this->ezTable( $tot1,$coltot1,'',array('xPos'=>'right', 'xOrientation'=>'left', 'shaded'=>'0', 'showLines'=>'1'));
			}
			else
			{	
				$myY = $this->y;
			}	

		}
		
		
		/*
		// info pagamento 
		if ($pagamento != "" )
		{
			$this->ezText( $myline, 12, array('justification'=>'left','left'=>'1' ));
			$this->ezText( "Pagamento", 12, array('justification'=>'left','left'=>'1' ));
			$this->ezText( $pagamento, 12, array('justification'=>'left','left'=>'1' ));
		}	
		
		// estremi banca 
		$dati_banca = trim($p4a->e3g_banca.$p4a->e3g_agenzia.$p4a->e3g_abi.$p4a->e3g_cab.$p4a->e3g_cin.$p4a->e3g_conto_corrente);			     
		if ($dati_banca != "")
		{
			$this->ezText( $myline, 12, array('justification'=>'left','left'=>'1' ));
			$this->ezText( "Coordinate Bancarie", 12, array('justification'=>'left','left'=>'1' ));
			$this->ezText( $p4a->e3g_banca." ".$p4a->e3g_agenzia, 12, array('justification'=>'left','left'=>'1' ));
			$this->ezText( "C/C: ".$p4a->e3g_conto_corrente, 12, array('justification'=>'left','left'=>'1' ));
			$this->ezText( "ABI: ".$p4a->e3g_abi." CAB: ".$p4a->e3g_cab." CIN: ".$p4a->e3g_cin, 12, array('justification'=>'left','left'=>'1' ));
		}	
		*/			   
	  

		/*
		$privacy = "Ai sensi del Dlgs. 196/2003 si informa che, in base ai rapporti commerciali in essere, deteniamo i Vs. dati anagrafici e fiscali strettamente necessari"; 
		$privacy2 = "ai fini dell'espletamento degli adempimenti di legge e per esclusiva finalita' commerciale";

		$this->ezText("", 6,array('justification'=>'center','left'=>'1' ));
		$this->ezText("", 6,array('justification'=>'center','left'=>'1' ));
		$this->ezText($privacy, 6,array('justification'=>'center','left'=>'1' ));
		$this->ezText($privacy2, 6,array('justification'=>'center','left'=>'1' ));
		*/
		
		//$this->set_pdf_footer();

		
		$this->ezStopPageNumbers(1,1);
		


		if ($nomefile == "" )
			$filename = "doc.pdf";
		else 
			$filename = $nomefile;
		$output = $this->ezOutput(1);
		
		$p4a =& p4a::singleton();

        header("Cache-control: private");
        header("Content-Type: text/comma-separated-values; charset=" .
        $p4a->i18n->getCharset());
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Content-Length: " . strlen($output));
		echo $output;
		die();
	}
	
}
?>