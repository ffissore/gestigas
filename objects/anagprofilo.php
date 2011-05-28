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


class anagprofilo extends P4A_Mask
{
	var $newrecord = 0;
	
    // -------------------------------------------------------------------------
	function anagprofilo()
    // -------------------------------------------------------------------------
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();

		$this->setTitle("Profilo personale");
		$this->setIcon( "personal" );
	
		// Sorgente dati principale
		$this->build("p4a_db_source", "ds_anagr");
		$this->ds_anagr->setTable($p4a->e3g_prefix."anagrafiche");
		$this->ds_anagr->setWhere("codice ='$p4a->e3g_utente_codice'");
		$this->ds_anagr->setPk("idanag");
		$this->ds_anagr->load();
		$this->setSource($this->ds_anagr);
		$this->ds_anagr->firstRow();
				
		// Fields properties
		$fields =& $this->fields;

		// Campi Obbligatori Fields
	    $this->mf = array( "codice", "descrizione", "email" );
		foreach( $this->mf as $mf ) 
			$fields->$mf->label->setFontWeight( "bold" );

		// Larghezza fields
		while ( $field =& $this->fields->nextItem() ) 
			$field->label->setWidth(130);

		
		// Toolbar
		$this->build("p4a_actions_toolbar", "toolbar");
		$this->toolbar->setMask( $this );

		// Message
		$message =& $this->build( "p4a_message", "message" );
		$message->setWidth( 400 );


		// ------------------------------------------------- Campi di anagrafica
 		$this->fields->codice->disable(); 
		$this->fields->localita->setLabel( "Localita'" );
                $this->fields->fax->setLabel( "Telefono cellulare" );
		
        $this->fields->descrizione->setWidth( 250 );
        $this->fields->indirizzo->setWidth( 250 );

		//Fieldset con l'elenco dei campi ANAGRAFICA
		$fs_anagrafica =& $this->build( "p4a_fieldset", "fs_anagrafica" );
		$fs_anagrafica->setTitle( "Dati anagrafici" );

 		$fs_anagrafica->anchor( $this->fields->codice );
		$fs_anagrafica->anchor( $this->fields->Titolo );
		$fs_anagrafica->anchor( $this->fields->nome );
		$fs_anagrafica->anchorLeft( $this->fields->cognome );
		$fs_anagrafica->anchor( $this->fields->descrizione );
		$fs_anagrafica->anchor( $this->fields->indirizzo );
		$fs_anagrafica->anchor( $this->fields->cap );
 		$fs_anagrafica->anchorLeft( $this->fields->localita );
 		$fs_anagrafica->anchorLeft( $this->fields->provincia );
 		$fs_anagrafica->anchor( $this->fields->telefono );
 		$fs_anagrafica->anchorLeft( $this->fields->fax );
		
		$fs_anagrafica->setWidth( E3G_FIELDSET_DATI_WIDTH );


        // ----------------------------------------------- Dati fiscali GestiGAS
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

        // ---------------------------------------------------------- Altri dati
		$this->build( "p4a_db_source", "ds_luoghi_cons" );
		$this->ds_luoghi_cons->setTable( "_luoghi_cons" );
		$this->ds_luoghi_cons->setWhere( "prefix = '" . $p4a->e3g_prefix . "'" );
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
        $fs_altri_dati->anchor( $this->fields->note, "157px" );
        $fs_altri_dati->anchor( $this->fields->filtro_ingredienti );
        $fs_altri_dati->anchor( $this->fields->ingredienti_escludi, "157px" );
        $fs_altri_dati->anchor( $this->fields->db_source_page_limit );
		
		
		// -------------------------------------------------- Dati per l'accesso				
		$this->fields->email->setWidth(200);

		// Sorgente dati per il tipo utente
		$this->build( "p4a_db_source", "ds_tipo" );
		$this->ds_tipo->setTable( "_login_tipo_utente" );
		$this->ds_tipo->setWhere( "codice IN ('A', 'AS', 'R', 'U')" );
		$this->ds_tipo->setPk( "codice" );
		$this->ds_tipo->load();
		$this->fields->tipoutente->setLabel( "Tipo accesso" );
		$this->fields->tipoutente->setWidth( 200 );
		$this->fields->tipoutente->setType( "select");
		$this->fields->tipoutente->setSourceValueField( "codice" );
		$this->fields->tipoutente->setSourceDescriptionField( "descrizione" );
		$this->fields->tipoutente->setSource( $this->ds_tipo );
		$this->fields->tipoutente->disable();

