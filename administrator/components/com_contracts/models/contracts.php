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
                'p.date_start',
                'manager',
                'number',
                'i.doc_status',
                'c.amount',
                'c.payments',
                'c.debt',
                'search',
                'list',
                'c.tasks_count',
                'c.tasks_date',
                'num',
                'arrival',
                'thematics',
                'title_to_diploma',
                'priority',
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
        $this->heads = [
            'company' => 'COM_MKV_HEAD_COMPANY',
            'status' => 'COM_MKV_HEAD_CONTRACT_STATUS',
            'stands' => 'COM_MKV_HEAD_STANDS',
            'number' => 'COM_MKV_HEAD_CONTRACT_NUMBER',
            'dat' => 'COM_MKV_HEAD_DATE',
            'manager' => 'COM_MKV_HEAD_MANAGER',
            'tasks_count' => 'COM_CONTRACTS_HEAD_CONTRACTS_TASKS_COUNT',
            'doc_status' => 'COM_CONTRACTS_HEAD_CONTRACTS_ORIGINAL',
            'currency' => 'COM_CONTRACTS_HEAD_CURRENCY',
            'amount' => 'COM_MKV_HEAD_AMOUNT',
            'payments' => 'COM_MKV_HEAD_PAYED',
            'debt' => 'COM_MKV_HEAD_DEBT',
            'catalog_info' => 'COM_CONTRACTS_FORM_CONTRACT_INFO_CATALOG_LABEL',
            'catalog_logo' => 'COM_CONTRACTS_FORM_CONTRACT_LOGO_CATALOG_LABEL',
            'pvn_1' => 'COM_CONTRACTS_FORM_CONTRACT_PVN_1_LABEL',
            'pvn_1a' => 'COM_CONTRACTS_FORM_CONTRACT_PVN_1A_LABEL',
            'pvn_1b' => 'COM_CONTRACTS_FORM_CONTRACT_PVN_1B_LABEL',
            'pvn_1v' => 'COM_CONTRACTS_FORM_CONTRACT_PVN_1V_LABEL',
            'pvn_1g' => 'COM_CONTRACTS_FORM_CONTRACT_PVN_1G_LABEL',
            'no_exhibit' => 'COM_CONTRACTS_FORM_CONTRACT_NO_EXHIBIT_LABEL',
            'info_arrival' => 'COM_CONTRACTS_FORM_CONTRACT_INFO_ARRIVAL_LABEL',
            'scheme_title' => 'COM_CONTRACTS_FORM_CONTRACT_PLAN_TITLE_RU_LABEL',
            'scheme_title_en' => 'COM_CONTRACTS_FORM_CONTRACT_PLAN_TITLE_EN_LABEL',
            'invite_date' => 'COM_CONTRACTS_FORM_CONTRACT_INVITE_DATE_LABEL',
            'invite_outgoing_number' => 'COM_CONTRACTS_FORM_CONTRACT_OUTGOING_NUMBER_LABEL',
            'invite_incoming_number' => 'COM_CONTRACTS_FORM_CONTRACT_INCOMING_NUMBER_LABEL',
            'title_to_diploma' => 'COM_CONTRACTS_FORM_CONTRACT_TITLE_TO_DIPLOMA_LABEL',
            'thematics' => 'COM_CONTRACTS_HEAD_THEMATICS',
        ];
    }

    protected function _getListQuery()
    {
        $query = $this->_db->getQuery(true);

        /* Сортировка */
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');

        $query
            ->select("c.id, c.projectID, c.companyID, c.dat, c.number, c.number_free, c.currency, c.amount, c.payments, c.debt, c.status as status_code")
            ->select("c.tasks_count, c.tasks_date")
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

        if ($this->export) {
            $query
                ->select("i.catalog_info, i.catalog_logo, i.pvn_1, i.pvn_1a, i.pvn_1b, i.pvn_1v, i.pvn_1g, i.no_exhibit, i.info_arrival, i.scheme_title_en, i.title_to_diploma")
                ->select("ifnull(i.scheme_title_ru, concat_ws(', ', e.title, ifnull(e.form, ''))) as scheme_title")
                ->select("si.invite_date, si.invite_outgoing_number, si.invite_incoming_number")
                ->leftJoin("#__mkv_contract_sent_info si on si.contractID = c.id");
        }
        if ($this->companyID > 0) {
            $query
                ->where("c.companyID = {$this->_db->q($this->companyID)}");
            $orderCol = 'p.date_start';
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
                    if (stripos($search, 'num:') !== false || stripos($search, '#') !== false || stripos($search, '№') !== false) { //Поиск по номеру договора
                        $delimiter = ":";
                        if (stripos($search, 'num:') !== false) $delimiter = ":";
                        if (stripos($search, '#') !== false) $delimiter = "#";
                        if (stripos($search, '№') !== false) $delimiter = "№";
                        $num = explode($delimiter, $search);
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
            else {
                //Оставляем только проекты, в которые входит юзер
                $userGroups = implode(', ', JFactory::getUser()->groups);
                if (!empty($userGroups)) $query->where("p.groupID in ({$userGroups})");
            }
            $manager = $this->getState('filter.manager');
            if (is_numeric($manager) && ContractsHelper::canDo('core.show.all')) {
                $query->where("c.managerID = {$this->_db->q($manager)}");
            }
            $status = $this->getState('filter.status');
            if (is_array($status) && !empty($status)) {
                $statuses = implode(", ", $status);
                if (in_array(101, $status)) {
                    $query->where("(c.status in ({$statuses}) or c.status is null)");
                } else {
                    $query->where("c.status in ({$statuses})");
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

            $userID = JFactory::getUser()->id;
            if (!ContractsHelper::canDo('core.show.all')) $query->where("c.managerID = {$this->_db->q($userID)}");

            $title_to_diploma = $this->getState('filter.title_to_diploma');
            if (is_numeric($title_to_diploma)) {
                if ($title_to_diploma === '0') $query->where("i.title_to_diploma is null");
                if ($title_to_diploma === '1') {
                    $query->where("i.title_to_diploma is not null");
                }
            }

            $currency = $this->getState('filter.currency');
            if (!empty($currency)) {
                $query->where("c.currency like {$this->_db->q($currency)}");
            }

            $list = $this->getState('filter.list');
            if (!empty($list)) {
                $query->leftJoin("#__mkv_contract_lists l on l.contractID = c.id");
                if ($list == 'all') {
                    $query->where("l.listID is not null");
                }
                elseif ($list == 'empty') {
                    $query->where("l.listID is null");
                }
                else {
                    $query->where("l.listID = {$this->_db->q($list)}");
                }
            }

            $priority = $this->getState('filter.priority');
            if (is_numeric($priority)) {
                $companies = ContractsHelper::getPriority();
                $not = ($priority == '1') ? '' : 'not';
                $query->where("c.companyID {$not} in ({$companies})");
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
        $thematics = $this->getState('filter.thematics');
        if (is_numeric($thematics)) {
            $ids = $this->getThematicsContracts([$thematics]);
            if (!empty($ids)) {
                $cid = implode(', ', $ids);
                $query->where("c.id in ({$cid})");
            }
            else {
                $query->where("c.id = -1");
            }
        }

        $arrival = $this->getState('filter.arrival');
        if (is_numeric($arrival)) {
            $query->where("i.info_arrival = {$this->_db->q($arrival)}");
        }

        //Ограничение длины списка
        $this->setState('list.limit', (!$this->export) ? $this->state->get('list.limit') : 0);
        $query->order($this->_db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = ['items' => [], 'lists' => $this->getLists(), 'amount' => [], 'amount_by_status' => [], 'sum' => [
            'amount' => ['rub' => 0, 'usd' => 0, 'eur' => 0],
            'payments' => ['rub' => 0, 'usd' => 0, 'eur' => 0],
            'debt' => ['rub' => 0, 'usd' => 0, 'eur' => 0],
        ]
        ];
        $ids = [];
        foreach ($items as $item) {
            $arr = [];
            $arr['id'] = $item->id;
            $ids[] = $item->id;
            $arr['company'] = $item->company;
            $arr['project'] = $item->project;
            $arr['projectID'] = $item->projectID;
            $arr['tasks_count'] = $item->tasks_count;
            if ($item->tasks_count == '1') {
                $url = JRoute::_("index.php?option=com_scheduler&amp;task=task.gotoContractActiveTask&amp;contractID={$item->id}&amp;return={$this->return}");
                $arr['tasks_link'] = JHtml::link($url, $item->tasks_count);
            }
            $arr['tasks_date'] = (!empty($item->tasks_date) && $item->tasks_date != '0000-00-00') ? JDate::getInstance($item->tasks_date)->format("d.m.Y") : '';
            $arr['status'] = $item->status ?? JText::sprintf('COM_MKV_STATUS_IN_PROJECT');
            $arr['status_code'] = $item->status_code;
            $arr['manager'] = MkvHelper::getLastAndFirstNames($item->manager);
            $currency = mb_strtoupper($item->currency);
            $amount = number_format((float) $item->amount ?? 0, MKV_FORMAT_DEC_COUNT, MKV_FORMAT_SEPARATOR_FRACTION, MKV_FORMAT_SEPARATOR_DEC);
            $payments = number_format((float) $item->payments ?? 0, MKV_FORMAT_DEC_COUNT, MKV_FORMAT_SEPARATOR_FRACTION, MKV_FORMAT_SEPARATOR_DEC);
            $debt = number_format((float) $item->debt ?? 0, MKV_FORMAT_DEC_COUNT, MKV_FORMAT_SEPARATOR_FRACTION, MKV_FORMAT_SEPARATOR_DEC);
            $arr['dat'] = (!empty($item->dat)) ? JDate::getInstance($item->dat)->format("d.m.Y") : '';
            $arr['currency'] = JText::sprintf("COM_CONTRACTS_CURRENCY_{$currency}_SHORT");
            $arr['currency_clean'] = $item->currency;
            $arr['amount_full'] = JText::sprintf("COM_MKV_AMOUNT_{$currency}_SHORT", $amount);
            $arr['payments_full'] = JText::sprintf("COM_MKV_AMOUNT_{$currency}_SHORT", $payments);
            $arr['debt_full'] = JText::sprintf("COM_MKV_AMOUNT_{$currency}_SHORT", $debt);
            $arr['amount'] = number_format((float) $item->amount ?? 0, MKV_FORMAT_DEC_COUNT, MKV_FORMAT_SEPARATOR_FRACTION, '');
            $arr['payments'] = number_format((float) $item->payments ?? 0, MKV_FORMAT_DEC_COUNT, MKV_FORMAT_SEPARATOR_FRACTION, '');
            $arr['debt'] = number_format((float) $item->debt ?? 0, MKV_FORMAT_DEC_COUNT, MKV_FORMAT_SEPARATOR_FRACTION, '');
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
            if ($this->export) {
                $arr['no_exhibit'] = JText::sprintf(($item->no_exhibit) != '1' ? 'JNO' : 'JYES');
                $arr['info_arrival'] = JText::sprintf(($item->info_arrival) != '1' ? 'JNO' : 'JYES');
                $arr['catalog_info'] = JText::sprintf(($item->catalog_info) != '1' ? 'JNO' : 'JYES');
                $arr['catalog_logo'] = JText::sprintf(($item->catalog_logo) != '1' ? 'JNO' : 'JYES');
                $arr['pvn_1'] = JText::sprintf(($item->pvn_1) != '1' ? 'JNO' : 'JYES');
                $arr['pvn_1a'] = JText::sprintf(($item->pvn_1a) != '1' ? 'JNO' : 'JYES');
                $arr['pvn_1b'] = JText::sprintf(($item->pvn_1b) != '1' ? 'JNO' : 'JYES');
                $arr['pvn_1v'] = JText::sprintf(($item->pvn_1v) != '1' ? 'JNO' : 'JYES');
                $arr['pvn_1g'] = JText::sprintf(($item->pvn_1g) != '1' ? 'JNO' : 'JYES');
                $arr['scheme_title'] = $item->scheme_title;
                $arr['scheme_title_en'] = $item->scheme_title_en;
                $arr['invite_date'] = (!empty($item->invite_date)) ? JDate::getInstance($item->invite_date)->format("d.m.Y") : '';
                $arr['invite_outgoing_number'] = $item->invite_outgoing_number;
                $arr['invite_incoming_number'] = $item->invite_incoming_number;
                $arr['title_to_diploma'] = $item->title_to_diploma;
            }
            $url = JRoute::_("index.php?option={$this->option}&amp;task=contract.edit&amp;id={$item->id}&amp;return={$this->return}");
            $arr['edit_link'] = JHtml::link($url, JText::sprintf('COM_CONTRACTS_ACTION_OPEN'));
            $url = JRoute::_("index.php?option=com_companies&amp;task=company.edit&amp;id={$item->companyID}&amp;return={$this->return}");
            $arr['company_link'] = JHtml::link($url, $item->company);
            $url = JRoute::_("index.php?option={$this->option}&amp;task=contract.edit&amp;id={$item->id}&amp;return={$this->return}");
            $contract_with_number = null;
            if (($item->status_code == '1' || $item->status_code == '10') && !empty($arr['number'])) {
                $contract_with_number = sprintf("%s №%s", $arr['status'], $arr['number']);
            }
            $arr['status_link'] = JHtml::link($url, $contract_with_number ?? $item->status ?? JText::sprintf('COM_MKV_STATUS_IN_PROJECT'));
            $url = JRoute::_("index.php?option={$this->option}&amp;view=items&amp;contractID={$item->id}");
            $arr['items_link'] = JHtml::link($url, JText::sprintf('COM_CONTRACTS_ACTION_ITEMS'));
            $result['sum']['amount'][$item->currency] += $item->amount;
            $result['sum']['payments'][$item->currency] += $item->payments;
            $result['sum']['debt'][$item->currency] += $item->debt;
            $result['items'][$item->id] = $arr;
        }
        $stands = $this->getStands($ids);
        $lists = $this->getContractsLists($result['lists'] ?? [], $ids);
        if (!empty($ids)) $stands_info = ContractsHelper::getContractStandInfo($ids ?? []);
        $thematics = $this->getThematics($ids);
        $project = PrjHelper::getActiveProject();
        if (!$this->export) {
            $result['amount_by_status'] = ContractsHelper::getProjectAmount(true);
        }
        if (is_numeric($project) && ContractsHelper::canDo('core.project.amount')) $result['amount'] = ContractsHelper::getProjectAmount();
        foreach ($result['items'] as $contractID => $item) {
            $result['items'][$contractID]['stands'] = $stands[$contractID];
            $result['items'][$contractID]['thematics'] = $thematics[$contractID];
            foreach ($lists[$contractID] as $listID => $list_value) {
                $result['items'][$contractID][$listID] = $list_value;
            }
            if (!empty($result['items'][$contractID]['stands'])) $result['items'][$contractID]['stand_items'] = $stands_info[$contractID];
        }

        return $result;
    }

    public function export()
    {
        $items = $this->getItems();
        JLoader::discover('PHPExcel', JPATH_LIBRARIES);
        JLoader::register('PHPExcel', JPATH_LIBRARIES . '/PHPExcel.php');

        $xls = new PHPExcel();
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

        //Добавляем списки

        //Заголовки
        $j = 0;
        $this->addListsToHead($items['lists']);
        foreach ($this->heads as $item => $head) $sheet->setCellValueByColumnAndRow($j++, 1, JText::sprintf($head));

        $sheet->setTitle(JText::sprintf('COM_CONTRACTS_MENU_CONTRACTS'));

        //Данные
        $row = 2; //Строка, с которой начнаются данные
        $col = 0;
        foreach ($items['items'] as $contractID => $item) {
            foreach ($this->heads as $elem => $head) {
                $float = ['amount', 'payments', 'debt'];
                if (array_search($elem, $float) === false) {
                    $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item[$elem], PHPExcel_Cell_DataType::TYPE_STRING);
                }
                else {
                    $sheet->setCellValueByColumnAndRow($col++, $row, $item[$elem]);
                    $sheet->getStyleByColumnAndRow($col-1, $row)->getNumberFormat()->setFormatCode('0');
                }
            }
            $col = 0;
            $row++;
        }
        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: public");
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Contracts.xls");
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
        $objWriter->save('php://output');
        jexit();
    }

    private function addListsToHead(array $lists): void
    {
        if (!ContractsHelper::canDo('core.access.filter.lists')) return;
        foreach ($lists as $listID => $list) {
            $this->heads["list_{$listID}"] = sprintf("%s - %s", $list['type'], $list['title']);
        }
    }

    private function getContractsLists(array $lists, array $contractIDs = []): array
    {
        if (empty($contractIDs)) return [];
        if (empty($lists)) return [];
        $model = ListModel::getInstance("Lists", "ContractsModel", ['contractID' => $contractIDs]);
        $items = $model->getItems();
        $result = [];
        foreach ($contractIDs as $contractID) {
            foreach ($lists as $listID => $list) {
                $result[$contractID]["list_{$listID}"] = JText::sprintf((array_search($listID, array_values($items[$contractID])) !== false) ? 'JYES' : 'JNO');
            }
        }
        return $result;
    }

    private function getLists(): array
    {
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_mkv/models", "MkvModel");
        $model = JModelLegacy::getInstance("Lists", "MkvModel");
        return $model->getItems();
    }

    private function getStands(array $ids = []): array
    {
        if (empty($ids)) return [];
        $model = ListModel::getInstance('StandsLight', 'ContractsModel', ['contractIDs' => $ids, 'byContractID' => true, 'byCompanyID' => false]);
        $items = $model->getItems();
        $result = [];
        $tmp = [];
        foreach ($items as $contractID => $data) foreach ($data as $item) $tmp[$contractID][] = (!$this->export) ? $item['edit_link'] : sprintf("%s (%s)", $item['number'], $item['square']);
        foreach ($tmp as $contractID => $stand) $result[$contractID] = implode((!$this->export) ? '<br>' : ', ', $stand);
        return $result;
    }

    private function getThematics(array $ids = []): array
    {
        if (empty($ids)) return [];
        $model = ListModel::getInstance('Thematics', 'ContractsModel', ['contractIDs' => $ids]);
        $items = $model->getItems();
        foreach ($items as $contractID => $thematics) $items[$contractID] = implode(', ', $thematics);
        return $items;
    }

    private function getThematicsContracts(array $thematicIDs = []): array
    {
        if (empty($thematicIDs)) return [];
        $model = ListModel::getInstance('Thematics', 'ContractsModel', ['thematicIDs' => $thematicIDs]);
        return $model->getItems();
    }

    protected function populateState($ordering = 'c.tasks_date', $direction = 'ASC')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $status = $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status');
        $this->setState('filter.status', $status);
        $manager = $this->getUserStateFromRequest($this->context . '.filter.manager', 'filter_manager', JFactory::getUser()->id, 'integer', false);
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
        $title_to_diploma = $this->getUserStateFromRequest($this->context . '.filter.title_to_diploma', 'filter_title_to_diploma');
        $this->setState('filter.title_to_diploma', $title_to_diploma);
        $thematics = $this->getUserStateFromRequest($this->context . '.filter.thematics', 'filter_thematics');
        $this->setState('filter.thematics', $thematics);
        $arrival = $this->getUserStateFromRequest($this->context . '.filter.arrival', 'filter_arrival');
        $this->setState('filter.arrival', $arrival);
        $priority = $this->getUserStateFromRequest($this->context . '.filter.priority', 'filter_priority');
        $this->setState('filter.priority', $priority);
        $list = $this->getUserStateFromRequest($this->context . '.filter.list', 'filter_list');
        $this->setState('filter.list', $list);
        parent::populateState($ordering, $direction);
        ContractsHelper::check_refresh();
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
        $id .= ':' . $this->getState('filter.title_to_diploma');
        $id .= ':' . $this->getState('filter.thematics');
        $id .= ':' . $this->getState('filter.arrival');
        $id .= ':' . $this->getState('filter.priority');
        $id .= ':' . $this->getState('filter.list');
        return parent::getStoreId($id);
    }

    private $export, $return, $companyID, $heads;
}
