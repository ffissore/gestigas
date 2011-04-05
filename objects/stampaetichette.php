<?php

class stampaetichette extends P4A_Mask
{
	function stampaetichette ()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		

		$this->build("p4a_db_source", "ds_prodotti");
		$this->ds_prodotti->setTable($p4a->e3g_prefix."articoli");
		$this->ds_prodotti->setPk("idarticolo");
		//$this->ds_prodotti->setPageLimit(5);


		$this->ds_prodotti->addOrder("descrizione");
		$this->ds_prodotti->load();


		$this->setSource($this->ds_prodotti);
		$this->ds_prodotti->firstRow();

		// Table articoli 
		$table =& $this->build("p4a_table", "table");
		$table->setWidth(350);
		$table->setTitle("Elenco Articoli");
		$table->setSource($this->ds_prodotti);
		//$table->setVisibleCols(array("barcode", "codice", "descrizione","prezzoven","codiva"));
		$table->setVisibleCols(array("barcode", "descrizione"));
		$this->intercept($table->rows, "afterClick", "seleziona_click");


		//$table->cols->barcode->setWidth(160);
		//$table->cols->descrizione->setWidth(200);
		
		$table->showNavigationBar();
	    $table->navigation_bar->buttons->button_go->setVisible(false);
	    $table->navigation_bar->buttons->field_num_page->setVisible(false);


		$this->build("p4a_db_source", "ds_eti");
		$this->ds_eti->setTable($p4a->e3g_prefix."etichette");
		$this->ds_eti->setSelect($p4a->e3g_prefix."etichette.idtable,".$p4a->e3g_prefix."etichette.idanag, ".$p4a->e3g_prefix."etichette.barcode,".$p4a->e3g_prefix."articoli.descrizione,".$p4a->e3g_prefix."articoli.codice");
		$this->ds_eti->setWhere($p4a->e3g_prefix."etichette.idanag = ".$p4a->e3g_utente_idanag);
		
		$this->ds_eti->setPk("idtable");
		$this->ds_eti->addJoin($p4a->e3g_prefix."articoli",$p4a->e3g_prefix."articoli.barcode = ".$p4a->e3g_prefix."etichette.barcode");
		$this->ds_eti->addOrder("descrizione");
		$this->ds_eti->load();
		$this->ds_eti->firstRow();

		// Table Etichette
		$tabeti =& $this->build("p4a_table", "tabeti");
		$coda_stp = $db->queryOne("SELECT COUNT( idtable) FROM " . $p4a->e3g_prefix . "etichette WHERE ".$p4a->e3g_prefix."etichette.idanag = ".$p4a->e3g_utente_idanag );
		$tabeti->setTitle("Coda di stampa ( ".$coda_stp." etichette)");
		$tabeti->setWidth(350);
		$tabeti->setSource($this->ds_eti);
		$tabeti->setVisibleCols(array("barcode", "descrizione"));
		

		//$tabeti->cols->barcode->setWidth(160);
		//$tabeti->cols->descrizione->setWidth(200);
		$tabeti->showNavigationBar();
	    $tabeti->navigation_bar->buttons->button_go->setVisible(false);
	    $tabeti->navigation_bar->buttons->field_num_page->setVisible(false);




		//Altri db_source
		//---------------------------------------------------------
		// tipo Merce
		$this->build("p4a_db_source", "ds_tipo");
		$this->ds_tipo->setTable($p4a->e3g_prefix."tipiarticoli");
		$this->ds_tipo->setPk("codice");		
		$this->ds_tipo->load();


		// codice iva
		$this->build("p4a_db_source", "ds_iva");
		$this->ds_iva->setTable($p4a->e3g_prefix."aliquoteiva");
		$this->ds_iva->setPk("codice");
		$this->ds_iva->load();

		// Table
		$tab_prodotti =& $this->build("p4a_table", "tab_prodotti");
 		$tab_prodotti->setWidth(730);

