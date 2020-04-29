<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView;

class ContractsViewStatus extends HtmlView {
    protected $item, $form, $script;

    public function display($tmp = null) {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->script = $this->get('Script');

        $this->addToolbar();
        $this->setDocument();

        parent::display($tmp);
    }

    protected function addToolbar() {
	    JToolBarHelper::apply('status.apply', 'JTOOLBAR_APPLY');
        JToolbarHelper::save('status.save', 'JTOOLBAR_SAVE');
        JToolbarHelper::cancel('status.cancel', 'JTOOLBAR_CLOSE');
        JFactory::getApplication()->input->set('hidemainmenu', true);
    }

    protected function setDocument() {
        $title = ($this->item->id !== null) ? JText::sprintf('COM_CONTRACTS_TITLE_STATUS_EDIT', $this->item->title) : JText::sprintf('COM_CONTRACTS_TITLE_STATUS_ADD');
        JToolbarHelper::title($title, 'wrench');
        JHtml::_('bootstrap.framework');
    }
}