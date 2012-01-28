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


class preferenze extends P4A_Mask
{
    // -------------------------------------------------------------------------
	function preferenze()
    // -------------------------------------------------------------------------
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();


		$this->SetTitle( "Preferenze $p4a->e3g_nome_sw" );
		$this->setIcon( "misc" );

		
		// Sorgente dati principale
		$this->build( "p4a_db_source", "ds_azienda" );
		$this->ds_azienda->setTable( "_aziende" );
		$this->ds_azienda->setWhere( "prefix = '$p4a->e3g_prefix'" );
		$this->ds_azienda->setPk( "prefix" );
		$this->ds_azienda->load();
		$this->ds_azienda->firstRow();

		$this->setSource( $this->ds_azienda );

		// Campi obbligatori
	    $this->mf = array("rag_soc", "n_decimali_prezzi", "tipo_gestione_prezzi");
		foreach($this->mf as $mf) 
			$this->fields->$mf->label->setFontWeight("bold");
		
		while ($field =& $this->fields->nextItem()) 
			$field->label->setWidth(110);
			
		$fields =& $this->fields;


		$this->build("p4a_actions_toolbar", "toolbar");
		$this->toolbar->setMask($this);

		// ------------------------ Message per eventuale segnalazione di errori
		$this->build("p4a_message", "message");
		$this->message->setWidth("650");

		// ----------------------------------------- Parametri Equogest/GestiGAS 
		$fields->show_new_account->setLabel( "Login: mostra richiesta account" );
        $fields->show_new_account->label->setWidth( 180 );
        $fields->show_new_account->setType( "checkbox" );

        $fields->mailing_list->setLabel( "Gestione mailing-list" );
        $fields->mailing_list->label->setWidth( 180 );
        $fields->mailing_list->setType( "checkbox" );

		$fields->gestione_luoghi_cons->setLabel( "Gestione luoghi di consegna" );
        $fields->gestione_luoghi_cons->label->setWidth( 180 );
        $fields->gestione_luoghi_cons->setType( "checkbox" );

        $fields->gestione_cassa->setLabel( "Gestione cassa comune" );
        $fields->gestione_cassa->label->setWidth( 180 );
        $fields->gestione_cassa->setType( "checkbox" );

        $fields->acquista_se_credito_insufficiente->setLabel( "Acquisto se credito insufficiente" );
        $fields->acquista_se_credito_insufficiente->setTooltip( "Permetti l'acquisto anche agli utenti con credito insufficiente" );
        $fields->acquista_se_credito_insufficiente->label->setWidth( 360 );
        $fields->acquista_se_credito_insufficiente->setType( "checkbox" );

		$fields->etichette_path->setLabel( "Percorso etichette" );
		$fields->etichette_path->label->setWidth( 180 );
		$fields->etichette_path->setWidth( 400 );

        $fields->path_logo->setLabel( "Path logo" );
		$fields->path_logo->label->setWidth( 180 );
		$fields->path_logo->setWidth( 400 );


		// Fieldset
		$this->build("p4a_fieldset", "fs_parametri");
		$this->fs_parametri->setWidth( E3G_FIELDSET_DATI_WIDTH );
		$this->fs_parametri->setTitle("Parametri $p4a->e3g_nome_sw" );
        $this->fs_parametri->anchor( $fields->show_new_account );
		if ( E3G_TIPO_GESTIONE == 'G' ) {
            $this->fs_parametri->anchor( $fields->mailing_list );
			$this->fs_parametri->anchor( $fields->gestione_luoghi_cons );
            $this->fs_parametri->anchor( $fields->gestione_cassa );
            $this->fs_parametri->anchorLeft( $fields->acquista_se_credito_insufficiente );
		}
		else {
			$this->fs_parametri->anchor( $fields->etichette_path );
		}
		$this->fs_parametri->anchor( $fields->path_logo );

