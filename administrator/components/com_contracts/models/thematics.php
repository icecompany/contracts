<?php
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

class ContractsModelThematics extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'ct.id',
                'search',
            );
        }
        parent::__construct($config);
        $input = JFactory::getApplication()->input;
        $this->contractID = $config['contractID'] ?? 0;
        $this->export = ($input->getString('format', 'html') === 'html') ? false : true;
        $this->return = ContractsHelper::getReturnUrl();
    }

    protected function _getListQuery()
    {
        $query = $this->_db->getQuery(true);

        $query
            ->select("ct.thematicID")
            ->from("#__mkv_contract_thematics ct")
            ->where("ct.contractID = {$this->_db->q($this->contractID)}");

        $this->setState('list.limit', 0);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = [];
        foreach ($items as $item) {
            $result[] = $item->thematicID;
        }
        return $result;
    }

    protected function populateState($ordering = 'ct.id', $direction = 'ASC')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        return parent::getStoreId($id);
    }

    private $export, $contractID, $return;
}
