ALTER TABLE _aziende ADD prezzo_vendita_modificabile CHAR(1) NOT NULL DEFAULT '0';


UPDATE _config SET dbver = '0012';