		// ------------------------------------------ Gestione prezzi (GestiGAS)
        $fields->n_decimali_prezzi->setLabel("N. decimali");
		$fields->n_decimali_prezzi->label->setWidth( 180 );
        $fields->n_decimali_prezzi->setWidth(100);

        $fields->ordine_minimo->setLabel("Ordine minimo [euro]");
        $fields->ordine_minimo->setTooltip( "Persuade gli utenti ad ordinare per un importo superiore a quello specificato" );
		$fields->ordine_minimo->label->setWidth( 180 );
        $fields->ordine_minimo->setWidth(100);

        $fields->tipo_gestione_prezzi->setLabel("Tipo gestione prezzi");
		$fields->tipo_gestione_prezzi->label->setWidth( 180 );
        $fields->tipo_gestione_prezzi->setWidth(400);
        $fields->tipo_gestione_prezzi->setType("select");
        // Array per il tipo gestione prezzi        
        $a_tipo_gest_prezzi = array(); 
        $a_tipo_gest_prezzi[] = array("id" => "0", "desc" => "Prezzo vendita utente = prezzo acquisto fornitore");
        $a_tipo_gest_prezzi[] = array("id" => "1", "desc" => "Maggiorazione fissa per ogni ordine");
        $a_tipo_gest_prezzi[] = array("id" => "2", "desc" => "Maggiorazione percentuale sul prezzo d'acquisto");
        $as_tipo_gest_prezzi =& $this->build("p4a_array_source", "as_tipo_gest_prezzi"); 
        $as_tipo_gest_prezzi->load($a_tipo_gest_prezzi); 
        $as_tipo_gest_prezzi->setPk("id"); 
        $fields->tipo_gestione_prezzi->setSourceValueField('id');
        $fields->tipo_gestione_prezzi->setSourceDescriptionField('desc');
        $fields->tipo_gestione_prezzi->setSource($as_tipo_gest_prezzi);

        $fields->mostra_prezzo_sorgente->setLabel("Mostra prezzo sorgente");
		$fields->mostra_prezzo_sorgente->label->setWidth( 180 );
        $fields->mostra_prezzo_sorgente->setType("checkbox");

        $fields->prezzi_mag_fissa->setLabel("Maggiorazione fissa [euro]");
		$fields->prezzi_mag_fissa->label->setWidth( 180 );
        $fields->prezzi_mag_fissa->setWidth(100);
        $fields->prezzi_mag_fissa->data_field->setType("decimal");

        $fields->prezzi_mag_perc->setLabel("Maggiorazione percentuale [%]");
		$fields->prezzi_mag_perc->label->setWidth( 180 );
        $fields->prezzi_mag_perc->setWidth(100);
        $fields->prezzi_mag_perc->data_field->setType("decimal");


		// Fieldset
		$this->build("p4a_fieldset", "fs_gg_prezzi");
		$this->fs_gg_prezzi->setWidth( E3G_FIELDSET_DATI_WIDTH );
		$this->fs_gg_prezzi->setTitle("Prezzi");
		$this->fs_gg_prezzi->anchor( $fields->n_decimali_prezzi);
		$this->fs_gg_prezzi->anchor( $fields->ordine_minimo);
		$this->fs_gg_prezzi->anchor( $fields->tipo_gestione_prezzi);
		$this->fs_gg_prezzi->anchor( $fields->mostra_prezzo_sorgente);
		$this->fs_gg_prezzi->anchor( $fields->prezzi_mag_fissa );
		$this->fs_gg_prezzi->anchorLeft( $fields->prezzi_mag_perc);

		
		// ----------------------------------------------------- Dati anagrafici 
		$fields->rag_soc->setLabel("Ragione sociale");
        $fields->rag_soc->setWidth(400);

        $fields->indirizzo->setLabel("Indirizzo");
        $fields->indirizzo->setWidth(400);

		$fields->cap->setLabel("CAP");
        $fields->cap->setWidth(50);

