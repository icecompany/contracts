<?php
use Joomla\CMS\MVC\Controller\AdminController;

defined('_JEXEC') or die;

class ContractsControllerResponsibles extends AdminController
{
    public function getModel($name = 'Responsibles', $prefix = 'ContractsModel', $config = array())
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function download(): void
    {
        echo "<script>window.open('index.php?option=com_contracts&task=responsibles.execute&format=xls');</script>";
        echo "<script>location.href='{$_SERVER['HTTP_REFERER']}'</script>";
        jexit();
    }
}