		$tab_prodotti->setSource($this->ds_prodotti);
		$tab_prodotti->setVisibleCols(array('barcode', 'codice', 'descrizione', 'prezzoven', 'codiva', 'tipoarticolo'));
		$tab_prodotti->cols->prezzoven->setLabel('prezzo Vendita');
		$tab_prodotti->cols->tipoarticolo->setSource($this->ds_tipo);
		$tab_prodotti->cols->codiva->setLabel('iva');
		$tab_prodotti->cols->codiva->setSource($this->ds_iva);
		$tab_prodotti->cols->codiva->setSourceValueField("codice");
		$tab_prodotti->cols->codiva->setSourceDescriptionField("iva");
		
		
		// Esegui Stampa
		$this->build("p4a_button", "stampa");
		$this->stampa->setLabel("Stampa Etichette");
		$this->stampa->setIcon("print");
		$this->stampa->addAction("onClick");
		$this->stampa->setWidth(200);
		$this->intercept($this->stampa, "onClick", "stampa_click");

		// Esegui Elimina
		$this->build("p4a_button", "elimina");
		$this->elimina->setLabel("Svuota coda di stampa");
		$this->elimina->setIcon("delete");
		$this->elimina->addAction("onClick");
		$this->elimina->setWidth(200);
		$this->intercept($this->elimina, "onClick", "elimina_click");
		$this->elimina->requireConfirmation( "onClick", "Confermi l'eliminazione della coda di stampa?" );

		// Esegui Elimina Riga
		$this->build("p4a_button", "elimina_riga");
		$this->elimina_riga->setLabel("Elimina etichetta selezionata");
		$this->elimina_riga->setIcon("edit_remove");
		$this->elimina_riga->addAction("onClick");
		$this->elimina_riga->setWidth(250);
		$this->intercept($this->elimina_riga, "onClick", "elimina_riga_click");
		$this->elimina_riga->requireConfirmation( "onClick", "Confermi l'eliminazione dell'etichetta selezionata?" );

		// Num Etichette da aggiungere 
		$this->build("p4a_field", "fld_num_eti");
		$this->fld_num_eti->setLabel("Num. Etichette");
		$this->fld_num_eti->setWidth(50);
		
		// Aggiungi Etichetta
		$this->build("p4a_button", "aggiungi");
		$this->aggiungi->setLabel("Aggiungi");
		$this->aggiungi->setIcon("edit_add");
		$this->aggiungi->addAction("onClick");
		$this->aggiungi->setWidth(200);
		$this->intercept($this->aggiungi, "onClick", "aggiungi_click");

        
		//Campo codice
		$codarticolo=& $this->build("p4a_field", "codarticolo");
		$codarticolo->setLabel('Codice Articolo');
		$codarticolo->setWidth("200");
		$codarticolo->addAction("onReturnPress");
		$this->intercept($codarticolo, "onReturnPress","carica_click");



		//Campo descrizione Articolo (per ricerca in anagrafica)
		$desarticolo=& $this->build("p4a_field", "desarticolo");
		$desarticolo->setLabel('descrizione');
		$desarticolo->setWidth("200");
		$desarticolo->addAction("onReturnPress");
		$this->intercept($desarticolo, "onReturnPress","cercades_click");


		// Esegui RiCerca per descrizione
		$this->build("p4a_button", "cercades");
		$this->cercades->setLabel("Cerca");
		$this->cercades->setIcon("find");
		$this->cercades->addAction("onClick");
		$this->intercept($this->cercades, "onClick", "cercades_click");


		// Annulla RiCerca per descrizione
		$this->build("p4a_button", "annullacerca");
		$this->annullacerca->setLabel("Annulla ricerca");
		$this->annullacerca->setIcon("cancel");
		$this->annullacerca->addAction("onClick");
		$this->intercept($this->annullacerca, "onClick", "annullacerca_click");

		//Setto il titolo della maschera
		$this->SetTitle('Stampa Etichette');


		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");




		$sh_but =& $this->build("p4a_sheet", "sh_but");
		$this->sh_but->setWidth(740);
    $this->sh_but->defineGrid(1, 3);
		$this->sh_but->anchor($this->stampa,1,1);
		$this->sh_but->anchor($this->elimina,1,2);
		//$this->sh_but->anchor($this->elimina_riga,1,3);



		//Fieldset con l'elenco dei campi
		$fset2=& $this->build("p4a_fieldset", "fset2");
		$fset2->setTitle("Ricerca");

 		$fset2->anchor($this->desarticolo);
 		$fset2->anchorLeft($this->cercades);
		$fset2->anchor($this->codarticolo);
		$fset2->anchorLeft($this->carica);
		$fset2->anchorLeft($this->annullacerca);