		$fields->localita->setLabel("Localita'");
        $fields->localita->setWidth(200);

		$fields->provincia->setLabel("Provincia");
		$fields->provincia->setWidth(200);


		// Fieldset
		$this->build("p4a_fieldset", "fs_anagrafica");
		$this->fs_anagrafica->setWidth( E3G_FIELDSET_DATI_WIDTH );
		$this->fs_anagrafica->setTitle("Dati anagrafici");
		$this->fs_anagrafica->anchor($fields->rag_soc);
		$this->fs_anagrafica->anchor($fields->indirizzo);
		$this->fs_anagrafica->anchor($fields->cap);
		$this->fs_anagrafica->anchorLeft($fields->localita);
		$this->fs_anagrafica->anchorLeft($fields->provincia);


		// ------------------------------------------------------------ Recapiti
		$fields->telefono->setLabel("Telefono");
        $fields->telefono->setWidth(200);

		$fields->fax->setLabel("Fax");
        $fields->fax->setWidth(200);

		$fields->web->setLabel("Sito web");
        $fields->web->setWidth(400);

		$fields->email->setLabel("Indirizzo e-mail");
		$fields->email->setWidth(400);


		// Fieldset
		$this->build("p4a_fieldset", "fs_recapiti");
		$this->fs_recapiti->setWidth( E3G_FIELDSET_DATI_WIDTH );
		$this->fs_recapiti->setTitle("Recapiti");
		$this->fs_recapiti->anchor($fields->telefono);
		$this->fs_recapiti->anchor($fields->fax);
		$this->fs_recapiti->anchor($fields->web);
		$this->fs_recapiti->anchor($fields->email);
		

		// ---------------------------------------------------------- Altri dati		
        $fields->piva->setLabel("Partita IVA");
        $fields->piva->setWidth(100);

        $fields->codice_fiscale->setLabel("Codice Fiscale");
        $fields->codice_fiscale->setWidth(200);

		$fields->conto_corrente->setLabel("Conto Corrente");
        $fields->conto_corrente->setWidth(200);

		$fields->cin->setLabel("CIN");
        $fields->cin->setWidth(50);

		$fields->banca->setLabel("Banca");
        $fields->banca->setWidth(400);

		$fields->agenzia->setLabel("Agenzia");
        $fields->agenzia->setWidth(400);

		$fields->abi->setLabel("ABI");
        $fields->abi->setWidth(100);

		$fields->cab->setLabel("CAB");
        $fields->cab->setWidth(100);

		$fields->iban->setLabel("IBAN");
        $fields->iban->setWidth(200);

        $fields->tipo_documento->setLabel("Tipo documento");
        $fields->tipo_documento->setWidth(100);
        $fields->tipo_documento->setType('select');
        // Array per il tipo documenti in stampa: PDF oppure OpenOffice 
        $a_tipo_doc = array(); 
        $a_tipo_doc[] = array("id" => "PDF", "desc" => "PDF");
        $a_tipo_doc[] = array("id" => "ODT", "desc" => "OpenOffice");
        $as_tipo_doc =& $this->build("p4a_array_source", "as_tipo_doc"); 
        $as_tipo_doc->load($a_tipo_doc); 
        $as_tipo_doc->setPk("id"); 
        $fields->tipo_documento->setSourceValueField('id');
        $fields->tipo_documento->setSourceDescriptionField('desc');
        $fields->tipo_documento->setSource($as_tipo_doc);

        $fields->path_documento->setLabel("Percorso Documenti");
        $fields->path_documento->setWidth(400);
		
        $fields->eg_cod_doc_scontrino->setLabel("Codice doc. scontrino");
        $fields->eg_cod_doc_scontrino->setWidth(50);

