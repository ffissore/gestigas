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
require_once( dirname(__FILE__) . '/../libraries/e3g_cron.php' );
require_once( dirname(__FILE__) . '/../config.php' );

class e3g_main extends P4A
{

	// -------------------------------------------------------------------------
	// DICHIARAZIONE DELLE VARIABILI GLOBALI 
	// -------------------------------------------------------------------------

	var $e3g_nome_sw; // Contiene "GestiGAS" oppure "Equogest"

	var $e3g_prefix;  // prefisso tabelle della gestione in uso

	var $e3g_db_cond_versione;  // versione del database (tabelle CONDivise)
	
	// Condizione WHERE per le query che coinvolgono i referenti
	// Aggiornata da e3g_update_where_referente() che viene a sua volta richia-
	// mata da e3g_update_var_utente() visto che dipende dall'utente corrente
	var $e3g_where_referente;
	
	// Dati azienda (gas/bottega) di interesse globale
	// Da aggiornare richiamando e3g_update_var_azienda();	
	var $e3g_azienda_rag_soc;  // Ragione sociale del GAS o Bottega corrente
	var $e3g_azienda_indirizzo; 
	var $e3g_azienda_cap; 
	var $e3g_azienda_localita; 
	var $e3g_azienda_provincia; 
	var $e3g_azienda_telefono;  
    var $e3g_azienda_web;  
    var $e3g_azienda_email;  
	var $e3g_azienda_db_multi_versione;
	var $e3g_azienda_gg_cod_doc_ordine;
	var $e3g_azienda_gg_cod_doc_ordine_fam;
	var $e3g_azienda_n_decimali_prezzi;
	var $e3g_azienda_ordine_minimo;  // importo minimo dell'ordine
	var $e3g_azienda_gestione_luoghi_cons;
    var $e3g_azienda_gestione_cassa;
    var $e3g_azienda_acquista_se_credito_insufficiente;
	
	var $e3g_azienda_piva;  
	var $e3g_banca;							 
	var $e3g_agenzia;					 
	var $e3g_abi;			         
	var $e3g_cab;							 
	var $e3g_cin;	    					 
	var $e3g_conto_corrente;			     
	var $e3g_iban;	    					 
	
