<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;

class ContractsControllerItem extends FormController {
    public function add()
    {
        $uri = JUri::getInstance($_SERVER['HTTP_REFERER']);
        $contractID = $uri->getVar('contractID', 0);
        $referer = JUri::getInstance($_SERVER['HTTP_REFERER']);
        if ($referer->getVar('view') === 'contract') {
            $contractID = $referer->getVar('id');
            $this->input->set('return', base64_encode($_SERVER['HTTP_REFERER']));
        }
        if ($contractID > 0) JFactory::getApplication()->setUserState($this->option . '.item.contractID', $contractID);
        return parent::add();
    }

    public function edit($key = null, $urlVar = null)
    {
        $uri = JUri::getInstance();
        $id = $uri->getVar('id', 0);
        if ($id > 0) {
            $model = $this->getModel();
            $item = $model->getItem($id);
            JFactory::getApplication()->setUserState($this->option . '.item.contractID', $item->contractID);
        }
        return parent::edit($key, $urlVar);
    }

    public function getModel($name = 'Item', $prefix = 'ContractsModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function display($cachable = false, $urlparams = array())
    {
        return parent::display($cachable, $urlparams);
    }

    public function __construct($config = array())
    {
        $this->registerTask('save2new', 'save');
        parent::__construct($config);
    }
}