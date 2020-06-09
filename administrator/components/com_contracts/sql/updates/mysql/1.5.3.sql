create table `#__mkv_contract_sent_info`
(
    id                     int unsigned not null primary key auto_increment,
    contractID             int unsigned not null,
    invite_date            date         null default null comment 'Дата отправки приглашения',
    invite_outgoing_number varchar(255) null default null comment 'Исходящий номер',
    invite_incoming_number varchar(255) null default null comment 'Входящий номер',
    constraint `#__mkv_contract_sent_info_contracts_contractID_id_fk`
        foreign key (contractID) references `#__mkv_contracts` (id)
            on update cascade on delete cascade
) character set utf8
  collate utf8_general_ci;

insert into `#__mkv_contract_sent_info`
select null, id, null, null, null from `#__mkv_contracts`;

