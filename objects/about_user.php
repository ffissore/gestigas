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


class about_user extends P4A_Mask
{

	// -------------------------------------------------------------------------
	function about_user()
	// -------------------------------------------------------------------------
	{
		// GestiGAS: finestra iniziale semplificata per normali utenti
		
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
 		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
        $this->setTitle("");  // Nessun titolo

        // Message
        $message =& $this->build("p4a_message", "message");
        $message->setWidth("500");

    // Colonna SINISTRA con riassunto ordini aperti o prossimi -----------------
	
        // Immagine logo (come box HTML anzichè p4a_image altrimenti non si centra)
        $src_logo = 'images/gestigas_02.jpg';
        $this->build("p4a_box", "box_logo");
        $this->box_logo->setValue( '<div align="center"><img src="' . $src_logo .
            '" alt="Progetto e3g - Equogest/GestiGAS" /></div>' );
        
    	$lbl_info =& $this->build("p4a_label", "lbl_info");


    // Colonna DESTRA con bottoni vari -----------------------------------------
    
        $button_width = 160;
        $lbl_info->setWidth( E3G_MAIN_FRAME_WIDTH-$button_width-25 );
        
        // Pulsante "Aggiorna database" (tabelle specifiche)
        $this->build("p4a_button", "bu_aggiorna_db_multi");
        $this->bu_aggiorna_db_multi->setLabel("Aggiorna database...");
        $this->bu_aggiorna_db_multi->setWidth( $button_width );
        $this->bu_aggiorna_db_multi->setIcon( "warning" );
        $this->bu_aggiorna_db_multi->setInvisible();
        $this->bu_aggiorna_db_multi->addAction("onClick");
        $this->bu_aggiorna_db_multi->requireConfirmation( "onClick", "Confermi l'operazione di aggiornamento del database ?" );
        $this->intercept($this->bu_aggiorna_db_multi, "onClick", "aggiorna_db_multi_click");

        // Pulsante "Aggiorna database" (tabelle condivise)
        $this->build("p4a_button", "bu_aggiorna_db_cond");
        $this->bu_aggiorna_db_cond->setLabel("Aggiorna database (tab. condivise)...");
        $this->bu_aggiorna_db_cond->setWidth( $button_width );
        $this->bu_aggiorna_db_cond->setIcon( "warning" );
        $this->bu_aggiorna_db_cond->setInvisible();
        $this->bu_aggiorna_db_cond->addAction("onClick");
        $this->bu_aggiorna_db_cond->requireConfirmation( "onClick", "Confermi l'operazione di aggiornamento del database (tabelle condivise) ?" );
        $this->intercept($this->bu_aggiorna_db_cond, "onClick", "aggiorna_db_cond_click");

        // Pulsante "Amministra"
		$bu_amministra =& $this->build("p4a_button", "bu_amministra");
		$bu_amministra->setLabel("Amministra");
		$bu_amministra->setWidth( $button_width );
		$bu_amministra->setIcon( "admin" );
		$this->bu_amministra->addAction("onClick");
		$this->intercept($this->bu_amministra, "onClick", "bu_amministraClick");

        // Pulsante "Ordine corrente"
        $bu_ordine_corrente =& $this->build("p4a_button", "bu_ordine_corrente");
        $bu_ordine_corrente->setLabel("Ordine corrente");
        $bu_ordine_corrente->setWidth( $button_width );
        $bu_ordine_corrente->setIcon( "kwrite" );
        $this->bu_ordine_corrente->addAction("onClick");
        $this->intercept($this->bu_ordine_corrente, "onClick", "bu_ordine_correnteClick");

        // Pulsante "Listino articoli"
        $bu_listino =& $this->build("p4a_button", "bu_listino");
        $bu_listino->setLabel("Listino articoli");
        $bu_listino->setWidth( $button_width );
        $bu_listino->setIcon( "articoli" );
        $this->bu_listino->addAction("onClick");
        $this->intercept($this->bu_listino, "onClick", "bu_listinoClick");

        // Pulsante "Esporta listino"
		$bu_esporta_listino =& $this->build("p4a_button", "bu_esporta_listino");
		$bu_esporta_listino->setLabel("Esporta listino...");
		$bu_esporta_listino->setWidth( $button_width );
		$bu_esporta_listino->setIcon( "pdf" );
		$this->bu_esporta_listino->addAction("onClick");
		$this->intercept($this->bu_esporta_listino, "onClick", "bu_esporta_listinoClick");

        // Pulsante "Esci"
		$bu_esci =& $this->build("p4a_button", "bu_esci");
		$bu_esci->setLabel("Esci");
		$bu_esci->setWidth( $button_width );
		$bu_esci->setIcon( "on_off" );
		$this->bu_esci->addAction("onClick");
		$this->intercept($this->bu_esci, "onClick", "bu_esciClick");

		 
		// Eventuale aggiornamento database ------------------------------------
		
		switch ( $p4a->e3g_utente_tipo ) {
			case "A":
			case "AS":
				$dbver = $db->queryOne( "SELECT dbver FROM _aziende WHERE prefix = '" . $p4a->e3g_prefix . "'" );	
				if ( $dbver < E3G_DB_MULTI_VERSIONE_ATTESA ) 
					$this->bu_aggiorna_db_multi->setVisible();

				$dbver = $db->queryOne( "SELECT dbver FROM _config" );	
				if ( $dbver < E3G_DB_COND_VERSIONE_ATTESA ) 
					$this->bu_aggiorna_db_cond->setVisible();
				break;
		}
		
        // Ancoraggi -----------------------------------------------------------

        // Sheet pulsanti colonna destra		
		$sh_pulsanti =& $this->build("p4a_sheet", "sh_pulsanti", 9);
        $this->sh_pulsanti->anchor($this->bu_aggiorna_db_multi, 1);
        $this->sh_pulsanti->anchor($this->bu_aggiorna_db_cond, 2);
    	if ( $p4a->e3g_utente_tipo == "A" or $p4a->e3g_utente_tipo == "AS" or $p4a->e3g_utente_tipo == "R" ) 
			$this->sh_pulsanti->anchor($this->bu_amministra, 3);
        // 4: spazio                    	        	        
        $this->sh_pulsanti->anchor($this->bu_ordine_corrente, 5);                    
        $this->sh_pulsanti->anchor($this->bu_listino, 6);                    
        $this->sh_pulsanti->anchor($this->bu_esporta_listino, 7);
        // 8: spazio
        $this->sh_pulsanti->anchor($this->bu_esci, 9);  
        
		// Sheet principale (con logo, info e sheet pulsanti)
        $sh_pulsanti =& $this->build("p4a_sheet", "sh_main");
        $this->sh_main->defineGrid(2, 2);
        $this->sh_main->anchor($this->box_logo, 1, 1);
        $this->sh_main->anchor($this->lbl_info, 2, 1);
        $this->sh_main->anchor($this->sh_pulsanti, 1, 2, 2, 1);


		// Frame
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth( E3G_MAIN_FRAME_WIDTH );

        $frm->anchor($this->message);
        $frm->anchor($this->sh_main);

		e3g_scrivi_footer( $this, $frm );

  		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
	}

	
	// -------------------------------------------------------------------------
	function main()
	// -------------------------------------------------------------------------
	{
		$this->aggiorna_intro_text();
		
		parent::main();
	}
	
	
    // -------------------------------------------------------------------------
    function bu_amministraClick()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $e3g_main =& e3g_main::singleton(); 
        
