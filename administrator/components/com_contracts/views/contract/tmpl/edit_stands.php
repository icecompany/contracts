<?php
defined('_JEXEC') or die;
?>
<?php if (!empty($this->stands)): ?>
    <div>
        <table class="table table-stripped">
            <thead>
            <tr>
                <th><?php echo JText::sprintf('COM_MKV_HEAD_NUMBER'); ?></th>
                <th><?php echo JText::sprintf('COM_MKV_HEAD_TYPE'); ?></th>
                <th><?php echo JText::sprintf('COM_MKV_HEAD_STATUS'); ?></th>
                <th><?php echo JText::sprintf('COM_CONTRACTS_HEAD_STAND_FREEZE'); ?></th>
                <th><?php echo JText::sprintf('COM_MKV_HEAD_COMMENT'); ?></th>
                <th><?php echo JText::sprintf('COM_MKV_ACTION_DELETE'); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <?php foreach ($this->stands as $stand) : ?>
                <td><?php echo $stand['edit_link']; ?></td>
                <td><?php echo $stand['type']; ?></td>
                <td><?php echo $stand['status']; ?></td>
                <td><?php echo $stand['freeze']; ?></td>
                <td><?php echo $stand['comment']; ?></td>
                <td><?php echo $stand['delete_link']; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>