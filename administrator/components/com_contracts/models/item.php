<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\AdminModel;

class ContractsModelItem extends AdminModel {

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);
        if ($item->id === null) {
            $item->contractID = JFactory::getApplication()->getUserState($this->option.'.item.contractID');
        }
        else {
            $item->item = $this->getPriceItem($item->itemID)->title;
            $item->price_type = $this->getPriceItem($item->itemID)->type;
            $item->factor = 100 - (100 * $item->factor);
        }
        $item->contract = $this->getContract($item->contractID);
        if ($item->id === null) {
            $item->columnID = $item->contract->project_item->columnID;
        }
        else {
            $item->contract_new_amount = $item->contract->amount;
            $item->old_amount = $item->amount;
        }
        $item->contract_old_amount = $item->contract->amount;
        return $item;
    }

    public function save($data)
    {
        return parent::save($data);
    }

    public function getContract(int $contractID)
    {
        $model = AdminModel::getInstance('Contract', 'ContractsModel');
        return $model->getItem($contractID);
    }

    public function getPriceItem(int $itemID)
    {
        JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_prices/tables");
        $table = JTable::getInstance('Items', 'TablePrices');
        $table->load($itemID);
        return $table;
    }

    public function getTable($name = 'Items', $prefix = 'TableContracts', $options = array())
    {
        return JTable::getInstance($name, $prefix, $options);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            $this->option.'.item', 'item', array('control' => 'jform', 'load_data' => $loadData)
        );
        $form->addFieldPath(JPATH_ADMINISTRATOR."/components/com_prices/models/fields");
        if (empty($form))
        {
            return false;
        }

        return $form;
    }

    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState($this->option.'.edit.item.data', array());
        if (empty($data))
        {
            $data = $this->getItem();
        }

        return $data;
    }

    protected function prepareTable($table)
    {
        $all = get_class_vars($table);
        unset($all['_errors']);
        $nulls = ['value2']; //Поля, которые NULL
        foreach ($all as $field => $v) {
            if (empty($field)) continue;
            if (in_array($field, $nulls)) {
                if (!strlen($table->$field)) {
                    $table->$field = NULL;
                    continue;
                }
            }
            if (!empty($field)) $table->$field = trim($table->$field);
        }

        parent::prepareTable($table);
    }

    protected function canEditState($record)
    {
        $user = JFactory::getUser();

        if (!empty($record->id))
        {
            return $user->authorise('core.edit.state', $this->option . '.item.' . (int) $record->id);
        }
        else
        {
            return parent::canEditState($record);
        }
    }

    public function getScript()
    {
        return 'administrator/components/' . $this->option . '/models/forms/item.js';
    }
}