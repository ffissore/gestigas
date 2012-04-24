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


require_once( dirname(__FILE__) . '/../libraries/e3g_utils.php' );
require_once( dirname(__FILE__) . '/../config.php' );


class cassa_eg extends P4A_Mask
{
	function cassa_eg()
	{
		// MASCHERA CASSA PER EQUOGEST
	
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		//articoli per fornitore
		//SELECT codarticolo, articoli.descrizione as DesArticolo, anagrafiche.descrizione as Fornitore FROM ".$p4a->e3g_prefix."(carrello INNER JOIN articoli ON articoli.codice =carrello.codarticolo) INNER JOIN anagrafiche ON articoli.centrale=anagrafiche.codice
		
		$this->build("p4a_image", "cesto");
		$this->cesto->setValue('images/cesto.gif');

		$this->build("p4a_button", "schedaarticolo");
		$this->schedaarticolo->setLabel("SCHEDA PRODOTTO");
		$this->schedaarticolo->addAction("onClick");
		$this->intercept($this->schedaarticolo, "onClick", "schedaarticolo_click");

		$this->build("p4a_button", "esporta_listino");
		$this->esporta_listino->setLabel("STAMPA LISTINO");
		$this->esporta_listino->addAction("onClick");
		$this->intercept($this->esporta_listino, "onClick", "esporta_listino_click");
				
		// FINESTRELLA RICERCA DETTAGLIATA DESCRIZIONE
		$this->build("p4a_button", "ricercadett");
		$this->ricercadett->setLabel("Ricerca");
		$this->ricercadett->addAction("onClick");
		$this->intercept($this->ricercadett, "onClick", "ricercadett_click");

		// FINESTRELLA RICERCA DETTAGLIATA ALTRI PARAMETRI
		$this->build("p4a_button", "ricercadett0");
		$this->ricercadett0->setLabel("Ricerca");
		$this->ricercadett0->addAction("onClick");
		$this->intercept($this->ricercadett0, "onClick", "ricercadett0_click");

		$this->build("p4a_button", "ricercatutti");
		$this->ricercatutti->setLabel("Tutti i Prodotti");
		$this->ricercatutti->addAction("onClick");
		$this->intercept($this->ricercatutti, "onClick", "ricercatutti_click");

		
		$this->build("p4a_label", "lbldesarticolo");
		$this->lbldesarticolo->setValue(".....");
		
		$this->build("p4a_label", "lbldescarrello");
		$this->lbldescarrello->setValue(".....");
		$this->lbldescarrello->setWidth('400');

		
		$this->build("p4a_db_source", "ds_tipo");
		$this->ds_tipo->setTable($p4a->e3g_prefix."tipiarticoli");
		$this->ds_tipo->setPk("codice");		
		$this->ds_tipo->addOrder("codice");		
		$this->ds_tipo->load();
		$this->ds_tipo->firstRow();
		
		$this->build("p4a_db_source", "ds_forn");
		$this->ds_forn->setTable($p4a->e3g_prefix."anagrafiche");
		$this->ds_forn->setPk("codice");		
		$this->ds_forn->setWhere("tipocfa='F'");
		// filtro per referenti GESTI.GAS
		// MENU GESTI.GAS
		// il codice Fornitore 00 è un codice Fittizio utilizzato per quando i combo come impostazione Default
		//switch (TRUE) {
		//	case $p4a->e3g_utente_tipo == "R" :
		//		echo "a";
		//		$this->ds_forn->setWhere("(tipocfa='F' AND ".str_replace("#CAMPOCODICE#", "codice", $p4a->e3g_where_referente).") OR codice='00'");
		//		break;
		//	default:
		//		echo "b";
		//		$this->ds_forn->setWhere("tipocfa='F' OR codice='00'");				
		//		break;
		//}

		$this->ds_forn->addOrder("codice");		
		$this->ds_forn->load();
		$this->ds_forn->firstRow();
		
		$fld_cod=& $this->build("p4a_field", "fld_cod");
		$fld_cod->setLabel('Cod. Utente');
		$fld_cod->label->setWidth(100);
		$fld_cod->setWidth("50");

		
		$fld_des=& $this->build("p4a_field", "fld_des");
		$fld_des->setLabel('descrizione');
		$fld_des->label->setWidth(100);
		$fld_des->setWidth("100");
		
		$fld_tipo=& $this->build("p4a_field", "fld_tipo");
		$fld_tipo->setLabel('tipo');
		$fld_tipo->label->setWidth(100);
		$fld_tipo->setType("select");
		$fld_tipo->setSource($this->ds_tipo);
		$fld_tipo->setSourceValueField("codice");
		$fld_tipo->setSourceDescriptionField("descrizione");
		$fld_tipo->addAction("OnChange");
		$this->intercept($this->fld_tipo, "onChange","seleztipo_click");		
		$fld_tipo->setWidth("100");
		
		$fld_forn=& $this->build("p4a_field", "fld_forn");
		$fld_forn->setLabel('Fornitore');
		$fld_forn->label->setWidth(100);
		$fld_forn->setType("select");
		$fld_forn->setSource($this->ds_forn);
		$fld_forn->setSourceValueField("codice");
		$fld_forn->setSourceDescriptionField("descrizione");
		$fld_forn->addAction("OnChange");
		$this->intercept($this->fld_forn, "onChange","selezforn_click");		
		$fld_forn->setWidth("100");

		$this->build("p4a_db_source", "ds_cat");
		$this->ds_cat->setTable($p4a->e3g_prefix."catmerceologica");
		$this->ds_cat->setWhere("tipo='".$this->ds_tipo->fields->codice->getNewValue()."'");		
		$this->ds_cat->setPk("codice");		
		$this->ds_cat->addOrder("codice");		
		$this->ds_cat->load();
		
		$fld_cat=& $this->build("p4a_field", "fld_cat");
		$fld_cat->setLabel('Cat. Merceol.');
		$fld_cat->label->setWidth(100);
		$fld_cat->setType("select");
		$fld_cat->setSource($this->ds_cat);
		$fld_cat->setSourceValueField("codice");
		$fld_cat->setSourceDescriptionField("descrizione");
		$fld_cat->setWidth("100");
		
		$this->build("p4a_db_source", "ds_prodotti");
		$this->ds_prodotti->setTable($p4a->e3g_prefix."articoli");
		$this->ds_prodotti->setPk("idarticolo");
		//$this->ds_prodotti->setPageLimit(8);
		$this->ds_prodotti->addOrder("descrizione");
		$this->ds_prodotti->load();
		$this->ds_prodotti->firstRow();
				
		$this->lbldesarticolo->setValue($this->ds_prodotti->fields->descrizione->getNewValue());

		// Table
		$tab_prodotti =& $this->build("p4a_table", "tab_prodotti");
 		$tab_prodotti->setWidth(400);
		//$tab_prodotti->setTitle("Elenco Tutti Prodotti");
		$tab_prodotti->setSource($this->ds_prodotti);
		$tab_prodotti->setVisibleCols(array('centrale', 'descrizione','prezzoven'));
		$this->intercept($tab_prodotti->rows, "afterClick", "seleziona_click");
		
		$tab_prodotti->hideHeaders(); 
		//$tab_prodotti->hideNavigationBar(); 
		
 
		
		// FINE RINESTRELLA RICERCA DETTAGLIATA
		
		
		//Campo per la ricerca
		$fld_ricerca=& $this->build("p4a_field", "fld_ricerca");
		$fld_ricerca->setLabel('Num. Docum.');
		$fld_ricerca->label->setWidth(50);
		$fld_ricerca->setWidth("150");
		$fld_ricerca->addAction('onReturnPress');
		$this->intercept($this->fld_ricerca,'onReturnPress','ricerca_click');

		//$this->ds_prodotti->firstRow();
		//$this->fld_ricerca->setNewValue($this->ds_prodotti->fields->codice->getNewValue());


		$qta=& $this->build("p4a_field", "qta");
		//$qta->setLabel('Q.ta');
		$qta->setLabel('Aggiungi');
		$qta->setWidth("30");
		$qta->label->setWidth(50);
		
		
		$qtacons=& $this->build("p4a_field", "qtacons");
		$qtacons->setLabel('Q.ta cons.');
		$qtacons->setWidth("50");
		
		$sconto=& $this->build("p4a_field", "sconto");
		$sconto->setLabel('Sconto');
		$sconto->setWidth("30");
		$sconto->label->setWidth(50);
		
		
		// Equogest 
		$this->setTitle('Vendita al Dettaglio');
	
		
		
		//Button per la ricerca
		$this->build("p4a_button", "button_ricerca");
		//$this->button_ricerca->setLabel("Aggiungi");
		$this->button_ricerca->setIcon( "notes" );
		$this->button_ricerca->setWidth(50);
		
		$this->button_ricerca->addAction("onClick");
		$this->intercept($this->button_ricerca, "onClick", "ricerca_click");
		
		
		//Button per incrementare la qta
		$this->build("p4a_button", "piuqta");
		//$this->piuqta->setLabel("+ qta");
		$this->piuqta->setIcon( "edit_add" );
		$this->piuqta->setWidth(50);
		$this->piuqta->addAction("onClick");
		$this->intercept($this->piuqta, "onClick", "piuqta_click");

		//Button per diminuire la qta
		$this->build("p4a_button", "menoqta");
		//$this->menoqta->setLabel("- qta");
		$this->menoqta->setIcon( "edit_remove" );
		$this->menoqta->setWidth(50);
		$this->menoqta->addAction("onClick");
		$this->intercept($this->menoqta, "onClick", "menoqta_click");


		//Sorgente dati della maschera
		$this->build("p4a_db_source", "ds_carrello");
		$this->ds_carrello->setTable($p4a->e3g_prefix."carrello");
		$this->ds_carrello->setPk("idriga");
		$this->ds_carrello->setPageLimit(20);
		$this->ds_carrello->setWhere("codutente='$p4a->e3g_utente_codice'");			


		$this->ds_carrello->load();

		
		//$this->ds_carrello->fields->idriga->setSequence("carrello");

		$this->setSource($this->ds_carrello);
		$this->ds_carrello->firstRow();

		$this->lbldescarrello->setValue($this->ds_carrello->fields->descrizione->getNewValue());
		
		// Fields properties
		$fields =& $this->fields;

		// Campi Obbligatori Fields
	    $this->mf = array("idriga");
		foreach($this->mf as $mf){
			$fields->$mf->label->setFontWeight("bold");
		}

	

		//Tabella riepilogativa del carrello
		$tab_carrello =& $this->build("p4a_table", "tab_carrello");
		$tab_carrello->setWidth(450);
		$tab_carrello->setSource($this->ds_carrello);
		$tab_carrello->setTitle("Totale = 0 euro");
		$this->intercept($tab_carrello->rows, "afterClick", "tabcarrello_click");

		//Nascondo le colonne che non mi servono
		$this->tab_carrello->cols->idriga->setInvisible();
		$this->tab_carrello->cols->data->setInvisible();
		$this->tab_carrello->cols->username->setInvisible();
		$this->tab_carrello->cols->codcaumov->setInvisible();
		$this->tab_carrello->cols->carscar->setInvisible();
		$this->tab_carrello->cols->idsessione->setInvisible();
		$this->tab_carrello->cols->codiva->setInvisible();
		$this->tab_carrello->cols->prezzoven->setInvisible();
		$this->tab_carrello->cols->codarticolo->setInvisible();

		$totale = $db->queryOne("SELECT SUM(prezzoven * qta) as importo FROM ".$p4a->e3g_prefix."carrello WHERE codutente='".$p4a->e3g_utente_codice."' ");
		$tab_carrello->setTitle("Totale : ".$totale." euro");
		
		
		
		$this->build("p4a_db_source", "ds_anag");
		$this->ds_anag->setTable($p4a->e3g_prefix."anagrafiche");
		$this->ds_anag->setPk("codice");		
		$this->ds_anag->setWhere("tipocfa='F'");		
		$this->ds_anag->load();
		
		
		$this->build("p4a_db_source", "ds_anagc");
		$this->ds_anagc->setTable($p4a->e3g_prefix."anagrafiche");
		$this->ds_anagc->setPk("codice");		
		$this->ds_anagc->setWhere("tipocfa='C'");		
		$this->ds_anagc->load();
		
		//$this->tab_carrello->cols->codarticolo->setLabel('codice Art.');
		//$this->tab_carrello->cols->prezzoven->setLabel('prezzo');
		//$this->tab_carrello->cols->codiva->setLabel('iva');
		
		
		$this->tab_carrello->cols->codfornitore->setLabel('Fornitore');
		$this->tab_carrello->cols->codfornitore->setSource($this->ds_anag);
		$this->tab_carrello->cols->codfornitore->setSourceValueField("codice");
		$this->tab_carrello->cols->codfornitore->setSourceDescriptionField("descrizione");
		
		$this->tab_carrello->cols->codutente->setLabel('Utente');		
		$this->tab_carrello->cols->codutente->setSource($this->ds_anagc);
		$this->tab_carrello->cols->codutente->setSourceValueField("codice");
		$this->tab_carrello->cols->codutente->setSourceDescriptionField("descrizione");
	
		
		//$tab_carrello->setVisibleCols(array("codarticolo", "descrizione","qta","prezzoven","codiva","codutente","codfornitore"));
		//$tab_carrello->setVisibleCols(array("descrizione","qta","prezzoven","codiva","codfornitore"));
		$tab_carrello->setVisibleCols(array("descrizione","qta","prezzoven","codiva"));
		
		$this->tab_carrello->cols->descrizione->setWidth(240);
		$this->tab_carrello->cols->qta->setWidth(60);
		$this->tab_carrello->cols->prezzoven->setWidth(40);
		$this->tab_carrello->cols->codiva->setWidth(40);
		$this->tab_carrello->cols->prezzoven->setLabel("Prezzo");
		$this->tab_carrello->cols->codiva->setLabel("Iva");
		
		$tab_carrello->showNavigationBar();
		

		//Toolbar
		$this->build("p4a_standard_toolbar", "toolbar");
		$this->toolbar->setMask($this);
		$this->toolbar->buttons->new->setInvisible();
		$this->toolbar->buttons->save->setInvisible();
		$this->toolbar->buttons->delete->setInvisible();
		$this->toolbar->buttons->cancel->setInvisible();
		
		$this->toolbar->buttons->next->setInvisible();
		$this->toolbar->buttons->prev->setInvisible();
		$this->toolbar->buttons->first->setInvisible();
		$this->toolbar->buttons->last->setInvisible();
		$this->toolbar->buttons->print->setInvisible();
		


		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("500");



		//Button svuota carrello
		$this->build("p4a_button", "b_svuota_carrello");
		$this->b_svuota_carrello->setLabel("Svuota Carrello");
		$this->b_svuota_carrello->addAction("onClick");
		$this->b_svuota_carrello->setIcon( "cancel" );

		$this->intercept($this->b_svuota_carrello, "onClick", "svuota_carrello");
		$this->b_svuota_carrello->requireConfirmation( "onClick", "Confermi l'eliminazione di tutti gli articoli dal carrello?" );


		$this->build("p4a_button", "b_elimina_riga");
		$this->b_elimina_riga->setLabel("Elimina Riga");
		$this->b_elimina_riga->setIcon( "delete" );
		$this->b_elimina_riga->addAction("onClick");
		$this->intercept($this->b_elimina_riga, "onClick", "elimina_riga");


		$this->build("p4a_button", "updrow");
		$this->updrow->setLabel("Aggiorna");
		$this->updrow->addAction("onClick");
		$this->intercept($this->updrow, "onClick", "updrow_click");

		//Button svuota carrello
		$this->build("p4a_button", "conferma");
		$this->conferma->setLabel("Conferma");
		$this->conferma->setIcon( "save" );
		$this->conferma->addAction("onClick");
		$this->intercept($this->conferma, "onClick", "conferma_scontrino");
		
		
		if ($p4a->e3g_utente_codice != "")
		{
    		//LABEL UTENTE
    		$this->build("p4a_label", "utente");
			$this->utente->setValue($db->queryOne("SELECT descrizione FROM ".$p4a->e3g_prefix."anagrafiche WHERE codice='$p4a->e3g_utente_codice'"));
			$this->utente->setWidth("200");
		}
		
		$this->setFocus($this->fld_ricerca);

/////////////////////////////////////////////////



		//$this->fld_ricerca->setValue('');
		$this->fld_ricerca->setLabel('Articolo');
   		$this->b_svuota_carrello->setValue('Annulla Scontrino');
   		$this->conferma->setValue('Conferma Scontrino');
		

		if ($this->qta->getNewValue()==''){
			$this->qta->setValue('1');
		}


		$this->sconto->setValue('0');
		//$this->reload_row();

		$sh_main =& $this->build("p4a_sheet", "sh_main");
        $this->sh_main->defineGrid(2, 2);
        $this->sh_main->setWidth(750);
		
		$sh_testa =& $this->build("p4a_sheet", "sh_testa");
        //$this->sh_testa->defineGrid(10, 6);
        $this->sh_testa->defineGrid(7, 3);
        $this->sh_testa->setWidth(300);
		//$this->sh_testa->setStyleProperty('border', '1px solid #C6D3DE');
		
               
		$line = str_pad("", 110, "-", STR_PAD_LEFT);
		
		//Dispongo gli oggetti
		$this->sh_testa->grid[1][1]->setProperty('width','350');
		$this->sh_testa->grid[2][1]->setProperty('width','350');
		$this->sh_testa->grid[3][1]->setProperty('width','350');
		$this->sh_testa->grid[1][2]->setProperty('width','350');
		$this->sh_testa->grid[2][2]->setProperty('width','350');
		$this->sh_testa->grid[3][2]->setProperty('width','350');
		

		$this->sh_testa->anchor($this->qta,1,1);
		$this->sh_testa->anchor($this->piuqta,1,2);
		$this->sh_testa->anchor($this->menoqta,1,3);
		$this->sh_testa->anchor($this->sconto,2,1);
		//$this->sh_testa->anchor($this->menoqta,2,2);
		$this->sh_testa->anchor($this->fld_ricerca,3,1,1,2);
		$this->sh_testa->anchor($this->button_ricerca,3,3);
		//$this->sh_testa->anchor($this->button_ricerca,3,2);
		
		$this->sh_testa->anchor($this->b_elimina_riga,5,1,1,3);
		$this->sh_testa->anchor($this->b_svuota_carrello,6,1,1,3);
		$this->sh_testa->anchor($this->conferma,7,1,1,3);
		

		
		
		//$fset2=& $this->build("p4a_fieldset", "fset2");
		//$fset2->setWidth(350);
		//$fset2->setTitle("");
		//$fset2->anchor($this->b_elimina_riga);
		//$fset2->anchor($this->b_svuota_carrello);
		//$fset2->anchor($this->conferma);
		
		//$fset3=& $this->build("p4a_fieldset", "frame3");
		//$fset3->setWidth(700);
		//$fset3->setTitle("");
		
		
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(700);
		$frm->anchor($this->message);
		
		$this->sh_main->anchor($this->sh_testa,1,1);
		$this->sh_main->anchor($this->tab_carrello,1,2,2,1);
		//$this->sh_main->anchor($fset2,2,1);
		
	
		//$frm->anchor($this->sh_testa);
		//$frm->anchor($this->tab_carrello);
		//$frm->anchor($fset2);
		$frm->anchor($this->sh_main);
		//$frm->anchor($fset3);
		
		e3g_scrivi_footer( $this, $frm );

  		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
		
		if ( E3G_TIPO_GESTIONE == 'G' )
		{
		}
		else
		{
			parent::newRow();
		}
					
	}



