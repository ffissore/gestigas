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


require_once( dirname(__FILE__) . '/../libraries/e3g_utils.php' );
require_once( dirname(__FILE__) . '/../config.php' );


class anag_utentigg_clientieg extends P4A_Mask
{
	var $newrecord = false;
	
	// -------------------------------------------------------------------------
	function anag_utentigg_clientieg()
	// -------------------------------------------------------------------------
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();

		if ( E3G_TIPO_GESTIONE == 'G' )
			$this->setTitle("Anagrafica utenti");  // GestiGAS
		else 
			// Equogest 
			$this->setTitle("Anagrafica clienti");  // Equogest
		$this->setIcon( "users" );


		// Sorgente dati principale --------------------------------------------
		$this->build( "p4a_db_source", "ds_anag" );
		$this->ds_anag->setTable( $p4a->e3g_prefix . "anagrafiche" );
		$this->ds_anag->setPk( "idanag" );
		$this->ds_anag->setPageLimit( $p4a->e3g_utente_db_source_page_limit );
		$this->ds_anag->addOrder( "descrizione" );
		$this->ds_anag->load();  // WHERE impostato in bu_cercaClick()
		$this->ds_anag->firstRow();

		$this->setSource($this->ds_anag);

		
		// Fields properties 
		//$this->setFieldsProperties();
		$fields =& $this->fields;
		
		$this->fields->idanag->setType('decimal');
		
		
		// Campi Obbligatori Fields --------------------------------------------
		if ( E3G_TIPO_GESTIONE == 'G' ) {
			$this->mf = array("idanag", "codice", "descrizione", "email");
		}
		else {
	 		$this->mf = array("idanag", "codice", "descrizione");
		}
	    foreach ( $this->mf as $mf ) 
			$fields->$mf->label->setFontWeight("bold");


		// Larghezza fields
		while ($field =& $this->fields->nextItem()) 
			$field->label->setWidth(140);


		// Altri db source -----------------------------------------------------
		$this->build("p4a_db_source", "ds_tipo");
		$this->ds_tipo->setTable("_login_tipo_utente");
        if ( $p4a->e3g_utente_tipo == "A" )  // Solo il super-admin può vedere e impostare anche il tipo "super-admin"
            $this->ds_tipo->setWhere( "codice IN ('A', 'AS', 'R', 'U')" );
        else
            $this->ds_tipo->setWhere( "codice IN      ('AS', 'R', 'U')" );
		$this->ds_tipo->setPk("codice");
		$this->ds_tipo->load();
		
		$this->build("p4a_db_source", "ds_stato");
		$this->ds_stato->setTable("_anagrafiche_stato");
		$this->ds_stato->setPk("codice");
		$this->ds_stato->load();

		$this->build("p4a_db_source", "ds_mas");
		$this->ds_mas->setTable($p4a->e3g_prefix."contmastri");
		$this->ds_mas->setPk("codice");
		$this->ds_mas->load();

		$this->build("p4a_db_source", "ds_con");
		$this->ds_con->setTable($p4a->e3g_prefix."contconti");
		$this->ds_con->setPk("codice");
		$this->ds_con->load();

		$this->build("p4a_db_source", "ds_segno");
		$this->ds_segno->setTable($p4a->e3g_prefix."contsegno");
		$this->ds_segno->setPk("codice");
		$this->ds_segno->load();


 		// ------------------------------------------------------------- Toolbar
		if ( E3G_TIPO_GESTIONE == 'G' ) {
			switch ($p4a->e3g_utente_tipo) {
                case "A":
                case "AS":
					$this->build("p4a_standard_toolbar", "toolbar");
					break;

                case "R":
					$this->build("p4a_navigation_toolbar", "toolbar");
					break;

                case "U":
                case "G":
					die();
					break;
			}			
		}
		else  
			$this->build("p4a_standard_toolbar", "toolbar");
		
		$this->toolbar->setMask($this);
        
        
        // ------------------------- Bottone toolbar per esportare righe tabella
        $this->toolbar->addSeparator();
        $this->toolbar->addButton( "bu_esporta_csv", "spreadsheet" );
        // Con il false finale non si dovrebbe vedere la label ma solo il tooltip, ma ciò non accade in p4a 2.2.3 (bug?)
        $this->toolbar->buttons->bu_esporta_csv->setLabel( "Esporta righe come CSV (foglio elettronico)", false );
        $this->toolbar->buttons->bu_esporta_csv->addAction( "onClick" );
        $this->intercept( $this->toolbar->buttons->bu_esporta_csv, "onClick", "bu_esporta_csvClick" );
		
		
		// ------------------------------------------------------- Campi Ricerca
        // Ricerca per descrizione
        $this->build( "p4a_field", "txt_search" );
        $this->txt_search->setWidth( 250 );
        $this->txt_search->setLabel( "Descrizione" );
        $this->txt_search->setTooltip( "contiene" );
        $this->txt_search->label->setWidth( 120 );
        $this->txt_search->addAction( "onReturnPress" );
        $this->intercept( $this->txt_search, "onReturnPress", "bu_cercaClick" );

