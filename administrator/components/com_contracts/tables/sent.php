<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TableContractsSent extends Table
{
    var $id = null;
    var $contractID = null;
    var $invite_date = null;
    var $invite_outgoing_number = null;
    var $invite_incoming_number = null;

	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__mkv_contract_sent_info', 'id', $db);
	}

	public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
