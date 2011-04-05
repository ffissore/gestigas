ALTER TABLE [PREFIX]docr CHANGE CodIva codiva CHAR(3);

ALTER TABLE [PREFIX]carrello ADD qta_agg INT DEFAULT 0;

ALTER TABLE [PREFIX]doct ADD data_ins datetime default NULL;
ALTER TABLE [PREFIX]doct ADD idanag INT;

DROP TABLE IF EXISTS [PREFIX]anag_id_seq;
DROP TABLE IF EXISTS [PREFIX]articoloperiodo_id;
DROP TABLE IF EXISTS [PREFIX]articoloperiodo_id_seq;
DROP TABLE IF EXISTS [PREFIX]carrello_seq;
DROP TABLE IF EXISTS [PREFIX]doccampi_seq;
DROP TABLE IF EXISTS [PREFIX]doccampidoc_seq_seq;
DROP TABLE IF EXISTS [PREFIX]doccampireport_id_seq;
DROP TABLE IF EXISTS [PREFIX]fornitoreperiodo_id_seq;

UPDATE _aziende SET dbver = '0012' WHERE prefix = '[PREFIX]';

