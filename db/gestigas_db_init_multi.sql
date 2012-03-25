
-- Struttura della tabella '[PREFIX]aliquoteiva'
-- 

CREATE TABLE [PREFIX]aliquoteiva ( codice char(3) NOT NULL default '', descrizione varchar(30) default NULL, iva double NOT NULL default '0', reparto char(3) default NULL, PRIMARY KEY  (codice), UNIQUE KEY codice (codice), KEY aliquoteivaiva (iva) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]aliquoteiva'
-- 

INSERT INTO [PREFIX]aliquoteiva (codice, descrizione, iva, reparto) VALUES ('00', 'IVA Esente', 0, 'R1'), ('04', 'IVA 4 perc.', 4, 'R2'), ('10', 'IVA 10 perc.', 10, 'R3'), ('20', 'IVA 20 perc.', 20, 'R4');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]anagrafiche'
-- 

CREATE TABLE [PREFIX]anagrafiche ( idanag int(10) NOT NULL default '0', codice varchar(15) NOT NULL default '', nome varchar(50) default NULL, cognome varchar(50) default NULL, descrizione varchar(50) default NULL, indirizzo varchar(50) default NULL, localita varchar(50) default NULL, cap varchar(5) default NULL, provincia char(20) default NULL, destalter1 varchar(30) default NULL, destalter2 varchar(30) default NULL, telefono varchar(20) default NULL, telefono2 varchar(20) default NULL, fax varchar(20) default NULL, email varchar(50) default NULL, www varchar(50) default NULL, tipo varchar(50) default NULL, tipoutente varchar(2) default NULL, note text, cf varchar(16) default NULL, piva varchar(11) default NULL, sconto double default NULL, tipocfa char(1) default NULL, noprinteti tinyint(3) unsigned default NULL, conto char(3) default NULL, segnocontabile char(1) default NULL, mastro char(1) default NULL, `password` varchar(40) default NULL, admin char(1) default NULL, stato int(11) default '1', data_ins datetime default NULL, data_agg timestamp NULL default NULL, datafine date default NULL, datainizio date default NULL, last_login datetime default NULL, n_login int(11) NOT NULL default '0', id_luogo_cons INT( 11 ) NULL DEFAULT '0', data_nascita DATE NULL, luogo_nascita VARCHAR( 30 ) NULL, mailing_list CHAR( 1 ) NOT NULL DEFAULT '1', db_source_page_limit TINYINT UNSIGNED NOT NULL DEFAULT 10, desc_agg VARCHAR(25), modifica_ingredienti CHAR( 1 ) NOT NULL DEFAULT '0', filtro_ingredienti CHAR( 1 ) NOT NULL DEFAULT '0', ingredienti_escludi TEXT NULL, note_ordine TEXT, cassiere CHAR( 1 ) NOT NULL DEFAULT '0', cellulare VARCHAR( 20 ) NULL, comune VARCHAR( 20 ) NULL, PRIMARY KEY  (codice), UNIQUE KEY id (idanag), KEY codice (codice) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]anagrafiche'
-- 