        $fields->anno_contabile->setLabel("Anno contabile");
        $fields->anno_contabile->label->setWidth(180);

		
		// Fieldset
		$this->build("p4a_fieldset", "fs_altri_dati");
		$this->fs_altri_dati->setWidth( E3G_FIELDSET_DATI_WIDTH );
		$this->fs_altri_dati->setTitle("Altri dati");
        $this->fs_altri_dati->anchor($fields->piva);
        $this->fs_altri_dati->anchorLeft($fields->codice_fiscale);
		$this->fs_altri_dati->anchor($fields->conto_corrente);
		$this->fs_altri_dati->anchorLeft($fields->cin);
		$this->fs_altri_dati->anchor($fields->banca);
		$this->fs_altri_dati->anchor($fields->agenzia);
		$this->fs_altri_dati->anchor($fields->abi);
		$this->fs_altri_dati->anchorLeft($fields->cab);
		$this->fs_altri_dati->anchorLeft($fields->iban);
		$this->fs_altri_dati->anchor($fields->tipo_documento);
		$this->fs_altri_dati->anchorLeft($fields->path_documento);
		
		if ( E3G_TIPO_GESTIONE == 'E' ) {
			$this->fs_altri_dati->anchor($fields->eg_cod_doc_scontrino);
			$this->fs_altri_dati->anchor($fields->anno_contabile);
		}
		

        // ----------------------------------------------- Notifiche automatiche
        $fields->notifica_apertura_ref->setLabel( "Apertura ordine (referenti)" );
        $fields->notifica_apertura_ref->setTooltip( "Invio ai REFERENTI notifica per invitarli a controllare i listini." );
        $fields->notifica_apertura_ref->label->setWidth( 200 );
        $fields->notifica_apertura_ref->setType( "checkbox" );

        $fields->notifica_apertura_ref_data->setLabel( "Ultimo invio" );
        $fields->notifica_apertura_ref_data->label->setWidth( 80 );
        $fields->notifica_apertura_ref_data->setWidth( 80 );
        $fields->notifica_apertura_ref_data->disable();

        $fields->notifica_apertura_ref_gg->setLabel( "Giorni di anticipo" );
        $fields->notifica_apertura_ref_gg->setTooltip( "Giorni di anticipo con cui inviare la notifica rispetto alla data di apertura dell'ordine." );
        $fields->notifica_apertura_ref_gg->label->setWidth( 120 );
        $fields->notifica_apertura_ref_gg->setWidth( 30 );

        $this->build( "p4a_button", "bu_notifica_apertura_ref" );
        $this->bu_notifica_apertura_ref->setLabel( "Notifica adesso" );
        $this->bu_notifica_apertura_ref->setIcon( "mail_new" );
        $this->bu_notifica_apertura_ref->setSize( 16 );
        $this->bu_notifica_apertura_ref->setWidth( 150 );
        $this->bu_notifica_apertura_ref->addAction( "onClick" );
        $this->intercept( $this->bu_notifica_chiusura, "onClick", "bu_notifica_apertura_ref_click" );


        $fields->notifica_apertura->setLabel( "Apertura ordine (utenti)" );
        $fields->notifica_apertura->label->setWidth( 200 );
        $fields->notifica_apertura->setType( "checkbox" );

        $fields->notifica_apertura_data->setLabel( "Ultimo invio" );
        $fields->notifica_apertura_data->label->setWidth( 80 );
        $fields->notifica_apertura_data->setWidth( 80 );
        $fields->notifica_apertura_data->disable();

        $this->build( "p4a_button", "bu_notifica_apertura" );
        $this->bu_notifica_apertura->setLabel( "Notifica adesso" );
        $this->bu_notifica_apertura->setIcon( "mail_new" );
        $this->bu_notifica_apertura->setSize( 16 );
        $this->bu_notifica_apertura->setWidth( 150 );
        $this->bu_notifica_apertura->addAction( "onClick" );
        $this->intercept( $this->bu_notifica_apertura, "onClick", "bu_notifica_apertura_click" );


