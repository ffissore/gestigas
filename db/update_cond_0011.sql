INSERT INTO _luoghi_cons ( id_luogo_cons, prefix, descrizione ) VALUES ( '0', '', 'Non indicato' );

ALTER TABLE _aziende ADD notifica_apertura_ref CHAR(1) NOT NULL DEFAULT '0';
ALTER TABLE _aziende ADD notifica_apertura_ref_data DATE NULL DEFAULT '2000-01-01';
ALTER TABLE _aziende ADD notifica_apertura_ref_gg TINYINT(4) NOT NULL DEFAULT '14';

ALTER TABLE _causali_mov_cassa ADD segno TINYINT(4) NOT NULL DEFAULT '1';
UPDATE _causali_mov_cassa SET segno =  0 WHERE id_causale_mov_cassa = 0;
UPDATE _causali_mov_cassa SET segno = -1 WHERE id_causale_mov_cassa = 1;
UPDATE _causali_mov_cassa SET segno = -1 WHERE id_causale_mov_cassa = 2;
UPDATE _causali_mov_cassa SET segno = -1 WHERE id_causale_mov_cassa = 7;

ALTER TABLE _causali_mov_cassa ADD tipo_rif CHAR(1) NOT NULL DEFAULT '0';
UPDATE _causali_mov_cassa SET tipo_rif = 'F' WHERE id_causale_mov_cassa = 2;
UPDATE _causali_mov_cassa SET tipo_rif = 'C' WHERE id_causale_mov_cassa = 3;
UPDATE _causali_mov_cassa SET tipo_rif = 'C' WHERE id_causale_mov_cassa = 4;
UPDATE _causali_mov_cassa SET tipo_rif = 'C' WHERE id_causale_mov_cassa = 5;


UPDATE _config SET dbver = '0011';