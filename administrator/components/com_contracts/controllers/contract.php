<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;

class ContractsControllerContract extends FormController {
    public function add()
    {
        $uri = JUri::getInstance();
        $projectID = PrjHelper::getActiveProject();
        if ($projectID > 0) JFactory::getApplication()->setUserState($this->option . '.contract.projectID', $projectID);
        $companyID = $uri->getVar('companyID', 0);
        if ($companyID > 0) JFactory::getApplication()->setUserState($this->option . '.contract.companyID', $companyID);
        return parent::add();
    }

    public function display($cachable = false, $urlparams = array())
    {
        return parent::display($cachable, $urlparams);
    }
}