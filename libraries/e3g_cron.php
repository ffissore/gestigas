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


require_once( dirname(__FILE__) . '/../config.php' );
require_once( dirname(__FILE__) . '/../libraries/pop3_class/pop3.php' );


//------------------------------------------------------------------------------
// Funzione richiamata periodicamente da cron o simili
//------------------------------------------------------------------------------
function e3g_cron() {
    
    $p4a =& p4a::singleton();
    $db =& p4a_db::singleton();

    $sql_text = 
        "SELECT mailing_list, " .
        "       notifica_apertura_ref, ( notifica_apertura_ref_data = CURDATE() ) AS notifica_apertura_ref_inviata_oggi, " .
        "       notifica_apertura,     ( notifica_apertura_data = CURDATE() )     AS notifica_apertura_inviata_oggi, " .
        "       notifica_chiusura,     ( notifica_chiusura_data = CURDATE() )     AS notifica_chiusura_inviata_oggi, " .
        "       notifica_lista_spesa,  ( notifica_lista_spesa_data = CURDATE() )  AS notifica_lista_spesa_inviata_oggi, " .
        "       notifica_mov_cassa,    ( notifica_mov_cassa_data = CURDATE() )    AS notifica_mov_cassa_inviata_oggi " .
        "  FROM _aziende" .
        " WHERE prefix = '" . $p4a->e3g_prefix . "'";
    $query = $db->getRow( $sql_text );

//  if ( $query["mailing_list"] )      e3g_mailing_list();

    if ( $query["notifica_apertura_ref"] and !$query["notifica_apertura_ref_inviata_oggi"] ) 
        e3g_notifica_apertura_ref();

    if ( $query["notifica_apertura"] and !$query["notifica_apertura_inviata_oggi"] ) 
        e3g_notifica_apertura();

    if ( $query["notifica_chiusura"] and !$query["notifica_chiusura_inviata_oggi"] ) 
        e3g_notifica_chiusura();   

//    if ( $query["notifica_lista_spesa"] and !$query["notifica_lista_spesa_inviata_oggi"] ) 
//        e3g_notifica_lista_spesa();   

    if ( $query["notifica_mov_cassa"] and !$query["notifica_mov_cassa_inviata_oggi"] ) 
        e3g_notifica_mov_cassa();           
}


//------------------------------------------------------------------------------
// Visualizzazione messaggi a scopo debug
//------------------------------------------------------------------------------
function e3g_debug( $a_message ) {
    echo "<PRE>$a_message</PRE>\n";
}                        


