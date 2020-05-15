<?php
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

class ContractsHelper
{
    public function addSubmenu($vName)
    {
        HTMLHelper::_('sidebar.addEntry', JText::sprintf('COM_CONTRACTS_MENU_CONTRACTS'), 'index.php?option=com_contracts&view=contracts', $vName === 'contracts');
        HTMLHelper::_('sidebar.addEntry', JText::sprintf('COM_CONTRACTS_MENU_ITEMS'), 'index.php?option=com_contracts&view=items', $vName === 'items');
        if (ContractsHelper::canDo('core.access.statuses')) {
            HTMLHelper::_('sidebar.addEntry', JText::sprintf('COM_CONTRACTS_MENU_STATUSES'), 'index.php?option=com_contracts&view=statuses', $vName === 'statuses');
        }
        JHtmlSidebar::addFilter(JText::sprintf("COM_CONTRACTS_FILTER_SELECT_ACTIVE_PROJECT"), "set_active_project", JHtml::_("select.options", PrjHelper::getAvailableProjects(), "value", "text", PrjHelper::getActiveProject()));
    }

    /**
     * Проверяет необходимость перезагрузить страницу. Используется для возврата на предыдущую страницу при отправке формы в админке
     * @throws Exception
     * @since 1.0.4
     */
    public static function check_refresh(): void
    {
        $refresh = JFactory::getApplication()->input->getBool('refresh', false);
        if ($refresh) {
            $current = JUri::getInstance(self::getCurrentUrl());
            $current->delVar('refresh');
            JFactory::getApplication()->redirect($current);
        }
    }

    /**
     * Возвращает параметр ID из реферера
     * @since 1.0.1
     * @return int ID Элемента
     */
    public static function getItemID(): int
    {
        $uri = JUri::getInstance($_SERVER['HTTP_REFERER']);
        return (int) $uri->getVar('id') ?? 0;
    }

    /**
     * Возвращает URL для обработки формы
     * @return string
     * @since 1.0.0
     * @throws
     */
    public static function getActionUrl(): string
    {
        $uri = JUri::getInstance();
        $uri->setVar('refresh', '1');
        $input = JFactory::getApplication()->input;
        $view = $input->getString('view');
        $contractID = $input->getInt('contractID', 0);
        if ($view === 'items' && $contractID > 0) {
            $uri->setVar('return', self::getReturnUrl());
        }
        $query = $uri->getQuery();
        $client = (!JFactory::getApplication()->isClient('administrator')) ? 'site' : 'administrator';
        return JRoute::link($client, "index.php?{$query}");
    }

    /**
     * Возвращает текущий URL
     * @return string
     * @since 1.0.0
     * @throws
     */
    public static function getCurrentUrl(): string
    {
        $uri = JUri::getInstance();
        $query = $uri->getQuery();
        return "index.php?{$query}";
    }

    /**
     * Возвращает URL для возврата (текущий адрес страницы)
     * @return string
     * @since 1.0.0
     */
    public static function getReturnUrl(): string
    {
        $uri = JUri::getInstance();
        $query = $uri->getQuery();
        return base64_encode("index.php?{$query}");
    }

    /**
     * Возвращает URL для обработки формы левой панели
     * @return string
     * @since 1.0.0
     */
    public static function getSidebarAction():string
    {
        $return = self::getReturnUrl();
        return JRoute::_("index.php?return={$return}");
    }

    public static function canDo(string $action): bool
    {
        return JFactory::getUser()->authorise($action, 'com_contracts');
    }

    public static function getConfig(string $param, $default = null)
    {
        $config = JComponentHelper::getParams("com_contracts");
        return $config->get($param, $default);
    }
}
