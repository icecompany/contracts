<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TableContractsParents extends Table
{
    var $id = null;
    var $contractID = null;
    var $companyID = null;
    var $contractStandID = null;
	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__mkv_contract_parents', 'id', $db);
	}

	public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