	var $e3g_azienda_tipo_gestione_prezzi; 
	var $e3g_azienda_mostra_prezzo_sorgente; 
	var $e3g_azienda_prezzi_mag_fissa;
	var $e3g_azienda_prezzi_mag_perc;
	var $e3g_azienda_etichette_path;
	var $e3g_azienda_anno_contabile;  // anno contabile per i documenti
	var $e3g_azienda_tipo_documento; // tipo documento in stampa  
	var $e3g_azienda_path_documento; // percorso documento ODT
	var $e3g_azienda_path_logo; // percorso logo documenti PDF 

	
	// Dati utente di interesse globale
	// Da aggiornare richiamando e3g_update_var_utente( $id_anagrafica );	
	var $e3g_utente_codice;
	var $e3g_utente_desc;
	var $e3g_utente_tipo;  // A:super AS:admin R:ref U:user G:guest
	var $e3g_utente_tipo_desc;
	var $e3g_utente_last_login;  // data ultimo accesso (settato unicamente all'avvio)
	var $e3g_utente_desc_last_login;  // data ultimo accesso (come stringa "DATA alle ORE", settato solo all'avvio)
	var $e3g_utente_idanag;
    var $e3g_utente_email;
    var $e3g_utente_db_source_page_limit;
    var $e3g_utente_modifica_ingredienti;  // abilitato alla modifica degli ingredienti
    var $e3g_utente_filtro_ingredienti;  // visualizzazione filtro ingredienti
    var $e3g_utente_cassiere;  

	
	// -------------------------------------------------------------------------
	function e3g_main()
	// -------------------------------------------------------------------------
	{		
		parent::p4a();
		
 		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
	
		$p4a->i18n->setCharset("ISO-8859-1");
		
		$this->e3g_nome_sw = (E3G_TIPO_GESTIONE == 'G' ? "GestiGAS" : "Equogest");
		
		e3g_title();		
/*
		// inserire in alternativa una chiamata alla maschera (da fare) per la creazione del database
		// la query sotto restituisce 1 se esiste una tabella _aziende, 0 se non esiste
		$esistetabella = $db->queryOne(
			"SELECT COUNT(table_name) FROM information_schema.tables " .
			" WHERE table_schema = '" . DB_NAME . "' AND table_name = '_aziende' " );
		
		if ( $esistetabella == 0 )
			$this->openMask("sqldbcreate");
		else
			$this->openMask("login");*/
			
		$this->openMask("login");
	}

	
	// -------------------------------------------------------------------------
	function inizializza()
	// -------------------------------------------------------------------------
	{		
		$this->crea_menu(); 	
		if ( E3G_TIPO_GESTIONE == 'G' )
		{
			$this->set_menu_utente();
			
			$this->openMask("about_user");
		}	
		else
		{
			$this->openMask("about_admin");
		}	
	}

		
	// -------------------------------------------------------------------------
	// MENU SEMPLIFICATO (per la finestra principale)
	// -------------------------------------------------------------------------
	function set_menu_utente()
	{
		$p4a =& p4a::singleton();
		
		if ( E3G_TIPO_GESTIONE == 'G' ) {
			switch ( $p4a->e3g_utente_tipo ) {
				case "A":   // SUPER AMMINISTRATORE 
				case "AS":  // AMMINISTRATORE SINGOLO GAS
				case "R":   // REFERENTE
					$this->menu->items->home->items->admin->setVisible();
					break;
	            case "U":
	            	$this->menu->items->home->items->admin->setInvisible();
	            	break;
	            case "G":
	                $this->menu->items->home->items->admin->setInvisible();
	            	break;
			}

            $this->menu->items->ordini->items->ordine_globale->setInvisible();
		}
		
		$this->menu->items->documenti->setInvisible();
		while ( $sottomenu =& $this->menu->items->documenti->items->nextItem() ) 
			$sottomenu->setInvisible();
		
		$this->menu->items->articoli->items->anagtipiarticoli->setInvisible();
		$this->menu->items->articoli->items->anagcatmerce->setInvisible();
		
		if ( E3G_TIPO_GESTIONE == 'G' ) {
			$this->menu->items->articoli->items->periodoarticoli->setInvisible();
			$this->menu->items->anagrafiche->items->referenti_fornitori->setInvisible();    		
		}
		
		$this->menu->items->articoli->items->anagtipiarticoli->setInvisible();
		$this->menu->items->articoli->items->anagum->setInvisible();
		
		$this->menu->items->anagrafiche->items->anag_clienti->setInvisible();
		
        while ( $sottomenu =& $this->menu->items->strumenti->items->nextItem() ) 
            $sottomenu->setInvisible();
        if ( $p4a->e3g_azienda_gestione_cassa ) {
            $this->menu->items->strumenti->setVisible();
            $this->menu->items->strumenti->items->gestione_cassa->setVisible();
        }
        else
            $this->menu->items->strumenti->setInvisible();
	}

					
	// -------------------------------------------------------------------------
	// MENU AVANZATO (per la finestra di amministrazione)
	// -------------------------------------------------------------------------
	function set_menu_avanzato()
	{
		$p4a =& p4a::singleton();
		
		$this->menu->items->home->items->admin->setInvisible();

		switch ( $p4a->e3g_utente_tipo ) {
            case "A":   // SUPER AMMINISTRATORE 
            case "AS":  // AMMINISTRATORE SINGOLO GAS
 				$this->menu->items->ordini->items->ordine_globale->setVisible();
				
				$this->menu->items->documenti->setVisible();
				while ( $sottomenu =& $this->menu->items->documenti->items->nextItem() ) 
					$sottomenu->setVisible();
				
				$this->menu->items->articoli->items->anagtipiarticoli->setVisible();
				$this->menu->items->articoli->items->anagcatmerce->setVisible();
				$this->menu->items->articoli->items->periodoarticoli->setVisible();
				$this->menu->items->articoli->items->anagum->setVisible();
				
				$this->menu->items->anagrafiche->items->anag_clienti->setVisible();
				$this->menu->items->anagrafiche->items->referenti_fornitori->setVisible();    		
				
				$this->menu->items->strumenti->setVisible();
                if ( $p4a->e3g_azienda_gestione_cassa ) 
                    $this->menu->items->strumenti->items->gestione_cassa->setVisible();
                $this->menu->items->strumenti->items->invio_email->setVisible();
				$this->menu->items->strumenti->items->azienda->setVisible();
                $this->menu->items->strumenti->items->mailing_list_admin->setVisible();
				if ( $p4a->e3g_utente_tipo == "A" ) {  // Solo per il super-admin
                	$this->menu->items->strumenti->items->sqlexecute->setVisible();
					$this->menu->items->strumenti->items->nuova_gestione->setVisible();
					$this->menu->items->strumenti->items->multigestione->setVisible();
//					$this->menu->items->strumenti->items->azzeradb->setVisible();
                    $this->menu->items->strumenti->items->cron->setVisible();
				}
                break;
                
            case "R":  // REFERENTE
   				$this->menu->items->ordini->items->ordine_globale->setVisible();
				
				$this->menu->items->documenti->setVisible();
				$this->menu->items->documenti->items->gesdocumenti1->setVisible();
				$this->menu->items->documenti->items->estraz_ordini_fornitore->setVisible();
                $this->menu->items->documenti->items->modifica_prezzi_art->setVisible();
                $this->menu->items->documenti->items->modifica_qta_art->setVisible();
				//$this->menu->items->documenti->items->consegna_utente_totale->setVisible();
				$this->menu->items->documenti->items->consegna_utente->setVisible();
				
				$this->menu->items->articoli->items->periodoarticoli->setVisible();
				$this->menu->items->articoli->items->anagum->setVisible();
				
				$this->menu->items->anagrafiche->items->anag_clienti->setVisible();
				
                $this->menu->items->strumenti->setVisible();
                if ( $p4a->e3g_azienda_gestione_cassa ) 
                    $this->menu->items->strumenti->items->gestione_cassa->setVisible();
                $this->menu->items->strumenti->items->invio_email->setVisible();

                break;
                
            case "U":
            case "G":
				// Normale UTENTE e GUEST non devono accedere alla finestra di amministrazione
				die();
                break;
        }
	}
	
	
	function crea_menu()
	{
		$p4a =& p4a::singleton();
	
		$this->build( "p4a_menu", "menu" );
			
		// ---------------------------------------------------------------------
		$this->menu->addItem( "home", "HOME" );
		// ---------------------------------------------------------------------
		$this->intercept( $this->menu->items->home,"onClick", "homeClick" );
//		$this->menu->items->home->setIcon( "home" ); disattivato per incompatibilità con IE (TODO ripristinare quando possibile)
				
		if ( E3G_TIPO_GESTIONE == 'G' )
		{
			$this->menu->items->home->addItem( "admin", "Amministra" );
			$this->intercept( $this->menu->items->home->items->admin, "onClick", "adminClick" );
			$this->menu->items->home->items->admin->setIcon( "admin" );
		}	

		$this->menu->items->home->addItem( "esci", "Esci..." );
		$this->intercept( $this->menu->items->home->items->esci, "onClick", "esciClick" );
		$this->menu->items->home->items->esci->setIcon( "on_off" );
		$this->menu->items->home->items->esci->requireConfirmation( "onClick", 
			"Vuoi veramente scollegarti da '$p4a->e3g_nome_sw' ?" );

		if ( E3G_TIPO_GESTIONE == 'G' )
		{	
			// -----------------------------------------------------------------	
    		$this->menu->addItem("ordini", "Ordini"); 
			// -----------------------------------------------------------------	
    		$this->menu->items->ordini->addItem( "cassa", "Ordine corrente" );
    		$this->intercept( $this->menu->items->ordini->items->cassa, "onClick", "cassaClick" );
			$this->menu->items->ordini->items->cassa->setIcon( "kwrite" );

			$this->menu->items->ordini->addItem( "ordine_globale", "Ordine globale" );
    		$this->intercept( $this->menu->items->ordini->items->ordine_globale, "onClick", "ordine_globaleClick" );
            $this->menu->items->ordini->items->ordine_globale->setIcon( "kwrite" );
							
            $this->menu->items->ordini->addItem( "periodo_ordini", "Periodi ordini" );
            $this->intercept($this->menu->items->ordini->items->periodo_ordini,"onClick", "periodo_ordiniClick");
            $this->menu->items->ordini->items->periodo_ordini->setIcon( "date" );

			$this->menu->items->ordini->addItem( "cassa_archivio", "Archivio ordini" );
    		$this->intercept( $this->menu->items->ordini->items->cassa_archivio, "onClick", "cassa_archivioClick" );
			
            $this->menu->items->ordini->addItem( "statistiche_ordini", "Statistiche" );
            $this->intercept( $this->menu->items->ordini->items->statistiche_ordini, "onClick", "statistiche_ordiniClick" );
            
			if ( $p4a->e3g_azienda_gestione_luoghi_cons ) {
	    		$this->menu->items->ordini->addItem( "luoghi_consegna", "Luoghi consegna" );
	    		$this->intercept($this->menu->items->ordini->items->luoghi_consegna,"onClick", "luoghi_consegna_click");
			}
				
			// -----------------------------------------------------------------	
    		$this->menu->addItem("documenti", "Documenti");  
			// -----------------------------------------------------------------	
			$this->menu->items->documenti->addItem( "gesdocumenti1", "Gestione doc." );
			$this->intercept( $this->menu->items->documenti->items->gesdocumenti1,"onClick", "gesdocumenti1Click" );
	
            $this->menu->items->documenti->addItem( "estraz_ordini_fornitore", "Estrai ordine fornitore" );
       		$this->intercept( $this->menu->items->documenti->items->estraz_ordini_fornitore,"onClick", "estraz_ordini_fornitoreClick" );                    
	    		
            $this->menu->items->documenti->addItem( "modifica_prezzi_art", "Modifica prezzi articoli in consegna" );
            $this->intercept( $this->menu->items->documenti->items->modifica_prezzi_art,"onClick", "modifica_prezzi_art_click" );                    
                
            $this->menu->items->documenti->addItem( "modifica_qta_art", "Modifica quantita' articoli in consegna" );
            $this->intercept( $this->menu->items->documenti->items->modifica_qta_art,"onClick", "modifica_qta_art_click" );                    

//          $this->menu->items->documenti->addItem( "consegna_utente_totale", "Consegna articoli ad Utenti - TOT" );
//          $this->intercept( $this->menu->items->documenti->items->consegna_utente_totale,"onClick", "consegna_utente_totaleClick");                    
             
            $this->menu->items->documenti->addItem( "consegna_utente", "Consegna articoli all'utente" );
            $this->intercept( $this->menu->items->documenti->items->consegna_utente,"onClick", "consegna_utenteClick" );                    

            $this->menu->items->documenti->addItem( "consegna_utente_chiusura", "Chiusura ordini aperti" );
            $this->intercept( $this->menu->items->documenti->items->consegna_utente_chiusura,"onClick", "consegna_utente_chiusuraClick" );                    
            
            //$this->menu->items->documenti->addItem( "documentiestratti", "Ordini estratti" );
    		//$this->intercept( $this->menu->items->documenti->items->documentiestratti,"onClick", "documentiestrattiClick" );                    
			// sostituito da 
			$this->menu->items->documenti->addItem( "documenti_righe_estratte", "Modifica estratti" );
    		$this->intercept( $this->menu->items->documenti->items->documenti_righe_estratte,"onClick", "documenti_righe_estratteClick" );                    
				            		
	    	$this->menu->items->documenti->addItem( "doctipidoc", "Tipi documento" );
    		$this->intercept( $this->menu->items->documenti->items->doctipidoc,"onClick", "doctipidocClick" );
	    		        
    		$this->menu->items->documenti->addItem( "doccampireport", "Opzioni PDF" );
    		$this->intercept( $this->menu->items->documenti->items->doccampireport,"onClick", "opzioni_esport_pdfClick" );
			$this->menu->items->documenti->items->doccampireport->setIcon( "misc" );
	    	
			// -----------------------------------------------------------------	
    		$this->menu->addItem("articoli", "Articoli");
			// -----------------------------------------------------------------	
    		$this->menu->items->articoli->addItem("anagarticoli", "Listino articoli");
    		$this->intercept($this->menu->items->articoli->items->anagarticoli,"onClick", "anagarticoliClick");
            $this->menu->items->articoli->items->anagarticoli->setIcon( "articoli" );
	            
			$this->menu->items->articoli->addItem("esporta_listino", "Esporta listino...");
    		$this->intercept($this->menu->items->articoli->items->esporta_listino,"onClick", "esporta_listino_menuClick");
			$this->menu->items->articoli->items->esporta_listino->setIcon( "pdf" );

    		$this->menu->items->articoli->addItem("anagtipiarticoli", "Categorie");
    		$this->intercept($this->menu->items->articoli->items->anagtipiarticoli,"onClick", "anagtipiarticoliClick");
				
    		$this->menu->items->articoli->addItem("anagcatmerce", "Sotto-categorie");
    		$this->intercept($this->menu->items->articoli->items->anagcatmerce,"onClick", "anagcatmerceClick");
	    		
    		$this->menu->items->articoli->addItem("periodoarticoli", "Disponibilita' stagionale");
    		$this->intercept($this->menu->items->articoli->items->periodoarticoli,"onClick", "periodoarticoliClick");
	
    		$this->menu->items->articoli->addItem("anagum", "Unita' di Misura");
    		$this->intercept($this->menu->items->articoli->items->anagum,"onClick", "anagumClick");
    
			// -----------------------------------------------------------------	
    		$this->menu->addItem("anagrafiche", "Anagrafiche");
			// -----------------------------------------------------------------	
			$this->menu->items->anagrafiche->addItem("anagprofilo", "Dati personali");
    		$this->intercept($this->menu->items->anagrafiche->items->anagprofilo,"onClick", "anagprofiloClick");
			$this->menu->items->anagrafiche->items->anagprofilo->setIcon( "personal" );
	
    		$this->menu->items->anagrafiche->addItem("anag_clienti", "Utenti");
    		$this->intercept($this->menu->items->anagrafiche->items->anag_clienti,"onClick", "anag_clientiClick");
			$this->menu->items->anagrafiche->items->anag_clienti->setIcon( "users" );
	
    		$this->menu->items->anagrafiche->addItem("anagfornitori", "Fornitori");
    		$this->intercept($this->menu->items->anagrafiche->items->anagfornitori,"onClick", "anagfornitoriClick");
            $this->menu->items->anagrafiche->items->anagfornitori->setIcon( "users" );
	    		
			$this->menu->items->anagrafiche->addItem("referenti_fornitori", "Referenti/Fornitori");
    		$this->intercept($this->menu->items->anagrafiche->items->referenti_fornitori,"onClick", "referenti_fornitori_menuClick");            

//    		$this->menu->items->anagrafiche->addItem("login_tipo", "Accessi - tipo Utente");
//    		$this->intercept($this->menu->items->anagrafiche->items->login_tipo,"onClick", "login_tipoClick");
		
			// -----------------------------------------------------------------	
//			$this->menu->addItem("stampe", "Stampe");
			// -----------------------------------------------------------------	

			// -----------------------------------------------------------------	
			$this->menu->addItem("strumenti", "Strumenti");
			// -----------------------------------------------------------------	
            $this->menu->items->strumenti->addItem( "gestione_cassa", "Gestione cassa" );
            $this->intercept( $this->menu->items->strumenti->items->gestione_cassa, "onClick", "gestione_cassaClick" );
            $this->menu->items->strumenti->items->gestione_cassa->setIcon( "kcalc" );
    
            $this->menu->items->strumenti->addItem( "invio_email", "Invio email..." );
            $this->intercept( $this->menu->items->strumenti->items->invio_email, "onClick", "invio_emailClick" );
            $this->menu->items->strumenti->items->invio_email->setIcon( "mail_new" );
    
            $this->menu->items->strumenti->addItem("azienda", "Preferenze");
            $this->intercept($this->menu->items->strumenti->items->azienda,"onClick", "azienda_menuclick");
            $this->menu->items->strumenti->items->azienda->setIcon( "misc" );
    
            $this->menu->items->strumenti->addItem("mailing_list_admin", "Mailing-list");
            $this->intercept($this->menu->items->strumenti->items->mailing_list_admin,"onClick", "mailing_list_admin_click");
            $this->menu->items->strumenti->items->mailing_list_admin->setIcon( "email" );
    
    		$this->menu->items->strumenti->addItem("sqlexecute", "Esegui query");
    		$this->intercept($this->menu->items->strumenti->items->sqlexecute,"onClick", "sqlexecuteClick");
			$this->menu->items->strumenti->items->sqlexecute->setIcon( "execute" );
	
            // Solo superadmin			
			$this->menu->items->strumenti->addItem("multigestione", "Multi-gestione");
			$this->intercept($this->menu->items->strumenti->items->multigestione,"onClick", "multigestione_click");

            $this->menu->items->strumenti->addItem("nuova_gestione", "Nuova gestione");
            $this->intercept($this->menu->items->strumenti->items->nuova_gestione,"onClick", "nuova_gestioneClick");
    
            $this->menu->items->strumenti->addItem("cron", "Operazioni periodiche");
            $this->intercept($this->menu->items->strumenti->items->cron,"onClick", "cronClick");
    
			// PERICOLOSO
//			$this->menu->items->strumenti->addItem("azzeradb", "Azzera database");
//			$this->intercept($this->menu->items->strumenti->items->azzeradb,"onClick", "azzeradb_click");
		}
		else 
		{	// MENU EQUOGEST
			// -----------------------------------------------------------------	
    		$this->menu->addItem("cassa", "Vendita al Banco");
			// -----------------------------------------------------------------	
    		$this->intercept($this->menu->items->cassa, "onClick", "cassaClick");
    		
			// -----------------------------------------------------------------	
    		$this->menu->addItem("documenti", "Documenti");
			// -----------------------------------------------------------------	

			$this->menu->items->documenti->addItem("gesdocumenti1", "Gestione Doc.");
    		$this->intercept($this->menu->items->documenti->items->gesdocumenti1,"onClick", "gesdocumenti1Click");
    
    		//$this->menu->items->documenti->addItem("gesdocumenti", "Tutti i Documenti");
    		//$this->intercept($this->menu->items->documenti->items->gesdocumenti,"onClick", "gesdocumentiClick");
    		
            $this->menu->items->documenti->addItem("doctipidoc", "Tipi Documento");
    		$this->intercept($this->menu->items->documenti->items->doctipidoc,"onClick", "doctipidocClick");                    
    		
    		$this->menu->items->documenti->addItem("doc_estrazione_eg", "Estrazione Documenti");
    		$this->intercept($this->menu->items->documenti->items->doc_estrazione_eg,"onClick", "doc_estrazione_egClick");                    
    		
    		
    		$this->menu->items->documenti->addItem("anagpagamenti", "Tipi Pagamento");
    		$this->intercept($this->menu->items->documenti->items->anagpagamenti,"onClick", "anagpagamentiClick");
    
    		$this->menu->items->documenti->addItem("doc_registri", "Registri dei documenti");
    		$this->intercept($this->menu->items->documenti->items->doc_registri,"onClick", "doc_registriClick");
    
    		
    		$this->menu->items->documenti->addItem("pianodeiconti", "Piano dei conti");
    		$this->intercept($this->menu->items->documenti->items->pianodeiconti,"onClick", "pianodeicontiClick");
    
    		$this->menu->items->documenti->addItem( "doccampireport", "Opzioni PDF" );
    		$this->intercept( $this->menu->items->documenti->items->doccampireport,"onClick", "opzioni_esport_pdfClick" );
			$this->menu->items->documenti->items->doccampireport->setIcon( "misc" );
    
			// -----------------------------------------------------------------	
    		$this->menu->addItem("articoli", "Articoli");
			// -----------------------------------------------------------------	
    
    		$this->menu->items->articoli->addItem("anagarticoli", "Listino Articoli");
    		$this->intercept($this->menu->items->articoli->items->anagarticoli,"onClick", "anagarticoliClick");
            $this->menu->items->articoli->items->anagarticoli->setIcon( "articoli" );

    		$this->menu->items->articoli->addItem("anagtipiarticoli", "Categorie");
    		$this->intercept($this->menu->items->articoli->items->anagtipiarticoli,"onClick", "anagtipiarticoliClick");
				
    		$this->menu->items->articoli->addItem("anagcatmerce", "Sotto-categorie");
    		$this->intercept($this->menu->items->articoli->items->anagcatmerce,"onClick", "anagcatmerceClick");

    		$this->menu->items->articoli->addItem("anagprogetti", "Elenco Progetti");
    		$this->intercept($this->menu->items->articoli->items->anagprogetti,"onClick", "anagprogettiClick");
    
    		$this->menu->items->articoli->addItem("anagum", "Unita' di Misura");
    		$this->intercept($this->menu->items->articoli->items->anagum,"onClick", "anagumClick");
    
			$this->menu->items->articoli->addItem("sqlnewcode", "Modifica Codice Articolo");
    		$this->intercept($this->menu->items->articoli->items->sqlnewcode,"onClick", "sqlnewcodeClick");

			$this->menu->items->articoli->addItem("stampalistino", "Esporta listino");
    		$this->intercept($this->menu->items->articoli->items->stampalistino,"onClick", "esporta_listino_menuClick");
		
			$this->menu->items->articoli->addItem("anagcodiva", "Aliquote IVA");
			$this->intercept($this->menu->items->articoli->items->anagcodiva,"onClick", "anagcodivaClick");

			$this->menu->items->articoli->addItem("stampaetichette", "Stampa Etichette");
			$this->intercept($this->menu->items->articoli->items->stampaetichette,"onClick", "stampaetichetteClick");
    
			// -----------------------------------------------------------------	
    		$this->menu->addItem("anagrafiche", "Anagrafiche");
			// -----------------------------------------------------------------	
    
    		$this->menu->items->anagrafiche->addItem("anag_clienti", "Clienti");
    		$this->intercept($this->menu->items->anagrafiche->items->anag_clienti,"onClick", "anag_clientiClick");
    
    		$this->menu->items->anagrafiche->addItem("anagfornitori", "Fornitori");
    		$this->intercept($this->menu->items->anagrafiche->items->anagfornitori,"onClick", "anagfornitoriClick");

    		$this->menu->items->anagrafiche->addItem("anag_utenti_eg", "Utenti");
    		$this->intercept($this->menu->items->anagrafiche->items->anag_utenti_eg,"onClick", "anag_utenti_egClick");

			$this->menu->items->anagrafiche->addItem("sqlnewcli", "Modifica Codice Cliente");
    		$this->intercept($this->menu->items->anagrafiche->items->sqlnewcli,"onClick", "sqlnewcliClick");
			
			$this->menu->items->anagrafiche->addItem("sqlnewfor", "Modifica Codice Fornitore");
    		$this->intercept($this->menu->items->anagrafiche->items->sqlnewfor,"onClick", "sqlnewforClick");

			// -----------------------------------------------------------------	
			$this->menu->addItem("stampe", "Stampe");
			// -----------------------------------------------------------------	

			$this->menu->items->stampe->addItem("stpmovmagaz", "Movimenti di Magazzino");
    		$this->intercept($this->menu->items->stampe->items->stpmovmagaz,"onClick", "stpmovmagaz_click");
    
			$this->menu->items->stampe->addItem("stpinventario", "Inventario");
    		$this->intercept($this->menu->items->stampe->items->stpinventario,"onClick", "stpinventario_click");
    
			$this->menu->items->stampe->addItem("stpprimanota", "Brogliaccio Prima Nota");
    		$this->intercept($this->menu->items->stampe->items->stpprimanota,"onClick", "stpprimanota_click");
    
			// -----------------------------------------------------------------	
    		$this->menu->addItem("servizi", "Servizi");
			// -----------------------------------------------------------------	
    
			$this->menu->items->servizi->addItem("azienda", "Config. Equogest");
			$this->intercept($this->menu->items->servizi->items->azienda,"onClick", "azienda_menuclick");
			$this->menu->items->servizi->items->azienda->setIcon( "misc" );
    	
    		$this->menu->items->servizi->addItem("sqlexecute", "Esegui Query");
    		$this->intercept($this->menu->items->servizi->items->sqlexecute,"onClick", "sqlexecuteClick");
			$this->menu->items->servizi->items->sqlexecute->setIcon( "execute" );


			$this->menu->items->servizi->addItem("nuova_gestione", "Nuova gestione");
    		$this->intercept($this->menu->items->servizi->items->nuova_gestione,"onClick", "nuova_gestioneClick");

			$this->menu->items->servizi->addItem("esporta_dati", "Esporta Dati");
    		$this->intercept($this->menu->items->servizi->items->esporta_dati,"onClick", "esporta_datiClick");


            // -----------------------------------------------------------------    
			$this->menu->addItem("strumenti", "Strumenti");
            // -----------------------------------------------------------------    
            $this->menu->items->strumenti->addItem( "invio_email", "Invio email..." );
            $this->intercept( $this->menu->items->strumenti->items->invio_email, "onClick", "invio_emailClick" );
            $this->menu->items->strumenti->items->invio_email->setIcon( "mail_new" );
    
			$this->menu->items->strumenti->addItem("multigestione", "Multi-gestione");
			$this->intercept($this->menu->items->strumenti->items->multigestione,"onClick", "multigestione_click");


    		// PERICOLOSO
    		//$this->menu->items->servizi->addItem("azzeradb", "Azzera dataBase");
    		//$this->intercept($this->menu->items->servizi->items->azzeradb,"onClick", "azzeradb_click");
		}

		// -----------------------------------------------------------------	
		$this->menu->addItem("aiuto", "?");
		// -----------------------------------------------------------------	
		$this->menu->items->aiuto->addItem("manuale", "Manuale");
		if ( E3G_TIPO_GESTIONE == 'G' )
			$url_manuale = ( defined('GG_URL_MANUALE') ? GG_URL_MANUALE : "docs/Manuale_GestiGAS.pdf" );
		else
			$url_manuale = ( defined('EG_URL_MANUALE') ? GG_URL_MANUALE : "docs/Manuale_Equogest.pdf" );
		$this->menu->items->aiuto->items->manuale->setProperty( "onClick", 
//			"myRef = window.open('$url_manuale', 'Manuale $p4a->e3g_nome_sw'," .
			"myRef = window.open('$url_manuale', ''," .  // Non mettere titoli: non vengono considerati e mandano in errore IE se contengono uno spazio
			"'menubar=no,toolbar=no,statusbar=no,scrollbars=yes,location=no,resizable=yes');myRef.focus()"); 
		$this->menu->items->aiuto->items->manuale->setIcon( "help" );

		$this->menu->items->aiuto->addItem("informazioni", "Info su $p4a->e3g_nome_sw");
		$this->intercept($this->menu->items->aiuto->items->informazioni,"onClick", "informazioni_menuclick");
	}
					
					
	function azzeradb_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
	
