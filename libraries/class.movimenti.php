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


// Classe Movimenti Magazzino e Contabili
class Cmovimenti {
	
	function Segnocontabile ($conto)
	{
		$p4a =& p4a::singleton();	
		$db =& p4a_db::singleton();
		
		$strsegno = $db->queryOne("SELECT segnocontabile FROM  ".$p4a->e3g_prefix."anagrafiche WHERE codice = '".$conto."' ");
		
		return $strsegno; 
	}	
	
	
	
	function contoIva($strcodtipodoc)
	{
		$p4a =& p4a::singleton();	
		$db =& p4a_db::singleton();
	
		$strconto = $db->queryOne("SELECT codcontropiva FROM  ".$p4a->e3g_prefix."doctipidoc WHERE codice = '".$strcodtipodoc."' ");
		
		return $strconto; 
	}	
	
	function contoSpeseVarie($strcodtipodoc)
	{
		$p4a =& p4a::singleton();	
		$db =& p4a_db::singleton();
	
		$strconto = $db->queryOne("SELECT codcontropspvarie FROM  ".$p4a->e3g_prefix."doctipidoc WHERE codice = '".$strcodtipodoc."' ");
		
		return $strconto; 
	}	

	function contoSpeseTrasporto($strcodtipodoc)
	{
		$p4a =& p4a::singleton();	
		$db =& p4a_db::singleton();
	
		$strconto = $db->queryOne("SELECT codcontropsptrasp FROM  ".$p4a->e3g_prefix."doctipidoc WHERE codice = '".$strcodtipodoc."' ");
		
		return $strconto; 
	}	