		// Ricerca email
		$this->build( "p4a_field", "txt_mail" );
        $this->txt_mail->setWidth( 250 );
		$this->txt_mail->setLabel( "E-Mail" );
        $this->txt_mail->label->setWidth( 120 );
        $this->txt_mail->addAction( "onReturnPress" );
        $this->intercept( $this->txt_mail, "onReturnPress", "bu_cercaClick" );

        // Filtro anche utenti disabilitati
        $this->build( "p4a_field", "ck_anche_disabilitati" );
        $this->ck_anche_disabilitati->setType( "checkbox" );
        $this->ck_anche_disabilitati->setLabel( "Anche disabilitati" );
        $this->ck_anche_disabilitati->label->setWidth( 120 );
        $this->ck_anche_disabilitati->setTooltip( "Visualizza anche gli utenti disabilitati e in attesa" );
        
        // Bottone "Cerca"		
		$this->build( "p4a_button", "bu_cerca" );
        $this->bu_cerca->setWidth( 150 );
		$this->bu_cerca->setLabel( "Cerca" );
		$this->bu_cerca->setIcon( "find" );
		$this->bu_cerca->setSize( 16 );
        $this->bu_cerca->addAction( "onClick" );
		$this->intercept( $this->bu_cerca, "onClick", "bu_cercaClick" );
		
		// Bottone "Annulla Ricerca"
		$this->build("p4a_button", "bu_annulla_cerca");
        $this->bu_annulla_cerca->setWidth(150);
		$this->bu_annulla_cerca->setLabel("Annulla");
		$this->bu_annulla_cerca->setIcon("cancel");
		$this->bu_annulla_cerca->setSize(16);
        $this->bu_annulla_cerca->addAction("onClick");
		$this->intercept($this->bu_annulla_cerca, "onClick", "bu_annulla_cercaClick");

		$fs_search =& $this->build("p4a_fieldset","fs_search");
		$fs_search->setTitle( "Cerca" );
		$fs_search->setWidth( E3G_FIELDSET_SEARCH_WIDTH );
		
		$fs_search->anchor($this->txt_search);
		$fs_search->anchorLeft($this->txt_mail);
        $fs_search->anchor($this->ck_anche_disabilitati);
        $fs_search->anchorRight($this->bu_annulla_cerca);
		$fs_search->anchorRight($this->bu_cerca);
		

		// ------------------------------------------------------------- Tabella
		$table =& $this->build("p4a_table", "table");
 		$table->setWidth( E3G_TABLE_WIDTH );
		$table->setSource($this->ds_anag);
		$table->setVisibleCols( array("descrizione", "email", "tipoutente", "stato", "n_login", "last_login") );
		$table->showNavigationBar();
        $this->intercept( $table->rows, "beforeDisplay", "tableBeforeDisplay" );  
        $this->intercept( $table->rows, "afterClick", "tableAfterClick" );

		$this->table->cols->descrizione->setLabel("Descrizione");
		$this->table->cols->email->setLabel("Indirizzo e-mail");
		
		$this->table->cols->tipoutente->setLabel("Tipo accesso");
		$this->table->cols->tipoutente->setType("select");
		$this->table->cols->tipoutente->setSource($this->ds_tipo);
		$this->table->cols->tipoutente->setSourceValueField("codice");
		$this->table->cols->tipoutente->setSourceDescriptionField("descrizione");
		$this->table->cols->tipoutente->setWidth(100);

		$this->table->cols->stato->setLabel("Stato");
		$this->table->cols->stato->setType("select");
		$this->table->cols->stato->setSource($this->ds_stato);
		$this->table->cols->stato->setSourceValueField("codice");
		$this->table->cols->stato->setSourceDescriptionField("descrizione");
		$this->table->cols->stato->setWidth(60);
		
		$this->table->cols->n_login->setLabel("N. accessi");
		$this->table->cols->n_login->setWidth(60);
		$this->table->cols->last_login->setLabel("Data ultimo accesso");
		$this->table->cols->last_login->setWidth(130);

		
		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth(400);


		//------------------------------------------------------ Dati anagrafici
		$this->fields->codice->disable();

