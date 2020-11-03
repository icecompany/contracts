alter table `#__mkv_contract_stands`
    add bypassing bool not null default 0 comment 'Стенд входит в маршрут обхода' after `status`,
    add production_diversification text character set utf8mb4 collate utf8mb4_general_ci null default null comment 'Продукция по диверсификации' after bypassing,
    add production_first_in_forum text character set utf8mb4 collate utf8mb4_general_ci null default null comment 'Продукция, представляемая впервые на форуме' after production_diversification,
    add production_first_in_world text character set utf8mb4 collate utf8mb4_general_ci null default null comment 'Продукция, представляемая впервые в мире' after production_first_in_forum,
    modify freeze text character set utf8mb4 collate utf8mb4_general_ci null default null comment 'Надпись на стенде',
    modify comment mediumtext character set utf8mb4 collate utf8mb4_general_ci null default null comment 'Поле комментария',
    drop checked_out,
    drop checked_out_time,
    add index `#__mkv_contract_stands_bypassing_index` (bypassing);

alter table `#__mkv_contract_stands` convert to character set utf8mb4 collate utf8mb4_general_ci;

