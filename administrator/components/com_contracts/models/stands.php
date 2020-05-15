<?php
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

class ContractsModelStands extends ListModel
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
            ->select("cs.id, cs.freeze, cs.status, cs.comment")
            ->select("s.square, s.number")
            ->select("i.id as itemID, i.type, i.title as item")
            ->select("e.title as company")
            ->select("p.title as project")
            ->select("ci.value")
            ->select("ifnull(c.number_free, c.number) as contract_number, c.dat")
            ->select("st.title as contract_status")
            ->from("#__mkv_contract_stands cs")
            ->leftJoin("#__mkv_contracts c on c.id = cs.contractID")
            ->leftJoin("#__mkv_companies e on e.id = c.companyID")
            ->leftJoin("#__mkv_projects p on p.id = c.projectID")
            ->leftJoin("#__mkv_stands s on s.id = cs.standID")
            ->leftJoin("#__mkv_contract_items ci on ci.contractStandID = cs.id")
            ->leftJoin("#__mkv_contract_statuses st on st.code = c.status")
            ->leftJoin("#__mkv_price_items i on i.id = ci.itemID");
        $search = (!$this->export) ? $this->getState('filter.search') : JFactory::getApplication()->input->getString('search', '');
        if (!empty($search)) {
            if (stripos($search, 'cid:') !== false) { //Поиск по ID сделки
                $id = explode(':', $search);
                $id = $id[1];
                if (is_numeric($id)) {
                    $query->where("cs.contractID = {$this->_db->q($id)}");
                }
            }
            else {
                $text = $this->_db->q("%{$search}%");
                $query->where("(s.number like {$text})");
            }
        }
        $project = PrjHelper::getActiveProject();
        if (is_numeric($project)) {
            $query->where("c.projectID = {$this->_db->q($project)}");
        }
        $status = $this->getState('filter.status');
        if (is_array($status) && !empty($status)) {
            if (!in_array(100, $status)) {
                if (in_array('', $status)) {
                    $query->where('c.status is null');
                }
                else {
                    $statuses = implode(", ", $status);
                    $query->where("c.status in ($statuses)");
                }
            }
        }

        $query->order($this->_db->escape($orderCol . ' ' . $orderDirn));
        $this->setState('list.limit', $limit);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = ['stands' => [], 'items' => [], 'titles' => []];
        $return = ContractsHelper::getReturnUrl();
        foreach ($items as $item) {
            $arr = [];
            $arr['id'] = $item->id;
            $arr['number'] = $item->number;
            $arr['square_clean'] = $item->square;
            $arr['square'] = JText::sprintf('COM_CONTRACTS_STANDS_SQUARE', $item->square);
            $arr['freeze'] = $item->freeze;
            $arr['status'] = JText::sprintf("COM_CONTRACTS_STAND_STATUS_{$item->status}");
            $arr['comment'] = $item->comment;
            $arr['company'] = $item->company;
            $arr['project'] = $item->project;
            $arr['contract_status'] = $item->contract_status ?? JText::sprintf("COM_CONTRACTS_CONTRACT_STATUS_IN_PROJECT");
            $arr['contract_number'] = $item->contract_number ?? '';
            $arr['contract_dat'] = (!empty($item->dat)) ? JDate::getInstance($item->dat)->format("d.m.Y") : '';
            $url = JRoute::_("index.php?option={$this->option}&amp;task=stand.edit&amp;id={$item->id}&amp;return={$return}");
            $arr['edit_link'] = JHtml::link($url, $item->number);
            if (!isset($result['stands'][$item->id])) $result['stands'][$item->id] = $arr;
            if (!isset($result['titles'][$item->itemID]) && !empty($item->item) && !empty($item->itemID)) $result['titles'][$item->itemID] = $item->item;
            if (!isset($result['items'][$item->id][$item->itemID])) $result['items'][$item->id][$item->itemID] = $item->value;
        }
        asort($result['titles']);
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

    private $export;
}
