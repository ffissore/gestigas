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


class multigestione extends P4A_Mask
{
	var $newrecord = false;

	function multigestione()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();

		$this->SetTitle( "Multi-gestione" );

		// DB source principale
		$this->build( "p4a_db_source", "ds_aziende" );
		$this->ds_aziende->setTable( "_aziende" );
		$this->ds_aziende->setPk( "id_azienda" );
		$this->ds_aziende->addOrder( "last_login", "DESC" );
		$this->ds_aziende->setPageLimit( $p4a->e3g_utente_db_source_page_limit );
		$this->ds_aziende->load();
		$this->ds_aziende->firstRow();

		$this->setSource( $this->ds_aziende );
		
		while ( $field =& $this->fields->nextItem() ) 
			$field->label->setWidth( 130 );

			
		// Griglia iniziale ----------------------------------------------------
		$table =& $this->build( "p4a_table", "table" );
 		$table->setWidth( E3G_TABLE_WIDTH );
		$table->setSource($this->ds_aziende);
		$table->setVisibleCols( array("rag_soc", "dbver", "admin_desc",
			"n_login", "n_clienti", "n_fornitori", "n_articoli", "n_doc_ord_fornitori", "n_doc_cons_utenti", "last_login") );
		
		$table->cols->rag_soc->setLabel( "Ragione sociale" );
		$table->cols->dbver->setLabel( "Vers. DB" );
		$table->cols->admin_desc->setLabel( "Admin" );
        $table->cols->n_login->setLabel( "N. accessi" );
		$table->cols->n_clienti->setLabel( (E3G_TIPO_GESTIONE == 'G' ? "N. utenti attivi" : "N. clienti attivi") );
		$table->cols->n_fornitori->setLabel( "N. fornitori" );
		$table->cols->n_articoli->setLabel( "N. articoli" );
        $table->cols->n_doc_ord_fornitori->setLabel( "N. ord. fornitore" );
        $table->cols->n_doc_cons_utenti->setLabel( "N. cons. utente" );
        $table->cols->last_login->setLabel( "Data ultimo accesso" );

//      $table->cols->rag_soc-> per differenza
		$table->cols->dbver->setWidth( 40 );
//      $table->cols->admin_desc-> per differenza
        $table->cols->n_login->setWidth( 60 );
		$table->cols->n_clienti->setWidth( 50 );
		$table->cols->n_fornitori->setWidth( 50 );
		$table->cols->n_articoli->setWidth( 50 );
        $table->cols->n_doc_ord_fornitori->setWidth( 50 );
        $table->cols->n_doc_cons_utenti->setWidth( 50 );
        $table->cols->last_login->setWidth( 130 );

	
		// Dati anagrafici -----------------------------------------------------
		$this->fields->rag_soc->setLabel( "Ragione Sociale" );
		$this->fields->localita->setLabel( "Località" );
		$this->fields->web->setLabel( "Sito web" );
		$this->fields->email->setLabel( "Indirizzo e-mail" );

		$this->fields->rag_soc->setWidth( 400 );
		$this->fields->provincia->setWidth( 200 );
		$this->fields->web->setWidth( 400 );
		$this->fields->email->setWidth( 300 );

		// Fieldset
		$this->build( "p4a_fieldset", "fs_anagrafica" );
		$this->fs_anagrafica->setWidth( E3G_FIELDSET_DATI_WIDTH );
		$this->fs_anagrafica->setTitle( "Dati anagrafici" );
		$this->fs_anagrafica->anchor( $this->fields->rag_soc );
		$this->fs_anagrafica->anchor( $this->fields->provincia );
		$this->fs_anagrafica->anchor( $this->fields->web );
		$this->fs_anagrafica->anchor( $this->fields->email );

		
		// Parametri -----------------------------------------------------------
		$this->fields->prefix->setLabel( "Prefisso" );
		$this->fields->dbver->setLabel( "Versione database" );

		$this->fields->prefix->setWidth( 150 );
		$this->fields->dbver->setWidth( 100 );
		
		// Fieldset
		$this->build( "p4a_fieldset", "fs_parametri" );
		$this->fs_parametri->setWidth( E3G_FIELDSET_DATI_WIDTH );
		$this->fs_parametri->setTitle( "Parametri" );
		$this->fs_parametri->anchor( $this->fields->prefix );
		$this->fs_parametri->anchorLeft( $this->fields->dbver );


		// Amministratore ------------------------------------------------------
		$this->fields->admin_desc->setLabel( "Nome" );
		$this->fields->admin_email->setLabel( "Indirizzo e-mail" );

		$this->fields->admin_desc->setWidth( 200 );
		$this->fields->admin_email->setWidth( 300 );

		$this->fields->admin_desc->disable();
		$this->fields->admin_email->disable();

