<?php
use Joomla\CMS\MVC\Controller\AdminController;

defined('_JEXEC') or die;

class ContractsControllerResponsibles extends AdminController
{
    public function getModel($name = 'Responsibles', $prefix = 'ContractsModel', $config = array())
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function execute($task): void
    {
        $model = $this->getModel();
        $model->export();
        jexit();
    }

}
