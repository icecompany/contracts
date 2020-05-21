<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\AdminModel;

class ContractsModelContract extends AdminModel {

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);
        if ($item->id === null) {
            $item->companyID = JFactory::getApplication()->getUserState($this->option.'.contract.companyID');
            $item->projectID = JFactory::getApplication()->getUserState($this->option.'.contract.projectID');
            $item->managerID = JFactory::getUser()->id;
        }
        $company = $this->getCompany($item->companyID);
        $project = $this->getProject($item->projectID);
        $item->company = $company->title;
        $item->project = $project->title;
        $item->project_item = $project;

        $number = $item->number_free ?? $item->number;

        $item->title = $this->getTitle($item->company, $item->project, $item->dat ?? '', $number ?? '', $item->id ?? 0);
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

    public function getTitle(string $company, string $project, string $date = '', string $number = '', int $id = 0): string
    {
        $title = '';
        if ($id > 0) {
            if (!empty($date)) {
                $date = JDate::getInstance($date);
                $title = JText::sprintf('COM_CONTRACTS_CONTRACT_TITLE_EDIT_WITH_DATE', $company, $project, $date->format("d.m.Y"));
                if (!empty($number)) {
                    $title = JText::sprintf('COM_CONTRACTS_CONTRACT_TITLE_EDIT_WITH_DATE_AND_NUMBER', $number, $company, $project, $date->format("d.m.Y"));
                }
            }
        }
        else {
            $title = JText::sprintf('COM_CONTRACTS_CONTRACT_TITLE_ADD_NEW', $company, $project);
        }
        return $title;
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
        $form->addFieldPath(JPATH_ADMINISTRATOR."/components/com_mkv/models/fields");
        $form->addFieldPath(JPATH_ADMINISTRATOR."/components/com_prices/models/fields");

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