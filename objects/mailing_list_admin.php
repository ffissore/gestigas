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


class mailing_list_admin extends P4A_Mask
{
    // -------------------------------------------------------------------------
    function mailing_list_admin()
    // -------------------------------------------------------------------------
    {
        $this->p4a_mask();
        $this->addCss( E3G_TEMPLATE_DIR . 'css/style.css' );
        $p4a =& p4a::singleton();
        $db  =& p4a_db::singleton();
        
        $this->setTitle('Parametri mailing-list/forum');
        $this->setIcon( "email" );  // TODO Sistemare l'icona
        
        
        // Sorgente dati principale --------------------------------------------
        $this->build("p4a_db_source", "ds_ml");
        $this->ds_ml->setTable("_mailing_list");
        $this->ds_ml->setWhere( "prefix = '" . $p4a->e3g_prefix . "'" );
        $this->ds_ml->setPk("id_mailing_list");
        $this->ds_ml->load();
        $this->ds_ml->firstRow();
        
        $this->setSource($this->ds_ml);

        // Fields properties 
        $fields =& $this->fields;
        
        $this->mf = array();  // Impostato nel saveRow()

        // Se non esiste, inserisce il record con i valori predefiniti 
        if ( $this->ds_ml->getNumRows() == 0 ) {
            $maxid = $db->queryOne( "SELECT MAX( id_mailing_list ) FROM _mailing_list" );
            $maxid = ( is_numeric($maxid) ) ? $maxid+1 : 1; 
            $this->ds_ml->newRow();
            $this->fields->id_mailing_list->setNewValue( $maxid );
            $this->fields->prefix->setNewValue( $p4a->e3g_prefix );
            $this->fields->tipo_lista->setNewValue( 2 );  // 2:Solo mailing-list
            $this->fields->pop_port->setNewValue( 110 );
            $this->fields->reply_to->setNewValue( 1 );  // 1:Quello della lista
            $this->fields->prefisso->setNewValue( '[' . $p4a->e3g_azienda_rag_soc . ']' );
            $this->ds_ml->saveRow();
        }


        // Toolbar salva, annulla, stampa, chiudi ------------------------------
        $toolbar =& $this->build("p4a_actions_toolbar", "toolbar");
        $toolbar->setMask($this);

        // Eventuale warning ---------------------------------------------------
        $msg_info =& $this->build( "p4a_message", "msg_info" );
        $msg_info->setWidth( 700 );


        // Elenco campi --------------------------------------------------------
        $this->fields->tipo_lista->setLabel("Tipo");
        $this->fields->tipo_lista->setWidth(150);
        $this->fields->tipo_lista->label->setWidth(150);
        // Valori "Tipo lista"
        $values_tl = array();
        $values_tl[] = array("id" => "1", "desc" => "Solo forum on-line");   // Non vengono letti/spediti msg, ma solo archiviati nel db
        $values_tl[] = array("id" => "2", "desc" => "Solo mailing-list");    // Vengono letti/spediti msg, ma non archiviati nel db
        $values_tl[] = array("id" => "3", "desc" => "Forum + mailing-list"); // Vengono letti/spediti msg ed anche archiviati nel db
        $array_source_tl =& $this->build("p4a_array_source", "array_source"); 
        $array_source_tl->load( $values_tl ); 
        $array_source_tl->setPk( "id" ); 
        $this->fields->tipo_lista->setType( "select" );
        $this->fields->tipo_lista->setSource( $array_source_tl ); 
        $this->fields->tipo_lista->setSourceDescriptionField( "desc" );
        $this->fields->tipo_lista->setValue( "2" );

        $this->fields->email->setLabel("Indirizzo e-mail");
        $this->fields->email->setTooltip("I messaggi rivolti alla lista devono essere inviati a questo indirizzo, " .
            "il quale deve essere usato solo per questo scopo.");
        $this->fields->email->setWidth(300);
        $this->fields->email->label->setWidth(150);

        $this->fields->pop_server->setLabel("Server di posta (POP)");
        $this->fields->pop_server->setTooltip($p4a->e3g_nome_sw . " tentera' di collegarsi a questo server per leggere i messaggi.");
        $this->fields->pop_server->setWidth(300);
        $this->fields->pop_server->label->setWidth(150);

        $this->fields->pop_port->setLabel("Porta (POP)");
        $this->fields->pop_port->setTooltip("Porta predefinita: 110");
        $this->fields->pop_port->setWidth(50);

        $this->fields->pop_user->setLabel("Nome utente (POP)");
        $this->fields->pop_user->setWidth(300);
        $this->fields->pop_user->label->setWidth(150);

        $this->fields->pop_password->setLabel("Password (POP)");
        $this->fields->pop_password->setWidth(300);
        $this->fields->pop_password->label->setWidth(150);

        $this->fields->reply_to->setLabel("Indirizzo risposta");
        $this->fields->reply_to->setTooltip("Preimpostazione dell'indirizzo di risposta per i messaggi della lista.");
        $this->fields->reply_to->setWidth(150);
        $this->fields->reply_to->label->setWidth(150);
        // Valori "Reply-to"
        $values_rt = array();
        $values_rt[] = array("id" => "1", "desc" => "Quello della lista");
        $values_rt[] = array("id" => "2", "desc" => "Quello del mittente");
        $array_source_rt =& $this->build("p4a_array_source", "array_source"); 
        $array_source_rt->load( $values_rt ); 
        $array_source_rt->setPk( "id" ); 
        $this->fields->reply_to->setType( "select" );
        $this->fields->reply_to->setSource( $array_source_rt ); 
        $this->fields->reply_to->setSourceDescriptionField( "desc" );
        $this->fields->reply_to->setValue( "1" );

        $this->fields->prefisso->setLabel("Prefisso nel soggetto");
        $this->fields->prefisso->setTooltip("Solitamente si mette il nome della lista (o una sua abbreviazione) tra parentesi " .
            "quadre. E' utile per individuare rapidamente i messaggi e per poter impostare dei filtri nei programmi di posta elettronica.");
        $this->fields->prefisso->setWidth(150);
        $this->fields->prefisso->label->setWidth(150);

        $this->fields->modello_header->setLabel("Modello intestazione");
        $this->fields->modello_header->setTooltip("Questo testo (facoltativo) verra' incluso come intestazione nella parte iniziale di ogni messaggio.");
        $this->fields->modello_header->label->setWidth(150);
        $this->fields->modello_header->setType( "textarea" );
        $this->fields->modello_header->setWidth( 470 );
        $this->fields->modello_header->setHeight( 70 );

        $this->fields->modello_footer->setLabel("Modello pie' di pagina");
        $this->fields->modello_footer->setTooltip("Questo testo (facoltativo) verra' incluso nella parte finale di ogni messaggio.");
        $this->fields->modello_footer->label->setWidth(150);
        $this->fields->modello_footer->setType( "textarea" );
        $this->fields->modello_footer->setWidth( 470 );
        $this->fields->modello_footer->setHeight( 70 );


        // Fieldset con l'elenco dei campi -------------------------------------
        $fset1=& $this->build( "p4a_fieldset", "frame" );
        $fset1->setWidth( 700 );
        $fset1->anchor( $this->fields->tipo_lista );

        $fset2=& $this->build( "p4a_fieldset", "frame" );
        $fset2->setWidth( 700 );
        $fset2->anchor( $this->fields->email );
        $fset2->anchor( $this->fields->pop_server );
        $fset2->anchorLeft( $this->fields->pop_port );
        $fset2->anchor( $this->fields->pop_user );
        $fset2->anchor( $this->fields->pop_password );
        $fset2->anchor( $this->fields->reply_to );

        $fset3=& $this->build( "p4a_fieldset", "frame" );
        $fset3->setWidth( 700 );
        $fset3->anchor( $this->fields->prefisso);
        $fset3->anchor( $this->fields->modello_header );
        $fset3->anchor( $this->fields->modello_footer );

//TODO da vedere perchè non funziona...  
        // Primo eventuale warning ---------------------------------------------
        $ml = $db->queryOne( "SELECT mailing_list FROM _aziende WHERE prefix = '" . $p4a->e3g_prefix . "'" );
        if ( $ml == 0 ) {
            $this->msg_info->setIcon("warning");
            $this->msg_info->setValue( "Per utilizzare la funziona mailing-list, abilitare l'apposita opzione in 'Strumenti | Opzioni'" );
        }
        
        
        // ---------------------------------------------------- Frame principale
        $frm=& $this->build("p4a_frame", "frm");
        $frm->setWidth(730);

        $frm->anchor( $msg_info );
        $frm->anchor( $fset1 );
        $frm->anchor( $fset2 );
        $frm->anchor( $fset3 );

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
        $p4a =& p4a::singleton();

        parent::main();
        
        $this->fields->email->label->setFontWeight("");
        $this->fields->email->unsetStyleProperty("border");
        $this->fields->pop_server->label->setFontWeight("");
        $this->fields->pop_server->unsetStyleProperty("border");
        $this->fields->pop_port->label->setFontWeight("");
        $this->fields->pop_port->unsetStyleProperty("border");
        $this->fields->pop_user->label->setFontWeight("");
        $this->fields->pop_user->unsetStyleProperty("border");
        $this->fields->pop_password->label->setFontWeight("");
        $this->fields->pop_password->unsetStyleProperty("border");
        $this->fields->reply_to->label->setFontWeight("");
        $this->fields->reply_to->unsetStyleProperty("border");
    }
    

