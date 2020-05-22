alter table `#__mkv_contracts`
    add index `#__mkv_contracts_projectID_managerID_status_index` (projectID, managerID, status);

