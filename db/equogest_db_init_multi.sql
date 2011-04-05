-- 
-- Struttura della tabella '[PREFIX]aliquoteiva'
-- 

CREATE TABLE [PREFIX]aliquoteiva (
  codice char(3) NOT NULL default '',
  descrizione varchar(30) default NULL,
  iva double NOT NULL default '0',
  reparto char(3) default NULL,
  PRIMARY KEY  (codice),
  UNIQUE KEY codice (codice),
  KEY aliquoteivaiva (iva)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]aliquoteiva'
-- 

INSERT INTO [PREFIX]aliquoteiva (codice, descrizione, iva, reparto) VALUES 
('04', 'Iva 4 %', 4, ''),
('10', 'Iva 10 %', 10, ''),
('20', 'Iva 20 %', 20, ''),
('00', 'Iva 0', 0, '');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]anagrafiche'
-- 

CREATE TABLE [PREFIX]anagrafiche (
  idanag int(10) NOT NULL default '0',
  codice varchar(15) NOT NULL default '',
  nome varchar(50) default NULL,
  cognome varchar(50) default NULL,
  descrizione varchar(50) default NULL,
  indirizzo varchar(50) default NULL,
  localita varchar(50) default NULL,
  cap varchar(5) default NULL,
  provincia char(20) default NULL,
  destalter1 varchar(30) default NULL,
  destalter2 varchar(30) default NULL,
  telefono varchar(20) default NULL,
  telefono2 varchar(20) default NULL,
  fax varchar(20) default NULL,
  email varchar(50) default NULL,
  www varchar(50) default NULL,
  tipo varchar(50) default NULL,
  tipoutente varchar(2) default NULL,
  note text,
  cf varchar(16) default NULL,
  piva varchar(11) default NULL,
  sconto double default NULL,
  tipocfa char(1) default NULL,
  noprinteti tinyint(3) unsigned default NULL,
  conto char(3) default NULL,
  segnocontabile char(1) default NULL,
  mastro char(1) default NULL,
  `password` varchar(40) default NULL,
  admin char(1) default NULL,
  stato int(11) default '1',
  data_ins datetime default NULL,
  data_agg timestamp NULL default NULL,
  datafine date default NULL,
  datainizio date default NULL,
  last_login datetime default NULL,
  n_login int(11) NOT NULL default '0',
  id_luogo_cons int(11) default NULL,
  PRIMARY KEY  (codice),
  UNIQUE KEY id (idanag),
  KEY codice (codice)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]anagrafiche'
-- 

