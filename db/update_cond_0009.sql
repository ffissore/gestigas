ALTER TABLE _aziende CHANGE notifica_apertura_data notifica_apertura_data DATE NOT NULL DEFAULT '2000-01-01'
ALTER TABLE _aziende CHANGE notifica_chiusura_data notifica_chiusura_data DATE NOT NULL DEFAULT '2000-01-01'

ALTER TABLE _aziende ADD notifica_lista_spesa CHAR( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE _aziende ADD notifica_lista_spesa_data DATE NULL DEFAULT '2000-01-01';

ALTER TABLE _aziende ADD n_doc_ord_fornitori INT DEFAULT '0' NOT NULL;
ALTER TABLE _aziende ADD n_doc_cons_utenti INT DEFAULT '0' NOT NULL;


UPDATE _config SET dbver = '0009';