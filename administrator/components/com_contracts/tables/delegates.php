<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TableContractsDelegates extends Table
{
    var $id = null;
    var $standID = null;
    var $contractID = null;
	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__mkv_contract_stand_delegates', 'id', $db);
	}

	public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