	function main()
	{

		parent::main();

		foreach($this->mf as $mf){
			$this->fields->$mf->unsetStyleProperty("border");
		}

	}

    
	function ricerca_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
        $cod_ricerca = $this->fld_ricerca->getNewValue();
    
        $numrighe = $db->queryOne("SELECT COUNT(*) FROM " . $p4a->e3g_prefix . "articoli WHERE codice ='".$cod_ricerca."' OR barcode ='".$cod_ricerca."'");
        
		if ($cod_ricerca != '' && $numrighe >0)
		{
			
			$query = "SELECT idarticolo,codice,descrizione,prezzoven,codiva,centrale  FROM ".$p4a->e3g_prefix."articoli WHERE barcode='$cod_ricerca' OR codice='$cod_ricerca'";
			$aProdotto = $db->queryRow($query);
    		
			$id_articolo = $aProdotto["idarticolo"];
			$codice = $aProdotto["codice"];
			$descrizione = $aProdotto["descrizione"];
			$fornitore = $aProdotto["centrale"];
			 
			$prezzoven =$aProdotto["prezzoven"];
			
			$codiva =$aProdotto["codiva"];
			$quantita = $this->qta->getNewValue();
			if (is_numeric($this->sconto->getNewValue()))
			{
				$valoresconto = doubleval($this->sconto->getNewValue());
			}
			else
			{
				$valoresconto = 0;
			}
			
			
			// Ricavo il codice Utente 
			if ($this->fld_cod->getNewValue() == "")
			{
				$codute = $p4a->e3g_utente_codice;
			}
			else 
			{
				$codute = $this->fld_cod->getNewValue();
			}
			
			if ($codice !=''){
				$rigaid = 0 ;
				
				// sono in EQUOGEST oppure non ho nessuna riga per questo utente
				parent::newRow();
			
				$this->fields->codarticolo->setNewValue($codice);
				$this->fields->descrizione->setNewValue($descrizione);
				$this->fields->prezzoven->setValue($prezzoven);
				
				
				$this->fields->qta->setValue($quantita);
				
				$this->fields->sconto->setValue($valoresconto);
				
				$this->fields->codiva->setNewValue($codiva);
				$this->fields->idsessione->setNewValue(session_id());
				
				// il responsabile può inserire il codice di un utente diverso dal suo
					
				$this->fields->codutente->setNewValue($codute);

				
				if ( E3G_TIPO_GESTIONE == 'G' )
				{
					$this->fields->stato->setNewValue("A");
					//$this->fields->codutente->setNewValue($p4a->e3g_utente_codice);        
					$this->fields->codfornitore->setNewValue($fornitore);
				}

				$ultimariga = $db->queryOne("SELECT idriga FROM ".$p4a->e3g_prefix."carrello ORDER BY idriga DESC");
				if ($ultimariga=='')
				{
					$ultimariga = 0;
				}

				$ultimariga++;
				
				$this->fields->idriga->setNewValue($ultimariga);
				// il Flag Carico/Scarico � sempre S perch� siamo in vendita alla cassa
				$this->fields->carscar->setNewValue("S");
				// Il codice della causale di movimento magazzino lo devo pescare dalla tabella Tipi Doc (al tipo documento vendita dettaglio)
				$this->fields->codcaumov->setNewValue($db->queryOne("SELECT dettaglio_causalemovmag FROM ".$p4a->e3g_prefix."azienda"));
				// Inserisco la data Odierna
				$this->fields->data->setNewValue(date ("y-m-d"));
				//Inserisco il codice articolo nel carrello

				parent::saveRow();
				
			}
			
			$totale = $db->queryOne("SELECT SUM(prezzoven * qta) as importo FROM ".$p4a->e3g_prefix."carrello WHERE codutente='".$p4a->e3g_utente_codice."' ");
			$this->tab_carrello->setTitle("Totale : ".$totale." euro");
			
		}
		else 
        {
			
    		$this->message->setValue("Articolo non trovato.");

        	
        }
		
		
		$this->qta->setValue(1);
		$this->fld_ricerca->setNewValue('');
		$this->setFocus($this->fld_ricerca);
		