		// Fieldset
		$this->build( "p4a_fieldset", "fs_admin" );
		$this->fs_admin->setWidth( E3G_FIELDSET_DATI_WIDTH );
		$this->fs_admin->setTitle( "Amministratore" );
		$this->fs_admin->anchor( $this->fields->admin_desc );
		$this->fs_admin->anchorLeft( $this->fields->admin_email );

		
		// Statistiche ---------------------------------------------------------
        $this->fields->n_login->setLabel( "N. accessi" );
		$this->fields->n_clienti->setLabel( "Numero " . (E3G_TIPO_GESTIONE == 'G' ? "utenti attivi" : "clienti attivi") );
		$this->fields->n_fornitori->setLabel( "Numero fornitori" );
		$this->fields->n_articoli->setLabel( "Numero articoli" );
        $this->fields->n_doc_ord_fornitori->setLabel( "N. ordini a fornitore" );
        $this->fields->n_doc_cons_utenti->setLabel( "N. consegne ad utente" );
		$this->fields->last_login->setLabel( "Data ultimo accesso" );
		$this->fields->data_inizio->setLabel( "Data inserimento" );
		$this->fields->data_agg->setLabel( "Data aggiornamento" );

        $this->fields->n_login->setWidth( 50 );
		$this->fields->n_clienti->setWidth( 50 );
		$this->fields->n_fornitori->setWidth( 50 );
		$this->fields->n_articoli->setWidth( 50 );
        $this->fields->n_doc_ord_fornitori->setWidth( 50 );
        $this->fields->n_doc_cons_utenti->setWidth( 50 );
		$this->fields->last_login->setWidth( 125 );
		$this->fields->data_inizio->setWidth( 125 );
		$this->fields->data_agg->setWidth( 125 );
				
        $this->fields->n_login->disable();
		$this->fields->n_clienti->disable();
		$this->fields->n_fornitori->disable();
		$this->fields->n_articoli->disable();
        $this->fields->n_doc_ord_fornitori->disable();
        $this->fields->n_doc_cons_utenti->disable();
		$this->fields->last_login->disable();
        $this->fields->data_inizio->disable();
        $this->fields->data_agg->disable();
				
		// Fieldset
		$this->build( "p4a_fieldset", "fs_stat" );
		$this->fs_stat->setWidth( E3G_FIELDSET_DATI_WIDTH );
		$this->fs_stat->setTitle( "Statistiche" );
        $this->fs_stat->anchor( $this->fields->n_login );

		$this->fs_stat->anchor( $this->fields->n_clienti );
		$this->fs_stat->anchorLeft( $this->fields->n_fornitori );
		$this->fs_stat->anchorLeft( $this->fields->n_articoli );

        $this->fs_stat->anchor( $this->fields->n_doc_ord_fornitori );
        $this->fs_stat->anchorLeft( $this->fields->n_doc_cons_utenti );

        $this->fs_stat->anchor( $this->fields->last_login );
		$this->fs_stat->anchorLeft( $this->fields->data_inizio );
		$this->fs_stat->anchorLeft( $this->fields->data_agg );


		// Message per eventuale segnalazione di errori ------------------------
		$this->build ("p4a_message", "message" );
		$this->message->setWidth( 650 );

		//Mandatory Fields -----------------------------------------------------
	    $this->mf = array( "rag_soc", "dbver", "prefix", "data_inizio" );
		foreach( $this->mf as $mf ) 
			$this->fields->$mf->label->setFontWeight("bold");

		// Toolbar -------------------------------------------------------------
		$this->build( "p4a_standard_toolbar", "toolbar" );
   		$this->toolbar->buttons->new->setInvisible();  // PROVVISORIO: dovrà gestire la creazione di una nuova gestione
		$this->toolbar->buttons->delete->setInvisible();  // PROVVISORIO: dovrà gestire l'eliminazione completa di una gestione
		$this->toolbar->setMask( $this );


		// Frame principale ----------------------------------------------------
		$frm=& $this->build( "p4a_frame", "frm" );
		$frm->setWidth( E3G_MAIN_FRAME_WIDTH );
	
		$frm->anchorCenter( $this->table );
		$frm->anchorCenter( $this->message );
		$frm->anchorCenter( $this->fs_anagrafica );
		$frm->anchorCenter( $this->fs_parametri );
		$frm->anchorCenter( $this->fs_admin );
		$frm->anchorCenter( $this->fs_stat );

		e3g_scrivi_footer( $this, $frm );
		
