<?php
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

class ContractsHelper
{
    public function addSubmenu($vName)
    {
        PrjHelper::addNotifies();
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

    /**
     * Возвращает массив с суммами всех сделок по валютам
     * @param bool $by_filters Если true, то значение возвращается с учётом фильтров в модели сделок
     * @return array|int[]
     * @throws Exception
     * @since 2.1.16
     */
    public static function getProjectAmount(bool $by_filters = false): array
    {
        $app = JFactory::getApplication();
        $context = "com_contracts.contracts";
        $result = ['rub' => 0, 'usd' => 0, 'eur' => 0];

        $db = JFactory::getDbo();

        $projectID = PrjHelper::getActiveProject();
        if ($projectID === 0) return $result;

        $query = $db->getQuery(true);
        $query
            ->select("c.currency, ifnull(sum(c.amount), 0) as amount, ifnull(sum(c.payments), 0) as payments, ifnull(sum(if(c.debt < 0, 0, c.debt)), 0) as debt")
            ->from("#__mkv_contracts c")
            ->leftJoin("#__mkv_contract_incoming_info i on i.contractID = c.id")
            ->leftJoin("#__mkv_companies e on e.id = c.companyID")
            ->where("c.projectID = {$db->q($projectID)}")
            ->group("c.currency");
        if (is_numeric($projectID)) {
            $query->where("c.projectID = {$db->q($projectID)}");
        }
        if (!ContractsHelper::canDo('core.project.amount_full')) {
            $userID = JFactory::getUser()->id;
            $query
                ->where("c.managerID = {$db->q($userID)}");
        }
        //Если с учётом фильтров
        if ($by_filters) {
            $search = $app->getUserState("{$context}.filter.search");
            if (!empty($search)) {
                if (stripos($search, 'id:') !== false) { //Поиск по ID
                    $id = explode(':', $search);
                    $id = $id[1];
                    if (is_numeric($id)) {
                        $query->where("c.id = {$db->q($id)}");
                    }
                } else {
                    if (stripos($search, 'num:') !== false || stripos($search, '#') !== false || stripos($search, '№') !== false) { //Поиск по номеру договора
                        $delimiter = ":";
                        if (stripos($search, 'num:') !== false) $delimiter = ":";
                        if (stripos($search, '#') !== false) $delimiter = "#";
                        if (stripos($search, '№') !== false) $delimiter = "№";
                        $num = explode($delimiter, $search);
                        $num = $num[1];
                        if (is_numeric($num)) {
                            $query->where("c.number = {$db->q($num)}");
                        }
                    } else {
                        $text = $db->q("%{$search}%");
                        $query->where("(e.title like {$text} or e.title_full like {$text} or e.title_en like {$text})");
                    }
                }
            }

            $manager = $app->getUserState("{$context}.filter.managerID");
            if (is_numeric($manager) && ContractsHelper::canDo('core.project.amount_full')) {
                $query->where("c.managerID = {$db->q($manager)}");
            }
            
            $status = $app->getUserState("{$context}.filter.status");
            if (is_array($status) && !empty($status)) {
                $statuses = implode(", ", $status);
                if (in_array(101, $status)) {
                    $query->where("(c.status in ({$statuses}) or c.status is null)");
                } else {
                    $query->where("c.status in ({$statuses})");
                }
            }
            
            $catalog_info = $app->getUserState("{$context}.filter.catalog_info");
            if (is_numeric($catalog_info)) {
                $query->where("i.catalog_info = {$db->q($catalog_info)}");
            }
            
            $catalog_logo = $app->getUserState("{$context}.filter.catalog_logo");
            if (is_numeric($catalog_logo)) {
                $query->where("i.catalog_logo = {$db->q($catalog_logo)}");
            }
            
            $doc_status = $app->getUserState("{$context}.filter.doc_status");
            if (is_numeric($doc_status)) {
                $query->where("i.doc_status = {$db->q($doc_status)}");
            }
            
            $title_to_diploma = $app->getUserState("{$context}.filter.title_to_diploma");
            if (is_numeric($title_to_diploma)) {
                if ($title_to_diploma === '0') $query->where("i.title_to_diploma is null");
                if ($title_to_diploma === '1') {
                    $query->where("i.title_to_diploma is not null");
                }
            }
            
            $currency = $app->getUserState("{$context}.filter.currency");
            if (!empty($currency)) {
                $query->where("c.currency like {$db->q($currency)}");
            }
            
            $pvn_1 = $app->getUserState("{$context}.filter.pvn_1");
            $pvn_1a = $app->getUserState("{$context}.filter.pvn_1a");
            $pvn_1b = $app->getUserState("{$context}.filter.pvn_1b");
            $pvn_1v = $app->getUserState("{$context}.filter.pvn_1v");
            $pvn_1g = $app->getUserState("{$context}.filter.pvn_1g");
            $no_exhibit = $app->getUserState("{$context}.filter.no_exhibit");
            if (is_numeric($pvn_1) or is_numeric($pvn_1a) or is_numeric($pvn_1b) or is_numeric($pvn_1v) or is_numeric($pvn_1g) or is_numeric($no_exhibit)) {
                if ($pvn_1 == '0' and $pvn_1a == '0' and $pvn_1b == '0' and $pvn_1g == '0' and $no_exhibit == '0') {
                    $query->where("(i.pvn_1 = 0 and i.pvn_1a = 0 and i.pvn_1b = 0 and i.pvn_1v = 0 and i.pvn_1g = 0 and i.no_exhibit = 0)");
                }
                else {
                    $query->where("(i.pvn_1 = {$db->q($pvn_1)} or i.pvn_1a = {$db->q($pvn_1a)} or i.pvn_1b = {$db->q($pvn_1b)} or i.pvn_1v = {$db->q($pvn_1v)} or i.pvn_1g = {$db->q($pvn_1g)} or i.no_exhibit = {$db->q($no_exhibit)})");
                }
            }

            $thematics = $app->getUserState("{$context}.filter.thematics");
            if (is_numeric($thematics)) {
                $ids = self::getThematicsContracts([$thematics]);
                if (!empty($ids)) {
                    $cid = implode(', ', $ids);
                    $query->where("c.id in ({$cid})");
                }
                else {
                    $query->where("c.id = -1");
                }
            }
        }

        $items = $db->setQuery($query)->loadAssocList('currency');
        if (empty($items)) return $result;
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

    private static function getThematicsContracts(array $thematicIDs = []): array
    {
        if (empty($thematicIDs)) return [];
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $ids = implode(', ', $thematicIDs);
        $query
            ->select("contractID")
            ->from("#__mkv_contract_thematics")
            ->where("thematicID in ({$ids})");
        return $db->setQuery($query)->loadColumn() ?? [];
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
