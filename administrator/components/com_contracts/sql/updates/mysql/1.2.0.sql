drop table if exists `#__mkv_contract_stand_delegates`;

create table `#__mkv_contract_stand_delegates` (
                                                      id int unsigned not null auto_increment primary key,
                                                      standID int unsigned not null,
                                                      contractID int unsigned not null,
                                                      unique index `#__mkv_contract_stand_delegates_uindex` (standID, contractID),
                                                      constraint `#__mkv_contract_stand_delegates_cs_standID_id_fk` foreign key (standID)
                                                          references `#__mkv_contract_stands` (id) on update cascade on delete cascade,
                                                      constraint `#__mkv_contract_stand_delegates_contracts_contractID_id_fk` foreign key (contractID)
                                                          references `#__mkv_contracts` (id) on update cascade on delete cascade
) character set utf8 collate utf8_general_ci;

insert into `#__mkv_contract_stand_delegates` select * from `#__prj_stands_delegate`;