//------------------------------------------------------------------------------
// Gestione mailing_list
//------------------------------------------------------------------------------
function e3g_mailing_list() {
 
//TODO Scrivere eventuali errori in un file di testo da usare come log
//TODO Fare in modo che l'output del debug, se attivato, venga scritto nello stesso log
//TODO Aggiungere voce di menu che permetta di controllare tale log
//TODO Impostare l'X-Mailer di invio come e3g/GestiGAS v... (direttamente nella funzione e3g_invia_email)
/*TODO Intestazioni facoltative, esempio da lista retegas (sympa):
    List-Id: <gas.liste.retelilliput.org>
    List-Archive: <http://liste.retelilliput.org/wws/arc/gas>
    List-Help: <mailto:sympa@liste.retelilliput.org?subject=help>
    List-Owner: <mailto:gas-request@liste.retelilliput.org>
    List-Post: <mailto:gas@liste.retelilliput.org>
    List-Subscribe: <mailto:sympa@liste.retelilliput.org?subject=subscribe%20gas>
    List-Unsubscribe: <mailto:sympa@liste.retelilliput.org?subject=unsubscribe%20gas> 
*/
//TODO Fare in modo che un utente possa eliminarsi dalla lista anche via email
//     o meglio disattivare la ricezione dei messaggi
//     (nel senso di impostare [PREFIX]anagrafiche.mailing_list a zero)

    
    // Determina l'ora di invio del messaggio (la riga e' quella iniziante con "Date:")
    // ESEMPI: "Date: Mon, 23 Mar 2009 17:26:02 +0100"
    function GetSendDate( $headers ) {
        for( $line=0; $line<count($headers); $line++) 
            if ( strpos($headers[$line], "Date: ") === 0 ) break;
        if ( $line < count($headers) ) 
            return substr( $headers[$line], 6 );
    }
      
        
    // Determina il mittente dall'header del messaggio (la riga e' quella iniziante con "From:")
    // ESEMPI: "From: Marco Munari <nome.utente@progettoe3g.org>" (talvolta il nick e' tra doppi apici)
    // RIFERIMENTI: (generalita') http://news.aioe.org/spip.php?article94
    // RIFERIMENTI: (sul vero mittente di un msg) http://www.ol-service.com/sikurezza/misterpici/Privacy_Email.htm 
    function GetMittente( $headers ) {
        for( $line=0; $line<count($headers); $line++) 
            if ( strpos($headers[$line], "From: ") === 0 ) break;
        if ( $line < count($headers) ) 
            return substr( $headers[$line], 
                1 + strpos($headers[$line], "<"), strpos($headers[$line], ">") - strpos($headers[$line], "<") - 1 );        
    }
      
        
    // Determina l'oggetto dall'header del messaggio (la riga e' quella iniziante con "Subject:")
    // ESEMPI: "Subject: Prova invio messaggio"
    // RIFERIMENTI: http://news.aioe.org/spip.php?article94 - http://www.anti-phishing.it/tecniche/headers.php
    function GetOggetto( $headers ) {
        for( $line=0; $line<count($headers); $line++) 
            if ( strpos($headers[$line], "Subject: ") === 0 ) break;
        if ( $line < count($headers) ) {
            $oggetto = substr( $headers[$line], 9 );
            return $oggetto;        
        }      
    }
     
        
    // Oggetto: aggiunge l'eventuale [PREFISSO]
    function ReOggetto( $a_oggetto, $prefisso ) {
        if ( !strpos($a_oggetto, $prefisso) )            
            return $prefisso . " " . $a_oggetto;
        else        
            return $a_oggetto;        
    }
     
        
    // Questa funzione dovrebbe ripulire il corpo del messaggio se questo e' stato composto come HTML
    // Content-Type:
    // E' diviso in due parti: la prima indica in che formato è stato scritto il post (text plain o HTML), 
    // la seconda indica il charset. Dove per charset indichiamo un set di caratteri (che può essere standard o meno). 
// TODO Per ora restituisce tutto il $body        
    // ATTENZIONE che $body e' un array
    function GetCorpo( $body, $modello_header, $modello_footer ) {
//        for ( $line=0; $line<count($body); $line++ )
//            e3g_debug( HtmlSpecialChars($body[$line]) );

        // Eventuale aggiunta di header e footer
        $s_body = implode( "", $body );
        if ( trim($modello_header) <> "" and !( strpos($s_body, $modello_header) === 0 ) ) 
            array_unshift( $body, $modello_header );  // Aggiunge l'elemento all'inizio dell'array 
        if ( trim($modello_footer) <> "" and !( strrpos($s_body, $modello_footer) === (strlen($s_body)-strlen($modello_footer)) ) ) 
            array_push( $body, $modello_footer );  // Aggiunge l'elemento in coda all'array 
        
        return $body;
    }


    $p4a =& p4a::singleton();
    $db =& p4a_db::singleton();

    $sql_text = "SELECT ml.* " .  
                "  FROM _mailing_list AS ml " .
                " WHERE ml.prefix = '" . $p4a->e3g_prefix . "'" ;
    $query1 = $db->getRow( $sql_text );

    // Se tipo "mailing-list" o "forum + mailing-list"
    if ( $query1["tipo_lista"]==2 or $query1["tipo_lista"]==3 ) { 
        $pop3 = new pop3_class;

        // Imposta parametri di connessione
        $pop3->hostname = $query1["pop_server"];   // POP 3 server host name                      
        $pop3->port = $query1["pop_port"];         // POP 3 server host port, usually 110 but some servers use other ports (Gmail uses 995)              
        $pop3->tls = 0;                            // Establish secure connections using TLS      
        $user = $query1["pop_user"];               // Authentication user name                    
        $password = $query1["pop_password"];       // Authentication password                     
        $pop3->realm = "";                         // Authentication realm or domain              
        $pop3->workstation = "";                   // Workstation for NTLM authentication         
        $apop = 0;                                 // Use APOP authentication                     
        $pop3->authentication_mechanism = "USER";  // SASL authentication mechanism               
        $pop3->debug = 0;                          // Output debug information                    
        $pop3->html_debug = 1;                     // Debug information is in HTML                
        $pop3->join_continuation_header_lines = 1; // Concatenate headers split in multiple lines 
        
        // Connessione server di posta
        if ( ($error = $pop3->Open()) == "" ) {

            // Login con nome utente e password
            if( ($error = $pop3->Login($user,$password,$apop)) == "" ) {

                // Verifica presenza messaggi
                if ( ($error = $pop3->Statistics($messages,$size)) == "" /*&& $messages > 0*/ ) {  // $messages: n° messaggi; $size: ampiezza totale
                   
                    // Prepara elenco utenti a cui spedire il messaggio
                    $sql_text = "SELECT descrizione, email FROM " . $p4a->e3g_prefix . "anagrafiche " .
                                " WHERE tipocfa = 'C' AND tipoutente <> 'A' AND stato = 1 AND mailing_list = 1 " .
                             " ORDER BY descrizione";  
                    $query2 = $db->getAll( $sql_text );

                    $result = $pop3->ListMessages( "", 1 );  
                    if ( GetType($result) == "array" ) {
                        for ( Reset($result), $message=1; $message<=count($result); Next($result), $message++ ) {
                            e3g_debug( "MESSAGGIO " . Key($result) . ", Unique ID: \"" . $result[Key($result)] . "\"" );

                            if ( ( $error = $pop3->RetrieveMessage($message,$headers,$body,2) ) == "" ) {
                                // Decodifica il mittente
                                $mittente = GetMittente( $headers );
                                // Se il mittente è uno degli iscritti, allora...                         
                                foreach ( $query2 as $record ) 
                                    if ( $record["email"] == $mittente ) break;
                                    
                                // ...prepara i dati per comporre il messaggio da inviare
                                $data_invio = GetSendDate( $headers );
                                $mittente_name = $record["descrizione"];
                                $oggetto = GetOggetto( $headers );
                                $corpo = GetCorpo( $body, $query1["modello_header"], $query1["modello_footer"] );
                                
                                if ( $record["email"] == $mittente ) {
                                    // A) Mittente iscritto alla mailing-list -> invio del messaggio ad ogni utente iscritto 
                                    foreach ( $query2 as $record ) {
                                        if ( $record["email"] == $mittente ) break;
                                        
                                        e3g_debug( "  DESTINATARIO: " . $record["descrizione"] . " <" .$record["email"] . ">" );
                                        e3g_debug( "  - MITTENTE: $mittente_name <$mittente>" );
                                        e3g_debug( "  - OGGETTO: $oggetto" );
                                        for ( $line=0; $line<count($corpo); $line++ )
                                            e3g_debug( "  - CORPO RIGA $line: ",HtmlSpecialChars($corpo[$line]) );
                                            
                                        if ( !e3g_invia_email( ReOggetto( $oggetto, $query1["prefisso"] ), 
                                                               $corpo, 
                                                               $record["email"], $record["descrizione"], 
                                                               3, $mittente, $mittente_name,
                                                                 ( $query1["reply_to"] == 1 ? $query1["email"] : $mittente ), 
                                                                 ( $query1["reply_to"] == 1 ? "Mailing-list " . $p4a->e3g_azienda_rag_soc : $mittente_name ) ) ) {
                                            e3g_debug( "Si sono verificati errori durante l'invio a: " . $record["descrizione"] . " <" .$record["email"] . ">" ); 
                                        }
                                        
                                    }
                                        
                                }
                                else {
                                    // B) Mittente NON iscritto alla mailing-list -> risposta con un msg informativo 
                                    e3g_debug( "  -> Non spedito perche' il mittente <$mittente> non e' conosciuto." );
                                    
                                    $corpo = "Salve $mittente_name,\n" .
                                        "il messaggio che hai inviato il $data_invio non e' stato inoltrato alla mailing-list perche' solo gli iscritti alla lista possono inviare messaggi e " .
                                        "l'indirizzo e-mail dal quale hai scritto non risulta tra quelli ammessi.\n\n" .
                                        "I riferimenti al tuo messaggio sono i seguenti:\n" .
                                        "    Mittente: $mittente\n" .
                                        "    Oggetto: $oggetto\n" .
                                        "    Data invio: $data_invio";

                                    e3g_invia_email(
                                        "Mailing-list " . $query1["prefisso"] . ": problema con il messaggio '$oggetto'", 
                                        $corpo, 
                                        $mittente, $mittente_name );
                               }
 
                               // Marcatura messaggio come da cancellare [l'eliminazione effetiva avviene al ->Close()]
                               $error = $pop3->DeleteMessage($message);
                            }
                        } 
                        $error = $pop3->ResetDeletedMessages();  // Solo a scopo di debug
                    }
                    else
                        $error = $result;
                }
            }
            // Disconnessione server
            if( $error == "" && ($error=$pop3->Close()) == "" )
                e3g_debug( "Disconnected from the POP3 server &quot;".$pop3->hostname."&quot;." );                            
        }
    
        if ( $error != "" )
            e3g_debug( "<H2>Error: ",HtmlSpecialChars($error),"</H2>" );
    }
}


