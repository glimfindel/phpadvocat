alter table phpa_partner add column mobile varchar(30);

alter table phpa_pfiles add column endnumber varchar(30);
alter table phpa_config add column invoice_base  integer;


alter table phpa_partner add column insurance_number integer;
alter table phpa_partner add column insurance_id varchar(30);

alter table phpa_pfiles add column opposing_rep integer;

alter table phpa_partner alter column type type varchar(30);

alter table phpa_partnertypes alter column type type varchar(30);
insert into phpa_partnertypes values ('gegn. PB');
insert into phpa_partnertypes values ('Versicherung');


alter table phpa_invoices add column service_start date;
alter table phpa_invoices add column service_end date;
alter table phpa_invoices add column invoiceid varchar(30);
alter table phpa_invoicetypes add column chargefactor numeric(10,2);

alter table phpa_invoicetypes alter column description type varchar(120);