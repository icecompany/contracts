<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView;

class ContractsViewItem extends HtmlView {
    protected $item, $form, $script;

    public function display($tmp = null) {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->script = $this->get('Script');

        if ($this->item->id !== null) {
            $this->form->setFieldAttribute('value', 'readonly', false);
            switch ($this->item->price_type) {
                case 'square':
                case 'electric':
                case 'internet':
                case 'multimedia':
                case 'water':
                case 'cleaning': {
                    $this->form->setFieldAttribute('contractStandID', 'readonly', false);
                    break;
                }
            }
        }
        else {
            $this->form->removeField('old_amount');
        }

        $this->addToolbar();
        $this->setDocument();

        parent::display($tmp);
    }

    protected function addToolbar() {
	    JToolBarHelper::apply('item.apply', 'JTOOLBAR_APPLY');
        JToolbarHelper::save('item.save', 'JTOOLBAR_SAVE');
        JToolbarHelper::cancel('item.cancel', 'JTOOLBAR_CLOSE');
        JFactory::getApplication()->input->set('hidemainmenu', true);
    }

    protected function setDocument() {
        $title = JText::sprintf(($this->item->id !== null) ? 'COM_CONTRACTS_TITLE_ITEM_EDIT' : 'COM_CONTRACTS_TITLE_ITEM_ADD', $this->item->contract->company, $this->item->contract->project);
        JToolbarHelper::title($title, 'cart');
        JHtml::_('bootstrap.framework');
    }
}