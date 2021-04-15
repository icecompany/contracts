create table `#__mkv_lists_types`
(
    id    tinyint unsigned not null auto_increment primary key,
    title text             not null
) character set utf8mb4
  collate utf8mb4_general_ci;

create table `#__mkv_lists`
(
    id     smallint unsigned not null auto_increment primary key,
    typeID tinyint unsigned  not null,
    title  text              not null,
    constraint `#__mkv_lists_#__mkv_lists_types_typeID_id_fk`
        foreign key (typeID) references `#__mkv_lists_types` (id)
            on update cascade on delete restrict
) character set utf8mb4
  collate utf8mb4_general_ci;

create table `#__mkv_contract_lists`
(
    id         int unsigned      not null auto_increment primary key,
    contractID int unsigned      not null,
    listID     smallint unsigned not null,
    constraint `#__mkv_contract_lists_#__mkv_contracts_contractID_id_fk`
        foreign key (contractID) references `#__mkv_contracts` (id)
            on update cascade on delete cascade,
    constraint `#__mkv_contract_lists_#__mkv_lists_listID_id_fk`
        foreign key (listID) references `#__mkv_lists` (id)
            on update cascade on delete restrict,
    unique index `#__mkv_contract_lists_contractID_listID_uindex` (contractID, listID)
) character set utf8mb4
  collate utf8mb4_general_ci;

insert into `#__mkv_lists_types` (`id`, `title`)
values ('1', 'МЧС');

insert into `#__mkv_lists` (id, typeID, title)
VALUES (null, 1, 'Список Гречушкина'),
       (null, 1, 'ГУПО'),
       (null, 1, 'ДТО'),
       (null, 1, 'УБВО'),
       (null, 1, 'ДИТ (связь)'),
       (null, 1, 'Арктика'),
       (null, 1, 'ВНИИПО'),
       (null, 1, 'СЗФО'),
       (null, 1, 'УА'),
       (null, 1, 'ДГО');
