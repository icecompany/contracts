<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;

class ContractsControllerItem extends FormController {
    public function add()
    {
        $uri = JUri::getInstance($_SERVER['HTTP_REFERER']);
        $contractID = $uri->getVar('contractID', 0);
        if ($contractID > 0) JFactory::getApplication()->setUserState($this->option . '.item.contractID', $contractID);
        return parent::add();
    }

    public function display($cachable = false, $urlparams = array())
    {
        return parent::display($cachable, $urlparams);
    }
}