        $e3g_main->set_menu_avanzato();     

        $p4a->openMask("about_admin");  
    }
    
    
    // -------------------------------------------------------------------------
    function bu_ordine_correnteClick()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $p4a->openMask('cassa_gg_singolo');
    }
    
    
	// -------------------------------------------------------------------------
	function bu_listinoClick()
	// -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$p4a->openMask('anagarticoli');
	}


    // -------------------------------------------------------------------------
    function bu_esporta_listinoClick()
    // -------------------------------------------------------------------------
    {
        $e3g_main =& e3g_main::singleton(); 
        $e3g_main->esporta_listino_menuClick();     
    }
    
    
	// -------------------------------------------------------------------------
	function bu_esciClick()
	// -------------------------------------------------------------------------
	{
		$e3g_main =& e3g_main::singleton(); 
		$e3g_main->esciClick(); 	
	}
	
	
	// -------------------------------------------------------------------------
	function aggiorna_db_multi_click()
	// -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		
		$result = (array)e3g_aggiorna_database( $p4a->e3g_prefix );
		$this->message->setIcon( $result[0] ? "info" : "warning" );
		$this->message->setValue( $result[1] );
		
		$this->bu_aggiorna_db_multi->setInvisible();
	}


	// -------------------------------------------------------------------------
	function aggiorna_db_cond_click()
	// -------------------------------------------------------------------------
	{
		$result = (array)e3g_aggiorna_database( "#COND#" );
		$this->message->setIcon( $result[0] ? "info" : "warning" );
		$this->message->setValue( $result[1] );
		
		$this->bu_aggiorna_db_cond->setInvisible();
	}


	// -------------------------------------------------------------------------
	function aggiorna_intro_text()
	// -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

        $testo = "";

