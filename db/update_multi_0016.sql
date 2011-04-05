UPDATE [PREFIX]anagrafiche SET descrizione =' Non Indicato' WHERE  idanag=0;
INSERT INTO [PREFIX]doctipidoc VALUES('00000',' Non Indicato','NONIND','00','','N','N','','','','N','N','','','','','','','','','','','0','','','0','','','0','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','0','0','0','','','','0','0','N','0','','','','F','');

UPDATE _aziende SET dbver = '0016' WHERE prefix = '[PREFIX]';
