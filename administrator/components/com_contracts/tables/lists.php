<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TableContractsLists extends Table
{
    var $id = null;
    var $contractID = null;
    var $listID = null;

	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__mkv_contract_lists', 'id', $db);
	}

	public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
