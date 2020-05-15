<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TableContractsStands extends Table
{
    var $id = null;
    var $contractID = null;
    var $standID = null;
    var $freeze = null;
    var $comment = null;
    var $checked_out = null;
    var $checked_out_time = null;
	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__mkv_contract_stands', 'id', $db);
	}

	public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
