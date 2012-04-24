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


require_once( dirname(__FILE__) . '/../libraries/e3g_doc_routines.php' );


class gesdocumenti1	extends P4A_Mask
{
	// Variabile per definire il tipo Anagrafica Cliente / Fornitore
	var $codicetipodoc = '';
	var $codclifor = '';
	var $strdata = '';
	var $anno = '';	
	//var $numdoc = 0;
	var $iddoc = 0;
	
		
	function gesdocumenti1()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		$this->setTitle( "Gestione documenti" );

        // ------------------------------------------------ DB source principale
        $this->build( "p4a_db_source", "ds_doc" );
        $this->ds_doc->setSelect( "iddoc, data, numdocum, codtipodoc, codclifor, anno, idanag, data_ins, codtipopag, totdoc" );
        $this->ds_doc->setTable( $p4a->e3g_prefix . "doct" );
        $this->ds_doc->setWhere( "0 = 1" );  // Il vero where viene impostato da bu_filtro_click();
        $this->ds_doc->addOrder( "data", "DESC" );
        $this->ds_doc->addOrder( "iddoc", "DESC" );
        $this->ds_doc->setPk( "iddoc" );
        $this->ds_doc->setPageLimit( $p4a->e3g_utente_db_source_page_limit );
        $this->ds_doc->load();
        $this->ds_doc->firstRow();
        $this->setSource( $this->ds_doc );

        
        // ------------------------------------------------------------- Message
        $message =& $this->build("p4a_message", "message");
        $message->setWidth( 400 );


        // ------------------------------------------------------------- Toolbar
        $this->build("p4a_quit_toolbar", "toolbar");


        // ---------------------------------------------------- Filtro documenti

        // Combo tipi documento
        $this->build("p4a_db_source", "ds_tipodoc");
        $this->ds_tipodoc->setTable($p4a->e3g_prefix."doctipidoc");
        $this->ds_tipodoc->setPk("codice");
        $this->ds_tipodoc->load();
		
        $this->build("p4a_field", "fld_tipodoc");
        $this->fld_tipodoc->setLabel('Tipo doc.');
        $this->fld_tipodoc->label->setWidth(80);
        $this->fld_tipodoc->setWidth(200);
        $this->fld_tipodoc->setType('select');
        $this->fld_tipodoc->setSource($this->ds_tipodoc);
        $this->fld_tipodoc->setSourceValueField('codice');
        $this->fld_tipodoc->setSourceDescriptionField('descrizione');
        
        /* 
        Flag per la stampa delle Righe Invisibili
        e campo per la selezione dell'ordinamento 
        */
		
        $this->build( "p4a_field", "fld_tutte" );
        $this->fld_tutte->setType( "checkbox" );
        $this->fld_tutte->label->setWidth( 250 );
        $this->fld_tutte->setLabel( "Visualizza dettaglio articoli per utente" );
        
        $data_oggi = explode("-", date("Y-m-d"));
        $yea_d = $data_oggi[0];
        $mon_d = $data_oggi[1];
        $day_d = $data_oggi[2];
        $mk_data_oggi = mktime(0, 0, 0, $mon_d, $day_d, $yea_d, 0);
        $trenta_giorni = 60 * 60 * 24 * 30; //Calcolo 30 giorni in secondi 
        $un_mese_fa = date("d/m/Y",$mk_data_oggi - $trenta_giorni); 
   
        $this->build("p4a_field", "fld_filtro_dal");
        $this->fld_filtro_dal->setLabel('Dalla data');
        $this->fld_filtro_dal->label->setWidth(80);
        $this->fld_filtro_dal->setWidth(80);
        $this->fld_filtro_dal->setType("date");
        $this->fld_filtro_dal->setNewValue($un_mese_fa);
        
        $this->build("p4a_field", "fld_filtro_al");
        $this->fld_filtro_al->setLabel('alla data');
        $this->fld_filtro_al->label->setWidth(80);
        $this->fld_filtro_al->setWidth(80);
        $this->fld_filtro_al->setType("date");
        $this->fld_filtro_al->setNewValue(date("d/m/Y"));

        // Bottone Filtra 
        $this->build("p4a_button", "bu_filtro");
        $this->bu_filtro->setLabel("Filtra");
        $this->bu_filtro->setIcon("find");
        $this->bu_filtro->setWidth( 100 );
        $this->bu_filtro->setSize( 16 );
        $this->bu_filtro->addAction("onClick");
        $this->intercept($this->bu_filtro, "onClick", "bu_filtro_click");
        
        // Annulla Filtro 
        $this->build("p4a_button", "bu_annulla_filtro");
        $this->bu_annulla_filtro->setLabel("Mostra tutto");
        $this->bu_annulla_filtro->setIcon("cancel");
        $this->bu_annulla_filtro->setWidth( 100 );
        $this->bu_annulla_filtro->setSize( 16 );
        $this->bu_annulla_filtro->addAction("onClick");
        $this->intercept($this->bu_annulla_filtro, "onClick", "bu_annulla_filtro_click");
        