	function movimenticon($strcodtipodoc, $strnumdoc, $strcodclifor, $strdate, $strannocontabile, $striddoc)
	{		
		$p4a =& p4a::singleton();	
		$db =& p4a_db::singleton();
		
		$tipofn = 'F';
		
		// al momento non funziona bene meglio escludere
		// 11.06.2007 AP
		//return 0;
		
		// Se manca anche solo un parametro
		if ($strcodtipodoc=='' || $strnumdoc=='' || $strannocontabile=='')
		{
			return 0;
		}

		// Controllo che il Documento Generi Movimenti di Magazzino (il flag genmovmag sul tipo doc deve essere S)
		if ($db->queryOne("SELECT genmovcon FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice=".$strcodtipodoc) != 'S')
		{
			return 0;
		}
		
		// Controllo se  Fattura o nota di credito 
		$tipofn = $db->queryOne("SELECT tipofn FROM  ".$p4a->e3g_prefix."doctipidoc WHERE codice=".$strcodtipodoc);
		
		
		// Recupero Causale Movimento e Flag Carico / Scarico
		
		// Recupero Il seriale del Movimento
		$seriale = $db->queryOne("SELECT MAX(codice) FROM  ".$p4a->e3g_prefix."movcont ");
		if (is_numeric($seriale))
        {    	
        	$seriale++;
		}
       	else
       	{
   			$seriale = 1;
   		}
		

		// Apro le righe del  Documento che devo copiare nei movimenti contabili
		$query = "numdocum='".$strnumdoc."' AND codtipodoc='".$strcodtipodoc."' AND anno='".$strannocontabile."'";
		$p4a->build("p4a_db_source", "ds_doc");
		$p4a->ds_doc->setTable( $p4a->e3g_prefix."docr");
		$p4a->ds_doc->setPk("idriga");
		$p4a->ds_doc->setWhere($query);
		$p4a->ds_doc->load();
		$p4a->ds_doc->firstRow();
		
		// Carico la Testa del Documento
		$p4a->build("p4a_db_source", "ds_doctes");
		$p4a->ds_doctes->setTable($p4a->e3g_prefix."doct");
		$p4a->ds_doctes->setPk("iddoc");
		$p4a->ds_doctes->setWhere($query);
		$p4a->ds_doctes->load();
		$p4a->ds_doctes->firstRow();		

		// Cancello gli eventuali movimenti contabili gi? scritti per questo documento
		$codicemov = $db->queryOne("SELECT codice FROM  ".$p4a->e3g_prefix."movcont WHERE anno='".$strannocontabile."' AND codtipodoc='".$strcodtipodoc."' AND numdocum='".$strnumdoc."' ");
		$db->query("DELETE FROM  ".$p4a->e3g_prefix."movconr WHERE codice='".$codicemov."'");
		$db->query("DELETE FROM  ".$p4a->e3g_prefix."movcont WHERE anno='".$strannocontabile."' AND codtipodoc='".$strcodtipodoc."' AND numdocum='".$strnumdoc."'");
			
		// Apro i movimenti contabili RIGHE
		$p4a->build("p4a_db_source", "ds_movconr");
		$p4a->ds_movconr->setTable($p4a->e3g_prefix."movconr");
		$p4a->ds_movconr->setPk("nriga");
		$p4a->ds_movconr->setWhere("1=0");
		$p4a->ds_movconr->load();

		// Apro i movimenti contabili TESTA
		$p4a->build("p4a_db_source", "ds_movcont");
		$p4a->ds_movcont->setTable($p4a->e3g_prefix."movcont");
		$p4a->ds_movcont->setPk("codice");
		$p4a->ds_movcont->setWhere("1=0");
		$p4a->ds_movcont->load();
				
		// trovo l'ultima riga e la incremento
		$id_riga = $db->queryOne("SELECT nriga FROM  ".$p4a->e3g_prefix."movconr ORDER BY nriga DESC ");
		
		if (is_numeric($id_riga))
        {    	
        	$id_riga++;
		}
       	else
       	{
   			$id_riga = 1;
   		}

			
		// Registro la Testa dei Movimenti contabili
		$p4a->ds_movcont->newRow(); 
		$p4a->ds_movcont->fields->codice->setNewValue($seriale); 
        $p4a->ds_movcont->fields->anno->setNewValue($strannocontabile); 
        $p4a->ds_movcont->fields->datareg->setNewValue($p4a->ds_doctes->fields->data->getNewValue()); 
        $p4a->ds_movcont->fields->dataope->setNewValue($p4a->ds_doctes->fields->data->getNewValue()); 
        $p4a->ds_movcont->fields->numdocum->setNewValue($strnumdoc) ;
        $p4a->ds_movcont->fields->regdocum->setNewValue($p4a->ds_doctes->fields->regdocum->getNewValue());
        $p4a->ds_movcont->fields->codtipodoc->setNewValue($strcodtipodoc) ;
        $p4a->ds_movcont->fields->numdoceff->setNewValue($p4a->ds_doctes->fields->numdoceff->getNewValue());
        if (is_numeric($p4a->ds_doctes->fields->totdoc->getNewValue()))
        {
        	$p4a->ds_movcont->fields->totdoc->setValue($p4a->ds_doctes->fields->totdoc->getValue());
        }
        else
        {
        	$p4a->ds_movcont->fields->totdoc->setValue(0);
        }
        
    	
    	$p4a->ds_movcont->fields->fatnac->setNewValue($p4a->ds_doctes->fields->tipofn->getNewValue());
		$p4a->ds_movcont->saveRow();
    	
		
		$totimponibile = 0; 
        $totimposta= 0;
		
		$riga = 1;
		while($riga <= $p4a->ds_doc->getNumRows())
		{		
			
			$p4a->ds_movconr->newRow();
				
			if ($p4a->ds_doc->fields->conto->getNewValue() == '' || $p4a->ds_doc->fields->prezzo->getValue() == 0 )
			{
				$strdescrizione = "";
						
			}
			else
			{	
    			$p4a->ds_movconr->fields->codice->setNewValue($seriale) ; 
            	$strdescrizione = $db->queryOne("SELECT desbreve FROM  ".$p4a->e3g_prefix."doctipidoc WHERE codice = '".$strcodtipodoc."'").'/'.$strnumdoc.'/'; 
            	$strdescrizione = $strdescrizione.$db->queryOne("SELECT codregdoc FROM  ".$p4a->e3g_prefix."doctipidoc WHERE codice = '".$strcodtipodoc."'").'/'.$strannocontabile;
            	
            	//$p4a->ds_movconr->fields->descrizione->getNewValue()= $strdescrizione;
				$p4a->ds_movconr->fields->descrizione->setNewValue($db->queryOne("SELECT descrizione FROM  ".$p4a->e3g_prefix."anagrafiche WHERE codice = '".$p4a->ds_doc->fields->conto->getNewValue()."'")); 
            	$p4a->ds_movconr->fields->nriga->setNewValue($id_riga)  ; 
            	
            	$p4a->ds_movconr->fields->codconto->setNewValue($p4a->ds_doc->fields->conto->getNewValue());
            	
            	$p4a->ds_movconr->fields->anno->setNewValue($p4a->ds_doc->fields->anno->getNewValue()) ;
            	
            	$p4a->ds_movconr->fields->importodare->setValue(0) ; 
            	$p4a->ds_movconr->fields->importoavere->setValue(0)  ;
            	if ($tipofn != 'F') 
            	{
					switch ($this->Segnocontabile($p4a->ds_doc->fields->conto->getNewValue())) {
        				case 'D':
            		    	// Dare
        					if (is_numeric($p4a->ds_doc->fields->imponibile->getValue()))
        					{
        						$p4a->ds_movconr->fields->importodare->setValue($p4a->ds_doc->fields->imponibile->getValue());
        					}
        					else
        					{ 
            					$p4a->ds_movconr->fields->importodare->setValue(0) ;
        					} 
            				$p4a->ds_movconr->fields->importoavere->setValue(0);
            				break;
        
        				case 'A':
            		    	// Avere
            		    	if (is_numeric($p4a->ds_doc->fields->imponibile->getValue()))
        					{
        						$p4a->ds_movconr->fields->importoavere->setValue($p4a->ds_doc->fields->imponibile->getValue()) ;
        					}
        					else
        					{ 
            					$p4a->ds_movconr->fields->importoavere->setValue(0) ;
        					} 
            				$p4a->ds_movconr->fields->importodare->setValue(0) ;
            				break; 	    
        			}
            	}
            	else 
            	{
					switch ($this->Segnocontabile($p4a->ds_doc->fields->conto->getNewValue())) {
        				case 'A':
            		    	// Avere
        					if (is_numeric($p4a->ds_doc->fields->imponibile->getValue()))
        					{
        						$p4a->ds_movconr->fields->importoavere->setValue($p4a->ds_doc->fields->imponibile->getValue()) ;
        					}
        					else
        					{ 
            					$p4a->ds_movconr->fields->importoavere->setValue(0) ;
        					} 
            				$p4a->ds_movconr->fields->importodare->setValue(0) ;
            				break;
        
        				case 'D':
            		    	// Dare
            		    	if (is_numeric($p4a->ds_doc->fields->imponibile->getValue()))
        					{
        						$p4a->ds_movconr->fields->importodare->setValue($p4a->ds_doc->fields->imponibile->getValue());
        					}
        					else
        					{ 
            					$p4a->ds_movconr->fields->importodare->setValue(0) ;
        					} 
            				$p4a->ds_movconr->fields->importoavere->setValue(0) ;
            				break; 	    
        			}            		 
				}
				
           		$totimponibile = $totimponibile + $p4a->ds_doc->fields->totale->getValue();
            	$totimposta= $totimposta + $p4a->ds_doc->fields->imposta->getValue();
           		
            		
				$p4a->ds_movconr->saveRow();
				$id_riga++;
			}
	
			
			$p4a->ds_doc->nextRow();
			
			$riga++;
			
    		
		}
		
		// Inserisco L'imposta 
   	 	$p4a->ds_movconr->newRow();
		$p4a->ds_movconr->fields->codice->setNewValue($seriale); 	
        $p4a->ds_movconr->fields->descrizione->setNewValue($db->queryOne("SELECT descrizione FROM  ".$p4a->e3g_prefix."anagrafiche WHERE codice = '".$this->contoIva($strcodtipodoc)."'")) ; 
        $p4a->ds_movconr->fields->nriga->setNewValue($id_riga); 
        $p4a->ds_movconr->fields->codconto->setNewValue($this->contoIva($strcodtipodoc));
        $p4a->ds_movconr->fields->anno->setNewValue($strannocontabile);

		$p4a->ds_movconr->fields->importodare->setValue(0) ; 
        $p4a->ds_movconr->fields->importoavere->setValue(0) ;
        
        if ($tipofn != 'F') 
        {	  
       		switch ($this->Segnocontabile($p4a->ds_movconr->fields->codconto->getNewValue())) 
			{
    			case 'D':
    		    	// Dare
    				if (is_numeric($p4a->ds_doctes->fields->imposta->getValue()))
    				{
    					$p4a->ds_movconr->fields->importodare->setValue($p4a->ds_doctes->fields->imposta->getValue());
    				}
    				else
    				{ 
    					$p4a->ds_movconr->fields->importodare->setValue(0);
    				} 
    				$p4a->ds_movconr->fields->importoavere->setValue(0);
    				break;
    
    			case 'A':
    		    	// Avere
    		    	if (is_numeric($p4a->ds_doctes->fields->imposta->getValue()))
    				{
    					$p4a->ds_movconr->fields->importoavere->setValue($p4a->ds_doctes->fields->imposta->getValue()) ;
    				}
    				else
    				{ 
    					$p4a->ds_movconr->fields->importoavere->setValue(0);
    				} 
    				$p4a->ds_movconr->fields->importodare->setValue(0);
    				break; 	    
    		}
    		 
		}
		else
		{ 
       		switch ($this->Segnocontabile($p4a->ds_movconr->fields->codconto->getNewValue())) {
    			case 'A':
    		    	// 
    				if (is_numeric($p4a->ds_doctes->fields->imposta->getValue()))
    				{
    					$p4a->ds_movconr->fields->importoavere->setValue($p4a->ds_doctes->fields->imposta->getValue());
    				}
    				else
    				{ 
    					$p4a->ds_movconr->fields->importoavere->setValue(0) ;
    				} 
    				$p4a->ds_movconr->fields->importodare->setValue(0);
    				break;
    
    			case 'D':
    		    	// Dare
    		    	if (is_numeric($p4a->ds_doctes->fields->imposta->getValue()))
    				{
    					$p4a->ds_movconr->fields->importodare->setValue($p4a->ds_doctes->fields->imposta->getValue());
    				}
    				else
    				{ 
    					$p4a->ds_movconr->fields->importodare->setValue(0) ;
    				} 
    				$p4a->ds_movconr->fields->importoavere->setValue(0);
    				break; 	    
    		}
		} 
		
		
			
		$p4a->ds_movconr->saveRow();
    	$id_riga++; 

		
		// Registro il Totale del Documento 
		$p4a->ds_movconr->newRow();
		$p4a->ds_movconr->fields->codice->setNewValue($seriale) ; 	
        $p4a->ds_movconr->fields->descrizione->setNewValue($strdescrizione); 
        $p4a->ds_movconr->fields->nriga->setNewValue($id_riga) ; 
        $p4a->ds_movconr->fields->codconto->setNewValue($strcodclifor) ; 
        $p4a->ds_movconr->fields->anno->setNewValue($strannocontabile);
        	
        $p4a->ds_movconr->fields->importodare->setValue(0) ; 
        $p4a->ds_movconr->fields->importoavere->setValue(0) ;
        
		
        if ($tipofn != 'F') 
        {        	
       		switch ($this->Segnocontabile($strcodclifor)) 
			{
    			case 'D':
    		    	// Dare
    				if (is_numeric($p4a->ds_doctes->fields->totdoc->getValue()))
    				{
						$p4a->ds_movconr->fields->importodare->setValue($p4a->ds_doctes->fields->totdoc->getValue() + $p4a->ds_doctes->fields->spesetrasporto->getValue() + $p4a->ds_doctes->fields->spesevarie->getValue()) ;
    				}
    				else
    				{ 
						$p4a->ds_movconr->fields->importodare->setValue(0);
    				} 
    				$p4a->ds_movconr->fields->importoavere->setValue(0);
    				break;
    
    			case 'A':
    		    	// Avere					
					if (is_numeric($p4a->ds_doctes->fields->totdoc->getValue()))
    				{
    					$p4a->ds_movconr->fields->importoavere->setValue($p4a->ds_doctes->fields->totdoc->getValue() + $p4a->ds_doctes->fields->spesetrasporto->getValue() + $p4a->ds_doctes->fields->spesevarie->getValue()) ;
    				}
    				else
    				{ 
    					$p4a->ds_movconr->fields->importoavere->setValue(0) ;
    				} 
    				$p4a->ds_movconr->fields->importodare->getValue(0);
    				break; 	    
    		}
		}
		else
		{
			switch ($this->Segnocontabile($strcodclifor)) 
			{
    			case 'A':
    		    	// Avere
    				if (is_numeric($p4a->ds_doctes->fields->totdoc->getValue()))
    				{
    					$p4a->ds_movconr->fields->importoavere->setValue($p4a->ds_doctes->fields->totdoc->getValue() + $p4a->ds_doctes->fields->spesetrasporto->getValue() + $p4a->ds_doctes->fields->spesevarie->getValue());
    				}
    				else
    				{ 
    					$p4a->ds_movconr->fields->importoavere->setValue(0) ;
    				} 
    				$p4a->ds_movconr->fields->importodare->getValue(0) ;
    				break;
    
    			case 'D':
    		    	// Dare
    		    	if (is_numeric($p4a->ds_doctes->fields->totdoc->getValue()))
    				{
    					$p4a->ds_movconr->fields->importodare->setValue($p4a->ds_doctes->fields->totdoc->getValue() + $p4a->ds_doctes->fields->spesetrasporto->getValue() + $p4a->ds_doctes->fields->spesevarie->getValue());
    				}
    				else
    				{ 
    					$p4a->ds_movconr->fields->importodare->setValue(0);
    				} 
    				$p4a->ds_movconr->fields->importoavere->setValue(0) ;
    				break; 	    
    		}
    		
    	
        			
		}

  
		//$ds_movconr->insert_row($movcon);
		$p4a->ds_movconr->saveRow();
		$id_riga++; 


		// Registro le spese varie 
		if (is_numeric($p4a->ds_doctes->fields->spesevarie->getValue()))
		{
			if ($p4a->ds_doctes->fields->spesevarie->getValue() > 0 )
			{
				$desspese = "Spese Varie";
				$codspese = $this->contoSpeseVarie($strcodtipodoc); 
				
				$p4a->ds_movconr->newRow();
				$p4a->ds_movconr->fields->codice->setNewValue($seriale) ; 	
		        $p4a->ds_movconr->fields->descrizione->setNewValue($desspese); 
		        $p4a->ds_movconr->fields->nriga->setNewValue($id_riga) ; 
		        $p4a->ds_movconr->fields->codconto->setNewValue($codspese) ; 
		        $p4a->ds_movconr->fields->anno->setNewValue($strannocontabile);
		        	
		        $p4a->ds_movconr->fields->importodare->setValue(0) ; 
		        $p4a->ds_movconr->fields->importoavere->setValue(0) ;
		        
				
		        if ($tipofn != 'F') 
		        {        	
		       		switch ($this->Segnocontabile($codspese)) 
					{
		    			case 'D':
		    		    	// Dare
		    				if (is_numeric($p4a->ds_doctes->fields->spesevarie->getValue()))
		    				{
								$p4a->ds_movconr->fields->importodare->setValue($p4a->ds_doctes->fields->spesevarie->getValue()) ;
		    				}
		    				else
		    				{ 
								$p4a->ds_movconr->fields->importodare->setValue(0);
		    				} 
		    				$p4a->ds_movconr->fields->importoavere->setValue(0);
		    				break;
		    
		    			case 'A':
		    		    	// Avere					
							if (is_numeric($p4a->ds_doctes->fields->spesevarie->getValue()))
		    				{
		    					$p4a->ds_movconr->fields->importoavere->setValue($p4a->ds_doctes->fields->spesevarie->getValue()) ;
		    				}
		    				else
		    				{ 
		    					$p4a->ds_movconr->fields->importoavere->setValue(0) ;
		    				} 
		    				$p4a->ds_movconr->fields->importodare->getValue(0);
		    				break; 	    
		    		}
				}
				else
				{
		       		
		        	switch ($this->Segnocontabile($codspese)) 
					{
		    			case 'A':
		    		    	// Avere
		    				if (is_numeric($p4a->ds_doctes->fields->spesevarie->getValue()))
		    				{
		    					$p4a->ds_movconr->fields->importoavere->setValue($p4a->ds_doctes->fields->spesevarie->getValue());
		    				}
		    				else
		    				{ 
		    					$p4a->ds_movconr->fields->importoavere->setValue(0) ;
		    				} 
		    				$p4a->ds_movconr->fields->importodare->getValue(0) ;
		    				break;
		    
		    			case 'D':
		    		    	// Dare
		    		    	if (is_numeric($p4a->ds_doctes->fields->spesevarie->getValue()))
		    				{
		    					$p4a->ds_movconr->fields->importodare->setValue($p4a->ds_doctes->fields->spesevarie->getValue());
		    				}
		    				else
		    				{ 
		    					$p4a->ds_movconr->fields->importodare->setValue(0);
		    				} 
		    				$p4a->ds_movconr->fields->importoavere->setValue(0) ;
		    				break; 	    
		    		}
				}
				$p4a->ds_movconr->saveRow();
				$id_riga++; 
			}	
		}


		// Registro le spese Trasporto 
		if (is_numeric($p4a->ds_doctes->fields->spesevarie->getValue()))
		{
			if ($p4a->ds_doctes->fields->spesevarie->getValue() >0 )
			{
				$desspese = "Spese Trasporto";
				$codspese = $this->contoSpeseTrasporto($strcodtipodoc); 
				
				$p4a->ds_movconr->newRow();
				$p4a->ds_movconr->fields->codice->setNewValue($seriale) ; 	
		        $p4a->ds_movconr->fields->descrizione->setNewValue($desspese); 
		        $p4a->ds_movconr->fields->nriga->setNewValue($id_riga) ; 
		        $p4a->ds_movconr->fields->codconto->setNewValue($codspese) ; 
		        $p4a->ds_movconr->fields->anno->setNewValue($strannocontabile);
		        	
		        $p4a->ds_movconr->fields->importodare->setValue(0) ; 
		        $p4a->ds_movconr->fields->importoavere->setValue(0) ;
		        
				
		        if ($tipofn != 'F') 
		        {        	
		       		switch ($this->Segnocontabile($codspese)) 
					{
		    			case 'D':
		    		    	// Dare
		    				if (is_numeric($p4a->ds_doctes->fields->spesetrasporto->getValue()))
		    				{
								$p4a->ds_movconr->fields->importodare->setValue($p4a->ds_doctes->fields->spesetrasporto->getValue()) ;
		    				}
		    				else
		    				{ 
								$p4a->ds_movconr->fields->importodare->setValue(0);
		    				} 
		    				$p4a->ds_movconr->fields->importoavere->setValue(0);
		    				break;
		    
		    			case 'A':
		    		    	// Avere					
							if (is_numeric($p4a->ds_doctes->fields->spesetrasporto->getValue()))
		    				{
		    					$p4a->ds_movconr->fields->importoavere->setValue($p4a->ds_doctes->fields->spesetrasporto->getValue()) ;
		    				}
		    				else
		    				{ 
		    					$p4a->ds_movconr->fields->importoavere->setValue(0) ;
		    				} 
		    				$p4a->ds_movconr->fields->importodare->getValue(0);
		    				break; 	    
		    		}
				}
				else
				{
		        	switch ($this->Segnocontabile($codspese)) 
					{
		    			case 'A':
		    		    	// Avere
		    				if (is_numeric($p4a->ds_doctes->fields->spesetrasporto->getValue()))
		    				{
		    					$p4a->ds_movconr->fields->importoavere->setValue($p4a->ds_doctes->fields->spesetrasporto->getValue());
		    				}
		    				else
		    				{ 
		    					$p4a->ds_movconr->fields->importoavere->setValue(0) ;
		    				} 
		    				$p4a->ds_movconr->fields->importodare->getValue(0) ;
		    				break;
		    
		    			case 'D':
		    		    	// Dare
		    		    	if (is_numeric($p4a->ds_doctes->fields->spesetrasporto->getValue()))
		    				{
		    					$p4a->ds_movconr->fields->importodare->setValue($p4a->ds_doctes->fields->spesetrasporto->getValue());
		    				}
		    				else
		    				{ 
		    					$p4a->ds_movconr->fields->importodare->setValue(0);
		    				} 
		    				$p4a->ds_movconr->fields->importoavere->setValue(0) ;
		    				break; 	    
		    		}
				}
				$p4a->ds_movconr->saveRow();
				$id_riga++; 
			}
		}


		
    	$p4a->ds_movconr->destroy();
    	$p4a->ds_movcont->destroy();
    	
	
    	return 0;
	}

	// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXx
	
	function movimentimag($strcodtipodoc, $strnumdoc, $strcodclifor, $strdate, $strannocontabile, $striddoc)
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
	
		$p4a->build("p4a_db_source", "ds_magmagr");
		$p4a->ds_magmagr->setTable($p4a->e3g_prefix."movmagr");
		$p4a->ds_magmagr->setWhere("1=0");
		$p4a->ds_magmagr->setPK("idriga");
		$p4a->ds_magmagr->load();
		$p4a->ds_magmagr->firstRow();

		
		// Se manca anche solo un parametro
		if ($strcodtipodoc=='' || $strnumdoc=='' || $strannocontabile=='')
		{
			return 0;
		}

		// Controllo che il Documento Generi Movimenti di Magazzino (il flag genmovmag sul tipo doc deve essere S)
		if ($db->queryOne("SELECT genmovmag FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice=".$strcodtipodoc) != 'S')
		{
			return 0;
		}

		// Recupero Causale Movimento e Flag Carico / Scarico
		$causalemov = $db->queryOne("SELECT codcaumag FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$strcodtipodoc."'");
		$carscar = $db->queryOne("SELECT carscar FROM ".$p4a->e3g_prefix."movmagcausali WHERE codice='".$causalemov."'");

		// Recupero Il seriale del Movimento
		$seriale = $db->queryOne("SELECT MAX(seriale) FROM ".$p4a->e3g_prefix."movmagr");
		if (is_numeric($seriale))
        {    	
        	$seriale++;
		}
       	else
       	{
   			$seriale = 1;
   		}

		// Apro le righe del  Documento che devo copiare nei movimenti di magazzino
		$query = "numdocum='".$strnumdoc."' AND codtipodoc='".$strcodtipodoc."' AND anno='".$strannocontabile."'";
		$p4a->build("p4a_db_source", "ds_doc");
		$p4a->ds_doc->setTable($p4a->e3g_prefix."docr");
		$p4a->ds_doc->setPk("idriga");
		$p4a->ds_doc->setWhere($query);
		$p4a->ds_doc->load();
		$p4a->ds_doc->firstRow();


		// Cancello gli eventuali movimenti di magazzino gi� scritti per questo documento
		$db->query("DELETE FROM ".$p4a->e3g_prefix."movmagr WHERE anno='".$strannocontabile."' AND codtipodoc='".$strcodtipodoc."' AND numdocum='".$strnumdoc."'");


		$riga = $p4a->ds_doc->getRowNumber();
		// trovo l'ultima riga e la incremento
		$id_riga = $db->queryOne("SELECT MAX(idriga) FROM ".$p4a->e3g_prefix."movmagr");
		
		if (is_numeric($id_riga))
        {    	
        	$id_riga++;
		}
       	else
       	{
   			$id_riga = 1;
   		}
			
		
			 
		while($riga<=$p4a->ds_doc->getNumRows())
		{		
			if ($p4a->ds_doc->fields->codice->getNewValue() != '')
			{
				$p4a->ds_magmagr->newRow();

				$p4a->ds_magmagr->fields->codarticolo->setNewValue($p4a->ds_doc->fields->codice->getNewValue());
				
    			if ($carscar == 'S')
    			{
    				$p4a->ds_magmagr->fields->qta->setNewValue(-1 * ($p4a->ds_doc->fields->quantita->getNewValue() - $p4a->ds_doc->fields->quantita2->getNewValue()));
           		}
           		else
           		{
          			$p4a->ds_magmagr->fields->qta->setNewValue($p4a->ds_doc->fields->quantita->getNewValue() - $p4a->ds_doc->fields->quantita2->getNewValue());
           		}
 				$p4a->ds_magmagr->fields->data->setNewValue($p4a->ds_doc->fields->data->getNewValue());
 				$p4a->ds_magmagr->fields->seriale->setNewValue($seriale);
 				$p4a->ds_magmagr->fields->codcaumov->setNewValue($causalemov);
 				$p4a->ds_magmagr->fields->carscar->setNewValue($carscar);
    			
    			$p4a->ds_magmagr->fields->prezzoven->setNewValue($p4a->ds_doc->fields->prezzo->getNewValue());
 				$p4a->ds_magmagr->fields->codtipodoc->setNewValue($strcodtipodoc);
 				$p4a->ds_magmagr->fields->numdocum->setNewValue($strnumdoc);
 				$p4a->ds_magmagr->fields->anno->setNewValue($strannocontabile);
    			$p4a->ds_magmagr->fields->idriga->setNewValue($id_riga);
    			$p4a->ds_magmagr->fields->idtable->setNewValue($id_riga);
    			if ($strcodclifor == "")
    			{
          			$p4a->ds_magmagr->fields->codanag->setNewValue(""); 			
    			}
 				else 
 				{
          			$p4a->ds_magmagr->fields->codanag->setNewValue($strcodclifor); 			
    			}
 				
    			
    			$p4a->ds_magmagr->saveRow();
    		
    			//parent::saveRow();
    			$id_riga++;
    			
			}
		
			$p4a->ds_doc->nextRow();
			$riga++;
		}

		//$ds_movmag->destroy();
				
					  
		return 0;
	}	

	
	
	
}
