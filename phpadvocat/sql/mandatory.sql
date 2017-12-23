SET client_encoding = 'UTF8';
insert into phpa_partnertypes values('Person');
insert into phpa_partnertypes values('Firma');
insert into phpa_partnertypes values('Gericht');
insert into phpa_partnertypes values('gegn. PB');
insert into phpa_partnertypes values('Versicherung');

insert into phpa_expendituretypes (description, category, vat, vat_category) values ('Auslagen',1370,0,0);
insert into phpa_expendituretypes (description, category, vat, vat_category) values ('Fremdgeld',1374,0,0);
insert into phpa_expendituretypes (description, category, vat, vat_category) values ('Gebuehren',1200,19,3806);
insert into phpa_expendituretypes (description, category, vat, vat_category) values ('Gebuehren',1200,16,3805);

insert into phpa_accounts (description) values('Betriebskonto');
insert into phpa_accounts (description) values('Barkasse');

-- -- obsolete entries
-- insert into phpa_invoicetypes (number, description, charge, amount_category, vat_percent, vat_category) values (1, 'Honorar', NULL, 1200, 19, 3806);
-- insert into phpa_invoicetypes (number, description, charge, amount_category, vat_percent, vat_category) values (2, 'Prozessgebuehr/Gebuehr fuer Mahnbescheid', NULL, 1370, 0, 0);
-- insert into phpa_invoicetypes (number, description, charge, amount_category, vat_percent, vat_category) values (3, 'Erhoehungsgebuehr', NULL, 1200, 19, 3806);
-- insert into phpa_invoicetypes (number, description, charge, amount_category, vat_percent, vat_category) values (4, 'Verhandlungsgebuehr/Gebuehr fuer Vollstreckungsbescheid', NULL, 1370, 0, 0);
-- insert into phpa_invoicetypes (number, description, charge, amount_category, vat_percent, vat_category) values (5, 'Verhandlungsgebuehr, nichtstreitig', NULL, 1370, 0, 0);
-- insert into phpa_invoicetypes (number, description, charge, amount_category, vat_percent, vat_category) values (6, 'Verhandlungsgebuehr bei Einspruch', NULL, 1370, 0, 0);
-- insert into phpa_invoicetypes (number, description, charge, amount_category, vat_percent, vat_category) values (7, 'Eroerterungsgebuehr', NULL, 1370, 0, 0);
-- insert into phpa_invoicetypes (number, description, charge, amount_category, vat_percent, vat_category) values (8, 'Beweisgebuehr', NULL, 1200, 19, 3806);
-- insert into phpa_invoicetypes (number, description, charge, amount_category, vat_percent, vat_category) values (9, 'Vergleichsgebuehr', NULL, 1370, 0, 0);
-- insert into phpa_invoicetypes (number, description, charge, amount_category, vat_percent, vat_category) values (10, 'Zwangsvollstreckungsgebuehr', NULL, 1370, 0, 0);
-- insert into phpa_invoicetypes (number, description, charge, amount_category, vat_percent, vat_category) values (11, 'Gebuehr fuer eidesstattliche Versicherung', NULL, 1370, 0, 0);
-- insert into phpa_invoicetypes (number, description, charge, amount_category, vat_percent, vat_category) values (12, 'Tage- und Abwesenheitsgeld', NULL, 1200, 19, 3806);
-- insert into phpa_invoicetypes (number, description, charge, amount_category, vat_percent, vat_category) values (13, 'Schreibauslagen - Fotokopien', NULL, 1200, 19, 3806);
-- insert into phpa_invoicetypes (number, description, charge, amount_category, vat_percent, vat_category) values (14, 'Porto, Telefon-, Telefax und BTX-Auslagen -Pauschale-', NULL, 1200, 19, 3809);


-- Allgemeine geb.en:
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (15, '1,5 Vergleichssgeb. gem. §§ 2, 13 Nr. 1000 VV RVG', NULL, 1.5, 1200, 19, 3806);
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (16, '1,0 Vergleichsgeb. gem. §§ 2,13 Nr. 1003 VV RVG', NULL, 1.0, 1200, 19, 3806);
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (17, '0,3 Erhöhung gem. §§ 2,13 Nr. 1008 VV RVG', NULL, 0.3, 1200, 19, 3806);

-- außergerichtliche Vertretung:
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (18, '1,3 Geschäftsgeb. gem. §§ 2, 13 Nr. 2300 VV RVG', NULL, 1.3, 1200, 19, 3806);

-- Sozialrecht:
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (19, 'Gesch.geb. gem. §§ 2, 14 Nr. 2400 VV RVG (Rahmengeb.)', NULL, 1.0, 1200, 19, 3806);
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (20, 'Verf.-geb. gem. §§ 2, 14 Nr. 3102 VV RVG (Rahmengeb.)', NULL, 1.0, 1200, 19, 3806);
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (21, 'Terminsgeb. gem. §§ 2, 14 Nr. 3106 VV RVG (Rahmengeb.)', NULL, 1.0, 1200, 19, 3806);

