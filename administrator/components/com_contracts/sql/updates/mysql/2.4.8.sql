alter table `#__mkv_contracts`
    add is_sponsor bool not null default 0 comment 'Спонсорский контракт',
    add index `#__mkv_contracts_is_sponsor_index` (is_sponsor);

create temporary table `#__tmp_sponsors` as
select ci.contractID
from `#__mkv_contract_items` ci
         left join `#__mkv_price_items` pi on ci.itemID = pi.id
where pi.title like '%Спонсорский%';

update `#__mkv_contracts` c
set is_sponsor = 1 where c.id in (
    select contractID from `#__tmp_sponsors`
);

drop table `#__tmp_sponsors`;
