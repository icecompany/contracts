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
        $this->contractIDs = $config['contractIDs'] ?? [];
        $this->thematicIDs = $config['thematicIDs'] ?? [];
        $this->export = ($input->getString('format', 'html') === 'html') ? false : true;
        $this->return = ContractsHelper::getReturnUrl();
    }

    protected function _getListQuery()
    {
        $query = JFactory::getDbo()->getQuery(true);

        $query
            ->select("ct.thematicID")
            ->from("#__mkv_contract_thematics ct");
        if ($this->contractID > 0) {
            $query->where("ct.contractID = {$this->_db->q($this->contractID)}");
        }
        if (!empty($this->contractIDs)) {
            $ids = implode(', ', $this->contractIDs);
            $query
                ->select("ct.contractID, t.title")
                ->leftJoin("#__mkv_thematics t on t.id = ct.thematicID")
                ->where("ct.contractID in ({$ids})");
        }
        if (!empty($this->thematicIDs)) {
            $tid = implode(', ', $this->thematicIDs);
            $query
                ->select("ct.contractID")
                ->where("ct.thematicID in ({$tid})");
        }

        $this->setState('list.limit', 0);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = [];
        foreach ($items as $item) {
            if ($this->contractID > 0) $result[] = $item->thematicID;
            if (!empty($this->contractIDs)) {
                $result[$item->contractID][] = $item->title;
            }
            if (!empty($this->thematicIDs)) {
                if (!array_search($item->contractID, $result)) $result[] = $item->contractID;
            }
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

    private $export, $contractID, $contractIDs, $thematicIDs, $return;
}