INSERT INTO [PREFIX]anagrafiche (idanag, codice, nome, cognome, descrizione, indirizzo, localita, cap, provincia, destalter1, destalter2, telefono, telefono2, fax, email, www, tipo, tipoutente, note, cf, piva, sconto, tipocfa, noprinteti, conto, segnocontabile, mastro, password, admin, stato, data_ins, data_agg, datafine, datainizio, last_login, n_login, id_luogo_cons) VALUES 
(0, '00', '', '', ' Non Indicato', '', '', '', '', '', '', '', '', '', '', '', '', 'U', '', '', '', 0, 'A', 0, '', 'A', '', '', '', 1, '2008-04-12 02:04:48', '2008-04-12 02:04:48', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(1, '00000', '', '', 'Vendita al dettaglio', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '', 'D', '', '', '', 1, '0000-00-00 00:00:00', '2006-04-13 14:42:27', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(2, '0.014.00001', '', '', 'Cassa Contante', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '014', 'D', '0', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(3, '0.014.00010', '', '', 'Cassa Assegni', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '014', 'D', '0', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(4, '0.014.00020', '', '', 'Cassa Effetti', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '014', 'D', '0', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(5, '0.014.00030', '', '', 'Cassa Vendite al Banco', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '014', 'D', '0', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(6, '0.016.00001', '', '', 'Ricevute Bancarie', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '016', 'D', '0', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(7, '0.030.00001', '', '', 'Iva C/Acquisti', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '030', 'D', '0', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(8, '1.016.00001', '', '', 'Ricevute Bancarie Passive', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '016', 'A', '1', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(9, '1.016.00010', '', '', 'Contributo CONAI', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '016', 'A', '1', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(10, '1.020.00001', '', '', 'Iva C/Vendite', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '020', 'A', '1', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(11, '1.020.00010', '', '', 'Iva su Corrispettivi', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '020', 'A', '1', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(12, '1.020.00020', '', '', 'Erario C/Iva', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '020', 'A', '1', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(13, '2.001.00001', '', '', 'Merci C/Acquisti', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '001', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(14, '2.001.00010', '', '', 'Trasporti su Acquisti', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '001', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(15, '2.001.00020', '', '', 'Spese Accessorie su Acquisti', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '001', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(16, '2.001.00030', '', '', 'Campionature', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '001', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(17, '2.001.00050', '', '', 'Merci C/Resi da Clienti', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '001', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(18, '2.010.00001', '', '', 'Spese Telefoniche', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '010', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(19, '2.010.00010', '', '', 'Spese Energia Elettrica', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '010', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(20, '2.010.00020', '', '', 'Spese di Pubblicit?', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '010', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(21, '2.010.00030', '', '', 'Spese Gas,Acqua,Riscaldamento', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '010', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(22, '2.010.00040', '', '', 'Smaltimento Rifiuti', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '010', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(23, '2.020.00001', '', '', 'Lavorazioni Esterne', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '020', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(24, '2.030.00001', '', '', 'Spese Consulenze Commerciali', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '030', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(25, '2.030.00010', '', '', 'Spese Consulenze Legali', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '030', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(26, '2.040.00001', '', '', 'Manutenzioni Locali', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '040', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(27, '2.040.00010', '', '', 'Manutenzioni Impianti', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '040', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(28, '2.040.00020', '', '', 'Manutenzioni Mobili', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '040', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(29, '2.040.00030', '', '', 'Manutenzioni Macchine per Ufficio', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '040', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(30, '2.040.00040', '', '', 'Manutenzioni HardWare', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '040', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(31, '2.040.00050', '', '', 'Manutenzioni SoftWare', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '040', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(32, '2.040.00060', '', '', 'Manutenzioni Automezzi', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '040', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(33, '2.050.00001', '', '', 'Provvigioni Agenti', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '050', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(34, '2.060.00001', '', '', 'Assicurazioni Varie', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '060', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(35, '2.070.00001', '', '', 'Affitto locali', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '070', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(36, '2.080.00001', '', '', 'Retribuzioni Dipendenti', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '080', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(37, '2.090.00001', '', '', 'Materiali di Consumo Vario', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '090', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(38, '2.090.00010', '', '', 'Cancelleria', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '090', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(39, '2.090.00020', '', '', 'Stampanti', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '090', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(40, '2.090.00030', '', '', 'Ricambi per Macchine per Ufficio', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '090', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(41, '2.100.00001', '', '', 'Merci Danneggiate o Avariate', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '100', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(42, '2.100.00010', '', '', 'Multe e Ammende', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '100', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(43, '2.100.00020', '', '', 'Altre Spese di Gestione', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '100', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(44, '2.100.00030', '', '', 'Abbuoni Passivi', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '100', 'D', '2', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(45, '3.001.00001', '', '', 'Merci C/Vendite', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '001', 'A', '3', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(46, '3.001.00050', '', '', 'Merci C/Resi a Fornitori', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '001', 'A', '3', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(47, '3.010.00001', '', '', 'Ricavi Vari', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '010', 'A', '3', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(48, '3.010.00010', '', '', 'Abbuoni Attivi', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '010', 'A', '3', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(49, '3.010.00020', '', '', 'Ricavi per Errato Calcolo', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '010', 'A', '3', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(50, '3.010.00030', '', '', 'Prestazioni Professionali', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '010', 'A', '3', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(51, '4.010.00001', '', '', 'Bilancio di Apertura', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '010', 'D', '4', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(52, '4.020.00001', '', '', 'Bilancio di Chiusura', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'S', 0, '020', 'D', '4', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:24', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(53, 'F0002', '', '', 'CTM', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'F', 0, '', 'D', '', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:55', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(54, 'F0003', '', '', 'COMMERCIO ALTERNATIVO', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'F', 0, '', 'D', '', '', '', 1, '0000-00-00 00:00:00', '2006-04-19 10:43:56', '0000-00-00', '0000-00-00', NULL, 0, NULL),
(55, 'ADMIN', '', '', 'Administrator', '', '', '', '', '', '', '', '', '', 'admin', '', '', 'AS', '', '', '', 0, 'U', 0, '', 'A', '', '21232f297a57a5a743894a0e4a801fc3', '', 1, '0000-00-00 00:00:00', '2008-04-12 02:14:17', '0000-00-00', '0000-00-00', NULL, 0, NULL);

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]articoli'
-- 

CREATE TABLE [PREFIX]articoli (
  idarticolo int(11) NOT NULL default '0',
  barcode varchar(50) default NULL,
  codice varchar(20) NOT NULL default '',
  descrizione varchar(100) default NULL,
  prezzoven double default NULL,
  prezzoacq double default NULL,
  codiva char(3) default NULL,
  tipo char(2) default NULL,
  giacenza int(11) default '0',
  centrale varchar(15) default NULL,
  progetto varchar(10) default NULL,
  sconto1 double default NULL,
  sconto2 double default NULL,
  sconto3 double default NULL,
  catmerce varchar(5) default NULL,
  tipoarticolo char(1) default 'A',
  paese varchar(15) default NULL,
  contovendita varchar(15) default NULL,
  contoacquisto varchar(15) default NULL,
  posizione varchar(10) default NULL,
  periodo varchar(30) default NULL,
  um varchar(10) default NULL,
  scortaminima double default NULL,
  pzperconf double default '0',
  qtaminordine double default '0',
  qtaminperfamiglia double default '0',
  um_qta double default NULL,
  stato int(11) default '1',
  data_ins datetime default NULL,
  data_agg timestamp NOT NULL,
  PRIMARY KEY  (codice),
  UNIQUE KEY idarticolo (idarticolo)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]articoli'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]articoloperiodo'
-- 

CREATE TABLE [PREFIX]articoloperiodo (
  idtable int(11) unsigned NOT NULL default '0',
  codice varchar(20) default NULL,
  dalmese int(3) unsigned default '0',
  almese int(3) unsigned default '0'
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]articoloperiodo'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]carrello'
-- 

CREATE TABLE [PREFIX]carrello (
  idriga int(11) NOT NULL default '0',
  `data` varchar(19) default NULL,
  codarticolo varchar(20) default NULL,
  descrizione varchar(100) default NULL,
  qta double default NULL,
  qtaconsegnata double default NULL,
  prezzoven double default NULL,
  username varchar(20) default NULL,
  codcaumov varchar(5) default NULL,
  carscar char(1) default NULL,
  sconto double default NULL,
  codiva char(3) default NULL,
  idsessione varchar(255) default NULL,
  codfornitore varchar(5) default NULL,
  stato char(1) default NULL,
  um varchar(10) default NULL,
  codutente varchar(15) default NULL,
  qta_agg int(11) default '0',
  PRIMARY KEY  (idriga)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]carrello'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]catmerceologica'
-- 

CREATE TABLE [PREFIX]catmerceologica (
  codice varchar(5) NOT NULL default '',
  descrizione varchar(50) default NULL,
  tipo char(2) default NULL,
  PRIMARY KEY  (codice),
  UNIQUE KEY codice (codice)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]catmerceologica'
-- 

INSERT INTO [PREFIX]catmerceologica (codice, descrizione, tipo) VALUES 
('000', 'Non indicato', '00'),
('CAF', 'Caffe', 'AL'),
('BEV', 'Bevande', 'AL'),
('LIB', 'Libri', 'ED'),
('ACC', 'Accessori per la casa', 'AR'),
('DOL', 'Dolci e snack', 'AL');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]contconti'
-- 

CREATE TABLE [PREFIX]contconti (
  codice varchar(4) default NULL,
  descrizione varchar(50) default NULL,
  mastro char(1) default NULL
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]contconti'
-- 

INSERT INTO [PREFIX]contconti (codice, descrizione, mastro) VALUES 
('5005', 'IMMOBILIZZAZIONI', '5'),
('5010', 'CLIENTI', '5'),
('5014', 'CASSA', '5'),
('5015', 'DEPOSITI BANCARI', '5'),
('5016', 'PORTAFOGLIO', '5'),
('5030', 'CREDITI DIVERSI', '5'),
('6010', 'FORNITORI', '6'),
('6020', 'DEBITI TRIBUTARI', '6'),
('6800', 'FONDO AMMORTAMENTO', '6'),
('6900', 'PATRIMONIO NETTO', '6'),
('7001', 'MERCI', '7'),
('7010', 'SERVIZI', '7'),
('7020', 'LAVORAZIONI', '7'),
('7030', 'CONSULENZE E PRESTAZIONI', '7'),
('7040', 'MANUTENZIONI', '7'),
('7050', 'PROVVIGIONI', '7'),
('7060', 'ASSICURAZIONI', '7'),
('7070', 'AFFITTI E LOCAZIONI', '7'),
('7080', 'SALARI E STIPENDI', '7'),
('7090', 'MATERIALI DI CONSUMO', '7'),
('100', 'ONERI DIVERSI DI GESTIONE', '7'),
('001', 'MERCI', '8'),
('010', 'RICAVI DIVERSI', '8'),
('010', 'BILANCIO DI APERTURA', '9'),
('020', 'BILANCIO DI CHIUSURA', '9');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]contmastri'
-- 

CREATE TABLE [PREFIX]contmastri (
  codice char(1) default NULL,
  descrizione varchar(50) default NULL
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]contmastri'
-- 

INSERT INTO [PREFIX]contmastri (codice, descrizione) VALUES 
('5', 'ATTIVITA'),
('6', 'PASSIVITA'),
('7', 'COSTI'),
('8', 'RICAVI'),
('9', 'CONTI ORDINE');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]contsegno'
-- 

CREATE TABLE [PREFIX]contsegno (
  codice char(1) default NULL,
  descrizione varchar(5) default NULL
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]contsegno'
-- 

INSERT INTO [PREFIX]contsegno (codice, descrizione) VALUES 
('A', 'Avere'),
('D', 'Dare');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]doccampi'
-- 

CREATE TABLE [PREFIX]doccampi (
  idtable bigint(20) NOT NULL auto_increment,
  codtipodoc varchar(5) default NULL,
  testatarighe char(1) NOT NULL default '',
  nomecampo varchar(50) default NULL,
  visible char(1) NOT NULL default '',
  PRIMARY KEY  (idtable),
  UNIQUE KEY univoca (codtipodoc,nomecampo,testatarighe)
) TYPE=MyISAM ;

-- 
-- Dump dei dati per la tabella '[PREFIX]doccampi'
-- 

INSERT INTO [PREFIX]doccampi (idtable, codtipodoc, testatarighe, nomecampo, visible) VALUES 
(2, '00001', 'T', 'iddoc', 'S'),
(3, '00001', 'T', 'codclifor', 'S'),
(4, '00001', 'T', 'data', 'S'),
(5, '00001', 'T', 'numdocum', 'S'),
(6, '00001', 'T', 'anno', 'S'),
(7, '00001', 'T', 'codtipodoc', 'S'),
(8, '00001', 'T', 'bloccato', 'S'),
(9, '00001', 'T', 'tipofn', 'S'),
(10, '00001', 'T', 'imponibile', 'S'),
(11, '00001', 'T', 'imposta', 'S'),
(12, '00001', 'T', 'totdoc', 'S'),
(13, '00001', 'T', 'destalter1', 'S'),
(14, '00001', 'T', 'destalter2', 'S'),
(15, '00001', 'T', 'numeroscontrino', 'S'),
(16, '00001', 'T', 'datascontrino', 'S'),
(17, '00001', 'T', 'numdoceff', 'S'),
(18, '00001', 'T', 'referente', 'S'),
(19, '00001', 'T', 'note', 'S'),
(20, '00001', 'T', 'docchiuso', 'S'),
(21, '00001', 'T', 'rifestrnum', 'S'),
(22, '00001', 'T', 'rifestranno', 'S'),
(23, '00001', 'T', 'rifestrtipodoc', 'S'),
(24, '00001', 'T', 'regdocum', 'S'),
(25, '00001', 'T', 'codtipopag', 'S'),
(26, '00001', 'T', 'rifiddoc', 'S'),
(27, '00001', 'R', 'idriga', 'N'),
(28, '00001', 'R', 'barcode', 'N'),
(29, '00001', 'R', 'codice', 'S'),
(30, '00001', 'R', 'descrizione', 'S'),
(31, '00001', 'R', 'quantita', 'S'),
(32, '00001', 'R', 'quantita2', 'N'),
(33, '00001', 'R', 'prezzo', 'S'),
(34, '00001', 'R', 'codiva', 'S'),
(35, '00001', 'R', 'sconto', 'S'),
(36, '00001', 'R', 'imponibile', 'N'),
(37, '00001', 'R', 'imposta', 'N'),
(38, '00001', 'R', 'totale', 'N'),
(39, '00001', 'R', 'nriga', 'N'),
(40, '00001', 'R', 'anno', 'N'),
(41, '00001', 'R', 'codtipodoc', 'N'),
(42, '00001', 'R', 'numdocum', 'N'),
(43, '00001', 'R', 'conto', 'N'),
(44, '00001', 'R', 'data', 'N'),
(45, '00001', 'R', 'iddocr', 'N'),
(46, '00001', 'R', 'rifiddoc', 'N'),
(47, '00001', 'R', 'rifidriga', 'N'),
(48, '00001', 'R', 'codutente', 'N');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]doccampireport'
-- 

CREATE TABLE [PREFIX]doccampireport (
  idtable int(11) unsigned default NULL,
  codtipodoc varchar(5) default NULL,
  campo varchar(30) default '0',
  nomecampo varchar(30) default NULL,
  ordine int(10) unsigned default NULL
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]doccampireport'
-- 

INSERT INTO [PREFIX]doccampireport (idtable, codtipodoc, campo, nomecampo, ordine) VALUES 
(16, '00004', 'quantita', 'Quantita', 3),
(14, '00004', 'codice', 'Codice', 1),
(2, '00013', 'descrizione', 'Descrizione', 2),
(3, '00013', 'quantita', 'Quantita', 3),
(1, '00013', 'codice', 'Codice', 1),
(15, '00004', 'descrizione', 'Descrizione', 2),
(7, '00023', 'codice', 'Codice', 1),
(8, '00023', 'descrizione', 'Descrizione', 2),
(9, '00023', 'quantita', 'Quantita', 3),
(10, '00023', 'prezzo', 'Prezzo', 4),
(11, '00012', 'codice', 'Codice', 1),
(12, '00012', 'descrizione', 'Descrizione', 2),
(13, '00012', 'quantita', 'Quantita', 3),
(17, '00004', 'prezzo', 'Prezzo', 4),
(18, '00013', 'prezzo', 'Prezzo', 4),
(1, '00024', 'codice', 'Codice', 1),
(2, '00024', 'descrizione', 'Descrizione', 2),
(3, '00024', 'quantita', 'Quantita', 3),
(4, '00024', 'prezzo', 'Prezzo', 4),
(5, '00024', 'totale', 'Importo', 5),
(11, '00023', 'totale', 'Importo', 5);

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]docr'
-- 

CREATE TABLE [PREFIX]docr (
  idriga bigint(20) NOT NULL default '0',
  barcode varchar(50) default NULL,
  codice varchar(20) default NULL,
  descrizione varchar(100) default NULL,
  quantita double default NULL,
  quantita2 double default NULL,
  prezzo double default '0',
  codiva char(3) default NULL,
  sconto double default NULL,
  imponibile double default NULL,
  imposta double default NULL,
  totale double default NULL,
  nriga int(11) NOT NULL default '0',
  anno varchar(4) NOT NULL default '',
  codtipodoc varchar(5) NOT NULL default '',
  numdocum varchar(10) NOT NULL default '',
  conto varchar(15) default NULL,
  `data` date default NULL,
  iddocr bigint(20) NOT NULL default '0',
  rifiddoc bigint(20) default NULL,
  rifidriga bigint(20) default NULL,
  codutente varchar(5) default NULL,
  estratto char(1) default NULL,
  dataordine date default NULL,
  visibile char(1) default 'S',
  PRIMARY KEY  (nriga,anno,codtipodoc,numdocum),
  UNIQUE KEY idriga (idriga),
  KEY aliquoteivaDocR (codiva),
  KEY anno (anno),
  KEY codtipodoc (codtipodoc),
  KEY numdocum (numdocum),
  KEY idtabella (iddocr),
  KEY iddocumento (iddocr)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]docr'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]docregistri'
-- 

CREATE TABLE [PREFIX]docregistri (
  codice char(2) NOT NULL default '',
  descrizione varchar(50) default NULL,
  seriale int(11) default '0',
  PRIMARY KEY  (codice),
  UNIQUE KEY codice (codice)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]docregistri'
-- 

INSERT INTO [PREFIX]docregistri (codice, descrizione, seriale) VALUES 
('01', 'Fatture Vendita', 0),
('02', 'Giacenza', 0),
('03', 'DDT Acquisto', 0),
('04', 'Vendita Scontrino', 0),
('05', 'DDT Resi', 0),
('06', 'DDT Vendita', 0),
('07', 'Prestito', 0),
('08', 'Reso da Prestito', 0),
('09', 'Nota Credito', 0);

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]docr_temp'
-- 

CREATE TABLE [PREFIX]docr_temp (
  idriga int(11) NOT NULL auto_increment,
  `data` varchar(19) default NULL,
  codarticolo varchar(20) default NULL,
  qta int(11) default '0',
  prezzoven double default NULL,
  username varchar(20) default NULL,
  codcaumov varchar(5) default NULL,
  carscar char(1) default NULL,
  sconto double default NULL,
  PRIMARY KEY  (idriga),
  KEY idriga (idriga)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]docr_temp'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]doct'
-- 

CREATE TABLE [PREFIX]doct (
  iddoc bigint(11) NOT NULL default '0',
  codclifor varchar(15) default NULL,
  `data` date default NULL,
  numdocum varchar(10) NOT NULL default '',
  anno varchar(4) NOT NULL default '',
  codtipodoc varchar(5) NOT NULL default '',
  bloccato char(1) default NULL,
  tipofn char(1) default NULL,
  imponibile double default NULL,
  imposta double default NULL,
  totdoc double default NULL,
  destalter1 varchar(30) default NULL,
  destalter2 varchar(30) default NULL,
  numeroscontrino varchar(5) default NULL,
  datascontrino varchar(20) default NULL,
  numdoceff varchar(20) default NULL,
  referente varchar(100) default NULL,
  note varchar(50) default NULL,
  docchiuso tinyint(3) unsigned default NULL,
  rifestrnum int(11) default NULL,
  rifestranno varchar(4) default NULL,
  rifestrtipodoc varchar(5) default NULL,
  regdocum char(2) default NULL,
  codtipopag varchar(15) default NULL,
  rifiddoc bigint(20) default NULL,
  spesetrasporto double default '0',
  spesevarie double default '0',
  data_ins datetime default NULL,
  idanag int(11) default NULL,
  PRIMARY KEY  (numdocum,anno,codtipodoc),
  UNIQUE KEY iddocumento (iddoc),
  KEY AnagraficaDocT (codclifor),
  KEY anno (anno),
  KEY codtipodoc (codtipodoc),
  KEY doctipidocdoct (codtipodoc),
  KEY numdoceff (numdoceff),
  KEY numdocum (numdocum),
  KEY numscontrino (numeroscontrino),
  KEY rifestrnum (rifestrnum),
  KEY iddoc (iddoc)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]doct'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]doctipidoc'
-- 

CREATE TABLE [PREFIX]doctipidoc (
  codice varchar(5) NOT NULL default '',
  descrizione varchar(30) default NULL,
  desbreve varchar(10) default NULL,
  codcaumag varchar(5) NOT NULL default '',
  tipoanagrafica char(1) default NULL,
  genmovcon char(1) default NULL,
  genautonum char(1) default NULL,
  genmovconriba char(1) default NULL,
  genscadenze char(1) default NULL,
  genprovvigioni char(1) default NULL,
  genmovmag char(1) default NULL,
  genprestito char(1) default NULL,
  genstatacq char(1) default NULL,
  genstatven char(1) default NULL,
  genriba char(1) default NULL,
  variazscad char(1) default NULL,
  prelaltridoc char(1) default NULL,
  codaltridoc varchar(5) default NULL,
  tipoprezzo char(1) default NULL,
  prezziivacomp char(1) default NULL,
  nomereport1 varchar(30) default NULL,
  stampapreview1 char(1) default NULL,
  numcopieinstampa1 smallint(6) default NULL,
  nomereport2 varchar(30) default NULL,
  stampapreview2 char(1) default NULL,
  numcopieinstampa2 smallint(6) default NULL,
  nomereport3 varchar(30) default NULL,
  stampapreview3 char(1) default NULL,
  numcopieinstampa3 smallint(6) default NULL,
  codregdoc char(2) default NULL,
  verdispart char(1) default NULL,
  verfido char(1) default NULL,
  vercliforblocc char(1) default NULL,
  verartblocc char(1) default NULL,
  verdocprelblocc char(1) default NULL,
  tipocalcimporto char(1) default NULL,
  codcontropiva varchar(15) default NULL,
  codcontropsptrasp varchar(15) default NULL,
  codcontropspvarie varchar(15) default NULL,
  codcontropspimballo varchar(15) default NULL,
  codcontropspart15 varchar(15) default NULL,
  codcontropeffetti varchar(15) default NULL,
  codcontropcontanti varchar(15) default NULL,
  codcontropcauzioni varchar(15) default NULL,
  codcontropomaggi varchar(15) default NULL,
  codabbuoniatt varchar(15) default NULL,
  codabbuonipas varchar(15) default NULL,
  codivaart15 varchar(15) default NULL,
  annotazioni varchar(30) default NULL,
  codtiporeg char(2) default NULL,
  diciturafissarpt varchar(255) default NULL,
  datiazienda1rpt varchar(255) default NULL,
  datiazienda2rpt varchar(255) default NULL,
  diciturafissa2rpt varchar(255) default NULL,
  stprpt1 char(1) default NULL,
  stprpt2 char(1) default NULL,
  stprpt3 char(1) default NULL,
  stpdraft1 char(1) default NULL,
  stpdraft2 char(1) default NULL,
  stpdraft3 char(1) default NULL,
  nomerptdraft1 varchar(30) default NULL,
  nomerptdraft2 varchar(30) default NULL,
  nomerptdraft3 varchar(30) default NULL,
  numcopied1 smallint(6) default NULL,
  numcopied2 smallint(6) default NULL,
  numcopied3 smallint(6) default NULL,
  dicituraomaggirpt varchar(70) default NULL,
  categoriadoc char(1) default NULL,
  notememo varchar(255) default NULL,
  contatorescontrini int(11) default '0',
  printetichette tinyint(3) unsigned default NULL,
  gesscontrino char(1) default NULL,
  filtroartforn tinyint(3) unsigned default NULL,
  sqlt text,
  sqlr text,
  sqlsub1 text,
  tipofn char(1) default NULL,
  campireport varchar(255) default NULL,
  PRIMARY KEY  (codice),
  KEY MovMagCausaliDocTipiDoc (codcaumag)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]doctipidoc'
-- 

INSERT INTO [PREFIX]doctipidoc (codice, descrizione, desbreve, codcaumag, tipoanagrafica, genmovcon, genautonum, genmovconriba, genscadenze, genprovvigioni, genmovmag, genprestito, genstatacq, genstatven, genriba, variazscad, prelaltridoc, codaltridoc, tipoprezzo, prezziivacomp, nomereport1, stampapreview1, numcopieinstampa1, nomereport2, stampapreview2, numcopieinstampa2, nomereport3, stampapreview3, numcopieinstampa3, codregdoc, verdispart, verfido, vercliforblocc, verartblocc, verdocprelblocc, tipocalcimporto, codcontropiva, codcontropsptrasp, codcontropspvarie, codcontropspimballo, codcontropspart15, codcontropeffetti, codcontropcontanti, codcontropcauzioni, codcontropomaggi, codabbuoniatt, codabbuonipas, codivaart15, annotazioni, codtiporeg, diciturafissarpt, datiazienda1rpt, datiazienda2rpt, diciturafissa2rpt, stprpt1, stprpt2, stprpt3, stpdraft1, stpdraft2, stpdraft3, nomerptdraft1, nomerptdraft2, nomerptdraft3, numcopied1, numcopied2, numcopied3, dicituraomaggirpt, categoriadoc, notememo, contatorescontrini, printetichette, gesscontrino, filtroartforn, sqlt, sqlr, sqlsub1, tipofn, campireport) VALUES 
('00001', 'DDT di Acquisto', 'DDTAcq', '11', 'F', 'N', 'N', '', 'S', '', 'S', 'N', '', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '03', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', 'D', '', 2366, 255, 'N', 255, '', '', '', '', ''),
('00002', 'Scarico per Rettif. Inventario', '', '25', '', 'N', 'N', '', 'N', '', 'S', 'N', '', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '05', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', 'D', '', 2366, 0, 'N', 0, '', '', '', '', ''),
('00003', 'Giacenza Iniziale', 'Giacenza', '00', '', 'N', 'N', '', 'N', '', 'S', 'N', '', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '02', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', 'G', '', 2366, 0, 'N', 0, '', '', '', '', ''),
('00004', 'Fattura da DDT', 'FTDDT', '00', 'C', 'S', 'N', '', 'N', '', 'N', 'N', '', '', '', '', '', '00012', '', '', 'rptfattura', '', 0, 'RptFatturaSub', '', 0, 'RptTotaliIVA', '', 0, '01', '', '', '', '', '', '', '1.020.00001', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', 'F', '', 2366, 0, 'N', 0, 'SELECT (DocT.numdocum),DocT.data,Anagrafiche.descrizione, Anagrafiche.indirizzo,Anagrafiche.citta,Anagrafiche.Prov,  DocT.destalter1, DocT.destalter2,Anagrafiche.Piva, DocT.Imponibile,DocT.imposta,DocT.TotDoc,DocT.anno, DocT.NumeroScontrino + '' del '' + DocT.dataScontrino, DocT.Referente , Pagamenti.descrizione AS DesPagamento FROM (DocT  INNER JOIN Anagrafiche on DocT.CodClifor=Anagrafiche.codice ) LEFT JOIN Pagamenti ON Pagamenti.codice = DocT.CodtipoPag WHERE DocT.numdocum=#NUMDOC# AND DocT.codtipodoc=''#TIPODOC#'' AND DocT.anno=''#ANNO#''', 'SELECT DocR.codice,DocR.descrizione,DocR.quantita,DocR.prezzo,DocR.sconto,DocR.codiva,DocR.Imponibile,DocR.Imposta,DocR.Totale FROM DocR  WHERE DocR.numdocum=#NUMDOC# AND DocR.codtipodoc=''#TIPODOC#'' AND DocR.anno=''#ANNO#'' ORDER BY DocR.nriga', 'SELECT SUM(Imponibile), codiva, aliquoteiva.descrizione , SUM (Imposta)  FROM DocR INNER JOIN aliquoteiva ON aliquoteiva.codice = DocR.codiva  WHERE DocR.numdocum=#NUMDOC# AND DocR.codtipodoc=''#TIPODOC#'' AND DocR.anno=''#ANNO#'' GROUP BY codiva,aliquoteiva.descrizione', '', ''),
('00005', 'Vendita con Scontrino', 'Scontrino', '21', 'C', 'S', 'N', '', 'N', '', 'S', 'N', '', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '04', '', '', '', '', '', '', '1.020.00001', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', '', '', 2366, 0, 'N', 0, '', '', '', 'F', ''),
('00006', 'Reso da Banchetto', '', '13', '', 'N', 'N', '', 'N', '', 'N', 'N', '', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '05', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', '', '', 2366, 0, 'N', 0, '', '', '', '', ''),
('00007', 'Reso da Cliente', '', '12', 'C', 'N', 'N', '', 'N', '', 'S', 'N', '', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '05', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', '', '', 2366, 0, 'N', 0, '', '', '', '', ''),
('00008', 'Reso a Fornitore', 'DDTReso', '22', 'F', 'N', 'S', '', 'N', '', 'S', 'N', '', '', '', '', '', '', '', '', 'RptDDT2', '', 0, 'RptDDTSub', '', 0, '', '', 0, '06', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', '', '', 2366, 0, 'N', 0, 'SELECT (DocT.numdocum),DocT.data,DocT.anno, DocT.Referente, DocT.note, Anagrafiche.descrizione, Anagrafiche.indirizzo, Anagrafiche.citta, Anagrafiche.Prov FROM DocT  INNER JOIN Anagrafiche ON Anagrafiche.codice=DocT.codclifor WHERE DocT.numdocum=#NUMDOC# AND DocT.codtipodoc=''#TIPODOC#'' AND DocT.anno=''#ANNO#''', 'SELECT DocR.codice,DocR.descrizione,DocR.quantita,DocR.prezzo,DocR.sconto,DocR.codiva,DocR.Imponibile,DocR.Imposta,DocR.Totale,DocR.Barcode FROM DocR  WHERE DocR.numdocum=#NUMDOC# AND DocR.codtipodoc=''#TIPODOC#'' AND DocR.anno=''#ANNO#'' ORDER BY DocR.nriga', '', '', ''),
('00009', 'Bolla per Banchetto', 'DDTBanc', '23', '', 'N', 'N', '', 'N', '', 'N', 'N', '', '', '', '', '', '', '', '', 'RptDDTBanchetto', '', 0, 'RptDDTBanchettoSub', '', 0, '', '', 0, '06', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', '', '', 2366, 0, 'S', 0, 'SELECT (DocT.numdocum),DocT.data,DocT.anno, DocT.Referente, DocT.note FROM DocT  WHERE DocT.numdocum=#NUMDOC# AND DocT.codtipodoc=''#TIPODOC#'' AND DocT.anno=''#ANNO#''', 'SELECT DocR.codice,DocR.descrizione,DocR.quantita,DocR.prezzo,DocR.sconto,DocR.codiva,DocR.Imponibile,DocR.Imposta,DocR.Totale,DocR.Barcode FROM DocR  WHERE DocR.numdocum=#NUMDOC# AND DocR.codtipodoc=''#TIPODOC#'' AND DocR.anno=''#ANNO#'' ORDER BY DocR.nriga', '', '', ''),
('00010', 'Carico per Rettif. Inventario', '', '15', '', 'N', 'N', '', 'N', '', 'S', 'N', '', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '05', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', '', '', 2366, 0, 'N', 0, '', '', '', '', ''),
('00011', 'Fattura RP', 'FTrp', '00', 'C', 'S', 'N', '', 'N', '', 'N', 'N', '', '', '', '', '', '', '', '', 'rptfattura', '', 0, 'RptFatturaSub', '', 0, 'RptTotaliIVA', '', 0, '01', '', '', '', '', '', '', '1.020.00001', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', '', '', 2366, 0, 'N', 0, 'SELECT (DocT.numdocum),DocT.data,Anagrafiche.descrizione, Anagrafiche.indirizzo,Anagrafiche.citta,Anagrafiche.Prov,  DocT.destalter1, DocT.destalter2,Anagrafiche.Piva, DocT.Imponibile,DocT.imposta,DocT.TotDoc,DocT.anno, DocT.NumeroScontrino + '' del '' + DocT.dataScontrino, DocT.Referente , Pagamenti.descrizione AS DesPagamento FROM (DocT  INNER JOIN Anagrafiche on DocT.CodClifor=Anagrafiche.codice ) LEFT JOIN Pagamenti ON Pagamenti.codice = DocT.CodtipoPag WHERE DocT.numdocum=#NUMDOC# AND DocT.codtipodoc=''#TIPODOC#'' AND DocT.anno=''#ANNO#''', 'SELECT DocR.codice,DocR.descrizione,DocR.quantita,DocR.prezzo,DocR.sconto,DocR.codiva,DocR.Imponibile,DocR.Imposta,DocR.Totale FROM DocR  WHERE DocR.numdocum=#NUMDOC# AND DocR.codtipodoc=''#TIPODOC#'' AND DocR.anno=''#ANNO#'' ORDER BY DocR.nriga', 'SELECT SUM(Imponibile), codiva, aliquoteiva.descrizione , SUM (Imposta)  FROM DocR INNER JOIN aliquoteiva ON aliquoteiva.codice = DocR.codiva  WHERE DocR.numdocum=#NUMDOC# AND DocR.codtipodoc=''#TIPODOC#'' AND DocR.anno=''#ANNO#'' GROUP BY codiva,aliquoteiva.descrizione', '', ''),
('00012', 'DDT Vendita', 'DDT', '26', 'C', 'N', 'N', '', 'N', '', 'S', 'N', '', '', '', '', '', '', '', '', 'rptddt', '', 0, 'RptDDTSub', '', 0, '', '', 0, '06', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', '', '', 2366, 0, 'N', 0, 'SELECT (DocT.numdocum),DocT.data,DocT.anno, DocT.Referente, DocT.note, Anagrafiche.descrizione, Anagrafiche.indirizzo, Anagrafiche.citta, Anagrafiche.Prov FROM DocT  INNER JOIN Anagrafiche ON Anagrafiche.codice=DocT.codclifor WHERE DocT.numdocum=#NUMDOC# AND DocT.codtipodoc=''#TIPODOC#'' AND DocT.anno=''#ANNO#''', 'SELECT DocR.codice,DocR.descrizione,DocR.quantita,DocR.prezzo,DocR.sconto,DocR.codiva,DocR.Imponibile,DocR.Imposta,DocR.Totale,DocR.Barcode FROM DocR  WHERE DocR.numdocum=#NUMDOC# AND DocR.codtipodoc=''#TIPODOC#'' AND DocR.anno=''#ANNO#'' ORDER BY DocR.nriga', '', '', ''),
('00013', 'Fattura', 'FTAcc', '24', 'C', 'S', 'N', '', 'N', '', 'S', 'N', '', '', '', '', '', '', '', '', 'rptfattura', '', 0, 'RptFatturaSub', '', 0, 'RptTotaliIVA', '', 0, '01', '', '', '', '', '', '', '1.020.00001', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', 'F', '', 2366, 0, 'N', 0, 'SELECT (DocT.numdocum),DocT.data,Anagrafiche.descrizione, Anagrafiche.indirizzo,Anagrafiche.citta,Anagrafiche.Prov,  DocT.destalter1, DocT.destalter2,Anagrafiche.Piva, DocT.Imponibile,DocT.imposta,DocT.TotDoc,DocT.anno, DocT.NumeroScontrino + '' del '' + DocT.dataScontrino, DocT.Referente , Pagamenti.descrizione AS DesPagamento FROM (DocT  INNER JOIN Anagrafiche on DocT.CodClifor=Anagrafiche.codice ) LEFT JOIN Pagamenti ON Pagamenti.codice = DocT.CodtipoPag WHERE DocT.numdocum=#NUMDOC# AND DocT.codtipodoc=''#TIPODOC#'' AND DocT.anno=''#ANNO#''', 'SELECT DocR.codice,DocR.descrizione,DocR.quantita,DocR.prezzo,DocR.sconto,DocR.codiva,DocR.Imponibile,DocR.Imposta,DocR.Totale FROM DocR  WHERE DocR.numdocum=#NUMDOC# AND DocR.codtipodoc=''#TIPODOC#'' AND DocR.anno=''#ANNO#'' ORDER BY DocR.nriga', 'SELECT SUM(Imponibile), codiva, aliquoteiva.descrizione , SUM (Imposta)  FROM DocR INNER JOIN aliquoteiva ON aliquoteiva.codice = DocR.codiva  WHERE DocR.numdocum=#NUMDOC# AND DocR.codtipodoc=''#TIPODOC#'' AND DocR.anno=''#ANNO#'' GROUP BY codiva,aliquoteiva.descrizione', 'F', ''),
('00020', 'Prestito (scarico)', 'ScPres', '27', 'U', 'N', 'N', '', 'N', '', 'N', 'S', '', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '07', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', 'D', '', 2366, 0, 'N', 0, '', '', '', '', ''),
('00021', 'Reso da Prestito', 'ResPre', '16', 'U', 'N', 'N', '', 'N', '', 'N', 'S', '', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '08', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', 'D', '', 2366, 0, 'N', 0, '', '', '', '', ''),
('00022', 'Nota Credito', 'NTCRED', '00', 'C', 'S', 'N', '', '', '', 'N', 'N', '', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '09', '', '', '', '', '', '', '1.020.00001', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', '', '', 0, 0, 'N', 0, '', '', '', 'N', ''),
('00023', 'OrdineFAM', 'ORDFAM', '00', 'C', 'N', 'N', '', '', '', 'N', 'N', '', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '01', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', '', '', 0, 0, 'N', 0, '', '', '', 'F', ''),
('00024', 'Ordine GAS', 'ORDGAS', '11', 'F', 'N', 'N', '', '', '', 'S', 'N', '', '', '', '', '', '00023', '', '', '', '', 0, '', '', 0, '', '', 0, '03', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', '', '', 0, 0, 'N', 0, '', '', '', 'F', ''),
('00000', ' Non Indicato', 'NONIND', '00', '', 'N', 'N', '', '', '', 'N', 'N', '', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', '', '', 0, 0, 'N', 0, '', '', '', 'F', '');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]doctscad'
-- 

CREATE TABLE [PREFIX]doctscad (
  seriale int(11) default NULL,
  datascadenza varchar(19) default NULL,
  importoscadenza double default NULL,
  dataincasso varchar(19) default NULL,
  importoincassato double default NULL,
  numdocum int(11) NOT NULL default '0',
  codtipodoc varchar(5) default NULL,
  anno varchar(4) default NULL,
  chiusa tinyint(3) unsigned default NULL,
  tipo char(1) NOT NULL default '',
  descrizione varchar(50) default NULL,
  KEY numdocum (numdocum)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]doctscad'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]fornitoreperiodo'
-- 

CREATE TABLE [PREFIX]fornitoreperiodo (
  idtable int(11) NOT NULL default '0',
  fornitore varchar(5) default NULL,
  datainizio date default NULL,
  datafine date default NULL,
  chiuso char(1) default NULL,
  eseguito char(1) default NULL,
  ricorsivo char(1) default NULL,
  PRIMARY KEY  (idtable)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]fornitoreperiodo'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]giacenza'
-- 

CREATE TABLE [PREFIX]giacenza (
  codice varchar(20) NOT NULL default '',
  descrizione varchar(100) default NULL
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]giacenza'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]movconr'
-- 

CREATE TABLE [PREFIX]movconr (
  nriga int(11) NOT NULL default '0',
  codice varchar(6) NOT NULL default '',
  anno varchar(4) NOT NULL default '',
  codconto varchar(15) default NULL,
  importodare double NOT NULL default '0',
  importoavere double NOT NULL default '0',
  numpartita int(11) default NULL,
  regpartita char(2) default NULL,
  annopartita smallint(6) default NULL,
  descrizione varchar(90) default NULL,
  flagcorrisp char(1) default NULL,
  codcauscontind varchar(5) default NULL,
  PRIMARY KEY  (codice,nriga)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]movconr'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]movcont'
-- 

CREATE TABLE [PREFIX]movcont (
  codice varchar(6) NOT NULL default '',
  anno varchar(4) NOT NULL default '',
  datareg varchar(19) default NULL,
  dataope varchar(19) default NULL,
  numdocum varchar(10) NOT NULL default '0',
  regdocum char(2) default NULL,
  codtipodoc varchar(5) default NULL,
  emessodadocesterni char(1) default NULL,
  fatnac char(1) default NULL,
  numdoceff varchar(10) default NULL,
  desvalutavalore varchar(15) default NULL,
  codpagamento varchar(5) default NULL,
  codbanca varchar(5) default NULL,
  totdoc double NOT NULL default '0',
  codclifor varchar(15) default NULL,
  flagcorrisp char(1) default NULL,
  codpuntoven varchar(5) default NULL,
  bloccatoiva char(1) default NULL,
  aperturatemp char(1) default NULL,
  dichiusura char(1) default NULL,
  bloccatocon char(1) default NULL,
  codcausale varchar(5) default NULL,
  congelato char(1) default NULL,
  tipomovcon varchar(8) default NULL,
  PRIMARY KEY  (codice)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]movcont'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]movmagcausali'
-- 

CREATE TABLE [PREFIX]movmagcausali (
  codice varchar(5) NOT NULL default '',
  descrizione varchar(50) default NULL,
  carscar char(1) default NULL,
  PRIMARY KEY  (codice),
  KEY codice (codice)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]movmagcausali'
-- 

INSERT INTO [PREFIX]movmagcausali (codice, descrizione, carscar) VALUES 
('00', 'Giacenza Iniziale', 'G'),
('11', 'Carico per Acquisto', 'C'),
('12', 'Carico per reso da cliente', 'C'),
('13', 'Carico reso banchetto', 'C'),
('15', 'Carico Rettifica Inventario', 'C'),
('21', 'Scarico vendita scontrino', 'S'),
('22', 'Scarico reso fornitore', 'S'),
('23', 'Scarico  consegna banchetto', 'S'),
('24', 'Scarico per vendita fatture', 'S'),
('25', 'Scarico rettifica inventario', 'S'),
('26', 'Scarico per DDT Generico', 'S');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]movmagr'
-- 

CREATE TABLE [PREFIX]movmagr (
  idriga int(11) NOT NULL default '0',
  seriale int(11) NOT NULL default '0',
  `data` date NOT NULL default '0000-00-00',
  codarticolo varchar(20) default NULL,
  codcaumov varchar(5) NOT NULL default '',
  carscar char(1) default NULL,
  qta double default NULL,
  prezzoacq double default NULL,
  prezzoven double default NULL,
  sede varchar(50) default NULL,
  numdocum varchar(10) NOT NULL default '',
  anno varchar(4) NOT NULL default '',
  codtipodoc varchar(5) NOT NULL default '',
  tipo char(1) default NULL,
  codanag varchar(15) default NULL,
  idtable bigint(20) NOT NULL default '0',
  PRIMARY KEY  (idriga)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]movmagr'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]pagamenti'
-- 

CREATE TABLE [PREFIX]pagamenti (
  codice varchar(15) NOT NULL default '0',
  descrizione varchar(40) default NULL,
  PRIMARY KEY  (codice)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]pagamenti'
-- 

INSERT INTO [PREFIX]pagamenti (codice, descrizione) VALUES 
('B30FM', 'Bonifico a 30 gg Fine Mese'),
('RIMDIR', 'Rimessa diretta'),
('---', 'Non Indicato');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]progetti'
-- 

CREATE TABLE [PREFIX]progetti (
  codice varchar(10) default NULL,
  descrizione varchar(50) default NULL,
  centrale varchar(15) default NULL,
  ordine int(11) default '10'
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]progetti'
-- 

INSERT INTO [PREFIX]progetti (codice, descrizione, centrale, ordine) VALUES 
('00', 'Non Indicato', '', 0);

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]referenti'
-- 

CREATE TABLE [PREFIX]referenti (
  idtable int(3) default NULL,
  codanag varchar(5) default NULL,
  codfornitore varchar(5) default NULL
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]referenti'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]tipiarticoli'
-- 

CREATE TABLE [PREFIX]tipiarticoli (
  codice char(2) NOT NULL default '',
  descrizione varchar(50) default NULL,
  PRIMARY KEY  (codice),
  UNIQUE KEY codice (codice)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]tipiarticoli'
-- 

INSERT INTO [PREFIX]tipiarticoli (codice, descrizione) VALUES 
('ED', 'Editoriale'),
('AL', 'Alimentare'),
('AR', 'Artigianato'),
('00', 'Non Indicato');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]tipimovcon'
-- 

CREATE TABLE [PREFIX]tipimovcon (
  idtipo char(8) default NULL,
  descrizione char(15) default NULL,
  UNIQUE KEY descrizione (descrizione),
  UNIQUE KEY idtipo (idtipo)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]tipimovcon'
-- 

INSERT INTO [PREFIX]tipimovcon (idtipo, descrizione) VALUES 
('ACQ', 'ACQUISTO'),
('BILAP', 'BIL. APERTURA'),
('BILCH', 'BIL. CHIUSURA'),
('COMP', 'COMPENSO'),
('EMRIBA', 'EMISSIONE RI.BA'),
('FATSER', 'FATT. SERVIZI'),
('INCAS', 'INCASSO'),
('LIQIVA', 'LIQUID. IVA'),
('NOTACR', 'NOTA CREDITO'),
('OPBAN', 'OP. BANCARIE'),
('PAGAM', 'PAGAMENTO'),
('STORN', 'STORNO'),
('VEND', 'VENDITA');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]tipoanagrafiche'
-- 

CREATE TABLE [PREFIX]tipoanagrafiche (
  codice char(1) NOT NULL default '',
  descrizione varchar(20) default NULL,
  UNIQUE KEY codice (codice)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]tipoanagrafiche'
-- 

INSERT INTO [PREFIX]tipoanagrafiche (codice, descrizione) VALUES 
('C', 'Clienti'),
('F', 'Fornitori'),
('U', 'Utenti'),
('V', 'Volontari');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]tipofatnac'
-- 

CREATE TABLE [PREFIX]tipofatnac (
  codice char(1) NOT NULL default '',
  descrizione varchar(15) default NULL,
  PRIMARY KEY  (codice)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]tipofatnac'
-- 

INSERT INTO [PREFIX]tipofatnac (codice, descrizione) VALUES 
('F', 'Fattura'),
('N', 'Nota di Credito');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]tipostato'
-- 

CREATE TABLE [PREFIX]tipostato (
  codice char(1) default NULL,
  descrizione varchar(15) default NULL
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]tipostato'
-- 

INSERT INTO [PREFIX]tipostato (codice, descrizione) VALUES 
('A', 'Aperto'),
('C', 'Chiuso'),
('L', 'Inoltrato');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]um'
-- 

CREATE TABLE [PREFIX]um (
  codice varchar(10) default NULL,
  desc_singolare varchar(30) default NULL,
  desc_plurale varchar(30) default NULL,
  genere varchar(30) NOT NULL,
  ordine int(11) default NULL,
  UNIQUE KEY codice (codice)
) TYPE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]um'
-- 

INSERT INTO [PREFIX]um (codice, desc_singolare, desc_plurale, genere, ordine) VALUES 
('hg', 'Ettogrammo', 'Ettogrammi', 'Peso', 10),
('kg', 'Chilogrammo', 'Chilogrammi', 'Peso', 20),
('dl', 'Decilitro', 'Decilitri', 'Volume', 30),
('lt', 'Litro', 'Litri', 'Volume', 40);

-- --------------------------------------------------------

DELETE FROM _aziende WHERE prefix = '[PREFIX]';

INSERT INTO _aziende (id_azienda, rag_soc, prefix, dbver, indirizzo, etichette_max, gg_cod_doc_ordine, gg_cod_doc_ordine_fam, eg_cod_doc_scontrino, n_decimali_prezzi, etichette_path, show_new_account, data_inizio, data_agg) SELECT 1+COALESCE(MAX(id_azienda), 0), 'Nome BOTTEGA non impostato', '[PREFIX]', '0009', 'indirizzo...', 12, '00024', '00023', '00005', 2, 'etichette/', '0', CURDATE(), NOW() FROM _aziende;

UPDATE _aziende set dbver = '0016' WHERE prefix = '[PREFIX]';
