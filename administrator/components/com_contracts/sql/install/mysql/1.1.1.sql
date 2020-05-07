drop table if exists `#__mkv_contract_items`;
drop table if exists `#__mkv_contract_stands`;

create table `#__mkv_contract_stands` (
                                             id int unsigned not null primary key auto_increment,
                                             contractID int unsigned not null,
                                             standID int unsigned not null,
                                             freeze text null default null,
                                             `status` tinyint(4) not null default 0,
                                             comment text null default null,
                                             checked_out int not null default 0,
                                             checked_out_time timestamp null default null,
                                             unique index `#__mkv_contract_stands_contractID_standID_uindex` (contractID, standID),
                                             index `#__mkv_contract_stands_status_index` (`status`),
                                             constraint `#__mkv_contract_stands_#__mkv_contracts_contractID_id_fk` foreign key (contractID)
                                                 references `#__mkv_contracts` (id)
                                                 on update cascade on delete cascade,
                                             constraint `#__mkv_contract_stands_#__mkv_stands_standID_id_fk` foreign key (standID)
                                                 references `#__mkv_stands` (id)
                                                 on update restrict on delete restrict
) character set utf8 collate utf8_general_ci;

insert into `#__mkv_contract_stands`
select s.id, contractID, catalogID, freeze, s.`status`, s.`comment`, 0, null
from `#__prj_stands` s
         left join `#__prj_catalog` c on s.catalogID = c.id
where c.titleID in (1, 2, 7);

create table `#__mkv_contract_items` (
                                            id int unsigned not null primary key auto_increment,
                                            contractID int unsigned not null,
                                            itemID smallint unsigned not null,
                                            columnID tinyint unsigned not null default 1,
                                            contractStandID int unsigned null default null,
                                            factor decimal(3,2) not null default 1,
                                            markup decimal(3,2) not null default 1,
                                            cost decimal(11,2) not null default 0,
                                            `value` decimal(11,2) not null default 1,
                                            value2 decimal(11,2) null default null,
                                            amount decimal(11,2) not null default 0,
                                            index `#__mkv_contract_items_columnID_index` (columnID),
                                            index `#__mkv_contract_items_amount_index` (amount),
                                            constraint `#__mkv_contract_items_#__mkv_contracts_contractID_id_fk`
                                                foreign key (contractID) references `#__mkv_contracts` (id)
                                                    on update cascade on delete restrict,
                                            constraint `#__mkv_contract_items_#__mkv_price_items_priceID_id_fk`
                                                foreign key (itemID) references `#__mkv_price_items` (id)
                                                    on update cascade on delete restrict,
                                            constraint `#__mkv_contract_items_#__mkv_contract_stands_standID_id_fk`
                                                foreign key (contractStandID) references `#__mkv_contract_stands` (id)
                                                    on update cascade on delete cascade
) character set utf8 collate utf8_general_ci;

insert into `#__mkv_contract_items`
select ci.id,
       ci.contractID,
       ci.itemID,
       ci.columnID,
       st.id,
       ifnull(ci.factor,1),
       ifnull(ci.markup,1),
       round(((case `c`.`currency`
                   when 'rub' then `i`.`price_rub`
                   when 'usd' then `i`.`price_usd`
                   when 'eur' then `i`.`price_eur` end)
           *
              (case `ci`.`columnID`
                   when 1 then `i`.`column_1`
                   when 2 then `i`.`column_2`
                   when 3 then `i`.`column_3` end)), 2) as cost,
       ci.value,
       ci.value2,
       round(((((((case `c`.`currency`
                       when 'rub' then `i`.`price_rub`
                       when 'usd' then `i`.`price_usd`
                       when 'eur' then `i`.`price_eur` end)
           *
                  (case `ci`.`columnID`
                       when 1 then `i`.`column_1`
                       when 2 then `i`.`column_2`
                       when 3 then `i`.`column_3` end)) * `ci`.`value`) * ifnull(`ci`.`value2`, 1)) * ifnull(`ci`.`markup`, 1)) -
              (((((case `c`.`currency`
                       when 'rub' then `i`.`price_rub`
                       when 'usd' then `i`.`price_usd`
                       when 'eur' then `i`.`price_eur` end)
                  *
                  (case `ci`.`columnID`
                       when 1 then `i`.`column_1`
                       when 2 then `i`.`column_2`
                       when 3 then `i`.`column_3` end)) * `ci`.`value`) * ifnull(`ci`.`value2`, 1)) * (1 - `ci`.`factor`))), 2) as amount
from `#__prj_contract_items` ci
         left join `#__prj_stands` st on st.contractID = ci.contractID and st.itemID = ci.itemID and st.columnID = ci.columnID
         left join `#__prc_items` i on ci.itemID = i.id
         left join `#__prj_contracts` c on c.id = ci.contractID
where c.prjID in (5, 6, 11)
on duplicate key update amount = VALUES(amount);

update `#__mkv_contracts` c set c.amount = (select ifnull(sum(amount),0) from `#__mkv_contract_items` ci where ci.contractID = c.id);