INSERT INTO [PREFIX]anagrafiche (idanag, codice, nome, cognome, descrizione, indirizzo, localita, cap, provincia, destalter1, destalter2, telefono, telefono2, fax, email, www, tipo, tipoutente, note, cf, piva, sconto, tipocfa, noprinteti, conto, segnocontabile, mastro, password, admin, stato, data_ins, data_agg, datafine, datainizio, last_login, n_login, id_luogo_cons, data_nascita, luogo_nascita, mailing_list, db_source_page_limit, desc_agg, modifica_ingredienti, filtro_ingredienti, ingredienti_escludi ) VALUES (0, '00', '', '', ' Non Indicato', '', '', '', '', '', '', '', '', '', '', '', '', 'U', '', '', '', 0, 'A', 0, '', 'D', '', '', '', 1, '2008-04-12 01:22:02', '2008-04-12 01:22:02', '0000-00-00', '0000-00-00', NULL, 0, 0, NULL, NULL, '1', 10, NULL, '0', '0', NULL), (1, '1', 'Mario', 'Rossi', 'Mario Rossi', '', '', '', '', '', '', '', '', '', 'admin@admin.it', '', '', 'AS', '', '', '', 0, 'C', 0, '', 'A', '', '21232f297a57a5a743894a0e4a801fc3', '', 1, '2008-04-12 01:22:02', '2008-04-12 01:22:02', '0000-00-00', '0000-00-00', NULL, 0, 0, NULL, NULL, '1', 10, NULL, '0', '0', NULL), (2, '2', 'Mario', 'Rossi', 'Mario Rossi', '', '', '', '', '', '', '', '', '', 'super@super.it', '', '', 'A', '', '', '', 0, 'C', 0, '', '', '', '1b3231655cebb7a1f783eddf27d254ca', '', 2, '2008-04-12 01:22:02', '2008-04-12 01:22:02', '0000-00-00', '0000-00-00', NULL, 0, 0, NULL, NULL, '1', 10, NULL, '0', '0', NULL), (3, '3', 'Mario', 'Rossi', 'Mario Rossi', '', '', '', '', '', '', '', '', '', 'ref@ref.it', '', '', 'R', '', '', '', 0, 'C', 0, '', '', '', '18389a4a9ad5795744699cff0ba66c15', '', 2, '2008-04-12 01:22:02', '2008-04-12 01:22:02', '0000-00-00', '0000-00-00', NULL, 0, 0, NULL, NULL, '1', 10, NULL, '0', '0', NULL), (4, '4', 'Mario', 'Rossi', 'Mario Rossi', '', '', '', '', '', '', '', '', '', 'user@user.it', '', '', 'U', '', '', '', 0, 'C', 0, '', '', '', 'ee11cbb19052e40b07aac0ca060c23ee', '', 2, '2008-04-12 01:22:02', '2008-04-12 01:22:02', '0000-00-00', '0000-00-00', NULL, 0, 0, NULL, NULL, '1', 10, NULL, '0', '0', NULL);


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]articoli'
-- 

CREATE TABLE [PREFIX]articoli ( idarticolo int(11) NOT NULL default '0', barcode varchar(50) default NULL, codice varchar(20) NOT NULL default '', descrizione varchar(100) default NULL, prezzoven double default NULL, prezzoacq double default NULL, codiva char(3) default NULL, tipo char(2) default NULL, giacenza int(11) default '0', centrale varchar(15) default NULL, progetto varchar(10) default NULL, sconto1 double default NULL, sconto2 double default NULL, sconto3 double default NULL, catmerce varchar(5) default NULL, tipoarticolo char(1) default 'A', paese varchar(15) default NULL, contovendita varchar(15) default NULL, contoacquisto varchar(15) default NULL, posizione varchar(10) default NULL, periodo varchar(30) default NULL, um varchar(10) default NULL, scortaminima double default NULL, pzperconf double default '0', qtaminordine double default '0', qtaminperfamiglia double default '0', um_qta double default NULL, stato int(11) default '1', data_ins datetime default NULL, data_agg timestamp NOT NULL, bio CHAR(1) NOT NULL DEFAULT '0', ingredienti TEXT NULL, data_agg_ing TIMESTAMP NULL, desc_agg TEXT, gestione_a_peso CHAR( 1 ) DEFAULT '0', PRIMARY KEY  (codice), UNIQUE KEY idarticolo (idarticolo) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]articoli'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]articoloperiodo'
-- 

CREATE TABLE [PREFIX]articoloperiodo ( idtable int(11) unsigned NOT NULL default '0', codice varchar(20) default NULL, dalmese int(3) unsigned default '0', almese int(3) unsigned default '0' ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]articoloperiodo'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]carrello'
-- 

CREATE TABLE [PREFIX]carrello ( idriga int(11) NOT NULL default '0', `data` varchar(19) default NULL, codarticolo varchar(20) default NULL, descrizione varchar(100) default NULL, qta double default NULL, qtaconsegnata double default NULL, prezzoven double default NULL, username varchar(20) default NULL, codcaumov varchar(5) default NULL, carscar char(1) default NULL, sconto double default NULL, codiva char(3) default NULL, idsessione varchar(255) default NULL, codfornitore varchar(5) default NULL, stato char(1) default NULL, um varchar(10) default NULL, codutente varchar(15) default NULL, qta_agg DOUBLE NOT NULL DEFAULT 0, PRIMARY KEY  (idriga) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]carrello'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]catmerceologica'
-- 

