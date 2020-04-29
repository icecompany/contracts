<?php
use Joomla\CMS\MVC\Controller\AdminController;

defined('_JEXEC') or die;

class ContractsControllerContracts extends AdminController
{
    public function getModel($name = 'Contract', $prefix = 'ContractsModel', $config = array())
    {
        return parent::getModel($name, $prefix, $config);
    }
}
