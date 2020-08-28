<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$colspan = ($this->contractID > 0) ? '13' : '15';
$currency = $this->items['currency'];
if ($this->contractID > 0): ?>
    <tr>
        <td colspan="8" style="text-align: right; font-weight: bold;"><?php echo JText::sprintf('COM_CONTRACTS_HEAD_CONTRACTS_SUM_IN_CONTRACT');?></td>
        <td><?php echo $this->items['values'];?></td>
        <td colspan="2"><?php echo JText::sprintf('COM_CONTRACTS_CURRENCY_RUB_AMOUNT_SHORT', number_format($this->items['amount'][$currency], 2, '.', ' '));?></td>
    </tr>
<?php endif;?>
<tr>
    <td colspan="<?php echo $colspan;?>" class="pagination-centered"><?php echo $this->pagination->getListFooter(); ?></td>
</tr>