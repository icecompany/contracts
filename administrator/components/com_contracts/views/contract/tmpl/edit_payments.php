<?php
defined('_JEXEC') or die;
$ii = 0;
?>
<?php if (!empty($this->payments)): ?>
    <div>
        <table class="table table-stripped">
            <thead>
            <tr>
                <th>№</th>
                <th><?php echo JText::sprintf('COM_CONTRACTS_HEAD_CONTRACTS_SCORE_NUMBER'); ?></th>
                <th><?php echo JText::sprintf('COM_CONTRACTS_HEAD_CONTRACTS_SCORE_STATUS'); ?></th>
                <th><?php echo JText::sprintf('COM_MKV_HEAD_PAYMENT_ORDER'); ?></th>
                <th><?php echo JText::sprintf('COM_MKV_HEAD_PAYMENT_DATE'); ?></th>
                <th><?php echo JText::sprintf('COM_CONTRACTS_HEAD_CONTRACTS_PAYMENT_AMOUNT'); ?></th>
                <th><?php echo JText::sprintf('COM_MKV_HEAD_PAYER'); ?></th>
                <th>ID</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <?php foreach ($this->payments as $item) : ?>
                <td><?php echo ++$ii; ?></td>
                <td><?php echo $item['score_link']; ?></td>
                <td><?php echo $item['score_status']; ?></td>
                <td><?php echo $item['edit_link']; ?></td>
                <td><?php echo $item['dat']; ?></td>
                <td><?php echo $item['amount']; ?></td>
                <td><?php echo (!empty($item['payer'])) ? $item['payer_link'] : $this->item->company; ?></td>
                <td><?php echo $item['id']; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>