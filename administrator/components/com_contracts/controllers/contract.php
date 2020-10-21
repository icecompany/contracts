<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;

class ContractsControllerContract extends FormController {
    public function add()
    {
        $uri = JUri::getInstance();
        $companyID = $uri->getVar('companyID', 0);
        $projectID = $uri->getVar('projectID', 0);
        if ($companyID > 0) JFactory::getApplication()->setUserState($this->option . '.contract.companyID', $companyID);
        if ($projectID > 0) JFactory::getApplication()->setUserState($this->option . '.contract.projectID', $projectID);
        return parent::add();
    }

    public function go_to_company()
    {
        $referer = JUri::getInstance($_SERVER['HTTP_REFERER']);
        $view = $referer->getVar('view');
        $contractID = $referer->getVar('id');
        $model = parent::getModel('Contract', 'ContractsModel');
        $item = $model->getItem($contractID);
        if ($view === 'contract' && $item->companyID > 0) {
            $query = [
                'option' => 'com_companies',
                'task' => 'company.edit',
                'id' => $item->companyID,
                'return' => base64_encode($referer->toString())
            ];
            $this->setRedirect("index.php?" . http_build_query($query));
            $this->redirect();
            jexit();
        }
    }

    public function display($cachable = false, $urlparams = array())
    {
        return parent::display($cachable, $urlparams);
    }
}