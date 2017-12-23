alter table phpa_config add column pdf_command varchar(200);
create table phpa_deadlines (
  number serial,
  pfile integer,
  eventday timestamp,
  description varchar(50),
  type integer,
  constraint pri_phpa_deadlines primary key(number),
  foreign key(pfile) references phpa_pfiles(number) on delete cascade
);
grant all on phpa_deadlines to public;
grant all on phpa_deadlines_number_seq to public; 
