<?php
defined('_JEXEC') or die;
$ii = 0;
?>
<?php if (!empty($this->contractItems['items'])): ?>
    <div>
        <table class="table table-stripped">
            <thead>
            <tr>
                <th style="width: 1%;">№</th>
                <th><?php echo JText::sprintf('COM_CONTRACTS_HEAD_ITEMS_ITEM'); ?></th>
                <th style="width: 5%;"><?php echo JText::sprintf('COM_CONTRACTS_HEAD_ITEMS_STAND'); ?></th>
                <th style="width: 10%;"><?php echo JText::sprintf('COM_CONTRACTS_HEAD_ITEMS_COST'); ?></th>
                <th style="width: 2%;"><?php echo JText::sprintf('COM_CONTRACTS_HEAD_ITEMS_FACTOR'); ?></th>
                <th style="width: 2%;"><?php echo JText::sprintf('COM_CONTRACTS_HEAD_ITEMS_MARKUP'); ?></th>
                <th style="width: 1%;"><?php echo JText::sprintf('COM_CONTRACTS_HEAD_ITEMS_COLUMN'); ?></th>
                <th style="width: 5%;"><?php echo JText::sprintf('COM_CONTRACTS_HEAD_ITEMS_VALUE'); ?></th>
                <th><?php echo JText::sprintf('COM_CONTRACTS_HEAD_ITEMS_PERIOD'); ?></th>
                <th style="width: 10%;"><?php echo JText::sprintf('COM_CONTRACTS_HEAD_ITEMS_AMOUNT'); ?></th>
                <th><?php echo JText::sprintf('COM_MKV_ACTION_DELETE'); ?></th>
                <th style="width: 1%;">ID</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <?php foreach ($this->contractItems['items'] as $item) : ?>
                <td><?php echo ++$i; ?></td>
                <td><?php echo $item['edit_link']; ?></td>
                <td><?php echo $item['stand_link']; ?></td>
                <td><?php echo $item['cost']; ?></td>
                <td><?php echo $item['factor']; ?></td>
                <td><?php echo $item['markup']; ?></td>
                <td><?php echo $item['columnID']; ?></td>
                <td><?php echo $item['value']; ?></td>
                <td><?php echo $item['period']; ?></td>
                <td><?php echo $item['amount']; ?></td>
                <td><?php echo $item['delete_link']; ?></td>
                <td><?php echo $item['id']; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="7" style="text-align: right; font-weight: bold;"><?php echo JText::sprintf('COM_CONTRACTS_HEAD_CONTRACTS_SUM_IN_CONTRACT');?></td>
                <td><?php echo $this->contractItems['values'];?></td>
                <td colspan="3"><?php echo JText::sprintf('COM_CONTRACTS_CURRENCY_RUB_AMOUNT_SHORT', number_format($this->contractItems['amount'][$this->item->currency], 2, '.', ' '));?></td>
            </tr>
            </tfoot>
        </table>
    </div>
<?php endif; ?>