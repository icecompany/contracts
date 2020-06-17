<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TableContractsContracts extends Table
{
    var $id = null;
    var $projectID = null;
    var $companyID = null;
    var $managerID = null;
    var $status = null;
    var $dat = null;
    var $number = null;
    var $number_free = null;
    var $currency = null;
    var $amount = null;
    var $payments = null;
    var $debt = null;
    var $tasks_count = null;
    var $tasks_date = null;

	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__mkv_contracts', 'id', $db);
	}

	public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
