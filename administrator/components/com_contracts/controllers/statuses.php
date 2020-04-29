<?php
use Joomla\CMS\MVC\Controller\AdminController;

defined('_JEXEC') or die;

class ContractsControllerStatuses extends AdminController
{
    public function getModel($name = 'Status', $prefix = 'ContractsModel', $config = array())
    {
        return parent::getModel($name, $prefix, $config);
    }
}
