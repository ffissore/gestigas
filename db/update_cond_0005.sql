ALTER TABLE _aziende ADD cab VARCHAR(10)  AFTER abi_cab;
ALTER TABLE _aziende CHANGE abi_cab abi VARCHAR(10) ;
ALTER TABLE _aziende ADD conto_corrente VARCHAR(30)  AFTER cab;
ALTER TABLE _aziende ADD cin VARCHAR(1)  AFTER cab;

ALTER TABLE _aziende ADD tipo_documento VARCHAR(3) NOT NULL DEFAULT 'PDF';
UPDATE _aziende SET tipo_documento = 'PDF';
ALTER TABLE _aziende ADD path_documento VARCHAR( 100 ) NULL ;

UPDATE _config SET dbver = '0005';