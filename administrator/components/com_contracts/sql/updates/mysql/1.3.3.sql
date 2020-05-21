create view `#__mkv_companies_contacts_occupancy` as
select companyID, sum(for_accreditation) as for_accreditation, sum(for_building) as for_building from `#__mkv_companies_contacts`
group by companyID;

alter table `#__mkv_contracts` add index `#__mkv_contracts_projectID_status_index` (projectID, status);


