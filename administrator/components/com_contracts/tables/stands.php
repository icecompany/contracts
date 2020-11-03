<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TableContractsStands extends Table
{
    var $id = null;
    var $type = null;
    var $contractID = null;
    var $standID = null;
    var $freeze = null;
    var $comment = null;
    var $bypassing = null;
    var $production_diversification = null;
    var $production_first_in_forum = null;
    var $production_first_in_world = null;

	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__mkv_contract_stands', 'id', $db);
	}

	public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