CREATE TABLE [PREFIX]catmerceologica ( codice varchar(5) NOT NULL default '', descrizione varchar(50) default NULL, tipo char(2) default NULL, PRIMARY KEY  (codice), UNIQUE KEY codice (codice) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]catmerceologica'
-- 

INSERT INTO [PREFIX]catmerceologica (codice, descrizione, tipo) VALUES ('000', 'Non indicato', '00'), ('FC5', 'Cereali in chicchi', 'FC'), ('FC4', 'Altre farine', 'FC'), ('FC3', 'Farina di grano: integrale', 'FC'), ('FC2', 'Farina di grano: semintegrale', 'FC'), ('FC1', 'Farina di grano: bianca', 'FC'), ('PF4', 'Altre prodotti da forno', 'PF'), ('PF3', 'Fette biscottate', 'PF'), ('PF2', 'Crackers', 'PF'), ('PF1', 'Biscotti', 'PF'), ('PA6', 'Altre paste', 'PA'), ('PA5', 'Pasta all''uovo', 'PA'), ('PA1', 'Pasta di semola: bianca', 'PA'), ('PA2', 'Pasta di semola: semintegrale', 'PA'), ('PA3', 'Pasta di semola: integrale', 'PA'), ('PA4', 'Pasta di farro', 'PA'), ('FC6', 'Cereali in fiocchi', 'FC'), ('FC7', 'Cereali soffiati', 'FC'), ('FC8', 'Cereali tostati', 'FC'), ('FC9', 'Muesli', 'FC'), ('FV1', 'Agrumi', 'FV'), ('FV2', 'Altra frutta e verdura', 'FV'), ('BE1', 'Liquori', 'BE'), ('BE2', 'Succhi di frutta', 'BE'), ('BE3', 'Vino', 'BE'), ('BE4', 'Altre bevande', 'BE'), ('AP1', 'CaffÃ¨, the e tisane', 'AP'), ('AP2', 'Cioccolato e cacao', 'AP'), ('AP3', 'Dolcificanti', 'AP'), ('AP4', 'Formaggi e latticini', 'AP'), ('AP5', 'Legumi', 'AP'), ('AP6', 'Marmellate, confetture, mostarde', 'AP'), ('AP7', 'Miele e prodotti dell''alveare', 'AP'), ('AP8', 'Olio', 'AP'), ('AP9', 'Prodotti senza glutine', 'AP'), ('AP10', 'Riso', 'AP'), ('AP11', 'Seitan, tofu e alghe', 'AP'), ('AP12', 'Spezie', 'AP'), ('AP13', 'Varie', 'AP'), ('DE1', 'Bucato a mano e in lavatrice', 'DE'), ('DE2', 'Stoviglie', 'DE'), ('DE3', 'Igiene Casa', 'DE'), ('IP1', 'Saponi liquidi', 'IP'), ('IP2', 'Saponi solidi', 'IP'), ('IP3', 'Capelli', 'IP'), ('IP4', 'Denti', 'IP'), ('IP5', 'Viso e labbra', 'IP'), ('IP6', 'Corpo', 'IP');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]contconti'
-- 

CREATE TABLE [PREFIX]contconti ( codice varchar(4) default NULL, descrizione varchar(50) default NULL, mastro char(1) default NULL ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]contconti'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]contmastri'
-- 

CREATE TABLE [PREFIX]contmastri ( codice char(1) default NULL, descrizione varchar(50) default NULL ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]contmastri'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]contsegno'
-- 

CREATE TABLE [PREFIX]contsegno ( codice char(1) default NULL, descrizione varchar(5) default NULL ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]contsegno'
-- 

INSERT INTO [PREFIX]contsegno (codice, descrizione) VALUES ('D', 'Dare'), ('A', 'Avere');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]doccampi'
-- 

CREATE TABLE [PREFIX]doccampi ( idtable bigint(20) NOT NULL auto_increment, codtipodoc varchar(5) default NULL, testatarighe char(1) NOT NULL default '', nomecampo varchar(50) default NULL, visible char(1) NOT NULL default '', PRIMARY KEY  (idtable), UNIQUE KEY univoca (codtipodoc,nomecampo,testatarighe) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]doccampi'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]doccampireport'
-- 

CREATE TABLE [PREFIX]doccampireport ( idtable int(11) unsigned default NULL, codtipodoc varchar(5) default NULL, campo varchar(30) default '0', nomecampo varchar(30) default NULL, ordine int(10) unsigned default NULL ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]doccampireport'
-- 