-- gerichtliche Vertretung: erster Rechtszug
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (22, '1,3 Verf.-geb. gem. §§ 2, 13 Nr. 3100 VV RVG', NULL, 1.3, 1200, 19, 3806);
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (23, '1,2 Terminsgeb. gem. §§ 2, 13 Nr. 3104 VV RVG', NULL, 1.2, 1200, 19, 3806);

-- gerichtliche Vertretung: Berufung, Revision
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (24, '1,6 Verf.-geb. gem. §§ 2, 13 Nr. 3200 VV RVG', NULL, 1.6, 1200, 19, 3806);

-- Mahnverfahren:
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (25, '1,0 Verf.-geb. gem §§ 2, 13 Nr. 3305 VV RVG', NULL, 1.0, 1200, 19, 3806);
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (26, '0,5 Verf.-geb. gem §§ 2, 13 Nr. 3307 VV RVG', NULL, 0.5, 1200, 19, 3806);
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (27, '0,5 Verf.-geb. gem. §§ 2, 13 Nr. 3308 VV RVG', NULL, 0.5, 1200, 19, 3806);

-- Zwangsvollstreckung:
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (28, '0,3 Verf.-geb. gem. §§ 2, 13 Nr. 3309 VV RVG', NULL, 0.3, 1200, 19, 3806);
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (29, '0,3 Terminsgeb. gem. §§ 2, 13 Nr. 3310 VV RVG', NULL, 0.3, 1200, 19, 3806);

-- Strafsachen:
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (30, 'Grundgeb. gem. §§ 2, 14 Nr. 4100 VV RVG (Rahmengeb.)', NULL, 1.0, 1200, 19, 3806);
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (31, 'Verf.-geb. gem. §§ 2, 14 Nr. 4104 VV RVG (Rahmengeb.)', NULL, 1.0, 1200, 19, 3806);
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (32, 'Verf.-geb. gem. §§ 2, 14 Nr. 4106 VV RVG (Rahmengeb.)', NULL, 1.0, 1200, 19, 3806);
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (33, 'Terminsgeb. je Verh.Tag gem. §§ 2, 14 Nr. 4108 VV RVG (Rahmengeb.)', NULL, 1.0, 1200, 19, 3806);

-- Pauschale:
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (34, 'Ablichtungen und Ausdrucke gem. Nr. 7000 VV RVG', NULL, 1.0, 1200, 19, 3806);
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (35, 'Post- und Telekommunikationsentgelte gem. Nr. 7002 VV RVG', NULL, 1.0, 1200, 19, 3806);
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (36, 'Fahrtkosten km x 0,3 x 2) gem. Nr. 7003 VV RVG', NULL, 1.0, 1200, 19, 3806);
insert into phpa_invoicetypes (number, description, charge, chargefactor, amount_category, vat_percent, vat_category) values (37, 'Fahrtkosten (z.B. Bahn (in voller Höhe)) gem. Nr. 7008 VV RVG', NULL, 1.0, 1200, 19, 3806);



insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (0,	25);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (300,	25);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (600,	45);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (900,	65);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (1200,	85);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (1500,	105);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (2000,	133);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (2500,	161);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (3000,	189);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (3500,	217);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (4000,	245);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (4500,	273);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (5000,	301);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (6000,	338);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (7000,	375);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (8000,	412);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (9000,	449);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (10000, 486);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (13000, 526);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (16000, 566);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (19000, 606);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (22000, 646);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (25000, 686);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (30000, 758);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (35000, 830);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (40000, 902);

insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (45000, 974);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (50000, 1046);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (65000, 1123);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (80000, 1200);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (95000, 1277);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (110000, 1354);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (125000, 1431);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (140000, 1508);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (155000, 1585);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (170000, 1662);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (185000, 1739);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (200000, 1816);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (230000, 1934);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (260000, 2052);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (290000, 2170);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (320000, 2288);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (350000, 2406);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (380000, 2524);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (410000, 2642);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (440000, 2760);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (470000, 2878);
insert into phpa_rvgcharges  (rvgvalue, rvgcharge) values (500000, 2996);

-- Demo Configuration; cannot live without ;-)
insert into  phpa_config (title, name, prename, organization, street, zip, city, language, pdf_command) 
  values ('Frau', 'Duck', 'Daisy', 'Rechtsanwaltskanzlei', 'Bahnhofstr. 12', '12344', 'Entenhausen', 'DE', 'lowriter --headless --convert-to pdf %f --outdir %p');

