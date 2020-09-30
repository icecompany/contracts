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
        $this->byProjectID = $config['byProjectID'] ?? false;
        $this->byContractID = $config['byContractID'] ?? false;
        $this->projectID = $config['projectID'] ?? 0;
        $this->standID = $config['standID'] ?? 0;
        $this->export = ($input->getString('format', 'html') === 'html') ? false : true;
        $this->return = PrjHelper::getReturnUrl();
    }

    protected function _getListQuery()
    {
        $query = JFactory::getDbo()->getQuery(true);

        //Ограничение длины списка
        $limit = 0;

        $query
            ->select("cs.id, cs.status, cs.type, cs.freeze, cs.comment, cs.contractID")
            ->select("s.square, s.number, s.id as standID")
            ->select("e.title as company, e.id as companyID")
            ->select("c.managerID")
            ->select("cp.contractID as delegated_by")
            ->from("#__mkv_contract_stands cs")
            ->leftJoin("#__mkv_contracts c on c.id = cs.contractID")
            ->leftJoin("#__mkv_companies e on e.id = c.companyID")
            ->leftJoin("#__mkv_stands s on s.id = cs.standID")
            ->leftJoin("#__mkv_contract_parents cp on cp.contractStandID = cs.id");
        if (!empty($this->contractIDs)) {
            $delegates = $this->getDelegatedStand();
            $csid = implode(', ', array_values($delegates));
            $cids = implode(', ', $this->contractIDs);
            if (!empty($csid)) {
                $query->where("(cs.contractID in ({$cids}) or cs.id in ({$csid}))");
            }
            else {
                $query->where("cs.contractID in ({$cids})");
            }
        }
        if ($this->projectID > 0) {
            $query->where("c.projectID = {$this->_db->q($this->projectID)}");
        }
        if ($this->standID > 0) {
            $query->where("cs.standID = {$this->_db->q($this->standID)}");
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
            $arr['square'] = JText::sprintf('COM_MKV_STANDS_SQUARE', $item->square);
            $arr['status'] = JText::sprintf("COM_MKV_STAND_STATUS_{$item->status}");
            $arr['company'] = $item->company;
            $arr['type'] = JText::sprintf("COM_MKV_STAND_TYPE_{$item->type}");
            $arr['freeze'] = $item->freeze;
            $arr['comment'] = $item->comment;
            if (($item->managerID == JFactory::getUser()->id && ContractsHelper::canDo('core.edit')) || ContractsHelper::canDo('core.edit.all')) {
                $url = JRoute::_("index.php?option={$this->option}&amp;task=stand.edit&amp;id={$item->id}&amp;return={$this->return}");
                $arr['edit_link'] = JHtml::link($url, JText::sprintf('COM_MKV_STANDS_NUMBER_WITH_SQUARE', $item->number, $item->square));
            }
            else {
                $arr['edit_link'] = JText::sprintf('COM_MKV_STANDS_NUMBER_WITH_SQUARE', $item->number, $item->square);
            }
            if (($item->managerID == JFactory::getUser()->id && ContractsHelper::canDo('core.delete')) || ContractsHelper::canDo('core.edit.all')) {
                $url = JRoute::_("index.php?option={$this->option}&amp;task=stands.delete&amp;cid[]={$item->id}");
                $arr['delete_link'] = JHtml::link($url, JText::sprintf('COM_MKV_ACTION_DELETE'));
            }
            if ($this->byCompanyID) $result[$item->companyID][] = $item->number;
            if ($this->byContractID) {
                if (!isset($result[$item->contractID][$item->id])) $result[$item->contractID][$item->id] = $arr;
                $result[$item->delegated_by ?? $item->contractID][$item->id] = $arr;
            }
            if ($this->byProjectID) $result[$item->standID] = $arr;
            if ($this->standID > 0) $result[] = $arr;
        }
        return $result;
    }

    private function getDelegatedStand() {
        $model = ListModel::getInstance('Parents', 'ContractsModel', ['contractIDs' => $this->contractIDs ?? []]);
        $items = $model->getItems();
        $result = [];
        if (empty($items)) return $result;
        foreach ($items as $item) if (!empty($item['contractStandID'])) $result[$item['contractID']] = $item['contractStandID'];
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

    private $export, $contractIDs, $byCompanyID, $byContractID, $byProjectID, $projectID, $standID, $return;
}
