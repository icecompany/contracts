<?php
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die;

class ContractsViewItems extends HtmlView
{
    protected $sidebar = '';
    public $items, $pagination, $uid, $state, $filterForm, $activeFilters, $contractID, $itemID;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->contractID = $this->get('contractID');
        $this->itemID = $this->get('itemID');
        if ($this->contractID > 0) {
            $this->filterForm->removeField('currency', 'filter');
            $this->filterForm->removeField('manager', 'filter');
        }
        else {
            $this->filterForm->addFieldPath(JPATH_ADMINISTRATOR . "/components/com_mkv/models/fields");
        }

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

        if ($this->itemID > 0) $title = $this->get('ItemTitle');

        JToolBarHelper::title($title, 'cart');

        if (ContractsHelper::canDo('core.create') && $this->contractID > 0)
        {
            JToolbarHelper::addNew('item.add');
        }
        if (ContractsHelper::canDo('core.edit'))
        {
            JToolbarHelper::editList('item.edit');
        }
        if (ContractsHelper::canDo('core.delete'))
        {
            JToolbarHelper::deleteList('COM_CONTRACTS_CONFIRM_REMOVE_ITEM', 'items.delete');
        }
        JToolbarHelper::custom('items.download', 'download', 'download', JText::sprintf('COM_MKV_BUTTON_EXPORT_TO_EXCEL'), false);
        if (ContractsHelper::canDo('core.admin'))
        {
            JToolBarHelper::preferences('com_contracts');
        }
    }
}
