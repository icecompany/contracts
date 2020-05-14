<?php
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

class ContractsModelContracts extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'c.id',
                'c.dat',
                's.ordering', 'status',
                'project', 'p.title',
                'company',
                'manager',
                'number',
                'i.doc_status',
                'c.amount',
                'search',
                'catalog_info', 'i.catalog_info',
                'catalog_logo', 'i.catalog_logo',
                'pvn_1', 'i.pvn_1',
                'pvn_1a', 'i.pvn_1a',
                'pvn_1b', 'i.pvn_1b',
                'pvn_1v', 'i.pvn_1v',
                'pvn_1g', 'i.pvn_1g',
                'doc_status', 'i.doc_status',
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
        $limit = (!$this->export) ? $this->getState('list.limit') : 0;

        $query
            ->select("c.id, c.dat, c.number, c.number_free, c.currency, c.amount")
            ->select("ifnull(c.number_free, c.number) as num")
            ->select("s.title as status")
            ->select("p.title as project")
            ->select("e.title as company")
            ->select("u.name as manager")
            ->select("i.doc_status, i.catalog_info, i.catalog_logo, i.pvn_1, i.pvn_1a, i.pvn_1b, i.pvn_1v, i.pvn_1g")
            ->from("#__mkv_contracts c")
            ->leftJoin("#__mkv_contract_statuses s on s.code = c.status")
            ->leftJoin("#__mkv_projects p on p.id = c.projectID")
            ->leftJoin("#__mkv_companies e on e.id = c.companyID")
            ->leftJoin("#__users u on u.id = c.managerID")
            ->leftJoin("#__mkv_contract_incoming_info i on i.contractID = c.id");
        $search = (!$this->export) ? $this->getState('filter.search') : JFactory::getApplication()->input->getString('search', '');
        if (!empty($search)) {
            if (stripos($search, 'id:') !== false) { //Поиск по ID
                $id = explode(':', $search);
                $id = $id[1];
                if (is_numeric($id)) {
                    $query->where("c.id = {$this->_db->q($id)}");
                }
            }
            else {
                if (stripos($search, 'num:') !== false) { //Поиск по номеру договора
                    $num = explode(':', $search);
                    $num = $num[1];
                    if (is_numeric($num)) {
                        $query->where("c.number = {$this->_db->q($num)}");
                    }
                }
                else {
                    $text = $this->_db->q("%{$search}%");
                    $query->where("(e.title like {$text} or e.title_full like {$text} or e.title_en like {$text})");
                }
            }
        }
        $project = $this->getState('filter.project');
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
        $catalog_info = $this->getState('filter.catalog_info');
        if (is_numeric($catalog_info)) {
            $query->where("i.catalog_info = {$this->_db->q($catalog_info)}");
        }
        $catalog_logo = $this->getState('filter.catalog_logo');
        if (is_numeric($catalog_logo)) {
            $query->where("i.catalog_logo = {$this->_db->q($catalog_logo)}");
        }
        $pvn_1 = $this->getState('filter.pvn_1');
        if (is_numeric($pvn_1)) {
            $query->where("i.pvn_1 = {$this->_db->q($pvn_1)}");
        }
        $pvn_1a = $this->getState('filter.pvn_1a');
        if (is_numeric($pvn_1a)) {
            $query->where("i.pvn_1a = {$this->_db->q($pvn_1a)}");
        }
        $pvn_1b = $this->getState('filter.pvn_1b');
        if (is_numeric($pvn_1b)) {
            $query->where("i.pvn_1b = {$this->_db->q($pvn_1b)}");
        }
        $pvn_1v = $this->getState('filter.pvn_1v');
        if (is_numeric($pvn_1v)) {
            $query->where("i.pvn_1v = {$this->_db->q($pvn_1v)}");
        }
        $pvn_1g = $this->getState('filter.pvn_1g');
        if (is_numeric($pvn_1g)) {
            $query->where("i.pvn_1g = {$this->_db->q($pvn_1g)}");
        }
        $doc_status = $this->getState('filter.doc_status');
        if (is_numeric($doc_status)) {
            $query->where("i.doc_status = {$this->_db->q($doc_status)}");
        }

        if ($orderCol === 'number') {
            $query->where("((c.number is not null and c.number_free is null) or (c.number_free is not null and c.number is null))");
            if ($orderDirn === 'DESC') $orderCol = 'LENGTH(num), num';
            if ($orderDirn === 'desc') $orderCol = 'LENGTH(num) DESC, num';
        }
        if ($orderCol === 'c.dat') {
            $query->where("c.dat is not null");
        }

        $query->order($this->_db->escape($orderCol . ' ' . $orderDirn));
        $this->setState('list.limit', $limit);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = ['items' => []];
        $return = ContractsHelper::getReturnUrl();
        foreach ($items as $item) {
            $arr = [];
            $arr['id'] = $item->id;
            $arr['company'] = $item->company;
            $arr['project'] = $item->project;
            $arr['status'] = $item->status ?? JText::sprintf('COM_CONTRACTS_CONTRACT_STATUS_IN_PROJECT');
            $manager = explode(' ', $item->manager);
            $arr['manager'] = $manager[0];
            $currency = mb_strtoupper($item->currency);
            $amount = number_format((float) $item->amount ?? 0, 2, '.', ' ');
            $arr['dat'] = (!empty($item->dat)) ? JDate::getInstance($item->dat)->format("d.m.Y") : '';
            $arr['currency'] = JText::sprintf("COM_CONTRACTS_CURRENCY_{$currency}_SHORT");
            $arr['amount_full'] = JText::sprintf("COM_CONTRACTS_CURRENCY_{$currency}_AMOUNT_SHORT", $amount);
            $arr['doc_status'] = JText::sprintf("COM_CONTRACTS_DOC_STATUS_{$item->doc_status}_SHORT");
            $arr['number'] = $item->number_free ?? $item->number;
            $url = JRoute::_("index.php?option={$this->option}&amp;task=contract.edit&amp;id={$item->id}&amp;return={$return}");
            $arr['edit_link'] = JHtml::link($url, JText::sprintf('COM_CONTRACTS_ACTION_OPEN'));
            $result['items'][] = $arr;
        }
        return $result;
    }

    protected function populateState($ordering = 'c.id', $direction = 'DESC')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $project = $this->getUserStateFromRequest($this->context . '.filter.project', 'filter_project');
        $this->setState('filter.project', $project);
        $status = $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status',  array(100));
        $this->setState('filter.status', $status);
        $catalog_info = $this->getUserStateFromRequest($this->context . '.filter.catalog_info', 'filter_catalog_info');
        $this->setState('filter.catalog_info', $catalog_info);
        $catalog_logo = $this->getUserStateFromRequest($this->context . '.filter.catalog_logo', 'filter_catalog_logo');
        $this->setState('filter.catalog_logo', $catalog_logo);
        $pvn_1 = $this->getUserStateFromRequest($this->context . '.filter.pvn_1', 'filter_pvn_1');
        $this->setState('filter.pvn_1', $pvn_1);
        $pvn_1a = $this->getUserStateFromRequest($this->context . '.filter.pvn_1a', 'filter_pvn_1a');
        $this->setState('filter.pvn_1a', $pvn_1a);
        $pvn_1b = $this->getUserStateFromRequest($this->context . '.filter.pvn_1b', 'filter_pvn_1b');
        $this->setState('filter.pvn_1b', $pvn_1b);
        $pvn_1v = $this->getUserStateFromRequest($this->context . '.filter.pvn_1v', 'filter_pvn_1v');
        $this->setState('filter.pvn_1v', $pvn_1v);
        $pvn_1g = $this->getUserStateFromRequest($this->context . '.filter.pvn_1g', 'filter_pvn_1g');
        $this->setState('filter.pvn_1g', $pvn_1g);
        $doc_status = $this->getUserStateFromRequest($this->context . '.filter.doc_status', 'filter_doc_status');
        $this->setState('filter.doc_status', $doc_status);
        parent::populateState($ordering, $direction);
        ContractsHelper::check_refresh();
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.status');
        $id .= ':' . $this->getState('filter.catalog_info');
        $id .= ':' . $this->getState('filter.catalog_logo');
        $id .= ':' . $this->getState('filter.pvn_1');
        $id .= ':' . $this->getState('filter.pvn_1a');
        $id .= ':' . $this->getState('filter.pvn_1b');
        $id .= ':' . $this->getState('filter.pvn_1v');
        $id .= ':' . $this->getState('filter.pvn_1g');
        $id .= ':' . $this->getState('filter.doc_status');
        return parent::getStoreId($id);
    }

    private $export;
}
