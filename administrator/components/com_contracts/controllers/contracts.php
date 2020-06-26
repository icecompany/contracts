<?php
use Joomla\CMS\MVC\Controller\AdminController;

defined('_JEXEC') or die;

class ContractsControllerContracts extends AdminController
{
    public function getModel($name = 'Contract', $prefix = 'ContractsModel', $config = array())
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function setContractNumber()
    {
        $ids = $this->input->get('cid');
        $model = $this->getModel();
        $result = [];
        foreach ($ids as $id) $result[] = $model->setContractNumber($id);
        if (count($result) > 1) {
            $text = JText::sprintf('COM_CONTRACTS_MSG_CONTRACTS_HAVE_NUMBERS', implode(', ', $result));
        }
        else {
            $text = JText::sprintf('COM_CONTRACTS_MSG_CONTRACT_HAVE_NUMBER', implode(', ', $result));
        }
        $this->setRedirect("index.php?option={$this->option}&view=contracts", $text);
        $this->redirect();
        jexit();
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

    public function download(): void
    {
        echo "<script>window.open('index.php?option=com_contracts&task=contracts.execute&format=xls');</script>";
        echo "<script>location.href='{$_SERVER['HTTP_REFERER']}'</script>";
        jexit();
    }
}
