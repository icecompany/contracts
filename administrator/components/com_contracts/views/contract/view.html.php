<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView;

class ContractsViewContract extends HtmlView {
    protected $item, $form, $script, $stands, $contractItems, $children;

    public function display($tmp = null) {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->script = $this->get('Script');
        $this->children = $this->get('Children');
        $this->stands = $this->get('Stands');
        $this->contractItems = $this->get('ContractItems');

        if ($this->item->id === null) {
            $this->form->setFieldAttribute('dat', 'readonly', true);
        }

        $this->addToolbar();
        $this->setDocument();

        parent::display($tmp);
    }

    protected function addToolbar() {
	    JToolBarHelper::apply('contract.apply', 'JTOOLBAR_APPLY');
        JToolbarHelper::save('contract.save', 'JTOOLBAR_SAVE');
        if ($this->item->id !== null) {
            JToolbarHelper::custom('item.add', 'cart', 'cart', JText::sprintf('COM_MKV_BUTTON_ADD_PRICE_ITEM'), false);
            JToolbarHelper::custom('stand.add', 'cube', 'cube', JText::sprintf('COM_MKV_BUTTON_ADD_STAND'), false);
        }
        JToolbarHelper::cancel('contract.cancel', 'JTOOLBAR_CLOSE');
        JFactory::getApplication()->input->set('hidemainmenu', true);
    }

    protected function setDocument() {
        JToolbarHelper::title($this->item->title, 'briefcase');
        JHtml::_('bootstrap.framework');
    }
}