ALTER TABLE _aziende ADD path_logo VARCHAR( 100 ) NULL ;

UPDATE _config SET dbver = '0006';