        $fields->notifica_chiusura->setLabel( "Chiusura ordine (utenti)" );
        $fields->notifica_chiusura->label->setWidth( 200 );
        $fields->notifica_chiusura->setType( "checkbox" );

        $fields->notifica_chiusura_data->setLabel( "Ultimo invio" );
        $fields->notifica_chiusura_data->label->setWidth( 80 );
        $fields->notifica_chiusura_data->setWidth( 80 );
        $fields->notifica_chiusura_data->disable();

        $fields->notifica_chiusura_gg->setLabel( "Giorni di anticipo" );
        $fields->notifica_chiusura_gg->setTooltip( "Giorni di anticipo con cui inviare la notifica rispetto alla data di chiusura ordine." );
        $fields->notifica_chiusura_gg->label->setWidth( 120 );
        $fields->notifica_chiusura_gg->setWidth( 30 );

        $this->build( "p4a_button", "bu_notifica_chiusura" );
        $this->bu_notifica_chiusura->setLabel( "Notifica adesso" );
        $this->bu_notifica_chiusura->setIcon( "mail_new" );
        $this->bu_notifica_chiusura->setSize( 16 );
        $this->bu_notifica_chiusura->setWidth( 150 );
        $this->bu_notifica_chiusura->addAction( "onClick" );
        $this->intercept( $this->bu_notifica_chiusura, "onClick", "bu_notifica_chiusura_click" );


// NON ANCORA ATTIVATI A CAUSA DELLA COMPLESSITA'
        $fields->notifica_lista_spesa->setLabel( "Lista della spesa (utenti)" );
        $fields->notifica_lista_spesa->setTooltip( "Invia notifica con lista della spesa alla chiusura dell'ordine." );
        $fields->notifica_lista_spesa->label->setWidth( 200 );
        $fields->notifica_lista_spesa->setType( "checkbox" );

        $fields->notifica_lista_spesa_data->setLabel( "Ultimo invio" );
        $fields->notifica_lista_spesa_data->label->setWidth( 80 );
        $fields->notifica_lista_spesa_data->setWidth( 80 );
        $fields->notifica_lista_spesa_data->disable();

        $this->build( "p4a_button", "bu_notifica_lista_spesa" );
        $this->bu_notifica_lista_spesa->setLabel( "Notifica adesso" );
        $this->bu_notifica_lista_spesa->setIcon( "mail_new" );
        $this->bu_notifica_lista_spesa->setSize( 16 );
        $this->bu_notifica_lista_spesa->setWidth( 150 );
        $this->bu_notifica_lista_spesa->addAction( "onClick" );
        $this->intercept( $this->bu_notifica_lista_spesa, "onClick", "bu_notifica_lista_spesa_click" );
// 


        $fields->notifica_mov_cassa->setLabel( "Movimenti cassa (cassiere)" );
        $fields->notifica_mov_cassa->setTooltip( "Invia al CASSIERE notifica dei movimenti di cassa in attesa di validazione." );
        $fields->notifica_mov_cassa->label->setWidth( 200 );
        $fields->notifica_mov_cassa->setType( "checkbox" );

        $fields->notifica_mov_cassa_data->setLabel( "Ultimo invio" );
        $fields->notifica_mov_cassa_data->label->setWidth( 80 );
        $fields->notifica_mov_cassa_data->setWidth( 80 );
        $fields->notifica_mov_cassa_data->disable();

        $this->build( "p4a_button", "bu_notifica_mov_cassa" );
        $this->bu_notifica_mov_cassa->setLabel( "Notifica adesso" );
        $this->bu_notifica_mov_cassa->setIcon( "mail_new" );
        $this->bu_notifica_mov_cassa->setSize( 16 );
        $this->bu_notifica_mov_cassa->setWidth( 150 );
        $this->bu_notifica_mov_cassa->addAction( "onClick" );
        $this->intercept( $this->bu_notifica_mov_cassa, "onClick", "bu_notifica_mov_cassa_click" );


