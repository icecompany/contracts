<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TableContractsStatuses extends Table
{
    var $id = null;
    var $code = null;
    var $title = null;
    var $ordering = null;

	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__mkv_contract_statuses', 'id', $db);
	}

	public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
