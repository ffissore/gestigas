ALTER TABLE _aziende ADD gestione_cassa CHAR( 1 ) NOT NULL default '0';
ALTER TABLE _aziende ADD acquista_se_credito_insufficiente CHAR( 1 ) NOT NULL default '0';

CREATE TABLE IF NOT EXISTS _cassa ( id_cassa int(10) unsigned NOT NULL default '0', prefix varchar(20) NOT NULL default '_', data_mov date default NULL, importo double NOT NULL default '0', note text, id_utente_crea int(10) unsigned default NULL, data_ins datetime default NULL, data_agg timestamp ON UPDATE CURRENT_TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP, validato char(1) NOT NULL default '0', id_utente_rif int(10) unsigned, id_causale_mov_cassa int(10) unsigned NOT NULL,   PRIMARY KEY  (id_cassa) );

CREATE TABLE IF NOT EXISTS _causali_mov_cassa ( id_causale_mov_cassa int(11) NOT NULL, causale_mov_cassa varchar(50) NOT NULL, PRIMARY KEY (id_causale_mov_cassa) );

INSERT INTO _causali_mov_cassa ( id_causale_mov_cassa, causale_mov_cassa ) VALUES( 0, ' Non indicata' );
INSERT INTO _causali_mov_cassa ( id_causale_mov_cassa, causale_mov_cassa ) VALUES( 1, 'Spese ed interessi bancari' );
INSERT INTO _causali_mov_cassa ( id_causale_mov_cassa, causale_mov_cassa ) VALUES( 2, 'GAS: pagamento a fornitore' );
INSERT INTO _causali_mov_cassa ( id_causale_mov_cassa, causale_mov_cassa ) VALUES( 3, 'Utente: acquisto articoli' );
INSERT INTO _causali_mov_cassa ( id_causale_mov_cassa, causale_mov_cassa ) VALUES( 4, 'Utente: ricarica conto' );
INSERT INTO _causali_mov_cassa ( id_causale_mov_cassa, causale_mov_cassa ) VALUES( 5, 'Utente: quota associativa' );
INSERT INTO _causali_mov_cassa ( id_causale_mov_cassa, causale_mov_cassa ) VALUES( 6, 'Altre entrate' );
INSERT INTO _causali_mov_cassa ( id_causale_mov_cassa, causale_mov_cassa ) VALUES( 7, 'Altre uscite' );

ALTER TABLE _aziende ADD notifica_mov_cassa CHAR( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE _aziende ADD notifica_mov_cassa_data DATE NULL DEFAULT '2000-01-01';


UPDATE _config SET dbver = '0010';