INSERT INTO [PREFIX]doccampireport ( idtable, codtipodoc, campo, nomecampo, ordine ) VALUES	( 1, '00023', 'fornitore',   'Fornitore', 10), ( 2, '00023', 'quantita',    'Q.tà', 20), ( 3, '00023', 'descrizione', 'Descrizione', 30), ( 4, '00023', 'prezzo',      'Prezzo', 40), ( 5, '00023', 'totale',      'Importo', 50), ( 6, '00024', 'quantita',    'Q.tà', 10), ( 7, '00024', 'descrizione', 'Descrizione', 20), ( 8, '00024', 'prezzo',      'Prezzo', 30),	( 9, '00024', 'totale',      'Importo', 40);

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]docr'
-- 

CREATE TABLE [PREFIX]docr ( idriga bigint(20) NOT NULL default '0',	barcode varchar(50) default NULL, codice varchar(20) default NULL, descrizione varchar(100) default NULL, quantita double default NULL, quantita2 double default NULL, prezzo double default '0', codiva char(3) default NULL, sconto double default NULL, imponibile double default NULL,	imposta double default NULL, totale double default NULL, nriga int(11) NOT NULL default '0', anno varchar(4) NOT NULL default '', codtipodoc varchar(5) NOT NULL default '', numdocum varchar(10) NOT NULL default '', conto varchar(15) default NULL,	`data` date default NULL, iddocr bigint(20) NOT NULL default '0',	rifiddoc bigint(20) default NULL, rifidriga bigint(20) default NULL, codutente varchar(15) default NULL,	estratto char(1) default NULL, dataordine date default NULL, visibile char(1) default 'S', delta_prezzo DOUBLE NOT NULL DEFAULT 0, PRIMARY KEY  (nriga,anno,codtipodoc,numdocum), UNIQUE KEY idriga (idriga), KEY aliquoteivaDocR (codiva), KEY anno (anno), KEY codtipodoc (codtipodoc), KEY numdocum (numdocum), KEY idtabella (iddocr), KEY iddocumento (iddocr) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]docr'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]docregistri'
-- 

CREATE TABLE [PREFIX]docregistri ( codice char(2) NOT NULL default '', descrizione varchar(50) default NULL, seriale int(11) default '0', PRIMARY KEY  (codice), UNIQUE KEY codice (codice) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]docregistri'
-- 

INSERT INTO [PREFIX]docregistri (codice, descrizione, seriale) VALUES ('01', 'Fatture Vendita', 0), ('02', 'Giacenza', 0), ('03', 'DDT Acquisto', 0), ('04', 'Vendita Scontrino', 0), ('05', 'DDT Resi', 0), ('06', 'DDT Vendita', 0), ('07', 'Prestito', 0), ('08', 'Reso da Prestito', 0), ('09', 'Nota Credito', 0);

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]docr_temp'
-- 

CREATE TABLE [PREFIX]docr_temp ( idriga int(11) NOT NULL auto_increment, `data` varchar(19) default NULL, codarticolo varchar(20) default NULL, qta int(11) default '0', prezzoven double default NULL, username varchar(20) default NULL, codcaumov varchar(5) default NULL, carscar char(1) default NULL, sconto double default NULL, PRIMARY KEY  (idriga), KEY idriga (idriga) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]docr_temp'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]doct'
-- 

CREATE TABLE [PREFIX]doct ( iddoc bigint(11) NOT NULL default '0', codclifor varchar(15) default NULL, `data` date default NULL, numdocum varchar(10) NOT NULL default '', anno varchar(4) NOT NULL default '', codtipodoc varchar(5) NOT NULL default '', bloccato char(1) default NULL, tipofn char(1) default NULL, imponibile double default NULL, imposta double default NULL, totdoc double default NULL,	destalter1 varchar(30) default NULL, destalter2 varchar(30) default NULL, numeroscontrino varchar(5) default NULL, datascontrino varchar(20) default NULL, numdoceff varchar(20) default NULL, referente varchar(100) default NULL, note TEXT default NULL, docchiuso tinyint(3) unsigned default NULL, rifestrnum int(11) default NULL, rifestranno varchar(4) default NULL, rifestrtipodoc varchar(5) default NULL, regdocum char(2) default NULL, codtipopag varchar(15) default NULL, rifiddoc bigint(20) default NULL, spesetrasporto double default '0', spesevarie double default '0', data_ins datetime default NULL,	idanag int(11) default NULL, PRIMARY KEY  (numdocum,anno,codtipodoc), UNIQUE KEY iddocumento (iddoc), KEY AnagraficaDocT (codclifor), KEY anno (anno), KEY codtipodoc (codtipodoc), KEY doctipidocdoct (codtipodoc), KEY numdoceff (numdoceff), KEY numdocum (numdocum), KEY numscontrino (numeroscontrino), KEY rifestrnum (rifestrnum), KEY iddoc (iddoc) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]doct'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]doctipidoc'
-- 

