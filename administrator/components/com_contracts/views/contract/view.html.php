<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView;

class ContractsViewContract extends HtmlView {
    protected $item, $form, $script, $stands;

    public function display($tmp = null) {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->script = $this->get('Script');
        $this->stands = $this->get('Stands');

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
        JToolbarHelper::cancel('contract.cancel', 'JTOOLBAR_CLOSE');
        JFactory::getApplication()->input->set('hidemainmenu', true);
    }

    protected function setDocument() {
        JToolbarHelper::title($this->item->title, 'briefcase');
        JHtml::_('bootstrap.framework');
    }
}