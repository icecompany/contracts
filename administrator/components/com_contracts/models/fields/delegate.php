<?php
defined('_JEXEC') or die;
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldContractDelegate extends JFormFieldList
{
    protected $type = 'Delegate';
    protected $loadExternally = 0;

    protected function getOptions()
    {
        $id = JFactory::getApplication()->input->getInt('id', 0);
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("id, code, title")
            ->from('#__mkv_contract_statuses')
            ->order("ordering");
        $result = $db->setQuery($query)->loadObjectList();

        $options = array();

        foreach ($result as $item) {
            $options[] = JHtml::_('select.option', $item->code, $item->title);
        }

        if (!$this->loadExternally) {
            $options = array_merge(parent::getOptions(), $options);
        }

        return $options;
    }

    public function getOptionsExternally()
    {
        $this->loadExternally = 1;
        return $this->getOptions();
    }
}