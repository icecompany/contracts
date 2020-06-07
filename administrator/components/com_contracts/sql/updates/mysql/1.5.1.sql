alter table `#__mkv_contracts`
    add tasks_count smallint not null default 0 comment 'Кол-во активных задач' after debt,
    add tasks_date date null default null comment 'Ближайшая дата задачи' after tasks_count,
    add index `#__mkv_contracts_tasks_count_index` (tasks_count),
    add index `#__mkv_contracts_tasks_date_index` (tasks_date),
    add index `#__mkv_contracts_projectID_managerID_tasks_date_status_index` (projectID, managerID, tasks_date, status);