CREATE TABLE [PREFIX]doctipidoc ( codice varchar(5) NOT NULL default '', descrizione varchar(30) default NULL, desbreve varchar(10) default NULL, codcaumag varchar(5) NOT NULL default '', tipoanagrafica char(1) default NULL, genmovcon char(1) default NULL, genautonum char(1) default NULL, genmovconriba char(1) default NULL, genscadenze char(1) default NULL, genprovvigioni char(1) default NULL, genmovmag char(1) default NULL, genprestito char(1) default NULL, genstatacq char(1) default NULL, genstatven char(1) default NULL, genriba char(1) default NULL, variazscad char(1) default NULL, prelaltridoc char(1) default NULL, codaltridoc varchar(5) default NULL,	tipoprezzo char(1) default NULL, prezziivacomp char(1) default NULL, nomereport1 varchar(30) default NULL, stampapreview1 char(1) default NULL, numcopieinstampa1 smallint(6) default NULL, nomereport2 varchar(30) default NULL, stampapreview2 char(1) default NULL, numcopieinstampa2 smallint(6) default NULL, nomereport3 varchar(30) default NULL, stampapreview3 char(1) default NULL, numcopieinstampa3 smallint(6) default NULL, codregdoc char(2) default NULL,	verdispart char(1) default NULL, verfido char(1) default NULL, vercliforblocc char(1) default NULL, verartblocc char(1) default NULL, verdocprelblocc char(1) default NULL, tipocalcimporto char(1) default NULL, codcontropiva varchar(15) default NULL, codcontropsptrasp varchar(15) default NULL, codcontropspvarie varchar(15) default NULL, codcontropspimballo varchar(15) default NULL, codcontropspart15 varchar(15) default NULL, codcontropeffetti varchar(15) default NULL, codcontropcontanti varchar(15) default NULL, codcontropcauzioni varchar(15) default NULL, codcontropomaggi varchar(15) default NULL, codabbuoniatt varchar(15) default NULL, codabbuonipas varchar(15) default NULL, codivaart15 varchar(15) default NULL, annotazioni varchar(30) default NULL, codtiporeg char(2) default NULL, diciturafissarpt varchar(255) default NULL, datiazienda1rpt varchar(255) default NULL, datiazienda2rpt varchar(255) default NULL, diciturafissa2rpt varchar(255) default NULL, stprpt1 char(1) default NULL, stprpt2 char(1) default NULL, stprpt3 char(1) default NULL, stpdraft1 char(1) default NULL, stpdraft2 char(1) default NULL, stpdraft3 char(1) default NULL, nomerptdraft1 varchar(30) default NULL, nomerptdraft2 varchar(30) default NULL, nomerptdraft3 varchar(30) default NULL, numcopied1 smallint(6) default NULL, numcopied2 smallint(6) default NULL, numcopied3 smallint(6) default NULL, dicituraomaggirpt varchar(70) default NULL, categoriadoc char(1) default NULL, notememo varchar(255) default NULL, contatorescontrini int(11) default '0', printetichette tinyint(3) unsigned default NULL, gesscontrino char(1) default NULL, filtroartforn tinyint(3) unsigned default NULL, sqlt text, sqlr text, sqlsub1 text, tipofn char(1) default NULL, campireport varchar(255) default NULL, PRIMARY KEY  (codice), KEY MovMagCausaliDocTipiDoc (codcaumag) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]doctipidoc'
-- 