/* ESEMPIO
    Oggi: Tuesday 5 January 2010
    
    Ciao Mario Rossi, questo e' il tuo 123' collegamento (il precedente e' avvenuto il 05/01/2010 alle 13:00).
    Il tuo credito/debito nella cassa comune risulta di 60 euro.
    
    Non risulta attualmente aperto alcun ordine; il prossimo periodo sara' il:
        * da Monday 18 January al 1 February: Az. Agr. Michele Guidetti (18 articoli)
        * da Tuesday 15 June al 15 July: Az. Agr. Alfredo Andreoli / Frutta e verdura (4 articoli)
    
    Ultimi articoli inseriti/modificati:
        * CTM / Mappamondo: "Atamisqui" - miele monoflor'a - bio - argentina - ABC [0.5 kg] 3.698 euro
        * Coop. Agricola IRIS: Chifferi Bio al Farro Integrale 500 gr. [0.5 kg] 1.96 euro
 */
		// --------------------------------------- Introduzione con data e login
		if ( $p4a->e3g_azienda_db_multi_versione >= '0010' ) {
            // Vecchie versioni di GestiGAS (<= 0.8.0 del 22/10/2006)
			$login = $db->queryRow(
				"SELECT n_login, last_login FROM " . $p4a->e3g_prefix . "anagrafiche " .
				" WHERE codice = '" . $p4a->e3g_utente_codice . "'" );
			
			$testo .=
				"<p><em>Oggi: " . date( "l j F Y" ) . "</em></p>" .
				"<p>Ciao <strong>$p4a->e3g_utente_desc</strong>, questo e' il tuo " . $login["n_login"] .
					"' collegamento" . ($login["n_login"]>1 ? " (il precedente e' avvenuto il " . 
					$p4a->e3g_utente_desc_last_login . ")" : "") . ".</p>";
		}
		else {
            // Versioni GestiGAS > 0.8.0
			$testo .=
				"<p><em>Oggi: " . date( "l j F Y" ) . "</em></p>" .
				"<p>Ciao, <strong>$p4a->e3g_utente_desc</strong>.</p>";
		}

        // --------------------------------------------- Credito/debito in cassa
        if ( $p4a->e3g_azienda_gestione_cassa ) {
            $saldo_utente = (double) $db->queryOne(
                "SELECT SUM( importo ) FROM _cassa " .
                " WHERE prefix = '" . $p4a->e3g_prefix . "' AND  validato = 1" . 
                "   AND id_utente_rif = " . $p4a->e3g_utente_idanag );
            $testo .=
                "<p>Il tuo " . ( $saldo_utente>=0 ? "credito" : "debito" ) . " nella cassa comune risulta di <strong>$saldo_utente euro</strong>.</p>";
        }
		
        // ------------------------- Eventuali utenti in attesa (solo per admin)
        switch ($p4a->e3g_utente_tipo) {
            case "A":
            case "AS":
                $inattesa = $db->queryOne(
                    "SELECT COUNT(*) as Valore FROM ".$p4a->e3g_prefix."anagrafiche " .
                    " WHERE stato=0 AND tipocfa = 'C'" );
                            
                if ( $inattesa > 0 )
                    $testo .= "<p>" . ($inattesa==1 ? "C'e'" : "Ci sono" ) . " $inattesa utent" . ($inattesa==1 ? "e" : "i" ) .
                        " in <strong>attesa di abilitazione</strong>.</p>";               

                break;
        }       

		// ------------------------------------------------------- Ordini aperti		
		$n_ordini_aperti = $db->queryOne(
			"SELECT COUNT(*) FROM " . $p4a->e3g_prefix . "fornitoreperiodo WHERE " . e3g_where_ordini_aperti() );

		if ( $n_ordini_aperti > 0 ) 
            $testo .= 
                "<p>Risultano attualmente aperti gli ordini verso i seguenti fornitori:</p>" .
                e3g_get_html_elenco_prossime_chiusure();
		else {
			$testo .= 
                "<p>Non risulta attualmente aperto alcun ordine; il prossimo periodo sara' il:</p>" .
                e3g_get_html_elenco_prossime_aperture();
		}
		
        // --------------------------------- Ultimi articoli inseriti/modificati        
        $this->build( "p4a_db_source", "ds_art" );
        $this->ds_art->setSelect( "f.descrizione AS fornitore, a.descrizione, a.um_qta, a.um, a.prezzoven, a.data_ins, a.data_agg" );
        $this->ds_art->setTable( $p4a->e3g_prefix . "articoli AS a" );
        $this->ds_art->addJoin( $p4a->e3g_prefix . "anagrafiche AS f", "a.centrale = f.codice" );
        $this->ds_art->setWhere( "a.stato = 1" );
        $this->ds_art->addOrder( "a.data_agg", "DESC" );
        $this->ds_art->load();
        $this->ds_art->firstRow();

        if ( $this->ds_art->getAll(0, 5) ) {
            $testo .= "<p>Ultimi articoli inseriti/modificati:</p><ul>";
            for( $riga=1; $riga<=5; $riga++ ) {       
                $testo .= "<li>" . $this->ds_art->fields->fornitore->getnewValue() . ": " .
                    "<strong>" . $this->ds_art->fields->descrizione->getnewValue() . "</strong> " .
                    "[" . $this->ds_art->fields->um_qta->getnewValue() . " " . $this->ds_art->fields->um->getnewValue() . "] " .
                    $this->ds_art->fields->prezzoven->getnewValue() . " euro" .
                    ( $this->ds_art->fields->data_ins->getnewValue() == $this->ds_art->fields->data_agg->getnewValue() ? 
                        " - <strong>NEW</strong>" : "" ) .
                    "</li>";
                $this->ds_art->nextRow();
            }
            $testo .= "</ul>";
        }
            
		// ----------------------------------------------------- Visualizzazione
		$this->lbl_info->setValue( $testo );
	}
	
}

?>