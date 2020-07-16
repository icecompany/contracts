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
                <th><?php echo JText::sprintf('COM_MKV_HEAD_PAYMENT_AMOUNT'); ?></th>
                <th><?php echo JText::sprintf('COM_MKV_HEAD_SCORE_DATE'); ?></th>
                <th><?php echo JText::sprintf('COM_MKV_HEAD_PAYMENTS'); ?></th>
                <th><?php echo JText::sprintf('COM_CONTRACTS_HEAD_CONTRACTS_SCORE_STATUS'); ?></th>
                <th><?php echo JText::sprintf('COM_MKV_HEAD_DEBT'); ?></th>
                <th>ID</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <?php foreach ($this->payments as $scoreID => $score) : ?>
                <td><?php echo ++$ii; ?></td>
                <td><?php echo $score['number']; ?></td>
                <td><?php echo $score['amount']; ?></td>
                <td><?php echo $score['dat']; ?></td>
                <td>
                    <?php if (!empty($score['payments'])): ?>
                            <table class="table table-stripped">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;"><?php echo JText::sprintf('COM_MKV_HEAD_PAYMENT_DATE');?></th>
                                        <th style="width: 20%;"><?php echo JText::sprintf('COM_MKV_HEAD_PAYMENT_ORDER');?></th>
                                        <th><?php echo JText::sprintf('COM_MKV_HEAD_AMOUNT');?></th>
                                        <th style="width: 5%;">ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($score['payments'] as $payment): ?>
                                        <tr>
                                            <td><?php echo $payment['payment_date']; ?></td>
                                            <td><?php echo $payment['order_name']; ?></td>
                                            <td><?php echo $payment['payment_amount']; ?></td>
                                            <td><?php echo $payment['paymentID']; ?></td>
                                        </tr>
                                    <?php endforeach;?>
                                </tbody>
                            </table>
                    <?php endif;?>
                    <?php if (empty($score['payments'])): ?>
                        <?php echo $score['status']; ?>
                    <?php endif;?>
                </td>
                <td><?php echo $score['status']; ?></td>
                <td><?php echo $score['debt']; ?></td>
                <td><?php echo $score['id']; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>