        // ----------------------------------------------------- Altri DB source 

		// DB Source per visualizzare il fornitore
		$this->build("p4a_db_source", "ds_anag_for");
		$this->ds_anag_for->setTable($p4a->e3g_prefix."anagrafiche");
		$this->ds_anag_for->setPk("codice");
		$this->ds_anag_for->load();
		
		// DB Source per visualizzare chi ha creato il documento
		$this->build("p4a_db_source", "ds_anag_ute");
		$this->ds_anag_ute->setTable($p4a->e3g_prefix."anagrafiche");
		$this->ds_anag_ute->setPk("idanag");
		$this->ds_anag_ute->load();

        // TIPO DOCUMENTO       
        $this->build("p4a_db_source", "ds_tipo");
        $this->ds_tipo->setTable($p4a->e3g_prefix."doctipidoc");
        $this->ds_tipo->addOrder("descrizione");
        $this->ds_tipo->setPk("codice");
        $this->ds_tipo->load();

        // Cliente / Fornitore Anagrafica Documento
        $coddocumento = $db->queryOne("SELECT codtipodoc FROM ".$p4a->e3g_prefix."doct ORDER BY data DESC, iddoc DESC");        
        $tipocf = $db->queryOne("SELECT tipoanagrafica FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$coddocumento."'");
        $this->build("p4a_db_source", "ds_anag");
        $this->ds_anag->setTable( $p4a->e3g_prefix."anagrafiche" );
        $this->ds_anag->setWhere( "tipocfa = '" . $tipocf . "'" );
        $this->ds_anag->setPk("idanag");
        $this->ds_anag->load();

				
		// -------------------------------------------- Tabella elenco documenti
		$tbl_doct = & $this->build("p4a_table", "tbl_doct");
		$tbl_doct->setWidth( E3G_TABLE_WIDTH );
		$tbl_doct->setSource($this->ds_doc);
		$this->intercept($tbl_doct->rows, "afterClick", "tbl_doct_click");

		$tbl_doct->setVisibleCols( array("data", "numdocum", "codtipodoc", "codclifor", "totdoc", "data_ins", "idanag") );
		$tbl_doct->showNavigationBar();

		$tbl_doct->cols->data->setLabel("Data doc.");
		$tbl_doct->cols->numdocum->setLabel("Num.");
		$tbl_doct->cols->codtipodoc->setLabel("Tipo");
        $tbl_doct->cols->codclifor->setLabel("Anagrafica");
        $tbl_doct->cols->totdoc->setLabel("Importo doc.");
		$tbl_doct->cols->data_ins->setLabel("Data creazione");
        $tbl_doct->cols->idanag->setLabel("Creato da");

		$tbl_doct->cols->data->setWidth( 75 );
		$tbl_doct->cols->numdocum->setWidth( 40 );
		$tbl_doct->cols->codtipodoc->setWidth( 140 );
//		$tbl_doct->cols->codclifor->setWidth();  per differenza
        $tbl_doct->cols->totdoc->setWidth( 60 );
		$tbl_doct->cols->data_ins->setWidth( 130 );
        $tbl_doct->cols->idanag->setWidth( 180 );


        $tbl_doct->cols->codtipodoc->setSource($this->ds_tipodoc);
        $tbl_doct->cols->codtipodoc->setSourceValueField("codice");
        $tbl_doct->cols->codtipodoc->setSourceDescriptionField("descrizione");

		$tbl_doct->cols->codclifor->setSource($this->ds_anag_for);
		$tbl_doct->cols->codclifor->setSourceValueField("codice");
		$tbl_doct->cols->codclifor->setSourceDescriptionField("descrizione");

		$tbl_doct->cols->idanag->setSource($this->ds_anag_ute);
		$tbl_doct->cols->idanag->setSourceValueField("idanag");
		$tbl_doct->cols->idanag->setSourceDescriptionField("descrizione");


        // --------------------------------------------------- Pulsantiera sotto				

        // Pulsante "Apri documento"
        $this->build("p4a_button", "bu_apri_doc");
        $this->bu_apri_doc->setLabel("Apri documento");
        $this->bu_apri_doc->setIcon("fileopen");
        $this->bu_apri_doc->setWidth(200);
        $this->bu_apri_doc->addAction("onClick");
        $this->intercept($this->bu_apri_doc, "onClick", "bu_apri_docClick");

        // Pulsante "Esporta PDF/Stampa" 
        $this->build("p4a_button", "bu_esporta_pdf");
        $this->bu_esporta_pdf->setLabel("Esporta come PDF...");
        $this->bu_esporta_pdf->setIcon("pdf");
        $this->bu_esporta_pdf->setWidth(200);
        $this->bu_esporta_pdf->addAction("onClick");
        if ( $p4a->e3g_azienda_tipo_documento == "PDF" ) {
            // per documenti PDF chiamo la routine di generazione PDF
            $this->intercept($this->bu_esporta_pdf, "onClick", "bu_esporta_pdfClick");
        }
        else {
            // per documenti ODT chiamo la routine di generazione HTML+OpenOffice
            $this->intercept($this->bu_esporta_pdf, "onClick", "bu_esporta_docClick");
        }

        // Pulsante "Esporta CSV" 
        $this->build("p4a_button", "bu_esporta_csv");
        $this->bu_esporta_csv->setLabel("Esporta foglio elettronico");
        $this->bu_esporta_csv->setIcon( "spreadsheet" );
        $this->bu_esporta_csv->setWidth(200);
        $this->bu_esporta_csv->addAction("onClick");
        $this->intercept($this->bu_esporta_csv, "onClick", "bu_esporta_csvClick");

        // Pulsante "Elimina"
        $this->build("p4a_button", "bu_elimina_doc");
        $this->bu_elimina_doc->setLabel("Elimina doc.");
        $this->bu_elimina_doc->setIcon("delete");
        $this->bu_elimina_doc->setWidth(200);
        $this->bu_elimina_doc->addAction("onClick");
        $this->intercept($this->bu_elimina_doc, "onClick", "bu_elimina_docClick");
        $this->bu_elimina_doc->requireConfirmation( "onClick", "Vuoi veramente eliminare il documento selezionato?" );

        // Pulsante "Num. Prossimo Doc." (solo Equogest) 
        $this->build("p4a_button", "bu_num_prox_doc");
        $this->bu_num_prox_doc->setLabel("Num. Prossimo Doc.");
        $this->bu_num_prox_doc->setIcon("find");
        $this->bu_num_prox_doc->setWidth(200);
        $this->bu_num_prox_doc->addAction("onClick");
        $this->intercept($this->bu_num_prox_doc, "onClick", "bu_num_prox_docClick");

        // Pulsante  "Nuovo doc." (solo Equogest)
        $this->build("p4a_button", "bu_nuovo_doc");
        $this->bu_nuovo_doc->setLabel("Nuovo Doc.");
        $this->bu_nuovo_doc->setIcon("new");
        $this->bu_nuovo_doc->setWidth(200);
        $this->bu_nuovo_doc->addAction("onClick");
        $this->intercept($this->bu_nuovo_doc, "onClick", "bu_nuovo_docClick");


        // -------------------------- Field sotto la tabella (solo per Equogest)

		$this->build("p4a_field", "fld_cod_tipo_doc");
		$this->fld_cod_tipo_doc->setLabel("Tipo documento");
		$this->fld_cod_tipo_doc->setWidth(150);
		$this->fld_cod_tipo_doc->setType('select');
		$this->fld_cod_tipo_doc->setSourceValueField('codice');
		$this->fld_cod_tipo_doc->setSourceDescriptionField('descrizione');
		$this->fld_cod_tipo_doc->setSource($this->ds_tipo);
		$this->fld_cod_tipo_doc->addAction("OnChange");
		$this->intercept($this->fld_cod_tipo_doc, "onChange","codtipodoc_click");
		
        $numerodoc=& $this->build("p4a_field", "numerodoc");
        $numerodoc->setLabel('N. documento');
        $numerodoc->setWidth("150");

		$this->build("p4a_field", "fld_codclifor");
		$this->fld_codclifor->setLabel("Cliente / Fornitore");
		$this->fld_codclifor->setWidth(150);
		$this->fld_codclifor->setType('select');
		$this->fld_codclifor->setSourceValueField('codice');
		$this->fld_codclifor->setSourceDescriptionField('descrizione');
		$this->fld_codclifor->setSource($this->ds_anag);
		
		
		// ------------------------------------------------- ANCORAGGIO ELEMENTI

        // Pannello filtro
        $fs_filtro = & $this->build( "p4a_fieldset", "fs_filtro" );
        $this->fs_filtro->setTitle( "Filtro documenti" );
        $this->fs_filtro->setWidth( E3G_FIELDSET_SEARCH_WIDTH );
        $this->fs_filtro->anchor($this->fld_tipodoc);
        $this->fs_filtro->anchor($this->fld_filtro_dal);
        $this->fs_filtro->anchorLeft($this->fld_filtro_al);
        $this->fs_filtro->anchorRight($this->bu_annulla_filtro);
        $this->fs_filtro->anchorRight($this->bu_filtro);
		
		// Sheet pulsantiera sotto
        $sh_campi =& $this->build("p4a_sheet", "sh_campi");
//		$this->sh_campi->setWidth(700);

		if ( E3G_TIPO_GESTIONE == 'G' ) {
            $this->sh_campi->defineGrid( 2, 4 );
            $this->sh_campi->anchor($this->bu_apri_doc,    1, 1);
            $this->sh_campi->anchor($this->bu_esporta_pdf, 1, 2);
            $this->sh_campi->anchor($this->bu_esporta_csv, 1, 3);
            $this->sh_campi->anchor($this->bu_elimina_doc, 1, 4);
            $this->sh_campi->anchor($this->fld_tutte,      2, 2, 1, 2);
	    }
		else {
            $this->sh_campi->defineGrid( 3, 3 );
			$this->sh_campi->anchor($this->fld_cod_tipo_doc, 1, 1);
            $this->sh_campi->anchor($this->bu_apri_doc,      1, 2);
            $this->sh_campi->anchor($this->bu_num_prox_doc,  1, 3);

			$this->sh_campi->anchor($this->numerodoc,        2, 1);
            $this->sh_campi->anchor($this->bu_esporta_pdf,   2, 2);
            $this->sh_campi->anchor($this->bu_nuovo_doc,     2, 3);

			$this->sh_campi->anchor($this->fld_codclifor,    3, 1);
	        $this->sh_campi->anchor($this->bu_elimina_doc,   3, 3);
		}


		// Frame
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth( E3G_MAIN_FRAME_WIDTH );

		$frm->anchor($message);
        $frm->anchor($this->fs_filtro); 
		$frm->anchor($this->tbl_doct);
		$frm->anchor($this->sh_campi); 
		
		e3g_scrivi_footer( $this, $frm );
  		
		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
		
		
        $this->bu_filtro_click();
        if ( $this->ds_doc->getNumRows() == 0 )
            // Inizialmente il periodo di date è fissato sull'ultimo mese: se non esistono documenti
            // allora il filtro viene annullato per farli vedere tutti 
            $this->bu_annulla_filtro_click();

		$this->tbl_doct_click();
	}


