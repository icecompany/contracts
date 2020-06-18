<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView;

class ContractsViewStand extends HtmlView {
    protected $item, $form, $script, $standItems;

    public function display($tmp = null) {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->script = $this->get('Script');
        if ($this->item->id !== null) {
            $this->standItems = $this->get('standItems');
        }

        $this->addToolbar();
        $this->setDocument();

        parent::display($tmp);
    }

    protected function addToolbar() {
	    JToolBarHelper::apply('stand.apply', 'JTOOLBAR_APPLY');
        JToolbarHelper::save('stand.save', 'JTOOLBAR_SAVE');
        JToolbarHelper::cancel('stand.cancel', 'JTOOLBAR_CLOSE');
        JFactory::getApplication()->input->set('hidemainmenu', true);
    }

    protected function setDocument() {
        JToolbarHelper::title($this->item->title, 'cube');
        JHtml::_('bootstrap.framework');
    }
}