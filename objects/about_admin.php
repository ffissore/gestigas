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


class about_admin extends P4A_Mask
{
	function about_admin()
	{
		// MASCHERA ABOUT AVANZATA PER AMMINISTRATORI, REFERENTI, ... 

 		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
 		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

        $this->setTitle("");


    // Colonna SINISTRA con bottoni vari ---------------------------------------

        $button_width = 180;
        
		// Pulsante Aggiorna database (tabelle specifiche)
		$this->build("p4a_button", "bu_aggiorna_db_multi");
		$this->bu_aggiorna_db_multi->setLabel("Aggiorna database...");
		$this->bu_aggiorna_db_multi->setIcon( "warning" );
        $this->bu_aggiorna_db_multi->setWidth( $button_width );
		$this->bu_aggiorna_db_multi->setInvisible();
        $this->bu_aggiorna_db_multi->addAction("onClick");
		$this->bu_aggiorna_db_multi->requireConfirmation( "onClick", "Confermi l'operazione di aggiornamento del database ?" );
        $this->intercept($this->bu_aggiorna_db_multi, "onClick", "aggiorna_db_multi_click");

		// Pulsante Aggiorna database (tabelle condivise)
		$this->build("p4a_button", "bu_aggiorna_db_cond");
		$this->bu_aggiorna_db_cond->setLabel("Aggiorna database (tab. condivise)...");
		$this->bu_aggiorna_db_cond->setIcon( "warning" );
        $this->bu_aggiorna_db_cond->setWidth( $button_width );
		$this->bu_aggiorna_db_cond->setInvisible();
        $this->bu_aggiorna_db_cond->addAction("onClick");
		$this->bu_aggiorna_db_cond->requireConfirmation( "onClick", "Confermi l'operazione di aggiornamento del database (tabelle condivise) ?" );
        $this->intercept($this->bu_aggiorna_db_cond, "onClick", "aggiorna_db_cond_click");

        
        $bu_home =& $this->build("p4a_button", "bu_home");
        $bu_home->setLabel("HOME");
        $bu_home->setIcon( "home" );
        $bu_home->setWidth( $button_width );
        $this->bu_home->addAction("onClick");
        $this->intercept($this->bu_home, "onClick", "bu_homeClick");


        $bu_articoli =& $this->build("p4a_button", "bu_articoli");
        $bu_articoli->setLabel("Listino articoli");
        $bu_articoli->setIcon( "articoli" );
        $bu_articoli->setWidth( $button_width );
        $this->bu_articoli->addAction("onClick");
        $this->intercept($this->bu_articoli, "onClick", "bu_articoliClick");

        $bu_fornitori =& $this->build("p4a_button", "bu_fornitori");
        $bu_fornitori->setLabel("Anagrafica Fornitori");
        $bu_fornitori->setIcon( "users" );
        $bu_fornitori->setWidth( $button_width );
        $this->bu_fornitori->addAction("onClick");
        $this->intercept($this->bu_fornitori, "onClick", "bu_fornitoriClick");
         
        $bu_anag_utenti =& $this->build("p4a_button", "bu_anag_utenti");
        $bu_anag_utenti->setLabel( E3G_TIPO_GESTIONE == 'G' ? "Anagrafica Utenti" : "Anagrafica Clienti");
        $bu_anag_utenti->setIcon( "users" );
        $bu_anag_utenti->setWidth( $button_width );
        $this->bu_anag_utenti->addAction("onClick");
        $this->intercept($this->bu_anag_utenti, "onClick", "bu_anag_utentiClick");


        $bu_ordine_globale =& $this->build("p4a_button", "bu_ordine_globale");
        $bu_ordine_globale->setLabel("Ordine globale");
        $bu_ordine_globale->setIcon( "kwrite" );
        $bu_ordine_globale->setWidth( $button_width );
        $this->bu_ordine_globale->addAction("onClick");
        $this->intercept($this->bu_ordine_globale, "onClick", "bu_ordine_globaleClick");

        $bu_estrai_ordine_for =& $this->build("p4a_button", "bu_estrai_ordine_for");
        $bu_estrai_ordine_for->setLabel("Estrai ordine fornitore");
        $bu_estrai_ordine_for->setIcon( "execute" );
        $bu_estrai_ordine_for->setWidth( $button_width );
        $this->bu_estrai_ordine_for->addAction("onClick");
        $this->intercept($this->bu_estrai_ordine_for, "onClick", "bu_estrai_ordine_forClick");

        $this->build("p4a_button", "bu_modifica_prezzi_articoli");
        $this->bu_modifica_prezzi_articoli->setLabel("Modifica prezzi articoli");
        $this->bu_modifica_prezzi_articoli->setIcon( "execute" );
        $this->bu_modifica_prezzi_articoli->setWidth( $button_width );
        $this->bu_modifica_prezzi_articoli->addAction("onClick");
        $this->intercept($this->bu_modifica_prezzi_articoli, "onClick", "bu_modifica_prezzi_articoliClick");

        $this->build("p4a_button", "bu_modifica_quantita_articoli");
        $this->bu_modifica_quantita_articoli->setLabel("Modifica Q.ta articoli");
        $this->bu_modifica_quantita_articoli->setIcon( "execute" );
        $this->bu_modifica_quantita_articoli->setWidth( $button_width );
        $this->bu_modifica_quantita_articoli->addAction("onClick");
        $this->intercept($this->bu_modifica_quantita_articoli, "onClick", "bu_modifica_quantita_articoliClick");
        
		$bu_consegna_utente =& $this->build("p4a_button", "bu_consegna_utente");
		$bu_consegna_utente->setLabel("Consegna all'utente");
        $bu_consegna_utente->setIcon( "execute" );
		$bu_consegna_utente->setWidth( $button_width );
		$this->bu_consegna_utente->addAction("onClick");
		$this->intercept($this->bu_consegna_utente, "onClick", "bu_consegna_utenteClick");
		

    // Colonna DESTRA con logo e info varie ------------------------------------

        // Immagine logo (come box HTML anzichè p4a_image altrimenti non si centra)
        if ( E3G_TIPO_GESTIONE == 'G' )
            $src_logo = 'images/gestigas_01.jpg';
        else
            $src_logo = 'images/equogest_01.jpg';
        $this->build("p4a_box", "box_logo");
        $this->box_logo->setValue( '<div align="center"><img src="' . $src_logo .
            '" alt="Progetto e3g - Equogest/GestiGAS" /></div>' );

        $lbl_info =& $this->build("p4a_label", "lbl_info");
        $lbl_info->setWidth( E3G_MAIN_FRAME_WIDTH-$button_width-25 );
        

		// Eventuale aggiornamento database ------------------------------------
		
		$dbver = $db->queryOne( "SELECT dbver FROM _aziende WHERE prefix = '" . $p4a->e3g_prefix . "'" );	
		if ( $dbver < E3G_DB_MULTI_VERSIONE_ATTESA ) 
			$this->bu_aggiorna_db_multi->setVisible();
			
		$dbver = $db->queryOne( "SELECT dbver FROM _config" );	
		if ( $dbver < E3G_DB_COND_VERSIONE_ATTESA ) 
			$this->bu_aggiorna_db_cond->setVisible();
			
		// Ancoraggi -----------------------------------------------------------
        
        // Sheet pulsanti colonna sinistra        
        $sh_pulsanti =& $this->build("p4a_sheet", "sh_pulsanti", 14);
        $this->sh_pulsanti->anchor($this->bu_aggiorna_db_multi, 1);
        $this->sh_pulsanti->anchor($this->bu_aggiorna_db_cond, 2);
        // 3: spazio
        if ( E3G_TIPO_GESTIONE == 'G' ) {
            $this->sh_pulsanti->anchor( $this->bu_home, 4 );
            // 5: spazio
            $this->sh_pulsanti->anchor( $this->bu_articoli, 6 );
            $this->sh_pulsanti->anchor( $this->bu_fornitori, 7 );
            $this->sh_pulsanti->anchor( $this->bu_anag_utenti, 8 );
            // 9: spazio
            $this->sh_pulsanti->anchor( $this->bu_ordine_globale, 10 );
            $this->sh_pulsanti->anchor( $this->bu_estrai_ordine_for, 11 );
            $this->sh_pulsanti->anchor( $this->bu_modifica_prezzi_articoli, 12 );
            $this->sh_pulsanti->anchor( $this->bu_modifica_quantita_articoli, 13 );
            $this->sh_pulsanti->anchor( $this->bu_consegna_utente, 14 );
        }       
        
        // Sheet principale (con logo, info e sheet pulsanti)
        $sh_pulsanti =& $this->build("p4a_sheet", "sh_main");
        $this->sh_main->defineGrid(2, 2);
        $this->sh_main->anchor($this->box_logo, 1, 2);
        $this->sh_main->anchor($this->lbl_info, 2, 2);
        $this->sh_main->anchor($this->sh_pulsanti, 1, 1, 2, 1);


		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("500");


		if ( E3G_TIPO_GESTIONE == 'E' ) {
			// Controllo la coerenza delle date e segnalo eventuali stranezze
			$last_login = $db->queryOne( "SELECT YEAR(last_login) FROM ".$p4a->e3g_prefix ."anagrafiche WHERE idanag = '" . $p4a->e3g_utente_idanag. "'" );	
				
			if (intval($last_login) != intval(date("Y")))
				$message->setValue("ATTENZIONE! Dall'ultimo Login e' cambiato l'anno. Verificare l'Anno Contabile impostato.");
			
			if (intval($p4a->e3g_azienda_anno_contabile) != intval(date("Y")))
				$message->setValue("ATTENZIONE! L'Anno Contabile impostato non corrisponde alla data di sistema. Verificare le impostazioni del programma.");
		}
		
		
		// Frame
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(E3G_MAIN_FRAME_WIDTH);

		$frm->anchor($this->message);
		$frm->anchor($this->sh_main);
		
		e3g_scrivi_footer( $this, $frm );
		
  		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
	}


