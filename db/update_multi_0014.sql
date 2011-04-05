ALTER TABLE [PREFIX]articoli CHANGE progetto progetto VARCHAR(10);

CREATE TABLE [PREFIX]progetti (codice VARCHAR (10), descrizione VARCHAR (50), centrale VARCHAR (15), ordine INT DEFAULT 10) ;
INSERT INTO [PREFIX]progetti (codice, descrizione, centrale, ordine) VALUES ('00', 'Non Indicato', '', 0);

ALTER TABLE [PREFIX]movmagr CHANGE codanag codanag VARCHAR(15);
ALTER TABLE [PREFIX]movmagr CHANGE qta qta DOUBLE;

ALTER TABLE [PREFIX]docr CHANGE quantita quantita DOUBLE;
ALTER TABLE [PREFIX]docr CHANGE quantita2 quantita2 DOUBLE;

ALTER TABLE [PREFIX]carrello CHANGE qta qta DOUBLE;
ALTER TABLE [PREFIX]carrello CHANGE qtaconsegnata qtaconsegnata DOUBLE;
ALTER TABLE [PREFIX]carrello CHANGE codutente codutente VARCHAR(15);

ALTER TABLE [PREFIX]doct CHANGE codclifor codclifor VARCHAR(15);

ALTER TABLE [PREFIX]movcont CHANGE codclifor codclifor VARCHAR(15);  


UPDATE _aziende SET dbver = '0014' WHERE prefix = '[PREFIX]';
