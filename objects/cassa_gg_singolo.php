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


class cassa_gg_singolo extends P4A_Mask
{

    // -------------------------------------------------------------------------
	function cassa_gg_singolo()
    // -------------------------------------------------------------------------
	{
		// ORDINE CORRENTE SINGOLA FAMIGLIA (solo per GestiGAS)
		
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
        $this->setTitle( "" );

		// -------------------------------------- Sorgente dati LISTINO ARTICOLI	
        	
		$this->build("p4a_db_source", "ART_ds_articoli");
        $this->ART_ds_articoli->setPageLimit( $p4a->e3g_utente_db_source_page_limit );

        $this->ART_ds_articoli->setSelect(
            "a.idarticolo, a.centrale, a.codice, a.bio, " .
            "a.descrizione, a.desc_agg, a.gestione_a_peso, " .
            "CONCAT_WS( ' ', um_qta, um ) AS um_qta_um, " .  // CONCAT_WS non è vuoto se manca l'UM 
            "ROUND( a.prezzoven, $p4a->e3g_azienda_n_decimali_prezzi ) AS prezzoven, " .
            "CONCAT( ROUND( a.prezzoven/a.um_qta, $p4a->e3g_azienda_n_decimali_prezzi), '/', a.um ) AS prezzo_ven_um, " .  // CONCAT è vuoto se manca l'UM 
            "a.prezzoacq, a.codiva, a.tipo, a.giacenza, a.progetto, a.ingredienti, " .
            "a.catmerce, a.tipoarticolo, a.paese, a.contovendita, a.contoacquisto, " .
            "a.posizione, a.periodo, a.um, a.scortaminima, a.pzperconf, a.qtaminordine, a.qtaminperfamiglia, a.um_qta, " .
            "f.descrizione AS fornitore, f.www, um.genere AS genere_um " );
        
        $this->ART_ds_articoli->setTable( $p4a->e3g_prefix . "articoli AS a" );
        $this->ART_ds_articoli->addJoin( $p4a->e3g_prefix . "fornitoreperiodo AS fp", "a.centrale = fp.fornitore" );
        $this->ART_ds_articoli->addJoin( $p4a->e3g_prefix . "anagrafiche AS f", "a.centrale = f.codice" );
        $this->ART_ds_articoli->addJoin( $p4a->e3g_prefix . "um AS um", "a.um = um.codice", "LEFT" );
        $this->ART_ds_articoli->setWhere( e3g_where_ordini_aperti("fp") . " AND ( a.stato = 1 ) " );
        $this->ART_ds_articoli->addOrder( "f.descrizione" );  // Mantenere l'ordinamento come nell'esportazione listino
        $this->ART_ds_articoli->addOrder( "a.catmerce" );
        $this->ART_ds_articoli->addOrder( "a.descrizione" );

		$this->ART_ds_articoli->setPk( "a.idarticolo" );
		$this->ART_ds_articoli->load();
		$this->ART_ds_articoli->firstRow();
		
        $this->setSource( $this->ART_ds_articoli );


        // ------------------------------------- Sorgente dati LISTA DELLA SPESA
        
        $this->build( "p4a_db_source", "SPE_ds_lista_spesa" );
        $this->SPE_ds_lista_spesa->setPageLimit( $p4a->e3g_utente_db_source_page_limit );

        // NON variare l'ordine dei campi estratti, leggere il motivo in SPE_bu_esporta_ordineCsvClick()    
        $this->SPE_ds_lista_spesa->setSelect(
            "c.idriga, c.data, " .
            "c.codfornitore, f.descrizione AS desc_fornitore, " .
            "( c.qta + c.qta_agg ) AS qta, " .
            "art.bio, art.codice AS cod_articolo, art.descrizione, " .
            "CONCAT_WS( ' ', art.um_qta, art.um ) AS um_qta_um, " .  // CONCAT_WS non è vuoto se manca l'UM 
            "c.codcaumov, c.carscar, c.codiva, c.idsessione, c.stato, " .
            "c.qtaconsegnata, c.um, c.codutente, " .
            "CONCAT( ROUND( art.prezzoven/art.um_qta, $p4a->e3g_azienda_n_decimali_prezzi), '/', art.um ) AS prezzo_ven_um, " .  // CONCAT è vuoto se manca l'UM
            "ROUND( c.prezzoven, $p4a->e3g_azienda_n_decimali_prezzi ) AS prezzoven, " .
            "ROUND( c.prezzoven * c.qta, $p4a->e3g_azienda_n_decimali_prezzi ) AS importo, " .
            "art.desc_agg, art.pzperconf, art.qtaminordine, art.qtaminperfamiglia, art.um_qta, art.gestione_a_peso, um.genere AS genere_um "  );
        
        $this->SPE_ds_lista_spesa->setTable( $p4a->e3g_prefix . "carrello AS c" );
        $this->SPE_ds_lista_spesa->addJoin( $p4a->e3g_prefix . "articoli AS art", "c.codarticolo = art.codice" );
        $this->SPE_ds_lista_spesa->addJoin( $p4a->e3g_prefix . "anagrafiche AS f", "c.codfornitore = f.codice" );  // Serve per l'esportazione dell'ordine
        $this->SPE_ds_lista_spesa->addJoin( $p4a->e3g_prefix . "um AS um", "art.um = um.codice", "LEFT" );
        $this->SPE_ds_lista_spesa->setWhere( "c.codutente = '$p4a->e3g_utente_codice' " );
        $this->SPE_ds_lista_spesa->addOrder( "c.codfornitore" );
        $this->SPE_ds_lista_spesa->addOrder( "c.descrizione" );

        $this->SPE_ds_lista_spesa->setPk( "c.idriga" );
        $this->SPE_ds_lista_spesa->load();
        $this->SPE_ds_lista_spesa->firstRow();

        
		// ----------------------------------------------------- Altri DB source
        
		// Fornitori (per decodifica codice nelle griglie)
        $this->build( "p4a_db_source", "ds_fornitori" );
		$this->ds_fornitori->setTable( $p4a->e3g_prefix . "anagrafiche" );
		$this->ds_fornitori->setPk( "codice" );		
		$this->ds_fornitori->setWhere( "tipocfa = 'F'" );		
		$this->ds_fornitori->addOrder( "codice" );		
		$this->ds_fornitori->load();
		$this->ds_fornitori->firstRow();

		// Fornitore (per filtri)
		$this->build( "p4a_db_source", "ds_filtro_fornitori" );
        $this->ds_filtro_fornitori->setSelect( "codice, CONCAT_WS( ' / ', descrizione, desc_agg ) AS descrizione" );
		$this->ds_filtro_fornitori->setTable( $p4a->e3g_prefix . "anagrafiche" );
		$this->ds_filtro_fornitori->setWhere( "( tipocfa = 'F' AND " . e3g_where_ordini_aperti() . " ) OR idanag = 0" );
		$this->ds_filtro_fornitori->setPk( "codice" );		
		$this->ds_filtro_fornitori->addJoin( $p4a->e3g_prefix . "fornitoreperiodo", $p4a->e3g_prefix . "anagrafiche.codice = " . $p4a->e3g_prefix . "fornitoreperiodo.fornitore", "LEFT" );		
		$this->ds_filtro_fornitori->addOrder( "descrizione" );		
		$this->ds_filtro_fornitori->load();		
		
		// Categorie articoli (per filtri)
		$this->build( "p4a_db_source", "ds_filtro_cat_articoli" );
		$this->ds_filtro_cat_articoli->setTable( $p4a->e3g_prefix . "tipiarticoli" );
		$this->ds_filtro_cat_articoli->setPk( "codice" );		
		$this->ds_filtro_cat_articoli->addOrder( "codice" );		
		$this->ds_filtro_cat_articoli->load();
		$this->ds_filtro_cat_articoli->firstRow();
		
        // Sotto-categorie articoli (per filtri)
		$this->build( "p4a_db_source", "ds_filtro_sottocat_articoli");
		$this->ds_filtro_sottocat_articoli->setTable( $p4a->e3g_prefix."catmerceologica" );
		$this->ds_filtro_sottocat_articoli->setWhere( "tipo = '" . $this->ds_filtro_cat_articoli->fields->codice->getNewValue() . "'" );		
		$this->ds_filtro_sottocat_articoli->setPk( "codice" );		
		$this->ds_filtro_sottocat_articoli->addOrder( "codice" );		
		$this->ds_filtro_sottocat_articoli->load();


        //--------------------------------------- Oggetti in testa alla finestra
        
        // Chiusura finestra        
        $this->build( "p4a_button", "bu_chiudi" );
        $this->bu_chiudi->setLabel( "Chiudi finestra" );
        $this->bu_chiudi->addAction( "onClick");
        $this->intercept( $this->bu_chiudi, "onClick", "bu_chiudiClick" );
        $this->bu_chiudi->setWidth( 150 );
        $this->bu_chiudi->setIcon ( "exit" );
        
        // Label per segnalare situazione ordine        
        $this->build( "p4a_label", "lbl_situazione" );
        $this->lbl_situazione->setWidth( E3G_MAIN_FRAME_WIDTH-$this->bu_chiudi->getWidth()-25 );

		// Message per eventuali warning relativi...
		
		// ...al singolo prodotto che si sta aggiungendo/modificando (vengono visualizzati una sola volta)
		$this->build( "p4a_message", "msg_warning" );
		$this->msg_warning->setWidth( 750 );

		// ...ordine chiuso, importo minimo non raggiunto (rimangono visualizzati sempre)
		$this->build( "p4a_message", "msg_info" );
		$this->msg_info->setWidth( 750 );
		$this->msg_info->setIcon( "info" );
		$this->msg_info->autoClear( false );


		// -------------------------------- Pannello ricerca in LISTINO ARTICOLI

		$ART_fld_forn_cerca = & $this->build("p4a_field", "ART_fld_forn_cerca");
		$ART_fld_forn_cerca->setLabel( "Fornitore" );
		$ART_fld_forn_cerca->label->setWidth(100);
		$ART_fld_forn_cerca->setType("select");
		$ART_fld_forn_cerca->setSource($this->ds_filtro_fornitori);
		$ART_fld_forn_cerca->setSourceValueField("codice");
		$ART_fld_forn_cerca->setSourceDescriptionField("descrizione");
		$ART_fld_forn_cerca->setWidth( 250 );
		$ART_fld_forn_cerca->setNewValue("00");

        $this->build( "p4a_field", "ART_ck_solo_bio" );
        $this->ART_ck_solo_bio->setType( "checkbox" );
        $this->ART_ck_solo_bio->setLabel( "Solo articoli bio" );
        $this->ART_ck_solo_bio->setTooltip( "Vedi solo articoli da agricoltura biologica" );
        $this->ART_ck_solo_bio->label->setWidth( 150 );
        
		$ART_fld_categ_cerca = & $this->build("p4a_field", "ART_fld_categ_cerca");
		$ART_fld_categ_cerca->setLabel( "Categoria" );
		$ART_fld_categ_cerca->label->setWidth( 100 );
		$ART_fld_categ_cerca->setType("select");
		$ART_fld_categ_cerca->setSource($this->ds_filtro_cat_articoli);
		$ART_fld_categ_cerca->setSourceValueField("codice");
		$ART_fld_categ_cerca->setSourceDescriptionField("descrizione");
		$ART_fld_categ_cerca->addAction("OnChange");
		$ART_fld_categ_cerca->setWidth( 250 );
		$this->intercept($this->ART_fld_categ_cerca, "onChange","fld_categ_cerca_change");		
		
		$ART_fld_sottocateg_cerca=& $this->build("p4a_field", "ART_fld_sottocateg_cerca");
		$ART_fld_sottocateg_cerca->setLabel( "Sottocategoria" );
		$ART_fld_sottocateg_cerca->label->setWidth( 150 );
		$ART_fld_sottocateg_cerca->setType("select");
		$ART_fld_sottocateg_cerca->setSource($this->ds_filtro_sottocat_articoli);
		$ART_fld_sottocateg_cerca->setSourceValueField("codice");
		$ART_fld_sottocateg_cerca->setSourceDescriptionField("descrizione");
		$ART_fld_sottocateg_cerca->setWidth( 200 );
		
		$ART_fld_desc_cerca=& $this->build("p4a_field", "ART_fld_desc_cerca");
		$ART_fld_desc_cerca->setLabel( "Descrizione" );
		$ART_fld_desc_cerca->label->setWidth(100);
		$ART_fld_desc_cerca->setWidth( 250 );

        // Eventuali campi filtro sugli ingredienti        
        $this->build( "p4a_field", "ART_fld_ingredienti" );
        $this->ART_fld_ingredienti->setType( "textarea" );
        $this->ART_fld_ingredienti->setLabel( "Ingredienti da escludere" );
        $this->ART_fld_ingredienti->setTooltip( "Visualizza solo articoli che non includono gli ingredienti specificati (separarli con una virgola e non inserire spazi)" );
        $this->ART_fld_ingredienti->label->setWidth( 250 );
        $this->ART_fld_ingredienti->setWidth( 625 );
        $this->ART_fld_ingredienti->setHeight( 50 );
        
        $result = $db->queryRow(
            "SELECT ingredienti_escludi FROM " . $p4a->e3g_prefix . "anagrafiche " .
            " WHERE idanag = " . $p4a->e3g_utente_idanag );
        $this->ART_fld_ingredienti->setNewValue( $result["ingredienti_escludi"] );

		// Bottone FILTRA ARTICOLI
		$this->build("p4a_button", "ART_bu_filtra");
		$this->ART_bu_filtra->setLabel("Filtra listino articoli");
		$this->ART_bu_filtra->addAction("onClick");
		$this->ART_bu_filtra->setIcon("find");
		$this->ART_bu_filtra->setSize( 16 );
		$this->intercept($this->ART_bu_filtra, "onClick", "ART_bu_filtraClick");

		// Bottone ANNULLA FILTRO (vedi tutto)
		$this->build("p4a_button", "ART_bu_annulla_filtro");
		$this->ART_bu_annulla_filtro->setLabel("Mostra tutto");
		$this->ART_bu_annulla_filtro->addAction("onClick");
		$this->ART_bu_annulla_filtro->setIcon("cancel");
		$this->ART_bu_annulla_filtro->setSize( 16 );
		$this->intercept($this->ART_bu_annulla_filtro, "onClick", "ART_bu_annulla_filtroClick");


		// -------------------------------------------- Tabella LISTINO ARTICOLI
        
		$this->build( "p4a_table", "ART_tab_listino" );
 		$this->ART_tab_listino->setWidth( E3G_NARROW_TABLE_IN_TAB_PANE_WIDTH );
		$this->ART_tab_listino->setSource( $this->ART_ds_articoli );
		$this->ART_tab_listino->setVisibleCols( array("idarticolo", "centrale", "gestione_a_peso", "bio", "descrizione", "um_qta_um", "um_qta", "prezzoven", "prezzo_ven_um", "qtaminperfamiglia"));
		$this->ART_tab_listino->showNavigationBar();
		$this->intercept( $this->ART_tab_listino->rows, "afterClick", "ART_tab_listino_afterClick" );
        $this->intercept( $this->ART_tab_listino->rows, "beforeDisplay", "ART_tab_listino_beforeDisplay" );  
		
        $this->ART_tab_listino->data->fields->prezzoven->setType("float");

        $this->ART_tab_listino->cols->idarticolo->setVisible( false );
        $this->ART_tab_listino->cols->gestione_a_peso->setVisible( false );
        $this->ART_tab_listino->cols->um_qta->setVisible( false );

//		$this->ART_tab_listino->data->fields->prezzo_ven_um->setAlign('right');
//		$this->ART_tab_listino->data->fields->prezzo_ven_um->setType("float");
//		$this->ART_tab_listino->data->fields->prezzo_ven_um->setStyleProperty('text-align', 'right');

		$this->ART_tab_listino->cols->descrizione->setOrderable( false );
        $this->ART_tab_listino->cols->um_qta_um->setOrderable( false );
		$this->ART_tab_listino->cols->prezzo_ven_um->setOrderable( false );

		$this->ART_tab_listino->cols->centrale->setLabel( "Fornitore" );
		$this->ART_tab_listino->cols->descrizione->setLabel( "Articolo" );
        $this->ART_tab_listino->cols->um_qta_um->setLabel( "Conf." );
		$this->ART_tab_listino->cols->prezzo_ven_um->setLabel( "Prezzo/UM" );
		$this->ART_tab_listino->cols->prezzoven->setLabel( "Prezzo" );
        $this->ART_tab_listino->cols->qtaminperfamiglia->setLabel( "Min." );

		$this->ART_tab_listino->cols->centrale->setWidth( 160 );
        $this->ART_tab_listino->cols->bio->setWidth( 20 );
//		$this->ART_tab_listino->cols->descrizione->  per differenza
        $this->ART_tab_listino->cols->um_qta_um->setWidth( 50 );
		$this->ART_tab_listino->cols->prezzo_ven_um->setWidth( 75 );
		$this->ART_tab_listino->cols->prezzoven->setWidth( 60 );
        $this->ART_tab_listino->cols->qtaminperfamiglia->setWidth( 40 );            

		$this->ART_tab_listino->cols->centrale->setSource( $this->ds_fornitori );
		$this->ART_tab_listino->cols->centrale->setSourceValueField( "codice" );
		$this->ART_tab_listino->cols->centrale->setSourceDescriptionField( "descrizione" );
		

        // ----------------- Campi dettaglio sotto la tabella "LISTINO ARTICOLI"
        
        // Campo descrizione (arricchita)        
        $this->build( "p4a_field", "ART_fld_desc_articolo" );

        // SetType()
        $this->fields->desc_agg->setType( "textarea" );
        $this->fields->gestione_a_peso->setType( "checkbox" );
        $this->fields->ingredienti->setType( "textarea" );

        // Label()
        $this->ART_fld_desc_articolo->setLabel( "Articolo" );
        $this->fields->desc_agg->setLabel( "" );
        $this->fields->qtaminordine->setLabel( "Pezzi per cartone" );
        $this->fields->gestione_a_peso->setLabel( "A peso" );
        $this->fields->qtaminperfamiglia->setLabel( "Ordine minimo [pz]" );
        $this->fields->pzperconf->setLabel( "Ordine solo multiplo di [pz]" );
        $this->fields->ingredienti->setLabel( "Ingredienti" );
        
        // setWidth()
        $this->ART_fld_desc_articolo->setWidth( 635 );
        $this->fields->desc_agg->setWidth( 635 );
        $this->fields->qtaminordine->setWidth( 195 );
        $this->fields->gestione_a_peso->setWidth( 195 );
        $this->fields->qtaminperfamiglia->setWidth( 210 );
        $this->fields->pzperconf->setWidth( 260 );
        $this->fields->ingredienti->setWidth( 635 );

        // setHeight()
        $this->fields->desc_agg->setHeight( 30 );  // 2 righe
        $this->fields->ingredienti->setHeight( 30 );  // 2 righe

        // label->setWidth()
        $this->fields->qtaminperfamiglia->label->setWidth( 120 );
        $this->fields->pzperconf->label->setWidth( 160 );

        // FontColor()       
        $this->ART_fld_desc_articolo->setFontColor( "black" );
        $this->fields->desc_agg->setFontColor( "black" );

        // SetFontWeight()       
        $this->ART_fld_desc_articolo->setFontWeight( "bold" );

        // Tooltip
        $this->fields->gestione_a_peso->setTooltip( "Articolo ordinabile a peso" );


		// ------------------------- Oggetti sul lato destro di LISTINO ARTICOLI		
		
		$this->build( "p4a_label", "ART_lbl_desc_articolo" );
		$this->ART_lbl_desc_articolo->setWidth(E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH);
        $this->ART_lbl_desc_articolo->setFontColor( "black" );
		
		$this->build( "p4a_field", "ART_fld_aggiungi_qta" );
		$this->ART_fld_aggiungi_qta->setLabel( "Quantita'" );
		$this->ART_fld_aggiungi_qta->label->setWidth( E3G_LABEL_IN_TAB_PANE_WIDTH );
		$this->ART_fld_aggiungi_qta->setWidth( E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->ART_fld_aggiungi_qta->setFontColor( "black" );
        $this->ART_fld_aggiungi_qta->label->setFontColor( "black" );

		$this->build("p4a_button", "ART_bu_aggiungi_qta");
		$this->ART_bu_aggiungi_qta->setLabel("Aggiungi");
		$this->ART_bu_aggiungi_qta->setIcon( "edit_add" );
        $this->ART_bu_aggiungi_qta->setWidth( 20 + E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->ART_bu_aggiungi_qta->setSize( 16 );
		$this->ART_bu_aggiungi_qta->addAction("onClick");
		$this->intercept($this->ART_bu_aggiungi_qta, "onClick", "ART_bu_aggiungi_qtaClick");
		if ( $p4a->e3g_utente_tipo == 'A' )
			$this->ART_bu_aggiungi_qta->Disable();  // L'admin globale NON può fare l'ordine
/*		
		$this->build("p4a_button", "ART_bu_scheda_articolo");
		$this->ART_bu_scheda_articolo->setLabel("Scheda prodotto");
		$this->ART_bu_scheda_articolo->setIcon ( "info" );
        $this->ART_bu_scheda_articolo->setWidth( 20 + E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->ART_bu_scheda_articolo->setSize( 16 );
		$this->ART_bu_scheda_articolo->dropAction("onClick"); 
		$nome_file_scheda = "cache/scheda_articolo_" . $p4a->e3g_prefix . md5($p4a->e3g_utente_codice) . ".html";
		$this->ART_bu_scheda_articolo->setProperty("onclick", 
			"myRef = window.open('$nome_file_scheda', 'method_desc', 'status=yes,width=450,height=500,resizable=0');myRef.focus()"); 
			//window.open(''+self.location,'mywin','left=20,top=20,width=500,height=500,toolbar=1,resizable=0')
*/
        // Bottone ESPORTA COME PDF
		$this->build( "p4a_button", "ART_bu_esporta_listinoPdf" );
		$this->ART_bu_esporta_listinoPdf->setLabel( "Esporta come PDF" );
        $this->ART_bu_esporta_listinoPdf->setIcon( "pdf" );
        $this->ART_bu_esporta_listinoPdf->setWidth( 20 + E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->ART_bu_esporta_listinoPdf->setSize( 16 );
		$this->ART_bu_esporta_listinoPdf->addAction( "onClick" );
		$this->intercept( $this->ART_bu_esporta_listinoPdf, "onClick", "ART_bu_esporta_listinoPdfClick" );

        // Bottone ESPORTA ORDINE CSV
        $this->build( "p4a_button", "ART_bu_esporta_listinoCsv" );
        $this->ART_bu_esporta_listinoCsv->setLabel( "Esporta come CSV" );
        $this->ART_bu_esporta_listinoCsv->setIcon( "spreadsheet" );
        $this->ART_bu_esporta_listinoCsv->setWidth( 20 + E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->ART_bu_esporta_listinoCsv->setSize( 16 );
        $this->ART_bu_esporta_listinoCsv->addAction( "onClick" );
        $this->intercept( $this->ART_bu_esporta_listinoCsv, "onClick", "ART_bu_esporta_listinoCsvClick" );
		
		
		// --------------------------------- ANCORAGGIO OGGETTI listino articoli
		
		// LISTINO ARTICOLI: filtro
		$ART_fs_filtro_articoli =& $this->build("p4a_fieldset", "ART_fs_filtro_articoli");
		$ART_fs_filtro_articoli->setTitle("Filtro");
		$ART_fs_filtro_articoli->setWidth( E3G_TABLE_IN_TAB_PANE_WIDTH -15 );
		$ART_fs_filtro_articoli->anchor( $this->ART_fld_forn_cerca );
        $ART_fs_filtro_articoli->anchorLeft( $this->ART_ck_solo_bio );
		$ART_fs_filtro_articoli->anchor( $this->ART_fld_categ_cerca );
		$ART_fs_filtro_articoli->anchorLeft( $this->ART_fld_sottocateg_cerca );
		$ART_fs_filtro_articoli->anchor( $this->ART_fld_desc_cerca );
        $ART_fs_filtro_articoli->anchorRight( $this->ART_bu_annulla_filtro );
		$ART_fs_filtro_articoli->anchorRight( $this->ART_bu_filtra );
        if ( $p4a->e3g_utente_filtro_ingredienti ) 
            $ART_fs_filtro_articoli->anchor( $this->ART_fld_ingredienti, "130px" );

		// LISTINO ARTICOLI: spalla
        $ART_sh_spalla_listino =& $this->build("p4a_sheet", "ART_sh_spalla_listino");
		$ART_sh_spalla_listino->defineGrid(8, 1);
		$ART_sh_spalla_listino->anchor( $this->ART_lbl_desc_articolo, 1,1);
		$ART_sh_spalla_listino->anchor( $this->ART_fld_aggiungi_qta, 2,1);
		$ART_sh_spalla_listino->anchor( $this->ART_bu_aggiungi_qta, 3,1);
		$ART_sh_spalla_listino->anchorText("<br>", 4,1);
//		$ART_sh_spalla_listino->anchor( $this->ART_bu_scheda_articolo, 5,1);
//        $ART_sh_spalla_listino->anchorText("<br>", 6,1);
        $ART_sh_spalla_listino->anchor( $this->ART_bu_esporta_listinoPdf, 7,1);
        $ART_sh_spalla_listino->anchor( $this->ART_bu_esporta_listinoCsv, 8,1);
		
		// Sheet LISTINO ARTICOLI
		$ART_sh_listino =& $this->build("p4a_sheet", "ART_sh_listino");
		$ART_sh_listino->defineGrid(1, 2);
		$ART_sh_listino->anchor( $this->ART_tab_listino, 1,1);
		$ART_sh_listino->anchor( $ART_sh_spalla_listino, 1,2);


        // -------------------------------- Pannello filtro in LISTA DELLA SPESA

        $SPE_fld_filtro_fornitore = & $this->build( "p4a_field", "SPE_fld_filtro_fornitore" );
        $SPE_fld_filtro_fornitore->setLabel( "Fornitore" );
        $SPE_fld_filtro_fornitore->label->setWidth( 100 );
        $SPE_fld_filtro_fornitore->setType( "select" );
        $SPE_fld_filtro_fornitore->setSource( $this->ds_filtro_fornitori );
        $SPE_fld_filtro_fornitore->setSourceValueField( "codice" );
        $SPE_fld_filtro_fornitore->setSourceDescriptionField( "descrizione" );
        $SPE_fld_filtro_fornitore->setWidth( 250 );
        $SPE_fld_filtro_fornitore->setNewValue( "00" );

        $SPE_fld_filtro_categoria = & $this->build( "p4a_field", "SPE_fld_filtro_categoria" );
        $SPE_fld_filtro_categoria->setLabel( "Categoria" );
        $SPE_fld_filtro_categoria->label->setWidth( 100 );
        $SPE_fld_filtro_categoria->setType( "select" );
        $SPE_fld_filtro_categoria->setSource( $this->ds_filtro_cat_articoli );
        $SPE_fld_filtro_categoria->setSourceValueField( "codice" );
        $SPE_fld_filtro_categoria->setSourceDescriptionField( "descrizione" );
        $SPE_fld_filtro_categoria->setWidth( 250 );
        
        // Bottone FILTRO
        $this->build("p4a_button", "SPE_bu_filtro");
        $this->SPE_bu_filtro->setLabel("Filtra lista della spesa");
        $this->SPE_bu_filtro->addAction("onClick");
        $this->SPE_bu_filtro->setIcon("find");
        $this->SPE_bu_filtro->setSize( 16 );
        $this->intercept($this->SPE_bu_filtro, "onClick", "SPE_bu_filtroClick");

        // Bottone ANNULLA FILTRO (vedi tutto)
        $this->build("p4a_button", "SPE_bu_annulla_filtro");
        $this->SPE_bu_annulla_filtro->setLabel("Mostra tutto");
        $this->SPE_bu_annulla_filtro->addAction("onClick");
        $this->SPE_bu_annulla_filtro->setIcon("cancel");
        $this->SPE_bu_annulla_filtro->setSize( 16 );
        $this->intercept($this->SPE_bu_annulla_filtro, "onClick", "SPE_bu_annulla_filtroClick");


        // ----------------------------------------- Tabella "LISTA DELLA SPESA"
        
        $this->build( "p4a_table", "SPE_tab_lista_spesa" );
        $this->SPE_tab_lista_spesa->setWidth( E3G_NARROW_TABLE_IN_TAB_PANE_WIDTH );
        $this->SPE_tab_lista_spesa->setSource( $this->SPE_ds_lista_spesa ); 
        $this->SPE_tab_lista_spesa->setVisibleCols( array("idriga", "gestione_a_peso", "desc_fornitore", "qta", "descrizione", "um_qta_um", "um_qta", "importo") );
        $this->SPE_tab_lista_spesa->showNavigationBar();
        $this->intercept( $this->SPE_tab_lista_spesa->rows, "afterClick", "SPE_tab_lista_spesa_afterClick");
        $this->intercept( $this->SPE_tab_lista_spesa->rows, "beforeDisplay", "SPE_tab_lista_spesa_beforeDisplay" );  

        $this->SPE_tab_lista_spesa->data->fields->importo->setType("float");

        $this->SPE_tab_lista_spesa->cols->idriga->setVisible( false );
        $this->SPE_tab_lista_spesa->cols->gestione_a_peso->setVisible( false );
        $this->SPE_tab_lista_spesa->cols->um_qta->setVisible( false );
        
        $this->SPE_tab_lista_spesa->cols->desc_fornitore->setLabel( "Fornitore" );
        $this->SPE_tab_lista_spesa->cols->qta->setLabel( "Q.ta'" );
        $this->SPE_tab_lista_spesa->cols->descrizione->setLabel( "Articolo" );
        $this->SPE_tab_lista_spesa->cols->um_qta_um->setLabel( "Conf." );
        $this->SPE_tab_lista_spesa->cols->importo->setLabel( "Importo" );

        // Larghezze colonne
        $this->SPE_tab_lista_spesa->cols->desc_fornitore->setWidth(160);
        $this->SPE_tab_lista_spesa->cols->qta->setWidth(50);
//      $this->SPE_tab_lista_spesa->cols->descrizione->setWidth(160);  per differenza
        $this->SPE_tab_lista_spesa->cols->um_qta_um->setWidth( 50 );
        $this->SPE_tab_lista_spesa->cols->importo->setWidth(50);
        
        $this->SPE_tab_lista_spesa->cols->um_qta_um->setOrderable( false );

        
        // ------------------------ Oggetti sotto la tabella "LISTA DELLA SPESA"        

        // Campi informativi sull'articolo ordinato (compilati in SPE_tab_lista_spesa_afterClick)
        $this->build( "p4a_field", "SPE_fld_info_articolo" );
        $this->SPE_fld_info_articolo->disable();
        $this->SPE_fld_info_articolo->setLabel( "Articolo" );
        $this->SPE_fld_info_articolo->setWidth( 620 );
        $this->SPE_fld_info_articolo->label->setWidth( 120 );
        $this->SPE_fld_info_articolo->setFontWeight( "bold" );
        $this->SPE_fld_info_articolo->setFontColor( "black" );
        $this->SPE_fld_info_articolo->label->setFontColor( "black" );

        $this->build( "p4a_field", "SPE_fld_info_articolo_desc_agg" );
        $this->SPE_fld_info_articolo_desc_agg->setType( "textarea" );
        $this->SPE_fld_info_articolo_desc_agg->disable();
        $this->SPE_fld_info_articolo_desc_agg->setLabel( "" );
        $this->SPE_fld_info_articolo_desc_agg->setWidth( 620 );
        $this->SPE_fld_info_articolo_desc_agg->label->setWidth( $this->SPE_fld_info_articolo->label->getWidth() );
        $this->SPE_fld_info_articolo_desc_agg->setHeight( 30 );  // 2 righe
        $this->SPE_fld_info_articolo_desc_agg->setFontColor( "black" );

        $this->build( "p4a_field", "SPE_fld_info_pzxcart" );
        $this->SPE_fld_info_pzxcart->disable();
        $this->SPE_fld_info_pzxcart->setLabel( "Pezzi per cartone" );
        $this->SPE_fld_info_pzxcart->setWidth( 75 );
        $this->SPE_fld_info_pzxcart->label->setWidth( $this->SPE_fld_info_articolo->label->getWidth() );

        $this->build( "p4a_field", "SPE_fld_info_gestione_a_peso" );
        $this->SPE_fld_info_gestione_a_peso->disable();
        $this->SPE_fld_info_gestione_a_peso->setLabel( "A peso" );
        $this->SPE_fld_info_gestione_a_peso->setWidth( 75 );
        $this->SPE_fld_info_gestione_a_peso->label->setWidth( $this->SPE_fld_info_articolo->label->getWidth() );
        $this->SPE_fld_info_gestione_a_peso->setTooltip( "Articolo ordinabile a peso" );
        $this->SPE_fld_info_gestione_a_peso->setType( "checkbox" );

        $this->build( "p4a_field", "SPE_fld_info_ordinemin" );
        $this->SPE_fld_info_ordinemin->disable();
        $this->SPE_fld_info_ordinemin->setLabel( "Ordine minimo []" );
        $this->SPE_fld_info_ordinemin->setWidth( 90 );
        $this->SPE_fld_info_ordinemin->label->setWidth( 120 );

        $this->build( "p4a_field", "SPE_fld_info_ordinemulti" );
        $this->SPE_fld_info_ordinemulti->disable();
        $this->SPE_fld_info_ordinemulti->setLabel( "Ordine solo multiplo di []" );
        $this->SPE_fld_info_ordinemulti->setWidth( 90 );
        $this->SPE_fld_info_ordinemulti->label->setWidth( 170 );

        $this->build( "p4a_field", "SPE_fld_info_ordineute" );
        $this->SPE_fld_info_ordineute->disable();
        $this->SPE_fld_info_ordineute->setLabel( "Tuo ordine []" );
        $this->SPE_fld_info_ordineute->setWidth( 75 );
        $this->SPE_fld_info_ordineute->label->setWidth( $this->SPE_fld_info_articolo->label->getWidth() );
        $this->SPE_fld_info_ordineute->setFontColor( "black" );
        $this->SPE_fld_info_ordineute->label->setFontColor( "black" );

        $this->build( "p4a_field", "SPE_fld_info_ordinegas" );
        $this->SPE_fld_info_ordinegas->disable();
        $this->SPE_fld_info_ordinegas->setLabel( "Ordine globale []" );
        $this->SPE_fld_info_ordinegas->setWidth( 75 );
        $this->SPE_fld_info_ordinegas->label->setWidth( $this->SPE_fld_info_articolo->label->getWidth() );

        $this->build( "p4a_field", "SPE_fld_info_cartonigas" );
        $this->SPE_fld_info_cartonigas->disable();
        $this->SPE_fld_info_cartonigas->setLabel( "N. cartoni" );
        $this->SPE_fld_info_cartonigas->setWidth( 90 );
        $this->SPE_fld_info_cartonigas->label->setWidth( $this->SPE_fld_info_ordinemin->label->getWidth() );

        $this->build( "p4a_field", "SPE_fld_info_surplus" );
        $this->SPE_fld_info_surplus->disable();
        $this->SPE_fld_info_surplus->setLabel( "Surplus (da assegnare) []" );
        $this->SPE_fld_info_surplus->setWidth( 90 );
        $this->SPE_fld_info_surplus->label->setWidth( 150 );
        $this->SPE_fld_info_surplus->label->setWidth( $this->SPE_fld_info_ordinemulti->label->getWidth() );

        $this->build( "p4a_message", "SPE_msg_info" );
        $this->SPE_msg_info->setWidth( E3G_NARROW_TABLE_IN_TAB_PANE_WIDTH );
        $this->SPE_msg_info->setIcon( "warning" );
        $this->SPE_msg_info->autoClear( false );


        // ---------------- Pannello "Lista della Spesa": oggetti colonna destra
        
        // Campo DESCRIZIONE ARTICOLO       
        $this->build("p4a_label", "SPE_lbl_desc_articolo");
        $this->SPE_lbl_desc_articolo->setWidth( E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->SPE_lbl_desc_articolo->setFontColor( "black" );
        
        // Campo QUANTITA'
        $this->build("p4a_field", "SPE_fld_spesa_qta");
        $this->SPE_fld_spesa_qta->setLabel("Quantita'");
        $this->SPE_fld_spesa_qta->label->setWidth( E3G_LABEL_IN_TAB_PANE_WIDTH );
        $this->SPE_fld_spesa_qta->setWidth( E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->SPE_fld_spesa_qta->setFontColor( "black" );
        $this->SPE_fld_spesa_qta->label->setFontColor( "black" );
        
        // Bottone AGGIORNA Q.TA'
        $this->build( "p4a_button", "SPE_bu_aggiorna_qta" );
        $this->SPE_bu_aggiorna_qta->setLabel( "Aggiorna" );
        $this->SPE_bu_aggiorna_qta->setIcon( "reload" );
        $this->SPE_bu_aggiorna_qta->setWidth( 20 + E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->SPE_bu_aggiorna_qta->setSize( 16 );
        $this->SPE_bu_aggiorna_qta->addAction( "onClick" );
        $this->intercept( $this->SPE_bu_aggiorna_qta, "onClick", "SPE_bu_aggiorna_qtaClick" );

        // Bottone ELIMINA RIGA
        $this->build( "p4a_button", "SPE_bu_elimina_riga" );
        $this->SPE_bu_elimina_riga->setLabel( "Elimina riga" );
        $this->SPE_bu_elimina_riga->setIcon( "edit_remove" );
        $this->SPE_bu_elimina_riga->setWidth( 20 + E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->SPE_bu_elimina_riga->setSize( 16 );
        $this->SPE_bu_elimina_riga->addAction( "onClick" );
        $this->intercept( $this->SPE_bu_elimina_riga, "onClick", "SPE_bu_elimina_rigaClick" );

        // Bottone ELIMINA TUTTO
        $this->build( "p4a_button", "SPE_bu_svuota_ordine" );
        $this->SPE_bu_svuota_ordine->setLabel( "Elimina TUTTO..." );
        $this->SPE_bu_svuota_ordine->setIcon( "delete" );
        $this->SPE_bu_svuota_ordine->setWidth( 20 + E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->SPE_bu_svuota_ordine->setSize( 16 );
        $this->SPE_bu_svuota_ordine->addAction( "onClick" );
        $this->intercept( $this->SPE_bu_svuota_ordine, "onClick", "SPE_bu_svuota_ordineClick" );
        $this->SPE_bu_svuota_ordine->requireConfirmation( "onClick", "Confermi l'eliminazione di tutti gli articoli dalla lista?" );

        // Bottone ESPORTA ORDINE PDF
        $this->build( "p4a_button", "SPE_bu_esporta_ordinePdf" );
        $this->SPE_bu_esporta_ordinePdf->setLabel( "Esporta come PDF" );
        $this->SPE_bu_esporta_ordinePdf->setIcon( "pdf" );
        $this->SPE_bu_esporta_ordinePdf->setWidth( 20 + E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->SPE_bu_esporta_ordinePdf->setSize( 16 );
        $this->SPE_bu_esporta_ordinePdf->addAction( "onClick" );
        $this->intercept( $this->SPE_bu_esporta_ordinePdf, "onClick", "SPE_bu_esporta_ordinePdfClick" );

        // Bottone ESPORTA ORDINE CSV
        $this->build( "p4a_button", "SPE_bu_esporta_ordineCsv" );
        $this->SPE_bu_esporta_ordineCsv->setLabel( "Esporta come CSV" );
        $this->SPE_bu_esporta_ordineCsv->setIcon( "spreadsheet" );
        $this->SPE_bu_esporta_ordineCsv->setWidth( 20 + E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->SPE_bu_esporta_ordineCsv->setSize( 16 );
        $this->SPE_bu_esporta_ordineCsv->addAction( "onClick" );
        $this->intercept( $this->SPE_bu_esporta_ordineCsv, "onClick", "SPE_bu_esporta_ordineCsvClick" );

        // Bottone INVIA
        $this->build( "p4a_button", "SPE_bu_invia_email" );
        $this->SPE_bu_invia_email->setLabel( "Invia per e-mail" );
        $this->SPE_bu_invia_email->setIcon( "mail_send" );
        $this->SPE_bu_invia_email->setWidth( 20 + E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->SPE_bu_invia_email->setSize( 16 );
        $this->SPE_bu_invia_email->addAction( "onClick" );
        $this->SPE_bu_invia_email->requireConfirmation( "onClick", "Confermi l'invio per e-mail a te stesso della lista della spesa ?" );
        $this->intercept( $this->SPE_bu_invia_email, "onClick", "SPE_bu_invia_email_click" );


        // -------------------------------- ANCORAGGIO OGGETTI lista della spesa

        // LISTA DELLA SPESA: filtro
        $SPE_fs_filtro_spesa =& $this->build("p4a_fieldset", "SPE_fs_filtro_spesa");
        $SPE_fs_filtro_spesa->setTitle("Filtro");
        $SPE_fs_filtro_spesa->setWidth( E3G_TABLE_IN_TAB_PANE_WIDTH -15 );  
        $SPE_fs_filtro_spesa->anchor( $this->SPE_fld_filtro_fornitore );
        $SPE_fs_filtro_spesa->anchor( $this->SPE_fld_filtro_categoria );
        $SPE_fs_filtro_spesa->anchorRight( $this->SPE_bu_annulla_filtro );
        $SPE_fs_filtro_spesa->anchorRight( $this->SPE_bu_filtro );

        // LISTA DELLA SPESA: spalla 
        $SPE_sh_spalla_lista_spesa =& $this->build("p4a_sheet", "SPE_sh_spalla_lista_spesa");
        $SPE_sh_spalla_lista_spesa->defineGrid(9, 1);
        $SPE_sh_spalla_lista_spesa->anchor( $this->SPE_lbl_desc_articolo, 1,1); 
        $SPE_sh_spalla_lista_spesa->anchor( $this->SPE_fld_spesa_qta, 2,1);
        $SPE_sh_spalla_lista_spesa->anchor( $this->SPE_bu_aggiorna_qta, 3,1);
        $SPE_sh_spalla_lista_spesa->anchor( $this->SPE_bu_elimina_riga, 4,1);
        $SPE_sh_spalla_lista_spesa->anchor( $this->SPE_bu_svuota_ordine, 5,1);
        $SPE_sh_spalla_lista_spesa->anchorText("<br>", 6,1);
        $SPE_sh_spalla_lista_spesa->anchor( $this->SPE_bu_esporta_ordinePdf, 7,1);
        $SPE_sh_spalla_lista_spesa->anchor( $this->SPE_bu_esporta_ordineCsv, 8,1);
        $SPE_sh_spalla_lista_spesa->anchor( $this->SPE_bu_invia_email, 9,1);
        
        // Sheet LISTA DELLA SPESA
        $SPE_sh_lista_spesa =& $this->build( "p4a_sheet", "SPE_sh_lista_spesa" );
        $SPE_sh_lista_spesa->defineGrid( 1, 2 );
        $SPE_sh_lista_spesa->anchor( $this->SPE_tab_lista_spesa, 1, 1 );
        $SPE_sh_lista_spesa->anchor( $SPE_sh_spalla_lista_spesa, 1, 2 );
        

        // ------------------------------------------------------- Pannello NOTE

        // Campo Note
        $this->build( "p4a_field", "NOT_fld_note" );
        $this->NOT_fld_note->setType( "textarea" );
        $this->NOT_fld_note->setLabel( "" );
        $this->NOT_fld_note->setWidth( E3G_NARROW_TABLE_IN_TAB_PANE_WIDTH );  // era 635
        $this->NOT_fld_note->setHeight( 90 );  // 6 righe
        
        // Bottone SALVA NOTE
        $this->build( "p4a_button", "SPE_bu_salva_note" );
        $this->SPE_bu_salva_note->setLabel( "Salva Note" );
        $this->SPE_bu_salva_note->setIcon( "apply" );
        $this->SPE_bu_salva_note->setWidth( 20 + E3G_LABEL_IN_TAB_PANE_WIDTH + E3G_FIELD_IN_TAB_PANE_WIDTH );
        $this->SPE_bu_salva_note->setSize( 16 );
        $this->SPE_bu_salva_note->addAction( "onClick" );
        $this->intercept( $this->SPE_bu_salva_note, "onClick", "bu_salva_note_click" );
        

		// ------------------------------------------------- Pannello principale

		$this->build( "p4a_tab_pane", "tab_pane" );		 

//        $this->tab_pane->addAction( "OnClick" );  NON FUNZIONA
//        $this->intercept( $this->tab_pane, "OnClick", "tab_pane_tabClick" );

//        $this->tab_pane->addAction( "tabClick" );  NON FUNZIONA 
//        $this->intercept( $this->tab_pane, "tabClick", "tab_pane_tabClick" );

		$this->tab_pane->pages->build( "p4a_frame", "tabframe1" );
		$this->tab_pane->pages->build( "p4a_frame", "tabframe2" );
        $this->tab_pane->pages->build( "p4a_frame", "tabframe3" );

        $this->tab_pane->addAction( "OnClick" );
        $this->intercept( $this->tab_pane, "OnClick", "tab_pane_tabClick" );
		$this->tab_pane->setWidth( E3G_TAB_PANE_WIDTH ); 

		//$this->tab_pane->pages->tabframe1->setLabel( "Listino Articoli" ); Compilata in ART_bu_filtraClick()
        //$this->tab_pane->pages->tabframe2->setLabel( "Lista della Spesa (...)" );  Compilata in update_top_message()
        //$this->tab_pane->pages->tabframe3->setLabel( "Note all'ordine (...)" );  Compilata in update_label_note()

		$this->tab_pane->pages->tabframe1->anchor( $ART_fs_filtro_articoli );		 
        $this->tab_pane->pages->tabframe1->anchor( $ART_sh_listino );        
        $this->tab_pane->pages->tabframe1->anchor( $this->ART_fld_desc_articolo );        
        $this->tab_pane->pages->tabframe1->anchor( $this->fields->desc_agg, "130px" );        
        $this->tab_pane->pages->tabframe1->anchor( $this->fields->qtaminordine );        
        $this->tab_pane->pages->tabframe1->anchorLeft( $this->fields->gestione_a_peso );        
        $this->tab_pane->pages->tabframe1->anchorLeft( $this->fields->qtaminperfamiglia );        
        $this->tab_pane->pages->tabframe1->anchorLeft( $this->fields->pzperconf );        
        $this->tab_pane->pages->tabframe1->anchor( $this->fields->ingredienti, "130px" );        

        $this->tab_pane->pages->tabframe2->anchor( $SPE_fs_filtro_spesa );       
        $this->tab_pane->pages->tabframe2->anchor( $SPE_sh_lista_spesa );
        $this->tab_pane->pages->tabframe2->anchor( $this->SPE_fld_info_articolo );
        $this->tab_pane->pages->tabframe2->anchor( $this->SPE_fld_info_articolo_desc_agg, "150px" );
        $this->tab_pane->pages->tabframe2->anchor( $this->SPE_fld_info_pzxcart );
        $this->tab_pane->pages->tabframe2->anchorLeft( $this->SPE_fld_info_gestione_a_peso );
        $this->tab_pane->pages->tabframe2->anchorLeft( $this->SPE_fld_info_ordinemin );
        $this->tab_pane->pages->tabframe2->anchorLeft( $this->SPE_fld_info_ordinemulti );
        $this->tab_pane->pages->tabframe2->anchor( $this->SPE_fld_info_ordineute );
        $this->tab_pane->pages->tabframe2->anchor( $this->SPE_fld_info_ordinegas );
        $this->tab_pane->pages->tabframe2->anchorLeft( $this->SPE_fld_info_cartonigas );
        $this->tab_pane->pages->tabframe2->anchorLeft( $this->SPE_fld_info_surplus );
        $this->tab_pane->pages->tabframe2->anchor( $this->SPE_msg_info );
        
        $this->tab_pane->pages->tabframe3->anchor( $this->NOT_fld_note );        
        $this->tab_pane->pages->tabframe3->anchorRight( $this->SPE_bu_salva_note );        

		
        // ------------------------------------- Sheet per situazione e chiusura
        $this->build( "p4a_sheet", "sh_info" );
        $this->sh_info->defineGrid( 1, 2 );
        $this->sh_info->anchor( $this->lbl_situazione, 1, 1 );
        $this->sh_info->anchor( $this->bu_chiudi, 1, 2 );


        // -------------------------------------------------- Pannello dettaglio
        
        $this->build( "p4a_tab_pane", "tp_articolo" );        
        $this->tp_articolo->pages->build( "p4a_frame", "fr_art_det" );
        $this->tp_articolo->pages->build( "p4a_frame", "fr_art_for" );
        $this->tp_articolo->pages->build( "p4a_frame", "fr_art_com" );

        $this->tp_articolo->setWidth( E3G_TAB_PANE_WIDTH ); 

        $this->tp_articolo->pages->fr_art_det->setLabel( "Dettaglio articolo" );
        $this->tp_articolo->pages->fr_art_for->setLabel( "Fornitore" );
        $this->tp_articolo->pages->fr_art_com->setLabel( "Commenti (nessuno)" );

        $this->build( "p4a_field", "fld_temp" );
        $this->fld_temp->setLabel( "In costruzione..." );
/*  
        $this->ART_ds_articoli->fields->descrizione->setLabel( "Descrizione" );
        $this->ART_ds_articoli->fields->prezzoven->setLabel( "Prezzo unitario" );
        $this->ART_ds_articoli->fields->prezzo_ven_um->setLabel( "Prezzo per U.M." );
        $this->ART_ds_articoli->fields->fornitore->setLabel( "Fornitore" );
        $this->ART_ds_articoli->fields->www->setLabel( "Sito web" );

        $this->fields->cod_articolo->setLabel( "Codice" );
        $this->fields->descrizione->setLabel( "Descrizione" );
        $this->fields->prezzoven->setLabel( "Prezzo" );

        $this->fields->cod_articolo->setWidth( 80 );
        $this->fields->descrizione->setWidth( 300 );
        $this->fields->prezzoven->setWidth( 80 );
        
        $this->tp_articolo->pages->fr_art_det->anchor( $this->fields->cod_articolo );
        $this->tp_articolo->pages->fr_art_det->anchor( $this->fields->descrizione );
        $this->tp_articolo->pages->fr_art_det->anchor( $this->fields->prezzoven );
*/
        
        $this->tp_articolo->pages->fr_art_for->anchor( $this->fld_temp );
        $this->tp_articolo->pages->fr_art_com->anchor( $this->fld_temp );


		// ---------------------------------------------------- Frame principale
		$frm=& $this->build( "p4a_frame", "frm" );
		$frm->setWidth( E3G_MAIN_FRAME_WIDTH );
        $frm->anchor( $this->sh_info );
        $frm->anchor( $this->msg_info );
        $frm->anchor( $this->msg_warning );
		$frm->anchor( $this->tab_pane );
//        $frm->anchor( $this->tp_articolo );
	
		e3g_scrivi_footer( $this, $frm );

  		// Display
		$this->display( "main", $frm );
		$this->display( "menu", $p4a->menu );


        $this->ART_bu_annulla_filtroClick();
		$this->SPE_tab_lista_spesa_afterClick();
        $this->update_top_message();
	}


	// -------------------------------------------------------------------------
	function main()
	// -------------------------------------------------------------------------
	{
	    $p4a =& p4a::singleton();
	    $db =& p4a_db::singleton();
	
	    // Recupera il testo delle note dall'apposito campo nell'anagrafica
	    $this->NOT_fld_note->setNewValue( $db->queryOne( 
            "SELECT note_ordine FROM " . $p4a->e3g_prefix . "anagrafiche WHERE idanag = " . $p4a->e3g_utente_idanag ) );
        $this->update_label_note();
		
		parent::main();

        $this->SPE_tab_lista_spesa_afterClick();
		$this->update_top_message();
	}

    
	// -------------------------------------------------------------------------
	function bu_chiudiClick()
	// -------------------------------------------------------------------------
	{		
		$this->maskClose( "cassa_gg_singolo" );
		$this->showPrevMask();
	}

	
    // -------------------------------------------------------------------------
    function fld_categ_cerca_change()
    // -------------------------------------------------------------------------
    {
        $this->ds_filtro_sottocat_articoli->setWhere( "tipo = '" . $this->ART_fld_categ_cerca->getNewValue() . "' OR codice = '000'" );       
        $this->ds_filtro_sottocat_articoli->load();
    }

   
    // NON SI RIESCE A FAR RICHIAMARE QUEST'EVENTO 
    // -------------------------------------------------------------------------
    function tab_pane_tabClick()
    // -------------------------------------------------------------------------
/*     
        $this->build("p4a_tab_pane", "tab_pane");        
        $this->intercept( $this->tab_pane, "tabClick", "tab_pane_tabClick" );
        $this->tab_pane->pages->build("p4a_frame", "tabframe1");
        $this->tab_pane->pages->build("p4a_frame", "tabframe2");

        $this->tab_pane->pages->tabframe1->setLabel("Lista della Spesa");
        $this->tab_pane->pages->tabframe2->setLabel("Listino Articoli");
*/        
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
        sdasd;
//        echo "Pannello attivo: " . $this->tab_pane->getActivePage();
        $this->lbl_situazione->setValue( "Pannello attivo: " . $this->tab_pane->getActivePage() );
        $this->msg_info->setValue( "Pannello attivo: " . $this->tab_pane->getActivePage() );
        
        
//      $this->setSource($this->SPE_ds_lista_spesa);
//      $this->setSource($this->ART_ds_articoli);
    }
    
    
    // -------------------------------------------------------------------------
    function update_top_message()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
        
/*  1) ORDINE CHIUSO + NESSUN ARTICOLO NEL CARRELLO
        Nessun ordine è attualmente aperto.
        Il prossimo periodo si aprirà il:
        - elenco fornitori e date di apertura...
    
    2) ORDINE CHIUSO + ARTICOLI NEL CARRELLO 
        Il periodo d'ordine è concluso.
        La lista della spesa è in elaborazione da parte dei referenti: rimangono da elaborare ...
    
    3) ORDINE APERTO + NESSUN ARTICOLO NEL CARRELLO
        La tua lista della spesa è vuota.
        Scegli i prodotti nel listino articoli, specifica la quantità desiderata e poi premi il bottone "Aggiungi quantità".
        La lista della spesa verrà automaticamente elaborata dai referenti al termine del periodo d'ordine:
        - elenco fornitori e date di chiusura...

    4) ORDINE APERTO + ARTICOLI NEL CARRELLO
        Hai 88 articoli in ordine per un importo totale di 123,45 euro.
        [stesso testo in coda al caso 3)]
*/
        $n_ordini_aperti = $db->queryOne(
            "SELECT COUNT(*) FROM ".$p4a->e3g_prefix."fornitoreperiodo WHERE " . e3g_where_ordini_aperti() );

        $result = $db->queryRow( 
            "SELECT SUM( qta ) AS qta, SUM( prezzoven * qta ) AS importo " .
            "  FROM " . $p4a->e3g_prefix . "carrello WHERE codutente = '$p4a->e3g_utente_codice'" );
        $qta = (integer) $result[ "qta" ];
        $importo = (double) $result[ "importo" ];

        $testo_msg = "";
        $testo_lbl = "";
        if ( $n_ordini_aperti == 0 ) {
            if ( $qta == 0 )
                // 1) ORDINE CHIUSO + NESSUN ARTICOLO NEL CARRELLO
                $testo_lbl .= "<p>Nessun ordine e' attualmente aperto. Il prossimo periodo sara':</p>" .
                    e3g_get_html_elenco_prossime_aperture();
            else 
                // 2) ORDINE CHIUSO + ARTICOLI NEL CARRELLO 
                $testo_lbl .= "<p>Il periodo d'ordine e' concluso.</p>" .
                    "<p>La lista della spesa e' in elaborazione da parte dei referenti: riman" . ( $qta==1 ? "e" : "gono" ) . " da elaborare " .
                    "<strong>$qta articol" . ( $qta==1 ? "o" : "i" ) . "</strong> per un importo totale di <strong>" . $importo . " euro</strong>.</p>";
        }
        else {
            if ( $p4a->e3g_azienda_gestione_cassa ) {
                $saldo_utente = (double) $db->queryOne(
                    "SELECT SUM( importo ) FROM _cassa " .
                    " WHERE prefix = '" . $p4a->e3g_prefix . "' AND  validato = 1" . 
                    "   AND id_utente_rif = " . $p4a->e3g_utente_idanag );
                $testo_lbl .= 
                    "<p>Il tuo " . ( $saldo_utente>=0 ? "credito" : "debito" ) . " nella cassa comune risulta di <strong>$saldo_utente euro</strong>.</p>";
            }
            if ( $qta == 0 ) {
                // 3) ORDINE APERTO + NESSUN ARTICOLO NEL CARRELLO
                $testo_lbl .= "<p>La tua lista della spesa e' vuota.</p>";
                $this->tab_pane->pages->tabframe2->setLabel( "Lista della Spesa (vuota)" );
            }
            else {
                // 4) ORDINE APERTO + ARTICOLI NEL CARRELLO
                $testo_lbl .= "<p>Hai <strong>$qta articol" . ( $qta==1 ? "o" : "i" ) . 
                    "</strong> in ordine per un importo totale di <strong>" . $importo . " euro</strong>.</p>";
                $this->tab_pane->pages->tabframe2->setLabel( "Lista della Spesa ($qta articol" . ( $qta==1 ? "o" : "i" ) . " / $importo euro)" );
            }
        
            $testo_lbl .= "<p>Scegli i prodotti nel listino articoli, specifica la quantita' desiderata e poi premi il bottone \"Aggiungi\".<br />" .
                    "La lista della spesa verra' automaticamente elaborata dai referenti alla chiusura del periodo d'ordine:</p>" .
                e3g_get_html_elenco_prossime_chiusure();
    
            if ( $p4a->e3g_azienda_ordine_minimo > 0 and $importo < $p4a->e3g_azienda_ordine_minimo ) 
                $testo_msg .= "<p>L'ordine minimo e' di $p4a->e3g_azienda_ordine_minimo euro.</p>" ;
                        }

        $this->msg_info->setValue( $testo_msg );
        $this->lbl_situazione->setValue( $testo_lbl );
    }


    // -------------------------------------------------------------------------
    function ART_tab_listino_afterClick()
    // -------------------------------------------------------------------------
    {
        $db =& p4a_db::singleton();
        
        $this->ART_lbl_desc_articolo->setValue( $this->ART_ds_articoli->fields->descrizione->getNewValue() );
        $this->ART_fld_aggiungi_qta->setValue( $this->ART_ds_articoli->fields->qtaminperfamiglia->getNewValue() );

        // ATTENZIONE che dopo una ricerca l'elenco articoli potrebbe essere vuoto

        // In coda alla descrizione articolo aggiunge la dimensione della confezione 
        // (se articolo non a peso) e l'eventuale provenienza da agricoltura biologica
        if ( $this->fields->gestione_a_peso->getNewValue() ) 
            $this->ART_fld_desc_articolo->setValue( $this->fields->descrizione->getNewValue() .
                ( $this->fields->bio->getNewValue() ? " - da agricoltura biologica" : "" ) );        
        else 
            $this->ART_fld_desc_articolo->setValue( $this->fields->descrizione->getNewValue() .
                ( $this->fields->um_qta->getNewValue()<>"" ? " [" . $this->fields->um_qta->getNewValue() . " " . $this->fields->um->getNewValue() . "]" : "" ) .
                ( $this->fields->bio->getNewValue() ? " - da agricoltura biologica" : "" ) );        
            
        // Adegua le etichette dei campi dettaglio, specie nel caso di articoli gestiti a peso    
        if ( $this->fields->gestione_a_peso->getNewValue() ) {
            $this->ART_fld_aggiungi_qta->setLabel( $this->fields->genere_um->getNewValue() . " [" . $this->fields->um->getNewValue() . "]" );

            $this->fields->qtaminordine->setInvisible();
            $this->fields->gestione_a_peso->setVisible();
            $this->fields->qtaminperfamiglia->setLabel( "Ordine minimo [" . $this->fields->um->getNewValue() . "]" );
            $this->fields->pzperconf->setLabel( "Ordine solo multiplo di [" . $this->fields->um->getNewValue() . "]" );
        }
        else {
            $this->ART_fld_aggiungi_qta->setLabel( "Quantita' [pz]" );

            $this->fields->qtaminordine->setVisible();
            $this->fields->gestione_a_peso->setInvisible();
            $this->fields->qtaminperfamiglia->setLabel( "Ordine minimo [pz]" );
            $this->fields->pzperconf->setLabel( "Ordine solo multiplo di [pz]" );
        }
    }


    // -------------------------------------------------------------------------
    function ART_tab_listino_beforeDisplay( $obj, $rows ) 
    // -------------------------------------------------------------------------
    {  
        // array("idarticolo", "centrale", "gestione_a_peso", "bio", "descrizione", "um_qta_um", "um_qta", "prezzoven", "prezzo_ven_um", "qtaminperfamiglia"));
        for( $i=0; $i<count($rows); $i++ ) {  
            // Evidenzia la riga selezionata 
            if ( $rows[$i]["idarticolo"] == $this->ART_ds_articoli->fields->idarticolo->getNewValue() )
                $rows[$i]["descrizione"] = "<span style='color:black;font-weight:bold;'>" . $rows[$i]["descrizione"] . "</span>";

            // Non mostra niente nella "Conf." se non è stata impostata l'unità di misura
            if ( $rows[$i]["um_qta"] == "" )
                $rows[$i]["um_qta_um"] = "";
                
            // Per gli articoli da gestire a peso, non visualizza "Conf." e "Prezzo" (per quest'ultimo vale quello per unità di misura)
            if ( $rows[$i]["gestione_a_peso"] ) {
                $rows[$i]["um_qta_um"] = "sfuso";
                $rows[$i]["prezzoven"] = "";  // In realtà non si riesce a far sparire, ma rimane uno "0"
            }  
            
            // Colonna "Articoli da agricoltura biologica"
            $rows[$i]["bio"] = ( $rows[$i]["bio"] ? "Bio" : "" );
        }  
        return $rows;  
    }  


    // -------------------------------------------------------------------------
    function ART_bu_filtraClick() 
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        $str_where = e3g_where_ordini_aperti("fp") . " AND ( a.stato = 1 ) ";
                
        if ($this->ART_fld_forn_cerca->getNewValue() != "00" )
            $str_where .= " AND a.centrale = '".$this->ART_fld_forn_cerca->getNewValue()."' ";
    
        if ( $this->ART_ck_solo_bio->getNewValue() != 0 )
            $str_where .= " AND bio = 1";
        
        if ($this->ART_fld_categ_cerca->getNewValue() != "00" )
            $str_where .= " AND a.tipo='".$this->ART_fld_categ_cerca->getNewValue()."' ";

        if ($this->ART_fld_sottocateg_cerca->getNewValue() != "000" )
            $str_where .= " AND a.catmerce='".$this->ART_fld_sottocateg_cerca->getNewValue()."' ";
        
        if ($this->ART_fld_desc_cerca->getNewValue() != "" )
            $str_where .= " AND UCASE(a.descrizione) LIKE '%" . addslashes( strtoupper(trim($this->ART_fld_desc_cerca->getNewValue())) ) . "%' ";
        
        // Filtro ingredienti (esclude articoli con ingredienti elencati; con questa query 
        // vengono giustamente esclusi anche quegli articoli che non hanno la specifica degli ingredienti)
        if ( $p4a->e3g_utente_filtro_ingredienti and ( $this->ART_fld_ingredienti->getNewValue() <> "" )  ) {
            $where_ing = "";
            $this->ART_fld_ingredienti->setNewValue( ucfirst(strtolower(trim( $this->ART_fld_ingredienti->getNewValue() ))) );
            $ingredienti = explode( ",", $this->ART_fld_ingredienti->getNewValue() );
            foreach( $ingredienti as $ingrediente ) 
                $where_ing .= " AND NOT FIND_IN_SET( '" . addslashes( strtoupper(trim($ingrediente)) ) . "', REPLACE( UCASE(ingredienti), ' ', '' ) ) ";
            $str_where .= $where_ing;
        }

        
        $this->ART_ds_articoli->setWhere( $str_where );
        $this->ART_ds_articoli->load();
        $this->ART_ds_articoli->firstRow();
        
        // Listino (nessun articolo)
        // Listino (123 articoli)
        if ( $this->ART_ds_articoli->getNumRows() == 0 )
            $this->tab_pane->pages->tabframe1->setLabel( "Listino (nessun articolo)" );
        else
            $this->tab_pane->pages->tabframe1->setLabel( "Listino (" . $this->ART_ds_articoli->getNumRows() . " articol" . ( $this->ART_ds_articoli->getNumRows()==1 ? "o)" : "i)" ) );

        $this->ART_tab_listino_afterClick();
    }


    // -------------------------------------------------------------------------
    function ART_bu_annulla_filtroClick()
    // -------------------------------------------------------------------------
    {       
        $p4a =& p4a::singleton();

        $this->ART_fld_forn_cerca->setNewValue( "00" );
        $this->ART_ck_solo_bio->setNewValue( 0 );
        $this->ART_fld_categ_cerca->setNewValue( "00" );
        $this->ART_fld_sottocateg_cerca->setNewValue( "000" );
        $this->ART_fld_desc_cerca->setNewValue( "" );       
        $this->ART_fld_ingredienti->setNewValue( "" );       
        
        $this->ART_bu_filtraClick();
    }

    
    // -------------------------------------------------------------------------
    function ART_bu_aggiungi_qtaClick()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        $cod_articolo = $this->ART_ds_articoli->fields->codice->getNewValue();
        $fornitore = $this->ART_ds_articoli->fields->centrale->getNewValue();

        // Se è attiva la gestione cassa e si vuol impedire di ordinare a quegli utenti che non hanno credito sufficiente...        
        if ( $p4a->e3g_azienda_gestione_cassa and !$p4a->e3g_azienda_acquista_se_credito_insufficiente ) {
            $saldo_utente = (double) $db->queryOne(
                "SELECT SUM( importo ) FROM _cassa " .
                " WHERE prefix = '" . $p4a->e3g_prefix . "' AND  validato = 1" . 
                "   AND id_utente_rif = " . $p4a->e3g_utente_idanag );
            
            $importo_ordine = (double) $db->queryOne(
                "SELECT SUM( prezzoven * qta ) " .
                " FROM " . $p4a->e3g_prefix . "carrello WHERE codutente = '$p4a->e3g_utente_codice'" );
            $importo_ordine += $this->ART_ds_articoli->fields->prezzoven->getNewValue()*$this->ART_fld_aggiungi_qta->getNewValue();
                
            if ( $saldo_utente < $importo_ordine ) {
                $testo_errore = "Credito insufficiente: impossibile inserire altri articoli nella lista della spesa.";
                $this->msg_warning->setValue( $testo_errore );
                return;
            }
        }
        
        $periodo_ordine_aperto = $db->queryOne(
            "SELECT COUNT(*) " .
            "  FROM " . $p4a->e3g_prefix . "fornitoreperiodo " .
            " WHERE ( fornitore = '" . $fornitore . "' OR fornitore = 'ALL' ) " .
            "   AND " . e3g_where_ordini_aperti() );

        $stagione = $db->queryOne(
            "SELECT COUNT(*) " .
            "  FROM " . $p4a->e3g_prefix . "articoloperiodo " .
            " WHERE codice = '" . $cod_articolo . "' " .
            "   AND ( (dalmese <= MONTH(NOW()) AND almese >= MONTH(NOW()) ) OR " .
            "         (dalmese > almese AND (MONTH(NOW()) >= dalmese OR MONTH(NOW()) <= almese)) )" );
        // AP: modificato il 31.01.09 per consentire la selezione di articoli disponibili in periodo a cavallo tra due anni
        // (in questo caso il campo dalmese è sempre maggiore del campo almese)
        
        $articolo_stagionale = $db->queryOne(
            "SELECT COUNT(*) " .
            "  FROM " . $p4a->e3g_prefix . "articoloperiodo " .
            "  WHERE codice='$cod_articolo' ");
            
        $pzperconf = $this->ART_ds_articoli->fields->pzperconf->getNewValue();
        if ( $pzperconf <> 0 ) {
// TODO Anche qui, come per la qtamin più sotto, bisognerebbe considerare se c'è già qualche pezzo in ordine...     
            if ( fmod($this->ART_fld_aggiungi_qta->getNewValue(), $pzperconf) == 0 ) 
                // Confez. OK 
                $pzperconf = 0;
            else 
                // Conf. Errata
                $pzperconf = $pzperconf * -1; 
        }


        // Controllo la qta Minima impostata 
        $qtamin = $this->ART_ds_articoli->fields->qtaminperfamiglia->getNewValue();        
        if ( $qtamin <> 0 ) {
            // C'è già qualche pezzo in ordine?
            $qta_gia_presente = $db->queryOne( 
                "SELECT qta FROM " . $p4a->e3g_prefix . "carrello " .
                " WHERE codutente = '" . $p4a->e3g_utente_codice . "' AND codarticolo = '" . $cod_articolo . "'" );

            if ( !is_numeric($qta_gia_presente) ) {
                if ( $this->ART_fld_aggiungi_qta->getNewValue() >= $qtamin ) 
                    // Confez. OK 
                    $qtamin = 0;
            }
            else {
                if ( ( $this->ART_fld_aggiungi_qta->getNewValue() + $qta_gia_presente ) >= $qtamin ) 
                    // Confez. OK 
                    $qtamin = 0;
            }
        }
        
        if ( $cod_articolo != '' && $periodo_ordine_aperto > 0 && $qtamin == 0 && $pzperconf == 0 && 
             ( $articolo_stagionale == 0 || ($articolo_stagionale > 0 && $stagione > 0) ) )
        {
            $rigaid = $db->queryOne(
                "SELECT idriga FROM " . $p4a->e3g_prefix . "carrello " .
                " WHERE codutente = '" . $p4a->e3g_utente_codice . "' AND codarticolo = '" . $cod_articolo . "'" );
                
            if ( is_numeric($rigaid) ) {
                // ho un ID RIGA valido quindi ho già righe per questo utente
                $db->query(
                    "UPDATE " . $p4a->e3g_prefix . "carrello " .
                    "   SET qta = qta + ". $this->ART_fld_aggiungi_qta->getNewValue() . 
                    " WHERE idriga = " . $rigaid );

                // Se nella pagina "Lista della Spesa" è selezionato lo stesso articolo, serve la
                // seguente istruzione altrimenti in $this->SPE_ds_lista_spesa->fields->qta rimane il valore non aggiornato
                $this->SPE_ds_lista_spesa->fields->qta->setValue( $this->SPE_ds_lista_spesa->fields->qta->getNewValue() + $this->ART_fld_aggiungi_qta->getNewValue() );
            }
            else {
                $new_idriga = $db->queryOne( "SELECT MAX( idriga ) FROM " . $p4a->e3g_prefix . "carrello" );
                if ( is_numeric ($new_idriga) )
                    $new_idriga++;
                else
                    $new_idriga = 1;
                
                // non ho nessuna riga per questo utente
                $db->query(
                    "INSERT INTO " . $p4a->e3g_prefix . "carrello " .
                    " ( idriga, codarticolo, um, descrizione, prezzoven, qta, codiva, idsessione, " .
                    "   codutente, stato, codfornitore, carscar, codcaumov, data ) " .
                    "VALUES ( " .
                        $new_idriga . ", '" .
                        $cod_articolo . "', '" . 
                        $this->ART_ds_articoli->fields->um->getNewValue() . "','" . 
                        addslashes( $this->ART_ds_articoli->fields->descrizione->getNewValue() ) . "'," .
                        $this->ART_ds_articoli->fields->prezzoven->getNewValue() . "," .
                        $this->ART_fld_aggiungi_qta->getNewValue() . ",'" .
                        $this->ART_ds_articoli->fields->codiva->getNewValue() . "','" . 
                        session_id() . "','" .
                        $p4a->e3g_utente_codice . "', 'A', '" .
                        $this->ART_ds_articoli->fields->centrale->getNewValue() . "', 'S', '".
                        $db->queryOne( "SELECT dettaglio_causale_mov_mag FROM _aziende WHERE prefix = '$p4a->e3g_prefix'" ). "', " .
                        "CURDATE() ) " );
            }
        
            $this->ART_tab_listino_afterClick();
            $this->SPE_tab_lista_spesa_afterClick();
            $this->update_top_message();
        }
        else {
            $testo_errore = $this->ART_ds_articoli->fields->descrizione->getNewValue() . ": ";
            
            if ( $periodo_ordine_aperto == 0 )
                $testo_errore .= "<br />- ordine NON CONSENTITO in questo periodo";

            if ( $qtamin != 0 )
                $testo_errore .= "<br />- la quantita' MINIMA e' di " . $qtamin . " pezzi";

            if ( $pzperconf != 0 )
                $testo_errore .= "<br />- solo ordini multipli di " . ($pzperconf * -1) . " pezzi";
            
            if ( $articolo_stagionale > 0 && $stagione == 0 )
                $testo_errore .= "<br />- prodotto FUORI STAGIONE.";

            $this->msg_warning->setValue( $testo_errore );
        }
    }


    // -------------------------------------------------------------------------
    function ART_bu_esporta_listinoPdfClick()
    // -------------------------------------------------------------------------
    {       
        $p4a =& p4a::singleton();
        
        $p4a->openMask('esporta_listino');
    }


    // -------------------------------------------------------------------------
    function ART_bu_esporta_listinoCsvClick ()
    // -------------------------------------------------------------------------
    {       
        $p4a =& p4a::singleton();
        
        $p4a->openMask('esporta_listino');
    }
    

    // -------------------------------------------------------------------------
    function SPE_tab_lista_spesa_afterClick()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
        
        //-------------------------- Campi alla destra della tabella - ETICHETTE
        if ( $this->SPE_ds_lista_spesa->fields->gestione_a_peso->getNewValue() ) {
            $this->SPE_fld_spesa_qta->setLabel( $this->SPE_ds_lista_spesa->fields->genere_um->getNewValue() . " [" . $this->SPE_ds_lista_spesa->fields->um->getNewValue() . "]" );
        }
        else {
            $this->SPE_fld_spesa_qta->setLabel( "Quantita' [pz]" );
        }

        //----------------------------- Campi alla destra della tabella - VALORI
        $this->SPE_lbl_desc_articolo->setValue( $this->SPE_ds_lista_spesa->fields->descrizione->getNewValue() );
        $this->SPE_fld_spesa_qta->setValue( $this->SPE_ds_lista_spesa->fields->qta->getNewValue() );
        
        
        //------------------------- Campi dettaglio sotto la tabella - ETICHETTE
        if ( $this->SPE_ds_lista_spesa->fields->gestione_a_peso->getNewValue() ) {
            $this->SPE_fld_info_pzxcart->setInvisible();
            $this->SPE_fld_info_gestione_a_peso->setVisible();
            $this->SPE_fld_info_ordinemin->setLabel( "Ordine minimo [" . $this->SPE_ds_lista_spesa->fields->um->getNewValue() . "]" );
            $this->SPE_fld_info_ordinemulti->setLabel( "Ordine solo multiplo di [" . $this->SPE_ds_lista_spesa->fields->um->getNewValue() . "]" );
            $this->SPE_fld_info_ordineute->setLabel( "Tuo ordine [" . $this->SPE_ds_lista_spesa->fields->um->getNewValue() . "]" );
        }
        else {
            $this->SPE_fld_info_pzxcart->setVisible();
            $this->SPE_fld_info_gestione_a_peso->setInvisible();
            $this->SPE_fld_info_ordinemin->setLabel( "Ordine minimo [pz]" );
            $this->SPE_fld_info_ordinemulti->setLabel( "Ordine solo multiplo di [pz]" );
            $this->SPE_fld_info_ordineute->setLabel( "Tuo ordine [pz]" );
        }


        //---------------------------- Campi dettaglio sotto la tabella - VALORI
        // Valori
        // Campi informativi sull'articolo ordinato (posizionati sotto la lista della spesa)

        /* Articolo:          Farina di Manitoba [1 Kg] - da agricoltura biologica      SPE_fld_info_articolo
         *                    Descrizione aggiuntiva dell'articolo                      SPE_fld_info_articolo_desc_agg   
         * Pezzi per cartone: 12 | Ordine minimo: 6 pezzi | Ordine multiplo di: 3 pezzi SPE_fld_info_pzxcart SPE_fld_info_ordinemin SPE_fld_info_ordinemulti    
         * Tuo ordine:        9 pezzi                                                   SPE_fld_info_ordineute 
         * Ordine globale:    9 pezzi | N. cartoni: 1 | Surplus (da assegnare): 3 pezzi SPE_fld_info_ordinegas SPE_fld_info_cartonigas SPE_fld_info_surplus 
         * 
         * Se non verrà raggiunto il quatitativo minimo per formare un cartone, allora il tuo ordine potrebbe venire annullato.
         * Se i pezzi totali non saranno multipli di quelli contenuti in un cartone, allora il tuo ordine potrebbe subire una modifica in più o in meno.
         */
        if ( $this->SPE_ds_lista_spesa->fields->gestione_a_peso->getNewValue() ) 
            $this->SPE_fld_info_articolo->setValue( $this->SPE_ds_lista_spesa->fields->descrizione->getNewValue() .
                ( $this->SPE_ds_lista_spesa->fields->bio->getNewValue() ? " - da agricoltura biologica" : "" ) );
        else
            $this->SPE_fld_info_articolo->setValue( $this->SPE_ds_lista_spesa->fields->descrizione->getNewValue() .
                ( $this->SPE_ds_lista_spesa->fields->um_qta->getNewValue()<>"" ? " [" . $this->SPE_ds_lista_spesa->fields->um_qta->getNewValue() . " " . $this->SPE_ds_lista_spesa->fields->um->getNewValue() . "]" : "" ) .
                ( $this->SPE_ds_lista_spesa->fields->bio->getNewValue() ? " - da agricoltura biologica" : "" ) );

        $this->SPE_fld_info_articolo_desc_agg->setValue( $this->SPE_ds_lista_spesa->fields->desc_agg->getNewValue() );

        $this->SPE_fld_info_pzxcart->setValue( $this->SPE_ds_lista_spesa->fields->qtaminordine->getNewValue() );
        $this->SPE_fld_info_gestione_a_peso->setValue( $this->SPE_ds_lista_spesa->fields->gestione_a_peso->getNewValue() );
        $this->SPE_fld_info_ordinemin->setValue( $this->SPE_ds_lista_spesa->fields->qtaminperfamiglia->getNewValue() ); 
        $this->SPE_fld_info_ordinemulti->setValue( $this->SPE_ds_lista_spesa->fields->pzperconf->getNewValue() ); 
        
        $this->SPE_fld_info_ordineute->setValue( $this->SPE_ds_lista_spesa->fields->qta->getNewValue() );
        
        
        //------------------------------------ Recupero dati sull'ordine globale
        $ordine_globale = $db->queryRow( 
            "SELECT SUM( c.qta+c.qta_agg ) AS qta, " .
            "       CEILING( SUM( c.qta+c.qta_agg ) / a.qtaminordine ) AS cartoni, " .
            "       ( CEILING(SUM(c.qta+c.qta_agg) / a.qtaminordine) * a.qtaminordine ) - SUM(c.qta+c.qta_agg) AS surplus " .
            "  FROM " . $p4a->e3g_prefix . "carrello AS c JOIN " . $p4a->e3g_prefix . "articoli AS a ON c.codarticolo = a.codice " .
            " WHERE c.codarticolo = '" . $this->SPE_ds_lista_spesa->fields->cod_articolo->getNewValue() . "' " );


        //-------------------------------------- Dati ordine globale - ETICHETTE
        if ( $this->SPE_ds_lista_spesa->fields->gestione_a_peso->getNewValue() ) {
            $this->SPE_fld_info_cartonigas->setInvisible();
            $this->SPE_fld_info_surplus->setInvisible();

            $this->SPE_fld_info_ordinegas->setLabel( "Ordine globale [" . $this->SPE_ds_lista_spesa->fields->um->getNewValue() . "]" );
        }
        else {
            $this->SPE_fld_info_cartonigas->setVisible();
            $this->SPE_fld_info_surplus->setVisible();

            $this->SPE_fld_info_ordinegas->setLabel( "Ordine globale [pz]" );
            $this->SPE_fld_info_surplus->setLabel( "Surplus (da assegnare) [pz]" );
        }


        //----------------------------------------- Dati ordine globale - VALORI
        $this->SPE_fld_info_ordinegas->setValue( $ordine_globale["qta"] );
        $this->SPE_fld_info_cartonigas->setValue( $ordine_globale["cartoni"] );
        $this->SPE_fld_info_surplus->setValue( $ordine_globale["surplus"] );


        // Avvisi di cartone incompleto (solo per articoli non a peso)
        if ( !$this->SPE_ds_lista_spesa->fields->gestione_a_peso->getNewValue() ) {
            if ( $ordine_globale["surplus"] <> 0 ) {
                $this->SPE_fld_info_surplus->label->setFontColor( "red" );
                $this->SPE_fld_info_surplus->setStyleProperty( "border", "1px solid red" );
                
                if ( $ordine_globale["qta"] < $this->SPE_ds_lista_spesa->fields->qtaminordine->getNewValue() )        
                    $this->SPE_msg_info->setValue( "Se non verra' raggiunto il quatitativo minimo per formare un cartone (" .
                     $this->SPE_ds_lista_spesa->fields->qtaminordine->getNewValue() . " pezzi), allora il tuo ordine potrebbe essere annullato." );
                else
                    $this->SPE_msg_info->setValue( 
                        "Se i pezzi in ordine globalmente (attualmente " .  $ordine_globale["qta"] . ") non saranno multipli di quelli contenuti in un cartone (" .
                        $this->SPE_ds_lista_spesa->fields->qtaminordine->getNewValue() . "), allora il tuo ordine potrebbe subire una modifica in piu' o in meno." );
            }
            else {
                $this->SPE_fld_info_surplus->label->unsetStyleProperty( "color" );
                $this->SPE_fld_info_surplus->unsetStyleProperty( "border" );
                
                $this->SPE_msg_info->setValue( "" );
            }
        }
    }

        
    // -------------------------------------------------------------------------
    function SPE_tab_lista_spesa_beforeDisplay( $obj, $rows ) 
    // -------------------------------------------------------------------------
    {  
        // array("idriga", "gestione_a_peso", "desc_fornitore", "qta", "descrizione", "um_qta_um", "um_qta", "importo") );
        for( $i=0; $i<count($rows); $i++ ) {  
            // Evidenzia la riga selezionata 
            if ( $rows[$i]["idriga"] == $this->SPE_ds_lista_spesa->fields->idriga->getNewValue() )
                $rows[$i]["descrizione"] = "<span style='color:black;font-weight:bold;'>" . $rows[$i]["descrizione"] . "</span>";

            // Non mostra niente nella "Conf." se non è stata impostata l'unità di misura
            if ( $rows[$i]["um_qta"] == "" )
                $rows[$i]["um_qta_um"] = "";

            // Per gli articoli da gestire a peso, non visualizza "Conf." e "Prezzo" (per quest'ultimo vale quello per unità di misura)
            if ( $rows[$i]["gestione_a_peso"] ) {
                $rows[$i]["um_qta_um"] = "sfuso";
            }  

            // Colonna "Articoli da agricoltura biologica"
            $rows[$i]["bio"] = ( $rows[$i]["bio"] == 1 ? "Bio" : "" );
        }  
        return $rows;  
    }  

    // -------------------------------------------------------------------------
    function SPE_bu_filtroClick() 
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        $strWhere = "c.codutente = '$p4a->e3g_utente_codice' ";
                
        if ( $this->SPE_fld_filtro_fornitore->getNewValue() != "00" )
            $strWhere .= " AND c.codfornitore = '".$this->SPE_fld_filtro_fornitore->getNewValue()."' ";
    
        if ( $this->SPE_fld_filtro_categoria->getNewValue() != "00" )
            $strWhere .= " AND art.tipo = '".$this->SPE_fld_filtro_categoria->getNewValue()."' ";
        
        $this->SPE_ds_lista_spesa->setWhere( $strWhere );
        $this->SPE_ds_lista_spesa->load();
        $this->SPE_ds_lista_spesa->firstRow();

        $this->SPE_tab_lista_spesa_afterClick();
    }


    // -------------------------------------------------------------------------
    function SPE_bu_annulla_filtroClick()
    // -------------------------------------------------------------------------
    {       
        $p4a =& p4a::singleton();

        $this->SPE_fld_filtro_fornitore->setNewValue( "00" );
        $this->SPE_fld_filtro_categoria->setNewValue( "00" );
        
        $this->SPE_bu_filtroClick();
    }

    
	// -------------------------------------------------------------------------
	function SPE_bu_aggiorna_qtaClick()
	// -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
        $cod_articolo = $this->SPE_ds_lista_spesa->fields->cod_articolo->getNewValue();
    	$fornitore = $this->SPE_ds_lista_spesa->fields->codfornitore->getNewValue();

        // Se è attiva la gestione cassa e si vuol impedire di ordinare a quegli utenti che non hanno credito sufficiente...        
        if ( $p4a->e3g_azienda_gestione_cassa and !$p4a->e3g_azienda_acquista_se_credito_insufficiente ) {
            $saldo_utente = (double) $db->queryOne(
                "SELECT SUM( importo ) FROM _cassa " .
                " WHERE prefix = '" . $p4a->e3g_prefix . "' AND  validato = 1" . 
                "   AND id_utente_rif = " . $p4a->e3g_utente_idanag );
            
            $importo_ordine = (double) $db->queryOne(
                "SELECT SUM( prezzoven * qta ) " .
                " FROM " . $p4a->e3g_prefix . "carrello WHERE codutente = '$p4a->e3g_utente_codice'" );
            $importo_ordine += ($this->SPE_fld_spesa_qta->getNewValue()-$this->SPE_fld_spesa_qta->getValue())*$this->SPE_ds_lista_spesa->fields->prezzoven->getNewValue();
            
            if ( $saldo_utente < $importo_ordine ) {
                $testo_errore = "Credito insufficiente: impossibile inserire altri articoli nella lista della spesa.";
                $this->msg_warning->setValue( $testo_errore );
                return;
            }
        }
        
		$periodo_ordine_aperto = $db->queryOne(
			"SELECT COUNT(*) " .
			"  FROM " . $p4a->e3g_prefix . "fornitoreperiodo " .
			" WHERE ( fornitore='".$fornitore."' OR fornitore='ALL' ) " .
			"   AND " . e3g_where_ordini_aperti() );
		
		$stagione = $db->queryOne(
        	"SELECT COUNT(*) " .
        	"  FROM " . $p4a->e3g_prefix . "articoloperiodo " .
        	" WHERE codice='".$cod_articolo."' AND ((dalmese <= MONTH(NOW()) AND almese >= MONTH(NOW())) OR " .
              " (dalmese > almese AND (MONTH(NOW()) >= dalmese OR MONTH(NOW()) <= almese)))");
		// AP: modificato il 31.01.09 per consentire la selezione di articoli disponibili in periodo a cavallo tra due anni
		// (in questo caso il campo dalmese è sempre maggiore del campo almese)
		
		$articolostagionale = $db->queryOne(
			"SELECT COUNT(*) " .
			"  FROM " . $p4a->e3g_prefix . "articoloperiodo " .
			"  WHERE codice='$cod_articolo' ");
			
        $pzperconf = $this->SPE_ds_lista_spesa->fields->pzperconf->getNewValue();
		if ( $pzperconf <> 0 ) {
			if ( fmod($this->SPE_fld_spesa_qta->getNewValue(), $pzperconf) == 0 ) 
				// Confez. OK 
				$pzperconf = 0;
            else 
            	// Conf. Errata
				$pzperconf = $pzperconf * -1; 
		}

		// Controllo la qta Minima impostata 
		$qtamin = $this->SPE_ds_lista_spesa->fields->qtaminperfamiglia->getNewValue();
		if ( $qtamin <> 0 ) {
			if ( $this->SPE_fld_spesa_qta->getNewValue() >= $qtamin ) 
				// Confez. OK 
				$qtamin = 0;
		}
		
		if ( $cod_articolo != '' && $periodo_ordine_aperto > 0 && $qtamin == 0 && $pzperconf == 0 && 
		     ( $articolostagionale == 0 || ($articolostagionale > 0 && $stagione > 0) ) ) {
			$db->query(
				"UPDATE " . $p4a->e3g_prefix . "carrello " .
				"   SET qta = " . $this->SPE_fld_spesa_qta->getNewValue() .
				" WHERE idriga = " . $this->SPE_ds_lista_spesa->fields->idriga->getNewValue() );
            
            // Seguente istruzione da fare altrimenti in $this->SPE_ds_lista_spesa->fields->qta rimane il valore non aggiornato
            $this->SPE_ds_lista_spesa->fields->qta->setValue( $this->SPE_fld_spesa_qta->getNewValue() );
            
            $this->SPE_tab_lista_spesa_afterClick();
            $this->update_top_message();
		}
        else {
			$testo_errore = $this->SPE_ds_lista_spesa->fields->descrizione->getNewValue() . ":";
        	
        	if ( $periodo_ordine_aperto == 0 )
    			$testo_errore .= "<br />- ordine NON CONSENTITO in questo periodo";

        	if ( $qtamin != 0 )
    			$testo_errore .= "<br />- la quantita' MINIMA e' di " . $qtamin . " pezzi";

			if ( $pzperconf != 0 )
            	$testo_errore .= "<br />- solo ordini multipli di " . ($pzperconf * -1) . " pezzi";
	    	
	    	if ( $articolostagionale >0 && $stagione == 0 )
    			$testo_errore .= "<br />- prodotto FUORI STAGIONE";
		
			$this->msg_warning->setValue( $testo_errore );
		}
	}
	
	
    // -------------------------------------------------------------------------
    function SPE_bu_elimina_rigaClick()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        // Prima di eliminare, controlla che l'articolo sia attualmente ordinabile
        // (altrimenti significa che l'ordine è già stato preso in carico dal referente)        
        $periodo_ordine_aperto = $db->queryOne(
            "SELECT COUNT(*) " .
            "  FROM " . $p4a->e3g_prefix . "fornitoreperiodo " .
            " WHERE ( fornitore = '" . $this->SPE_ds_lista_spesa->fields->codfornitore->getNewValue() . "' OR fornitore = 'ALL' ) " .
            "   AND " . e3g_where_ordini_aperti() );
        
        if ( $periodo_ordine_aperto ) {
            $db->query(
                "DELETE FROM " . $p4a->e3g_prefix . "carrello " .
                " WHERE idriga = " . $this->SPE_ds_lista_spesa->fields->idriga->getNewValue() );
            
            $this->SPE_tab_lista_spesa_afterClick();
            $this->update_top_message();
        }
        else {
            $testo_errore = 
                "Fornitore " . $this->SPE_ds_lista_spesa->fields->desc_fornitore->getNewValue() . ":<br />" .
                "- ordine NON CONSENTITO e quindi non modificabile in questo periodo";

            $this->msg_warning->setValue( $testo_errore );
        }
    }
    

    // -------------------------------------------------------------------------
    function SPE_bu_svuota_ordineClick()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
    
        // Elimina tutto ciò che è presente nella lista della spesa tranne
        // gli articoli di fornitori per i quali l'ordine è chiuso (e quindi già preso in carico dai referenti)
        $db->query(
            "DELETE FROM " . $p4a->e3g_prefix . "carrello " .
            " WHERE codutente = '$p4a->e3g_utente_codice' " .
            "   AND codfornitore IN " .
            "       ( SELECT fornitore FROM " . $p4a->e3g_prefix . "fornitoreperiodo WHERE " . e3g_where_ordini_aperti() . " )" );
        
        if ( $this->SPE_ds_lista_spesa->getNumRows() == 0 )
            $this->msg_warning->setValue( "La lista della spesa e' stata svuotata." );
        else
            $this->msg_warning->setValue( "La lista della spesa e' stata svuotata tranne che per i fornitori per i quali il periodo d'ordine e' chiuso." );

        $this->SPE_tab_lista_spesa_afterClick();
        $this->update_top_message();
    }


    // -------------------------------------------------------------------------
    function SPE_bu_esporta_ordinePdfClick()
    // -------------------------------------------------------------------------
    {       
        require("class.report.php");
        
        $db =& p4a_db::singleton();
        $p4a =& p4a::singleton();
        
        $pdf = new Creport('a4','portrait');
                    
        $arr = array (
            "desc_fornitore"    => "Fornitore",
            "qta"               => "Q.ta'",
            "bio"               => "Bio",
            "descrizione"       => "Articolo",
            "um_qta_um"         => "Conf.",
            "prezzo_ven_um"     => "Prezzo/UM",
            "prezzoven"         => "Prezzo",
            "importo"           => "Importo"
        );
    
        // Recupero importo da visualizzare in stampa
        $strWhere = "codutente = '$p4a->e3g_utente_codice' ";
                
        if ( $this->SPE_fld_filtro_fornitore->getNewValue() != "00" )
            $strWhere .= " AND codfornitore = '".$this->SPE_fld_filtro_fornitore->getNewValue()."' ";
    
        if ( $this->SPE_fld_filtro_categoria->getNewValue() != "00" )
            $strWhere .= " AND art.tipo = '".$this->SPE_fld_filtro_categoria->getNewValue()."' ";

          
        $result = $db->queryRow( "SELECT SUM( c.qta ) AS qta, SUM( c.prezzoven * c.qta ) AS importo " .
            " FROM " . $p4a->e3g_prefix . "carrello AS c INNER JOIN ".$p4a->e3g_prefix."articoli AS art ON c.codarticolo = art.codice WHERE  ".$strWhere );

        //$qta = (integer) $result[ "qta" ];
        $importo = (double) $result[ "importo" ];
        
        $pdf->stampareport( $this->SPE_ds_lista_spesa->getAll(), $arr, 
            "Ordine corrente " . $p4a->e3g_utente_desc . " (" . $importo . " euro)", 
            "Ordine corrente " . $p4a->e3g_utente_desc );
    }
    

    // -------------------------------------------------------------------------
    function SPE_bu_esporta_ordineCsvClick()
    // -------------------------------------------------------------------------
    {       
        $db =& p4a_db::singleton();
        $p4a =& p4a::singleton();
        
        // MM_2009-01-26 Attenzione: causa probabile bug di p4a 2.2.3, non è possibile 
        // esportare le colonne in un ordine diverso da come sono presenti in tabella/query
        $colonne = array (
            "codfornitore"   => "Codice For.",
            "desc_fornitore" => "Fornitore",
            "qta"            => "Q.ta'",
            "bio"            => "Bio",
            "cod_articolo"   => "Cod. Art.",
            "descrizione"    => "Articolo",
            "um_qta_um"      => "Conf.",
            "prezzo_ven_um"  => "Prezzo/UM",
            "prezzoven"      => "Prezzo unitario",
            "importo"        => "Importo"
        );
    
        e3g_db_source_exportToCsv( $this->SPE_ds_lista_spesa, $colonne, "Ordine corrente " . $p4a->e3g_utente_desc );
    }


    // -------------------------------------------------------------------------
    function SPE_bu_invia_email_click()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        /* OGGETTO: Manto-GAS, lista della spesa
         * 
         * Salve Mario Rossi,
         * 
         * la tua lista della spesa è vuota.
         * la tua attuale lista della spesa è composta da:
         * 
         * Cooperativa Iris / Pasta e farina
         * - 3 (Bio) Fusilli integrali catering [5 Kg] x 6,00€ = 18,00€
         * - 12 (Bio) Grissini al farro [250 gr] x 3,50€ = 27,50€
         * 
         * Bio Caseificio Tomasoni / Formaggi
         * - 3 (Bio) Mozzarella [250 gr] x 4,00€ = 12,00€
         * 
         * TOTALI: 45 articoli per un importo di 123,45 euro.
         * 
         * La lista della spesa viene automaticamente elaborata dai referenti alla chiusura del periodo d'ordine.
         */        

        $qu_spesa = $db->getAll( 
            "SELECT CONCAT_WS( ' / ', f.descrizione, f.desc_agg ) AS desc_fornitore, " . 
            "       ( c.qta + c.qta_agg ) AS qta, art.bio, art.descrizione, " . 
            "       art.um_qta, art.um, " .  
            "       ROUND( c.prezzoven, $p4a->e3g_azienda_n_decimali_prezzi ) AS prezzoven, " .  
            "       ROUND( c.prezzoven * c.qta, $p4a->e3g_azienda_n_decimali_prezzi ) AS importo " .  
            "  FROM " . $p4a->e3g_prefix . "carrello AS c " . 
            "       JOIN " . $p4a->e3g_prefix . "articoli AS art ON c.codarticolo = art.codice " . 
            "       JOIN " . $p4a->e3g_prefix . "anagrafiche AS f ON c.codfornitore = f.codice " . 
            " WHERE c.codutente = '$p4a->e3g_utente_codice' " . 
          "ORDER BY f.descrizione, c.descrizione "  );

        $corpo = "Salve " . $p4a->e3g_utente_desc . ",\n\n";
        
        if ( $qu_spesa ) {
            $corpo .= "la tua attuale lista della spesa e' composta da:\n";
            $prec_for    = "";
            $tot_for     = 0;
            $n_articoli  = 0;
            $tot_importo = 0;
            foreach ( $qu_spesa as $record ) {
                if ( $prec_for <> $record["desc_fornitore"] ) {
                    if ( $prec_for <> "" ) 
                        $corpo .= "Totale \"" . $prec_for . "\": " . $tot_for . " euro\n"; 
                    $corpo .= "\n" . $record["desc_fornitore"] . "\n";
                    $prec_for = $record["desc_fornitore"];
                    $tot_for  = 0;
                }
                $corpo .= 
                    "+ " . $record["qta"] . " " .
                    $record["descrizione"] . ( $record["bio"] ? " (*)" : "" ) .
                    ( $record["um_qta"] ? " [" . $record["um_qta"] . " " . $record["um"] . "]" : "" ) .
                    " x " . $record["prezzoven"] . " euro = " . $record["importo"] . " euro\n";
                $n_articoli  += $record["qta"];
                $tot_importo += $record["importo"];
                $tot_for     += $record["importo"];
            }
            $corpo .= 
                "Totale \"" . $record["desc_fornitore"] . "\": " . $tot_for . " euro\n\n" . 
                "TOTALE: $n_articoli articoli per un importo di $tot_importo euro\n\n" . 
                "(*) Da agricoltura biologica\n\n" . 
                "La lista della spesa viene automaticamente elaborata dai referenti alla chiusura del periodo d'ordine.";
        }
        else
            $corpo .= "la tua lista della spesa e' vuota.";

        if ( !e3g_invia_email( $p4a->e3g_azienda_rag_soc . ", lista della spesa", 
                               $corpo, 
                               $p4a->e3g_utente_email, $p4a->e3g_utente_desc ) ) 
            $this->msg_warning->setValue( "Si sono verificati errori durante l'invio e-mail." ); 
    }
    

    // -------------------------------------------------------------------------
    function update_label_note()
    // -------------------------------------------------------------------------
    {
        if ( trim( $this->NOT_fld_note->getNewValue() ) <> "" )
            $this->tab_pane->pages->tabframe3->setLabel( "Note all'ordine" ); 
        else
            $this->tab_pane->pages->tabframe3->setLabel( "Note all'ordine (nessuna)" ); 
    }

    // -------------------------------------------------------------------------
    function bu_salva_note_click()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        $db->query(
            "UPDATE " . $p4a->e3g_prefix . "anagrafiche " .
            "   SET note_ordine = '" . addslashes( trim( $this->NOT_fld_note->getNewValue() ) ) . "'" .
            " WHERE idanag = " . $p4a->e3g_utente_idanag );
        $this->update_label_note();
    }

    
}

?>