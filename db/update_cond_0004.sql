ALTER TABLE _aziende ADD ordine_minimo DOUBLE DEFAULT '0' NOT NULL;
ALTER TABLE _aziende ADD gestione_luoghi_cons CHAR( 1 ) DEFAULT '0' NOT NULL;

ALTER TABLE _aziende ADD tipo_gestione_prezzi INT DEFAULT '0' NOT NULL;
ALTER TABLE _aziende ADD mostra_prezzo_sorgente CHAR( 1 ) DEFAULT '1' NOT NULL;

ALTER TABLE _aziende ADD prezzi_mag_fissa DOUBLE DEFAULT '0' NOT NULL;
ALTER TABLE _aziende ADD prezzi_mag_perc DOUBLE DEFAULT '0' NOT NULL;

ALTER TABLE _aziende ADD ospite INT DEFAULT '0' NOT NULL;

CREATE TABLE IF NOT EXISTS _luoghi_cons ( id_luogo_cons INT UNSIGNED NOT NULL , prefix VARCHAR( 20 ) NOT NULL , descrizione VARCHAR( 50 ) NOT NULL , PRIMARY KEY ( id_luogo_cons ) , INDEX ( prefix ) );

DROP TABLE IF EXISTS carrello_seq;

UPDATE _config SET dbver = '0004';