<?php
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

class ContractsModelParents extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'e.title',
                'search',
            );
        }
        parent::__construct($config);
        $input = JFactory::getApplication()->input;
        $this->companyID = $config['companyID'] ?? 0;
        $this->projectID = $config['projectID'] ?? 0;
        $this->export = ($input->getString('format', 'html') === 'html') ? false : true;
        $this->return = ContractsHelper::getReturnUrl();
    }

    protected function _getListQuery()
    {
        $query = $this->_db->getQuery(true);

        //Ограничение длины списка
        $limit = 0;

        $query
            ->select("p.id, p.contractID")
            ->select("e.title as company")
            ->select("c.companyID")
            ->select("cs.title as status")
            ->from("#__mkv_contract_parents p")
            ->leftJoin("#__mkv_contracts c on c.id = p.contractID")
            ->leftJoin("#__mkv_contract_statuses cs on cs.code = c.status")
            ->leftJoin("#__mkv_companies e on e.id = c.companyID");
        if ($this->companyID > 0 && $this->projectID > 0) {
            $query->where("(c.projectID = {$this->_db->q($this->projectID)} and p.companyID = {$this->_db->q($this->companyID)})");
        }

        $this->setState('list.limit', $limit);

        /* Сортировка */
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($this->_db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = [];
        foreach ($items as $item) {
            $arr = [];
            $arr['id'] = $item->id;
            $arr['company'] = $item->company;
            $url = JRoute::_("index.php?option=com_companies&amp;task=company.edit&amp;id={$item->companyID}&amp;return={$this->return}");
            $arr['company_link'] = JHtml::link($url, $item->company);
            $url = JRoute::_("index.php?option={$this->option}&amp;task=contract.edit&amp;id={$item->contractID}&amp;return={$this->return}");
            $arr['contract_link'] = JHtml::link($url, $item->status ?? JText::sprintf('COM_CONTRACTS_CONTRACT_STATUS_IN_PROJECT'));
            $url = JRoute::_("index.php?option={$this->option}&amp;task=parents.delete&amp;cid[]={$item->id}");
            $arr['delete_link'] = JHtml::link($url, JText::sprintf('COM_MKV_ACTION_DELETE'));
            $result[] = $arr;
        }
        return $result;
    }

    protected function populateState($ordering = 'e.title', $direction = 'ASC')
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

    private $export, $companyID, $projectID, $return;
}
