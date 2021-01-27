<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_contracts'))
{
	throw new InvalidArgumentException(JText::sprintf('JERROR_ALERTNOAUTHOR'), 404);
}

// Require the helper
require_once JPATH_LIBRARIES . '/AWS/aws-autoloader.php';
JFactory::getLanguage()->load('com_mkv', JPATH_ADMINISTRATOR . "/components/com_mkv", 'ru-RU', true);
JFactory::getLanguage()->load('com_prj', JPATH_ADMINISTRATOR . "/components/com_prj", 'ru-RU', true);
JFactory::getLanguage()->load('com_scheduler', JPATH_ADMINISTRATOR . "/components/com_scheduler", 'ru-RU', true);
JFactory::getLanguage()->load('com_prices', JPATH_ADMINISTRATOR . "/components/com_prices", 'ru-RU', true);
JFactory::getLanguage()->load('com_yastorage', JPATH_ADMINISTRATOR . "/components/com_yastorage", 'ru-RU', true);
require_once JPATH_ADMINISTRATOR . "/components/com_companies/helpers/companies.php";
require_once JPATH_ADMINISTRATOR . "/components/com_mkv/helpers/mkv.php";
require_once JPATH_ADMINISTRATOR . "/components/com_prj/helpers/prj.php";
require_once JPATH_ADMINISTRATOR . "/components/com_finances/helpers/finances.php";
require_once JPATH_ADMINISTRATOR . "/components/com_scheduler/helpers/scheduler.php";
require_once JPATH_ADMINISTRATOR . "/components/com_finances/helpers/finances.php";
require_once JPATH_ADMINISTRATOR . "/components/com_prices/helpers/prices.php";
require_once JPATH_ADMINISTRATOR . "/components/com_yastorage/helpers/yastorage.php";
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/contracts.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/passwd.php';
$db = JFactory::getDbo();
$passwd = $db->q($credentials->password);
$db->setQuery("SELECT @pass:={$passwd}")->execute();

// Execute the task
$controller = BaseController::getInstance('contracts');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
