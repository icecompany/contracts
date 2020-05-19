<?php
defined('_JEXEC') or die;
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldContractStand extends JFormFieldList
{
    protected $type = 'ContractStand';
    protected $loadExternally = 0;

    protected function getOptions()
    {
        $id = JFactory::getApplication()->input->getInt('id', null);
        $db = JFactory::getDbo();
        if ($id !== null) {
            $query = $db->getQuery(true);
            $query
                ->select("ci.contractID")
                ->from("#__mkv_contract_items ci")
                ->where("ci.id = {$db->q($id)}");
            $contractID = $db->setQuery($query)->loadResult() ?? 0;
        }
        else {
            $contractID = JFactory::getApplication()->getUserState('com_contracts.item.contractID');
        }

        $query = $db->getQuery(true);
        $query
            ->select("cs.id, s.number, s.square")
            ->from('#__mkv_contract_stands cs')
            ->leftJoin("#__mkv_stands s on s.id = cs.standID")
            ->where("cs.contractID = {$contractID}")
            ->order("cs.id");
        $result = $db->setQuery($query)->loadObjectList();

        $options = array();

        foreach ($result as $item) {
            $title = JText::sprintf('COM_CONTRACTS_STANDS_NUMBER_WITH_SQUARE', $item->number, $item->square);
            $arr = array('data-square' => $item->square);
            $params = array('attr' => $arr, 'option.attr' => 'optionattr');
            $options[] = JHtml::_('select.option', $item->id, $title, $params);
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