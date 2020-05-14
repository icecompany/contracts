<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\AdminModel;

class ContractsModelContract extends AdminModel {

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);
        if ($item->id !== null) {
            $company = $this->getCompany($item->companyID);
            $project = $this->getProject($item->projectID);
            $item->company = $company->title;
            $item->project = $project->title;
        }
        return $item;
    }

    public function save($data)
    {
        return parent::save($data);
    }

    public function getCompany(int $companyID) {
        JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_companies/tables");
        $table = JTable::getInstance('Companies', 'TableCompanies', []);
        $table->load($companyID);
        return $table;
    }

    public function getProject(int $projectID)
    {
        JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_prj/tables");
        $table = JTable::getInstance('Projects', 'TablePrj', []);
        $table->load($projectID);
        return $table;
    }

    public function getTable($name = 'Contracts', $prefix = 'TableContracts', $options = array())
    {
        return JTable::getInstance($name, $prefix, $options);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            $this->option.'.contract', 'contract', array('control' => 'jform', 'load_data' => $loadData)
        );
        if (empty($form))
        {
            return false;
        }

        return $form;
    }

    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState($this->option.'.edit.contract.data', array());
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
        $nulls = []; //Поля, которые NULL
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
            return $user->authorise('core.edit.state', $this->option . '.contract.' . (int) $record->id);
        }
        else
        {
            return parent::canEditState($record);
        }
    }

    public function getScript()
    {
        return 'administrator/components/' . $this->option . '/models/forms/contract.js';
    }
}