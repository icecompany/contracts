<?php
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die;

class ContractsViewContracts extends HtmlView
{
    protected $sidebar = '';
    public $items, $pagination, $uid, $state, $filterForm, $activeFilters, $activeProject, $show_by_status;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->activeProject = PrjHelper::getActiveProject();
        $this->pagination = $this->get('Pagination');
        $this->show_by_status = true;

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
        JToolBarHelper::title(JText::sprintf('COM_CONTRACTS_MENU_CONTRACTS'), 'briefcase');

        if (ContractsHelper::canDo('core.edit'))
        {
            JToolbarHelper::editList('contract.edit');
        }
        if (ContractsHelper::canDo('core.delete'))
        {
            JToolbarHelper::deleteList('COM_CONTRACTS_CONFIRM_REMOVE_CONTRACT', 'contracts.delete');
        }
        JToolbarHelper::custom('contracts.assign_to_me', 'signup', 'signup', JText::sprintf('COM_CONTRACTS_BUTTON_ASSIGN_CONTRACTS_TO_ME'));
        JToolbarHelper::custom('contracts.download', 'download', 'download', JText::sprintf('COM_MKV_BUTTON_EXPORT_TO_EXCEL'), false);
        if (ContractsHelper::canDo('core.admin'))
        {
            JToolBarHelper::preferences('com_contracts');
        }
    }
}
