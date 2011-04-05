ALTER TABLE [PREFIX]doct CHANGE note note TEXT;

ALTER TABLE [PREFIX]docr CHANGE codutente codutente VARCHAR(15);

ALTER TABLE [PREFIX]anagrafiche ADD COLUMN note_ordine TEXT;
ALTER TABLE [PREFIX]anagrafiche ADD cassiere CHAR( 1 ) NOT NULL DEFAULT '0';

ALTER TABLE [PREFIX]articoli ADD desc_agg TEXT;
ALTER TABLE [PREFIX]articoli ADD gestione_a_peso CHAR( 1 ) DEFAULT '0';


UPDATE _aziende SET dbver = '0019' WHERE prefix = '[PREFIX]';
