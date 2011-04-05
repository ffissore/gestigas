ALTER TABLE [PREFIX]docr ADD delta_prezzo DOUBLE NOT NULL DEFAULT 0;

ALTER TABLE [PREFIX]articoli ADD bio CHAR(1) NOT NULL DEFAULT '0';
ALTER TABLE [PREFIX]articoli ADD ingredienti TEXT NULL;
ALTER TABLE [PREFIX]articoli ADD data_agg_ing TIMESTAMP NULL;

ALTER TABLE [PREFIX]anagrafiche ADD db_source_page_limit TINYINT UNSIGNED NOT NULL DEFAULT 10;
ALTER TABLE [PREFIX]anagrafiche ADD desc_agg VARCHAR(25);
ALTER TABLE [PREFIX]anagrafiche ADD modifica_ingredienti CHAR( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE [PREFIX]anagrafiche ADD filtro_ingredienti CHAR( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE [PREFIX]anagrafiche ADD ingredienti_escludi TEXT NULL;


UPDATE _aziende SET dbver = '0018' WHERE prefix = '[PREFIX]';