        // -------------------------------------------------- Fieldset NOTIFICHE
        $this->build( "p4a_fieldset", "fs_notifiche" );
        $this->fs_notifiche->setWidth( E3G_FIELDSET_DATI_WIDTH );
        $this->fs_notifiche->setTitle( "Notifiche automatiche" );
        
        $this->fs_notifiche->anchor( $fields->notifica_apertura_ref );
        $this->fs_notifiche->anchorLeft( $fields->notifica_apertura_ref_data );
        $this->fs_notifiche->anchorLeft( $fields->notifica_apertura_ref_gg );
//      $this->fs_notifiche->anchorRight( $this->bu_notifica_apertura_ref );

        $this->fs_notifiche->anchor( $fields->notifica_apertura );
        $this->fs_notifiche->anchorLeft( $fields->notifica_apertura_data );
//      $this->fs_notifiche->anchorRight( $this->bu_notifica_apertura );

        $this->fs_notifiche->anchor( $fields->notifica_chiusura );
        $this->fs_notifiche->anchorLeft( $fields->notifica_chiusura_data );
        $this->fs_notifiche->anchorLeft( $fields->notifica_chiusura_gg );
//      $this->fs_notifiche->anchorRight( $this->bu_notifica_chiusura );
/* TODO da completare
        $this->fs_notifiche->anchor( $fields->notifica_lista_spesa );
        $this->fs_notifiche->anchorLeft( $fields->notifica_lista_spesa_data );
        $this->fs_notifiche->anchorRight( $this->bu_notifica_lista_spesa );
*/
        $this->fs_notifiche->anchor( $fields->notifica_mov_cassa );
        $this->fs_notifiche->anchorLeft( $fields->notifica_mov_cassa_data );
        $this->fs_notifiche->anchorRight( $this->bu_notifica_mov_cassa );

        
		// Frame principale ----------------------------------------------------
		$frm=& $this->build( "p4a_frame", "frm" );
		$frm->setWidth( E3G_MAIN_FRAME_WIDTH );
	
		$frm->anchorCenter( $this->message );
		$frm->anchorCenter( $this->fs_parametri );
		if ( E3G_TIPO_GESTIONE == 'G' )
			$frm->anchorCenter( $this->fs_gg_prezzi );
		$frm->anchorCenter( $this->fs_anagrafica );
		$frm->anchorCenter( $this->fs_recapiti );
        $frm->anchorCenter( $this->fs_altri_dati );
        $frm->anchorCenter( $this->fs_notifiche );

		e3g_scrivi_footer( $this, $frm );
		
