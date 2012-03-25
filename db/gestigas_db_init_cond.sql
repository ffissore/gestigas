CREATE TABLE IF NOT EXISTS _aziende (
	id_azienda int(10) NOT NULL,
	rag_soc varchar(30) NOT NULL,
	prefix varchar(20) NOT NULL, 
	admin_id_anagrafica int(10) default NULL,
	admin_desc VARCHAR( 50 ),
	 admin_email varchar(50) default NULL, 
	dbver VARCHAR(20) DEFAULT '0000', 
	indirizzo VARCHAR(50), 
	cap VARCHAR(5), 
	localita VARCHAR(50), 
	provincia VARCHAR(20), 
	telefono VARCHAR(20), 
	fax VARCHAR(20), 
	web VARCHAR(50), 
	email VARCHAR(50), 
	banca VARCHAR(50), 
	agenzia VARCHAR(50), 
	abi VARCHAR(10), 
	cab VARCHAR(10), 
	cin VARCHAR(1), 
	conto_corrente VARCHAR(30), 
	matricola VARCHAR(20), 
	piva VARCHAR(11), 
	sede VARCHAR(50), 
	stampa_scontrino TINYINT(3) UNSIGNED, 
	baud INT(11) DEFAULT '0', 
	com SMALLINT(6) DEFAULT '0', 
	frm_dett_avvio TINYINT(3) UNSIGNED, 
	etichette_max INT(11) DEFAULT '0', 
	ean_azienda VARCHAR(5), 
	anno_contabile VARCHAR(4), 
	dettaglio_causale_mov_mag CHAR(2), 
	gg_cod_doc_ordine VARCHAR(5), 
	gg_cod_doc_ordine_fam VARCHAR(5), 
	eg_cod_doc_scontrino VARCHAR(5), 
	n_decimali_prezzi DOUBLE NOT NULL DEFAULT '2', 
	etichette_path  VARCHAR(100), 
	show_new_account CHAR(1) DEFAULT '0',
	n_clienti SMALLINT DEFAULT '0' NOT NULL, 
	n_fornitori SMALLINT DEFAULT '0' NOT NULL, 
	n_articoli SMALLINT DEFAULT '0' NOT NULL, 
	data_inizio DATE NOT NULL,
	data_agg timestamp, 
	last_login DATETIME, 
	n_login INT DEFAULT '0' NOT NULL,	
	ordine_minimo DOUBLE DEFAULT '0' NOT NULL, 
	gestione_luoghi_cons CHAR( 1 ) DEFAULT '0' NOT NULL, 
	tipo_gestione_prezzi INT DEFAULT '0' NOT NULL,
	mostra_prezzo_sorgente CHAR( 1 ) DEFAULT '1' NOT NULL, 
	prezzi_mag_fissa DOUBLE DEFAULT '0' NOT NULL, 
	prezzi_mag_perc DOUBLE DEFAULT '0' NOT NULL,
	ospite INT DEFAULT '0' NOT NULL,  
	tipo_documento VARCHAR(3) NOT NULL DEFAULT 'PDF', 
	path_documento VARCHAR( 100 ) NULL,
	path_logo VARCHAR( 100 ) NULL, 
	iban VARCHAR(27),
	codice_fiscale VARCHAR( 16 ) NULL, 
	mailing_list CHAR( 1 ) NOT NULL DEFAULT '0',
	notifica_apertura CHAR( 1 ) NOT NULL DEFAULT '0', 
	notifica_apertura_data DATE NOT NULL DEFAULT '2000-01-01',
	notifica_chiusura CHAR( 1 ) NOT NULL DEFAULT '0', 
	notifica_chiusura_data DATE NOT NULL DEFAULT '2000-01-01',
	notifica_chiusura_gg TINYINT(4) NOT NULL DEFAULT '3',
	notifica_lista_spesa CHAR( 1 ) NOT NULL DEFAULT '0', 
	notifica_lista_spesa_data DATE NULL DEFAULT '2000-01-01',
	n_doc_ord_fornitori INT DEFAULT '0' NOT NULL, 
	n_doc_cons_utenti INT DEFAULT '0' NOT NULL,
	gestione_cassa CHAR( 1 ) NOT NULL default '0',
	acquista_se_credito_insufficiente CHAR( 1 ) NOT NULL default '0',
    notifica_mov_cassa CHAR( 1 ) NOT NULL DEFAULT '0',
    notifica_mov_cassa_data DATE NULL DEFAULT '2000-01-01',
	notifica_apertura_ref CHAR(1) NOT NULL DEFAULT '0',
	notifica_apertura_ref_data DATE NULL DEFAULT '2000-01-01',
	notifica_apertura_ref_gg TINYINT(4) NOT NULL DEFAULT '14',
	PRIMARY KEY (id_azienda),
	UNIQUE KEY prefix (prefix),
	UNIQUE KEY rag_soc (rag_soc) );

CREATE TABLE IF NOT EXISTS _config (dbver varchar(20));
DELETE FROM _config; 
INSERT INTO _config VALUES("0011");