//------------------------------------------------------------------------------
// Notifiche anticipate apertura ordine (REFERENTI)
//------------------------------------------------------------------------------
function e3g_notifica_apertura_ref() {
    $p4a =& p4a::singleton();
    $db =& p4a_db::singleton();

    // Se tra (notifica_apertura_ref_gg) giorni si APRE almeno un ordine...
    $qu_referenti = $db->getAll( 
        "SELECT ar.descrizione, ar.email, ar.codice, az.notifica_apertura_ref_gg, fp.datainizio " .
        "  FROM " . $p4a->e3g_prefix . "fornitoreperiodo fp " . 
        "       JOIN _aziende AS az ON az.prefix = '" . $p4a->e3g_prefix . "' " .
        "       JOIN " . $p4a->e3g_prefix . "anagrafiche f ON f.codice = fp.fornitore AND f.stato = 1 " .
        "       JOIN " . $p4a->e3g_prefix . "referenti r ON r.codfornitore = fp.fornitore " .
        "       JOIN " . $p4a->e3g_prefix . "anagrafiche ar ON ar.codice = r.codanag AND ar.stato = 1 " .
        "  WHERE ( DATE_SUB( fp.datainizio, INTERVAL az.notifica_apertura_ref_gg DAY ) = CURDATE() OR " .
        "          ( DATE_SUB( fp.datainizio, INTERVAL az.notifica_apertura_ref_gg DAY ) = MAKEDATE( EXTRACT(YEAR FROM CURDATE()), DAYOFYEAR(fp.datainizio) ) AND " .
        "            fp.ricorsivo = 'S' ) ) " .
      " GROUP BY ar.descrizione, ar.email, ar.codice, az.notifica_apertura_ref_gg, fp.datainizio " . 
      " ORDER BY ar.descrizione " );
    
    if ( $qu_referenti ) {
        /* OGGETTO: Manto-GAS, apertura ordine tra 14 giorni
         * 
         * Salve Mario Rossi,
         * 
         * il 21/12/2011 si aprira' il periodo d'ordine nei confronti dei fornitori di cui risulti referente:
         * 
         * - Az. Agr. Erba Madre / cosmesi naturale (1 articolo)
         * - Eugea / Ecologia Urbana (6 articoli)
         * 
         * Sei invitato a controllare i listini aggiornando prezzo e disponibilita' degli articoli.
         * Per collegarti vai a: http://www.gestigas.org/e3g/?prefix=mantogas
         */        

        // Parte di messaggio finale
        $msg_fine = "\nSei invitato a controllare i listini aggiornando prezzo e disponibilita' degli articoli.\n" .
            "Per collegarti vai a: " . P4A_APPLICATION_URL . "?prefix=$p4a->e3g_prefix";
                
        // Per ogni referente determina i fornitori interessati (possono essere più di uno)
        $n_invii = 0;
        foreach ( $qu_referenti as $qu_referente ) {
            $qu_fornitori = $db->getAll( 
                "SELECT f.descrizione, COUNT( a.codice ) AS n_articoli " .
                "  FROM " . $p4a->e3g_prefix . "fornitoreperiodo fp " . 
                "       JOIN _aziende AS az ON az.prefix = '" . $p4a->e3g_prefix . "' " .
                "       JOIN " . $p4a->e3g_prefix . "anagrafiche f ON f.codice = fp.fornitore AND f.stato = 1 " .
                "       JOIN " . $p4a->e3g_prefix . "referenti r ON r.codfornitore = fp.fornitore " .
                "       LEFT JOIN " . $p4a->e3g_prefix . "articoli AS a ON a.centrale = f.codice AND a.stato = 1 " . 
                "  WHERE ( DATE_SUB( fp.datainizio, INTERVAL az.notifica_apertura_ref_gg DAY ) = CURDATE() OR " .
                "          ( DATE_SUB( fp.datainizio, INTERVAL az.notifica_apertura_ref_gg DAY ) = MAKEDATE( EXTRACT(YEAR FROM CURDATE()), DAYOFYEAR(fp.datainizio) ) AND " .
                "    fp.ricorsivo = 'S' ) ) AND " .
                "        r.codanag = '" . $qu_referente["codice"] . "' " .
              " GROUP BY f.descrizione " .
              " ORDER BY f.descrizione " );

            // Prepara parte di messaggio centrale con elenco fornitori
            $msg_centro = "";
            foreach ( $qu_fornitori as $qu_fornitore ) {
                // Esempio: "- Eugea / Ecologia Urbana (6 articoli)"
                $msg_centro .=
                    "- " . $qu_fornitore["descrizione"] . " (" . $qu_fornitore["n_articoli"] . " articol" . ( $qu_fornitore["n_articoli"]==1 ? "o" : "i" ) . ")\n";
            }

            $oggetto = $p4a->e3g_azienda_rag_soc . ", apertura ordine tra " . $qu_referente["notifica_apertura_ref_gg"] . " giorn" . ( $qu_referente["notifica_apertura_ref_gg"]==1 ? "o" : "i" );

            // Parte di messaggio iniziale
            $msg_inizio = 
                "Salve " . $qu_referente["descrizione"] . ",\n\n" .
                "il " . e3g_format_mysql_data( $qu_referente["datainizio"] ) . " si aprira' il periodo d'ordine nei confronti dei fornitori di cui risulti referente:\n\n";
                
            if ( !e3g_invia_email( $oggetto, 
                                   $msg_inizio . $msg_centro . $msg_fine, 
                                   $qu_referente["email"], $qu_referente["descrizione"] ) ) 
                e3g_debug( "Si sono verificati errori durante l'invio a: " . $qu_referente["descrizione"] );

            $n_invii++; 
        }
        
        // Aggiorna _aziende con la data odierna per evitare ulteriore spedizione
        $db->query( "UPDATE _aziende " .
            "   SET notifica_apertura_ref_data = CURDATE() " .
            " WHERE prefix = '" . $p4a->e3g_prefix . "'" );
            
        return $n_invii;  // TODO Però potrebbero essersi verificati degli errori
    }
    else
        return false;  // Niente da notificare
}


