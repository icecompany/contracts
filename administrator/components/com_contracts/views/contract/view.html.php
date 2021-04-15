<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView;

class ContractsViewContract extends HtmlView {
    protected $item, $form, $script, $stands, $contractItems, $children, $tasks, $payments, $files;

    public function display($tmp = null) {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->script = $this->get('Script');
        $this->children = $this->get('Children');
        $this->stands = $this->get('Stands');
        $this->tasks = $this->get('Tasks');
        $this->payments = $this->get('Payments');
        $this->contractItems = $this->get('ContractItems');
        $this->files = $this->get('Files');

        if ($this->item->id === null) {
            $this->form->setFieldAttribute('dat', 'readonly', true);
        }
        if ($this->item->is_archive) $this->form->setFieldAttribute('projectID', 'readonly', true);

        $this->addToolbar();
        $this->setDocument();

        parent::display($tmp);
    }

    protected function addToolbar() {
	    if ($this->item->id === null) {
            JToolBarHelper::apply('contract.apply');
            JToolbarHelper::save('contract.save');
        }
        if ($this->item->id !== null) {
            if ($this->item->managerID == JFactory::getUser()->id || ($this->item->managerID != JFactory::getUser()->id && ContractsHelper::canDo('core.edit.all'))) {
                if (($this->item->is_archive && (ContractsHelper::canDo('core.access.archive'))) || !$this->item->is_archive) {
                    JToolBarHelper::apply('contract.apply');
                    JToolbarHelper::save('contract.save');
                    if ($this->item->canAddItem) JToolbarHelper::custom('item.add', 'cart', 'cart', JText::sprintf('COM_MKV_BUTTON_ADD_PRICE_ITEM'), false);
                    if ($this->item->canAddStand) JToolbarHelper::custom('stand.add', 'cube', 'cube', JText::sprintf('COM_MKV_BUTTON_ADD_STAND'), false);
                }
            }
            JToolbarHelper::custom('task.add', 'calendar', 'calendar', JText::sprintf('COM_MKV_BUTTON_ADD_TASK'), false);
            if (FinancesHelper::canDo('core.create') && $this->item->debt > 0) {
                JToolbarHelper::custom('score.add', 'credit', 'credit', JText::sprintf('COM_CONTRACTS_BUTTON_ADD_SCORE'), false);
            }
            JToolbarHelper::custom('contract.go_to_company', 'vcard', 'vcard', JText::sprintf('COM_CONTRACTS_BUTTON_GO_TO_COMPANY'), false);
        }
        JToolbarHelper::cancel('contract.cancel', 'JTOOLBAR_CLOSE');
        JFactory::getApplication()->input->set('hidemainmenu', true);
    }

    protected function setDocument() {
        JToolbarHelper::title($this->item->title, 'briefcase');
        JHtml::_('bootstrap.framework');
    }
}