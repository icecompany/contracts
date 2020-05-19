<?php
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

class ContractsModelDelegates extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'd.id',
            );
        }
        parent::__construct($config);
        $input = JFactory::getApplication()->input;
        $this->standIDs = $config['standIDs'] ?? [];
        $this->contractIDs = $config['contractIDs'] ?? [];
        $this->export = ($input->getString('format', 'html') === 'html') ? false : true;
    }

    protected function _getListQuery()
    {
        $query = $this->_db->getQuery(true);

        /* Сортировка */
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');

        //Ограничение длины списка
        $limit = (!$this->export) ? $this->getState('list.limit') : 0;

        $query
            ->select("d.id, d.standID, d.contractID")
            ->select("e.title as company")
            ->from("#__mkv_contract_stand_delegates d")
            ->leftJoin("#__mkv_contracts c on c.id = d.contractID")
            ->leftJoin("#__mkv_companies e on e.id = c.companyID");
        if (!empty($this->contractIDs) && empty($this->standIDs)) {
            $ids = implode(', ', $this->contractIDs);
            $query->where("d.contractID in ({$ids})");
        }
        if (empty($this->contractIDs) && !empty($this->standIDs)) {
            $ids = implode(', ', $this->standIDs);
            $query->where("d.standID in ({$ids})");
        }

        $query->order($this->_db->escape($orderCol . ' ' . $orderDirn));
        $this->setState('list.limit', $limit);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = [];
        foreach ($items as $item) {
            if (!empty($this->contractIDs) && empty($this->standIDs)) {
                $result[$item->contractID][] = $item->standID;
            }
            if (empty($this->contractIDs) && !empty($this->standIDs)) {
                $result[$item->standID][] = $item->company;
            }
        }
        return $result;
    }

    protected function populateState($ordering = 'd.id', $direction = 'asc')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        parent::populateState($ordering, $direction);
        ContractsHelper::check_refresh();
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        return parent::getStoreId($id);
    }

    private $export, $standIDs, $contractIDs;
}
