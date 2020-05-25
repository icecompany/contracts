alter table `#__mkv_contract_incoming_info`
    add no_exhibit tinyint not null default 0 comment 'Отсутствуют экспонаты',
    add index `#__mkv_contract_incoming_info_pvn_1_index` (pvn_1),
    add index `#__mkv_contract_incoming_info_pvn_1a_index` (pvn_1a),
    add index `#__mkv_contract_incoming_info_pvn_1b_index` (pvn_1b),
    add index `#__mkv_contract_incoming_info_pvn_1v_index` (pvn_1v),
    add index `#__mkv_contract_incoming_info_pvn_1g_index` (pvn_1g),
    add index `#__mkv_contract_incoming_info_pvn_catalog_info_index` (catalog_info),
    add index `#__mkv_contract_incoming_info_pvn_catalog_logo_index` (catalog_logo),
    add index `#__mkv_contract_incoming_info_pvn_catalog_no_exhibit_index` (no_exhibit);
