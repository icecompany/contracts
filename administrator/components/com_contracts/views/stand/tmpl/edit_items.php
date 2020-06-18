<?php
defined('_JEXEC') or die;
$ii = 0;
?>
<?php if (!empty($this->standItems['items'])): ?>
    <div>
        <table class="table table-stripped">
            <thead>
            <tr>
                <th style="width: 1%;">№</th>
                <th><?php echo JText::sprintf('COM_CONTRACTS_HEAD_ITEMS_ITEM'); ?></th>
                <th style="width: 15%;"><?php echo JText::sprintf('COM_CONTRACTS_HEAD_ITEMS_VALUE'); ?></th>
                <th style="width: 1%;">ID</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <?php foreach ($this->standItems['items'] as $item) : ?>
                <td><?php echo ++$i; ?></td>
                <td><?php echo $item['item']; ?></td>
                <td><?php echo $item['value']; ?></td>
                <td><?php echo $item['id']; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>