INSERT INTO [PREFIX]doctipidoc (codice, descrizione, desbreve, codcaumag, tipoanagrafica, genmovcon, genautonum, genmovconriba, genscadenze, genprovvigioni, genmovmag, genprestito, genstatacq, genstatven, genriba, variazscad, prelaltridoc, codaltridoc, tipoprezzo, prezziivacomp, nomereport1, stampapreview1, numcopieinstampa1, nomereport2, stampapreview2, numcopieinstampa2, nomereport3, stampapreview3, numcopieinstampa3, codregdoc, verdispart, verfido, vercliforblocc, verartblocc, verdocprelblocc, tipocalcimporto, codcontropiva, codcontropsptrasp, codcontropspvarie, codcontropspimballo, codcontropspart15, codcontropeffetti, codcontropcontanti, codcontropcauzioni, codcontropomaggi, codabbuoniatt, codabbuonipas, codivaart15, annotazioni, codtiporeg, diciturafissarpt, datiazienda1rpt, datiazienda2rpt, diciturafissa2rpt, stprpt1, stprpt2, stprpt3, stpdraft1, stpdraft2, stpdraft3, nomerptdraft1, nomerptdraft2, nomerptdraft3, numcopied1, numcopied2, numcopied3, dicituraomaggirpt, categoriadoc, notememo, contatorescontrini, printetichette, gesscontrino, filtroartforn, sqlt, sqlr, sqlsub1, tipofn, campireport) VALUES ('00023', 'Consegna a utente', 'ORDFAM', '00', 'C', 'N', 'N', '', '', '', 'N', 'N', '', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '01', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', '', '', 0, 0, 'N', 0, '', '', '', 'F', ''), ('00024', 'Ordine a fornitore', 'ORDGAS', '11', 'F', 'N', 'N', '', '', '', 'S', 'N', '', '', '', '', '', '00023', '', '', '', '', 0, '', '', 0, '', '', 0, '03', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', '', '', 0, 0, 'N', 0, '', '', '', 'F', ''), ('00000', ' Non Indicato', 'NONIND', '00', '', 'N', 'N', '', '', '', 'N', 'N', '', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, '', '', '', 0, 0, 'N', 0, '', '', '', 'F', '');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]doctscad'
-- 

CREATE TABLE [PREFIX]doctscad ( seriale int(11) default NULL, datascadenza varchar(19) default NULL, importoscadenza double default NULL, dataincasso varchar(19) default NULL, importoincassato double default NULL, numdocum int(11) NOT NULL default '0', codtipodoc varchar(5) default NULL, anno varchar(4) default NULL, chiusa tinyint(3) unsigned default NULL, tipo char(1) NOT NULL default '', descrizione varchar(50) default NULL, KEY numdocum (numdocum) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]doctscad'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]fornitoreperiodo'
-- 

CREATE TABLE [PREFIX]fornitoreperiodo ( idtable int(11) NOT NULL default '0', fornitore varchar(5) default NULL, datainizio date default NULL, datafine date default NULL, chiuso char(1) default NULL, eseguito char(1) default NULL, ricorsivo char(1) default NULL, PRIMARY KEY  (idtable) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]fornitoreperiodo'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]giacenza'
-- 

CREATE TABLE [PREFIX]giacenza ( codice varchar(20) NOT NULL default '', descrizione varchar(100) default NULL ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]giacenza'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]movconr'
-- 

CREATE TABLE [PREFIX]movconr ( nriga int(11) NOT NULL default '0', codice varchar(6) NOT NULL default '', anno varchar(4) NOT NULL default '', codconto varchar(15) default NULL, importodare double NOT NULL default '0', importoavere double NOT NULL default '0', numpartita int(11) default NULL, regpartita char(2) default NULL, annopartita smallint(6) default NULL, descrizione varchar(90) default NULL, flagcorrisp char(1) default NULL, codcauscontind varchar(5) default NULL, PRIMARY KEY  (codice,nriga) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]movconr'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]movcont'
-- 

