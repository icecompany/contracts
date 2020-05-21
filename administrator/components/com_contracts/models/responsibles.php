<?php
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

class ContractsModelResponsibles extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'con.id',
                'length(number), number',
                'e.title',
                's.code',
                'search',
                'status',
                'without',
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
            ->select("con.id, ifnull(c.number_free, c.number) as number")
            ->select("e.title as company, c.companyID")
            ->select("s.title as status")
            ->select("c.id as contractID")
            ->select("con.fio, con.post, con.for_accreditation, con.for_building, con.phone_work_additional")
            ->select("aes_decrypt(con.phone_work,@pass) as phone_work")
            ->select("aes_decrypt(con.phone_mobile,@pass) as phone_mobile")
            ->select("aes_decrypt(con.email,@pass) as email")
            ->from("#__mkv_contracts c")
            ->leftJoin("#__mkv_contract_statuses s on s.code = c.status")
            ->leftJoin("#__mkv_companies e on e.id = c.companyID")
            ->leftJoin("#__mkv_companies_contacts con on con.companyID = c.companyID");

        $search = (!$this->export) ? $this->getState('filter.search') : JFactory::getApplication()->input->getString('search', '');
        if (!empty($search)) {
            $text = $this->_db->q("%{$search}%");
            $query->where("(e.title like {$text} or con.fio like {$text})");
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
        $without = $this->getState('filter.without');
        if (is_numeric($without)) {
            $query->leftJoin("#__mkv_companies_contacts_occupancy as o on o.companyID = c.companyID");

            if ($without == '0') $query->where("(o.for_accreditation = 0 and o.for_building = 0)");
            if ($without == '1') $query->where("(o.for_accreditation > 0 and o.for_building > 0)");
            if ($without == '2') $query->where("((o.for_accreditation = 0 and o.for_building > 0) or (o.for_accreditation > 0 and o.for_building = 0))");
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
            if (!isset($result['items'][$item->contractID])) {
                $result['items'][$item->contractID] = [];
                $result['items'][$item->contractID]['number'] = $item->number;
                $url = JRoute::_("index.php?option=com_companies&amp;task=company.edit&amp;id={$item->companyID}&amp;return={$return}");
                $result['items'][$item->contractID]['edit_link'] = JHtml::link($url, $item->company);
                $result['items'][$item->contractID]['company'] = $item->company;
                $result['items'][$item->contractID]['status'] = $item->status ?? JText::sprintf('COM_CONTRACTS_CONTRACT_STATUS_IN_PROJECT');
                $result['items'][$item->contractID]['for_accreditation'] = [];
                $result['items'][$item->contractID]['for_building'] = [];
            }

            $contact = [];
            if (!empty($item->fio)) $contact[] = $item->fio;
            if (!empty($item->post)) $contact[] = $item->post;
            if (!empty($item->phone_work)) {
                $phone_work = $item->phone_work;
                if (!empty($item->phone_work_additional)) $phone_work .= " (доб. {$item->phone_work_additional}) ";
                $contact[] = $phone_work;
            }
            if (!empty($item->phone_mobile)) $contact[] = $item->phone_mobile;
            if (!empty($item->email)) $contact[] = $item->email;

            if (empty($result['items'][$item->contractID]['for_accreditation']) && $item->for_accreditation == 1) {
                $result['items'][$item->contractID]['for_accreditation'][] = implode(', ', $contact);
            }
            if (empty($result['items'][$item->contractID]['for_building']) && $item->for_building == 1) {
                $result['items'][$item->contractID]['for_building'][] = implode(', ', $contact);
            }
        }
        return $result;
    }

    protected function populateState($ordering = 'length(number), number', $direction = 'ASC')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $status = $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status',  array(100));
        $this->setState('filter.status', $status);
        $without = $this->getUserStateFromRequest($this->context . '.filter.without', 'filter_without');
        $this->setState('filter.without', $without);
        parent::populateState($ordering, $direction);
        ContractsHelper::check_refresh();
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.status');
        $id .= ':' . $this->getState('filter.without');
        return parent::getStoreId($id);
    }

    private $export;
}
