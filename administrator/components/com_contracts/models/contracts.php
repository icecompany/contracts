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
                'c.payments',
                'c.debt',
                'search',
                'c.tasks_count',
                'c.tasks_date',
                'num',
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
        $this->companyID = $config['companyID'] ?? 0;
        $this->return = PrjHelper::getReturnUrl();
        if ($this->companyID > 0) $this->export = true;
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
            ->select("c.id, c.companyID, c.dat, c.number, c.number_free, c.currency, c.amount, c.payments, c.debt")
            ->select("c.tasks_count, c.tasks_date")
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
        if ($this->companyID > 0) {
            $query
                ->where("c.companyID = {$this->_db->q($this->companyID)}");
            $orderCol = 'c.id';
            $orderDirn = 'desc';
        }
        else {
            $search = (!$this->export) ? $this->getState('filter.search') : JFactory::getApplication()->input->getString('search', '');
            if (!empty($search)) {
                if (stripos($search, 'id:') !== false) { //Поиск по ID
                    $id = explode(':', $search);
                    $id = $id[1];
                    if (is_numeric($id)) {
                        $query->where("c.id = {$this->_db->q($id)}");
                    }
                } else {
                    if (stripos($search, 'num:') !== false) { //Поиск по номеру договора
                        $num = explode(':', $search);
                        $num = $num[1];
                        if (is_numeric($num)) {
                            $query->where("c.number = {$this->_db->q($num)}");
                        }
                    } else {
                        $text = $this->_db->q("%{$search}%");
                        $query->where("(e.title like {$text} or e.title_full like {$text} or e.title_en like {$text})");
                    }
                }
            }
            $project = PrjHelper::getActiveProject();
            if (is_numeric($project)) {
                $query->where("c.projectID = {$this->_db->q($project)}");
            }
            $manager = $this->getState('filter.manager');
            if (is_numeric($manager)) {
                $query->where("c.managerID = {$this->_db->q($manager)}");
            }
            $status = $this->getState('filter.status');
            if (is_array($status) && !empty($status)) {
                if (!in_array(100, $status)) {
                    if (in_array('', $status)) {
                        $query->where('c.status is null');
                    } else {
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
            
            $doc_status = $this->getState('filter.doc_status');
            if (is_numeric($doc_status)) {
                $query->where("i.doc_status = {$this->_db->q($doc_status)}");
            }
            $currency = $this->getState('filter.currency');
            if (!empty($currency)) {
                $query->where("c.currency like {$this->_db->q($currency)}");
            }
            $pvn_1 = $this->getState('filter.pvn_1');
            $pvn_1a = $this->getState('filter.pvn_1a');
            $pvn_1b = $this->getState('filter.pvn_1b');
            $pvn_1v = $this->getState('filter.pvn_1v');
            $pvn_1g = $this->getState('filter.pvn_1g');
            $no_exhibit = $this->getState('filter.no_exhibit');
            if (is_numeric($pvn_1) or is_numeric($pvn_1a) or is_numeric($pvn_1b) or is_numeric($pvn_1v) or is_numeric($pvn_1g) or is_numeric($no_exhibit)) {
                if ($pvn_1 == '0' and $pvn_1a == '0' and $pvn_1b == '0' and $pvn_1g == '0' and $no_exhibit == '0') {
                    $query->where("(i.pvn_1 = 0 and i.pvn_1a = 0 and i.pvn_1b = 0 and i.pvn_1v = 0 and i.pvn_1g = 0 and i.no_exhibit = 0)");
                }
                else {
                    $query->where("(i.pvn_1 = {$this->_db->q($pvn_1)} or i.pvn_1a = {$this->_db->q($pvn_1a)} or i.pvn_1b = {$this->_db->q($pvn_1b)} or i.pvn_1v = {$this->_db->q($pvn_1v)} or i.pvn_1g = {$this->_db->q($pvn_1g)} or i.no_exhibit = {$this->_db->q($no_exhibit)})");
                }
            }

            if ($orderCol === 'num') {
                if ($orderDirn == 'DESC') {
                    $orderCol = 'LENGTH(num) desc, num';
                }
                else {
                    $orderCol = 'LENGTH(num) asc, num';
                }
            }
            if ($orderCol === 'c.dat') {
                $query->where("c.dat is not null");
            }
        }

        $query->order($this->_db->escape($orderCol . ' ' . $orderDirn));
        $this->setState('list.limit', $limit);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = ['items' => [], 'stands' => [], 'amount' => []];
        $ids = [];
        foreach ($items as $item) {
            $arr = [];
            $arr['id'] = $item->id;
            $ids[] = $item->id;
            $arr['company'] = $item->company;
            $arr['project'] = $item->project;
            $arr['amount'] = $item->amount;
            $arr['payments'] = $item->payments;
            $arr['debt'] = $item->debt;
            $arr['tasks_count'] = $item->tasks_count;
            $arr['tasks_date'] = (!empty($item->tasks_date)) ? JDate::getInstance($item->tasks_date)->format("d.m.Y") : '';
            $arr['status'] = $item->status ?? JText::sprintf('COM_MKV_STATUS_IN_PROJECT');
            $arr['manager'] = MkvHelper::getLastAndFirstNames($item->manager);
            $currency = mb_strtoupper($item->currency);
            $amount = number_format((float) $item->amount ?? 0, 2, '.', ' ');
            $payments = number_format((float) $item->payments ?? 0, 2, '.', ' ');
            $debt = number_format((float) $item->debt ?? 0, 2, '.', ' ');
            $arr['dat'] = (!empty($item->dat)) ? JDate::getInstance($item->dat)->format("d.m.Y") : '';
            $arr['currency'] = JText::sprintf("COM_CONTRACTS_CURRENCY_{$currency}_SHORT");
            $arr['amount_full'] = JText::sprintf("COM_MKV_AMOUNT_{$currency}_SHORT", $amount);
            $arr['payments_full'] = JText::sprintf("COM_MKV_AMOUNT_{$currency}_SHORT", $payments);
            $arr['debt_full'] = JText::sprintf("COM_MKV_AMOUNT_{$currency}_SHORT", $debt);
            if ($item->debt > 0 && FinancesHelper::canDo('core.create')) {
                $url = JRoute::_("index.php?option=com_finances&amp;task=score.add&amp;contractID={$item->id}&amp;return={$this->return}");
                $arr['debt_full'] = JHtml::link($url, $arr['debt_full']);
            }
            if ($item->payments > 0) {
                $url = JRoute::_("index.php?option=com_finances&amp;view=scores&amp;contractID={$item->id}");
                $arr['payments_full'] = JHtml::link($url, $arr['payments_full']);
            }
            if (empty($item->doc_status)) $item->doc_status = 0;
            $arr['doc_status'] = JText::sprintf("COM_CONTRACTS_DOC_STATUS_{$item->doc_status}_SHORT");
            $arr['number'] = $item->number_free ?? $item->number;
            $url = JRoute::_("index.php?option={$this->option}&amp;task=contract.edit&amp;id={$item->id}&amp;return={$this->return}");
            $arr['edit_link'] = JHtml::link($url, JText::sprintf('COM_CONTRACTS_ACTION_OPEN'));
            $url = JRoute::_("index.php?option=com_companies&amp;task=company.edit&amp;id={$item->companyID}&amp;return={$this->return}");
            $arr['company_link'] = JHtml::link($url, $item->company);
            $url = JRoute::_("index.php?option={$this->option}&amp;task=contract.edit&amp;id={$item->id}&amp;return={$this->return}");
            $arr['status_link'] = JHtml::link($url, $item->status ?? JText::sprintf('COM_MKV_STATUS_IN_PROJECT'));
            $url = JRoute::_("index.php?option={$this->option}&amp;view=items&amp;contractID={$item->id}");
            $arr['items_link'] = JHtml::link($url, JText::sprintf('COM_CONTRACTS_ACTION_ITEMS'));
            $result['items'][] = $arr;
        }
        $result['stands'] = $this->getStands($ids);
        $project = PrjHelper::getActiveProject();
        if (is_numeric($project) && ContractsHelper::canDo('core.project.amount')) $result['amount'] = ContractsHelper::getProjectAmount((int) $project);
        return $result;
    }

    private function getStands(array $ids = []): array
    {
        if (empty($ids)) return [];
        $model = ListModel::getInstance('StandsLight', 'ContractsModel', ['contractIDs' => $ids, 'byContractID' => true, 'byCompanyID' => false]);
        $items = $model->getItems();
        $result = [];
        $tmp = [];
        foreach ($items as $contractID => $data) {
            foreach ($data as $item) $tmp[$contractID][] = $item['edit_link'];
        }
        foreach ($tmp as $contractID => $stand) $result[$contractID] = implode('<br>', $stand);
        return $result;
    }

    protected function populateState($ordering = 'c.tasks_date', $direction = 'ASC')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $status = $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status',  array(100));
        $this->setState('filter.status', $status);
        $manager = $this->getUserStateFromRequest($this->context . '.filter.manager', 'filter_manager', JFactory::getUser()->id, 'integer');
        $this->setState('filter.manager', $manager);
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
        $no_exhibits = $this->getUserStateFromRequest($this->context . '.filter.no_exhibits', 'filter_no_exhibits');
        $this->setState('filter.no_exhibits', $no_exhibits);
        parent::populateState($ordering, $direction);
        PrjHelper::check_refresh();
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.status');
        $id .= ':' . $this->getState('filter.manager');
        $id .= ':' . $this->getState('filter.catalog_info');
        $id .= ':' . $this->getState('filter.catalog_logo');
        $id .= ':' . $this->getState('filter.pvn_1');
        $id .= ':' . $this->getState('filter.pvn_1a');
        $id .= ':' . $this->getState('filter.pvn_1b');
        $id .= ':' . $this->getState('filter.pvn_1v');
        $id .= ':' . $this->getState('filter.pvn_1g');
        $id .= ':' . $this->getState('filter.doc_status');
        $id .= ':' . $this->getState('filter.no_exhibits');
        return parent::getStoreId($id);
    }

    private $export, $return, $companyID;
}
