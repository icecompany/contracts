<?php
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die;

class ContractsViewResponsibles extends HtmlView
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

        $this->filterForm->addFieldPath(JPATH_ADMINISTRATOR."/components/com_mkv/models/fields");
        if (!ContractsHelper::canDo('core.show.all')) $this->filterForm->removeField('manager', 'filter');

        // Show the toolbar
        $this->toolbar();

        // Show the sidebar
        ContractsHelper::addSubmenu('responsibles');
        $this->sidebar = JHtmlSidebar::render();

        // Display it all
        return parent::display($tpl);
    }

    private function toolbar()
    {
        JToolBarHelper::title(JText::sprintf('COM_CONTRACTS_MENU_RESPONSIBLES'), 'users');
        JToolbarHelper::custom('responsibles.download', 'download', 'download', JText::sprintf('COM_MKV_BUTTON_EXPORT_TO_EXCEL'), false);

        if (ContractsHelper::canDo('core.admin'))
        {
            JToolBarHelper::preferences('com_contracts');
        }
    }
}
