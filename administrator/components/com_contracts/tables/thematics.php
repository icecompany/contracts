<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TableContractsThematics extends Table
{
    var $id = null;
    var $contractID = null;
    var $thematicID = null;

	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__mkv_contract_thematics', 'id', $db);
	}

	public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
