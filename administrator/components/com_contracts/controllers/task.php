<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;

class ContractsControllerTask extends FormController {

    public function add()
    {
        $uri = JUri::getInstance();
        $contractID = $uri->getVar('contractID', 0);
        $referer = JUri::getInstance($_SERVER['HTTP_REFERER']);
        if ($referer->getVar('view') === 'contract') {
            $contractID = $referer->getVar('id');
            $this->input->set('return', base64_encode($_SERVER['HTTP_REFERER']));
        }
        if ($contractID > 0) {
            $return = base64_encode($_SERVER['HTTP_REFERER']);
            $this->setRedirect("index.php?option=com_scheduler&task=task.add&contractID={$contractID}&return={$return}");
            $this->redirect();
            jexit();
        }
        return parent::add();
    }

    public function find()
    {
        $uri = JUri::getInstance();
        $contractID = $uri->getVar('contractID', 0);
        $return = base64_encode($_SERVER['HTTP_REFERER']);
        $this->setRedirect("index.php?option=com_scheduler&task=task.gotoContractActiveTask&contractID={$contractID}&return={$return}");
        $this->redirect();
        jexit();
    }

    public function display($cachable = false, $urlparams = array())
    {
        return parent::display($cachable, $urlparams);
    }
}