<?php
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

use Joomla\CMS\HTML\HTMLHelper;
HTMLHelper::_('script', 'https://code.jquery.com/ui/1.11.4/jquery-ui.min.js', array('relative' => true));

HTMLHelper::_('script', $this->script);
?>
<script>
    let currency = '<?php echo $this->item->contract->currency;?>';
    let old_price_value = parseFloat('<?php echo $this->item->amount ?? 0;?>');
    let old_amount = parseFloat('<?php echo $this->item->contract->amount;?>');
</script>
<form action="<?php echo ContractsHelper::getActionUrl(); ?>"
      method="post" name="adminForm" id="adminForm" xmlns="http://www.w3.org/1999/html" class="form-validate">
    <div class="row-fluid">
        <div class="span12 form-horizontal">
            <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general', 'useCookie' => true)); ?>
            <div class="tab-content">
                <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::sprintf('COM_CONTRACTS_TITLE_TAB_GENERAL')); ?>
                <div class="row-fluid">
                    <div class="span6">
                        <div class="center"><h3><?php echo JText::sprintf('COM_CONTRACTS_TITLE_CONTRACT_ITEM');?></h3></div>
                        <div><?php echo $this->loadTemplate('general'); ?></div>
                        <div class="center"><h3><?php echo JText::sprintf('COM_CONTRACTS_TITLE_CONTRACT_PERIOD');?></h3></div>
                        <div><?php echo $this->loadTemplate('period'); ?></div>
                        <div class="center"><h3><?php echo JText::sprintf('COM_CONTRACTS_TITLE_CONTRACT_PAYER');?></h3></div>
                        <div><?php echo $this->loadTemplate('payer'); ?></div>
                    </div>
                    <div class="span6">
                        <div class="center"><h3><?php echo JText::sprintf('COM_CONTRACTS_TITLE_CONTRACT_COST');?></h3></div>
                        <div><?php echo $this->loadTemplate('price'); ?></div>
                        <div class="center"><h3><?php echo JText::sprintf('COM_CONTRACTS_TITLE_CONTRACT_AMOUNT');?></h3></div>
                        <div><?php echo $this->loadTemplate('contract_amount'); ?></div>
                        <div class="center"><h3><?php echo JText::sprintf('COM_CONTRACTS_TITLE_CONTRACT_DESCRIPTION');?></h3></div>
                        <div><?php echo $this->loadTemplate('description'); ?></div>
                    </div>
                </div>
                <?php echo JHtml::_('bootstrap.endTab'); ?>
            </div>
            <?php echo JHtml::_('bootstrap.endTabSet'); ?>
        </div>
        <div>
            <input type="hidden" name="task" value=""/>
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </div>
</form>

