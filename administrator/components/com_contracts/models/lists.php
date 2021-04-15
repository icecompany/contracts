<?php
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

class ContractsModelLists extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'l.id',
                'l.title',
                'type',
                'search',
            );
        }
        parent::__construct($config);
        $input = JFactory::getApplication()->input;
        $this->contractID = $config['contractID'] ?? [];
        $this->listID = $config['listID'] ?? [];
        $this->export = ($input->getString('format', 'html') === 'html') ? false : true;
        if (!empty($this->contractID)) $this->export = true;
    }

    protected function _getListQuery()
    {
        $query = JFactory::getDbo()->getQuery(true);

        $query
            ->select("l.id, l.contractID, l.listID")
            ->from("#__mkv_contract_lists l");

        if (is_array($this->contractID) && !empty($this->contractID)) {
            $ids = implode(', ', $this->contractID);
            $query->where("l.contractID in ({$ids})");
        }
        if (is_numeric($this->contractID) && $this->contractID > 0) {
            $query->where("l.contractID = {$this->_db->q($this->contractID)}");
        }

        if (!$this->export) {
            $query
                ->select("ls.title as list")
                ->leftJoin("#__mkv_lists ls on ls.id = l.listID");

            /* Сортировка */
            $orderCol = $this->state->get('list.ordering');
            $orderDirn = $this->state->get('list.direction');
            $limit = $this->state->get('list.limit');
        }
        else {
            $limit = 0;
            $orderCol = "l.id";
            $orderDirn = "ASC";
        }
        $query->order("{$orderCol} {$orderDirn}");
        $this->setState('list.limit', $limit);
        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = [];
        foreach ($items as $item) {
            if (!empty($this->contractID)) {
                if (is_numeric($this->contractID)) $result[] = $item->listID;
                if (is_array($this->contractID) && !empty($this->contractID)) {
                    if (!isset($result[$item->contractID])) $result[$item->contractID] = [];
                    $result[$item->contractID][] = $item->listID;
                }
            }
        }
        return $result;
    }

    protected function populateState($ordering = 'l.type', $direction = 'ASC')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $type = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type');
        $this->setState('filter.type', $type);
        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.type');
        $id .= ':' . $this->getState('filter.search');
        return parent::getStoreId($id);
    }

    private $export, $contractID, $listID;
}