		$sh_tab_but =& $this->build("p4a_sheet", "sh_tab_but");
		$this->sh_tab_but->setWidth(740);
    $this->sh_tab_but->defineGrid(1, 3);
    $this->sh_tab_but->anchor($this->fld_num_eti,1,1);
		$this->sh_tab_but->anchor($this->aggiungi,1,2);
		$this->sh_tab_but->anchor($this->elimina_riga,1,3);
		
		
		$sh_tab =& $this->build("p4a_sheet", "sh_tab");
		$this->sh_tab->setWidth(740);
    $this->sh_tab->defineGrid(1, 2);
    $this->sh_tab->anchor($this->table,1,1);
		$this->sh_tab->anchor($this->tabeti,1,2);
		
		$fset2->setWidth(730);


		$this->build("p4a_quit_toolbar", "toolbar");

		// Frame
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(740);

		$frm->anchor($message);
		$frm->anchor($this->sh_but);
		$frm->anchor($fset2);
		$frm->anchor($this->sh_tab_but);
		$frm->anchor($this->sh_tab);
		

		e3g_scrivi_footer( $this, $frm );

  		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
	}

	function main()
	{
		parent::main();

	}

		
	function seleziona_click()
	{
		$db =& p4a_db::singleton();
		$p4a =& p4a::singleton();

		$this->aggiungi_una_etichetta();
    $coda_stp = $db->queryOne("SELECT COUNT( idtable) FROM " . $p4a->e3g_prefix . "etichette WHERE ".$p4a->e3g_prefix."etichette.idanag = ".$p4a->e3g_utente_idanag );
    $this->tabeti->setTitle("Coda di stampa ( ".$coda_stp." etichette)");
	
	}
	
	function aggiungi_una_etichetta()
	{
		$db =& p4a_db::singleton();
		$p4a =& p4a::singleton();
		
		// conto quante etichette ci sono in coda 
		$num_eti = $db->queryOne("SELECT COUNT( idtable) FROM " . $p4a->e3g_prefix . "etichette WHERE ".$p4a->e3g_prefix."etichette.idanag = ".$p4a->e3g_utente_idanag );
		
		if ($num_eti >= 40)
		{
			// sono al massimo delle etichette consentite (il caso >40 non dovrebbe mai verificarsi) 
			$this->message->setValue("Non puoi stampare piu' di 40 etichette per volta!");			
		}
		else
		{
			// prendo l'articolo e lo metto nella tabella etichette
			$maxid = $db->queryOne("SELECT MAX( idtable) FROM " . $p4a->e3g_prefix . "etichette" );
			if ( is_numeric($maxid) )
			{
				$maxid++;
			}		
			else 
			{
				$maxid = 1;	
			}
			
		
			$db->query("INSERT INTO " . $p4a->e3g_prefix . "etichette (idtable, idanag, barcode, descrizione, prezzoven, iva , stampato, codice) VALUES (".$maxid.",".$p4a->e3g_utente_idanag .
						", '".$this->table->data->fields->barcode->getNewValue()."', '".$this->table->data->fields->descrizione->getNewValue()."'" .
						", ".$this->table->data->fields->prezzoven->getNewValue().", '".$this->table->data->fields->codiva->getNewValue()."', 'N', '".$this->table->data->fields->codice->getNewValue()."'" .
						"  )");
												
		}		
	}
	function cercades_click()
	{
		$num_rows = 0 ;
		if ($this->desarticolo->getNewValue() == "" )
		{
    		if ($this->codarticolo->getNewValue() != "" )
    		{
    			$value = $this->codarticolo->getNewValue();
        		$this->data->setWhere("codice LIKE '%{$value}%' OR barcode LIKE '%{$value}%'");
        		$this->data->firstRow();
        		$num_rows = $this->data->getNumRows();
    		}
		}
		else
		{
    		$value = $this->desarticolo->getNewValue();
    		$this->data->setWhere("descrizione LIKE '%{$value}%'");
    		$this->data->firstRow();
    		$num_rows = $this->data->getNumRows();
		}
		
		if (!$num_rows) {
			$this->message->setValue("No results were found");
			$this->data->setWhere(null);
			$this->data->firstRow();
		}


		$this->desarticolo->setValue('');

	}

	function annullacerca_click()
	{
		$this->desarticolo->SetNewValue('');
		//$this->data->dropFilter('descrizione');
		$this->data->setWhere("1=1");
		$this->data->load();
	}

	function aggiungi_click()
	{
		$db =& p4a_db::singleton();
		$p4a =& p4a::singleton();		

		if (is_numeric($this->fld_num_eti->getNewValue()))
		{
		    for ($id = 1; $id <= $this->fld_num_eti->getNewValue(); $id++) {
				$this->aggiungi_una_etichetta();
		    }
		}	
    $coda_stp = $db->queryOne("SELECT COUNT( idtable) FROM " . $p4a->e3g_prefix . "etichette WHERE ".$p4a->e3g_prefix."etichette.idanag = ".$p4a->e3g_utente_idanag );
    $this->tabeti->setTitle("Coda di stampa ( ".$coda_stp." etichette)");

	}
	
	function elimina_riga_click()
	{
		$db =& p4a_db::singleton();
		$p4a =& p4a::singleton();		

		$db->query("DELETE FROM " . $p4a->e3g_prefix . "etichette WHERE ".$p4a->e3g_prefix."etichette.idanag = ".$p4a->e3g_utente_idanag." AND idtable='".$this->tabeti->data->fields->idtable->getNewValue()."'");
		
		$coda_stp = $db->queryOne("SELECT COUNT( idtable) FROM " . $p4a->e3g_prefix . "etichette WHERE ".$p4a->e3g_prefix."etichette.idanag = ".$p4a->e3g_utente_idanag );
		$this->tabeti->setTitle("Coda di stampa ( ".$coda_stp." etichette)");

		
	}
	function elimina_click()
	{
		$db =& p4a_db::singleton();
		$p4a =& p4a::singleton();		
		$db->query("DELETE FROM " . $p4a->e3g_prefix . "etichette WHERE ".$p4a->e3g_prefix."etichette.idanag = ".$p4a->e3g_utente_idanag);
		
		$coda_stp = $db->queryOne("SELECT COUNT( idtable) FROM " . $p4a->e3g_prefix . "etichette WHERE ".$p4a->e3g_prefix."etichette.idanag = ".$p4a->e3g_utente_idanag );
		$this->tabeti->setTitle("Coda di stampa ( ".$coda_stp." etichette)");
		
	}
	
	function stampa_click()
	{
		$db =& p4a_db::singleton();
		$p4a =& p4a::singleton();
		
		
		
		require_once(dirname(__FILE__) . "/../config.php");
		require_once( P4A_ROOT_DIR . "/p4a/include.php");
		require_once(dirname(__FILE__) . "/../libraries/Image/Barcode.php");

		$num     = '800000011111';
		$type    = 'ean13';
		$imgtype = 'png';

		$numcols = 4;
		$numrows = 10;
		$ystart = 0;
		$xstart = 175;

		$tot_righe = $this->tabeti->data->getNumRows();
		$riga = 1;
		$this->tabeti->data->firstRow();
    	
    	$Array = array();	
		while($riga <= $tot_righe)
		{
			$codice = $this->tabeti->data->fields->codice->getNewValue();
			$descrizione = $this->tabeti->data->fields->descrizione->getNewValue();
			$prezzo = $db->queryOne("SELECT prezzoven FROM ".$p4a->e3g_prefix."articoli WHERE codice='".$this->tabeti->data->fields->codice->getNewValue()."'");
			$iva = $db->queryOne("SELECT codiva FROM ".$p4a->e3g_prefix."articoli WHERE codice='".$this->tabeti->data->fields->codice->getNewValue()."'");
    		$paese = $db->queryOne("SELECT paese FROM ".$p4a->e3g_prefix."articoli WHERE codice='".$this->tabeti->data->fields->codice->getNewValue()."'");                         
			$arr[] = $this->tabeti->data->fields->barcode->getNewValue();
			
			$Array[$riga]=array("codice" => $codice." " , "descrizione" => substr($descrizione, 0, 30)." ", "prezzo" => $prezzo." ", "iva" => $iva." ", "paese" => $paese." ");		
			$Array[$riga]=array("codice" => $codice." " , "descrizione" => substr($descrizione, 0, 30)." ", "prezzo" => $prezzo." ", "iva" => $iva." ", "paese" => $paese." ");					
			
			$this->tabeti->data->nextRow();
			$riga++;
		}		
				
    	//print_r($Array);
    	//echo "<br>";
    	//print_r($arr);
    	//die; 
    	 
    	Image_Barcode::draw($num, $type, $imgtype, $numcols , $numrows , $ystart , $xstart, $arr, $Array, "barcode.png");
		
	}	
	
}
?>