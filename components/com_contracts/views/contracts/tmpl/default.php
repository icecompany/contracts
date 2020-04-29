<?php
/**
 * @package    contracts
 *
 * @author     anton@nazvezde.ru <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\FileLayout;

/** @var ContractsViewContracts $this */

HTMLHelper::_('script', 'com_contracts/script.js', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'com_contracts/style.css', ['version' => 'auto', 'relative' => true]);

$layout       = new FileLayout('contracts.page');
$data         = [];
$data['text'] = 'Hello Joomla!';
echo $layout->render($data);
