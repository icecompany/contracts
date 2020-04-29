<?php
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die;

class ContractsViewStatuses extends HtmlView
{
    protected $sidebar = '';
    public $items, $pagination, $uid, $state, $filterForm, $activeFilters;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Show the toolbar
        $this->toolbar();

        // Show the sidebar
        ContractsHelper::addSubmenu('statuses');
        $this->sidebar = JHtmlSidebar::render();

        // Display it all
        return parent::display($tpl);
    }

    private function toolbar()
    {
        JToolBarHelper::title(JText::sprintf('COM_CONTRACTS_MENU_STATUSES'), 'wrench');

        if (ContractsHelper::canDo('core.create'))
        {
            JToolbarHelper::addNew('status.add');
        }
        if (ContractsHelper::canDo('core.edit'))
        {
            JToolbarHelper::editList('status.edit');
        }
        if (ContractsHelper::canDo('core.delete'))
        {
            JToolbarHelper::deleteList('COM_CONTRACTS_CONFIRM_REMOVE_STATUS', 'status.delete');
        }
        if (ContractsHelper::canDo('core.admin'))
        {
            JToolBarHelper::preferences('com_contracts');
        }
    }
}
