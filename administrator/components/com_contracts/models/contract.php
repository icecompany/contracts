<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\ListModel;

class ContractsModelContract extends AdminModel {

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);
        if ($item->id === null) {
            $item->companyID = JFactory::getApplication()->getUserState($this->option.'.contract.companyID');
            $item->projectID = JFactory::getApplication()->getUserState($this->option.'.contract.projectID');
            $item->managerID = JFactory::getUser()->id;
        }
        else {
            $incoming = $this->getIncomingInfo($item->id);
            if ($incoming !== null) {
                $item->doc_status = $incoming->doc_status;
                $item->pvn_1 = $incoming->pvn_1;
                $item->pvn_1a = $incoming->pvn_1a;
                $item->pvn_1b = $incoming->pvn_1b;
                $item->pvn_1v = $incoming->pvn_1v;
                $item->pvn_1g = $incoming->pvn_1g;
                $item->catalog_info = $incoming->catalog_info;
                $item->catalog_logo = $incoming->catalog_logo;
                $item->no_exhibit = $incoming->no_exhibit;
                $item->info_arrival = $incoming->info_arrival;
            }
            $parent = $this->getParent($item->id);
            if (!empty($parent)) {
                $item->parent_id = $parent['parentID'];
                $item->parent_title = $parent['title'];
            }
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
        if ($data['id'] != '') {
            //Сохраняем заполненность формы
            $this->saveIncomingInfo($data['id'], $data);
            //Сохраняем компанию-родителя соэкспонента
            $this->saveParentID($data['id'], is_numeric($data['parentID']) ? $data['parentID'] : 0);
        }
        return parent::save($data);
    }

    public function setContractNumber($pk = null): int
    {
        $item = parent::getItem($pk);
        if ($item->id !== null) {
            if ($item->status != '1') {
                $app = JFactory::getApplication();
                $error = JText::sprintf('COM_CONTRACTS_ERROR_NUMBER_ONLY_FOR_CONTRACTS');
                $app->enqueueMessage($error, 'error');
                $app->redirect("index.php?option={$this->option}&view=contracts");
                jexit();
            }
            $number = ContractsHelper::getNextContractNumber($item->projectID);
            $table = $this->getTable();
            $table->load($item->id);
            $table->save(['id' => $item->id, 'number' => $number]);
            return (int) $number;
        }
        else {
            return 0;
        }
    }

    public function getChildren()
    {
        $item = parent::getItem();
        if ($item->id !== null) {
            $model = ListModel::getInstance('Parents', 'ContractsModel', ['companyID' => $item->companyID, 'projectID' => $item->projectID]);
            return $model->getItems();
        }
        else return [];
    }

    public function getParent(int $contractID): array
    {
        $table = JTable::getInstance('Parents', 'TableContracts');
        $table->load(['contractID' => $contractID]);
        if ($table->companyID !== null) {
            $company = $this->getCompany($table->companyID);
            return ['parentID' => $table->companyID, 'title' => $company->title];
        }
        else return [];
    }

    public function saveParentID(int $contractID, int $companyID)
    {
        $table = JTable::getInstance('Parents', 'TableContracts');
        $table->load(['contractID' => $contractID]);
        if ($companyID > 0) {
            $arr = ['id' => $table->id ?? null, 'contractID' => $contractID, 'companyID' => $companyID];
            $table->save($arr);
        }
        else {
            if ($table->id !== null) $table->delete($table->id);
        }
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

    public function getStands(): array
    {
        $id = JFactory::getApplication()->input->getInt('id', 0);
        if ($id > 0) {
            $model = ListModel::getInstance('StandsLight', 'ContractsModel', ['contractIDs' => [$id], 'byContractID' => true, 'byCompanyID' => false]);
            $items = $model->getItems();
            if (empty($items)) return [];
            return $items[$id];
        }
        else return [];
    }

    public function getIncomingInfo(int $contractID)
    {
        $table = JTable::getInstance('Incoming', 'TableContracts');
        $table->load(['contractID' => $contractID]);
        return $table;
    }

    public function saveIncomingInfo(int $contractID, array $data)
    {
        $table = JTable::getInstance('Incoming', 'TableContracts');
        $table->load(['contractID' => $contractID]);
        $arr = [];
        $arr['id'] = $table->id ?? null;
        $arr['doc_status'] = $data['doc_status'];
        $arr['pvn_1'] = $data['pvn_1'];
        $arr['pvn_1a'] = $data['pvn_1a'];
        $arr['pvn_1b'] = $data['pvn_1b'];
        $arr['pvn_1v'] = $data['pvn_1v'];
        $arr['pvn_1g'] = $data['pvn_1g'];
        $arr['catalog_info'] = $data['catalog_info'];
        $arr['catalog_logo'] = $data['catalog_logo'];
        $arr['no_exhibit'] = $data['no_exhibit'];
        $arr['info_arrival'] = $data['info_arrival'];
        $table->save($arr);
    }

    public function getContractItems(): array
    {
        $id = JFactory::getApplication()->input->getInt('id', 0);
        if ($id > 0) {
            $model = ListModel::getInstance('Items', 'ContractsModel', ['contractID' => $id]);
            return $model->getItems();
        }
        else return [];
    }

    public function getTitle(string $company, string $project, string $date = '', string $number = '', int $id = 0): string
    {
        if ($id > 0) {
            if (!empty($date)) {
                $date = JDate::getInstance($date);
                $title = JText::sprintf('COM_CONTRACTS_CONTRACT_TITLE_EDIT_WITH_DATE', $company, $project, $date->format("d.m.Y"));
                if (!empty($number)) {
                    $title = JText::sprintf('COM_CONTRACTS_CONTRACT_TITLE_EDIT_WITH_DATE_AND_NUMBER', $number, $company, $project, $date->format("d.m.Y"));
                }
            }
            else {
                $title = JText::sprintf('COM_CONTRACTS_CONTRACT_TITLE_EDIT_CONTRACT', $company, $project);
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
        $nulls = ['status', 'dat', 'number', 'number_free']; //Поля, которые NULL
        foreach ($all as $field => $v) {
            if (empty($field)) continue;
            if (in_array($field, $nulls)) {
                if (!strlen($table->$field)) {
                    $table->$field = NULL;
                    continue;
                }
            }
            if (!empty($field)) {
                $table->$field = trim($table->$field);
                //Приводим дату в нужный формат
                if ($field === 'dat') {
                    $table->$field = JDate::getInstance($table->$field)->toSql();
                }
            }
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