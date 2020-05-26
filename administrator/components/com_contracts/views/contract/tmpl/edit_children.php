<?php
defined('_JEXEC') or die;
$ii = 0;
?>
<?php if (!empty($this->children)): ?>
    <div class="center"><h3><?php echo JText::sprintf('COM_CONTRACTS_TITLE_TAB_CHILDREN');?></h3></div>
    <table class="table table-stripped">
        <thead>
        <tr>
            <th>№</th>
            <th><?php echo JText::sprintf('COM_MKV_HEAD_COMPANY'); ?></th>
            <th><?php echo JText::sprintf('COM_MKV_HEAD_STATUS'); ?></th>
            <th><?php echo JText::sprintf('COM_MKV_ACTION_DELETE'); ?></th>
            <th>ID</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <?php foreach ($this->children as $item) : ?>
            <td><?php echo ++$ii; ?></td>
            <td><?php echo $item['company_link']; ?></td>
            <td><?php echo $item['contract_link']; ?></td>
            <td><?php echo $item['delete_link']; ?></td>
            <td><?php echo $item['id']; ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>