		// Campo per la modifica della password
		$new_pwd1 =& $this->build( "p4a_field", "new_pwd1" );
		$new_pwd1->setLabel( "Nuova password" );
		$new_pwd1->setType( "password" );
        $new_pwd1->label->setWidth( 140 );
		$new_pwd1->setWidth( 200 );
        $new_pwd1->setValue( "" );

		$new_pwd2 =& $this->build( "p4a_field", "new_pwd2" );
		$new_pwd2->setLabel( "Verifica nuova password" );
		$new_pwd2->setType( "password" );
        $new_pwd2->label->setWidth( 140 );
		$new_pwd2->setWidth( 200 );
        $new_pwd2->setValue( "" );

		$this->fields->n_login->setLabel( "N. accessi" );
		$this->fields->n_login->disable();
		$this->fields->last_login->setLabel( "Ultimo accesso" );
		$this->fields->last_login->disable();

		//Fieldset con l'elenco dei campi dati per l'accesso
		$fs_accesso =& $this->build( "p4a_fieldset", "fs_accesso" );
		$fs_accesso->setTitle( "Dati per l'accesso" );
		$fs_accesso->setWidth( E3G_FIELDSET_DATI_WIDTH );
		
		$fs_accesso->anchor( $this->fields->email );
		$fs_accesso->anchor( $this->fields->tipoutente );
		$fs_accesso->anchor( $this->new_pwd1 );
		$fs_accesso->anchor( $this->new_pwd2 );
		$fs_accesso->anchor( $this->fields->n_login );
		$fs_accesso->anchorLeft( $this->fields->last_login );


		// ---------------------------------------------------------------- Date		
		$this->fields->data_ins->disable();
		$this->fields->data_agg->disable();
		$this->fields->data_ins->setLabel("Inserimento");
		$this->fields->data_agg->setLabel("Ultima modifica");

		$fs_date=& $this->build("p4a_fieldset", "fs_date");
		$fs_date->setTitle("Date");
		$fs_date->anchor($this->fields->data_ins);
		$fs_date->anchorLeft($this->fields->data_agg);
		$fs_date->setWidth( E3G_FIELDSET_DATI_WIDTH );


		// ---------------------------------------------------- Frame PRINCIPALE
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(E3G_MAIN_FRAME_WIDTH);

		$frm->anchor($message);
  		$frm->anchor($fs_anagrafica);
        $frm->anchor($fs_dati_fiscali);
  		$frm->anchor($fs_altri_dati);
  		$frm->anchor($fs_accesso);
		$frm->anchor($fs_date);

