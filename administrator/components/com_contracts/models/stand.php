<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\ListModel;

class ContractsModelStand extends AdminModel {

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);
        if ($item->id !== null) {
            $stand = $this->getStandFromCatalog($item->standID);
        }
        else {
            $item->contractID = JFactory::getApplication()->getUserState($this->option.'.stand.contractID');
        }
        $item->contract = $this->getContract($item->contractID);
        if ($item->id !== null) {
            $item->title = JText::sprintf('COM_CONTRACTS_TITLE_STAND_EDIT', $stand->number ?? '', $item->contract->company, $item->contract->project);
            $item->children = $this->getChildrenContracts($item->contract->companyID, $item->contract->projectID);
        }
        else {
            $item->title = JText::sprintf('COM_CONTRACTS_TITLE_STAND_ADD', $item->contract->company, $item->contract->project);
        }
        return $item;
    }

    public function save($data)
    {
        $s = parent::save($data);
        if ($data['id'] === null && $s) {
            $this->addItemToContract($this->_db->insertid(), $data['standID'], $data['contractID']);
        }
        return $s;
    }

    public function getStandItems()
    {
        $item = parent::getItem();
        if ($item->id !== null) {
            $model = ListModel::getInstance('Items', 'ContractsModel', ['standID' => $item->id]);
            return $model->getItems();
        } else return [];
    }

    public function getStandFromCatalog(int $standID): TableStandsStands
    {
        JTable::addIncludePath(JPATH_ADMINISTRATOR."/components/com_stands/tables");
        $table = JTable::getInstance('Stands', 'TableStands');
        $table->load($standID);
        return $table;
    }

    public function getContract(int $contractID)
    {
        $model = AdminModel::getInstance('Contract', 'ContractsModel');
        return $model->getItem($contractID);
    }

    private function addItemToContract(int $contractStandID, int $standID, int $contractID): bool
    {
        $can = $this->checkAddItemToContract($contractStandID, $standID, $contractID);
        if (!$can['result']) {
            $msg = sprintf("%s: %s", JText::sprintf('COM_CONTRACTS_FORM_STAND_ADD_ITEM_ERROR'), $can['messages']);
            JFactory::getApplication()->enqueueMessage($msg, 'error');
            return false;
        }
        else {
            $model = AdminModel::getInstance('Item', 'ContractsModel');
            $model->save($can['data']);
            return true;
        }
    }

    private function checkAddItemToContract(int $contractStandID, int $standID, int $contractID): array
    {
        $result = true;
        $messages = [];
        $data = [];
        $return = ['result' => $result, 'messages' => [], 'data' => $data];
        $stand = $this->getStandFromCatalog($standID);
        if (!is_numeric($stand->itemID)) {
            $result = false;
            $messages[] = JText::sprintf('COM_CONTRACTS_FORM_STAND_ADD_ITEM_ERROR_NO_ITEM_ID');
        }
        if (!is_numeric($stand->open)) {
            $result = false;
            $messages[] = JText::sprintf('COM_CONTRACTS_FORM_STAND_ADD_ITEM_ERROR_NO_ITEM_ID');
        }
        if ($result) {
            $contract = $this->getContract($contractID);
            $arr = [];
            $arr['id'] = null;
            $arr['contractID'] = $contractID;
            $arr['itemID'] = $stand->itemID;
            $arr['columnID'] = $contract->project_item->columnID;
            $arr['contractStandID'] = $contractStandID;
            $arr['factor'] = ($contract->status != 9) ? 0 : 100;
            $arr['markup'] = $this->getMarkup((int) $stand->open);
            $arr['value'] = $stand->square;
        }

        $return['result'] = $result;
        if (!empty($messages)) {
            $return['messages'] = implode('; ', $messages);
        }
        if ($result) $return['data'] = $arr;
        return $return;
    }

    private function getMarkup(int $open): float
    {
        switch ($open) {
            case 2: {
                $markup = 1.1;
                break;
            }
            case 3: {
                $markup = 1.15;
                break;
            }
            case 4: {
                $markup = 1.2;
                break;
            }
            default: $markup = 1;
        }
        return $markup;
    }

    private function getChildrenContracts(int $parentID, int $projectID)
    {
        $model = AdminModel::getInstance('Parents', 'ContractsModel', ['companyID' => $parentID, 'projectID' => $projectID]);
        return $model->getItems();
    }

    public function getTable($name = 'Stands', $prefix = 'TableContracts', $options = array())
    {
        return JTable::getInstance($name, $prefix, $options);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            $this->option.'.stand', 'stand', array('control' => 'jform', 'load_data' => $loadData)
        );
        if (empty($form))
        {
            return false;
        }
        $form->addFieldPath(JPATH_ADMINISTRATOR."/components/com_stands/models/fields");

        return $form;
    }

    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState($this->option.'.edit.stand.data', array());
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
        $nulls = ['freeze', 'comment', 'production_diversification', 'production_first_in_forum', 'production_first_in_world']; //Поля, которые NULL
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
            return $user->authorise('core.edit.state', $this->option . '.stand.' . (int) $record->id);
        }
        else
        {
            return parent::canEditState($record);
        }
    }

    public function getScript()
    {
        return 'administrator/components/' . $this->option . '/models/forms/stand.js';
    }
}