        $this->fields->localita->setLabel( "Localita'" );
        $this->fields->cap->setLabel( "CAP" );
        $this->fields->telefono2->setLabel( "Telefono alternativo" );

        $this->fields->descrizione->setWidth( 250 );
        $this->fields->indirizzo->setWidth( 250 );
        $this->fields->cap->setWidth( 50 );
        
		//Fieldset con l'elenco dei campi
		$fs_anagrafica =& $this->build("p4a_fieldset", "fs_anagrafica");
		$fs_anagrafica->setTitle("Dati anagrafici");
		$fs_anagrafica->setWidth( E3G_FIELDSET_DATI_WIDTH );

 		$fs_anagrafica->anchor($this->fields->codice);
		$fs_anagrafica->anchor($this->fields->Titolo);

		$fs_anagrafica->anchor( $this->fields->nome );
		$fs_anagrafica->anchorLeft( $this->fields->cognome );
		$fs_anagrafica->anchor( $this->fields->descrizione );

		$fs_anagrafica->anchor( $this->fields->indirizzo );
        $fs_anagrafica->anchorLeft( $this->fields->localita );

		$fs_anagrafica->anchor( $this->fields->cap );
        $fs_anagrafica->anchorLeft( $this->fields->comune );
 		$fs_anagrafica->anchorLeft( $this->fields->provincia );

        $fs_anagrafica->anchor( $this->fields->telefono );
        $fs_anagrafica->anchorLeft( $this->fields->telefono2 );
        $fs_anagrafica->anchorLeft( $this->fields->cellulare );

 		$fs_anagrafica->anchorLeft( $this->fields->fax );
		
		if ( E3G_TIPO_GESTIONE == 'G' ) {
            ;			
		}
		else {
            $this->fields->cf->setLabel("Codice Fiscale");
            $this->fields->piva->setLabel("Partita IVA");

			$fs_anagrafica->anchor($this->fields->cf);
			$fs_anagrafica->anchorLeft($this->fields->piva);
			$fs_anagrafica->anchor($this->fields->sconto);
		}		

        // ----------------------------------------------- Dati fiscali GestiGAS
        if ( E3G_TIPO_GESTIONE == 'G' ) {
            $this->fields->data_nascita->setLabel("Data di nascita");
            $this->fields->luogo_nascita->setLabel("Luogo di nascita");
            $this->fields->cf->setLabel("Codice Fiscale");

            $fs_dati_fiscali =& $this->build("p4a_fieldset", "fs_dati_fiscali");
            $fs_dati_fiscali->setTitle("Dati fiscali");
            $fs_dati_fiscali->setWidth( E3G_FIELDSET_DATI_WIDTH );

            $this->fields->luogo_nascita->label->setWidth(110);

            $fs_dati_fiscali->anchor($this->fields->data_nascita);
            $fs_dati_fiscali->anchorLeft($this->fields->luogo_nascita);
            $fs_dati_fiscali->anchor($this->fields->cf);
        }

		// ---------------------------------------------------------- Altri dati
        $this->build( "p4a_db_source", "ds_luoghi_cons" );
        $this->ds_luoghi_cons->setTable( "_luoghi_cons" );
        $this->ds_luoghi_cons->setWhere( "prefix = '" . $p4a->e3g_prefix . "' OR id_luogo_cons = 0" );
        $this->ds_luoghi_cons->setPk( "id_luogo_cons" );
        $this->ds_luoghi_cons->load();
        
		$fields->id_luogo_cons->setLabel( "Luogo di consegna" );
		$fields->id_luogo_cons->setWidth( 250 );
		$fields->id_luogo_cons->setType( "select" );
		$fields->id_luogo_cons->setSourceValueField( "id_luogo_cons" );
		$fields->id_luogo_cons->setSourceDescriptionField( "descrizione" );
		$fields->id_luogo_cons->setSource( $this->ds_luoghi_cons );

        $fields->mailing_list->setLabel( "Mailing-list" );
        $fields->mailing_list->setType( "checkbox" );

        $fields->note->setType( "textarea" );
        $fields->note->setWidth( 480 );
        $fields->note->setHeight( 50 );
        
        $fields->filtro_ingredienti->setLabel( "Filtro ingredienti" );
        $fields->filtro_ingredienti->setType( "checkbox" );
        $fields->filtro_ingredienti->setTooltip( "Visualizzazione filtro ingredienti degli articoli" );