		e3g_scrivi_footer( $this, $frm );

		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
	}


    // -------------------------------------------------------------------------
	function main()
    // -------------------------------------------------------------------------
	{
		parent::main();

        foreach( $this->mf as $mf )
            $this->fields->$mf->unsetStyleProperty( "border" );
        
        $this->new_pwd1->unsetStyleProperty( "border" );
        $this->new_pwd2->unsetStyleProperty( "border" );
        $this->fields->cf->unsetStyleProperty( "border" );
        $this->fields->db_source_page_limit->unsetStyleProperty( "border" );
        $this->fields->nome->unsetStyleProperty( "border" );
        $this->fields->cognome->unsetStyleProperty( "border" );
        $this->fields->indirizzo->unsetStyleProperty( "border" );
        $this->fields->cap->unsetStyleProperty( "border" );
        $this->fields->localita->unsetStyleProperty( "border" );
        $this->fields->provincia->unsetStyleProperty( "border" );
        $this->fields->telefono->unsetStyleProperty( "border" );
        $this->fields->fax->unsetStyleProperty( "border" );
        $this->fields->data_nascita->unsetStyleProperty( "border" );
        $this->fields->luogo_nascita->unsetStyleProperty( "border" );
        $this->fields->cf->unsetStyleProperty( "border" );
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

		$error_text = "";
		
		if ( $this->fields->descrizione->getNewValue() == "" ) {
			$error_text = "Compilare la descrizione, ad esempio con nome e cognome.";
			$this->fields->descrizione->setStyleProperty( "border", "1px solid red" );
		}
		elseif ( !e3g_email_valido( $this->fields->email->getNewValue() ) ) {
			$error_text = "L'indirizzo e-mail indicato non sembra essere valido.";
			$this->fields->email->setStyleProperty( "border", "1px solid red" );
		}
		elseif ( $this->new_pwd1->getNewValue() != $this->new_pwd2->getNewValue() ) {
			$error_text = "Le due password non coincidono, prova a riscriverle.";
			$this->new_pwd1->setStyleProperty( "border", "1px solid red" );
			$this->new_pwd2->setStyleProperty( "border", "1px solid red" );
		}
        // Verifica correttezza codice fiscale
        elseif ( $this->fields->cf->getNewValue() <> '' and !CodiceFiscaleEsatto( $this->fields->cf->getNewValue() ) ) {
            // indirizzo e-mail non valido
            $error_text = "Il codice fiscale non e' corretto.";
            $this->fields->cf->setStyleProperty( "border", "1px solid red" );
        }
        // Tabelle, numero di righe per pagina: deve essere compreso tra 5 e 50
        elseif ( $this->fields->db_source_page_limit->getNewValue() < 5 or $this->fields->db_source_page_limit->getNewValue() > 50 ) {
            $error_text = "Il numero di righe per pagina deve essere compreso tra 5 e 50.";
            $this->fields->db_source_page_limit->setStyleProperty( "border", "1px solid red" );
        }
        elseif ( $this->fields->nome->getNewValue() == '' ) {
            $error_text = "Scrivere il nome.";
            $this->fields->nome->setStyleProperty( "border", "1px solid red" );
        }
	elseif ( $this->fields->cognome->getNewValue() == '' ) {
            $error_text = "Scrivere il cognome.";
            $this->fields->cognome->setStyleProperty( "border", "1px solid red" );
        }
	elseif ( $this->fields->indirizzo->getNewValue() == '' ) {
            $error_text = "Scrivere l'indirizzo.";
            $this->fields->indirizzo->setStyleProperty( "border", "1px solid red" );
        }
	elseif ( $this->fields->cap->getNewValue() == '' ) {
            $error_text = "Scrivere il CAP.";
            $this->fields->cap->setStyleProperty( "border", "1px solid red" );
        }
	elseif ( $this->fields->localita->getNewValue() == '' ) {
            $error_text = "Scrivere la localita.";
            $this->fields->localita->setStyleProperty( "border", "1px solid red" );
        }
	elseif ( $this->fields->provincia->getNewValue() == '' ) {
            $error_text = "Scrivere la provincia.";
            $this->fields->provincia->setStyleProperty( "border", "1px solid red" );
        }
	elseif ( $this->fields->fax->getNewValue() == '' ) {
            $error_text = "Scrivere un numero di telefono cellulare.";
            $this->fields->fax->setStyleProperty( "border", "1px solid red" );
        }
	elseif ( $this->fields->data_nascita->getNewValue() == '' ) {
            $error_text = "Scrivere la data di nascita.";
            $this->fields->data_nascita->setStyleProperty( "border", "1px solid red" );
        }
	elseif ( $this->fields->luogo_nascita->getNewValue() == '' ) {
            $error_text = "Scrivere il luogo di nascita.";
            $this->fields->luogo_nascita->setStyleProperty( "border", "1px solid red" );
        }
	elseif ( $this->fields->cf->getNewValue() == '' ) {
            $error_text = "Scrivere il codice fiscale.";
            $this->fields->cf->setStyleProperty( "border", "1px solid red" );
        }

		if ( $error_text == "" ) {
			if ( $this->new_pwd1->getNewValue() != "" ) 
				$this->fields->password->setValue( $this->new_pwd1->getNewValue() );

            $this->fields->email->setNewValue( trim( strtolower( $this->fields->email->getNewValue() ) ) );
            $this->fields->www->setNewValue( trim( strtolower( $this->fields->www->getNewValue() ) ) );
            $this->fields->ingredienti_escludi->setNewValue( ucfirst(strtolower(trim( $this->fields->ingredienti_escludi->getNewValue() ))) );

			parent::saveRow();

			e3g_update_var_utente( $this->fields->idanag->getValue() );
			$this->maskClose( "anagprofilo" );
		}
		else
			$this->message->setValue( $error_text );
	}

}

?>