<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;

class ContractsControllerScore extends FormController {

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
            $this->setRedirect("index.php?option=com_finances&task=score.add&contractID={$contractID}&return={$return}");
            $this->redirect();
            jexit();
        }
        return parent::add();
    }

    public function display($cachable = false, $urlparams = array())
    {
        return parent::display($cachable, $urlparams);
    }
}