ALTER TABLE _aziende ADD last_login DATETIME;
ALTER TABLE _aziende ADD n_login INT DEFAULT '0' NOT NULL;

UPDATE _config SET dbver = '0003';