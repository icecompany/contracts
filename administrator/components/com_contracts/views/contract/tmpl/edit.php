<?php
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('script', $this->script);
HTMLHelper::_('script', 'com_contracts/contract.js', array('version' => 'auto', 'relative' => true));
?>
<form action="<?php echo ContractsHelper::getActionUrl(); ?>"
      method="post" name="adminForm" id="adminForm" xmlns="http://www.w3.org/1999/html" class="form-validate">
    <div class="row-fluid">
        <div class="span12 form-horizontal">
            <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general', 'useCookie' => true)); ?>
            <div class="tab-content">
                <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::sprintf('COM_CONTRACTS_TITLE_TAB_GENERAL')); ?>
                <div class="row-fluid">
                    <div class="span4">
                        <div><?php echo $this->loadTemplate('general'); ?></div>
                    </div>
                    <div class="span4">

                    </div>
                    <div class="span4">

                    </div>
                </div>
                <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php if ($this->item->id !== null): ?>
                    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'items', JText::sprintf('COM_CONTRACTS_TITLE_TAB_ITEMS')); ?>
                    <div><?php echo $this->loadTemplate('items'); ?></div>
                    <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php endif;?>
                <?php if ($this->item->id !== null): ?>
                    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'stands', JText::sprintf('COM_CONTRACTS_TITLE_TAB_STANDS')); ?>
                    <div><?php echo $this->loadTemplate('stands'); ?></div>
                    <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php endif;?>
                <?php if ($this->item->id !== null): ?>
                    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'forms', JText::sprintf('COM_CONTRACTS_TITLE_TAB_FORM_TO_CATALOG')); ?>
                    <div><?php echo $this->loadTemplate('forms'); ?></div>
                    <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php endif;?>
            </div>
            <?php echo JHtml::_('bootstrap.endTabSet'); ?>
        </div>
        <div>
            <input type="hidden" name="task" value=""/>
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </div>
</form>

