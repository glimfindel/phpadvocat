 create table phpa_dfiles (
   number serial,
   pfile integer,
   createdate date,
   address integer,
   dfilecontent text,
   subject varchar(40),
   constraint pri_phpa_dfiles primary key(number),
   foreign key(pfile) references phpa_pfiles(number) on delete cascade
);
grant all on phpa_dfiles to public;
grant all on phpa_dfiles_number_seq to public;