		// Display
		$this->display( "menu", $p4a->menu );
		$this->display( "top", $this->toolbar );
		$this->display( "main", $frm );
	}

	
	function saveRow()
	{
		$db =& p4a_db::singleton();

		$errors = array();

		foreach ( $this->mf as $field ) {
			if ( strlen($this->fields->$field->getNewValue()) == 0 ) 
				$errors[] = $field;
		}

		if ( sizeof($errors) > 0 ) {
			$this->message->setValue( "Compilare i campi obbligatori." );

			foreach ( $errors as $field ) 
				$this->fields->$field->setStyleProperty( "border", "1px solid red" );
		} 
		else {
			if ( $this->newrecord ) {
				$maxid = $db->queryOne( "SELECT MAX(id_azienda) FROM _aziende" );
				if ( is_numeric($maxid) )
					$maxid++;
				else 
					$maxid=1;
				$this->fields->id_azienda->setNewValue( $maxid );
			}

			parent::saveRow();
			$this->newrecord = false;
		}
	}

	
    // -------------------------------------------------------------------------
	function main()
    // -------------------------------------------------------------------------
	{
        $db =& p4a_db::singleton();

		$this->update_aziende();
		
		// Se non ci sono record, allora la finestra si predispone
		// in inserimento, ma bisogna generare l'evento newRow()
		$n = $db->queryOne( "SELECT COUNT(*) FROM _aziende" );
		if ( $n == 0 )
			$this->newRow();
			
		parent::main();

		foreach ( $this->mf as $field ) 
			$this->fields->$field->unsetStyleProperty( "border" );
	}


    // -------------------------------------------------------------------------
	function update_aziende()
    // -------------------------------------------------------------------------
	{
        $p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		$this->build("p4a_db_source", "ds_aziende_bis");
		$this->ds_aziende_bis->setTable("_aziende");
		$this->ds_aziende_bis->setPk("id_azienda");
		$this->ds_aziende_bis->load();
		$this->ds_aziende_bis->firstRow();
		
		for ( $n_riga = 1; $n_riga <= $this->ds_aziende_bis->getNumRows(); $n_riga++ ) {
			// Dati amministratore
			if ( E3G_TIPO_GESTIONE == 'G' )
				$tipocfa = "C";
			else
				$tipocfa = "U";

			$admin = $db->queryRow(
				"SELECT idanag, descrizione, email " .
				" FROM " . $this->ds_aziende_bis->fields->prefix->getValue() . "anagrafiche " .
				" WHERE tipocfa = '" . $tipocfa . "' AND tipoutente = 'AS' AND stato = 1" );
			
            if ( !$admin ) {
            	// Situazione anomala: manca l'amministratore
                $admin["idanag"]      = -1;
                $admin["descrizione"] = "!! MANCA ADMIN !!";
                $admin["email"]       = "";
            }   

			// Statistiche
			$n_clienti = $db->queryOne(
				"SELECT COUNT( * ) AS n " .
				" FROM " . $this->ds_aziende_bis->fields->prefix->getValue() . "anagrafiche " .
				" WHERE tipocfa = 'C' AND stato = 1" );
			$n_fornitori = $db->queryOne(
				"SELECT COUNT( * ) AS n " .
				" FROM " . $this->ds_aziende_bis->fields->prefix->getValue() . "anagrafiche " .
				" WHERE tipocfa = 'F' AND stato = 1" );
			$n_articoli = $db->queryOne(
				"SELECT COUNT( * ) AS n " .
				" FROM " . $this->ds_aziende_bis->fields->prefix->getValue() . "articoli " .
                " WHERE stato = 1" );
				
            $n_doc_ord_fornitori = $db->queryOne(
                "SELECT COUNT( * ) AS n " .
                " FROM " . $this->ds_aziende_bis->fields->prefix->getValue() . "doct " .
                " WHERE codtipodoc = '" . $p4a->e3g_azienda_gg_cod_doc_ordine . "'" );
            $n_doc_cons_utenti = $db->queryOne(
                "SELECT COUNT( * ) AS n " .
                " FROM " . $this->ds_aziende_bis->fields->prefix->getValue() . "doct " .
                " WHERE codtipodoc = '" . $p4a->e3g_azienda_gg_cod_doc_ordine_fam . "'" );

			$login = $db->queryRow(
				"SELECT SUM( n_login ) AS n, MAX( last_login ) AS last  " .
				" FROM " . $this->ds_aziende_bis->fields->prefix->getValue() . "anagrafiche ");
			
			// Aggiornamento _aziende
			$db->query(  
				"UPDATE _aziende " .
				"   SET admin_id_anagrafica =  " . $admin["idanag"] . ", " .
				"       admin_desc          = '" . $admin["descrizione"] . "', " .
				"       admin_email         = '" . $admin["email"] . "', " .
				"       n_clienti           =  " . (integer)$n_clienti . ", " .
				"       n_fornitori         =  " . (integer)$n_fornitori . ", " .
				"       n_articoli          =  " . (integer)$n_articoli . ", " .
                "       n_doc_ord_fornitori =  " . (integer)$n_doc_ord_fornitori . ", " .
                "       n_doc_cons_utenti   =  " . (integer)$n_doc_cons_utenti . ", " .
				"       n_login             =  " . (integer)$login["n"] . ", " .
				"       last_login          = '" . $login["last"] . "'" . 
				" WHERE prefix = '" . $this->ds_aziende_bis->fields->prefix->getValue() . "'" );

			$this->ds_aziende_bis->nextRow();
		}
	}
	
}

?>