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
        $this->byCompanyID = $config['byCompanyID'] ?? false;
        $this->byContractID = $config['byContractID'] ?? false;
        $this->export = ($input->getString('format', 'html') === 'html') ? false : true;
    }

    protected function _getListQuery()
    {
        $query = $this->_db->getQuery(true);

        //Ограничение длины списка
        $limit = 0;

        $query
            ->select("cs.id, cs.status, cs.contractID")
            ->select("s.square, s.number")
            ->select("e.title as company, e.id as companyID")
            ->from("#__mkv_contract_stands cs")
            ->leftJoin("#__mkv_contracts c on c.id = cs.contractID")
            ->leftJoin("#__mkv_companies e on e.id = c.companyID")
            ->leftJoin("#__mkv_stands s on s.id = cs.standID");
        if (!empty($this->contractIDs)) {
            $cids = implode(', ', $this->contractIDs);
            $query->where("cs.contractID in ({$cids})");
        }

        $this->setState('list.limit', $limit);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = [];
        foreach ($items as $item) {
            $arr = [];
            $arr['id'] = $item->id;
            $arr['number'] = $item->number;
            $arr['square'] = JText::sprintf('COM_CONTRACTS_STANDS_SQUARE', $item->square);
            $arr['status'] = JText::sprintf("COM_CONTRACTS_STAND_STATUS_{$item->status}");
            $arr['company'] = $item->company;
            if ($this->byCompanyID) $result[$item->companyID][] = $item->number;
            if ($this->byContractID) $result[$item->contractID][] = $arr;
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
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.status');
        return parent::getStoreId($id);
    }

    private $export, $contractIDs, $byCompanyID, $byContractID;
}
