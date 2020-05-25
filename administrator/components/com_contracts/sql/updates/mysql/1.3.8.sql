alter table `#__mkv_contract_incoming_info`
    add info_arrival tinyint not null default 0 comment 'Прислали график заезда' after no_exhibit,
    add index `#__mkv_contract_incoming_info_info_arrival_index` (info_arrival);
