ALTER TABLE `_aziende` ADD `iban` VARCHAR(27) NULL AFTER `cin` ;

UPDATE _config SET dbver = '0007';