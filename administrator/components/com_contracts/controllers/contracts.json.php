<?php
defined('_JEXEC') or die;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\Controller\BaseController;

class ContractsControllerContracts extends BaseController
{
    public function getModel($name = 'Contracts', $prefix = 'ContractsModel', $config = array())
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function execute($task)
    {
        $items = $this->getModel()->getItems();
        echo new JsonResponse($items['items']);
    }
}