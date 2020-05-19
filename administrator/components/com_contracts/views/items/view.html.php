<?php
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die;

class ContractsViewItems extends HtmlView
{
    protected $sidebar = '';
    public $items, $pagination, $uid, $state, $filterForm, $activeFilters, $contractID;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->contractID = $this->get('contractID');

        // Show the toolbar
        $this->toolbar();

        // Show the sidebar
        ContractsHelper::addSubmenu('items');
        $this->sidebar = JHtmlSidebar::render();

        // Display it all
        return parent::display($tpl);
    }

    private function toolbar()
    {
        $title = (!empty($this->items['company'])) ? JText::sprintf('COM_CONTRACTS_TITLE_ITEMS_FOR_COMPANY_BY_PROJECT', $this->items['company'], $this->items['project']) : JText::sprintf('COM_CONTRACTS_MENU_ITEMS');
        JToolBarHelper::title($title, 'cart');

        if (ContractsHelper::canDo('core.create'))
        {
            JToolbarHelper::addNew('item.add');
        }
        if (ContractsHelper::canDo('core.edit'))
        {
            JToolbarHelper::editList('item.edit');
        }
        if (ContractsHelper::canDo('core.delete'))
        {
            JToolbarHelper::deleteList('COM_CONTRACTS_CONFIRM_REMOVE_ITEM', 'item.delete');
        }
        if (ContractsHelper::canDo('core.admin'))
        {
            JToolBarHelper::preferences('com_contracts');
        }
    }
}
