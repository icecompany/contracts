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
                'i.cost',
                'i.columnID',
                'pi.weight',
                'pi.title',
                'e.title',
                'currency',
                'search',
            );
        }
        parent::__construct($config);
        $input = JFactory::getApplication()->input;
        $this->export = ($input->getString('format', 'html') === 'html') ? false : true;
        $this->contractID = $input->getInt('contractID', 0);
        if (!empty($config['contractID'])) {
            $this->export = true;
            $this->contractID = $config['contractID'];
        }
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
            ->select("pi.title as item")
            ->select("c.currency")
            ->select("s.id as standID, s.number as stand")
            ->select("contractStandID")
            ->from("#__mkv_contract_items i")
            ->leftJoin("#__mkv_contracts c on c.id = i.contractID")
            ->leftJoin("#__mkv_companies e on e.id = c.companyID")
            ->leftJoin("#__mkv_price_items pi on pi.id = i.itemID")
            ->leftJoin("#__mkv_contract_stands cs on cs.id = i.contractStandID")
            ->leftJoin("#__mkv_stands s on s.id = cs.standID");
        if ($this->contractID > 0) {
            $query->where("i.contractID = {$this->_db->q($this->contractID)}");
            $limit = 0;
        }
        else {
            $query->select("e.title as company");

            $search = (!$this->export) ? $this->getState('filter.search') : JFactory::getApplication()->input->getString('search', '');
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
                            ->where("(e.title like {$text} or pi.title like {$text})");
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
            'currency' => null
        ];
        $return = ContractsHelper::getReturnUrl();
        foreach ($items as $item) {
            $arr = [];
            $link_option = [];
            $arr['id'] = $item->id;
            $arr['item'] = $item->item;
            if ($item->payerID !== null) {
                $payer = $im->getPayer($item->payerID);
                $item->item .= ' ' . JText::sprintf('COM_MKV_TEXT_ADDING_PAYER', $payer->title);
                $link_option = ['style' => 'color: red'];
            }
            $arr['columnID'] = $item->columnID;
            $arr['company'] = $item->company;
            $arr['factor'] = (1 - $item->factor) * 100 . "%";
            $arr['markup'] = ($item->markup - 1) * 100 . "%";
            $arr['cost_clean'] = $item->cost;
            $currency = mb_strtoupper($item->currency);
            $cost = number_format((float) $item->cost, 2, '.', ' ');
            $arr['cost'] = JText::sprintf("COM_CONTRACTS_CURRENCY_{$currency}_AMOUNT_SHORT", $cost);
            $arr['value'] = $item->value;
            $arr['value2'] = $item->value2;
            $arr['amount_clean'] = $item->amount;
            $amount = number_format((float) $item->amount, 2, '.', ' ');
            $arr['amount'] = JText::sprintf("COM_CONTRACTS_CURRENCY_{$currency}_AMOUNT_SHORT", $amount);
            $arr['stand'] = $item->stand;
            $url = JRoute::_("index.php?option={$this->option}&amp;task=item.edit&amp;id={$item->id}&amp;return={$return}");
            $arr['edit_link'] = JHtml::link($url, $item->item, $link_option);
            $url = JRoute::_("index.php?option={$this->option}&amp;task=items.delete&amp;cid[]={$item->id}");
            $arr['delete_link'] = JHtml::link($url, JText::sprintf('COM_MKV_ACTION_DELETE'));
            $url = JRoute::_("index.php?option={$this->option}&amp;task=stand.edit&amp;id={$item->contractStandID}&amp;return={$return}");
            $arr['stand_link'] = JHtml::link($url, $item->stand);
            $result['items'][] = $arr;
            $result['amount'][$item->currency] += $item->amount;
            $result['values'] += $item->value;
            if ($this->contractID > 0) $result['currency'] = $item->currency;
        }
        return $result;
    }

    public function getContractID(): int
    {
        return $this->contractID;
    }

    protected function populateState($ordering = 'pi.weight', $direction = 'ASC')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $currency = $this->getUserStateFromRequest($this->context . '.filter.currency', 'filter_currency');
        $this->setState('filter.currency', $currency);
        parent::populateState($ordering, $direction);
        ContractsHelper::check_refresh();
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.currency');
        return parent::getStoreId($id);
    }

    private $export, $contractID;
}
