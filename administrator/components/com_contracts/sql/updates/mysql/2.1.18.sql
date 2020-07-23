alter table `#__mkv_contract_parents`
    add contractStandID int unsigned null default null;

create temporary table `#__tmp_delegates_contracts` as
select ep.title as parent, ec.title as children, cp.contractID as contractChildren
from `#__mkv_contract_parents` cp
         left join `#__mkv_contracts` cc on cp.contractID = cc.id
         left join `#__mkv_companies` ec on ec.id = cc.companyID
         left join `#__mkv_companies` ep on cp.companyID = ep.id
         left join `#__mkv_contracts` cpar on cpar.companyID = ep.id and cpar.projectID = cc.projectID
         left join `#__mkv_projects` p on p.id = cpar.projectID
         left join `#__mkv_contract_stands` cs on cs.contractID = cpar.id
         left join `#__mkv_stands` s on s.id = cs.standID
where p.id = 11
group by p.id, parent, children, contractChildren
having count(cs.id) = 1;

create temporary table `#__tmp_delegates_1` as
select cp.id, ep.title as parent, ec.title as children, cs.id as contractStandID, s.number
from `#__mkv_contract_parents` cp
         left join `#__mkv_contracts` cc on cp.contractID = cc.id
         left join `#__mkv_companies` ec on ec.id = cc.companyID
         left join `#__mkv_companies` ep on cp.companyID = ep.id
         left join `#__mkv_contracts` cpar on cpar.companyID = ep.id and cpar.projectID = cc.projectID
         left join `#__mkv_projects` p on p.id = cpar.projectID
         left join `#__mkv_contract_stands` cs on cs.contractID = cpar.id
         left join `#__mkv_stands` s on s.id = cs.standID
where cp.contractID in (select contractChildren from `#__tmp_delegates_contracts`) and cs.id is not null;

update `#__mkv_contract_parents` u set u.contractStandID = (select d.contractStandID from `#__tmp_delegates_1` d where d.id = u.id);

drop table `#__tmp_delegates_1`;
drop table `#__tmp_delegates_contracts`;
