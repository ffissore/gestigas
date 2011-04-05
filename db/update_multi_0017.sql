UPDATE [PREFIX]anagrafiche SET segnocontabile ='D' WHERE  idanag=1;

ALTER TABLE [PREFIX]anagrafiche ADD data_nascita DATE NULL;
ALTER TABLE [PREFIX]anagrafiche ADD luogo_nascita VARCHAR( 30 ) NULL;
ALTER TABLE [PREFIX]anagrafiche ADD mailing_list CHAR( 1 ) NOT NULL DEFAULT '1';

CREATE TABLE [PREFIX]etichette (idtable INT NOT NULL ,idanag INT NOT NULL ,barcode VARCHAR( 13 ) NOT NULL, codice VARCHAR( 20 ) NOT NULL, descrizione VARCHAR( 100 ) NULL, prezzoven double default NULL, iva VARCHAR(3) default NULL, stampato CHAR(1)  NOT NULL default 'N') ;
 
UPDATE _aziende SET dbver = '0017' WHERE prefix = '[PREFIX]';
