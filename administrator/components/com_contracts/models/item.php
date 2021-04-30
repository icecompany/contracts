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
            $price_item = $this->getPriceItem($item->itemID);
            $item->price_item = $price_item;
            $item->item = $price_item->title;
            $item->price_type = $price_item->type;
            $item->factor = 100 - (100 * $item->factor);
            if ($item->payerID !== null) {
                $payer = $this->getPayer($item->payerID);
                $item->payer_id = $payer->id;
                $item->payer_title = $payer->title;
            }
            $item->unit_2_title = $this->getUnit2Title($price_item->unit_2_ID);
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
        $item = $this->getPriceItem($data['itemID']); //Пункт прайса, который хотят заказать
        $app = JFactory::getApplication();
        $old_value = 0; //Старое значение, для уже существующих записей
        if ($data['id'] !== null) {
            $already = parent::getItem($data['id']); //Текущая запись
            $old_value = (float) $already->value; //Уже заказано в текущей записи
        }
        //Проверяем доступное количество
        $balance = (float) ($item->available + $old_value - $data['value']); //Остаток, доступный в случае успешного сохранения
        if ($item->available > 0 && $item->available !== null && $balance < 0) {
            $app->enqueueMessage(JText::sprintf('COM_CONTRACTS_ERROR_VALUE_IS_OUT_OF_AVAILABLE_RANGE', $item->available), 'warning');
            return false;
        }
        //Проверяем заполненность стенда
        if ($item->type === 'square' || $item->type === 'electric' || $item->type === 'internet' || $item->type === 'multimedia' || $item->type === 'water' || $item->type === 'cleaning') {
            if (empty($data['contractStandID'])) {
                $app->enqueueMessage(JText::sprintf('COM_CONTRACTS_ERROR_STAND_IS_NOT_SELECTED'), 'warning');
                return false;
            }
        }
        //Проверяем заполненность периода
        if ($item->need_period == '1') {
            if (empty($data['date_1']) || empty($data['date_2'] || $data['date_1'] === '0000-00-00 00:00:00') || $data['date_2'] === '0000-00-00 00:00:00') {
                $app = JFactory::getApplication();
                $app->enqueueMessage(JText::sprintf('COM_CONTRACTS_ERROR_EMPTY_PERIOD'), 'error');
                return false;
            }
        }
        $standID = 0;
        if ($data['contractStandID'] !== null) {
            $table = JTable::getInstance('Stands', 'TableContracts');
            $table->load($data['contractStandID']);
            $standID = $table->standID;
        }
        if ($data['id'] === null) {
            $this->sendNotifyNewItem($data['contractID'], $data['itemID'], $data['value'], $standID ?? 0);
        }
        if ($item->type === 'technical' && empty($data['description'])) {
            $app->enqueueMessage(JText::sprintf('COM_CONTRACTS_ERROR_EMPTY_DESCRIPTION'), 'error');
            return false;
        }
        //Заполняем стоимость единицы
        $data['cost'] = $this->getCost($item, $data['contractID']);

        $s = parent::save($data);
        //Изменяем доступное кол-во остатка в пункте прайса
        if ($s && $item->available !== null) $this->setNewBalance($data['itemID'], $balance);

        //Пишем в историю
        if ($s) {
            $hst = [];
            $hst['managerID'] = JFactory::getUser()->id;
            $hst['itemID'] = $data['id'] ?? JFactory::getDbo()->insertid();
            $hst['action'] = ($data['id'] !== null) ? 'update' : 'add';
            $hst['section'] = 'cart';
            $hst['new_data'] = json_encode($data);
            $hst['old_data'] = '';
            if ($hst['action'] === 'update') {
                $hst['old_data'] = json_encode($already);
            }
            JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_mkv/tables");
            $history = JTable::getInstance('History', 'TableMkv');
            $history->save($hst);
        }
        return $s;
    }

    private function getCost(TablePricesItems $price_item, int $contractID): float
    {
        $contract = $this->getContract($contractID);
        $columnID = $contract->project_item->columnID;
        $field_column = "column_{$columnID}";
        $field_currency = "price_{$contract->currency}";
        return (float) ((float) $price_item->$field_currency * (float) $price_item->$field_column);
    }

    /**
     * Установка нового доступного значения для прайса
     * @param int $itemID ID пункта прайса
     * @param float $available новое доступное значение
     * @since 2.0.5
     */
    private function setNewBalance(int $itemID, float $available): void
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->update("#__mkv_price_items")
            ->set($db->qn("available") . " = " . $db->q($available))
            ->where($db->qn("id") . " = " . $db->q($itemID));
        $db->setQuery($query)->execute();
    }

    private function sendNotifyNewItem(int $contractID, int $itemID, int $value, int $standID = 0): void
    {
        $contract = $this->getContract($contractID);
        $company = $contract->company;
        $priceItem = $this->getPriceItem($itemID);
        if ($standID > 0) {
            //Уведомления о добавлении услуг в стенд
            if (ContractsHelper::getConfig('notify_new_stand_item_status') != '1') return;
            $groupID = ContractsHelper::getConfig('notify_new_stand_item_group');
            if (empty($groupID) || $groupID === null) return;
            $members = MkvHelper::getGroupUsers($groupID);
            if (empty($members)) return;
            $stand = $this->getStand($standID);
            $data['text'] = JText::sprintf('COM_CONTRACTS_NOTIFY_NEW_STAND_ITEM', $priceItem->title, $value, $company);
            $push_id = ContractsHelper::getConfig('notify_new_stand_item_chanel_id');
            $push_key = ContractsHelper::getConfig('notify_new_stand_item_chanel_key');
        }
        else {
            //Уведомления о конкретных пунктах прайса
            if (ContractsHelper::getConfig('notify_new_item_status') != '1') return;
            $data['text'] = JText::sprintf('COM_CONTRACTS_NOTIFY_NEW_ITEM', $priceItem->title, $value);
            $push_id = ContractsHelper::getConfig('notify_new_item_chanel_id');
            $push_key = ContractsHelper::getConfig('notify_new_item_chanel_key');
            $members = $this->getWatchers($itemID);
            if (empty($members)) return;
            $uids = $this->getRecipients($push_id, $push_key, $members);
        }
        $data['contractID'] = $contractID;
        $need_push = true;
        foreach ($members as $member) {
            $data['managerID'] = $member;
            $push = [];
            $push['id'] = $push_id;
            $push['key'] = $push_key;
            $push['title'] = ($standID > 0) ? JText::sprintf('COM_CONTRACTS_NOTIFY_NEW_STAND_ITEM_TITLE', $stand->number) : $company;
            $push['text'] = $data['text'];
            if (isset($uids) && !empty($uids)) $push['uids'] = "[" . implode(',', $uids) . "]";
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
        $nulls = ['contractStandID', 'value2', 'date_1', 'date_2', 'payerID', 'description']; //Поля, которые NULL
        foreach ($all as $field => $v) {
            if (empty($field)) continue;
            if (in_array($field, $nulls)) {
                if (!strlen($table->$field)) {
                    $table->$field = NULL;
                    continue;
                }
            }
            if (!empty($field)) $table->$field = trim($table->$field);
            if ($field === 'date_1' || $field === 'date_2') {
                $table->$field = (!empty($table->$field) && $table->$field !== '0000-00-00 00:00:00') ? JDate::getInstance($table->$field)->format("Y-m-d") : NULL;
            }
        }
        $table->factor = (float) 1 - ($table->factor / 100);
        $table->cost = (float) str_replace([' ₽', ' $', ' €', ' ', ','], ['', '', '', '', '.'], $table->cost);
        $table->amount = (float) str_replace([' ₽', ' $', ' €', ' ', ','], ['', '', '', '', '.'], $table->amount);
        if ($table->value2 <= 0 || $table->value2 == 1) $table->value2 = NULL;

        parent::prepareTable($table);
    }

    public function getUnit2Title(int $id): string
    {
        JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_prices/tables");
        $table = JTable::getInstance('Units', 'TablePrices');
        $table->load($id);
        return $table->title ?? '';
    }

    private function getRecipients(int $channelID, string $api_key, array $managerIDs = [])
    {
        if (empty($managerIDs)) return [];
        $ids = implode(', ', $managerIDs);
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("distinct uid")
            ->from("#__mkv_managers_push_channels")
            ->where("channelID = {$db->q($channelID)} and api_key = {$db->q($api_key)} and managerID in ({$ids})");
        return $db->setQuery($query)->loadColumn() ?? [];
    }

    private function getWatchers(int $itemID)
    {
        JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_prices/tables");
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_prices/models", "PricesModel");
        $model = JModelLegacy::getInstance('Watchers', "PricesModel", ['itemID' => $itemID]);
        return $model->getItems();
    }

    private function getStand(int $standID)
    {
        JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_stands/tables");
        $table = JTable::getInstance('Stands', 'TableStands');
        $table->load($standID);
        return $table;
    }

    private function getPriceItem(int $itemID): TablePricesItems
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

    public function delete(&$pks)
    {
        //Пишем историю
        JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_mkv/tables");
        foreach ($pks as $pk) {
            $item = parent::getItem($pk);
            $d = parent::delete($pk);
            if ($d) {
                $hst = [];
                $hst['managerID'] = JFactory::getUser()->id;
                $hst['itemID'] = $item->id;
                $hst['section'] = 'cart';
                $hst['action'] = 'delete';
                $hst['old_data'] = json_encode($item);
                $hst['new_data'] = '';
                $history = JTable::getInstance('History', 'TableMkv');
                $history->save($hst);
            }
            else return false;
        }
        return true;
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