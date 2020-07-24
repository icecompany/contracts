<?php
defined('_JEXEC') or die;
$ii = 0;
?>
<?php if (!empty($this->item->children)): ?>
    <div class="center"><h3><?php echo JText::sprintf('COM_CONTRACTS_TITLE_TAB_CHILDREN');?></h3></div>
    <table class="table table-stripped">
        <thead>
        <tr>
            <th>№</th>
            <th><?php echo JText::sprintf('COM_MKV_HEAD_COMPANY'); ?></th>
            <th><?php echo JText::sprintf('COM_MKV_HEAD_STATUS'); ?></th>
            <th style="text-align: center;"><?php echo JText::sprintf('COM_CONTRACTS_HEAD_STAND_DELEGATED'); ?></th>
            <th>ID</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <?php foreach ($this->item->children as $item) : ?>
            <td><?php echo ++$ii; ?></td>
            <td><?php echo $item['company_link']; ?></td>
            <td><?php echo $item['contract_link']; ?></td>
            <td style="text-align: center;">
                <input type="checkbox" data-id="<?php echo $item['id'];?>" data-csid="<?php echo $this->item->id; ?>" <?php if (!empty($item['contractStandID'])) echo 'checked'; ?> onchange="asset(this);" />
            </td>
            <td><?php echo $item['id']; ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>