	function main()
	{
        // testo pagina intro (compilato solo per Gestigas)
		if ( E3G_TIPO_GESTIONE == 'G' )
		{
			$this->aggiorna_intro_text();
		}
		
        
		parent::main();
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

    function bu_homeClick()
    {
        $p4a =& p4a::singleton();
        $p4a->openMask("about_user");
    }                   


	function bu_articoliClick()
	{
		$p4a =& p4a::singleton();
		$p4a->openMask('anagarticoli');
	}


    function bu_fornitoriClick()
    {
        $p4a =& p4a::singleton();
        $p4a->openMask('anag_fornitori');
    }


    function bu_anag_utentiClick()
    {
        $p4a =& p4a::singleton();
        $p4a->openMask('anag_utentigg_clientieg');
    }


	function bu_ordine_globaleClick()
	{
		$p4a =& p4a::singleton();
		if ( E3G_TIPO_GESTIONE == 'G' )
			$p4a->openMask('cassa_gg_globale');
		else
			$p4a->openMask('cassa_eg');
	}


    function bu_estrai_ordine_forClick()
    {
        $p4a =& p4a::singleton();
        $p4a->openMask('estraz_ordini_fornitore');
    }


    function bu_modifica_quantita_articoliClick()
    {
        $p4a =& p4a::singleton();
        $p4a->openMask('modifica_quantita_articoli');
    }
    
    function bu_modifica_prezzi_articoliClick()
    {
        $p4a =& p4a::singleton();
        $p4a->openMask('modifica_prezzi_articoli');
    }

	function bu_consegna_utenteClick()
	{
		$p4a =& p4a::singleton();
		$p4a->openMask('consegna_utente');
	}


    // -------------------------------------------------------------------------
    function aggiorna_intro_text()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        $testo = "";
        
/* ESEMPIO
    57 utenti = ( 46 attivi + 11 disabilitati ) = ( 2 amministratori + 5 referenti + 50 normali utenti )
    
    Ultimi utenti che si sono collegati: Marco Munari (il 30/06/2009 alle 16:00), Andrea Piazza (il 09/03/2009 alle 09:26)
    
    Ordine globale: 3 utenti hanno ordinato 19 articoli diversi in quantita' di 143 pezzi (da 5 fornitori)
    per un importo totale di 284.515 euro.
    
    Cassa comune: è presente un credito/debito pari a 1234 euro; ci sono 12 movimenti in attesa di validazione.
*/        
        // ----------------------------------------------------- Utenti iscritti
        // Vengono comunque esclusi dal conteggio gli admin globali
        $query_txt = "SELECT COUNT(*) FROM " . $p4a->e3g_prefix . "anagrafiche WHERE tipocfa = 'C' AND tipoutente <> 'A' AND ";

        $ute_attesa = $db->queryOne( $query_txt . "stato = 0" );
        $ute_attivi = $db->queryOne( $query_txt . "stato = 1" );
        $ute_disabi = $db->queryOne( $query_txt . "stato = 2" );

        $ute_admin  = $db->queryOne( $query_txt . "tipoutente = 'AS'" );
        $ute_ref    = $db->queryOne( $query_txt . "tipoutente = 'R'" );
        $ute_normal = $db->queryOne( $query_txt . "tipoutente = 'U'" );

        $testo .= "<p><strong>" . ($ute_attesa+$ute_attivi+$ute_disabi) . " utenti</strong> = ( " .
            ( $ute_attesa>0 ? $ute_attesa . " in attesa di abilitazione + " : "" ) . 
            $ute_attivi . " attivi + " . $ute_disabi . " disabilitati ) = ( " .
            $ute_admin . " amministratori + " . $ute_ref . " referenti + " . $ute_normal . " normali utenti )</p>";

        // --------------------------------------------- Ultimi utenti collegati        
        $this->build( "p4a_db_source", "ds_utenti" );
        $this->ds_utenti->setSelect( "descrizione, last_login, DATE_FORMAT( last_login, 'il %d/%m/%Y alle %H:%i' ) AS desc_last_login" );
        $this->ds_utenti->setTable( $p4a->e3g_prefix . "anagrafiche" );
        $this->ds_utenti->setWhere( "idanag <> " . $p4a->e3g_utente_idanag );
        $this->ds_utenti->addOrder( "last_login", "DESC" );
        $this->ds_utenti->load();
        $this->ds_utenti->firstRow();

        if ( $this->ds_utenti->getAll(0, 5) ) {
            $testo .= "<p><strong>Ultimi utenti</strong> che si sono collegati: ";
            for( $riga=1; $riga<=5; $riga++ ) {       
                $testo .= $this->ds_utenti->fields->descrizione->getnewValue() . 
                    " (" . $this->ds_utenti->fields->desc_last_login->getNewValue() . "), ";
                $this->ds_utenti->nextRow();
            }
            $testo = rtrim( $testo, ", " ) . ".</p>";
        }
            
        // -------------------------------------------- Info sull'ordine globale
        $ordine = $db->queryRow( 
            "SELECT COUNT( DISTINCT(c.codutente) )    AS n_utenti, " .
            "       COUNT( DISTINCT(c.codfornitore) ) AS n_fornitori, " .
            "       COUNT( DISTINCT(c.codarticolo) )  AS articoli_diversi, " .
            "       SUM( c.qta ) AS pezzi, " .
            "       ROUND( SUM(c.prezzoven * (c.qta+c.qta_agg)), $p4a->e3g_azienda_n_decimali_prezzi) AS importo " .
            "  FROM " . $p4a->e3g_prefix . "carrello c " );

        $pezzi = (integer) $ordine[ "pezzi" ];

        if ( $pezzi == 0 )
            $testo .= "<p>L'ordine globale e' vuoto.</p>";
        else 
            $testo .= 
                "<p><strong>Ordine globale</strong>: " . $ordine['n_utenti'] . " utent" . ( $ordine['n_utenti']==1 ? "e ha" : "i hanno" ) .
                " ordinato " . $ordine['articoli_diversi'] . " articoli diversi in quantita' di $pezzi pezzi " .
                "(da " . $ordine['n_fornitori'] . " fornitor" . ( $ordine['n_fornitori']==1 ? "e" : "i" ) .   
                ") per un importo totale di " . $ordine['importo'] . " euro.</p>";

        // --------------------------------------------- Credito/debito in cassa
        if ( $p4a->e3g_azienda_gestione_cassa ) {
            $saldo_finale = (double) $db->queryOne(
                "SELECT SUM( importo ) FROM _cassa " .
                " WHERE prefix = '" . $p4a->e3g_prefix . "' AND  validato = 1" );

            $n_da_validare = (double) $db->queryOne(
                "SELECT COUNT( * ) FROM _cassa " .
                " WHERE prefix = '" . $p4a->e3g_prefix . "' AND  validato = 0" );

            $testo .=
                "<p><strong>Cassa comune</strong>: e' presente un " . ( $saldo_finale>=0 ? "credito" : "debito" ) . " pari a <strong>$saldo_finale euro</strong>; " .
                ( $n_da_validare<>0 ? "ci sono $n_da_validare" : "non ci sono" ) . ( $n_da_validare==1 ? " movimento" : " movimenti" ) . 
                " in attesa di validazione.</p>";
        }
        
        // ----------------------------------------------------- Visualizzazione
        $this->lbl_info->setValue( $testo );
    }
    
}

?>