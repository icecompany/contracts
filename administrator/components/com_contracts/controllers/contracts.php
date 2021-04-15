<?php
use Joomla\CMS\MVC\Controller\AdminController;

defined('_JEXEC') or die;

class ContractsControllerContracts extends AdminController
{
    public function getModel($name = 'Contract', $prefix = 'ContractsModel', $config = array()): ContractsModelContract
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function assign_to_me()
    {
        $ids = $this->input->get('cid');
        $model = $this->getModel();
        $userID = JFactory::getUser()->id;
        foreach ($ids as $id) {
            $table = $model->getTable();
            $table->load($id);
            $table->save(['id' => $id, 'managerID' => $userID]);
            SchedulerHelper::updateTaskManager($id, $userID);
        }
        $this->setRedirect("index.php?option={$this->option}&view=contracts", JText::sprintf('COM_CONTRACTS_MSG_CONTRACTS_IS_ASSIGNED_TO_ME'));
        $this->redirect();
        jexit();
    }
}
