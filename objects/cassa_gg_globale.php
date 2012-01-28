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


class cassa_gg_globale extends P4A_Mask
{

	var $where_ds_spesa_utente;
	var $where_ds_spesa_articolo;
    var $filtro_ds_spesa_det = "0 = 0";
	var $where_ds_articoli;


    // -------------------------------------------------------------------------
	function cassa_gg_globale()
    // -------------------------------------------------------------------------
	{
		// ORDINE GLOBALE
		
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
        $this->setTitle('');

		// --------------- Sorgente dati LISTA DELLA SPESA (raggrup. per UTENTE)

		$this->build("p4a_db_source", "ds_spesa_utente");
		$this->ds_spesa_utente->setPageLimit( $p4a->e3g_utente_db_source_page_limit );

		$this->ds_spesa_utente->setSelect( "c.codutente, " .
			" ana.descrizione, " .
			" ana.descrizione AS desc_utente, " .
			" SUM( c.qta )             AS pezzi_in_ordine_orig, " .
			" SUM( c.qta_agg )         AS pezzi_in_ordine_agg, " .
			" SUM( c.qta + c.qta_agg ) AS pezzi_in_ordine_tot, " .
			" COUNT( DISTINCT(c.codarticolo) )  AS articoli_diversi, " .
			" COUNT( DISTINCT(c.codfornitore) ) AS fornitori_diversi, " .
			" ROUND(SUM(c.prezzoven * (c.qta+c.qta_agg)), $p4a->e3g_azienda_n_decimali_prezzi) AS importo " );
			
		$this->ds_spesa_utente->setTable( $p4a->e3g_prefix."carrello c, " . $p4a->e3g_prefix . "articoli art, " . $p4a->e3g_prefix . "anagrafiche ana" );
		$this->where_ds_spesa_utente = "c.codarticolo = art.codice AND c.codutente = ana.codice " .
			( $p4a->e3g_utente_tipo == 'R' ? "AND " . str_replace("#CAMPOCODICE#", "c.codfornitore", $p4a->e3g_where_referente) : "" );
		$this->ds_spesa_utente->setWhere( $this->where_ds_spesa_utente );
		$this->ds_spesa_utente->addGroup( "c.codutente" );
		$this->ds_spesa_utente->addOrder("ana.descrizione");
		$this->ds_spesa_utente->setPk("c.codutente"); 
		$this->ds_spesa_utente->load();
		$this->ds_spesa_utente->firstRow();


		// ------------ Sorgente dati LISTA DELLA SPESA (raggrup. per FORNITORE)

		$this->build("p4a_db_source", "ds_spesa_forn");
		$this->ds_spesa_forn->setPageLimit( $p4a->e3g_utente_db_source_page_limit );

		$this->ds_spesa_forn->setSelect( "c.codfornitore, " .
			" anag_f.descrizione, " .
			" anag_f.descrizione AS desc_fornitore, " .
			" SUM( c.qta )             AS pezzi_in_ordine_orig, " .
			" SUM( c.qta_agg )         AS pezzi_in_ordine_agg, " .
			" SUM( c.qta + c.qta_agg ) AS pezzi_in_ordine_tot, " .
			" COUNT( DISTINCT(c.codarticolo) ) AS articoli_diversi, " .
			" COUNT( DISTINCT(c.codutente) )   AS utenti_diversi, " .
			" ROUND(SUM(c.prezzoven * (c.qta+c.qta_agg)), $p4a->e3g_azienda_n_decimali_prezzi) AS importo " );
			
		$this->ds_spesa_forn->setTable( $p4a->e3g_prefix."carrello c, " . $p4a->e3g_prefix . "articoli art, " . 
            $p4a->e3g_prefix . "anagrafiche anag_f, " . $p4a->e3g_prefix . "anagrafiche anag_u" );
		$this->where_ds_spesa_forn = "c.codarticolo = art.codice AND c.codfornitore = anag_f.codice AND c.codutente = anag_u.codice " .
			( $p4a->e3g_utente_tipo == 'R' ? "AND " . str_replace("#CAMPOCODICE#", "c.codfornitore", $p4a->e3g_where_referente) : "" );
		$this->ds_spesa_forn->setWhere( $this->where_ds_spesa_forn );
		$this->ds_spesa_forn->addGroup( "c.codfornitore" );
		$this->ds_spesa_forn->addOrder("anag_f.descrizione");
		$this->ds_spesa_forn->setPk("c.codfornitore"); 
		
		
		$this->ds_spesa_forn->load();
		$this->ds_spesa_forn->firstRow();


		// ------------- Sorgente dati LISTA DELLA SPESA (raggrup. per ARTICOLO)
		
		$this->build("p4a_db_source", "ds_spesa_articolo");
		$this->ds_spesa_articolo->setPageLimit( $p4a->e3g_utente_db_source_page_limit );

		$this->ds_spesa_articolo->setSelect( "art.idarticolo, c.codfornitore, c.codarticolo, c.descrizione, " .
            " CONCAT_WS( ' ', art.um_qta, art.um ) AS um_qta_um, " .  // CONCAT_WS non è vuoto se manca l'UM
            " art.um, art.um_qta, art.gestione_a_peso, art.codiva, " . 
			" art.qtaminordine         AS pezzi_per_cartone, " .
			" SUM( c.qta )             AS pezzi_in_ordine_orig, " .
			" SUM( c.qta_agg )         AS pezzi_in_ordine_agg, " .
			" SUM( c.qta + c.qta_agg ) AS pezzi_in_ordine_tot, " .
			" CEILING( SUM(c.qta+c.qta_agg) / art.qtaminordine ) AS cartoni, " .
			" ( CEILING(SUM(c.qta+c.qta_agg) / art.qtaminordine) * art.qtaminordine ) - SUM(c.qta+c.qta_agg) AS surplus_pezzi, " .
			" ROUND( c.prezzoven, $p4a->e3g_azienda_n_decimali_prezzi ) AS prezzoven, " .
			" ROUND(SUM(c.prezzoven * (c.qta+c.qta_agg)), $p4a->e3g_azienda_n_decimali_prezzi) AS importo" );
            
        $this->ds_spesa_articolo->setSelect( str_replace( "c.",   $p4a->e3g_prefix."carrello.", $this->ds_spesa_articolo->getSelect() ) );            
        $this->ds_spesa_articolo->setSelect( str_replace( "art.", $p4a->e3g_prefix."articoli.", $this->ds_spesa_articolo->getSelect() ) );            

        $this->ds_spesa_articolo->setTable( $p4a->e3g_prefix."carrello" );
        $this->ds_spesa_articolo->addJoin( $p4a->e3g_prefix . "articoli", $p4a->e3g_prefix."carrello.codarticolo = " . $p4a->e3g_prefix . "articoli.codice " );        
        $this->ds_spesa_articolo->addJoin( $p4a->e3g_prefix . "anagrafiche", $p4a->e3g_prefix."carrello.codutente = " . $p4a->e3g_prefix . "anagrafiche.codice " );        

        $this->where_ds_spesa_articolo = " 0 = 0 " .
            ( $p4a->e3g_utente_tipo == 'R' ? " AND " . str_replace("#CAMPOCODICE#", $p4a->e3g_prefix."carrello.codfornitore", $p4a->e3g_where_referente) : "" );
            
		$this->ds_spesa_articolo->setWhere( $this->where_ds_spesa_articolo );
		$this->ds_spesa_articolo->addGroup( $p4a->e3g_prefix."carrello.codarticolo" );
		$this->ds_spesa_articolo->addOrder( $p4a->e3g_prefix."carrello.codfornitore" );
		$this->ds_spesa_articolo->addOrder( $p4a->e3g_prefix."carrello.descrizione" );
		$this->ds_spesa_articolo->setPk( "codarticolo" );
		$this->ds_spesa_articolo->load();
		$this->ds_spesa_articolo->firstRow();


		// -------------- Sorgente dati LISTA DELLA SPESA (dettaglio per utente)
		
		$this->build( "p4a_db_source", "ds_spesa_det" );
		$this->ds_spesa_det->setPageLimit( $p4a->e3g_utente_db_source_page_limit );

		$this->ds_spesa_det->setSelect( "c.idriga, c.codfornitore, c.data, c.codarticolo, c.descrizione, c.descrizione as articolo, " .
            " CONCAT_WS( ' ', art.um_qta, art.um ) AS um_qta_um, " .  // CONCAT_WS non è vuoto se manca l'UM
            " art.um_qta, art.gestione_a_peso, " . 
			" c.qta, c.qta_agg, " .
			" ( c.qta + c.qta_agg ) AS qta_tot, " .
			" c.qtaconsegnata, c.um, c.codutente, " .
			" ROUND(c.prezzoven, $p4a->e3g_azienda_n_decimali_prezzi) AS prezzoven, " .
			" ROUND(c.prezzoven * (c.qta+c.qta_agg), $p4a->e3g_azienda_n_decimali_prezzi) AS importo," .
			" ana.descrizione AS desc_utente, ana.email ");

        $this->ds_spesa_det->setTable( $p4a->e3g_prefix."carrello c" );
        $this->ds_spesa_det->addJoin( $p4a->e3g_prefix . "articoli art", "c.codarticolo = art.codice" );
        $this->ds_spesa_det->addJoin( $p4a->e3g_prefix . "anagrafiche ana", "c.codutente = ana.codice" );
        $this->ds_spesa_det->setWhere( "0 <> 0" );  // Impostato in tab_spesa_articolo_afterClick() e bu_filtra_click()

//		$this->ds_spesa_det->addOrder( "c.codfornitore" );
//		$this->ds_spesa_det->addOrder( "c.descrizione" );
        $this->ds_spesa_det->addOrder( "ana.descrizione" );
		$this->ds_spesa_det->setPk( "c.idriga" );
		$this->ds_spesa_det->load();
		$this->ds_spesa_det->firstRow();

		$this->setSource( $this->ds_spesa_det );

		// -------------------------------------- Sorgente dati LISTINO ARTICOLI	
			
		$this->build("p4a_db_source", "ds_articoli");
		$this->ds_articoli->setPageLimit( $p4a->e3g_utente_db_source_page_limit );
		
		$this->ds_articoli->setSelect( "idarticolo, centrale, barcode, codice, " .
            " descrizione, " .
            " CONCAT_WS( ' ', um_qta, um ) AS um_qta_um, " .  // CONCAT_WS non è vuoto se manca l'UM
            " um_qta, " . 
            " ROUND(prezzoven, $p4a->e3g_azienda_n_decimali_prezzi) AS prezzoven, " .
			" CONCAT( ROUND( prezzoven/um_qta, $p4a->e3g_azienda_n_decimali_prezzi), '/', um ) AS prezzo_ven_um, " .  // CONCAT è vuoto se manca l'UM 
			" prezzoacq, codiva, tipo, giacenza,  progetto, " .
			" catmerce, tipoarticolo, paese, contovendita, contoacquisto, " .
			" posizione, periodo, um, scortaminima, pzperconf, qtaminordine, qtaminperfamiglia, gestione_a_peso" );

		$this->ds_articoli->setTable( $p4a->e3g_prefix."articoli" );
		$this->where_ds_articoli = "stato = 1 AND " .
			( $p4a->e3g_utente_tipo == 'R' ? str_replace("#CAMPOCODICE#", "centrale", $p4a->e3g_where_referente) : "1=1" );
		$this->ds_articoli->setWhere( $this->where_ds_articoli );

		$this->ds_articoli->addOrder( "centrale" );
		$this->ds_articoli->addOrder( "descrizione" );
		$this->ds_articoli->setPk( "idarticolo" );
		$this->ds_articoli->load();
		$this->ds_articoli->firstRow();
		

		// ----------------------------------------------------- Altri DB source
		
		// Fornitori
		$this->build("p4a_db_source", "ds_forn");
		$this->ds_forn->setTable($p4a->e3g_prefix."anagrafiche");
		$this->ds_forn->setPk("codice");		
		$this->ds_forn->setWhere("tipocfa='F'");		
		$this->ds_forn->addOrder("codice");		
		$this->ds_forn->load();
		$this->ds_forn->firstRow();

		// Fornitore per ricerca (admin: tutti - ref: solo i propri)
/*        
        $this->build( "p4a_db_source", "ds_forn_ricerca" );
        $this->ds_forn_ricerca->setSelect( "codice, CONCAT_WS( ' / ', descrizione, desc_agg ) AS descrizione" );
        $this->ds_forn_ricerca->setTable( $p4a->e3g_prefix."anagrafiche" );
        $this->ds_forn_ricerca->setWhere( "( tipocfa = 'F' " .  
            ( $p4a->e3g_utente_tipo == 'R' ? " AND " . str_replace("#CAMPOCODICE#", "codice", $p4a->e3g_where_referente) : "" ).") OR idanag = 0 " );

        $this->ds_forn_ricerca->setPk( "codice" );      
        $this->ds_forn_ricerca->addOrder( "descrizione" );      
        $this->ds_forn_ricerca->load();     
*/

		$this->build( "p4a_db_source", "ds_forn_ricerca" );
        $this->ds_forn_ricerca->setSelect( "codice, CONCAT_WS( ' / ', descrizione, desc_agg ) AS descrizione" );
		$this->ds_forn_ricerca->setTable( $p4a->e3g_prefix . "anagrafiche" );

		$this->ds_forn_ricerca->setWhere( "( tipocfa = 'F' AND codice in ( " .
            "SELECT DISTINCT art.centrale FROM " . $p4a->e3g_prefix . "carrello AS c JOIN " . $p4a->e3g_prefix . "articoli AS art ON c.codarticolo = art.codice ) " .
			( $p4a->e3g_utente_tipo == 'R' ? " AND " . str_replace("#CAMPOCODICE#", "codice", $p4a->e3g_where_referente) : "" ).") OR idanag = 0 " );

		$this->ds_forn_ricerca->setPk( "a.codice" );		
		$this->ds_forn_ricerca->addOrder( "a.descrizione" );		
		$this->ds_forn_ricerca->load();		


		// Tipi articoli
		$this->build( "p4a_db_source", "ds_tipo" );
		$this->ds_tipo->setTable( $p4a->e3g_prefix . "tipiarticoli" );
		$this->ds_tipo->setPk( "codice" );		
		$this->ds_tipo->addOrder( "codice" );		
		$this->ds_tipo->load();
		$this->ds_tipo->firstRow();
		
		// Categorie
		$this->build( "p4a_db_source", "ds_cat" );
		$this->ds_cat->setTable( $p4a->e3g_prefix . "catmerceologica" );
		$this->ds_cat->setWhere( "tipo = '" . $this->ds_tipo->fields->codice->getNewValue() . "'" );		
		$this->ds_cat->setPk( "codice" );		
		$this->ds_cat->addOrder( "codice" );		
		$this->ds_cat->load();

		// Utenti per filtro
		$this->build("p4a_db_source", "ds_utenti_filtro");
		$this->ds_utenti_filtro->setTable( $p4a->e3g_prefix . "anagrafiche" );
		$this->ds_utenti_filtro->setWhere( "( tipocfa = 'C' AND tipoutente <> 'A' AND stato = 1 )  OR idanag = 0");		
		$this->ds_utenti_filtro->setPk("codice");		
		$this->ds_utenti_filtro->addOrder("descrizione");		
		$this->ds_utenti_filtro->load();
        
        // Luoghi consegna per filtro
        $this->build( "p4a_db_source", "ds_luoghi_cons" );
        $this->ds_luoghi_cons->setTable( "_luoghi_cons" );
        $this->ds_luoghi_cons->setWhere( "prefix = '" . $p4a->e3g_prefix . "' OR id_luogo_cons = 0 " );
        $this->ds_luoghi_cons->setPk( "id_luogo_cons" );
        $this->ds_luoghi_cons->load();


		// --------------------------- Message per eventuali warning relativi...
		
		// ...al singolo prodotto che si sta aggiungendo/modificando (vengono visualizzati una sola volta)
		$this->build("p4a_message", "msg_warning");
		$this->msg_warning->setWidth( 700 );

		// ...ordine chiuso, importo minimo non raggiunto (rimangono visualizzati sempre)
		$this->build("p4a_message", "msg_info");
		$this->msg_info->setWidth( 700 );
		$this->msg_info->setIcon( "info" );
		$this->msg_info->autoClear( false );

		// Label per segnalare entità dell'ordine (qtà prodotti e importo totale)		
		$this->build("p4a_label", "lbl_situazione");
		$this->lbl_situazione->setWidth( 700 );


		// --------------------------------------------------- Chiusura finestra	
			
		$this->build("p4a_button", "bu_chiudi");
		$this->bu_chiudi->setLabel("Chiudi finestra");
		$this->bu_chiudi->addAction("onClick");
		$this->intercept($this->bu_chiudi, "onClick", "bu_chiudi_click");
		$this->bu_chiudi->setWidth(150);
		$this->bu_chiudi->setIcon ( "exit" );
		

				
		// --------------------------------------------- Pannello filtro globale

		$fld_filtro_utente = & $this->build("p4a_field", "fld_filtro_utente");
		$fld_filtro_utente->setLabel('Utente');
		$fld_filtro_utente->label->setWidth(60);
		$fld_filtro_utente->setType("select");
		$fld_filtro_utente->setSource($this->ds_utenti_filtro);
		$fld_filtro_utente->setSourceValueField("codice");
		$fld_filtro_utente->setSourceDescriptionField("descrizione");
		$fld_filtro_utente->setWidth( 250 );
		$fld_filtro_utente->setNewValue("00");

        $this->build( "p4a_field", "fld_filtro_luogo_cons" );
        $this->fld_filtro_luogo_cons->setLabel( "Luogo di consegna" );
        $this->fld_filtro_luogo_cons->label->setWidth( 150 );
        $this->fld_filtro_luogo_cons->setWidth( 250 );
        $this->fld_filtro_luogo_cons->setType( "select" );
        $this->fld_filtro_luogo_cons->setSource( $this->ds_luoghi_cons );
        $this->fld_filtro_luogo_cons->setSourceValueField( "id_luogo_cons" );
        $this->fld_filtro_luogo_cons->setSourceDescriptionField( "descrizione" );

		$fld_filtro_forn = & $this->build( "p4a_field", "fld_filtro_forn" );
		$fld_filtro_forn->setLabel( 'Fornitore' );
		$fld_filtro_forn->label->setWidth( 60 );
		$fld_filtro_forn->setType ( "select" );
		$fld_filtro_forn->setSource( $this->ds_forn_ricerca );
		$fld_filtro_forn->setSourceValueField( "codice" );
		$fld_filtro_forn->setSourceDescriptionField( "descrizione" );
		$fld_filtro_forn->setWidth( 250 );
		$fld_filtro_forn->setNewValue( "00" );

		$fld_filtro_cat = & $this->build("p4a_field", "fld_filtro_cat");
		$fld_filtro_cat->setLabel('Categoria');
		$fld_filtro_cat->label->setWidth( 60 );
		$fld_filtro_cat->setType("select");
		$fld_filtro_cat->setSource($this->ds_tipo);
		$fld_filtro_cat->setSourceValueField("codice");
		$fld_filtro_cat->setSourceDescriptionField("descrizione");
		$fld_filtro_cat->addAction("OnChange");
		$fld_filtro_cat->setWidth( 250 );
		$this->intercept($this->fld_filtro_cat, "onChange","fld_filtro_cat_change");		
		
		$fld_filtro_sottocat=& $this->build("p4a_field", "fld_filtro_sottocat");
		$fld_filtro_sottocat->setLabel('Sottocategoria');
		$fld_filtro_sottocat->label->setWidth( 80 );
		$fld_filtro_sottocat->setType("select");
		$fld_filtro_sottocat->setSource($this->ds_cat);
		$fld_filtro_sottocat->setSourceValueField("codice");
		$fld_filtro_sottocat->setSourceDescriptionField("descrizione");
		$fld_filtro_sottocat->setWidth( 180 );
		
		// Bottone FILTRA
		$this->build("p4a_button", "bu_filtra");
		$this->bu_filtra->setLabel("Applica filtro");
		$this->bu_filtra->addAction("onClick");
		$this->bu_filtra->setIcon("find");
		$this->bu_filtra->setSize( 16 );
        $this->bu_filtra->setWidth( 120 );
		$this->intercept($this->bu_filtra, "onClick", "bu_filtra_click");

		// Bottone ANNULLA (vedi tutto)
		$this->build( "p4a_button", "bu_mostra_tutto" );
		$this->bu_mostra_tutto->setLabel( "Mostra tutto" );
		$this->bu_mostra_tutto->addAction( "onClick" );
		$this->bu_mostra_tutto->setIcon( "cancel" );
		$this->bu_mostra_tutto->setSize( 16 );
        $this->bu_mostra_tutto->setWidth( 120 );
		$this->intercept($this->bu_mostra_tutto, "onClick", "bu_mostra_tutto_click");

		// Label per segnalare entità dell'ordine del FORNITORE selezionato		
		$this->build("p4a_label", "lbl_situazione_forn");
		$this->lbl_situazione_forn->setWidth( 700 );
		
		// Label per segnalare entità dell'ordine dell'UTENTE selezionato		
		$this->build("p4a_label", "lbl_situazione_utente");
		$this->lbl_situazione_utente->setWidth( 700 );
		

		// ------------------- Tabella "LISTA DELLA SPESA" (raggrup. per UTENTE)

		$tab_spesa_utente =& $this->build("p4a_table", "tab_spesa_utente");
		$tab_spesa_utente->setWidth( E3G_TABLE_IN_TAB_PANE_WIDTH );
		$tab_spesa_utente->setSource($this->ds_spesa_utente);	
		$tab_spesa_utente->setVisibleCols( array(
			"desc_utente", "pezzi_in_ordine_orig","pezzi_in_ordine_agg", "pezzi_in_ordine_tot",  
			"articoli_diversi", "fornitori_diversi", "importo") );
		$tab_spesa_utente->showNavigationBar();

		$this->tab_spesa_utente->data->fields->importo->setType("float");

        $this->tab_spesa_utente->cols->desc_utente->setOrderable( false );
        $this->tab_spesa_utente->cols->pezzi_in_ordine_orig->setOrderable( false );
        $this->tab_spesa_utente->cols->pezzi_in_ordine_agg->setOrderable( false );
        $this->tab_spesa_utente->cols->pezzi_in_ordine_tot->setOrderable( false );
        $this->tab_spesa_utente->cols->articoli_diversi->setOrderable( false );
        $this->tab_spesa_utente->cols->fornitori_diversi->setOrderable( false );
        $this->tab_spesa_utente->cols->importo->setOrderable( false );

		$this->tab_spesa_utente->cols->desc_utente->setLabel('Utente');
		$this->tab_spesa_utente->cols->pezzi_in_ordine_orig->setLabel("Pezzi in ordine (orig.)");
		$this->tab_spesa_utente->cols->pezzi_in_ordine_agg->setLabel("Pezzi aggiunti");
		$this->tab_spesa_utente->cols->pezzi_in_ordine_tot->setLabel("Pezzi in ordine (TOT)");
		$this->tab_spesa_utente->cols->articoli_diversi->setLabel("N. articoli diversi");
		$this->tab_spesa_utente->cols->fornitori_diversi->setLabel("N. fornitori diversi");
		$this->tab_spesa_utente->cols->importo->setLabel("Importo ordine");

		// Larghezze colonne
//		$this->tab_spesa_utente->cols->desc_utente->setWidth(160);  per differenza
		$this->tab_spesa_utente->cols->pezzi_in_ordine_orig->setWidth(50);
		$this->tab_spesa_utente->cols->pezzi_in_ordine_agg->setWidth(50);
		$this->tab_spesa_utente->cols->pezzi_in_ordine_tot->setWidth(50);
		$this->tab_spesa_utente->cols->articoli_diversi->setWidth(80);
		$this->tab_spesa_utente->cols->fornitori_diversi->setWidth(80);
		$this->tab_spesa_utente->cols->importo->setWidth(50);
		
				
		// ---------------- Tabella "LISTA DELLA SPESA" (raggrup. per FORNITORE)

		$tab_spesa_forn =& $this->build("p4a_table", "tab_spesa_forn");
		$tab_spesa_forn->setWidth( E3G_TABLE_IN_TAB_PANE_WIDTH );
		$tab_spesa_forn->setSource($this->ds_spesa_forn);	
		$tab_spesa_forn->setVisibleCols( array(
			"desc_fornitore", "pezzi_in_ordine_orig","pezzi_in_ordine_agg", "pezzi_in_ordine_tot",  
			"articoli_diversi", "utenti_diversi", "importo") );
			
                $this->intercept( $this->tab_spesa_forn->rows, "afterClick", "tab_spesa_forn_afterClick" );
		$tab_spesa_forn->showNavigationBar();

        $this->tab_spesa_forn->cols->desc_fornitore->setOrderable( false );
        $this->tab_spesa_forn->cols->pezzi_in_ordine_orig->setOrderable( false );
        $this->tab_spesa_forn->cols->pezzi_in_ordine_agg->setOrderable( false );
        $this->tab_spesa_forn->cols->pezzi_in_ordine_tot->setOrderable( false );
        $this->tab_spesa_forn->cols->articoli_diversi->setOrderable( false );
        $this->tab_spesa_forn->cols->utenti_diversi->setOrderable( false );
        $this->tab_spesa_forn->cols->importo->setOrderable( false );

		$this->tab_spesa_forn->cols->importo->setType("decimal");
		
		$this->tab_spesa_forn->cols->desc_fornitore->setLabel('Fornitore');
		$this->tab_spesa_forn->cols->pezzi_in_ordine_orig->setLabel("Pezzi in ordine (orig.)");
		$this->tab_spesa_forn->cols->pezzi_in_ordine_agg->setLabel("Pezzi aggiunti");
		$this->tab_spesa_forn->cols->pezzi_in_ordine_tot->setLabel("Pezzi in ordine (TOT)");
		$this->tab_spesa_forn->cols->articoli_diversi->setLabel("N. articoli diversi");
		$this->tab_spesa_forn->cols->utenti_diversi->setLabel("N. utenti diversi");
		$this->tab_spesa_forn->cols->importo->setLabel("Importo ordine");

		// Larghezze colonne
//		$this->tab_spesa_forn->cols->desc_fornitore->setWidth(160);  per differenza
		$this->tab_spesa_forn->cols->pezzi_in_ordine_orig->setWidth(50);
		$this->tab_spesa_forn->cols->pezzi_in_ordine_agg->setWidth(50);
		$this->tab_spesa_forn->cols->pezzi_in_ordine_tot->setWidth(50);
		$this->tab_spesa_forn->cols->articoli_diversi->setWidth(80);
		$this->tab_spesa_forn->cols->utenti_diversi->setWidth(80);
		$this->tab_spesa_forn->cols->importo->setWidth(50);
		
				
		// ----------------- Tabella "LISTA DELLA SPESA" (raggrup. per ARTICOLO)

		$this->build( "p4a_table", "tab_spesa_articolo" );
		$this->tab_spesa_articolo->setWidth( E3G_TABLE_IN_TAB_PANE_WIDTH );
		$this->tab_spesa_articolo->setSource( $this->ds_spesa_articolo );	
		$this->tab_spesa_articolo->setVisibleCols( array( "idarticolo", "codarticolo",
			"codfornitore", "descrizione", "gestione_a_peso", "um_qta_um", "um_qta", "pezzi_per_cartone", 
			"pezzi_in_ordine_orig", "pezzi_in_ordine_agg", "pezzi_in_ordine_tot", "cartoni", "surplus_pezzi", "prezzoven","importo") );
        $this->intercept( $this->tab_spesa_articolo->rows, "afterClick", "tab_spesa_articolo_afterClick" );  
        $this->intercept( $this->tab_spesa_articolo->rows, "beforeDisplay", "tab_spesa_articolo_beforeDisplay" );  
		$this->tab_spesa_articolo->showNavigationBar();

        $this->tab_spesa_articolo->cols->idarticolo->setVisible( false );
        $this->tab_spesa_articolo->cols->codarticolo->setVisible( false );
        $this->tab_spesa_articolo->cols->gestione_a_peso->setVisible( false );
        $this->tab_spesa_articolo->cols->um_qta->setVisible( false );

        $this->tab_spesa_articolo->cols->um_qta_um->setOrderable( false );
        $this->tab_spesa_articolo->cols->pezzi_per_cartone->setOrderable( false );
        $this->tab_spesa_articolo->cols->pezzi_in_ordine_orig->setOrderable( false );
        $this->tab_spesa_articolo->cols->pezzi_in_ordine_agg->setOrderable( false );
        $this->tab_spesa_articolo->cols->pezzi_in_ordine_tot->setOrderable( false );
        $this->tab_spesa_articolo->cols->cartoni->setOrderable( false );
        $this->tab_spesa_articolo->cols->surplus_pezzi->setOrderable( false );
        $this->tab_spesa_articolo->cols->prezzoven->setOrderable( false );
        $this->tab_spesa_articolo->cols->importo->setOrderable( false );

		$this->tab_spesa_articolo->data->fields->prezzoven->setType("float");
		$this->tab_spesa_articolo->data->fields->importo->setType("float");

		$this->tab_spesa_articolo->cols->codfornitore->setLabel('Fornitore');
		$this->tab_spesa_articolo->cols->descrizione->setLabel("Articolo");
        $this->tab_spesa_articolo->cols->um_qta_um->setLabel( "Conf." );
		$this->tab_spesa_articolo->cols->pezzi_per_cartone->setLabel("Pezzi per cartone");
		$this->tab_spesa_articolo->cols->pezzi_in_ordine_orig->setLabel("Pezzi in ordine (orig.)");
		$this->tab_spesa_articolo->cols->pezzi_in_ordine_agg->setLabel("Pezzi aggiunti");
		$this->tab_spesa_articolo->cols->pezzi_in_ordine_tot->setLabel("Pezzi in ordine (TOT)");
		$this->tab_spesa_articolo->cols->cartoni->setLabel("N. cartoni");
		$this->tab_spesa_articolo->cols->surplus_pezzi->setLabel("Surplus [pezzi]");
		$this->tab_spesa_articolo->cols->prezzoven->setLabel("Prezzo");
		$this->tab_spesa_articolo->cols->importo->setLabel("Importo");

		$this->tab_spesa_articolo->cols->codfornitore->setSource($this->ds_forn);
		$this->tab_spesa_articolo->cols->codfornitore->setSourceValueField("codice");
		$this->tab_spesa_articolo->cols->codfornitore->setSourceDescriptionField("descrizione");

		// Larghezze colonne
		$this->tab_spesa_articolo->cols->codfornitore->setWidth(120);
//		$this->tab_spesa_articolo->cols->descrizione->setWidth(160);  per differenza
        $this->tab_spesa_articolo->cols->um_qta_um->setWidth( 50 );
		$this->tab_spesa_articolo->cols->pezzi_per_cartone->setWidth(50);
		$this->tab_spesa_articolo->cols->pezzi_in_ordine_orig->setWidth(50);
		$this->tab_spesa_articolo->cols->pezzi_in_ordine_agg->setWidth(50);
		$this->tab_spesa_articolo->cols->pezzi_in_ordine_tot->setWidth(50);
		$this->tab_spesa_articolo->cols->cartoni->setWidth(50);
		$this->tab_spesa_articolo->cols->surplus_pezzi->setWidth(50);
		$this->tab_spesa_articolo->cols->prezzoven->setWidth(50);
		$this->tab_spesa_articolo->cols->importo->setWidth(50);
		
				
		// ------------------ Tabella "LISTA DELLA SPESA" (dettaglio per utente)
		
		$this->build( "p4a_table", "tab_spesa_det" );
		$this->tab_spesa_det->setWidth( E3G_NARROW_TABLE_IN_TAB_PANE_WIDTH );
		$this->tab_spesa_det->setSource($this->ds_spesa_det);	
		$this->tab_spesa_det->setVisibleCols( array( 
            "idriga", "desc_utente", "articolo", "gestione_a_peso", "qta", "qta_agg", "qta_tot", "prezzoven", "importo") );
		$this->intercept( $this->tab_spesa_det->rows, "afterClick", "tab_spesa_det_afterClick" );
        $this->intercept( $this->tab_spesa_det->rows, "beforeDisplay", "tab_spesa_det_beforeDisplay" );  
		$this->tab_spesa_det->showNavigationBar();

        $this->tab_spesa_det->data->fields->qta_agg->setType( "float" );
        $this->tab_spesa_det->data->fields->prezzoven->setType( "float" );
        $this->tab_spesa_det->data->fields->importo->setType( "float" );

        $this->tab_spesa_det->cols->idriga->setVisible( false );
        $this->tab_spesa_det->cols->gestione_a_peso->setVisible( false );

        $this->tab_spesa_det->cols->desc_utente->setOrderable( false );
        $this->tab_spesa_det->cols->qta_tot->setOrderable( false );
        $this->tab_spesa_det->cols->prezzoven->setOrderable( false );
        $this->tab_spesa_det->cols->importo->setOrderable( false );

        $this->tab_spesa_det->cols->desc_utente->setLabel ("Utente" );
        //$this->tab_spesa_det->cols->email->setLabel ("e-mail" );
        $this->tab_spesa_det->cols->articolo->setLabel ("Articolo");
		$this->tab_spesa_det->cols->qta->setLabel( "Pezzi in ordine (orig.)" );
		$this->tab_spesa_det->cols->qta_agg->setLabel( "Pezzi aggiunti" );
		$this->tab_spesa_det->cols->qta_tot->setLabel( "Pezzi in ordine (TOT)" );
        $this->tab_spesa_det->cols->prezzoven->setLabel( "Prezzo" );
        $this->tab_spesa_det->cols->importo->setLabel( "Importo" );

		// Larghezze colonne
//      $this->tab_spesa_det->cols->desc_utente->setWidth(); per differenza
        $this->tab_spesa_det->cols->articolo->setWidth( 200 );
        $this->tab_spesa_det->cols->qta->setWidth( 50 );
		$this->tab_spesa_det->cols->qta_agg->setWidth( 50 );
		$this->tab_spesa_det->cols->qta_tot->setWidth( 50 );
        $this->tab_spesa_det->cols->prezzoven->setWidth( 50 );
        $this->tab_spesa_det->cols->importo->setWidth( 50 );
		
				
		// --------------- Pannello "Spesa per ARTICOLO": oggetti colonna destra

        // Campo nominativo utente, es. "Ordine Mario Rossi"       
        $this->build("p4a_label", "lbl_spesa_desc_utente");
        $this->lbl_spesa_desc_utente->setWidth( E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->lbl_spesa_desc_utente->setFontWeight( "bold" );
        $this->lbl_spesa_desc_utente->setFontColor( "black" );

        // Campo QUANTITA'
        $this->build("p4a_field", "fld_spesa_qta");
        $this->fld_spesa_qta->setLabel("Quantita'");
        $this->fld_spesa_qta->label->setWidth( E3G_LABEL_IN_TAB_PANE_WIDTH );
        $this->fld_spesa_qta->setWidth( E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->fld_spesa_qta->setFontColor( "black" );
        $this->fld_spesa_qta->setStyleProperty( "border", "1px solid black" );
        $this->fld_spesa_qta->label->setFontColor( "black" );
        
        // Bottone AGGIORNA Q.TA'
        $this->build( "p4a_button", "bu_spesa_aggiorna_qta" );
        $this->bu_spesa_aggiorna_qta->setLabel( "Aggiorna quantita'" );
        $this->bu_spesa_aggiorna_qta->setIcon( "reload" );
        $this->bu_spesa_aggiorna_qta->setWidth( 20 + E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->bu_spesa_aggiorna_qta->setSize( 16 );
        $this->bu_spesa_aggiorna_qta->addAction("onClick");
        $this->intercept($this->bu_spesa_aggiorna_qta, "onClick", "bu_spesa_aggiorna_qta_click");


		// Bottone ESPORTA ORDINE PDF
		$this->build("p4a_button", "bu_esporta_ordine");
		$this->bu_esporta_ordine->setLabel( "Esporta come PDF" );
        $this->bu_esporta_ordine->setIcon( "pdf" );
        $this->bu_esporta_ordine->setWidth( 20 + E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->bu_esporta_ordine->setSize( 16 );
		$this->bu_esporta_ordine->addAction( "onClick" );
		$this->intercept($this->bu_esporta_ordine, "onClick", "bu_esporta_ordineClick");

        // Bottone ESPORTA ORDINE CSV
        $this->build( "p4a_button", "bu_esporta_ordineCsv" );
        $this->bu_esporta_ordineCsv->setLabel( "Esporta come CSV" );
        $this->bu_esporta_ordineCsv->setIcon( "spreadsheet" );
        $this->bu_esporta_ordineCsv->setWidth( 20 + E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->bu_esporta_ordineCsv->setSize( 16 );
        $this->bu_esporta_ordineCsv->addAction( "onClick" );
        $this->intercept( $this->bu_esporta_ordineCsv, "onClick", "bu_esporta_ordineCsvClick" );


        // ------------------------ Pannello "Spesa per ARTICOLO": oggetti sotto
        
        // Combo utenti non ordinanti (che già non hanno ordinato una qtà dell'articolo selezionato)
        $this->build( "p4a_db_source", "ds_utenti_assegna_qta" );
        $this->ds_utenti_assegna_qta->setTable( $p4a->e3g_prefix . "anagrafiche AS anag" );
        $this->ds_utenti_assegna_qta->setWhere( "0 <> 0" ); // Assegnato in tab_spesa_articolo_afterClick()
        $this->ds_utenti_assegna_qta->setPk( "codice" );       
        $this->ds_utenti_assegna_qta->addOrder( "descrizione" );
        $this->ds_utenti_assegna_qta->load();       
        
        $this->build( "p4a_field", "fld_utenti_assegna_qta" );
        $this->fld_utenti_assegna_qta->setLabel( "Nuovo ordinante" );
        $this->fld_utenti_assegna_qta->label->setWidth( 80 );
        $this->fld_utenti_assegna_qta->setWidth( 150 );
        $this->fld_utenti_assegna_qta->setType( "select" );
        $this->fld_utenti_assegna_qta->setSource( $this->ds_utenti_assegna_qta );
        $this->fld_utenti_assegna_qta->setSourceValueField( "codice" );
        $this->fld_utenti_assegna_qta->setSourceDescriptionField( "descrizione" );
//      $this->fld_utenti_assegna_qta->setNewValue( "00" ); COSI NON VA BENE
        
        // Campo quantità da assegnare
        $this->build( "p4a_field", "fld_assegna_qta" );
        $this->fld_assegna_qta->setLabel( "Quantita'" );
        $this->fld_assegna_qta->label->setWidth( 60 );
        $this->fld_assegna_qta->setWidth( 50 );
        $this->fld_assegna_qta->setNewValue( 0 );
        
        // Bottone assegna quantità
        $this->build( "p4a_button", "bu_assegna_qta" );
        $this->bu_assegna_qta->setLabel( "Assegna quantita'" );
        $this->bu_assegna_qta->setIcon( "execute" );
        $this->bu_assegna_qta->setWidth( 20 + E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->bu_assegna_qta->setSize( 16 );
        $this->bu_assegna_qta->addAction( "onClick" );
        $this->intercept( $this->bu_assegna_qta, "onClick", "bu_assegna_qta_click" );
        
        
        
        
        
        

		
		// -------------------------------------------- Tabella LISTINO ARTICOLI
		
		$this->build("p4a_table", "tab_listino");
 		$this->tab_listino->setWidth( E3G_NARROW_TABLE_IN_TAB_PANE_WIDTH );
		$this->tab_listino->setSource($this->ds_articoli);
		$this->tab_listino->setVisibleCols(array("idarticolo", 
            "centrale", "descrizione", "gestione_a_peso", "um_qta_um", "um_qta", "prezzoven", "prezzo_ven_um", "qtaminperfamiglia"));
		$this->tab_listino->showNavigationBar();
		$this->intercept( $this->tab_listino->rows, "afterClick", "tab_listino_afterClick" );
        $this->intercept( $this->tab_listino->rows, "beforeDisplay", "tab_listino_beforeDisplay" );  
		
		$this->tab_listino->data->fields->prezzoven->setType("float");

//		$this->tab_listino->data->fields->prezzo_ven_um->setAlign('right');
//		$this->tab_listino->data->fields->prezzo_ven_um->setType("float");
//		$this->tab_listino->data->fields->prezzo_ven_um->setStyleProperty('text-align', 'right');

        $this->tab_listino->cols->idarticolo->setVisible( false );
        $this->tab_listino->cols->gestione_a_peso->setVisible( false );
        $this->tab_listino->cols->um_qta->setVisible( false );

        $this->tab_listino->cols->um_qta_um->setOrderable( false );
		$this->tab_listino->cols->prezzo_ven_um->setOrderable(false);

		$this->tab_listino->cols->centrale->setLabel("Fornitore");
		$this->tab_listino->cols->descrizione->setLabel("Articolo");
        $this->tab_listino->cols->um_qta_um->setLabel( "Conf." );
		$this->tab_listino->cols->prezzo_ven_um->setLabel("Prezzo/UM");
		$this->tab_listino->cols->prezzoven->setLabel("Prezzo");
		$this->tab_listino->cols->qtaminperfamiglia->setLabel("Min.");

		$this->tab_listino->cols->centrale->setWidth( 160 );
//		$this->tab_listino->cols->descrizione->  per differenza
        $this->tab_listino->cols->um_qta_um->setWidth( 50 );
		$this->tab_listino->cols->prezzo_ven_um->setWidth( 75 );
		$this->tab_listino->cols->prezzoven->setWidth( 60 );
		$this->tab_listino->cols->qtaminperfamiglia->setWidth(40);			

		$this->tab_listino->cols->centrale->setSource($this->ds_forn);
		$this->tab_listino->cols->centrale->setSourceValueField("codice");
		$this->tab_listino->cols->centrale->setSourceDescriptionField("descrizione");
		

		// ------------------------- Oggetti sul lato destro di LISTINO ARTICOLI		
		
		$this->build("p4a_label", "lbl_listino_desc_articolo");
		$this->lbl_listino_desc_articolo->setWidth(E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH);
        $this->lbl_listino_desc_articolo->setFontColor( "black" );
		
		$flg_aggiungi_qta =& $this->build("p4a_field", "flg_aggiungi_qta");
		$flg_aggiungi_qta->setLabel("Quantita'");
		$flg_aggiungi_qta->label->setWidth( E3G_LABEL_IN_TAB_PANE_WIDTH );
		$flg_aggiungi_qta->setWidth( E3G_FIELD_IN_TAB_PANE_WIDTH );
        $flg_aggiungi_qta->setFontColor( "black" );
        $flg_aggiungi_qta->label->setFontColor( "black" );

		$this->build("p4a_label", "lbl_listino_desc_utente");
		$this->lbl_listino_desc_utente->setWidth(E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH);
        $this->lbl_listino_desc_utente->setFontColor( "black" );

		$this->build( "p4a_button", "bu_aggiungi_qta" );
		$this->bu_aggiungi_qta->setLabel( "Aggiungi quantita'" );
		$this->bu_aggiungi_qta->setIcon( "edit_add" );
        $this->bu_aggiungi_qta->setWidth( 20 + E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->bu_aggiungi_qta->setSize( 16 );
		$this->bu_aggiungi_qta->addAction( "onClick" );
		$this->intercept( $this->bu_aggiungi_qta, "onClick", "bu_aggiungi_qta_click" );
		
		$this->build( "p4a_button", "bu_scheda_articolo" );
		$this->bu_scheda_articolo->setLabel( "Scheda prodotto" );
		$this->bu_scheda_articolo->setIcon ( "info" );
        $this->bu_scheda_articolo->setWidth( 20 + E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->bu_scheda_articolo->setSize( 16 );
		$this->bu_scheda_articolo->dropAction( "onClick" ); 
		$nome_file_scheda = "cache/scheda_articolo_" . $p4a->e3g_prefix . md5($p4a->e3g_utente_codice) . ".html";
		$this->bu_scheda_articolo->setProperty("onclick", 
			"myRef = window.open('$nome_file_scheda', 'method_desc', 'status=yes,width=450,height=500,resizable=0');myRef.focus()"); 
			//window.open(''+self.location,'mywin','left=20,top=20,width=500,height=550,toolbar=1,resizable=0')
		
		
		// -------------------------------------------------- ANCORAGGIO OGGETTI
		
		// Pannello filtro globale
		$fs_ricerca_articoli =& $this->build("p4a_fieldset", "fs_ricerca_articoli");
		$fs_ricerca_articoli->setTitle( "Filtro" );
		$fs_ricerca_articoli->setWidth( E3G_FIELDSET_SEARCH_WIDTH );
		$fs_ricerca_articoli->anchor( $this->fld_filtro_utente );
        if ( $p4a->e3g_azienda_gestione_luoghi_cons )
            $fs_ricerca_articoli->anchorLeft( $this->fld_filtro_luogo_cons );
		$fs_ricerca_articoli->anchor( $this->fld_filtro_forn );
		$fs_ricerca_articoli->anchor( $this->fld_filtro_cat );
		$fs_ricerca_articoli->anchorLeft( $this->fld_filtro_sottocat );
        $fs_ricerca_articoli->anchorRight( $this->bu_mostra_tutto );
		$fs_ricerca_articoli->anchorRight( $this->bu_filtra );
		$fs_ricerca_articoli->anchor( $this->lbl_situazione_utente );
		$fs_ricerca_articoli->anchor( $this->lbl_situazione_forn );

		// LISTA DELLA SPESA: spalla 
        $this->build( "p4a_sheet", "sh_spalla_lista_spesa" );
		$this->sh_spalla_lista_spesa->defineGrid( 6, 1 );
//      $this->sh_spalla_lista_spesa->setWidth(100);
        $this->sh_spalla_lista_spesa->anchor( $this->lbl_spesa_desc_utente, 1, 1 );   
		$this->sh_spalla_lista_spesa->anchor( $this->fld_spesa_qta,         2, 1 );
		$this->sh_spalla_lista_spesa->anchor( $this->bu_spesa_aggiorna_qta, 3, 1 );
		$this->sh_spalla_lista_spesa->anchorText("<br>", 4,1);
        $this->sh_spalla_lista_spesa->anchor( $this->bu_esporta_ordine,     5, 1 );
        $this->sh_spalla_lista_spesa->anchor( $this->bu_esporta_ordineCsv,  6, 1 );

		// LISTA DELLA SPESA 
		$this->build( "p4a_sheet", "sh_lista_spesa" );
        $this->sh_lista_spesa->defineGrid( 1, 2 );
		$this->sh_lista_spesa->anchor( $this->tab_spesa_det, 1,1 );
		$this->sh_lista_spesa->anchor( $this->sh_spalla_lista_spesa, 1,2 );

		// LISTINO ARTICOLI: spalla
        $this->build("p4a_sheet", "sh_spalla_listino");
		$this->sh_spalla_listino->defineGrid(6, 1);
//		$this->sh_spalla_listino->setWidth(100);
		$this->sh_spalla_listino->anchor( $this->lbl_listino_desc_articolo, 1,1);
		$this->sh_spalla_listino->anchor( $this->flg_aggiungi_qta, 2,1);		
		$this->sh_spalla_listino->anchor( $this->lbl_listino_desc_utente, 3,1);		
		$this->sh_spalla_listino->anchor( $this->bu_aggiungi_qta, 4,1);
		$this->sh_spalla_listino->anchorText("<br>", 5,1);
		$this->sh_spalla_listino->anchor( $this->bu_scheda_articolo, 6,1);

		// LISTINO ARTICOLI
		$this->build("p4a_sheet", "sh_listino");
		$this->sh_listino->defineGrid(1, 2);
		$this->sh_listino->anchor( $this->tab_listino, 1,1);
		$this->sh_listino->anchor( $this->sh_spalla_listino, 1,2);


		// ------------------------------------------------- Pannello principale
		
		$this->build("p4a_tab_pane", "tab_pane");		 
		$this->tab_pane->pages->build("p4a_frame", "tabframe1");
		$this->tab_pane->pages->build("p4a_frame", "tabframe2");
		$this->tab_pane->pages->build("p4a_frame", "tabframe3");
		$this->tab_pane->pages->build("p4a_frame", "tabframe4");

//		$this->tab_pane->setWidth(800);  Viene determinato automaticamente

		$this->tab_pane->pages->tabframe1->setLabel("Spesa per UTENTE");
		$this->tab_pane->pages->tabframe2->setLabel("Spesa per FORNITORE");
		$this->tab_pane->pages->tabframe3->setLabel("Spesa per ARTICOLO");
		$this->tab_pane->pages->tabframe4->setLabel("Listino Articoli");

		$this->tab_pane->pages->tabframe1->anchor( $this->tab_spesa_utente);		 
		$this->tab_pane->pages->tabframe2->anchor( $this->tab_spesa_forn);		 
		$this->tab_pane->pages->tabframe3->anchor( $this->tab_spesa_articolo );		 
        $this->tab_pane->pages->tabframe3->anchor( $this->sh_lista_spesa );      
        $this->tab_pane->pages->tabframe3->anchor( $this->fld_utenti_assegna_qta );      
        $this->tab_pane->pages->tabframe3->anchorLeft( $this->fld_assegna_qta );      
        $this->tab_pane->pages->tabframe3->anchorLeft( $this->bu_assegna_qta );      

		$this->tab_pane->pages->tabframe4->anchor( $this->sh_listino );		 

		
		// ---------------------------------------------------- Frame principale
		
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth( E3G_MAIN_FRAME_WIDTH );  
		$frm->anchor( $this->msg_info );
		$frm->anchor( $this->lbl_situazione );
		$frm->anchorRight( $this->bu_chiudi );
		$frm->anchor( $this->fs_ricerca_articoli );
		$frm->anchor( $this->msg_warning );

		$frm->anchor( $this->tab_pane );
	
		e3g_scrivi_footer( $this, $frm );

  		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);


        $this->tab_spesa_articolo_afterClick();
		$this->tab_spesa_det_afterClick();
		$this->tab_listino_afterClick();

		$this->update_message();
	}


	// -------------------------------------------------------------------------
	function main()
	// -------------------------------------------------------------------------
	{
		parent::main();

		$this->update_message();
	}

    
	// -------------------------------------------------------------------------
	function bu_chiudi_click()
	// -------------------------------------------------------------------------
	{		
		$this->maskClose('cassa_gg_singolo');
		$this->showPrevMask();
	}

	
    // -------------------------------------------------------------------------
    function fld_filtro_cat_change(){
    // -------------------------------------------------------------------------
        
        $this->ds_cat->setWhere("tipo='".$this->fld_filtro_cat->getNewValue()."' OR codice='000'");     
        $this->ds_cat->load();
    }


    // -------------------------------------------------------------------------
    function bu_filtra_click() 
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        // --------------------------------- Lista della spesa (raggrup. UTENTE)
        $strWhere = $this->where_ds_spesa_utente; 

        if ( $this->fld_filtro_utente->getNewValue() != "00" ) 
            $strWhere .= " AND c.codutente = '".$this->fld_filtro_utente->getNewValue()."' ";
    
        if ( $this->fld_filtro_forn->getNewValue() != "00" )
            $strWhere .= " AND c.codfornitore = '".$this->fld_filtro_forn->getNewValue()."' ";
    
        if ( $this->fld_filtro_cat->getNewValue() != "00" )
            $strWhere .= " AND art.tipo = '".$this->fld_filtro_cat->getNewValue()."' ";
        
        if ( $this->fld_filtro_sottocat->getNewValue() != "000" )
            $strWhere .= " AND art.catmerce='".$this->fld_filtro_sottocat->getNewValue()."' ";
        
        // Luogo consegna
        if ( ( $p4a->e3g_azienda_gestione_luoghi_cons ) and ( $this->fld_filtro_luogo_cons->getNewValue() != 0 ) )
            $strWhere .= " AND ana.id_luogo_cons = " . $this->fld_filtro_luogo_cons->getNewValue();

        $this->ds_spesa_utente->setWhere( $strWhere );
        $this->ds_spesa_utente->load();
        $this->ds_spesa_utente->firstRow();

        // ------------------------------ Lista della spesa (raggrup. FORNITORE)
        $strWhere = $this->where_ds_spesa_forn; 

        if ( $this->fld_filtro_utente->getNewValue() != "00" ) 
            $strWhere .= " AND c.codutente = '".$this->fld_filtro_utente->getNewValue()."' ";
    
        if ( $this->fld_filtro_forn->getNewValue() != "00" )
            $strWhere .= " AND c.codfornitore = '".$this->fld_filtro_forn->getNewValue()."' ";
    
        if ( $this->fld_filtro_cat->getNewValue() != "00" )
            $strWhere .= " AND art.tipo = '".$this->fld_filtro_cat->getNewValue()."' ";
        
        if ( $this->fld_filtro_sottocat->getNewValue() != "000" )
            $strWhere .= " AND art.catmerce='".$this->fld_filtro_sottocat->getNewValue()."' ";
        
        // Luogo consegna
        if ( ( $p4a->e3g_azienda_gestione_luoghi_cons ) and ( $this->fld_filtro_luogo_cons->getNewValue() != 0 ) )
            $strWhere .= " AND anag_u.id_luogo_cons = " . $this->fld_filtro_luogo_cons->getNewValue();

        $this->ds_spesa_forn->setWhere( $strWhere );
        $this->ds_spesa_forn->load();
        $this->ds_spesa_forn->firstRow();

        // ------------------------------- Lista della spesa (raggrup. ARTICOLI)
        $strWhere = $this->where_ds_spesa_articolo; 

        if ( $this->fld_filtro_utente->getNewValue() != "00" ) 
            $strWhere .= " AND c.codutente = '".$this->fld_filtro_utente->getNewValue()."' ";
    
        if ( $this->fld_filtro_forn->getNewValue() != "00" )
            $strWhere .= " AND c.codfornitore = '".$this->fld_filtro_forn->getNewValue()."' ";
    
        if ( $this->fld_filtro_cat->getNewValue() != "00" )
            $strWhere .= " AND art.tipo = '".$this->fld_filtro_cat->getNewValue()."' ";
        
        if ( $this->fld_filtro_sottocat->getNewValue() != "000" )
            $strWhere .= " AND art.catmerce='".$this->fld_filtro_sottocat->getNewValue()."' ";
        
        // Luogo consegna
        if ( ( $p4a->e3g_azienda_gestione_luoghi_cons ) and ( $this->fld_filtro_luogo_cons->getNewValue() != 0 ) )
            $strWhere .= " AND " . $p4a->e3g_prefix."anagrafiche.id_luogo_cons = " . $this->fld_filtro_luogo_cons->getNewValue();
            
        $strWhere =  str_replace( "c.",   $p4a->e3g_prefix."carrello.", $strWhere );            
        $strWhere =  str_replace( "art.", $p4a->e3g_prefix."articoli.", $strWhere );            
        $this->ds_spesa_articolo->setWhere( $strWhere );
        $this->ds_spesa_articolo->load();
        $this->ds_spesa_articolo->firstRow();

        // --------------------------------------- Lista della spesa (dettaglio)
        $this->filtro_ds_spesa_det = "0 = 0"; 
                
        if ( $this->fld_filtro_utente->getNewValue() != "00" ) 
            $this->filtro_ds_spesa_det .= " AND c.codutente = '".$this->fld_filtro_utente->getNewValue()."' ";
        // Non c'è bisogno di vincolare fornitore, categoria e sottocategoria perchè già influiscono su ds_spesa_articolo che fa da master
            
        $this->tab_spesa_articolo_afterClick();
        
        // ---------------------------------------------------- Listino articoli
        $strWhere = $this->where_ds_articoli;
                
        if ( $this->fld_filtro_forn->getNewValue() != "00" )
            $strWhere .= " AND centrale = '".$this->fld_filtro_forn->getNewValue()."' ";
    
        if ( $this->fld_filtro_cat->getNewValue() != "00" )
            $strWhere .= " AND tipo='".$this->fld_filtro_cat->getNewValue()."' ";

        if ( $this->fld_filtro_sottocat->getNewValue() != "000" )
            $strWhere .= " AND catmerce='".$this->fld_filtro_sottocat->getNewValue()."' ";
        
        $this->ds_articoli->setWhere( $strWhere );
        $this->ds_articoli->load();
        $this->ds_articoli->firstRow();

        $this->tab_listino_afterClick();
        
        // ---------------------------------------------------------------------
        $this->update_message();
    }


    // -------------------------------------------------------------------------
    function bu_mostra_tutto_click()
    // -------------------------------------------------------------------------
    {       
        $p4a =& p4a::singleton();

        if ( $p4a->e3g_utente_tipo <> 'R' )
            $this->fld_filtro_forn->setNewValue("00");
        $this->fld_filtro_cat->setNewValue("00");
        $this->fld_filtro_sottocat->setNewValue("000");
        $this->fld_filtro_utente->setNewValue("00");
        $this->fld_filtro_luogo_cons->setNewValue( 0 );
        
        $this->bu_filtra_click();
    }

    
	// -------------------------------------------------------------------------
	function bu_spesa_aggiorna_qta_click()
	// -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
        if ( !is_numeric( $this->fld_spesa_qta->getNewValue() ) ) {
            $this->fld_spesa_qta->setStyleProperty( "border", "1px solid red" );
            $this->msg_warning->setIcon( "error" );
            $this->msg_warning->setValue( "Il valore specificato deve essere un numero." );
            return;
        }
        else
            $this->fld_spesa_qta->setStyleProperty( "border", "1px solid black" );
         
        $cod_articolo = $this->ds_spesa_det->fields->codarticolo->getNewValue();

        // Se l'utente in questione ha (qta=0) allora significa che non si tratta di
        // qtà ordinata da lui, ma assegnatagli in questa finestra; in tal caso se la
        // nuova qunatità è stata impostata a zero, si può/deve eliminare il record per intero
        
        // $this->ds_spesa_det->fields->qta->getNewValue() = Quantità originale ordinata
        // $this->fld_spesa_qta->getNewValue()             = Nuova quantità desiderata 
        if ( ( $this->ds_spesa_det->fields->qta->getNewValue() <> 0 ) OR ( $this->fld_spesa_qta->getNewValue() <> 0 ) )
        	$db->query(
        		"UPDATE " . $p4a->e3g_prefix . "carrello " .
        		"   SET qta_agg = ( " . str_replace( ",", ".", $this->fld_spesa_qta->getNewValue() ) . " - qta ) " .
        		" WHERE idriga = " . $this->ds_spesa_det->fields->idriga->getNewValue() );
        else {
            $db->query(
                "DELETE FROM " . $p4a->e3g_prefix . "carrello " .
                " WHERE idriga = " . $this->ds_spesa_det->fields->idriga->getNewValue() );
    
            $this->ds_spesa_det->firstRow();  // Questo comando in quanto la riga corrente di ds_spesa_det non esiste più
            $this->aggiorna_campi_assegna_qta_nuovo_ordinante();
        }
 		
		$this->update_message();
        $this->tab_spesa_det_afterClick();
	}
	
        
    // -------------------------------------------------------------------------
    function bu_assegna_qta_click()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();        
        
        // Controllo corretteza quantità indicata
        if ( !is_numeric( $this->fld_assegna_qta->getNewValue() ) or ( $this->fld_assegna_qta->getNewValue() <= 0 ) ) {
            $this->fld_assegna_qta->setStyleProperty( "border", "1px solid red" );
            $this->msg_warning->setIcon( "error" );
            $this->msg_warning->setValue( "Il valore specificato deve essere un numero maggiore di zero." );
            return;
        }
        else
            $this->fld_assegna_qta->setStyleProperty( "border", "1px solid black" );
         
        // L'utente selezionato non dovrebbe già essere presente nel carrello con lo stesso articolo, 
        // quindi si inserisce un nuovo record con
        //   qta (qta originale)    = 0
        //   qta_agg (qta aggiunta) = quella assegnata dal referente/amministratore
        
        $new_idriga = $db->queryOne( "SELECT MAX( idriga ) FROM " . $p4a->e3g_prefix . "carrello" );
        if ( is_numeric ($new_idriga) )
            $new_idriga++;
        else
            $new_idriga = 1;
            
        $db->query(
            "INSERT INTO " . $p4a->e3g_prefix . "carrello " .
            " ( idriga, codarticolo, um, descrizione, prezzoven, qta_agg, codiva, idsessione, " .
            "   codutente, stato, codfornitore, carscar, codcaumov, data, qta ) " .
            "VALUES ( " .
                $new_idriga . ", '" .
                $this->ds_spesa_articolo->fields->codarticolo->getNewValue() . "', '" . 
                $this->ds_spesa_articolo->fields->um->getNewValue() . "', '" . 
                addslashes( $this->ds_spesa_articolo->fields->descrizione->getNewValue() ) . "', " .
                $this->ds_spesa_articolo->fields->prezzoven->getNewValue() . ", " .
                $this->fld_assegna_qta->getNewValue() . ", '" .
                $this->ds_spesa_articolo->fields->codiva->getNewValue() . "', '" . 
                session_id() . "', '" .
                $this->fld_utenti_assegna_qta->getNewValue() . "', 'A', '" .
                $this->ds_spesa_articolo->fields->codfornitore->getNewValue() . "', 'S', ".
           "    ( SELECT dettaglio_causale_mov_mag FROM _aziende WHERE prefix = '$p4a->e3g_prefix' ), " .
           "    DATE_FORMAT( CURDATE(), '%Y-%m-%d' ), 0 ) " );

        $this->fld_assegna_qta->setNewValue( 0 );  // Rimette a zero la qtà da aggiungere

        $this->update_message();
    }
    
    
    // -------------------------------------------------------------------------
    function tab_spesa_articolo_afterClick()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        // Sincronizza la tabella di dettaglio degli utenti
        $this->ds_spesa_det->setWhere( $this->filtro_ds_spesa_det . 
            " AND c.codarticolo = '" . $this->ds_spesa_articolo->fields->codarticolo->getNewValue() . "'" );
        $this->ds_spesa_det->load();
        $this->ds_spesa_det->firstRow();
        $this->tab_spesa_det_afterClick();

        // Sincronizza il combo degli utenti non ordinanti
        $this->aggiorna_campi_assegna_qta_nuovo_ordinante();
    }
    

    // Sincronizza il combo degli utenti non ordinanti
    // -------------------------------------------------------------------------
    function aggiorna_campi_assegna_qta_nuovo_ordinante()
    // -------------------------------------------------------------------------
    {  
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        $this->ds_utenti_assegna_qta->setWhere( "anag.tipocfa = 'C' AND anag.tipoutente <> 'A' AND anag.stato = 1 AND " .
            " anag.codice NOT IN ( SELECT c.codutente FROM " . $p4a->e3g_prefix . "carrello AS c " .
            "                       WHERE c.codarticolo = '" . $this->ds_spesa_articolo->fields->codarticolo->getNewValue() . "' )" );
        $this->ds_utenti_assegna_qta->load();
        
        // Disattiva combo utenti, campo qtà e bottone di assegnazione se non ci 
        // sono utenti nel combo, ovvero se già tutti gli utenti hanno in ordine l'articolo in questione
        if ( $this->ds_utenti_assegna_qta->getNumRows() == 0 ) {  
            $this->fld_utenti_assegna_qta->disable();
            $this->fld_assegna_qta->disable();
            $this->bu_assegna_qta->disable();
        }
        else {
            $this->fld_utenti_assegna_qta->enable();
            $this->fld_assegna_qta->enable();
            $this->bu_assegna_qta->enable();
        }
    }

    // -------------------------------------------------------------------------
    function tab_spesa_forn_afterClick()
    // -------------------------------------------------------------------------
    {
        $db =& p4a_db::singleton();

        // Sincronizza la tabella di dettaglio degli utenti
        $this->ds_spesa_det->setWhere( $this->filtro_ds_spesa_det .
            " AND c.codfornitore = '" . $this->ds_spesa_forn->fields->codfornitore->getNewValue() . "'" );
        $this->ds_spesa_det->load();
        $this->ds_spesa_det->firstRow();

        $this->tab_spesa_det_afterClick();
    }

        
    // -------------------------------------------------------------------------
    function tab_spesa_articolo_beforeDisplay( $obj, $rows ) 
    // -------------------------------------------------------------------------
    {  
        /* array( "idarticolo", "codarticolo",
         *  "codfornitore", "descrizione", "um_qta_um", "um_qta", "pezzi_per_cartone",
         *  "pezzi_in_ordine_orig", "pezzi_in_ordine_agg", "pezzi_in_ordine_tot", "cartoni", "surplus_pezzi", "prezzoven","importo") );
         */
        for( $i=0; $i<count($rows); $i++ ) {
            // Evidenzia le righe con surplus da sistemare (purtroppo non si può modificare la sola cella del surplus)
            if ( $rows[$i]["surplus_pezzi"] <> 0 )
                $rows[$i]["descrizione"] = "<span style='color:red;'>" . $rows[$i]["descrizione"] . "</span>";
            // Evidenzia la riga corrente
            if ( $rows[$i]["idarticolo"] == $this->ds_spesa_articolo->fields->idarticolo->getNewValue() ) 
                $rows[$i]["descrizione"] = "<span style='color:black;font-weight:bold;'>" . $rows[$i]["descrizione"] . "</span>";

            // Non mostra niente nella "Conf." se non è stata impostata l'unità di misura
            if ( $rows[$i]["um_qta"] == "" )
                $rows[$i]["um_qta_um"] = "";
                
            // Per gli articoli da gestire a peso, non visualizza "Conf." e "Prezzo" (per quest'ultimo vale quello per unità di misura)
            if ( $rows[$i]["gestione_a_peso"] ) {
                $rows[$i]["um_qta_um"] = "sfuso";
                $rows[$i]["prezzoven"] = "";  // In realtà non si riesce a far sparire, ma rimane uno "0"
            }  
        }
        return $rows;  
    }  


	// -------------------------------------------------------------------------
	function tab_spesa_det_afterClick()
	// -------------------------------------------------------------------------
	{
		$db =& p4a_db::singleton();

        // Pagina "Spesa per ARTICOLO": dati singolo utente        
        $this->lbl_spesa_desc_utente->setValue( "Ordine " . $this->ds_spesa_det->fields->desc_utente->getNewValue() );
        $this->fld_spesa_qta->setValue( 
            $this->ds_spesa_det->fields->qta->getNewValue() + $this->ds_spesa_det->fields->qta_agg->getNewValue() );

        // Pagina "Listino articoli"
		$this->lbl_listino_desc_utente->setValue( 
			$this->ds_spesa_det->fields->desc_utente->getNewValue() <> "" ?
				"Utente: " . $this->ds_spesa_det->fields->desc_utente->getNewValue() : "" );
	}

		
    // Evidenzia la riga selezionata 
    // -------------------------------------------------------------------------
    function tab_spesa_det_beforeDisplay( $obj, $rows ) 
    // -------------------------------------------------------------------------
    {  
        for( $i=0; $i<count($rows); $i++ ) {
            // Evidenzia la riga selezionata  
            if ( $rows[$i]["idriga"] == $this->ds_spesa_det->fields->idriga->getNewValue() ) 
                $rows[$i]["desc_utente"] = "<span style='color:black;font-weight:bold;'>" . $rows[$i]["desc_utente"] . "</span>";
        }  
        return $rows;  
    }  


	// -------------------------------------------------------------------------
	function tab_listino_afterClick()
	// -------------------------------------------------------------------------
	{
		$db =& p4a_db::singleton();
		
		$this->lbl_listino_desc_articolo->setValue( $this->ds_articoli->fields->descrizione->getNewValue() );
		$this->flg_aggiungi_qta->setValue( 1 );
		
		// Dopo una ricerca l'elenco articoli potrebbe essere vuoto
		if ( $this->ds_articoli->fields->codice->getNewValue() <> "" )
		{
			$this->bu_scheda_articolo->enable();  
			e3g_prepara_scheda_articolo( $this->ds_articoli->fields->codice->getNewValue() );
		}
		else 
// TODO Il bottone si disabilita, infatti l'icona viene sostituita, ma stranamente è ancora possibile premerlo		
			$this->bu_scheda_articolo->disable();  
	}

		
    // Evidenzia la riga selezionata 
    // -------------------------------------------------------------------------
    function tab_listino_beforeDisplay( $obj, $rows ) 
    // -------------------------------------------------------------------------
    {  
        for( $i=0; $i<count($rows); $i++ ) {
            // Evidenzia la riga selezionata  
            if ( $rows[$i]["idarticolo"] == $this->ds_articoli->fields->idarticolo->getNewValue() )
                $rows[$i]["descrizione"] = "<span style='color:black;font-weight:bold;'>" . $rows[$i]["descrizione"] . "</span>";

            // Non mostra niente nella "Conf." se non è stata impostata l'unità di misura
            if ( $rows[$i]["um_qta"] == "" )
                $rows[$i]["um_qta_um"] = "";
                
            // Per gli articoli da gestire a peso, non visualizza "Conf." e "Prezzo" (per quest'ultimo vale quello per unità di misura)
            if ( $rows[$i]["gestione_a_peso"] ) {
                $rows[$i]["um_qta_um"] = "sfuso";
                $rows[$i]["prezzoven"] = "";  // In realtà non si riesce a far sparire, ma rimane uno "0"
            }  
        }  
        return $rows;  
    }  


	// -------------------------------------------------------------------------
	function bu_esporta_ordineClick ()
	// -------------------------------------------------------------------------
	{		
		require("class.report.php");
		
		$p4a =& p4a::singleton();
		
		$pdf = new Creport('a4','portrait');
			 		
		$singolo_utente = ( $this->fld_filtro_utente->getNewValue() != "00" ); 

		if ( !$singolo_utente )
			$arr["desc_utente"] = "Utente";
		$arr["codfornitore"]    = "Fornitore";
		$arr["descrizione"]     = "Articolo";
//		$arr["prezzo_ven_um"]   = "Prezzo/UM";
		$arr["qta"]             = "Q.ta' orig.";
		$arr["qta_agg"]         = "Q.ta' agg.";
		$arr["qta_tot"]         = "Q.ta' TOT";
		$arr["prezzoven"]       = "Prezzo";
		$arr["importo"]         = "Importo";
		
		$pdf->stampareport(
			$this->ds_spesa_det->getAll(), $arr, 
            ( $singolo_utente ? 
                "Ordine corrente " . $this->ds_spesa_det->fields->desc_utente->getNewValue() : "Ordine globale" ), 
            ( $singolo_utente ? 
                "Ordine corrente " . $this->ds_spesa_det->fields->desc_utente->getNewValue() : "Ordine globale" ) );
	}

	
    // -------------------------------------------------------------------------
    function bu_esporta_ordineCsvClick ()
    // -------------------------------------------------------------------------
    {       
        $p4a =& p4a::singleton();
        
        $singolo_utente = ( $this->fld_filtro_utente->getNewValue() != "00" ); 

        // MM_2009-01-26 Attenzione: causa probabile bug di p4a 2.2.3, non è possibile 
        // esportare le colonne in un ordine diverso da come sono presenti in tabella/query
        $colonne = array (
            "codfornitore" => "Fornitore",
            "descrizione"  => "Articolo",
            "qta"          => "Pezzi in ordine (originale)",
            "qta_agg"      => "Pezzi aggiunti",
            "qta_tot"      => "Pezzi in ordine (totale)",
            "prezzoven"    => "Prezzo unitario",
            "importo"      => "Importo",
            "desc_utente"  => "Utente"
        );
        
        e3g_db_source_exportToCsv(
            $this->ds_spesa_det, $colonne, 
            ( $singolo_utente ? 
                "Ordine corrente " . $this->ds_spesa_det->fields->desc_utente->getNewValue() : "Ordine globale" ) );
    }

    
    // -------------------------------------------------------------------------
    function bu_aggiungi_qta_click()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
        
        // Ci deve essere un utente selezionato
        $codice_utente = "";
        if ( $this->lbl_listino_desc_utente->getValue() == "" ) 
        {
            if ($this->fld_filtro_utente->getNewValue() == "00")
            {   
                $codice_utente = "";
                $this->msg_warning->setValue( "Selezionare un utente (una riga nella \"Lista della Spesa/dettaglio\") al quale aggiungere l'articolo." );
            }
            else 
                $codice_utente = $this->fld_filtro_utente->getNewValue();                   
        }
        else 
            $codice_utente = $this->ds_spesa_det->fields->codutente->getNewValue();
        
        if ( $codice_utente != "" )
        {
            $cod_articolo = $this->ds_articoli->fields->codice->getNewValue();
    
            $rigaid = $db->queryOne(
                "SELECT idriga FROM " . $p4a->e3g_prefix . "carrello " .
                " WHERE codutente = '" . $codice_utente . "' AND codarticolo = '" . $cod_articolo . "'");
            if ( is_numeric($rigaid) )
            {
                // ho un ID RIGA valido quindi ho già righe per questo utente
                $db->query(
                    "UPDATE " . $p4a->e3g_prefix . "carrello " .
                    "   SET qta_agg = qta_agg + ". $this->flg_aggiungi_qta->getNewValue() . 
                    " WHERE idriga = " . $rigaid );
            }
            else 
            {
                $new_idriga = $db->queryOne( "SELECT MAX( idriga ) FROM " . $p4a->e3g_prefix . "carrello" );
                if ( is_numeric ($new_idriga) )
                    $new_idriga++;
                else
                    $new_idriga = 1;
                
                // non ho nessuna riga per questo utente
                $db->query(
                    "INSERT INTO " . $p4a->e3g_prefix . "carrello " .
                    " ( idriga, codarticolo, um, descrizione, prezzoven, qta_agg, codiva, idsessione, " .
                    "   codutente, stato, codfornitore, carscar, codcaumov, data, qta ) " .
                    "VALUES ( " .
                        $new_idriga . ", '" .
                        $cod_articolo . "', '" . 
                        $this->ds_articoli->fields->um->getNewValue() . "','" . 
                        addslashes( $this->ds_articoli->fields->descrizione->getNewValue() ) . "'," .
                        $this->ds_articoli->fields->prezzoven->getNewValue() . "," .
                        $this->flg_aggiungi_qta->getNewValue() . ",'" .
                        $this->ds_articoli->fields->codiva->getNewValue() . "','" . 
                        session_id() . "','" .
                        $codice_utente . "', 'A', '" .
                        $this->ds_articoli->fields->centrale->getNewValue() . "', 'S', '".
                        $db->queryOne( "SELECT dettaglio_causale_mov_mag FROM _aziende WHERE prefix = '$p4a->e3g_prefix'" ). "','" .
                        date ("Y-m-d")."', 0 ) " );
            }               
                
            
            $this->tab_listino_afterClick();
            
            $this->update_message();
        }
    }


	// -------------------------------------------------------------------------
	function update_message()
	// -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		$n_ordini_aperti = $db->queryOne(
			"SELECT COUNT(*) AS valore FROM ".$p4a->e3g_prefix."fornitoreperiodo " . 
			" WHERE " . e3g_where_ordini_aperti() );

		if ( $n_ordini_aperti = 0 )
   			$testo_msg = "Nessun ordine attualmente aperto. ";
   		else
   			$testo_msg = "";

		$this->msg_info->setValue( $testo_msg );
		

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
			$testo = "L'ordine globale e' vuoto.";
		else 
			$testo = 
				"<strong>" . $ordine['n_utenti'] . " utenti</strong> hanno ordinato da <strong>" . $ordine['n_fornitori'] . " fornitori</strong><br />" .   
				"<strong>" . $ordine['articoli_diversi'] . " articoli</strong> diversi in quantita' di <strong>$pezzi pezzi</strong> per un importo totale di <strong>" . $ordine['importo'] . " euro</strong>.";

		$this->lbl_situazione->setValue( $testo );


		// ------------------------------ Aggiorna situazione UTENTE selezionato
		
		if ( $this->fld_filtro_utente->getNewValue() <> "00" )
		{
			$result = $db->queryRow( "SELECT SUM( qta+qta_agg ) AS qta, SUM( prezzoven * (qta+qta_agg) ) AS importo FROM " .
				$p4a->e3g_prefix . "carrello WHERE codutente = '" . $this->fld_filtro_utente->getNewValue() . "'" );
	
			$qta = (integer) $result[ "qta" ];
			$importo = (double) $result[ "importo" ];
			
			if ( $qta == 0 )
				$testo = "Lista della spesa vuota";
			else {
				$testo = $this->ds_spesa_det->fields->desc_utente->getNewValue() . ": <strong>$qta articol" . ( $qta==1 ? "o" : "i" ) . 
					"</strong> in ordine per un importo totale di <strong>" . $importo . " euro</strong>";
	
				if ( $p4a->e3g_azienda_ordine_minimo > 0 and $p4a->e3g_azienda_ordine_minimo > $importo ) 
		   			$testo .= " <em>(l'ordine minimo e' di $p4a->e3g_azienda_ordine_minimo euro)</em>" ;			
			}
			
			$this->lbl_situazione_utente->setValue( $testo );
			$this->lbl_situazione_utente->setVisible();
		}
		else
			$this->lbl_situazione_utente->setInvisible();


		// --------------------------- Aggiorna situazione FORNITORE selezionato
		
		if ( $this->fld_filtro_forn->getNewValue() <> "00" )
		{
			$result = $db->queryRow( 
				"SELECT SUM( c.qta+c.qta_agg ) AS qta, SUM( c.prezzoven * (c.qta+c.qta_agg) ) AS importo " .
				"  FROM " . $p4a->e3g_prefix . "carrello c " .
				" WHERE c.codfornitore = '" . $this->fld_filtro_forn->getNewValue() . "'" );
	
			$qta = (integer) $result[ "qta" ];
			$importo = (double) $result[ "importo" ];
			
			if ( $qta == 0 )
				$testo = "Lista della spesa vuota";
			else {
				$testo = $this->ds_spesa_forn->fields->desc_fornitore->getNewValue() . ": <strong>$qta articol" . ( $qta==1 ? "o" : "i" ) . 
					"</strong> in ordine per un importo totale di <strong>" . $importo . " euro</strong>";
	
//				if ( $p4a->e3g_azienda_ordine_minimo > 0 and $p4a->e3g_azienda_ordine_minimo > $importo ) 
//		   			$testo .= " <em>(l'ordine minimo e' di $p4a->e3g_azienda_ordine_minimo euro)</em>" ;			
			}
			
			$this->lbl_situazione_forn->setValue( $testo );
			$this->lbl_situazione_forn->setVisible();
		}
		else
			$this->lbl_situazione_forn->setInvisible();
	}
/*
SELECT SUM( c.qta+c.qta_agg ) AS qta, 
       SUM( c.prezzoven * (c.qta+c.qta_agg) ) AS importo,
       SUM( a.um_qta * (c.qta+c.qta_agg) ) AS peso_tot,
       um.codice, um.genere
  FROM mantogas_carrello AS c 
       JOIN mantogas_articoli AS a ON c.codarticolo = a.codice
       LEFT JOIN mantogas_um AS um ON um.codice = a.um
 WHERE c.codfornitore = 'AMR'

GROUP BY um.codice
 */
}

?>
