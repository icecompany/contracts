<?php
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

class ContractsHelper
{
    public function addSubmenu($vName)
    {
        HTMLHelper::_('sidebar.addEntry', JText::sprintf('COM_CONTRACTS_MENU_CONTRACTS'), 'index.php?option=com_contracts&view=contracts', $vName === 'contracts');
        HTMLHelper::_('sidebar.addEntry', JText::sprintf('COM_CONTRACTS_MENU_ITEMS'), 'index.php?option=com_contracts&view=items', $vName === 'items');
        HTMLHelper::_('sidebar.addEntry', JText::sprintf('COM_CONTRACTS_MENU_STANDS'), 'index.php?option=com_contracts&view=stands', $vName === 'stands');
        HTMLHelper::_('sidebar.addEntry', JText::sprintf('COM_CONTRACTS_MENU_RESPONSIBLES'), 'index.php?option=com_contracts&view=responsibles', $vName === 'responsibles');

        if (ContractsHelper::canDo('core.access.statuses')) {
            HTMLHelper::_('sidebar.addEntry', JText::sprintf('COM_CONTRACTS_MENU_STATUSES'), 'index.php?option=com_contracts&view=statuses', $vName === 'statuses');
        }
        PrjHelper::addActiveProjectFilter();
    }

    public static function setZeroAmount(int $contractID): void
    {
        $db = JFactory::getDbo();
        $db->setQuery("set @is_zero := 1")->execute();
        $query = $db->getQuery(true);
        $query
            ->update("#__mkv_contract_items")
            ->set("amount = 0")
            ->where("contractID = {$db->q($contractID)}");
        $db->setQuery($query)->execute();
        $db->setQuery("set @is_zero := 0")->execute();
    }

    public static function getProjectAmount(int $projectID = 0, $status = []): array
    {
        $result = ['rub' => 0, 'usd' => 0, 'eur' => 0];
        if ($projectID === 0) return $result;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("currency, ifnull(sum(amount), 0) as amount, ifnull(sum(payments), 0) as payments, ifnull(sum(if(debt < 0, 0, debt)), 0) as debt")
            ->from("#__mkv_contracts")
            ->where("projectID = {$db->q($projectID)}")
            ->group("currency");
        if (is_array($status) && !empty($status)) {
            $statuses = implode(", ", $status);
            if (!in_array(100, $status)) {
                if (in_array(101, $status)) {
                    $query->where("(status in ({$statuses}) or status is null)");
                } else {
                    $query->where("status in ({$statuses})");
                }
            }
        }
        $items = $db->setQuery($query)->loadAssocList('currency');
        foreach ($items as $currency => $arr) {
            foreach ($arr as $type => $amount) {
                if ($type === 'currency') {
                    unset($items[$currency][$type]);
                    continue;
                }
                $c = mb_strtoupper($currency);
                $items[$currency][$type] = JText::sprintf("COM_MKV_AMOUNT_{$c}_SHORT", number_format((float) $amount, 2, '.', ' '));
            }
        }
        return $items;
    }

    public static function getNextContractNumber(int $projectID)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("ifnull(max(number), 0) + 1")
            ->from("#__mkv_contracts")
            ->where("projectID = {$db->q($projectID)}");
        return $db->setQuery($query)->loadResult();
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