        $fields->ingredienti_escludi->setLabel( "Ingredienti da escludere" );
        $fields->ingredienti_escludi->label->setWidth( 250 );
        $fields->ingredienti_escludi->setTooltip( "Elenco predefinito degli ingredienti da escludere (separarli con una virgola e non usare spazi)" );
        $fields->ingredienti_escludi->setType( "textarea" );
        $fields->ingredienti_escludi->setWidth( 480 );
        $fields->ingredienti_escludi->setHeight( 50 );

        $fields->db_source_page_limit->setLabel( "N. righe tabelle" );
        $fields->db_source_page_limit->setTooltip( "Visualizzazioni tabellari: numero di righe per pagina (compreso tra 5 e 50)" );


        // Fieldset altri dati
		$fs_altri_dati =& $this->build( "p4a_fieldset", "fs_altri_dati" );
		$fs_altri_dati->setWidth( E3G_FIELDSET_DATI_WIDTH );
		$fs_altri_dati->setTitle( "Altri dati" );
        $fs_altri_dati->anchor( $fields->mailing_list );
		if ( $p4a->e3g_azienda_gestione_luoghi_cons )
			$fs_altri_dati->anchor( $this->fields->id_luogo_cons );
		$fs_altri_dati->anchor( $this->fields->note, "167px" );
        $fs_altri_dati->anchor( $this->fields->filtro_ingredienti );
        $fs_altri_dati->anchor( $this->fields->ingredienti_escludi, "167px" );
        $fs_altri_dati->anchor( $this->fields->db_source_page_limit );
		

		// -------------------------------------- Dati contabili (solo Equogest)
		$this->fields->conto->setLabel("Conto");
		$this->fields->conto->setType("select");
		$this->fields->conto->setSource($this->ds_con);
		$this->fields->conto->setSourceValueField("codice");
		$this->fields->conto->setSourceDescriptionField("descrizione");
		
		$this->fields->mastro->setLabel("Mastro");
		$this->fields->mastro->setType("select");
		$this->fields->mastro->setSource($this->ds_mas);
		$this->fields->mastro->setSourceValueField("codice");
		$this->fields->mastro->setSourceDescriptionField("descrizione");
		$this->fields->mastro->addAction("OnChange");
		$this->intercept($this->fields->mastro, "onChange","mastro_click");

		$this->fields->segnocontabile->setType("select");
		$this->fields->segnocontabile->setSource($this->ds_segno);
		$this->fields->segnocontabile->setSourceValueField("codice");
		$this->fields->segnocontabile->setSourceDescriptionField("descrizione");

		//Fieldset user e pwd
		$fs_contabili=& $this->build("p4a_fieldset", "fs_contabili");
        $fs_contabili->setWidth( E3G_FIELDSET_DATI_WIDTH );
		$fs_contabili->setTitle("Dati Contabili");
		$fs_contabili->anchor($this->fields->mastro);
		$fs_contabili->anchor($this->fields->conto);
		$fs_contabili->anchor($this->fields->segnocontabile);
		

		// -------------------------------------------------- Dati per l'accesso
		$this->fields->stato->setLabel( "Stato" );
        $this->fields->stato->setWidth( 200 );
		$this->fields->stato->setType( "select" );
		$this->fields->stato->setSource( $this->ds_stato );
		$this->fields->stato->setSourceValueField( "codice" );
		$this->fields->stato->setSourceDescriptionField( "descrizione" );
		
		$this->fields->email->setWidth(200);
		
		$this->fields->tipoutente->setLabel( "Tipo accesso" );
		$this->fields->tipoutente->setWidth( 200 );
		$this->fields->tipoutente->setType( "select" );
		$this->fields->tipoutente->setSourceValueField( "codice" );
		$this->fields->tipoutente->setSourceDescriptionField( "descrizione" );
		$this->fields->tipoutente->setSource( $this->ds_tipo );

        $fields->modifica_ingredienti->setLabel( "Modifica ingredienti" );
        $fields->modifica_ingredienti->setType( "checkbox" );
        $fields->modifica_ingredienti->setTooltip( "Abilitazione alla modifica ingredienti degli articoli" );

        $fields->cassiere->setLabel( "Cassiere" );
        $fields->cassiere->setType( "checkbox" );
        $fields->cassiere->setTooltip( "Utente con qualifica di cassiere" );

		// Campo per la modifica della password
		$new_pwd1 =& $this->build( "p4a_field", "new_pwd1" );
		$new_pwd1->setLabel( "Nuova password" );
		$new_pwd1->setType( "password" );
		$new_pwd1->label->setWidth( 140 );
		$new_pwd1->setWidth( 200 );
		$new_pwd1->setValue( "" );
		$new_pwd1->unsetStyleProperty( "border" );

