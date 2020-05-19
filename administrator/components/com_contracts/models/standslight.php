<?php
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

class ContractsModelStandsLight extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                's.id',
                's.number',
                's.ordering',
                'status',
                'search',
            );
        }
        parent::__construct($config);
        $input = JFactory::getApplication()->input;
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
        $limit = 0;

        $query
            ->select("cs.id, cs.status")
            ->select("s.square, s.number")
            ->select("e.title as company")
            ->select("p.title as project")
            ->from("#__mkv_contract_stands cs")
            ->leftJoin("#__mkv_contracts c on c.id = cs.contractID")
            ->leftJoin("#__mkv_companies e on e.id = c.companyID")
            ->leftJoin("#__mkv_stands s on s.id = cs.standID");
        if (!empty($this->contractIDs)) {
            $cids = implode(', ', $this->contractIDs);
            $query->where("cs.contractID in ({$cids})");
        }

        $query->order($this->_db->escape($orderCol . ' ' . $orderDirn));
        $this->setState('list.limit', $limit);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = [];
        $ids = [];
        foreach ($items as $item) {
            $arr = [];
            $arr['id'] = $item->id;
            $ids[] = $item->id;
            $arr['number'] = $item->number;
            $arr['square'] = JText::sprintf('COM_CONTRACTS_STANDS_SQUARE', $item->square);
            $arr['status'] = JText::sprintf("COM_CONTRACTS_STAND_STATUS_{$item->status}");
            $arr['company'] = $item->company;
            $arr['project'] = $item->project;
            $result[$item->id] = $arr;
        }
        return $result;
    }

    protected function populateState($ordering = 's.number', $direction = 'ASC')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $status = $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status');
        $this->setState('filter.status', $status);
        parent::populateState($ordering, $direction);
        ContractsHelper::check_refresh();
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.status');
        return parent::getStoreId($id);
    }

    private $export, $contractIDs;
}
