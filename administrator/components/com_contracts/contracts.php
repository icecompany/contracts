<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_contracts'))
{
	throw new InvalidArgumentException(JText::sprintf('JERROR_ALERTNOAUTHOR'), 404);
}

// Require the helper
JFactory::getLanguage()->load('com_mkv', JPATH_ADMINISTRATOR . "/components/com_mkv", 'ru-RU', true);
JFactory::getLanguage()->load('com_prices', JPATH_ADMINISTRATOR . "/components/com_prices", 'ru-RU', true);
require_once JPATH_ADMINISTRATOR . "/components/com_mkv/helpers/mkv.php";
require_once JPATH_ADMINISTRATOR . "/components/com_prj/helpers/prj.php";
require_once JPATH_ADMINISTRATOR . "/components/com_finances/helpers/finances.php";
require_once JPATH_ADMINISTRATOR . "/components/com_scheduler/helpers/scheduler.php";
require_once JPATH_ADMINISTRATOR . "/components/com_finances/helpers/finances.php";
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/contracts.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/passwd.php';
$db = JFactory::getDbo();
$passwd = $db->q($credentials->password);
$db->setQuery("SELECT @pass:={$passwd}")->execute();
$db->setQuery("set @TRIGGER_CHECKS=true")->execute();


// Execute the task
$controller = BaseController::getInstance('contracts');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