//------------------------------------------------------------------------------
// Notifiche apertura ordine (UTENTI)
//------------------------------------------------------------------------------
function e3g_notifica_apertura() {
    $p4a =& p4a::singleton();
    $db =& p4a_db::singleton();

    // Se oggi si APRE almeno un ordine...
    $qu_fornitori = $db->getAll( 
        "SELECT f.descrizione, fp.datafine, " .
        "       COUNT( a.codice ) AS n_articoli " .
        "  FROM " . $p4a->e3g_prefix . "fornitoreperiodo fp " . 
        "       JOIN " . $p4a->e3g_prefix . "anagrafiche f ON f.codice = fp.fornitore AND f.stato = 1 " . 
        "       LEFT JOIN " . $p4a->e3g_prefix . "articoli a ON a.centrale = f.codice AND a.stato = 1 " .
        "  WHERE " . e3g_where_ordini_aperti("fp") .
        "    AND fp.datainizio = CURDATE() " .
      " GROUP BY f.descrizione, fp.datafine " .
      " ORDER BY f.descrizione" );  
    
    if ( $qu_fornitori ) {
        $oggetto = $p4a->e3g_azienda_rag_soc . ", apertura ordine";

        /* OGGETTO: Manto-GAS, apertura ordine
         * 
         * Salve Mario Rossi,
         * 
         * si apre oggi il periodo d'ordine nei confronti dei seguenti fornitori:
         * 
         * - Az. Agr. Gozzi Cesare e Franco, fino al 28/02/2009 (1 articolo)
         * - Eugea / Ecologia Urbana, fino al 28/02/2009 (6 articoli)
         * 
         * Per ordinare, collegarsi a: http://www.gestigas.org/e3g/?prefix=mantogas
         */        

        // Prepara parte di messaggio centrale con elenco fornitori
        $msg_centro = "";
        foreach ( $qu_fornitori as $qu_fornitore ) {
            // Esempio: "- Eugea / Ecologia Urbana, fino al 28 febbraio (6 articoli)"
            $msg_centro .=
                "- " . $qu_fornitore["descrizione"] . ", fino al " . e3g_format_mysql_data( $qu_fornitore["datafine"] ) .
                " (" . $qu_fornitore["n_articoli"] . " articol" . ( $qu_fornitore["n_articoli"]==1 ? "o" : "i" ) . ")\n";
        }
        // Parte di messaggio finale
        $msg_fine = "\nPer ordinare, collegarsi a: " . P4A_APPLICATION_URL . "?prefix=$p4a->e3g_prefix";
                
        // Invia la mail ad ogni utente attivo
        $qu_utenti = $db->getAll( 
            "SELECT descrizione, email FROM " . $p4a->e3g_prefix . "anagrafiche " .
            " WHERE tipocfa = 'C' AND tipoutente <> 'A' AND stato = 1 " );

        $n_invii = 0;
        foreach ( $qu_utenti as $qu_utente ) {
            // Parte di messaggio iniziale
            $msg_inizio = 
                "Salve " . $qu_utente["descrizione"] . ",\n\n" .
                "si apre oggi il periodo d'ordine nei confronti dei seguenti fornitori:\n\n";

            if ( !e3g_invia_email( $oggetto, 
                                   $msg_inizio . $msg_centro . $msg_fine, 
                                   $qu_utente["email"], $qu_utente["descrizione"] ) ) 
                e3g_debug( "Si sono verificati errori durante l'invio a: " . $qu_utente["descrizione"] );
            $n_invii++; 
        }
        
        // Aggiorna _aziende con la data odierna per evitare ulteriore spedizione
        $db->query( "UPDATE _aziende " .
            "   SET notifica_apertura_data = CURDATE() " .
            " WHERE prefix = '" . $p4a->e3g_prefix . "'" );
            
        return $n_invii;  // TODO Però potrebbero essersi verificati degli errori
    }
    else
        return false;  // Niente da notificare
}


