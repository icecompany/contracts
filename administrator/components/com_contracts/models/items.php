<?php
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Model\AdminModel;

defined('_JEXEC') or die;

class ContractsModelItems extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'i.id',
                'i.value',
                'i.factor',
                'i.markup',
                'i.amount',
                'i.cost',
                'i.columnID',
                'pi.weight',
                'pi.title',
                'e.title',
                'st.title',
                's.number',
                'currency',
                'manager',
                'status',
                'search',
            );
        }
        parent::__construct($config);
        $input = JFactory::getApplication()->input;
        $this->export = ($input->getString('format', 'html') === 'html') ? false : true;
        $this->contractID = $input->getInt('contractID', 0);
        $this->itemID = $input->getInt('itemID', 0);
        $this->standID = $config['standID'];
        $this->standIDs = $config['standIDs'] ?? [];
        if (!empty($config['contractID'])) {
            $this->export = true;
            $this->contractID = $config['contractID'];
        }
        $this->heads = [
            'company' => 'COM_MKV_HEAD_COMPANY',
            'status' => 'COM_MKV_HEAD_CONTRACT_STATUS',
            'manager' => 'COM_MKV_HEAD_MANAGER',
            'item' => 'COM_CONTRACTS_HEAD_ITEMS_ITEM',
            'cost_clean' => 'COM_CONTRACTS_HEAD_ITEMS_COST',
            'factor' => 'COM_CONTRACTS_HEAD_ITEMS_FACTOR',
            'markup' => 'COM_CONTRACTS_HEAD_ITEMS_MARKUP',
            'columnID' => 'COM_CONTRACTS_HEAD_ITEMS_COLUMN',
            'stand' => 'COM_CONTRACTS_HEAD_ITEMS_STAND',
            'value' => 'COM_CONTRACTS_HEAD_ITEMS_VALUE',
            'amount_clean' => 'COM_CONTRACTS_HEAD_ITEMS_AMOUNT',
        ];
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
            ->select("i.*")
            ->select("pi.title as item, pi.appID")
            ->select("u1.title as unit_1")
            ->select("u2.title as unit_2")
            ->select("c.currency")
            ->select("s.id as standID, s.number as stand")
            ->select("contractStandID")
            ->select("u.name as manager")
            ->from("#__mkv_contract_items i")
            ->leftJoin("#__mkv_contracts c on c.id = i.contractID")
            ->leftJoin("#__mkv_companies e on e.id = c.companyID")
            ->leftJoin("#__mkv_price_items pi on pi.id = i.itemID")
            ->leftJoin("#__mkv_price_units u1 on u1.id = pi.unit_1_ID")
            ->leftJoin("#__mkv_price_units u2 on u2.id = pi.unit_2_ID")
            ->leftJoin("#__mkv_contract_stands cs on cs.id = i.contractStandID")
            ->leftJoin("#__users u on u.id = c.managerID")
            ->leftJoin("#__mkv_stands s on s.id = cs.standID");
        if ($this->itemID > 0) {
            $query->where("i.itemID = {$this->_db->q($this->itemID)}");
        }
        if ($this->contractID > 0 || $this->standID > 0 || !empty($this->standIDs)) {
            if ($this->contractID > 0) {
                $query->where("i.contractID = {$this->_db->q($this->contractID)}");
            }
            if ($this->standID > 0) {
                $query->where("i.contractStandID = {$this->_db->q($this->standID)}");
            }
            if (!empty($this->standIDs)) {
                $standIDs = implode(', ', $this->standIDs);
                if (!empty($standIDs)) $query->where("i.contractStandID in ({$standIDs})");
            }
            $limit = 0;
        }
        else {
            $query
                ->select("st.title as status")
                ->select("e.title as company")
                ->leftJoin("#__mkv_contract_statuses st on st.code = c.status");

            $search = $this->getState('filter.search');
            if (!empty($search)) {
                if (stripos($search, 'id:') !== false) { //Поиск по ID
                    $id = explode(':', $search);
                    $id = $id[1];
                    if (is_numeric($id)) {
                        $query->where("i.id = {$this->_db->q($id)}");
                    }
                } else {
                    if (stripos($search, 'cid:') !== false) { //Поиск по ID договора
                        $cid = explode(':', $search);
                        $cid = $cid[1];
                        if (is_numeric($cid)) {
                            $query->where("i.contractID = {$this->_db->q($cid)}");
                        }
                    }
                    else {
                        $text = $this->_db->q("%{$search}%");
                        $query
                            ->where("(e.title like {$text} or pi.title like {$text} or i.description like {$text})");
                    }
                }
            }
            $project = PrjHelper::getActiveProject();
            if (is_numeric($project)) {
                $query->where("c.projectID = {$this->_db->q($project)}");
            }
            $currency = $this->getState('filter.currency');
            if (!empty($currency)) {
                $query->where("c.currency like {$this->_db->q($currency)}");
            }
            $manager = $this->getState('filter.manager');
            if (is_numeric($manager)) {
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
        }

        $query->order($this->_db->escape($orderCol . ' ' . $orderDirn));
        $this->setState('list.limit', $limit);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        if ($this->contractID > 0) {
            $model = AdminModel::getInstance('Contract', 'ContractsModel');
            $contract = $model->getItem($this->contractID);
        }
        $im = AdminModel::getInstance('Item', 'ContractsModel');
        $result = [
            'items' => [],
            'company' => ($this->contractID > 0) ? $contract->company : '',
            'project' => ($this->contractID > 0) ? $contract->project : '',
            'amount' => ['rub' => 0, 'usd' => 0, 'eur' => 0],
            'values' => 0,
            'currency' => null,
            'apps' => $this->getApps(),
            'amount_by_apps' => [],
            'count_by_apps' => [],
        ];
        if ($this->contractID > 0) $result['currency'] = mb_strtoupper($contract->currency);
        $return = ContractsHelper::getReturnUrl();
        foreach ($items as $item) {
            $arr = [];
            $link_option = [];
            $arr['id'] = $item->id;
            $arr['item'] = $item->description ?? $item->item;
            if ($item->payerID !== null) {
                $payer = $im->getPayer($item->payerID);
                $item->item .= ' ' . JText::sprintf('COM_MKV_TEXT_ADDING_PAYER', $payer->title);
                $link_option = ['style' => 'color: red'];
            }
            $arr['columnID'] = $item->columnID;
            $arr['appID'] = $item->appID;
            $arr['company'] = $item->company;
            $arr['factor'] = (1 - $item->factor) * 100 . "%";
            $arr['markup'] = ($item->markup - 1) * 100 . "%";
            $arr['cost_clean'] = number_format((float) $item->cost, MKV_FORMAT_DEC_COUNT, MKV_FORMAT_SEPARATOR_FRACTION, '');
            $arr['status'] = $item->status;
            if (!$this->export) {
                $url = JRoute::_("index.php?option={$this->option}&amp;task=contract.edit&amp;id={$item->contractID}&amp;return={$return}");
                $arr['status'] = JHtml::link($url, $item->status);
            }
            $currency = mb_strtoupper($item->currency);
            $cost = number_format((float) $item->cost, MKV_FORMAT_DEC_COUNT, MKV_FORMAT_SEPARATOR_FRACTION, MKV_FORMAT_SEPARATOR_DEC);
            $arr['cost'] = JText::sprintf("COM_CONTRACTS_CURRENCY_{$currency}_AMOUNT_SHORT", $cost);
            $arr['value'] = number_format((float) $item->value, MKV_FORMAT_DEC_COUNT, MKV_FORMAT_SEPARATOR_FRACTION, '');
            $arr['value_full'] = sprintf("%s %s", number_format((float) $item->value, MKV_FORMAT_DEC_COUNT, MKV_FORMAT_SEPARATOR_FRACTION, MKV_FORMAT_SEPARATOR_DEC), $item->unit_1);
            $arr['manager'] = MkvHelper::getLastAndFirstNames($item->manager);
            $arr['value2'] = $item->value2;
            $arr['amount_clean'] = number_format((float) $item->amount, MKV_FORMAT_DEC_COUNT, MKV_FORMAT_SEPARATOR_FRACTION, '');
            $date_1 = (!empty($item->date_1)) ? JDate::getInstance($item->date_1)->format("d.m.Y") : '';
            $date_2 = (!empty($item->date_2)) ? JDate::getInstance($item->date_2)->format("d.m.Y") : '';
            $arr['period'] = (!empty($date_1) && !empty($date_2)) ? $date_1 . " - " . $date_2 : '';
            $amount = number_format((float) $item->amount, MKV_FORMAT_DEC_COUNT, MKV_FORMAT_SEPARATOR_FRACTION, MKV_FORMAT_SEPARATOR_DEC);
            $arr['amount'] = JText::sprintf("COM_CONTRACTS_CURRENCY_{$currency}_AMOUNT_SHORT", $amount);
            $arr['stand'] = $item->stand;
            if ($contract->managerID == JFactory::getUser()->id || ContractsHelper::canDo('core.edit.all') && empty($item->description)) {
                $url = JRoute::_("index.php?option={$this->option}&amp;task=item.edit&amp;id={$item->id}&amp;return={$return}");
                $arr['edit_link'] = JHtml::link($url, $arr['item'], $link_option);
            }
            else {
                $arr['edit_link'] = $arr['item'];
            }
            if (($contract->managerID == JFactory::getUser()->id && ContractsHelper::canDo('core.delete')) || ContractsHelper::canDo('core.edit.all')) {
                $url = JRoute::_("index.php?option={$this->option}&amp;task=items.delete&amp;cid[]={$item->id}");
                $arr['delete_link'] = JHtml::link($url, JText::sprintf('COM_MKV_ACTION_DELETE'));
            }
            if (($contract->managerID == JFactory::getUser()->id && ContractsHelper::canDo('core.edit')) || ContractsHelper::canDo('core.edit.all')) {
                $url = JRoute::_("index.php?option={$this->option}&amp;task=stand.edit&amp;id={$item->contractStandID}&amp;return={$return}");
                $arr['stand_link'] = JHtml::link($url, $item->stand);
            }
            else {
                $arr['stand_link'] = $item->stand;
            }
            if (!isset($result['count_by_apps'][$item->appID])) $result['count_by_apps'][$item->appID] = 0;
            if (!isset($result['amount_by_apps'][$item->appID])) $result['amount_by_apps'][$item->appID] = 0;
            $result['count_by_apps'][$item->appID]++;
            $result['amount_by_apps'][$item->appID] += $item->amount;
            $result['items'][] = $arr;
            $result['amount'][$item->currency] += $item->amount;
            $result['values'] += $item->value;
            if ($this->contractID > 0) $result['currency'] = $item->currency;
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

        //Ширина столбцов
        $width = ["A" => 84, "B" => 26, "C" => 26, "D" => 120, "E" => 11, "F" => 9, "G" => 9, "H" => 9, "I" => 9, "J" => 9, "K" => 19];
        foreach ($width as $col => $value) $sheet->getColumnDimension($col)->setWidth($value);

        //Заголовки
        $j = 0;
        foreach ($this->heads as $item => $head) $sheet->setCellValueByColumnAndRow($j++, 1, JText::sprintf($head));

        $sheet->setTitle(JText::sprintf('COM_CONTRACTS_MENU_ITEMS'));

        //Данные
        $row = 2; //Строка, с которой начнаются данные
        $col = 0;
        foreach ($items['items'] as $i => $item) {
            foreach ($this->heads as $elem => $head) {
                $float = ['cost_clean', 'amount_clean', 'value'];
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
        header("Content-Disposition: attachment; filename=Sales.xls");
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
        $objWriter->save('php://output');
        jexit();
    }

    private function getApps(): array
    {
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_prices/models", "PricesModel");
        $model = JModelLegacy::getInstance('Apps', 'PricesModel');
        return $model->getItems();
    }

    public function getContractID(): int
    {
        return $this->contractID;
    }

    public function getItemID(): int
    {
        return $this->itemID;
    }

    public function getItemTitle()
    {
        JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_prices/tables");
        $table = JTable::getInstance('Items', 'TablePrices');
        $table->load($this->itemID);
        return $table->title;
    }

    protected function populateState($ordering = 'pi.weight', $direction = 'ASC')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $currency = $this->getUserStateFromRequest($this->context . '.filter.currency', 'filter_currency');
        $this->setState('filter.currency', $currency);
        $manager = $this->getUserStateFromRequest($this->context . '.filter.manager', 'filter_manager');
        $this->setState('filter.manager', $manager);
        $status = $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status');
        $this->setState('filter.status', $status);
        parent::populateState($ordering, $direction);
        ContractsHelper::check_refresh();
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.currency');
        $id .= ':' . $this->getState('filter.manager');
        $id .= ':' . $this->getState('filter.status');
        return parent::getStoreId($id);
    }

    private $export, $contractID, $standID, $standIDs, $heads, $itemID;
}
