UPDATE [PREFIX]anagrafiche SET id_luogo_cons = 0 WHERE id_luogo_cons IS NULL;

ALTER TABLE [PREFIX]anagrafiche CHANGE id_luogo_cons id_luogo_cons INT( 11 ) NULL DEFAULT '0';
ALTER TABLE [PREFIX]anagrafiche ADD cellulare VARCHAR( 20 ) NULL AFTER fax;
ALTER TABLE [PREFIX]anagrafiche ADD comune VARCHAR( 20 ) NULL AFTER cap; 

ALTER TABLE [PREFIX]carrello CHANGE qta_agg qta_agg DOUBLE NOT NULL DEFAULT 0;

UPDATE [PREFIX]doctipidoc SET descrizione = 'Consegna a utente' WHERE codice = '00023';
UPDATE [PREFIX]doctipidoc SET descrizione = 'Ordine a fornitore' WHERE codice = '00024';

TRUNCATE TABLE [PREFIX]doccampireport;
INSERT INTO [PREFIX]doccampireport
 ( idtable, codtipodoc, campo, nomecampo, ordine ) VALUES 
	( 1, '00023', 'fornitore',   'Fornitore', 10), 
	( 2, '00023', 'quantita',    'Q.tà', 20), 
	( 3, '00023', 'descrizione', 'Descrizione', 30), 
	( 4, '00023', 'prezzo',      'Prezzo', 40), 
	( 5, '00023', 'totale',      'Importo', 50),
	( 6, '00024', 'quantita',    'Q.tà', 10), 
	( 7, '00024', 'descrizione', 'Descrizione', 20), 
	( 8, '00024', 'prezzo',      'Prezzo', 30), 
	( 9, '00024', 'totale',      'Importo', 40);


UPDATE _aziende SET dbver = '0020' WHERE prefix = '[PREFIX]';