		$new_pwd2 =& $this->build( "p4a_field", "new_pwd2" );
		$new_pwd2->setLabel( "Verifica nuova password" );
		$new_pwd2->setType( "password" );
		$new_pwd2->label->setWidth( 140 );
		$new_pwd2->setWidth(200);
		$new_pwd2->setValue( "" );
		$new_pwd2->unsetStyleProperty( "border" );

		$this->fields->n_login->setLabel( "N. accessi" );
		$this->fields->n_login->disable();
		$this->fields->last_login->setLabel( "Ultimo accesso" );
		$this->fields->last_login->disable();

		//Fieldset con l'elenco dei campi
		$fs_accesso=& $this->build("p4a_fieldset", "fs_accesso");
		$fs_accesso->setWidth( E3G_FIELDSET_DATI_WIDTH );
		$fs_accesso->anchor($this->fields->stato);
		$fs_accesso->anchor($this->fields->email);

		if ( E3G_TIPO_GESTIONE == 'G' ) {
			$fs_accesso->setTitle("Dati Login");
            $fs_accesso->anchor( $this->fields->tipoutente );
            $fs_accesso->anchorLeft( $this->fields->modifica_ingredienti );
            $fs_accesso->anchorLeft( $this->fields->cassiere );

			switch ($p4a->e3g_utente_tipo) {
                case "A":
                case "AS":
					$fs_accesso->anchor($this->new_pwd1);
					$fs_accesso->anchor($this->new_pwd2);
					break;

                case "R":
					// Il referente può solo leggere
					while ( $field =& $this->fields->nextItem() ) 
						$field->disable();
					break;

                case "U":
                case "G":
					die();
					break;
			}			
		}
		else {
	 		$fs_accesso->setTitle("");

			$fs_accesso->anchor($this->new_pwd1);
			$fs_accesso->anchor($this->new_pwd2);
		}

		$fs_accesso->anchor( $this->fields->n_login );
		$fs_accesso->anchorLeft( $this->fields->last_login );


		// ---------------------------------------------------------------- Date
		$this->fields->data_ins->disable();
		$this->fields->data_agg->disable();
		$this->fields->data_ins->setLabel("Inserimento");
		$this->fields->data_agg->setLabel("Ultima modifica");

		//Fieldset con le date ins e agg
		$fs_date=& $this->build("p4a_fieldset", "fs_date");
		$fs_date->setTitle("Date");
		$fs_date->setWidth( E3G_FIELDSET_DATI_WIDTH );
		$fs_date->anchor($this->fields->data_ins);
		$fs_date->anchorLeft($this->fields->data_agg);


		// ---------------------------------------------------- Frame principale
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(E3G_MAIN_FRAME_WIDTH);

		$frm->anchor($fs_search);
		$frm->anchor($table);
		$frm->anchor($message);
        $frm->anchor($fs_anagrafica);
        $frm->anchor($fs_dati_fiscali);
		$frm->anchor($fs_altri_dati);
		
		if ( E3G_TIPO_GESTIONE == 'E' ) 
			$frm->anchor($fs_contabili);

  		$frm->anchor($fs_accesso);
		$frm->anchor($fs_date);
		