	function main()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		parent::main();
	}


	function bu_annulla_filtro_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		if ( E3G_TIPO_GESTIONE == 'G' ) {
           	switch ($p4a->e3g_utente_tipo) {
                case "R":
					$this->ds_doc->setWhere(str_replace("#CAMPOCODICE#", "codclifor", $p4a->e3g_where_referente));
					break;
				default:
					$this->ds_doc->setWhere("1=1");
					break;
            }
		}
		else {
			$docscontrino = $db->queryOne("SELECT eg_cod_doc_scontrino  FROM _aziende WHERE prefix='".$p4a->e3g_prefix."'");
			$this->ds_doc->setWhere("codtipodoc <> '" . $docscontrino . "'");
		}
		$this->fld_filtro_dal->setNewValue(""); 
		$this->fld_filtro_al->setNewValue("");   
    	$this->fld_tipodoc->setNewValue("00000");
	}
	
    
	function bu_filtro_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		$filtro = "";
        // controllo se il filtro tipo doc è valido <>"" e <>00000 (Non Indicato)   
        if ( $this->fld_tipodoc->getNewValue() != "" && $this->fld_tipodoc->getNewValue() != "00000" )  {
            $filtro .= " AND codtipodoc = '" . $this->fld_tipodoc->getNewValue() . "'";                  
        }   
		if ( $this->fld_filtro_dal->getNewValue() != "" ) {
			$filtro .= " AND data >= '" . e3g_format_data_to_mysql($this->fld_filtro_dal->getNewValue()) . "'";	
		}	
		if ( $this->fld_filtro_al->getNewValue() != "" )
		{
			$filtro .= " AND data <= '" . e3g_format_data_to_mysql($this->fld_filtro_al->getNewValue()) . "'";	
		}
        
		if ( E3G_TIPO_GESTIONE == 'G' ) {
           	switch ($p4a->e3g_utente_tipo) {
                case "R":
					$this->ds_doc->setWhere( str_replace("#CAMPOCODICE#", "codclifor", $p4a->e3g_where_referente) . $filtro );
					break;
				default:
					$this->ds_doc->setWhere( "1=1 " . $filtro );				
					break;
            }
		}
		else {
			$docscontrino = $db->queryOne( "SELECT eg_cod_doc_scontrino FROM _aziende WHERE prefix = '" . $p4a->e3g_prefix . "'" );
			$this->ds_doc->setWhere( "codtipodoc <> '" . $docscontrino . "' " . $filtro );
		}
    }
		

	function tbl_doct_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		$this->fld_cod_tipo_doc->setnewValue($this->tbl_doct->data->fields->codtipodoc->getNewValue());
		$this->numerodoc->setnewValue($this->tbl_doct->data->fields->numdocum->getNewValue());		
		$this->strdata = $this->tbl_doct->data->fields->data->getNewValue();	
		$this->codicetipodoc = $this->tbl_doct->data->fields->codtipodoc->getNewValue();
		$this->codclifor = $this->tbl_doct->data->fields->codclifor->getNewValue();
		
		$this->fld_codclifor->setNewValue($this->codclifor);
		
		$doc_estraibile = $db->queryOne("SELECT codaltridoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->fld_cod_tipo_doc->getNewValue()."'");
		if ($doc_estraibile != "")
		{
			$this->fld_tutte->setVisible();
		}
		else 
		{
			$this->fld_tutte->setInvisible();
		}
		
	}	


	function codtipodoc_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		$tipocf = $db->queryOne("SELECT tipoanagrafica FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->fld_cod_tipo_doc->getNewValue()."'");
				
		// Cliente / Fornitore
		
		$this->ds_anag->setWhere( "tipocfa = '" . $tipocf . "'" );
		$this->ds_anag->firstRow();
		$this->fld_codclifor->setSource($this->ds_anag);

		if ( $tipocf == 'C' ) {
			$this->fld_codclifor->setLabel('Cliente');
		}
		else 		{
			if ( $tipocf == 'F' ) {
				$this->fld_codclifor->setLabel('Fornitore');
			}
			else {
				$this->fld_codclifor->setLabel('Cliente/Fornitore');
			}
		}
	}	
		
	
	function bu_nuovo_docClick()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
				
		
		if ( $this->numerodoc->getNewValue() != "" )
		{
			// controllo se esiste gia 
			$numrows = $db->queryOne("SELECT COUNT(iddoc) as Righe FROM ".$p4a->e3g_prefix."doct WHERE numdocum='".$this->numerodoc->getNewValue()."' AND anno='".$p4a->e3g_azienda_anno_contabile."' AND codtipodoc='".$this->fld_cod_tipo_doc->getNewValue()."'");
			
			if ( $numrows == 0 )
			{
				// INSERISCO UN NUOVO DOCUMENTO
				$ultimo = $db->queryOne("SELECT MAX(iddoc) FROM ".$p4a->e3g_prefix."doct");
				$ultimo++;
				
				// TESTA DOCT				
				$db->query( 
                    "INSERT INTO " . $p4a->e3g_prefix . "doct " .
                    "    ( iddoc, numdocum, anno, codtipodoc, data_ins, idanag, codclifor, data) " .
                    "VALUES " .
                    "    ( " . $ultimo . ", '" . $this->numerodoc->getNewValue() . "', '" . $p4a->e3g_azienda_anno_contabile . "', '" .
                    $this->fld_cod_tipo_doc->getNewValue() . "', '" . date ("Y-m-d H:i:s") . "', " . $p4a->e3g_utente_idanag . ", '" .
                    $this->fld_codclifor->getNewValue() . "', '" . date ("Y-m-d") . "' )" );
			
				// aggiorno il Registro dei Documenti
				if (is_numeric($this->numerodoc->getNewValue())) {
					// solo se � numerico, non considero il caso Fatt. 2/bis
					$registro = $db->queryOne("SELECT codregdoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->fld_cod_tipo_doc->getNewValue()."'");
					$db->query("UPDATE ".$p4a->e3g_prefix."docregistri SET seriale = ".$this->numerodoc->getNewValue()." WHERE codice='".$registro."'");					
				}
				else {
					// niente il num doc � una stringa come ad es. 2/BIS
				}				
				
				// RIGHE DOCR
				//$idriga = $db->queryOne("SELECT MAX(idriga) FROM ".$p4a->e3g_prefix."docr");
				//$idriga++;
	
				//$query = "INSERT INTO ".$p4a->e3g_prefix."docr (idriga, descrizione, nriga, iddocr, numdocum , anno, codtipodoc, visibile) VALUES (".$idriga.", '----------', 1, ".$ultimo.",'".$this->numerodoc->getNewValue()."','".$p4a->e3g_azienda_anno_contabile."','".$this->fld_cod_tipo_doc->getNewValue()."','S')";
				//$db->query($query);
	
				$this->iddoc = $ultimo;
				$this->strdata = date("Y-m-d");
				$this->codclifor = $this->fld_codclifor->getNewValue();
		
				$this->caricacorpo_click();
			}
			else {
				$this->message->setValue( "Documento esistente!" );
			}
		}
	}


	function bu_num_prox_docClick()
	{
		$this->numerodoc->setValue( $this->Autonum() );
	}
	
	
	function bu_apri_docClick()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
	
		if ($this->numerodoc->getNewValue() != "" ) {
            // CERCO ID del DOCUMENTO
            //$this->iddoc = $db->queryOne("SELECT iddoc FROM ".$p4a->e3g_prefix."doct WHERE codtipodoc='".$this->fld_cod_tipo_doc->getNewValue()."' AND anno='".$p4a->e3g_azienda_anno_contabile."' AND numdocum='".$this->numerodoc->getNewValue()."'");
            // modifica del 20.01.09 Andrea: la vecchia query utilizzava ancora l'anno contabile che non è più gestito in Gestigas' 
            $this->iddoc = $this->ds_doc->fields->iddoc->getNewValue();
      
            $this->strdata = $db->queryOne( "SELECT data FROM " . $p4a->e3g_prefix . "doct " .
                " WHERE codtipodoc = '" . $this->fld_cod_tipo_doc->getNewValue() . "' AND anno = '" . $p4a->e3g_azienda_anno_contabile . "' " .
                "   AND numdocum = '" . $this->numerodoc->getNewValue() . "'" );

			if (is_numeric($this->iddoc)) 
				$this->caricacorpo_click();	
			else 
				$this->message->setValue("Il documento selezionato [".$this->numerodoc->getNewValue()."/".$this->fld_cod_tipo_doc->getNewValue()."] non esiste");
		}
		else {
			$this->message->setValue("Specificare il numero di documento che si desidera aprire");
		}
	}

	
	function Autonum()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		$registro = $db->queryOne("SELECT codregdoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->fld_cod_tipo_doc->getNewValue()."'");
		$ultimo = $db->queryOne("SELECT MAX(seriale) FROM ".$p4a->e3g_prefix."docregistri WHERE codice='".$registro."'");
		
		$ultimo++; 
		
		return $ultimo;
	}
	
	
	function caricacorpo_click()
	{
		$p4a =& p4a::singleton();
		
		//$this->maskClose('gesdocumenti1');
				
		// 3) apro la maschera delle righe
		$p4a->openMask('gesdocumenti1_righe');
	}

	
	function estrai_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		// trovo il cod tipo doc da generare estrarre 
		$estraidoc = $db->queryOne("SELECT codaltridoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->fields->codtipodoc->getNewValue()."'");
		
		$clifor = $this->codute->getNewValue();
		$datadoc = date("Y-m-d");
		
		$this->build("p4a_db_source", "ds_docr");
		$this->ds_docr->setTable($p4a->e3g_prefix."docr");
		$this->ds_docr->setPk("iddocr");
		$this->ds_docr->setWhere("1=0");
		$this->ds_docr->load();
		
		$this->build("p4a_db_source", "ds_righe");
		$this->ds_righe->setTable($p4a->e3g_prefix."docr");
		$this->ds_righe->setPk('idriga');
		$this->ds_righe->setPageLimit(10);
		$this->ds_righe->setWhere("visibile='S' AND iddocr=".$this->fields->iddoc->getNewValue());
		$this->ds_righe->addOrder("idriga");
		$this->ds_righe->load();
		
		
		// creo la testata del nuovo doc $estraidoc
		$this->ds_doct->newRow(); 
		$this->ds_doct->fields->data->setNewValue($datadoc); 
		$this->ds_doct->fields->codtipodoc->setNewValue($estraidoc);
		$this->ds_doct->fields->codclifor->setNewValue($clifor);
		$this->ds_doct->fields->anno->setNewValue($p4a->e3g_azienda_anno_contabile);
		// mettere tutti i riferimenti rifestrnum RifEstranno rifiddoc rifestrtipodoc
		$this->ds_doct->fields->rifestrnum->setNewValue($this->fields->numdocum->getNewValue());
		$this->ds_doct->fields->RifEstranno->setNewValue($this->fields->anno->getNewValue());
		$this->ds_doct->fields->rifestrtipodoc->setNewValue($this->fields->codtipodoc->getNewValue());
		$this->ds_doct->fields->rifiddoc->setNewValue($this->fields->iddoc->getNewValue());
		
		
		// Recupero ultimo numero documento
		$registro = $db->queryOne("SELECT codregdoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$estraidoc."'");
		$ultimo = $db->queryOne("SELECT MAX(seriale) FROM ".$p4a->e3g_prefix."docregistri WHERE codice='".$registro."'");
		$ultimo++;
		$this->ds_doct->fields->numdocum->setNewValue($ultimo);
		$numdoct = $ultimo;
		$query = "UPDATE ".$p4a->e3g_prefix."docregistri SET seriale=".$ultimo." WHERE codice='".$registro."'";
		$db->query($query);
			
		// Recupero ultimo iddoc
		$ultimo = $db->queryOne("SELECT MAX(iddoc) FROM ".$p4a->e3g_prefix."doct");
		$ultimo++;
		$this->ds_doct->fields->iddoc->setNewValue($ultimo);
			
		// questo sotto va in errore perche' ? 
		$this->ds_doct->saveRow(); 
			
		// faccio scorrere le righe del documento
		$this->ds_righe->firstRow();
		$riga = 1 ;
		$idriga = $db->queryOne("SELECT MAX(idriga) FROM ".$p4a->e3g_prefix."docr");
		$idriga++;
		
    	while($riga <= $this->ds_righe->getNumRows()) {
    		$this->ds_docr->newRow();
    		if ($this->tutterighe->getNewValue()) {
    			// ho selezionato tutte le righe
				$this->ds_docr->fields->codtipodoc->setNewValue($estraidoc);
				$this->ds_docr->fields->numdocum->setNewValue($numdoct);
				$this->ds_docr->fields->anno->setNewValue($p4a->e3g_azienda_anno_contabile);
				
				$this->ds_docr->fields->codice->setNewValue($this->ds_righe->fields->codice->getNewValue());
				$this->ds_docr->fields->descrizione->setNewValue($this->ds_righe->fields->descrizione->getNewValue());
				$this->ds_docr->fields->barcode->setNewValue($this->ds_righe->fields->barcode->getNewValue());
				$this->ds_docr->fields->prezzo->setValue($this->ds_righe->fields->prezzo->getNewValue());
				$this->ds_docr->fields->codiva->setNewValue($this->ds_righe->fields->codiva->getNewValue());
				$this->ds_docr->fields->quantita->setValue($this->ds_righe->fields->quantita->getNewValue());
				$this->ds_docr->fields->codutente->setNewValue($this->ds_righe->fields->codutente->getNewValue());

        		// Recupero ultimo iddocr
        		$this->ds_docr->fields->idriga->setNewValue($idriga);
				$this->ds_docr->fields->nriga->setNewValue($idriga);
				$this->ds_docr->fields->iddocr->setNewValue($ultimo);
			}
    		else {
    			// NON ho selezionato tutte le righe cerco = $clifor selezionato
				if ($this->ds_righe->fields->codutente->getNewValue() == $clifor) 
        		{		
					$this->ds_docr->fields->codtipodoc->setNewValue($estraidoc);
    				$this->ds_docr->fields->numdocum->setNewValue($numdoct);
    				$this->ds_docr->fields->anno->setNewValue($p4a->e3g_azienda_anno_contabile);
    				
    				$this->ds_docr->fields->codice->setNewValue($this->ds_righe->fields->codice->getNewValue());
    				$this->ds_docr->fields->descrizione->setNewValue($this->ds_righe->fields->descrizione->getNewValue());
    				$this->ds_docr->fields->barcode->setNewValue($this->ds_righe->fields->barcode->getNewValue());
    				$this->ds_docr->fields->prezzo->setValue($this->ds_righe->fields->prezzo->getNewValue());
    				$this->ds_docr->fields->codiva->setNewValue($this->ds_righe->fields->codiva->getNewValue());
    				$this->ds_docr->fields->quantita->setValue($this->ds_righe->fields->quantita->getNewValue());
    				$this->ds_docr->fields->codutente->setNewValue($this->ds_righe->fields->codutente->getNewValue());
    
            		// Recupero ultimo iddocr
            		$this->ds_docr->fields->idriga->setNewValue($idriga);
    				$this->ds_docr->fields->nriga->setNewValue($idriga);
    				$this->ds_docr->fields->iddocr->setNewValue($ultimo);
        		}
    		}	
    		
    		$this->ds_docr->saveRow(); 
    		$this->ds_righe->nextRow();
    		$riga++;
			$idriga++;
    	}	
	}


	function saveRow()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		$valid = true;

		foreach($this->mf as $mf){
			$value = $this->fields->$mf->getNewValue();
			if(trim($value) === ""){
				$this->fields->$mf->setStyleProperty("border", "1px solid red");
				$valid = false;
			}
		}


		if ( $valid ) {
			// se il documento e' settato per Gen. Auto Numero Doc vado a cercare
			// il numero Documento nel registro relativo
			$autonum = $db->queryOne("SELECT genautonum FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->fields->codtipodoc->getNewValue()."'");

			$strdata = e3g_format_mysql_data($this->fields->data->getNewValue());
			$this->fields->data->setNewValue($strdata);

			$this->fields->anno->setNewValue($p4a->e3g_azienda_anno_contabile);

			if ($this->numerodoc->getNewValue() != '') {
				// 1) procedo a salvare i dati della Testata
				parent::saveRow();
				
				// Aggiorno la data sui movimenti di magazzino 
				$query = "UPDATE ".$p4a->e3g_prefix."movmagr SET data='".$this->fields->data->getNewValue()."' WHERE codtipodoc='".$this->fields->codtipodoc->getNewValue()."' AND anno ='".$this->fields->anno->getNewValue()."' AND numdocum='".$this->fields->numdocum->getNewValue()."' ";
				$db->query($query);

				// Aggiorno il Registro (SOLO SE e' NUMERICO PERCHE' LASCIO LA LIBERTA' DI METTERE AD ES. UN NUMERO / BIS)
				if (is_numeric($this->numerodoc->getNewValue())) {
					$registro = $db->queryOne("SELECT codregdoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->fields->codtipodoc->getNewValue()."'");
					$ultimo = $db->queryOne("SELECT MAX(seriale) FROM ".$p4a->e3g_prefix."docregistri WHERE codice='".$registro."'");

					if ($this->numerodoc->getNewValue()>$ultimo) {
						$query = "UPDATE ".$p4a->e3g_prefix."docregistri SET seriale=".$this->numerodoc->getNewValue()." WHERE codice='".$registro."'";
						$db->query($query);
					}
				}
			}
		}
		else {
			$this->message->setValue("Compilare i campi obbligatori");
		}
	}


	function nextRow()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		$query = "codtipodoc='".$this->fields->codtipodoc->getNewValue()."' AND anno='".$p4a->e3g_azienda_anno_contabile."' ";

		$this->data->setWhere($query);

		parent::nextRow();
	}


	function prevRow()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
	
		$query = "codtipodoc='".$this->fields->codtipodoc->getNewValue()."' AND anno='".$p4a->e3g_azienda_anno_contabile."' ";

		$this->data->setWhere($query);

		parent::prevRow();
	}


	function newRow()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		// prima della nuova riga
		$this->codicetipodoc = $this->fields->codtipodoc->getNewValue();

		parent::newRow();
		
		$registro = $db->queryOne("SELECT codregdoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->codicetipodoc."'");
		$numero = $db->queryOne("SELECT seriale FROM ".$p4a->e3g_prefix."docregistri WHERE codice='".$registro."'");
		$numero++;

		$this->numerodoc->setNewValue($numero);

		// Dopo della Nuova Riga
 		$this->fields->codtipodoc->setValue($this->codicetipodoc);
 		$this->fields->numdocum->setValue($numero);
 		// cerco il tipo Anagrafica
		$tipocf = $db->queryOne("SELECT tipoanagrafica FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->fields->codtipodoc->getNewValue()."'");

		// Cliente / Fornitore
		$query = "tipocfa='".$tipocf."'";
		$this->ds_anag->setWhere($query);
		$this->ds_anag->load();
		if ($tipocf=='C') {
			$this->fields->codclifor->setLabel('Cliente');
		}
		else {
			if ( $tipocf == 'F' )
				$this->fields->codclifor->setLabel('Fornitore');
			else
				$this->fields->codclifor->setLabel('Cliente/Fornitore');
		}


 		// Attribuisco l'Id del Documento
 		$iddoc = $db->queryOne("SELECT iddoc FROM ".$p4a->e3g_prefix."doct ORDER BY iddoc DESC");
		$iddoc++;
		$this->fields->iddoc->setNewValue( $iddoc );
	}

	
	function bu_elimina_docClick()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
			
		$seriale = $db->queryOne(
            "SELECT codice FROM " . $p4a->e3g_prefix . "movcont " .
            " WHERE codtipodoc = '" . $this->fld_cod_tipo_doc->getNewValue() . "' " .
            "   AND anno = '" . $p4a->e3g_azienda_anno_contabile . "' " .
            "   AND numdocum = '" . $this->numerodoc->getNewValue() . "' " );
		
		$db->query( "DELETE FROM " . $p4a->e3g_prefix . "movmagr " .
            " WHERE codtipodoc = '" . $this->fld_cod_tipo_doc->getNewValue() . "' " .
            "   AND anno = '" . $p4a->e3g_azienda_anno_contabile . "' " .
            "   AND numdocum = '" . $this->numerodoc->getNewValue() . "'" );

		$db->query( "DELETE FROM " . $p4a->e3g_prefix  ."movconr WHERE codice = '" . $seriale . "'" );
		$db->query( "DELETE FROM " . $p4a->e3g_prefix . "movcont WHERE codice = '" . $seriale . "'" );
		
		$db->query( "DELETE FROM " . $p4a->e3g_prefix . "docr WHERE iddocr = " . $this->tbl_doct->data->fields->iddoc->getNewValue() );
		$db->query( "DELETE FROM " . $p4a->e3g_prefix . "doct WHERE iddoc = " . $this->tbl_doct->data->fields->iddoc->getNewValue() );
	}
	

	function bu_esporta_pdfClick()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		genera_stampa_pdf( 
            $this->numerodoc->getNewValue(), 
            $this->ds_doc->fields->iddoc->getNewValue(), 
            $this->ds_doc->fields->codtipodoc->getNewValue(), 
            $this->ds_doc->fields->codclifor->getNewValue(), 
            $this->ds_doc->fields->codtipopag->getNewValue(), 
            $this->fld_tutte->getNewValue() );
	}


	function bu_esporta_docClick()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		genera_stampa_odt(
            $this->numerodoc->getNewValue(), 
            $this->ds_doc->fields->iddoc->getNewValue(), 
            $this->ds_doc->fields->codtipodoc->getNewValue(),
            $this->ds_doc->fields->codclifor->getNewValue(), 
            $this->ds_doc->fields->codtipopag->getNewValue() );
	}

    function bu_esporta_csvClick()
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
        
        genera_stampa_csv(
            $this->numerodoc->getNewValue(), 
            $this->ds_doc->fields->iddoc->getNewValue(), 
            $this->ds_doc->fields->codtipodoc->getNewValue(), 
            $this->ds_doc->fields->codclifor->getNewValue(), 
            $this->ds_doc->fields->codtipopag->getNewValue(), 
            $this->fld_tutte->getNewValue() );
    }

}

?>