		// TROVARE UNA COLLOCAZIONE MENO PERICOLOSA es. sotto password 
		$db->query("DELETE FROM ".$p4a->e3g_prefix."movmagr");
		$db->query("DELETE FROM ".$p4a->e3g_prefix."movconr");
		$db->query("DELETE FROM ".$p4a->e3g_prefix."movcont");
		$db->query("DELETE FROM ".$p4a->e3g_prefix."docr");
		$db->query("DELETE FROM ".$p4a->e3g_prefix."doct");
		$db->query("UPDATE ".$p4a->e3g_prefix."docregistri SET seriale='0'");
	}
    
    
	function stpmovmagaz_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
	
		require("class.report.php");
		$pdf = new Creport('a4','portrait');
			
	
		$this->build("p4a_db_source", "ds_movmag");
		$this->ds_movmag->setTable($p4a->e3g_prefix."movmagr");
		$this->ds_movmag->setWhere("1=1");
		$this->ds_movmag->addOrder("data");
		$this->ds_movmag->load();
		$this->ds_movmag->firstRow();
		
		//$riga = 1 ;
    	//while($riga <= $this->ds_campi->getNumRows())
    	//{
    	//	$arr[$this->ds_campi->fields->campo->getNewValue()] = $this->ds_campi->fields->nomecampo->getNewValue();
    		
    	//	$this->ds_campi->nextRow();
    	//	$riga++;
    	//}	
		$arr["data"] = "data";
		$arr["codarticolo"] = "Articolo";
		$arr["qta"] = "qta";
  		 
  		
		$pdf->stampareport($this->ds_movmag->getAll(), $arr, "Movimenti di Magazzino","movimenti_magazzino");
	}


	function stpprimanota_click()
	{
		$p4a =& p4a::singleton();

		require("class.report.php");
		$pdf = new Creport('a4','portrait');
			
	
		$this->build("p4a_db_source", "ds_movcon");
		$this->ds_movcon->setTable($p4a->e3g_prefix."movconr");
		$query = "SELECT codice, codconto, descrizione, importodare, importoavere FROM ".$p4a->e3g_prefix."movconr ORDER BY codice DESC, importodare, importoavere DESC";
		$this->ds_movcon->setQuery($query);
		$this->ds_movcon->load();
		$this->ds_movcon->firstRow();
		
		$arr["codice"] = "Num. Doc.";
		$arr["codconto"] = "Sottoconto";
		$arr["descrizione"] = "descrizione";
		$arr["importodare"] = "Dare";
		$arr["importoavere"] = "Avere";
		
  		   		
		$pdf->stampareport($this->ds_movcon->getAll(), $arr, "Brogliaccio Prima Nota","primanota");
	}
	
	
	function stpinventario_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		// scorro gli articoli movimentati per cercare, per ognuno, la relativa giacenza
		$oggi = date ("Y-m-d");
		$query = "data<='".$oggi."'";
		$this->build("p4a_db_source", "ds_movmag");
		$this->ds_movmag->setTable($p4a->e3g_prefix."movmagr");
		$this->ds_movmag->setWhere($query);
		$this->ds_movmag->setSelect("codarticolo");
		$this->ds_movmag->setGroup("codarticolo");
		$this->ds_movmag->load();
		$this->ds_movmag->firstRow();
		
		$totale = 0;
		$riga=1;
		//echo $this->ds_movmag->getNumRows()."<br>";
		// calcolato così per via di un errore nel sorgente di P4A
		$numerorighe = $db->queryOne("SELECT count(DISTINCT codarticolo) FROM ".$p4a->e3g_prefix."movmagr WHERE ".$query);
		
		//echo $numerorighe."<br>";
		//die; 
		
		//while($riga<=$this->ds_movmag->getNumRows())
		$Array = array();
		while($riga<=$numerorighe )
		{
			// per ogni articolo nella tabella movmag vado a cercare la relativa giacenza
			// e poi la stampo sul pdf
	
			// carico la giacenza per l'articolo richiamato
			$descrizione = $db->queryOne("SELECT descrizione FROM ".$p4a->e3g_prefix."articoli WHERE codice='".$this->ds_movmag->fields->codarticolo->getNewValue()."'");
			$prezzoacq = $db->queryOne("SELECT prezzoacq FROM ".$p4a->e3g_prefix."articoli WHERE codice='".$this->ds_movmag->fields->codarticolo->getNewValue()."'");
			$qta = $this->giacenza($this->ds_movmag->fields->codarticolo->getNewValue());
			$valore = $prezzoacq * $qta ;
			
			$totale = $totale + $valore;
			
			$strriga = str_pad($this->ds_movmag->fields->codarticolo->getNewValue(), 20, "_", STR_PAD_RIGHT).str_pad($descrizione, 40, "_", STR_PAD_RIGHT).str_pad($qta, 7, "_", STR_PAD_RIGHT).str_pad($prezzoacq, 7, "_", STR_PAD_RIGHT).str_pad($valore, 6, "_", STR_PAD_RIGHT);
			// aggiungo riga
			//$this->report->add_text($strriga,10,array('justification'=>'left'));
			
			//echo $this->ds_movmag->fields->codarticolo->getNewValue()."<br>";
			//echo $strriga."<br>";
			
			//$Array["riga"]=$strriga;
			$Array[$riga]=array("codice" => $this->ds_movmag->fields->codarticolo->getNewValue(), "descrizione" => $descrizione, "Q.ta" => $qta, "prezzo" => $prezzoacq, "Valore" => $valore );
			
			//$multiarray= array("riga1" => array("testo" => $strriga,
            //                    "campo2" => "valore2A",
            //                    "campo3" => "valore3A"),
            //   "riga2" => array("campo1" => "valore1B",
            //                    "campo2" => "valore2B",
            //                    "campo3" => "valore3B") 				

			//$this->ds_movmag->nextRow();
			$riga++;
			$this->ds_movmag->Row($riga);
		}

		
		// TOTALI 
		//$this->report->add_text("",10,array('justification'=>'left'));
		//$this->report->add_text("",10,array('justification'=>'left'));
		//$this->report->add_text("TOTALE: ".$totale."             ",10,array('justification'=>'right'));
		
		$this->ds_movmag->destroy();
		//die; 

		//print_r($Array);
		//die; 
		
		require("class.report.php");
		$pdf = new Creport('a4','portrait');
		

		$arr["codice"] = "codice";
		$arr["descrizione"] = "descrizione";
		$arr["Q.ta"] = "Q.ta";
		$arr["prezzo"] = "prezzo";
		$arr["Valore"] = "Valore";
		
	
		$pdf->stampareport($Array, $arr, "Inventario","inventario");
	}
	
	
	function giacenza ($codicearticolo)
	{
		/////////////////////////////////////////////////
		// Provo a Calcolare la Giacenza 	 ///////////
		///////////////////////////////////////////////
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		// Azzero le variabili
		$oggi = date ("y-m-d");
		$ultimagiacenza ="";
		$totale=0;

		// recupero la data dell'ultimo movimento di Giacenza
		$query = "SELECT data FROM ".$p4a->e3g_prefix."movmagr WHERE carscar ='G' AND codarticolo='".$codicearticolo."' AND data<='".$oggi."' ORDER BY data DESC";
		$ultimagiacenza = $db->queryOne($query);


		if ($ultimagiacenza != "") 
		{
			// ho la data dell'ultima giacenza
			// ricavo subito la giacenza
			$query = "SELECT SUM(qta) AS quantita FROM ".$p4a->e3g_prefix."movmagr WHERE codarticolo='".$codicearticolo."' AND data>='".$ultimagiacenza."' AND data<='".$oggi."'";
			$totale = $db->queryOne($query);
		}
		Else 
		{
			// non ho l'ultima giacenza, quindi non filtro per data

			// Ricavo la qta caricata dall'inizio del database
			$query = "SELECT SUM(qta) AS quantita FROM ".$p4a->e3g_prefix."movmagr WHERE codarticolo='".$codicearticolo."' AND data<='".$oggi."'";
			$totale = $db->queryOne($query);
		}

		// mostro la giacenza ad oggi
		return $totale ;
	}
	

	function esciClick()
	{
		// 1) Modalità normale di chiusura: redireziona alla pagina standard di login (/index.php)
		//$this->restart();
		
		// 2) Altra modalità utilizzata quando il login era remoto
/*		
		$gestigas_url_to = $_SESSION['gestigas_url_to'];
    	
    	// Attenzione che dopo il seguente close() le variabili di sessione scompaiono
		$this->close();

		if ( isset($gestigas_url_to) )
			header( 'Location: ' . $gestigas_url_to );
		else
			header('Location: ' . P4A_APPLICATION_PATH );
*/		

		// Modalità attuale (rimette il parametro prefisso in coda all'url)
		$p4a =& p4a::singleton();

		$this->close();

		if ( isset($p4a->e3g_prefix) )
			header( "Location: " . P4A_APPLICATION_PATH . "?prefix=$p4a->e3g_prefix" );
		else
			header( "Location: " . P4A_APPLICATION_PATH );
	}
	
	
	function esporta_listino_menuClick()
	{
		$p4a =& p4a::singleton();
    	$p4a->openMask( "esporta_listino" );
	}

	
	function nuova_gestioneClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask( "nuova_gestione" );    
    }

	function esporta_datiClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask( "esporta_dati" );    
    }

    function sqlnewforClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask( "sqlnewfor" );
    }

	
	function sqlnewcliClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask( "sqlnewcli" );
    }

	
    function sqlnewcodeClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask( "sqlnewcode" );
    }


	function sqlexecuteClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask( "sqlexecute" );
    }

	
	function azienda_menuclick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask( "preferenze" );
    }
	

	function homeClick()
    {
    	$p4a =& p4a::singleton();

		$this->set_menu_utente();
		
		if ( E3G_TIPO_GESTIONE == 'G' )
			$p4a->openMask( "about_user" );
		else
			$p4a->openMask( "about_admin" );
    }            		


	function cassa_archivioClick()
    {
    	$p4a =& p4a::singleton();
		$p4a->openMask( "archivio_ordini" );
    }            		


    function statistiche_ordiniClick()
    {
        $p4a =& p4a::singleton();
        $p4a->openMask( "statistiche_ordini" );
    }                   


	function ordine_globaleClick()
    {
    	$p4a =& p4a::singleton();
		$p4a->openMask("cassa_gg_globale");
    }            		

		
	function cassaClick()
    {
    	$p4a =& p4a::singleton();

		if ( E3G_TIPO_GESTIONE == 'G' )
			$p4a->openMask( "cassa_gg_singolo" );
		else
			$p4a->openMask( "cassa_eg" );
    }            		

	
	function multigestione_click()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask( "multigestione" );
    }            		

		
	function gesdocumentiClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask( "gesdocumenti" );
    }            		

	
	function gesdocumenti1Click()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("gesdocumenti1");
    }            		

	
	function estraz_ordini_fornitoreClick()                    
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("estraz_ordini_fornitore");
    }            		
    
	
    function modifica_prezzi_art_click()                    
    {
        $p4a =& p4a::singleton();
        $p4a->openMask("modifica_prezzi_articoli");
    }                   


    function modifica_qta_art_click()                    
    {
        $p4a =& p4a::singleton();
        $p4a->openMask("modifica_quantita_articoli");
    }                   


	function documentiestrattiClick()                    
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("documentiestratti");
    }
    

