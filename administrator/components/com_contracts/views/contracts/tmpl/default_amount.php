<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$colspan = (!is_numeric($this->activeProject)) ? 14 : 13;
?>
<?php if ($this->show_by_status): ?>
<tr>
    <td colspan="<?php echo $colspan;?>" rowspan="3" style="text-align: right;">
        <?php echo JText::sprintf('COM_CONTRACTS_HEAD_TOTAL_AMOUNT_BY_PROJECT_BY_STATUSES'); ?>
    </td>
    <td><?php echo $this->items['amount_by_status']['rub']['amount'];?></td>
    <td><?php echo $this->items['amount_by_status']['rub']['payments'];?></td>
    <td colspan="2"><?php echo $this->items['amount_by_status']['rub']['debt'];?></td>
</tr>
<tr>
    <td><?php echo $this->items['amount_by_status']['usd']['amount'];?></td>
    <td><?php echo $this->items['amount_by_status']['usd']['payments'];?></td>
    <td colspan="2"><?php echo $this->items['amount_by_status']['usd']['debt'];?></td>
</tr>
<tr>
    <td><?php echo $this->items['amount_by_status']['eur']['amount'];?></td>
    <td><?php echo $this->items['amount_by_status']['eur']['payments'];?></td>
    <td colspan="2"><?php echo $this->items['amount_by_status']['eur']['debt'];?></td>
</tr>
<?php endif;?>
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
