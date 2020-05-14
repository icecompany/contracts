<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TableContractsItems extends Table
{
    var $id = null;
    var $contractID = null;
    var $itemID = null;
    var $columnID = null;
    var $contractStandID = null;
    var $factor = null;
    var $markup = null;
    var $cost = null;
    var $value = null;
    var $value2 = null;
    var $amount = null;

	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__mkv_contract_items', 'id', $db);
	}

	public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
