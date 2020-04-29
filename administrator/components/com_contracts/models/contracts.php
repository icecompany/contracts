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
            ->select("i.doc_status")
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


        if ($orderCol === 'number') {
            $query->where("((c.number is not null and c.number_free is null) or (c.number_free is not null and c.number is null))");
            if ($orderDirn === 'asc') $orderCol = 'LENGTH(num), num';
            if ($orderDirn === 'desc') $orderCol = 'LENGTH(num) desc, num';
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
            $amount = number_format((float) $item->amount ?? 0, 2, '.', '');
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

    protected function populateState($ordering = 'c.id', $direction = 'desc')
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

    private $export;
}