/*	
    function consegna_utente_totaleClick()                    
    {
    	$p4a =& p4a::singleton();
    	$db =& p4a_db::singleton();
		
		
		$righe_carrello = $db->queryOne( "SELECT COUNT(*) as Righe FROM ".$p4a->e3g_prefix."carrello" );	
		if ( $righe_carrello == 0 ) 
		{
			// non ho più righe nel carrello tutti gli articoli sono già stati estratti per i fornitori
			$p4a->openMask("consegna_utente_totale");
		}
		else
		{
			// ho ancora delle righe nel carrello 
			// blocco la consegna a utente 
		}
		
		
		
    	
    }
*/

	function documenti_righe_estratteClick()                    
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("documenti_righe_estratte");
    }

	function consegna_utente_chiusuraClick()                    
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("consegna_utente_chiusura");
    }
	
	
	function consegna_utenteClick()                    
    {
    	$p4a =& p4a::singleton();
		
       	$p4a->openMask("consegna_utente");
    }


	function doc_estrazione_egClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("doc_estrazione_eg");
    }


	function doc_registriClick()
	{
    	$p4a =& p4a::singleton();
    	$p4a->openMask("doc_registri");
    }
	
    function pianodeicontiClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("piano_dei_conti");
    }
	
    function anagpagamentiClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("anagpagamenti");
    }
    

    function doctipidocClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("doctipidoc");
    }
	
    function opzioni_esport_pdfClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("opzioni_esport_pdf");
    }
    
    
    function anagarticoliClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("anagarticoli");
    }
    
	            
    function anagtipiarticoliClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("anagtipiarticoli");
    }
    

	
	function anagumClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("anagum");
    }
	
    function anagprogettiClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("anagprogetti");
    }
		            
    function anagcatmerceClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("anagcatmerce");
    }
      
	          		
    function anagcodivaClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("anagcodiva");
    }
    
	
    function periodoarticoliClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("periodoarticoli");
    }
    

	function anagprofiloClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("anagprofilo");
    }
	  
	              
    function anag_clientiClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("anag_utentigg_clientieg");
    }
    
	
    function login_gestClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("login_gest");
    }
    
	
    function login_databaseClick()            
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("login_database");
    }            
    
	
	function login_tipoClick()      
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask("login_tipo");
    }            

    
	function referenti_fornitori_menuClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask( "referenti_fornitori" );
    }
	
    
	function stampaetichetteClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask( "stampaetichette" );
    }
	
	
	function anag_utenti_egClick()
	{
		$p4a =& p4a::singleton();
    	$p4a->openMask( "anag_utenti_eg" );
	}	    
    
	
	function anagfornitoriClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask( "anag_fornitori" );
    }
                		
    
	function periodo_ordiniClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask( "periodo_ordini" );
    }

    
	function informazioni_menuClick()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask( "informazioni" );
    }
	
    
	function luoghi_consegna_click()
    {
    	$p4a =& p4a::singleton();
    	$p4a->openMask( "luoghi_consegna" );
    }
	
    
	function adminClick()
    {
		$p4a =& p4a::singleton();
		
		$this->set_menu_avanzato(); 	
		$p4a->openMask( "about_admin" );	
    }
	
    
    function invio_emailClick()
    {
        $p4a =& p4a::singleton();
        $p4a->openMask( "invio_email" );  
    }
    
    
    function mailing_list_admin_click()
    {
        $p4a =& p4a::singleton();
        $p4a->openMask( "mailing_list_admin" );  
    }


    function cronClick()
    {
        e3g_cron();  
    }
    

    function gestione_cassaClick()
    {
        $p4a =& p4a::singleton();
        $p4a->openMask( "cassa_comune" );  
    }
    
}
?>
