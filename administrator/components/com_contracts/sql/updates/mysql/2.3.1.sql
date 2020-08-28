alter table `#__mkv_contract_items`
    add date_1 date null default null after value2,
    add date_2 date null default null after date_1;