		e3g_scrivi_footer( $this, $frm );

		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);

        $this->bu_cercaClick();
        $this->tableAfterClick(); 
	}

	
	// -------------------------------------------------------------------------
	function main()
	// -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		// Se non ci sono record, allora la finestra si predispone
		// in inserimento, ma bisogna generare l'evento newRow()
		$n = $db->queryOne(
			"SELECT COUNT(*) FROM " . $p4a->e3g_prefix . "anagrafiche WHERE tipocfa = 'C' and stato = 1" );
		if ( $n == 0 ) {
			$this->newRow();
			$this->fields->codice->enable();
		}
			
			
		parent::main();

		foreach ( $this->mf as $mf )
			$this->fields->$mf->unsetStyleProperty( "border" );
        $this->fields->cf->unsetStyleProperty( "border" );
	}


    // -------------------------------------------------------------------------
    function bu_cercaClick()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        
        if ( E3G_TIPO_GESTIONE == 'G' ) {
            switch ( $p4a->e3g_utente_tipo ) {
                case "A":
                case "AS": // Amministratori (anche globali) vedono tutto
                case "R":  // Il Referente vede tutto ma in sola lettura
                    $strwhere = " tipocfa = 'C' ";
                    break;
                case "U":
                case "G":
                    die();
                    break;
            }
        }
        else 
            $strwhere = " tipocfa = 'C' ";
        
        if ( $this->txt_search->getNewValue() != "" )
            $strwhere .= " AND UCASE(descrizione) LIKE '%" . addslashes( strtoupper(trim($this->txt_search->getNewValue())) ) . "%'";      
        
        if ( $this->txt_mail->getNewValue() != "" )
            $strwhere .= " AND LCASE(email) LIKE '%" . addslashes( strtolower(trim($this->txt_mail->getNewValue())) ) . "%'";      
        
        // Visualizza anche utenti disabilitati e in attesa [ 0:In attesa 1:Abilitato 2:Disabilitato ]
        if ( $this->ck_anche_disabilitati->getNewValue() == 0 )
            $strwhere .= " AND stato = 1";
        
        $oldwhere = $this->ds_anag->getWhere();
        $this->ds_anag->setWhere( $strwhere );
        
        if ( $this->ds_anag->getNumRows() == 0 ) {
            $this->message->setValue( "Nessun utente trovato." );
            $this->ds_anag->setWhere( $oldwhere );
        }
        $this->ds_anag->firstRow();
        $this->table->syncPageWithSource();
        $this->table->setTitle( $this->data->getNumRows() . " utent" . ( $this->ds_anag->getNumRows()==1 ? "e" : "i" ) );
    }
    
        
    // -------------------------------------------------------------------------
    function bu_annulla_cercaClick()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        
        $this->txt_search->setNewValue( "" );
        $this->txt_mail->setNewValue( "" );
        $this->ck_anche_disabilitati->setNewValue( 0 );
        
        $this->bu_cercaClick();
    }

    
	// -------------------------------------------------------------------------
	function deleteRow()
	// -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		if ( E3G_TIPO_GESTIONE == 'G' )
		{
			// Gestigas: controllo se utente cerca di auto eliminarsi 
			if ($p4a->e3g_utente_idanag == $this->fields->idanag->getNewValue())
			{
				// sto cercando di cancellare me stesso
				$this->message->setIcon( "error" );
				$this->message->setValue( "Eliminazione non consentita: utente utilizzato per l'accesso corrente." );			 
			}	
			else
			{
				parent::deleteRow();	
			}			
		}
		else
		{
			// Equogest gestisce i login dalla AnagUtenti
			parent::deleteRow();
		}
	}


	// -------------------------------------------------------------------------
	function newRow()
	// -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		parent::newRow();
		
		$this->newrecord = true;
		
        // Campi obbligatori
        $this->fields->tipocfa->setNewValue("C");
        $this->fields->stato->setValue(1);  //0:In attesa 1:Abilitato 2:Disabilitato
        $this->fields->data_ins->setNewValue( date ("Y-m-d H:i:s") );
        $this->fields->n_login->setNewValue( 0 );
        $this->fields->mailing_list->setNewValue( 0 );
        $this->fields->tipoutente->setValue("U");
        $this->fields->db_source_page_limit->setNewValue( 10 );
        $this->fields->modifica_ingredienti->setNewValue( 0 );
        $this->fields->filtro_ingredienti->setNewValue( 0 );
        $this->fields->cassiere->setNewValue( 0 );
		
		// Propone un codice del tipo C0000 (il controllo di unicità è nel saveRow)
		//$max_cod = $db->queryOne(
		//	"SELECT MAX( idanag ) FROM " . $p4a->e3g_prefix . "anagrafiche WHERE tipocfa = 'C'" );
		$max_cod = $db->queryOne("SELECT MAX( idanag ) FROM " . $p4a->e3g_prefix . "anagrafiche" );
		$this->fields->codice->setNewValue( 'C' . sprintf( "%04d", ++$max_cod ) );
		$this->fields->codice->enable();
				
		if ( E3G_TIPO_GESTIONE == 'E' )
			$this->fields->segnocontabile->setNewValue( "D" );
	}


	// -------------------------------------------------------------------------
	function saveRow()
	// -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		// ucwords capitalizza la stringa
		$this->fields->nome->setNewValue( ucwords( strtolower( $this->fields->nome->getNewValue() ) ) );  
		$this->fields->cognome->setNewValue( ucwords( strtolower( $this->fields->cognome->getNewValue() ) ) );
			
        // Rende maiuscolo il codice fiscale
        $this->fields->cf->setNewValue( trim( strtoupper( $this->fields->cf->getNewValue() ) ) );
            
		if ( !is_numeric($this->fields->idanag->getNewValue()) )
		{
			$maxid = $db->queryOne(
				"SELECT MAX( idanag ) FROM " . $p4a->e3g_prefix . "anagrafiche" );
			if ( is_numeric($maxid) )
				$maxid++;
			else 
				$maxid = 1;
			$this->fields->idanag->setNewValue( $maxid );
		}

		// Compongo la descrizione 
		if ( ( $this->fields->descrizione->getNewValue() == "" ) and
		     ( $this->fields->nome->getNewValue() <> "" ) and ( $this->fields->cognome->getNewValue() <> "" ) )
		{
			$this->fields->descrizione->setNewValue(
				$this->fields->nome->getNewValue() . " " . $this->fields->cognome->getNewValue() );
		}
				
				
		// Controllo validita' dati --------------------------------------------
		
		$error_text = "";
		
		if ( E3G_TIPO_GESTIONE == 'G' )  // GestiGAS
		{
			if ( !e3g_email_valido( $this->fields->email->getNewValue() ) ) {
				// indirizzo e-mail non valido
				$error_text = "Scrivere un indirizzo e-mail valido.";
				$this->fields->email->setStyleProperty("border", "1px solid red");
			}
			elseif ( $this->newrecord and $this->new_pwd1->getNewValue() == "" ) {
				// Nuovo utente, ma senza password
				$error_text = "Compilare la password.";
				$this->new_pwd1->setStyleProperty("border", "1px solid red");
			}
			elseif ( $this->new_pwd1->getNewValue() != $this->new_pwd2->getNewValue() ) {
				// Password diversa da seconda password di verifica
				$error_text = "Le due password non coincidono, prova a riscriverle.";
				$this->new_pwd1->setStyleProperty("border", "1px solid red");
				$this->new_pwd2->setStyleProperty("border", "1px solid red");
			}
	
			// Verifica e-mail non duplicato
			if ( $error_text == "" and $this->fields->email->getNewValue() != "" ) {
				$n = $db->queryOne(
					"SELECT COUNT( * ) FROM " . $p4a->e3g_prefix . "anagrafiche " .
					" WHERE email = '" . $this->fields->email->getNewValue() . "' " .
					"   AND idanag <> " . $this->fields->idanag->getNewValue() );
				if ( $n > 0 ) {
					$error_text = "Indirizzo e-mail '" . $this->fields->email->getNewValue() . "' gia' presente.";
					$this->fields->email->setStyleProperty("border", "1px solid red");
				}
			}
            
            // Verifica correttezza codice fiscale
            if ( $this->fields->cf->getNewValue() <> '' and !CodiceFiscaleEsatto( $this->fields->cf->getNewValue() ) ) {
                // indirizzo e-mail non valido
                $error_text = "Il codice fiscale non e' corretto.";
                $this->fields->cf->setStyleProperty("border", "1px solid red");
            }
		}
		else  // Equogest
		{
	 	}

		
		// Verifica campo codice non duplicato 
		if ( $error_text == "" and $this->fields->codice->getNewValue() != "" ) {
			$n = $db->queryOne(
				"SELECT COUNT( * ) FROM " . $p4a->e3g_prefix . "anagrafiche " .
				" WHERE codice = '" . $this->fields->codice->getNewValue() . "' " .
				"   AND idanag <> " . $this->fields->idanag->getNewValue() );
			if ( $n > 0 ) {
				$error_text = "Codice '" . $this->fields->codice->getNewValue() . "' gia' presente.";
				$this->fields->codice->setStyleProperty("border", "1px solid red");
			}
		}
		
		// Verifica campi obbligatori
		if ( $error_text == "" )
			foreach ( $this->mf as $mf ) {
				$value = $this->fields->$mf->getNewValue();
				if ( trim($value) === "" ) {
					$this->fields->$mf->setStyleProperty("border", "1px solid red");
					$error_text = "Compilare i campi obbligatori";
				}
			}


		if ( $error_text == "" ) 
		{
			if ( $this->new_pwd1->getNewValue() != "" ) 
				$this->fields->password->setValue( $this->new_pwd1->getNewValue() );

			if ( E3G_TIPO_GESTIONE == 'G' )  // GestiGAS
			{
				// Se si tratta di abilitazione di nuovo utente, allora invia notifica
				//TODO chiedere conferma invio			
                if ( !$this->newrecord and  
                     ( $this->fields->stato->getValue() == 0 ) and ( $this->fields->stato->getNewValue() == 1 ) )  // da "in attesa" ad "abilitato"
				{
					$nome    = $this->fields->nome->getNewValue();
					$cognome = $this->fields->cognome->getNewValue();
					$email   = $this->fields->email->getNewValue();
					
		  			$corpo = 
                        "Salve $nome $cognome,\n\n" .
		  		    	"avevi richiesto un nuovo account per accedere a $p4a->e3g_nome_sw" . ", " .
                        "il software gestionale di $p4a->e3g_azienda_rag_soc.\n\n" .
			  			"Ti comunichiamo che l'amministratore ha provveduto ad abilitarti.\n\n" .
			  			"Per l'accesso dovrai utilizzare l'indirizzo e-mail '$email' " .
			  			"e la password che ti era stata recapitata all'atto della tua richiesta.";
			  			
					if ( !e3g_invia_email( "$p4a->e3g_nome_sw: conferma abilitazione accesso",
		  					 $corpo, $email, "$nome $cognome" ) )
		        	{
		          		$this->message->setIcon( "error" );
		  				$this->message->setValue( "Si e' verificato un errore durante la spedizione della notifica di abilitazione." );
		  			}
		  			else {
						$this->message->setIcon( "info" );
						$this->message->setValue( "Notifica di abilitazione inviata all'utente $nome $cognome." );
		  			}
				}
			}
            
            $this->fields->email->setNewValue( trim( strtolower( $this->fields->email->getNewValue() ) ) );
            $this->fields->www->setNewValue( trim( strtolower( $this->fields->www->getNewValue() ) ) );
            $this->fields->ingredienti_escludi->setNewValue( ucfirst(strtolower(trim( $this->fields->ingredienti_escludi->getNewValue() ))) );

            parent::saveRow();  // Attenzione: dopo questo saveRow il record corrente è il primo del db_source        

            $this->newrecord = false;
//          $this->table->syncPageWithSource();
		}
		else {
			$this->message->setIcon("warning");
			$this->message->setValue( $error_text );
		}
	}

	
	// -------------------------------------------------------------------------
	function mastro_click()
	// -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		
		$this->ds_con->setTable($p4a->e3g_prefix."contconti");
		$this->ds_con->setWhere("mastro='".$this->fields->mastro->getNewValue()."'");		
		$this->ds_con->load();
		$this->ds_con->firstRow();
	}

	
    // Evidenzia le righe degli utenti disabilitati o in attesa
    // -------------------------------------------------------------------------
    function tableBeforeDisplay( $obj, $rows ) 
    // -------------------------------------------------------------------------
    {  
        for( $i=0; $i<count($rows); $i++ ) {
            // Campi visualizzati: array("descrizione", "email", "tipoutente", "stato", "n_login", "last_login") );
            switch ( $rows[$i]["stato"] ) {  // 0:In attesa 1:Abilitato 2:Disabilitato
                case 0:
                    $rows[$i]["descrizione"] = "<span style=\"border:1px solid blue\">" . $rows[$i]["descrizione"] . "</span>";
                    break;
                case 2: 
                    $rows[$i]["descrizione"] = "<strike>" . $rows[$i]["descrizione"] . "</strike>";
                    break;
            }  
        }  
        return $rows;  
    }  


    // -------------------------------------------------------------------------
    function tableAfterClick() 
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        $this->new_pwd1->setNewValue( "" );
        $this->new_pwd2->setNewValue( "" );
    }  


    // Esportazione righe tabella come CSV
    // -------------------------------------------------------------------------
    function bu_esporta_csvClick() 
    // -------------------------------------------------------------------------
    {  
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        if ( E3G_TIPO_GESTIONE == 'G' ) {
            $nome_file = "Utenti ";  // GestiGAS

            // MM_2009-01-26 Attenzione: causa probabile bug di p4a 2.2.3, non è possibile 
            // esportare le colonne in un ordine diverso da come sono presenti in tabella
            $colonne = array (
                "codice"        => "Codice",
                "nome"          => "Nome",
                "cognome"       => "Cognome",
                "descrizione"   => "Nome utente",
                "indirizzo"     => "Indirizzo",
                "localita"      => "Localita'",
                "cap"           => "CAP",
                "provincia"     => "Provincia",
                "telefono"      => "Telefono",
                "fax"      => "Fax/Cell",
                "email"         => "e-mail",
                "tipoutente"    => "Tipo accesso",
                "cf"            => "Codice fiscale",
                "stato"         => "Stato",
                "data_ins"      => "Data inserimento",
                "data_agg"      => "Data ultima modifica",
                "last_login"    => "Data ultimo accesso",
                "n_login"       => "Numero accessi",
                "data_nascita"  => "Data nascita",
                "luogo_nascita" => "Luogo nascita"
            );
        }
        else {
            $nome_file = "Clienti ";  // Equogest
            
            $colonne = null;  // Così le esporta tutte
        }

        e3g_db_source_exportToCsv( $this->ds_anag, $colonne, $nome_file . $p4a->e3g_azienda_rag_soc );
    }
}
?>