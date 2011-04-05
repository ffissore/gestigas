UPDATE [PREFIX]anagrafiche SET tipocfa='C' WHERE  tipoutente='A';

ALTER TABLE [PREFIX]anagrafiche ADD last_login DATETIME;
ALTER TABLE [PREFIX]anagrafiche ADD n_login INT DEFAULT '0' NOT NULL;

UPDATE _aziende SET dbver = '0010' WHERE prefix = '[PREFIX]';