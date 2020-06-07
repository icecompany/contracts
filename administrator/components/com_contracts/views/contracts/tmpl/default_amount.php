<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$colspan = (!is_numeric($this->activeProject)) ? 12 : 11;
?>
<tr>
    <td colspan="<?php echo $colspan;?>" rowspan="3" style="text-align: right;">
        <?php echo JText::sprintf('COM_CONTRACTS_HEAD_TOTAL_AMOUNT_BY_PROJECT'); ?>
    </td>
    <td><?php echo $this->items['amount']['rub']['amount'];?></td>
    <td><?php echo $this->items['amount']['rub']['payments'];?></td>
    <td colspan="2"><?php echo $this->items['amount']['rub']['debt'];?></td>
</tr>
<tr>
    <td><?php echo $this->items['amount']['usd']['amount'];?></td>
    <td><?php echo $this->items['amount']['usd']['payments'];?></td>
    <td colspan="2"><?php echo $this->items['amount']['usd']['debt'];?></td>
</tr>
<tr>
    <td><?php echo $this->items['amount']['eur']['amount'];?></td>
    <td><?php echo $this->items['amount']['eur']['payments'];?></td>
    <td colspan="2"><?php echo $this->items['amount']['eur']['debt'];?></td>
</tr>
