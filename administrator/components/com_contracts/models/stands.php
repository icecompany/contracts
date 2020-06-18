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
                'cs.type',
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
            ->select("cs.id, cs.freeze, cs.status, cs.comment, cs.type as stand_type")
            ->select("s.square, s.number")
            ->select("i.id as itemID, i.type, i.title as item")
            ->select("e.title as company")
            ->select("p.title as project")
            ->select("ci.value")
            ->select("ifnull(c.number_free, c.number) as contract_number, c.dat")
            ->select("st.title as contract_status")
            ->select("u.name as manager")
            ->from("#__mkv_contract_stands cs")
            ->leftJoin("#__mkv_contracts c on c.id = cs.contractID")
            ->leftJoin("#__mkv_companies e on e.id = c.companyID")
            ->leftJoin("#__mkv_projects p on p.id = c.projectID")
            ->leftJoin("#__mkv_stands s on s.id = cs.standID")
            ->leftJoin("#__mkv_contract_items ci on ci.contractStandID = cs.id")
            ->leftJoin("#__mkv_contract_statuses st on st.code = c.status")
            ->leftJoin("#__mkv_price_items i on i.id = ci.itemID")
            ->leftJoin("#__users u on u.id = c.managerID");
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
                $query->where("(s.number like {$text} or e.title like {$text})");
            }
        }
        $project = PrjHelper::getActiveProject();
        if (is_numeric($project)) {
            $query->where("c.projectID = {$this->_db->q($project)}");
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

        $query->order($this->_db->escape($orderCol . ' ' . $orderDirn));
        $this->setState('list.limit', $limit);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = ['stands' => [], 'items' => [], 'titles' => []];
        $return = ContractsHelper::getReturnUrl();
        $ids = [];
        foreach ($items as $item) {
            $arr = [];
            $arr['id'] = $item->id;
            $ids[] = $item->id;
            $arr['number'] = $item->number;
            $arr['square_clean'] = $item->square;
            $arr['square'] = JText::sprintf('COM_CONTRACTS_STANDS_SQUARE', $item->square);
            $arr['freeze'] = $item->freeze;
            $arr['status'] = JText::sprintf("COM_CONTRACTS_STAND_STATUS_{$item->status}");
            $arr['comment'] = $item->comment;
            $arr['company'] = $item->company;
            $arr['stand_type'] = JText::sprintf("COM_CONTRACTS_STAND_TYPE_{$item->stand_type}");
            $arr['project'] = $item->project;
            $manager = $item->manager;
            $manager = explode(' ', $manager);
            $arr['manager'] = $manager[0];
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
        $delegates = $this->getDelegates($ids);
        foreach ($delegates as $stand => $companies) $result['stands'][$stand]['delegates'] = implode(', ', $companies);
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
        $width = ["A" => 10, "B" => 10, "C" => 13, "D" => 60, "E" => 25, "F" => 10, "G" => 14, "H" => 17, "I" => 29];
        foreach ($width as $col => $value) $sheet->getColumnDimension($col)->setWidth($value);

        $sheet->setCellValue("A1", JText::sprintf('COM_CONTRACTS_HEAD_STANDS_NUMBER'));
        $sheet->setCellValue("B1", JText::sprintf('COM_CONTRACTS_HEAD_STANDS_SQUARE'));
        $sheet->setCellValue("C1", JText::sprintf('COM_CONTRACTS_HEAD_STANDS_TYPE'));
        $sheet->setCellValue("D1", JText::sprintf('COM_CONTRACTS_HEAD_STANDS_COMPANY'));
        $sheet->setCellValue("E1", JText::sprintf('COM_CONTRACTS_HEAD_STANDS_CONTRACT_STATUS'));
        $sheet->setCellValue("F1", JText::sprintf('COM_CONTRACTS_HEAD_STANDS_CONTRACT_NUMBER'));
        $sheet->setCellValue("G1", JText::sprintf('COM_CONTRACTS_HEAD_STANDS_CONTRACT_DATE'));
        $sheet->setCellValue("H1", JText::sprintf('COM_CONTRACTS_HEAD_STANDS_MANAGER'));
        $sheet->setCellValue("I1", JText::sprintf('COM_CONTRACTS_HEAD_STANDS_STAND_STATUS'));
        $col = 9;
        foreach ($items['titles'] as $id => $title) {
            $sheet->setCellValueByColumnAndRow($col, 1, $title);
            $col++;
        }

        $sheet->setTitle(JText::sprintf('COM_CONTRACTS_MENU_STANDS'));

        //Данные. Один проход цикла - одна строка
        $row = 2; //Строка, с которой начнаются данные
        $col = 9;
        foreach ($items['stands'] as $i => $stand) {
            $sheet->setCellValueExplicit("A{$row}", $stand['number'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue("B{$row}", $stand['square_clean']);
            $sheet->setCellValue("C{$row}", $stand['stand_type']);
            $sheet->setCellValue("D{$row}", $stand['delegates'] ?? $stand['company']);
            $sheet->setCellValue("E{$row}", $stand['contract_status']);
            $sheet->setCellValue("F{$row}", $stand['contract_number']);
            $sheet->setCellValue("G{$row}", $stand['contract_dat']);
            $sheet->setCellValue("H{$row}", $stand['manager']);
            $sheet->setCellValue("I{$row}", $stand['status']);
            foreach ($items['titles'] as $id => $title) {
                $sheet->setCellValueByColumnAndRow($col, $row, $items['items'][$stand['id']][$id]);
                $col++;
            }
            $row++;
            $col = 9;
        }
        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: public");
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Stands.xls");
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
        $objWriter->save('php://output');
        jexit();
    }


    private function getDelegates(array $ids = []): array
    {
        if (empty($ids)) return [];
        $model = ListModel::getInstance('Delegates', 'ContractsModel', ['standIDs' => $ids]);
        return $model->getItems();
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