//------------------------------------------------------------------------------
// Notifica anticipatamente la chiusura ordine
//------------------------------------------------------------------------------
function e3g_notifica_chiusura() {
    $p4a =& p4a::singleton();
    $db =& p4a_db::singleton();

    // Se tra (notifica_chiusura_gg) giorni si CHIUDE almeno un ordine...
    $qu_fornitori = $db->getAll( 
        "SELECT f.descrizione, fp.datafine, az.notifica_chiusura_gg, " .
        "       COUNT( a.codice ) AS n_articoli " .
        "  FROM " . $p4a->e3g_prefix . "fornitoreperiodo AS fp " . 
        "       JOIN " . $p4a->e3g_prefix . "anagrafiche AS f ON f.codice = fp.fornitore AND f.stato = 1 " . 
        "       LEFT JOIN " . $p4a->e3g_prefix . "articoli AS a ON a.centrale = f.codice AND a.stato = 1 " .
        "       JOIN _aziende AS az ON az.prefix = '" . $p4a->e3g_prefix . "'" .
        "  WHERE " . e3g_where_ordini_aperti("fp") .
        "    AND DATE_SUB( fp.datafine, INTERVAL az.notifica_chiusura_gg DAY ) = CURDATE() " .
      " GROUP BY f.descrizione, fp.datafine, az.notifica_chiusura_gg " .
      " ORDER BY f.descrizione" );  
    
    if ( $qu_fornitori ) {
        $oggetto = $p4a->e3g_azienda_rag_soc . ", chiusura ordine tra " . $qu_fornitori[0]["notifica_chiusura_gg"] . " giorn" .
            ( $qu_fornitori[0]["notifica_chiusura_gg"]==1 ? "o" : "i" );

        /* OGGETTO: Manto-GAS, chiusura ordine tra 3 giorni
         * 
         * Salve Mario Rossi,
         * 
         * il 28/02/2009 si chiuderà il periodo d'ordine nei confronti dei seguenti fornitori:
         * 
         * - Az. Agr. Gozzi Cesare e Franco (1 articolo)
         * - Eugea / Ecologia Urbana (6 articoli)
         * 
         * La tua attuale lista della spesa è vuota.
         * La tua attuale lista della spesa è composta da 45 articoli per un importo totale di 123 euro.
         * 
         * ATTENZIONE: l'importo del tuo ordine NON raggiunge il minimo richiesto pari a 123 euro.
         * 
         * Per ordinare, collegarsi a: http://www.gestigas.org/e3g/?prefix=mantogas
         */        

        // Prepara parte di messaggio centrale con elenco fornitori
        $msg_centro = "";
        foreach ( $qu_fornitori as $qu_fornitore ) {
            // Esempio: "- Eugea / Ecologia Urbana, fino al 28 febbraio (6 articoli)"
            $msg_centro .=
                "- " . $qu_fornitore["descrizione"] . " (" . $qu_fornitore["n_articoli"] . " articol" . ( $qu_fornitore["n_articoli"]==1 ? "o" : "i" ) . ")\n";
        }
        // Parte di messaggio finale
        $msg_fine = "\nPer ordinare, collegarsi a: " . P4A_APPLICATION_URL . "?prefix=$p4a->e3g_prefix";
                
        // Invia la mail ad ogni utente attivo
        $qu_utenti = $db->getAll( 
            "SELECT u.descrizione, u.email, " .
            "       SUM( c.qta ) AS qta, SUM( c.prezzoven * c.qta ) AS importo " .
            "  FROM " . $p4a->e3g_prefix . "anagrafiche AS u " .
            "       LEFT JOIN " . $p4a->e3g_prefix . "carrello AS c ON c.codutente = u.codice " .
            " WHERE u.tipocfa = 'C' AND u.tipoutente <> 'A' AND u.stato = 1 " .
         " GROUP BY u.descrizione, u.email " );

        $n_invii = 0;
        foreach ( $qu_utenti as $qu_utente ) {
            // Parte di messaggio iniziale
            $msg_inizio = 
                "Salve " . $qu_utente["descrizione"] . ",\n\n" .
                "il " . e3g_format_mysql_data( $qu_fornitore["datafine"] ) . " si chiudera' il periodo d'ordine nei confronti dei seguenti fornitori:\n\n";

            // Parte di messaggio coi dati sulla lista della spesa
            $qta = (integer) $qu_utente[ "qta" ];
            $importo = (double) $qu_utente[ "importo" ];
     
            if ( $qta == 0 )
                $msg_spesa = "\nLa tua attuale lista della spesa e' vuota.\n";
            else {
                $msg_spesa = "\nLa tua attuale lista della spesa e' composta da $qta articol" . ( $qta==1 ? "o" : "i" ) . 
                    " per un importo totale di " . $importo . " euro.\n";
                if ( $p4a->e3g_azienda_ordine_minimo > 0 and $importo < $p4a->e3g_azienda_ordine_minimo ) 
                    $msg_spesa .= "\nATTENZIONE: l'importo del tuo ordine NON raggiunge il minimo richiesto pari a $p4a->e3g_azienda_ordine_minimo euro.\n" ;
            }

            if ( !e3g_invia_email( $oggetto, 
                                   $msg_inizio . $msg_centro . $msg_spesa . $msg_fine, 
                                   $qu_utente["email"], $qu_utente["descrizione"] ) ) 
                e3g_debug( "Si sono verificati errori durante l'invio a: " . $qu_utente["descrizione"] ); 
            $n_invii++; 
        }
        
        // Aggiorna _aziende con la data odierna per evitare ulteriore spedizione
        $db->query( "UPDATE _aziende " .
            "   SET notifica_chiusura_data = CURDATE() " .
            " WHERE prefix = '" . $p4a->e3g_prefix . "'" );

        return $n_invii;  // TODO Però potrebbero essersi verificati degli errori
    }
    else
        return false;  // Niente da notificare
}


