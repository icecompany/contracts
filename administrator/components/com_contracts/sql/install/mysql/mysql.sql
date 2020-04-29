create table `#__mkv_contract_statuses` (
                                               id tinyint unsigned not null auto_increment primary key,
                                               code tinyint unsigned not null,
                                               title varchar(255) not null,
                                               ordering int unsigned not null default 0,
                                               index `#__mkv_contract_statuses_ordering_index` (ordering),
                                               unique index `#__mkv_contract_statuses_code_index` (code)
) character set utf8 collate utf8_general_ci;

insert into `#__mkv_contract_statuses` (code, title, ordering) VALUES
(0, 'Отказ', 0),
(1, 'Договор', 1),
(2, 'Направлено приглашение', 2),
(3, 'Планируют участие', 3),
(4, 'Заявка на участие', 4),
(5, 'Соэкспонент', 5),
(6, 'Соэкспонент без оплаты', 6),
(7, 'Выбор подрядчика', 7),
(8, 'Подрядчик', 8),
(9, 'Некоммерческий', 9),
(10, 'Счёт', 10);

create table `#__mkv_contracts` (
                                       id int unsigned not null auto_increment primary key,
                                       projectID smallint unsigned not null,
                                       companyID int unsigned not null,
                                       managerID int not null,
                                       status tinyint unsigned null default null,
                                       dat date null default null,
                                       `number` int null default null,
                                       number_free varchar(20) null default null,
                                       currency set('rub', 'usd', 'eur') not null default 'rub',
                                       amount double(11,2) not null default 0 comment 'Сумма договора',
                                       checked_out int unsigned not null default 0,
                                       checked_out_time datetime null default null,
                                       index `#__mkv_contracts_dat_index` (dat),
                                       index `#__mkv_contracts_currency_index` (currency),
                                       index `#__mkv_contracts_amount_index_index` (amount),
                                       constraint `#__mkv_contracts_#__users_managerID_id_fk` foreign key (managerID)
                                           references `#__users` (id)
                                           on update cascade on delete restrict,
                                       constraint `#__mkv_contracts_#__mkv_projects_projectID_id_fk` foreign key (projectID)
                                           references `#__mkv_projects` (id)
                                           on update cascade on delete restrict,
                                       constraint `#__mkv_contracts_#__mkv_companies_companyID_id_fk` foreign key (companyID)
                                           references `#__mkv_companies` (id)
                                           on update cascade on delete restrict,
                                       constraint `#__mkv_contracts_#__mkv_contract_statuses_status_code_fk` foreign key (status)
                                           references `#__mkv_contract_statuses` (code)
                                           on update cascade on delete restrict
) character set utf8 collate utf8_general_ci;

insert into `#__mkv_contracts`
select id, prjID, expID, managerID,
       case
           when `status` = -1 then null
           when `status` = 0 then 0
           when `status` = 1 then 1
           when `status` = 2 then 2
           when `status` = 3 then 3
           when `status` = 4 then 4
           when `status` = 7 then 7
           when `status` = 8 then 8
           when `status` = 9 then 9
           when `status` = 10 then 10
           when `status` = 11 then 5
           when `status` = 12 then 6
           end,
       dat, `number`, number_free, currency, 0, 0, null
from `#__prj_contracts`;

create table `#__mkv_contract_parents` (
                                              id int unsigned not null auto_increment primary key,
                                              contractID int unsigned not null,
                                              companyID int unsigned not null,
                                              constraint `#__mkv_contract_parents_#__mkv_contracts_contractID_id_fk` foreign key (contractID)
                                                  references `#__mkv_contracts` (id)
                                                  on update cascade on delete cascade,
                                              constraint `#__mkv_contract_parents_#__mkv_companies_companyID_id_fk` foreign key (companyID)
                                                  references `#__mkv_companies` (id)
                                                  on update cascade on delete cascade
) character set utf8 collate utf8_general_ci;

insert into `#__mkv_contract_parents`
select null, id, parentID from `#__prj_contracts` where parentID is not null;

create table `#__mkv_contract_incoming_info` (
                                                    id int unsigned not null auto_increment primary key,
                                                    contractID int unsigned not null,
                                                    doc_status tinyint(1) not null default 0,
                                                    pvn_1 tinyint(1) not null default 0,
                                                    pvn_1a tinyint(1) not null default 0,
                                                    pvn_1b tinyint(1) not null default 0,
                                                    pvn_1v tinyint(1) not null default 0,
                                                    pvn_1g tinyint(1) not null default 0,
                                                    catalog_info tinyint(1) not null default 0,
                                                    catalog_logo tinyint(1) not null default 0,
                                                    index `#__mkv_contract_incoming_info_doc_status_index` (doc_status),
                                                    constraint `#__mkv_contract_incoming_info_#__mkv_contracts_cID_id_fk` foreign key (contractID)
                                                        references `#__mkv_contracts` (id)
                                                        on update cascade on delete cascade
) character set utf8 collate utf8_general_ci;

insert into `#__mkv_contract_incoming_info`
select null, id, doc_status, pvn_1, pvn_1a, pvn_1b, pvn_1v, pvn_1g, info_catalog, logo_catalog from `#__prj_contracts`;

create table `#__mkv_contract_thematics` (
                                                id int unsigned not null auto_increment primary key,
                                                contractID int unsigned not null,
                                                thematicID smallint unsigned not null,
                                                constraint `#__mkv_contract_thematics_#__mkv_contracts_contractIDid_fk` foreign key (contractID)
                                                    references `#__mkv_contracts` (id)
                                                    on update cascade on delete cascade,
                                                constraint `#__mkv_contract_thematics_#__mkv_thematics_thematicIDid_fk` foreign key (thematicID)
                                                    references `#__mkv_thematics` (id)
                                                    on update cascade on delete restrict
) character set utf8 collate utf8_general_ci;

insert into `#__mkv_contract_thematics`
select * from `#__prj_contract_rubrics`;