		$this->qta->label->setBgcolor('#FFFFFF');
		$this->lbldesarticolo->setBgcolor('#FFFFFF');

	
		parent::newRow();

		
    	
	}

	function codice_premutoinvio()
	{
		//ricerca_click;
	}

	function svuota_carrello()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		while ($this->data->getNumRows())
		{
			//In questo caso io cancello semplicemente il record dal carrello
			$this->deleteRow();
		}
		
		$totale = $db->queryOne("SELECT SUM(prezzoven * qta) as importo FROM ".$p4a->e3g_prefix."carrello WHERE codutente='".$p4a->e3g_utente_codice."' ");
		$this->tab_carrello->setTitle("Totale : ".$totale." euro");
		
	}

	function elimina_riga()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		$this->deleteRow();
		
		
		$totale = $db->queryOne("SELECT SUM(prezzoven * qta) as importo FROM ".$p4a->e3g_prefix."carrello WHERE codutente='".$p4a->e3g_utente_codice."' ");		
		$this->tab_carrello->setTitle("Totale : ".$totale." euro");
		
	}
	

	function updrow_click()
	{
		$db =& p4a_db::singleton();
		
        $cod_ricerca = $this->fields->codarticolo->getnewValue();
    
			
		// CONTROLLO IL PERIODO PER GLI ORDINI 
		$fornitore = $db->queryOne("SELECT centrale  FROM ".$p4a->e3g_prefix."articoli WHERE barcode='$cod_ricerca' OR codice='$cod_ricerca'");				
		$numrighe = $db->queryOne("SELECT COUNT(*) FROM ".$p4a->e3g_prefix."fornitoreperiodo WHERE (fornitore='".$fornitore."' OR fornitore='ALL') AND datainizio<='".date("Y-m-d")."' AND datafine>='".date("Y-m-d")."'");
		$stagione = $db->queryOne("SELECT COUNT(*) FROM ".$p4a->e3g_prefix."articoloperiodo WHERE codice='$cod_ricerca' AND dalmese <= MONTH(NOW()) AND almese >= MONTH(NOW())");
		$articolostagionale = $db->queryOne("SELECT COUNT(*) FROM ".$p4a->e3g_prefix."articoloperiodo WHERE codice='$cod_ricerca' ");
									            
				
        $pzperconf = $db->queryOne("SELECT pzperconf FROM ".$p4a->e3g_prefix."articoli WHERE barcode='$cod_ricerca' OR codice='$cod_ricerca'");
			
		if ($pzperconf == 0 )
		{
		}
		else
		{
			if (($this->qta->getNewValue() % $pzperconf) == 0) 
			{
				// Confez. OK 
				$pzperconf = 0;
			}
            else 
            {
            	// Conf. Errata
				$tmp = $pzperconf * -1;
				$pzperconf = $tmp; 
			}
		}

		// Controllo la qta Minima impostata 
		$qtamin = $db->queryOne("SELECT qtaminperfamiglia FROM ".$p4a->e3g_prefix."articoli WHERE barcode='$cod_ricerca' OR codice='$cod_ricerca'");
		if ($qtamin == 0 )
		{
		}
		else
		{
			if (($this->qta->getNewValue() < $qtamin) ) 
			{
            	// Conf. Errata qta < qta min ordinabile
			}
            else 
            {
				// Confez. OK 
				$qtamin = 0;
			}
		}
			
 			
		if ($cod_ricerca != '' && $numrighe > 0 && $qtamin == 0 && $pzperconf == 0 && ($articolostagionale == 0 || ($articolostagionale >0 && $stagione>0)))
		{
			$this->saveRow();
		}
        else 
        {
            	if ($numrighe == 0 )
	            {
        			$this->message->setValue("Ordine NON CONSENTITO in questo periodo.");
    	    	}

            	if ( $qtamin != 0)
	            {
        			$this->message->setValue("La quantità MINIMA ordinabile è di " . $qtamin . " pezzi.");
    	    	}

				            	if ( $pzperconf != 0)
	            {
	            	$tmp = $pzperconf * -1;
        			$this->message->setValue("La quantità PER CONFEZIONE è di " . $tmp . " pezzi.");
    	    	}
    	    	
    	    	
    	    	if ($articolostagionale >0 && $stagione == 0)
    	    	{
        			$this->message->setValue("Prodotto FUORI STAGIONE.");
    	    	}

		}
		
		$this->fields->qta->label->setBgcolor('#FFFFFF');
		$this->lbldescarrello->setBgcolor('#FFFFFF');
				
		
	}
	
	
	function conferma_scontrino()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		// Db Source per il report 
		$this->build("p4a_db_source", "ds_report");
		$this->ds_report->setTable($p4a->e3g_prefix."carrello");
		$this->ds_report->setPk("idriga");
		if ( E3G_TIPO_GESTIONE == 'G' )
		{
			$this->ds_report->setWhere("");
		}
		else
		{
			$this->ds_report->setWhere("codutente='$p4a->e3g_utente_codice'");
		}
		$this->ds_report->load();
		
		// Source per la Tabella dei Totali per iva 
		$this->build("p4a_db_source", "ds_tot");
		$this->ds_tot->setTable($p4a->e3g_prefix."carrello"); 
 		$this->ds_tot->setSelect("codiva AS iva, SUM((prezzoven * qta) - (prezzoven * qta) * sconto/100)  AS Totale");
		if ( E3G_TIPO_GESTIONE == 'G' )
		{
			$this->ds_tot->setWhere("");
		}
		else
		{
			$this->ds_tot->setWhere("codutente='$p4a->e3g_utente_codice'");
							
		}

		//$this->ds_tot->setWhere("idsessione='".session_id()."'");
		$this->ds_tot->setGroup("codiva");
		$this->ds_tot->load();

	
 		// Calcolo i totali per categoria iva e registro i movimenti di magazzino
		$this->build("p4a_db_source", "ds_movmagr");
		$this->ds_movmagr->setTable($p4a->e3g_prefix."movmagr");
		$this->ds_movmagr->setPk("idriga");
		$this->ds_movmagr->load();				
		// ^^ Da eliminare dopo

		// Creo le righe del documento 
		$this->build("p4a_db_source", "ds_docr");
		$this->ds_docr->setTable($p4a->e3g_prefix."docr");
		$this->ds_docr->setPk("idriga");
		$this->ds_docr->load();				
		
		// Diciture totali pezzi e importo
		/*$pezzi = $db->queryOne("SELECT SUM(qta) as quantita  FROM ".$p4a->e3g_prefix."carrello WHERE codutente='".$p4a->e3g_utente_codice."'");		
		$totale = $db->queryOne("SELECT ROUND(SUM(qta*prezzoven),2) as importo   FROM ".$p4a->e3g_prefix."carrello WHERE codutente='".$p4a->e3g_utente_codice."'");		
		$articoli = $db->queryOne("SELECT DISTINCT COUNT(codarticolo) as articoli FROM ".$p4a->e3g_prefix."carrello WHERE codutente='".$p4a->e3g_utente_codice."'");		
		
		$arr_totali = array($articoli." articoli per un totale di ".$pezzi." pezzi ","Importo: ".$totale." euro"); 
		$this->as_totali =& $this->build('p4a_array_source','as_totali'); 
		$this->as_totali->load($arr_totali); 
		*/

		$this->build("p4a_db_source", "ds_pezzi");
		$this->ds_pezzi->setTable($p4a->e3g_prefix."carrello");
		$this->ds_pezzi->setSelect("CONCAT(SUM(qta), ' pezzi') as pezzi, CONCAT('Importo: ', ROUND(SUM(qta*prezzoven),2), ' euro') as importo");
		$this->ds_pezzi->load();				

		
		// Apro la maschera del Report Totale Scontrino
		$p4a->openMask('reportscontrino');
		
		
		
	}


	function menoqta_click(){

		$oldqta = $this->qta->getNewValue();
		if ($oldqta>0){
			$oldqta--;
		}
		$this->qta->setValue($oldqta);
	}

	function piuqta_click(){
		$oldqta = $this->qta->getNewValue();
		$oldqta++;
		$this->qta->setValue($oldqta);
	}

	function seleztipo_click(){
		
		$this->ds_cat->setWhere("tipo='".$this->fld_tipo->getNewValue()."' OR codice='000'");		
		$this->ds_cat->load();
		
	}

	function selezforn_click(){
		
	
		
	}
		
	
	function tabcarrello_click()
	{
		$this->lbldescarrello->setValue($this->tab_carrello->data->fields->descrizione->getNewValue());		
		$this->fields->qta->label->setBgcolor('#66FF66');
		$this->lbldescarrello->setBgcolor('#66FF66');
		
	}
	
	function seleziona_click()
	{
		$this->lbldesarticolo->setValue($this->tab_prodotti->data->fields->descrizione->getNewValue());
		$this->fld_ricerca->setNewValue($this->tab_prodotti->data->fields->codice->getNewValue());
		//$this->ricerca_click();
		
		$this->qta->label->setBgcolor('#66FF66');
		$this->lbldesarticolo->setBgcolor('#66FF66');
	}

		
	function ricercadett_click(){
		if ($this->fld_des->getNewValue() != "" )
		{	
			$strWhere = " descrizione LIKE '%".$this->fld_des->getNewValue()."%' AND ";
		}

				
		if (substr($strWhere, -4) == "AND ")
		{	
			$temp = substr($strWhere, 0, strlen($strWhere) - 4);
			$strWhere = $temp;
		}
		
		$this->ds_prodotti->setWhere($strWhere);
		$this->ds_prodotti->load();

		$this->ds_prodotti->firstRow();
		$this->lbldesarticolo->setValue($this->tab_prodotti->data->fields->descrizione->getNewValue());
		$this->fld_ricerca->setNewValue($this->tab_prodotti->data->fields->codice->getNewValue());
		
	}



	function esporta_listino_click()
	{		
		require("class.report.php");
		$pdf = new Creport('a4','portrait');
			 
		
		$arr["codice"] = "codice";
		$arr["descrizione"] = "descrizione";
		$arr["prezzoven"] = "prezzo";
		$arr["codiva"] = "iva";
		$arr["Fornitore"] = "Fornitore";
		
		
		$pdf->stampareport($this->ds_prodotti->getAll(), $arr, "Listino articoli","listino");

	}
	
	function schedaarticolo_click()
	{		
		$db =& p4a_db::singleton();
		
		// Apro la scheda articolo
		$this->build("p4a_db_source", "ds_art");
		$this->ds_art->setTable($p4a->e3g_prefix."articoli");
		$this->ds_art->setPk("codice");		
		$this->ds_art->setWhere("codice='".$this->fld_ricerca->getNewValue()."'");
		$this->ds_art->load();
		
		$codforn = $db->queryOne("SELECT centrale FROM ".$p4a->e3g_prefix."articoli WHERE codice='".$this->fld_ricerca->getNewValue()."'");	
		$this->build("p4a_db_source", "ds_pro");
		$this->ds_pro->setTable($p4a->e3g_prefix."anagrafiche");
		$this->ds_pro->setPk("codice");		
		$this->ds_pro->setWhere("tipocfa='F' AND codice='".$codforn."'");
		$this->ds_pro->load();

		
		
		require("class.report.php");
        $pdf = new Creport('a4','portrait');

        	
		
		$nomefile = "SCHEDA_".$this->fld_ricerca->getNewValue()."_".str_replace("/", "", $datadoc).".pdf";	
		$pdf->schedaarticolo($this->ds_art->getAll(),$this->ds_pro->getAll(),$nomefile);
        

		
		
		
	}
	
		
	function ricercatutti_click()
	{		
		$this->ds_prodotti->setWhere("");
		$this->ds_prodotti->load();	

		$this->ds_prodotti->firstRow();
		$this->lbldesarticolo->setValue($this->tab_prodotti->data->fields->descrizione->getNewValue());
		$this->fld_ricerca->setNewValue($this->tab_prodotti->data->fields->codice->getNewValue());

	}

		
	function ricercadett0_click(){
		if ($this->fld_tipo->getNewValue() != "00" )
		{	
			$strWhere = " tipo='".$this->fld_tipo->getNewValue()."' AND ";
		}
		if ($this->fld_cat->getNewValue() != "000" )
		{	
			$strWhere = " catmerce='".$this->fld_cat->getNewValue()."' AND ";
		}
		
		if ($this->fld_forn->getNewValue() != "00" )
		{	
			$strWhere = " centrale = '".$this->fld_forn->getNewValue()."' ";
		}
				
		if (substr($strWhere, -4) == "AND ")
		{	
			$temp = substr($strWhere, 0, strlen($strWhere) - 4);
			$strWhere = $temp;
		}
		
		$this->ds_prodotti->setWhere($strWhere);
		$this->ds_prodotti->load();

		$this->ds_prodotti->firstRow();
		$this->lbldesarticolo->setValue($this->tab_prodotti->data->fields->descrizione->getNewValue());
		$this->fld_ricerca->setNewValue($this->tab_prodotti->data->fields->codice->getNewValue());
		
	}

	
	
	function tabella_click(){
		echo "CLICK";
		die; 
	}



}
?>