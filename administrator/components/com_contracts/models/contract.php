<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Helper\UserGroupsHelper;

class ContractsModelContract extends AdminModel
{

	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);
		$app = JFactory::getApplication();
		if ($item->id === null) {
			$item->companyID = $app->getUserState($this->option . '.contract.companyID');
			$projectFromRequest = $app->getUserState($this->option . '.contract.projectID');
			$item->projectID = PrjHelper::getActiveProject() ?? MkvHelper::getConfig('default_project');
			if (is_numeric($projectFromRequest)) {
				$item->projectID = $projectFromRequest;
				$app->setUserState($this->option . '.contract.projectID', '');
			}
			$item->managerID = JFactory::getUser()->id;
			$item->canAddStand = false;
			$item->canAddItem = false;
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
				$item->scheme_title_ru = $incoming->scheme_title_ru;
				$item->scheme_title_en = $incoming->scheme_title_en;
				$item->title_to_diploma = $incoming->title_to_diploma;
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
			$item->listID = $this->getLists($item->id);
			$item->canAddStand = $this->canAddStand($item->status);
			$item->canAddItem = $this->canAddItem($item->status);
		}
		$company = $this->getCompany($item->companyID);
		$project = $this->getProject($item->projectID);
		$item->company = $company->title;
		$item->project = $project->title;
		$item->is_archive = $project->is_archive;
		$item->project_item = $project;

		$number = $item->number_free ?? $item->number;

		$item->title = $this->getTitle($item->company, $item->project, $item->dat ?? '', $number ?? '', $item->id ?? 0);
		return $item;
	}

	public function save($data)
	{
		$app = JFactory::getApplication();
		//Автозаполнение менеджера
		if (!ContractsHelper::canDo('core.show.all')) $data['managerID'] = JFactory::getUser()->id;

		if (!empty($data['number_free']) && ContractsHelper::isContractNumber666($data['number_free'])) {
			$app->enqueueMessage(JText::sprintf('COM_CONTRACTS_ERROR_SAVE_NUMBER_666'), 'error');
			return false;
		}

		if ($data['id'] != '') {
			$item = parent::getItem($data['id']);
			//Проверяем возможность выставить отказ
			if ((int)$data['status'] === 0) {
				//Проверяем наличие оплаты по сделке
				if ((float)$item->payments > 0) {
					$app->enqueueMessage(JText::sprintf('COM_CONTRACTS_ERROR_SAVE_CONTRACT_IS_PAYMENTS'), 'error');
					return false;
				}
				//Проверяем наличие стендов
				if ($this->isStandsInContract($data['id'])) {
					$app->enqueueMessage(JText::sprintf('COM_CONTRACTS_ERROR_SAVE_CONTRACT_IS_STANDS'), 'error');
					return false;
				}
			}

			//Сохраняем заполненность формы
			$this->saveIncomingInfo($data['id'], $data);
			$this->saveSentInfo($data['id'], $data);
			//Сохраняем компанию-родителя соэкспонента
			$this->saveParentID($data['id'], is_numeric($data['parentID']) ? $data['parentID'] : 0);
			//Сохраняем списки
			$this->saveLists($data['id'], $data['listID'] ?? []);
			//Сохраняем тематические рубрики
			$this->saveThematics($data['id'], $data['thematics'] ?? []);
			//Сохраняем виды деятельности
			$this->saveActivities($data['companyID'], $data['activities'] ?? []);
			//Переносим задачи
			if ($item->managerID != $data['managerID']) {
				SchedulerHelper::updateTaskManager($data['id'], $data['managerID']);
			}
			//Обнуляем сумму заказанных услуг, номер и дату договора, если сделка переходит в отказ
			if ($data['status'] == '0') {
				ContractsHelper::setZeroAmount($data['id']);
				if ($item->status == 1) {
					//Уведомляем об аннулировании договора
					$this->sendNotifyReject($data);
				}
			}
			//Загрузка файла
			$this->uploadFiles($data['id']);
		}
		//Присваиваем номер
		if ($data['status'] == 1) {
			if ($data['id'] == null || (isset($item) && $item->status != 1)) {
				$data['number'] = $this->setContractNumber($data['projectID']);
				$data['dat'] = JDate::getInstance()->toSql();
			}
		}
		$s = parent::save($data);
		//Пишем в историю
		if ($s) {
			$hst = [];
			$hst['managerID'] = JFactory::getUser()->id;
			$hst['itemID'] = $data['id'] ?? JFactory::getDbo()->insertid();
			$hst['action'] = ($data['id'] !== null) ? 'update' : 'add';
			$hst['section'] = 'contract';
			$hst['new_data'] = json_encode($data);
			$hst['old_data'] = '';
			if ($hst['action'] === 'update') {
				$hst['old_data'] = json_encode($item);
			}
			JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_mkv/tables");
			$history = JTable::getInstance('History', 'TableMkv');
			$history->save($hst);
			//Увеодмляем об изменении статуса
			$contract_statuses = [1, 5, 6, 9, 10];
			$notify_group = ContractsHelper::getConfig('notify_new_exhibitor_group', null);
			if ($notify_group !== null) {
				$notify_users = MkvHelper::getGroupUsers($notify_group);
				if ($item->id !== null && $item->status != $data['status'] && in_array($data['status'], $contract_statuses) && !empty($notify_users)) {
					$ntf = [];
					$ntf['user_create'] = 377;
					$ntf['contractID'] = $data['id'];
					$old_status = $this->getContractStatus($item->status);
					$new_status = $this->getContractStatus($data['status']);
					$ntf['text'] = JText::sprintf('COM_CONTRACTS_NOTIFY_NEW_CONTRACT_STATUS', $old_status, $new_status);
					foreach ($notify_users as $notify_user) {
						$ntf['managerID'] = $notify_user;
						SchedulerHelper::sendNotify($ntf);
					}
				}
			}
		}
		return $s;
	}

	public function setContractNumber(int $projectID): int
	{
		return ContractsHelper::getNextContractNumber($projectID);
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

	public function getCompany(int $companyID)
	{
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
			$model = JModelLegacy::getInstance('Sap', 'FinancesModel', ['contractID' => $id]);
			$items = $model->getItems();
			if (empty($items)) return [];
			return $items;
		}
		else return [];
	}

	public function getFiles(): array
	{
		$item = parent::getItem();
		if ($item->id === null) return [];
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_yastorage/models", 'YastorageModel');
		$model = JModelLegacy::getInstance('Mkv', 'YastorageModel');
		$bucket = 'mkv-contracts';
		$prefix = "contracts/{$item->id}/";
		$files = $model->listObjects($bucket, $prefix);
		if (empty($files)) return [];
		$result = [];
		foreach ($files as $file) {
			$arr = [];
			$url = JRoute::_("index.php?option=com_yastorage&amp;task=mkv.download&amp;bucket={$bucket}&amp;key={$file['Key']}");
			$arr['download_link'] = JHtml::link($url, basename($file['Key']));
			$url = JRoute::_("index.php?option=com_yastorage&amp;task=mkv.delete&amp;bucket={$bucket}&amp;key={$file['Key']}");
			$arr['delete_link'] = JHtml::link($url, JText::sprintf('COM_MKV_ACTION_DELETE'));
			$arr['size'] = JText::sprintf('COM_YASTORAGE_HEAD_OBJECT_SIZE_TEXT_MB', round((float)$file['Size'] / 1024 / 1024, 2));
			$arr['modified'] = JDate::getInstance($file['LastModified']->date)->format("d.m.Y");
			$result[] = $arr;
		}
		return $result;
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
		$arr['scheme_title_ru'] = $data['scheme_title_ru'] ?? null;
		$arr['scheme_title_en'] = $data['scheme_title_en'] ?? null;
		$arr['title_to_diploma'] = $data['title_to_diploma'] ?? null;
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
		$arr['contractID'] = $contractID;
		$arr['invite_date'] = JDate::getInstance(($data['invite_date'] != '0000-00-00 00:00:00') ? $data['invite_date'] : '')->toSql();
		$arr['invite_outgoing_number'] = $data['invite_outgoing_number'];
		$arr['invite_incoming_number'] = $data['invite_incoming_number'];
		return $model->save($arr);
	}

	public function getContractStatus(string $code = ''): string
	{
		if ($code === '') return JText::sprintf('COM_CONTRACTS_CONTRACT_STATUS_IN_PROJECT');
		$table = JTable::getInstance('Statuses', 'TableContracts');
		$table->load(['code' => $code]);
		return $table->title;
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
		$need_push = true;
		foreach ($members as $member) {
			$data['managerID'] = $member;
			$push = [];
			$push['id'] = ContractsHelper::getConfig('notify_new_doc_status_chanel_id');
			$push['key'] = ContractsHelper::getConfig('notify_new_doc_status_chanel_key');
			$push['title'] = JText::sprintf('COM_CONTRACTS_NOTIFY_NEW_DOC_STATUS_TITLE');
			$push['text'] = $data['text'];
			SchedulerHelper::sendNotify($data, (!$need_push) ? [] : $push);
			$need_push = false;
		}
	}

	private function sendNotifyReject(array $contract)
	{
		$groupID = ContractsHelper::getConfig('notify_contract_reject_group');
		if (!is_numeric($groupID)) return;
		$recipients = MkvHelper::getGroupUsers($groupID);
		if (empty($recipients)) return;
		$project = $this->getProject($contract['projectID']);
		foreach ($recipients as $managerID) {
			$data = [
				'user_create' => $contract['managerID'],
				'managerID' => $managerID,
				'contractID' => $contract['id'],
				'text' => JText::sprintf("COM_CONTRACTS_NOTIFY_CONTRACT_REJECT", $project->title),
			];
			SchedulerHelper::sendNotify($data);
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
			$this->option . '.contract', 'contract', array('control' => 'jform', 'load_data' => $loadData)
		);
		$form->addFieldPath(JPATH_ADMINISTRATOR . "/components/com_mkv/models/fields");
		$form->addFieldPath(JPATH_ADMINISTRATOR . "/components/com_prices/models/fields");
		$form->addFieldPath(JPATH_ADMINISTRATOR . "/components/com_prj/models/fields");
		$form->addFieldPath(JPATH_ADMINISTRATOR . "/components/com_companies/models/fields");

		if (!ContractsHelper::canDo('core.show.all')) {
			$form->setFieldAttribute('managerID', 'disabled', true);
			$form->setFieldAttribute('managerID', 'required', false);
			$form->setValue('managerID', 'general', JFactory::getUser()->id);
		}
		if (!ContractsHelper::canDo('core.edit.contract_number')) {
			$form->setFieldAttribute('number', 'disabled', true);
		}

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState($this->option . '.edit.contract.data', array());
		if (empty($data)) {
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

	private function isStandsInContract(int $contractID): bool
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select("ifnull(count(id), 0)")
			->from("#__mkv_contract_stands")
			->where("contractID = {$db->q($contractID)}");
		$result = (int)$db->setQuery($query)->loadResult();
		return ($result > 0);
	}

	private function uploadFiles(int $contractID)
	{
		$files = $_FILES['jform'];
		if (!empty($files['name'])) {
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_yastorage/models", 'YastorageModel');
			$model = JModelLegacy::getInstance('Mkv', 'YastorageModel');
			$key = "contracts/{$contractID}";
			$paths = [];
			foreach ($files['name'] as $file) {
				foreach ($file as $index => $name) {
					$paths[] = $model->upload($bucket = 'mkv-contracts', $key, $files['tmp_name']['file'][$index], $name);
				}
			}
		}
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

	private function getLists(int $contractID)
	{
		$model = ListModel::getInstance('Lists', 'ContractsModel', ['contractID' => $contractID]);
		return $model->getItems();
	}

	private function saveLists(int $contractID, array $lists = [])
	{
		$current = $this->getLists($contractID);
		if (empty($current)) {
			if (empty($lists)) return;
			foreach ($lists as $listID)
				$this->addList($contractID, $listID);
		}
		else {
			foreach ($lists as $item)
				if (($key = array_search($item, $current)) === false)
					$this->addList($contractID, $item);
			foreach ($current as $item)
				if (($key = array_search($item, $lists)) === false)
					$this->deleteList($contractID, $item);
		}
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

	private function addList(int $contractID, int $listID)
	{
		$table = $this->getTable('Lists', 'TableContracts');
		$data = ['id' => null, 'contractID' => $contractID, 'listID' => $listID];
		$table->save($data);
	}

	private function deleteList(int $contractID, int $listID)
	{
		$table = $this->getTable('Lists', 'TableContracts');
		$table->load(['contractID' => $contractID, 'listID' => $listID]);
		$table->delete($table->id);
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
				$hst['section'] = 'contract';
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

	private function canAddItem($status): bool
	{
		switch ($status) {
			case null:
			case 0:
			{
				return false;
			}
			default:
				return true;
		}
	}

	private function canAddStand($status): bool
	{
		switch ($status) {
			case 1:
			case 4:
			case 5:
			case 6:
			case 9:
			case 10:
			{
				return true;
			}
			default:
				return false;
		}
	}

	protected function canEdit(int $managerID)
	{
		if (!ContractsHelper::canDo('core.show.all') && $managerID != JFactory::getUser()->id) jexit('Access denied');
	}

	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		if (!empty($record->id)) {
			return $user->authorise('core.edit.state', $this->option . '.contract.' . (int)$record->id);
		}
		else {
			return parent::canEditState($record);
		}
	}

	public function getScript()
	{
		return 'administrator/components/' . $this->option . '/models/forms/contract.js';
	}
}