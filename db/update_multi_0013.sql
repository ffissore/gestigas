UPDATE [PREFIX]doccampireport SET campo = LOWER(campo);

INSERT INTO [PREFIX]doccampireport VALUES("5","00024","totale","Importo","5");
INSERT INTO [PREFIX]doccampireport VALUES("11","00023","totale","Importo","5");

UPDATE _aziende SET dbver = '0013' WHERE prefix = '[PREFIX]';

