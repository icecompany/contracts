<?php
defined('_JEXEC') or die;
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('groupedlist');

class JFormFieldContractLists extends JFormFieldGroupedList
{
    protected $type = 'ContractLists';
    protected $loadExternally = 0;

    protected function getGroups()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("l.id, l.title, t.title as list")
            ->from('#__mkv_lists l')
            ->leftJoin("#__mkv_lists_types t on t.id = l.typeID")
            ->order("t.title, l.title");
        $result = $db->setQuery($query)->loadObjectList();

        $options = array();

        foreach ($result as $item) {
            if (!isset($options[$item->list])) $options[$item->list] = [];
            $options[$item->list][] = JHtml::_('select.option', $item->id, $item->title);
        }

        if (!$this->loadExternally) {
            $options = array_merge(parent::getGroups(), $options);
        }

        return $options;
    }
}