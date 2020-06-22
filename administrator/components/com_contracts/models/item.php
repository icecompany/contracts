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
            if ($item->payerID !== null) {
                $payer = $this->getPayer($item->payerID);
                $item->payer_id = $payer->id;
                $item->payer_title = $payer->title;
            }

        }
        $item->contract = $this->getContract($item->contractID);
        if ($item->id === null) {
            $item->columnID = $item->contract->project_item->columnID;
            //Разрешаем менеджерам выбирать колонку
            if ($item->contract->status === '1' && JDate::getInstance()->format("Y-m-d") === JDate::getInstance($item->contract->dat)->format("Y-m-d")) {
                $item->columnID = '1';
            }
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
        $standID = 0;
        if ($data['contractStandID'] !== null) {
            $table = JTable::getInstance('Stands', 'TableContracts');
            $table->load($data['contractStandID']);
            $standID = $table->standID;
        }
        if ($data['id'] === null) {
            $this->sendNotifyNewItem($data['contractID'], $data['itemID'], $data['value'], $standID);
        }
        return parent::save($data);
    }

    private function sendNotifyNewItem(int $contractID, int $itemID, int $value, int $standID = 0): void
    {
        if (ContractsHelper::getConfig('notify_new_stand_item_status') != '1') return;
        $groupID = ContractsHelper::getConfig('notify_new_stand_item_group');
        if (empty($groupID) || $groupID === null) return;
        $members = MkvHelper::getGroupUsers($groupID);
        if (empty($members)) return;
        $contract = $this->getContract($contractID);
        $company = $contract->company;
        $priceItem = $this->getPriceItem($itemID);
        if ($standID > 0) {
            $stand = $this->getStand($standID);
            $data['text'] = JText::sprintf('COM_CONTRACTS_NOTIFY_NEW_STAND_ITEM', $priceItem->title, $value, $company);
        }
        else {
            $data['text'] = JText::sprintf('COM_CONTRACTS_NOTIFY_NEW_ITEM', $priceItem->title, $value);
        }
        $data['contractID'] = $contractID;
        $need_push = true;
        foreach ($members as $member) {
            $data['managerID'] = $member;
            $push = [];
            $push['id'] = ContractsHelper::getConfig('notify_new_stand_item_chanel_id');
            $push['key'] = ContractsHelper::getConfig('notify_new_stand_item_chanel_key');
            $push['title'] = ($standID > 0) ? JText::sprintf('COM_CONTRACTS_NOTIFY_NEW_STAND_ITEM_TITLE', $stand->number) : JText::sprintf('COM_CONTRACTS_NOTIFY_NEW_ITEM', $company);
            $push['text'] = $data['text'];
            SchedulerHelper::sendNotify($data, (!$need_push) ? [] : $push);
            $need_push = false;
        }
    }

    public function getPayer(int $payerID)
    {
        JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_companies/tables");
        $table = JTable::getInstance('Companies', 'TableCompanies');
        $table->load($payerID);
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
        $nulls = ['contractStandID', 'value2', 'payerID']; //Поля, которые NULL
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
        $table->factor = (float) 1 - ($table->factor / 100);
        $table->cost = (float) str_replace([' ₽', ' $', ' €', ' ', ','], ['', '', '', '', '.'], $table->cost);
        $table->amount = (float) str_replace([' ₽', ' $', ' €', ' ', ','], ['', '', '', '', '.'], $table->amount);
        if ($table->value2 <= 0 || $table->value2 == 1) $table->value2 = NULL;

        parent::prepareTable($table);
    }

    private function getStand(int $standID)
    {
        JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_stands/tables");
        $table = JTable::getInstance('Stands', 'TableStands');
        $table->load($standID);
        return $table;
    }

    private function getPriceItem(int $itemID)
    {
        JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_prices/tables");
        $table = JTable::getInstance('Items', 'TablePrices');
        $table->load($itemID);
        return $table;
    }

    private function getContract(int $contractID)
    {
        $model = AdminModel::getInstance('Contract', 'ContractsModel');
        return $model->getItem($contractID);
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