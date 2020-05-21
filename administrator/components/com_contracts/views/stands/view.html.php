<?php
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die;

class ContractsViewStands extends HtmlView
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
        ContractsHelper::addSubmenu('stands');
        $this->sidebar = JHtmlSidebar::render();

        // Display it all
        return parent::display($tpl);
    }

    private function toolbar()
    {
        JToolBarHelper::title(JText::sprintf('COM_CONTRACTS_MENU_STANDS'), 'equalizer');

        if (ContractsHelper::canDo('core.edit'))
        {
            JToolbarHelper::editList('stand.edit');
        }
        if (ContractsHelper::canDo('core.delete'))
        {
            JToolbarHelper::deleteList('COM_CONTRACTS_CONFIRM_REMOVE_STAND', 'stands.delete');
        }

        JToolbarHelper::custom('stands.download', 'download', 'download', JText::sprintf('COM_MKV_BUTTON_EXPORT_TO_EXCEL'), false);

        if (ContractsHelper::canDo('core.admin'))
        {
            JToolBarHelper::preferences('com_contracts');
        }
    }
}
