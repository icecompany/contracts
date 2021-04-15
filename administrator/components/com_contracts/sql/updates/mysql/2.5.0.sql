create temporary table `#__tmp_cwi` as
select cs.contractID, c.status, cs.id as contractStandID, cs.standID, s.itemID, s.open, s.square,
       if (c.currency = 'rub', pi.price_rub, if (c.currency = 'usd', pi.price_usd, pi.price_eur)) as cost
from `#__mkv_contract_stands` cs
         left join `#__mkv_contract_items` ci on cs.id = ci.contractStandID
         left join `#__mkv_contracts` c on cs.contractID = c.id
         left join `#__mkv_stands` s on cs.standID = s.id
         left join `#__mkv_price_items` pi on s.itemID = pi.id
where ci.id is null and c.projectID in (12, 30);

insert into `#__mkv_contract_items` (id, contractID, itemID, columnID, contractStandID, factor, markup, cost, value)
select null, t.contractID, t.itemID, 1, t.contractStandID, if(t.status != 9, 1, 0), if(t.open = 1, 1, if(t.open = 2, 1.1, if(t.open = 3, 1.15, 1.2))), t.cost, t.square
from `#__tmp_cwi` t;