CREATE TABLE IF NOT EXISTS _login_tipo_utente (codice char(2) DEFAULT '0' ,descrizione varchar(25) DEFAULT '0' );
DELETE FROM _login_tipo_utente; 
INSERT INTO _login_tipo_utente VALUES("A","Amministratore globale");
INSERT INTO _login_tipo_utente VALUES("AS","Amministratore");
INSERT INTO _login_tipo_utente VALUES("R","Referente");
INSERT INTO _login_tipo_utente VALUES("U","Utente");
INSERT INTO _login_tipo_utente VALUES("G","Visitatore ospite");
INSERT INTO _login_tipo_utente VALUES("F","Fornitore");
INSERT INTO _login_tipo_utente VALUES("AD","Aderente GAS");

CREATE TABLE IF NOT EXISTS _anagrafiche_stato (codice int(11) ,descrizione varchar(30) );
DELETE FROM _anagrafiche_stato ;
INSERT INTO _anagrafiche_stato VALUES("0","In attesa");
INSERT INTO _anagrafiche_stato VALUES("1","Abilitato");
INSERT INTO _anagrafiche_stato VALUES("2","Disabilitato");

CREATE TABLE IF NOT EXISTS _luoghi_cons ( 
	id_luogo_cons INT UNSIGNED NOT NULL , 
	prefix VARCHAR( 20 ) NOT NULL , 
	descrizione VARCHAR( 50 ) NOT NULL,
	PRIMARY KEY ( id_luogo_cons ) , INDEX ( prefix ) );
DELETE FROM _luoghi_cons;
INSERT INTO _luoghi_cons ( id_luogo_cons, prefix, descrizione ) VALUES ( '0', '', ' Non indicato' );

CREATE TABLE IF NOT EXISTS _mailing_list ( id_mailing_list int(10) unsigned NOT NULL, prefix varchar(20) NOT NULL, tipo_lista tinyint(4) NOT NULL, email varchar(50) default NULL, pop_server varchar(30) default NULL, pop_port tinyint(4) default 110 NOT NULL, pop_user varchar(30) default NULL, pop_password varchar(30) default NULL, reply_to tinyint(4) NOT NULL, prefisso varchar(20) default NULL, modello_header varchar(512) default NULL, modello_footer varchar(512) default NULL, PRIMARY KEY  (id_mailing_list) );

CREATE TABLE IF NOT EXISTS _cassa ( id_cassa int(10) unsigned NOT NULL default '0', prefix varchar(20) NOT NULL default '_', data_mov date default NULL, importo double NOT NULL default '0', note text, id_utente_crea int(10) unsigned default NULL, data_ins datetime default NULL, data_agg timestamp ON UPDATE CURRENT_TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP, validato char(1) NOT NULL default '0', id_utente_rif int(10) unsigned, id_causale_mov_cassa int(10) unsigned NOT NULL,   PRIMARY KEY  (id_cassa) );

CREATE TABLE IF NOT EXISTS _causali_mov_cassa ( 
	id_causale_mov_cassa int(11) NOT NULL, 
	causale_mov_cassa varchar(50) NOT NULL,
	segno TINYINT(4) NOT NULL DEFAULT '1',
	tipo_rif CHAR(1) NOT NULL DEFAULT '0', 
	PRIMARY KEY (id_causale_mov_cassa) );
DELETE FROM _causali_mov_cassa;
INSERT INTO _causali_mov_cassa ( id_causale_mov_cassa, causale_mov_cassa, segno, tipo_rif ) VALUES( 0, ' Non indicata', 0, '0' );
INSERT INTO _causali_mov_cassa ( id_causale_mov_cassa, causale_mov_cassa, segno, tipo_rif ) VALUES( 1, 'Spese ed interessi bancari', -1, '0' );
INSERT INTO _causali_mov_cassa ( id_causale_mov_cassa, causale_mov_cassa, segno, tipo_rif ) VALUES( 2, 'GAS: pagamento a fornitore', -1, 'F' );
INSERT INTO _causali_mov_cassa ( id_causale_mov_cassa, causale_mov_cassa, segno, tipo_rif ) VALUES( 3, 'Utente: acquisto articoli', 1, 'C' );
INSERT INTO _causali_mov_cassa ( id_causale_mov_cassa, causale_mov_cassa, segno, tipo_rif ) VALUES( 4, 'Utente: ricarica conto', 1, 'C' );
INSERT INTO _causali_mov_cassa ( id_causale_mov_cassa, causale_mov_cassa, segno, tipo_rif ) VALUES( 5, 'Utente: quota associativa', 1, 'C' );
INSERT INTO _causali_mov_cassa ( id_causale_mov_cassa, causale_mov_cassa, segno, tipo_rif ) VALUES( 6, 'Altre entrate', 1, '0' );
INSERT INTO _causali_mov_cassa ( id_causale_mov_cassa, causale_mov_cassa, segno, tipo_rif ) VALUES( 7, 'Altre uscite', -1, '0' );
