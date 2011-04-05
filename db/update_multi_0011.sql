ALTER TABLE [PREFIX]anagrafiche ADD id_luogo_cons INT;

ALTER TABLE [PREFIX]articoli ADD data_ins datetime default NULL;
ALTER TABLE [PREFIX]articoli ADD data_agg timestamp NOT NULL;

UPDATE [PREFIX]articoli SET data_ins = NOW();

UPDATE [PREFIX]articoli SET prezzoacq = prezzoven WHERE prezzoacq IS NULL;

ALTER TABLE [PREFIX]fornitoreperiodo ADD PRIMARY KEY ( idtable );

UPDATE _aziende SET dbver = '0011' WHERE prefix = '[PREFIX]';