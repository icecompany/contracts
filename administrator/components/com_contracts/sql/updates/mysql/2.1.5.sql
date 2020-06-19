create table `#__mkv_managers_push_channels`
(
    id        smallint     not null auto_increment primary key,
    channelID int unsigned not null,
    api_key   varchar(34)  not null,
    managerID int          not null,
    uid       int          not null,
    constraint `#__mkv_managers_push_channels_#__users_managerID_id_fk` foreign key (managerID)
        references `#__users` (id) on delete cascade on update cascade,
    unique index `#__mkv_managers_push_channels_channelID_managerID_index` (channelID, managerID)
) character set utf8
  collate utf8_general_ci;

