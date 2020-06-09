<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Helper\UserGroupsHelper;

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
            $sent = $this->getSentInfo($item->id);
            if ($sent !== null) {
                $item->invite_date = $sent->invite_date;
                $item->invite_outgoing_number = $sent->invite_outgoing_number;
                $item->invite_incoming_number = $sent->invite_incoming_number;
            }
            $parent = $this->getParent($item->id);
            if (!empty($parent)) {
                $item->parent_id = $parent['parentID'];
                $item->parent_title = $parent['title'];
            }
            $item->activities = $this->getActivities($item->companyID);
            $item->thematics = $this->getThematics($item->id);
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
            $this->saveSentInfo($data['id'], $data);
            //Сохраняем компанию-родителя соэкспонента
            $this->saveParentID($data['id'], is_numeric($data['parentID']) ? $data['parentID'] : 0);
            //Сохраняем тематические рубрики
            $this->saveThematics($data['id'], $data['thematics'] ?? []);
            //Сохраняем виды деятельности
            $this->saveActivities($data['companyID'], $data['activities'] ?? []);
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

    public function getTasks(): array
    {
        $item = parent::getItem();
        if ($item->id === null) return [];
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_scheduler/models");
        $model = JModelLegacy::getInstance('Tasks', 'SchedulerModel', ['contractID' => $item->id]);
        return $model->getItems();
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

    public function getPayments()
    {
        $id = JFactory::getApplication()->input->getInt('id', 0);
        if ($id > 0) {
            JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_finances/models", "FinancesModel");
            $model = JModelLegacy::getInstance('Payments', 'FinancesModel', ['contractID' => $id]);
            $items = $model->getItems();
            if (empty($items['items'])) return [];
            return $items['items'];
        }
        else return [];
    }

    public function getIncomingInfo(int $contractID)
    {
        $table = JTable::getInstance('Incoming', 'TableContracts');
        $table->load(['contractID' => $contractID]);
        return $table;
    }

    public function getSentInfo(int $contractID)
    {
        $table = JTable::getInstance('Sent', 'TableContracts');
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
        if ($table->doc_status == '0' && $data['doc_status'] != 0) {
            $this->sendNotifyNewDocStatus($contractID, $data['companyID'], $data['doc_status']);
        }
        $table->save($arr);
    }

    public function saveSentInfo(int $contractID, array $data)
    {
        $model = AdminModel::getInstance('Sent', 'ContractsModel');
        $item = $model->getItem(['contractID' => $contractID]);
        $arr = [];
        $arr['id'] = $item->id ?? null;
        $arr['invite_date'] = $data['invite_date'];
        $arr['invite_outgoing_number'] = $data['invite_outgoing_number'];
        $arr['invite_incoming_number'] = $data['invite_incoming_number'];
        $model->save($arr);
    }

    private function sendNotifyNewDocStatus(int $contractID, int $companyID, int $new_status): void
    {
        if (ContractsHelper::getConfig('notify_new_doc_status_status') != '1') return;
        $groupID = ContractsHelper::getConfig('notify_new_doc_status_group');
        if (empty($groupID) || $groupID === null) return;
        $members = MkvHelper::getGroupUsers($groupID);
        if (empty($members)) return;
        $company = $this->getCompany($companyID);
        $data['text'] = JText::sprintf('COM_CONTRACTS_NOTIFY_NEW_DOC_STATUS', $company->title, JText::sprintf("COM_CONTRACTS_DOC_STATUS_{$new_status}_SHORT"));
        $data['contractID'] = $contractID;
        foreach ($members as $member) {
            $data['managerID'] = $member;
            $push = [];
            $push['id'] = ContractsHelper::getConfig('notify_new_doc_status_chanel_id');
            $push['key'] = ContractsHelper::getConfig('notify_new_doc_status_chanel_key');
            $push['title'] = JText::sprintf('COM_CONTRACTS_NOTIFY_NEW_DOC_STATUS_TITLE');
            $push['text'] = $data['text'];
            SchedulerHelper::sendNotify($data, $push);
        }
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
        $form->addFieldPath(JPATH_ADMINISTRATOR."/components/com_prj/models/fields");
        $form->addFieldPath(JPATH_ADMINISTRATOR."/components/com_companies/models/fields");

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

    private function getActivities(int $companyID)
    {
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_companies/models", 'CompaniesModel');
        $model = JModelLegacy::getInstance('Companies_activities', 'CompaniesModel', ['companyID' => $companyID, 'ignore_request' => true]);
        $items = $model->getItems();
        $result = [];
        foreach ($items as $item) $result[] = $item['activityID'];
        return $result;
    }

    private function saveActivities(int $companyID, array $activities = [])
    {
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_companies/models", 'CompaniesModel');
        JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_companies/tables");
        $cm = JModelLegacy::getInstance('Company', 'CompaniesModel');
        $cm->saveActivities($companyID, $activities ?? []);
    }

    private function getThematics(int $contractID)
    {
        $model = ListModel::getInstance('Thematics', 'ContractsModel', ['contractID' => $contractID]);
        return $model->getItems();
    }

    private function saveThematics(int $contractID, array $thematics = [])
    {
        $model = ListModel::getInstance('Thematics', 'ContractsModel', ['contractID' => $contractID]);
        $current = $model->getItems();
        if (empty($current)) {
            if (empty($thematics)) return;
            foreach ($thematics as $thematicID)
                $this->addThematic($contractID, $thematicID);
        }
        else {
            foreach ($thematics as $item)
                if (($key = array_search($item, $current)) === false)
                    $this->addThematic($contractID, $item);
            foreach ($current as $item)
                if (($key = array_search($item, $thematics)) === false)
                    $this->deleteThematic($contractID, $item);
        }
    }

    private function addThematic(int $contractID, int $thematicID)
    {
        $table = $this->getTable('Thematics', 'TableContracts');
        $data = ['id' => null, 'contractID' => $contractID, 'thematicID' => $thematicID];
        $table->save($data);
    }

    private function deleteThematic(int $contractID, int $thematicID)
    {
        $table = $this->getTable('Thematics', 'TableContracts');
        $table->load(['contractID' => $contractID, 'thematicID' => $thematicID]);
        $table->delete($table->id);
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