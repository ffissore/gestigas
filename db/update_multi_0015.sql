ALTER TABLE [PREFIX]movcont CHANGE numdocum numdocum VARCHAR(10) NOT NULL DEFAULT '0';
DROP TABLE [PREFIX]doct_estraz;

UPDATE _aziende SET dbver = '0015' WHERE prefix = '[PREFIX]';
