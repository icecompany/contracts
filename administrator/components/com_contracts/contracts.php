<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_contracts'))
{
	throw new InvalidArgumentException(JText::sprintf('JERROR_ALERTNOAUTHOR'), 404);
}

// Require the helper
require_once JPATH_ADMINISTRATOR . "/components/com_prj/helpers/prj.php";
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
