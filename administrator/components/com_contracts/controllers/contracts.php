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
}