//------------------------------------------------------------------------------
// Notifica la lista della spesa il giorno successivo alla chiusura dell'ordine
//------------------------------------------------------------------------------
function e3g_notifica_lista_spesa() {
    /*TODO
     * 
     * ************************ DA TERMINARE ***********************************
     * 
ALTER TABLE _aziende ADD notifica_lista_spesa CHAR( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE _aziende ADD notifica_lista_spesa_data DATE NULL DEFAULT '2000-01-01';
*/    
    $p4a =& p4a::singleton();
    $db =& p4a_db::singleton();
    
    // Se ieri si è CHIUSO almeno un ordine...
    $qu_fornitori = $db->getAll( 
        "SELECT f.descrizione, fp.datafine, az.notifica_chiusura_gg, " .
        "       COUNT( a.codice ) AS n_articoli " .
        "  FROM " . $p4a->e3g_prefix . "fornitoreperiodo AS fp " . 
        "       JOIN " . $p4a->e3g_prefix . "anagrafiche AS f ON f.codice = fp.fornitore AND f.stato = 1 " . 
        "       LEFT JOIN " . $p4a->e3g_prefix . "articoli AS a ON a.centrale = f.codice AND a.stato = 1 " .
        "       JOIN _aziende AS az ON az.prefix = '" . $p4a->e3g_prefix . "'" .

        "  WHERE " . e3g_where_ordini_aperti("fp") .
        "    AND DATE_SUB( fp.datafine, INTERVAL az.notifica_chiusura_gg DAY ) = CURDATE() " .

      " GROUP BY f.descrizione, fp.datafine, az.notifica_chiusura_gg " .
      " ORDER BY f.descrizione" );  
    
    if ( $qu_fornitori ) {
        $oggetto = $p4a->e3g_azienda_rag_soc . ", chiusura ordine tra " . $qu_fornitori[0]["notifica_chiusura_gg"] . " giorn" .
            ( $qu_fornitori[0]["notifica_chiusura_gg"]==1 ? "o" : "i" );

        /* OGGETTO: Manto-GAS, lista della spesa
         * 
         * Salve Mario Rossi,
         * 
         * il 28/02/2009 si e' chiuso il periodo d'ordine nei confronti dei seguenti fornitori:
         * 
         * - Az. Agr. Gozzi Cesare e Franco (1 articolo)
         * - Eugea / Ecologia Urbana (6 articoli)
         * 
         * La tua lista della spesa è vuota.
         * La tua attuale lista della spesa è composta da 45 articoli per un importo totale di 123 euro; ecco il dettaglio:
         * 
         * [...]
         */        

        // Prepara parte di messaggio centrale con elenco fornitori
        $msg_centro = "";
        foreach ( $qu_fornitori as $qu_fornitore ) {
            // Esempio: "- Eugea / Ecologia Urbana, fino al 28 febbraio (6 articoli)"
            $msg_centro .=
                "- " . $qu_fornitore["descrizione"] . " (" . $qu_fornitore["n_articoli"] . " articol" . ( $qu_fornitore["n_articoli"]==1 ? "o" : "i" ) . ")\n";
        }
        // Parte di messaggio finale
        $msg_fine = "\nPer ordinare, collegarsi a: " . P4A_APPLICATION_URL . "?prefix=$p4a->e3g_prefix";
                
        // Invia la mail ad ogni utente attivo
        $qu_utenti = $db->getAll( 
            "SELECT u.descrizione, u.email, " .
            "       SUM( c.qta ) AS qta, SUM( c.prezzoven * c.qta ) AS importo " .
            "  FROM " . $p4a->e3g_prefix . "anagrafiche AS u " .
            "       LEFT JOIN " . $p4a->e3g_prefix . "carrello AS c ON c.codutente = u.codice " .
            " WHERE u.tipocfa = 'C' AND u.tipoutente <> 'A' AND u.stato = 1 " .
         " GROUP BY u.descrizione, u.email " );

        $n_invii = 0;
        foreach ( $qu_utenti as $qu_utente ) {
            // Parte di messaggio iniziale
            $msg_inizio = 
                "Salve " . $qu_utente["descrizione"] . ",\n\n" .
                "il " . e3g_format_mysql_data( $qu_fornitore["datafine"] ) . " si chiudera' il periodo d'ordine nei confronti dei seguenti fornitori:\n\n";

            // Parte di messaggio coi dati sulla lista della spesa
            $qta = (integer) $qu_utente[ "qta" ];
            $importo = (double) $qu_utente[ "importo" ];
     
            if ( $qta == 0 )
                $msg_spesa = "\nLa tua attuale lista della spesa e' vuota.\n";
            else 
                $msg_spesa = "\nLa tua attuale lista della spesa e' composta da $qta articol" . ( $qta==1 ? "o" : "i" ) . 
                    " per un importo totale di " . $importo . " euro.\n";

            if ( !e3g_invia_email( $oggetto, 
                                   $msg_inizio . $msg_centro . $msg_spesa . $msg_fine, 
                                   $qu_utente["email"], $qu_utente["descrizione"] ) ) 
                e3g_debug( "Si sono verificati errori durante l'invio a: " . $qu_utente["descrizione"] ); 
            $n_invii++; 
        }
        
        // Aggiorna _aziende con la data odierna per evitare ulteriore spedizione
        $db->query( "UPDATE _aziende " .
            "   SET notifica_lista_spesa_data = CURDATE() " .
            " WHERE prefix = '" . $p4a->e3g_prefix . "'" );

        return $n_invii;  // TODO Però potrebbero essersi verificati degli errori
    }
    else
        return false;  // Niente da notificare
}


