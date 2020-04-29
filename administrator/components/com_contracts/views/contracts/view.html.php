<?php
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die;

class ContractsViewContracts extends HtmlView
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
        ContractsHelper::addSubmenu('contracts');
        $this->sidebar = JHtmlSidebar::render();

        // Display it all
        return parent::display($tpl);
    }

    private function toolbar()
    {
        JToolBarHelper::title(JText::sprintf('COM_CONTRACTS_MENU_CONTRACTS'), 'contracts');

        if (ContractsHelper::canDo('core.create'))
        {
            JToolbarHelper::addNew('contract.add');
        }
        if (ContractsHelper::canDo('core.edit'))
        {
            JToolbarHelper::editList('contract.edit');
        }
        if (ContractsHelper::canDo('core.delete'))
        {
            JToolbarHelper::deleteList('COM_CONTRACTS_CONFIRM_REMOVE_CONTRACT', 'contracts.delete');
        }
        if (ContractsHelper::canDo('core.admin'))
        {
            JToolBarHelper::preferences('com_contracts');
        }
    }
}