		// Display
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
		$this->display("main", $frm);
	}

	
    // -------------------------------------------------------------------------
	function main()
    // -------------------------------------------------------------------------
	{
		parent::main();

		foreach ($this->mf as $field) {
			$this->fields->$field->unsetStyleProperty("border");
		}
	}
	
	
    // -------------------------------------------------------------------------
	function saveRow()
    // -------------------------------------------------------------------------
	{
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        $this->fields->email->setNewValue( trim( strtolower( $this->fields->email->getNewValue() ) ) );
        $this->fields->web->setNewValue( trim( strtolower( $this->fields->web->getNewValue() ) ) );

		$errors = array();

		foreach ( $this->mf as $field ) {
			if ( strlen($this->fields->$field->getNewValue()) == 0 ) {
				$errors[] = $field;
			}
		}

		if ( sizeof($errors) > 0 ) {
			$this->message->setValue( "Compilare i dati obbligatori." );

			foreach ( $errors as $field ) {
				$this->fields->$field->setStyleProperty( "border", "1px solid red" );
			}
		} 
        elseif ( $this->fields->email->getNewValue()<>"" and !e3g_email_valido( $this->fields->email->getNewValue() ) ) {
            $this->message->setValue( "L'indirizzo e-mail indicato non sembra essere valido." );
            $this->fields->email->setStyleProperty( "border", "1px solid red" );
        }
        else {
			if ( $this->fields->prezzi_mag_fissa->getNewValue() < 0 )
				$this->fields->prezzi_mag_fissa->setNewValue( 0 );

			if ( $this->fields->prezzi_mag_perc->getNewValue() < 0 )
				$this->fields->prezzi_mag_perc->setNewValue( 0 );

			if ( $this->fields->prezzi_mag_perc->getNewValue() > 100 )
				$this->fields->prezzi_mag_perc->setNewValue( 100 );
			
            if ( $this->fields->notifica_chiusura_gg->getNewValue() < 0 )
                $this->fields->notifica_chiusura_gg->setNewValue( 3 );
            
			parent::saveRow();

			e3g_update_var_azienda();
			$this->showPrevMask();
		} 
	}


    // -------------------------------------------------------------------------
    function bu_notifica_apertura_ref_click()
    // -------------------------------------------------------------------------
    {
        $n_invii = e3g_notifica_apertura_ref();
        
        if ( $n_invii ) { 
            $this->message->setIcon( "info" );
            $this->message->setValue( "Notifica di apertura ordine inviata a $n_invii referent" .
                ( $n_invii==1 ? "e" : "i" ) . "." );
        }
        else {
            $this->message->setIcon( "warning" );
//TODO Così non va bene...
            $this->message->setValue( "Notifica di apertura ordine non inviata: nessun ordine si apre oggi." );
        }
    }
    
    
    // -------------------------------------------------------------------------
    function bu_notifica_apertura_click()
    // -------------------------------------------------------------------------
    {
        $n_invii = e3g_notifica_apertura();
        
        if ( $n_invii ) { 
            $this->message->setIcon( "info" );
            $this->message->setValue( "Notifica di apertura ordine inviata a $n_invii utent" .
                ( $n_invii==1 ? "e" : "i" ) . "." );
        }
        else {
            $this->message->setIcon( "warning" );
//TODO Così non va bene...
            $this->message->setValue( "Notifica di apertura ordine non inviata: nessun ordine si apre oggi." );
        }
    }
    
    
    // -------------------------------------------------------------------------
    function bu_notifica_chiusura_click()
    // -------------------------------------------------------------------------
    {
//TODO Terminare di sistemare la seguente funzione per poterla usare anche da qui...        
        $n_invii = e3g_notifica_chiusura();   
        
        if ( $n_invii ) { 
            $this->message->setIcon( "info" );
            $this->message->setValue( "Notifica di chiusura ordine inviata a $n_invii utent" .
                ( $n_invii==1 ? "e" : "i" ) . "." );
        }
        else {
            $this->message->setIcon( "warning" );
//TODO Così non va bene...
            $this->message->setValue( "Notifica di chiusura ordine non inviata: nessun ordine si chiude oggi." );
        }
    }
    
    
    // -------------------------------------------------------------------------
    function bu_notifica_lista_spesa_click()
    // -------------------------------------------------------------------------
    {
        $n_invii = e3g_notifica_lista_spesa();   
        
        if ( $n_invii ) { 
            $this->message->setIcon( "info" );
            $this->message->setValue( "Notifica lista della spesa inviata a $n_invii utent" .
                ( $n_invii==1 ? "e" : "i" ) . "." );
        }
    }
    
    
    // -------------------------------------------------------------------------
    function bu_notifica_mov_cassa_click()
    // -------------------------------------------------------------------------
    {
        $n_invii = e3g_notifica_mov_cassa();   
        
        if ( $n_invii ) { 
            $this->message->setIcon( "info" );
            $this->message->setValue( "Notifica lista della spesa inviata a $n_invii cassier" .
                ( $n_invii==1 ? "e" : "i" ) . "." );
        }
    }
    
}

?>