CREATE TABLE [PREFIX]movcont ( codice varchar(6) NOT NULL default '', anno varchar(4) NOT NULL default '', datareg varchar(19) default NULL, dataope varchar(19) default NULL, numdocum varchar(10) NOT NULL default '0', regdocum char(2) default NULL, codtipodoc varchar(5) default NULL, emessodadocesterni char(1) default NULL, fatnac char(1) default NULL, numdoceff varchar(10) default NULL, desvalutavalore varchar(15) default NULL, codpagamento varchar(5) default NULL, codbanca varchar(5) default NULL, totdoc double NOT NULL default '0', codclifor varchar(15) default NULL, flagcorrisp char(1) default NULL, codpuntoven varchar(5) default NULL, bloccatoiva char(1) default NULL, aperturatemp char(1) default NULL, dichiusura char(1) default NULL, bloccatocon char(1) default NULL, codcausale varchar(5) default NULL, congelato char(1) default NULL, tipomovcon varchar(8) default NULL, PRIMARY KEY  (codice) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]movcont'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]movmagcausali'
-- 

CREATE TABLE [PREFIX]movmagcausali ( codice varchar(5) NOT NULL default '', descrizione varchar(50) default NULL, carscar char(1) default NULL, PRIMARY KEY  (codice), KEY codice (codice) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]movmagcausali'
-- 

INSERT INTO [PREFIX]movmagcausali (codice, descrizione, carscar) VALUES ('00', 'Giacenza Iniziale', 'G'), ('11', 'Carico per acquisto', 'C'), ('12', 'Carico per reso da cliente', 'C'), ('13', 'Carico reso banchetto', 'C'), ('15', 'Carico Rettifica inventariale', 'C'), ('21', 'Scarico per vendita (scontrino)', 'S'), ('22', 'Scarico per reso a fornitore', 'S'), ('23', 'Scarico per consegna a banchetto', 'S'), ('24', 'Scarico per vendita (fattura)', 'S'), ('25', 'Scarico Rettifica inventariale', 'S'), ('26', 'Scarico per DDT Generico', 'S'), ('16', 'Carico per Reso da Prestito', 'C'), ('27', 'Scarico per Prestito', 'S');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]movmagr'
-- 

CREATE TABLE [PREFIX]movmagr ( idriga int(11) NOT NULL default '0', seriale int(11) NOT NULL default '0', `data` date NOT NULL default '0000-00-00', codarticolo varchar(20) default NULL, codcaumov varchar(5) NOT NULL default '', carscar char(1) default NULL, qta double default NULL, prezzoacq double default NULL, prezzoven double default NULL, sede varchar(50) default NULL, numdocum varchar(10) NOT NULL default '', anno varchar(4) NOT NULL default '', codtipodoc varchar(5) NOT NULL default '', tipo char(1) default NULL, codanag varchar(15) default NULL, idtable bigint(20) NOT NULL default '0', PRIMARY KEY  (idriga) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]movmagr'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]pagamenti'
-- 

CREATE TABLE [PREFIX]pagamenti ( codice varchar(15) NOT NULL default '0', descrizione varchar(40) default NULL, PRIMARY KEY  (codice) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]pagamenti'
-- 

INSERT INTO [PREFIX]pagamenti (codice, descrizione) VALUES ('RIMDIR', 'Rimessa Diretta'), ('30GGFM', '30 giorni Fine Mese'), ('60GGFM', '60 giorni Fine Mese');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]progetti'
-- 

CREATE TABLE [PREFIX]progetti ( codice varchar(10) default NULL, descrizione varchar(50) default NULL, centrale varchar(15) default NULL, ordine int(11) default '10' ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]progetti'
-- 

INSERT INTO [PREFIX]progetti (codice, descrizione, centrale, ordine) VALUES ('00', 'Non Indicato', '', 0);

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]referenti'
-- 

CREATE TABLE [PREFIX]referenti ( idtable int(3) default NULL, codanag varchar(5) default NULL, codfornitore varchar(5) default NULL ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]referenti'
-- 


-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]tipiarticoli'
-- 

CREATE TABLE [PREFIX]tipiarticoli ( codice char(2) NOT NULL default '', descrizione varchar(50) default NULL, PRIMARY KEY  (codice), UNIQUE KEY codice (codice) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]tipiarticoli'
-- 

INSERT INTO [PREFIX]tipiarticoli (codice, descrizione) VALUES ('PF', 'Prodotti da forno'), ('PA', 'Pasta'), ('00', 'Non Indicato'), ('FC', 'Farine e cereali'), ('FV', 'Frutta e Verdura'), ('AP', 'Altri prodotti alimentari'), ('BE', 'Bevande'), ('DE', 'Detersivi'), ('IP', 'Igiene personale');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]tipimovcon'
-- 

