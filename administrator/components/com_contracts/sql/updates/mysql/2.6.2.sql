update `#__mkv_contract_stands` cs set cs.type = 5 where cs.standID in (
    select s.id from `#__mkv_stands` s where s.pavilionID in (10, 9, 8, 6, 4)
);