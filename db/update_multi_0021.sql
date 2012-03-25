ALTER TABLE [PREFIX]articoli ADD prezzo_mag_perc_libera DOUBLE NOT NULL DEFAULT 0 AFTER prezzoacq;


UPDATE _aziende SET dbver = '0021' WHERE prefix = '[PREFIX]';
