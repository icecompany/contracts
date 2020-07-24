<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;

class ContractsControllerParent extends FormController {
    public function getModel($name = 'Parent', $prefix = 'ContractsModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function asset()
    {
        $id = $this->input->getInt('id', 0);
        $csid = $this->input->getInt('csid', 0);
        $asset = $this->input->getBool('asset', false);
        if ($id === 0 || $csid === 0) return;
        $model = $this->getModel();
        $item = $model->getItem($id);
        if ($item->id === null) return;
        $data = [];
        $data['id'] = $item->id;
        $data['contractID'] = $item->contractID;
        $data['companyID'] = $item->companyID;
        $data['contractStandID'] = (!$asset) ? '' : $csid;
        $model->save($data);
        exit();
    }
}