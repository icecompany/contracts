<?php
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

class ContractsModelStatuses extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                's.id',
                's.code',
                's.title',
                's.ordering',
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
            ->select("s.id, s.title, s.code, s.ordering")
            ->from("#__mkv_contract_statuses s");
        $search = (!$this->export) ? $this->getState('filter.search') : JFactory::getApplication()->input->getString('search', '');
        if (!empty($search)) {
            if (stripos($search, 'id:') !== false) { //Поиск по ID
                $id = explode(':', $search);
                $id = $id[1];
                if (is_numeric($id)) {
                    $query->where("s.id = {$this->_db->q($id)}");
                }
            }
            else {
                $text = $this->_db->q("%{$search}%");
                $query->where("(s.title like {$text})");
            }
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
            $arr['title'] = $item->title;
            $arr['ordering'] = $item->ordering;
            $arr['code'] = $item->code;
            $url = JRoute::_("index.php?option={$this->option}&amp;task=status.edit&amp;id={$item->id}&amp;return={$return}");
            $arr['edit_link'] = JHtml::link($url, $item->title);
            $result['items'][] = $arr;
        }
        return $result;
    }

    protected function populateState($ordering = 's.ordering', $direction = 'asc')
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