//------------------------------------------------------------------------------
// Notifiche movimenti di cassa da validare
//------------------------------------------------------------------------------
function e3g_notifica_mov_cassa() {
    $p4a =& p4a::singleton();
    $db =& p4a_db::singleton();

    // Se ci sono movimenti in attesa di validazione...
    $qu_movimenti_cassa = $db->getAll( 
        "SELECT c.data_mov, c.importo, a.descrizione AS desc_utente " .
        "  FROM _cassa c JOIN " . $p4a->e3g_prefix . "anagrafiche a ON c.id_utente_rif = a.idanag " .
        " WHERE c.prefix = '" . $p4a->e3g_prefix . "' " .
        "   AND c.validato = 0 " . 
      "ORDER BY c.data_mov " );
    
    if ( $qu_movimenti_cassa ) {
        $oggetto = $p4a->e3g_azienda_rag_soc . ", movimenti di cassa in attesa";

        /* OGGETTO: Manto-GAS, movimenti di cassa in attesa
         * 
         * Salve Mario Rossi, cassiere di Manto-GAS,
         * ci sono movimenti di cassa in attesa di essere validati:
         * 
         * - 20/12/2009 versamento di 25 euro da parte di Luca Verdi 
         * - 03/01/2010 versamento di 90 euro da parte di Vittorio Bianchi 
         * - 09/01/2010 prelievo di 10 euro da parte di Fabio Moretti 
         * 
         * Per validarli, collegarsi a: http://www.gestigas.org/e3g/?prefix=mantogas
         */        

        // Prepara parte di messaggio centrale con elenco movimenti
        $msg_centro = "";
        foreach ( $qu_movimenti_cassa as $qu_movimento_cassa ) {
            // Esempio: "- 20/12/2009 versamento di 25 euro da parte di Luca Verdi"
            $msg_centro .=
                "- " . e3g_format_mysql_data( $qu_movimento_cassa["data_mov"] ) . ( $qu_movimento_cassa["importo"]>=0 ? " versamento di " : " prelievo di " ) .
                $qu_movimento_cassa["importo"] . " euro da parte di " . $qu_movimento_cassa["desc_utente"] . "\n";
        }
        // Parte di messaggio finale
        $msg_fine = "\nPer validarli, collegarsi a: " . P4A_APPLICATION_URL . "?prefix=$p4a->e3g_prefix";
                
        // Invia la mail ad ogni cassiere attivo
        $qu_utenti = $db->getAll( 
            "SELECT descrizione, email FROM " . $p4a->e3g_prefix . "anagrafiche " .
            " WHERE cassiere = 1 AND stato = 1 " );

        $n_invii = 0;
        foreach ( $qu_utenti as $qu_utente ) {
            // Parte di messaggio iniziale
            $msg_inizio = 
                "Salve " . $qu_utente["descrizione"] . ", cassiere di " . $p4a->e3g_azienda_rag_soc . ",\n\n" .
                "ci sono movimenti di cassa in attesa di essere validati:\n\n";

            if ( !e3g_invia_email( $oggetto, 
                                   $msg_inizio . $msg_centro . $msg_fine, 
                                   $qu_utente["email"], $qu_utente["descrizione"] ) ) 
                e3g_debug( "Si sono verificati errori durante l'invio a: " . $qu_utente["descrizione"] );
            $n_invii++; 
        }
        
        // Aggiorna _aziende con la data odierna per evitare ulteriore spedizione
        $db->query( "UPDATE _aziende " .
            "   SET notifica_mov_cassa_data = CURDATE() " .
            " WHERE prefix = '" . $p4a->e3g_prefix . "'" );
            
        return $n_invii;  // TODO Però potrebbero essersi verificati degli errori
    }
    else
        return false;  // Niente da notificare
}


?>