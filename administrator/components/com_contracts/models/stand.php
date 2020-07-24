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
        return parent::save($data);
    }

    public function getStandItems()
    {
        $item = parent::getItem();
        if ($item->id !== null) {
            $model = ListModel::getInstance('Items', 'ContractsModel', ['standID' => $item->id]);
            return $model->getItems();
        } else return [];
    }

    public function getStandFromCatalog(int $standID)
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
        $nulls = ['freeze', 'comment']; //Поля, которые NULL
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