<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TableContractsIncoming extends Table
{
    var $id = null;
    var $contractID = null;
    var $doc_status = null;
    var $pvn_1 = null;
    var $pvn_1a = null;
    var $pvn_1b = null;
    var $pvn_1v = null;
    var $pvn_1g = null;
    var $catalog_info = null;
    var $catalog_logo = null;
    var $no_exhibit = null;
    var $info_arrival = null;

	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__mkv_contract_incoming_info', 'id', $db);
	}

	public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
