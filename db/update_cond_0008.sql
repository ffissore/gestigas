CREATE TABLE IF NOT EXISTS _mailing_list ( id_mailing_list int(10) unsigned NOT NULL, prefix varchar(20) NOT NULL, tipo_lista tinyint(4) NOT NULL, email varchar(50) default NULL, pop_server varchar(30) default NULL, pop_port tinyint(4) default 110 NOT NULL, pop_user varchar(30) default NULL, pop_password varchar(30) default NULL, reply_to tinyint(4) NOT NULL, prefisso varchar(20) default NULL, modello_header varchar(512) default NULL, modello_footer varchar(512) default NULL, PRIMARY KEY  (id_mailing_list) );

ALTER TABLE _aziende ADD codice_fiscale VARCHAR( 16 ) NULL ;
ALTER TABLE _aziende ADD mailing_list CHAR( 1 ) NOT NULL DEFAULT '0';

ALTER TABLE _aziende ADD notifica_apertura CHAR( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE _aziende ADD notifica_apertura_data DATE NULL;
ALTER TABLE _aziende ADD notifica_chiusura CHAR( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE _aziende ADD notifica_chiusura_data DATE NULL;
ALTER TABLE _aziende ADD notifica_chiusura_gg TINYINT(4) NOT NULL DEFAULT '3';


UPDATE _config SET dbver = '0008';