    // -------------------------------------------------------------------------
    function saveRow()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
        
        // Controllo validita' dati --------------------------------------------
        
        $error_text = "";

        if ( $this->fields->tipo_lista->getNewValue()==1 or   // Solo forum
             $this->fields->tipo_lista->getNewValue()==3 ) {  // Forum + mailing-list

            $this->mf = array();
        }           
        
        if ( $this->fields->tipo_lista->getNewValue()==2 or   // Solo mailing-list
             $this->fields->tipo_lista->getNewValue()==3 ) {  // Forum + mailing-list

            $this->mf = array("email", "pop_server", "pop_port", "pop_user", "pop_password", "reply_to");

            if ( !e3g_email_valido( $this->fields->email->getNewValue() ) ) {
                $error_text = "Scrivere un indirizzo e-mail valido";
                $this->fields->email->setStyleProperty("border", "1px solid red");
            }
        }           
        foreach ( $this->mf as $mf ) 
            $this->fields->$mf->label->setFontWeight("bold");
        
        // Verifica campi obbligatori
        if ( $error_text == "" )
            foreach ( $this->mf as $mf ) {
                $value = $this->fields->$mf->getNewValue();
                if ( trim($value) === "" ) {
                    $this->fields->$mf->setStyleProperty("border", "1px solid red");
                    $error_text = "Compilare i campi obbligatori";
                }
            }


        if ( $error_text == "" ) {
            parent::saveRow();          
            $this->maskClose( "mailing_list_admin" );
        }
        else {
            $this->msg_info->setIcon("warning");
            $this->msg_info->setValue( $error_text );
        }
    }


}

?>