CREATE TABLE [PREFIX]tipimovcon ( idtipo char(8) default NULL, descrizione char(15) default NULL, UNIQUE KEY descrizione (descrizione), UNIQUE KEY idtipo (idtipo) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]tipimovcon'
-- 

INSERT INTO [PREFIX]tipimovcon (idtipo, descrizione) VALUES ('ACQ', 'ACQUISTO'), ('BILAP', 'BIL. APERTURA'), ('BILCH', 'BIL. CHIUSURA'), ('COMP', 'COMPENSO'), ('EMRIBA', 'EMISSIONE RI.BA'), ('FATSER', 'FATT. SERVIZI'), ('INCAS', 'INCASSO'), ('LIQIVA', 'LIQUID. IVA'), ('NOTACR', 'NOTA CREDITO'), ('OPBAN', 'OP. BANCARIE'), ('PAGAM', 'PAGAMENTO'), ('STORN', 'STORNO'), ('VEND', 'VENDITA');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]tipoanagrafiche'
-- 

CREATE TABLE [PREFIX]tipoanagrafiche ( codice char(1) NOT NULL default '', descrizione varchar(20) default NULL, UNIQUE KEY codice (codice) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]tipoanagrafiche'
-- 

INSERT INTO [PREFIX]tipoanagrafiche (codice, descrizione) VALUES ('C', 'Clienti'), ('F', 'Fornitori'), ('U', 'Utenti'), ('V', 'Volontari');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]tipofatnac'
-- 

CREATE TABLE [PREFIX]tipofatnac ( codice char(1) NOT NULL default '', descrizione varchar(15) default NULL, PRIMARY KEY  (codice) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]tipofatnac'
-- 

INSERT INTO [PREFIX]tipofatnac (codice, descrizione) VALUES ('F', 'Fattura'), ('N', 'Nota di Credito');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]tipostato'
-- 

CREATE TABLE [PREFIX]tipostato ( codice char(1) default NULL, descrizione varchar(15) default NULL ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]tipostato'
-- 

INSERT INTO [PREFIX]tipostato (codice, descrizione) VALUES ('A', 'Aperto'), ('C', 'Chiuso'), ('L', 'Inoltrato');

-- --------------------------------------------------------

-- 
-- Struttura della tabella '[PREFIX]um'
-- 

CREATE TABLE [PREFIX]um ( codice varchar(10) default NULL, desc_singolare varchar(30) default NULL, desc_plurale varchar(30) default NULL, genere varchar(30) NOT NULL, ordine int(11) default NULL, UNIQUE KEY codice (codice) ) ENGINE=MyISAM;

-- 
-- Dump dei dati per la tabella '[PREFIX]um'
-- 

INSERT INTO [PREFIX]um (codice, desc_singolare, desc_plurale, genere, ordine) VALUES ('hg', 'Ettogrammo', 'Ettogrammi', 'Peso', 10), ('kg', 'Chilogrammo', 'Chilogrammi', 'Peso', 20), ('dl', 'Decilitro', 'Decilitri', 'Volume', 30), ('lt', 'Litro', 'Litri', 'Volume', 40);

-- --------------------------------------------------------

CREATE TABLE [PREFIX]etichette (idtable INT NOT NULL ,idanag INT NOT NULL ,barcode VARCHAR( 13 ) NOT NULL, codice VARCHAR( 20 ) NOT NULL, descrizione VARCHAR( 100 ) NULL, prezzoven double default NULL, iva VARCHAR(3) default NULL, stampato CHAR(1)  NOT NULL default 'N') ;

-- --------------------------------------------------------

DELETE FROM _aziende WHERE prefix = '[PREFIX]';

INSERT INTO _aziende (id_azienda, rag_soc, prefix, dbver, indirizzo, etichette_max, gg_cod_doc_ordine, gg_cod_doc_ordine_fam, eg_cod_doc_scontrino, n_decimali_prezzi, etichette_path, show_new_account, data_inizio, data_agg) SELECT 1+COALESCE(MAX(id_azienda), 0), 'Nome GAS non impostato', '[PREFIX]', '0009', 'indirizzo...', 12, '00024', '00023', '00005', 2, 'etichette/', '0', CURDATE(), NOW() FROM _aziende;

UPDATE _aziende SET dbver = '0020' WHERE prefix = '[PREFIX]';
