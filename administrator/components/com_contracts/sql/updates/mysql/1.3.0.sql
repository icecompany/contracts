alter table `#__mkv_contract_stands`
    add type tinyint not null default 0 after id,
    add index `#__mkv_contract_stands_type_index` (type);
