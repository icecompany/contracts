<?php
defined('_JEXEC') or die;
$ii = 0;
?>
<?php if (!empty($this->files)): ?>
    <div>
        <table class="table table-stripped">
            <thead>
            <tr>
                <th>№</th>
                <th><?php echo JText::sprintf('COM_CONTRACTS_HEAD_FILE'); ?></th>
                <th><?php echo JText::sprintf('COM_CONTRACTS_HEAD_LAST_MODIFIED'); ?></th>
                <th><?php echo JText::sprintf('COM_MKV_ACTION_DELETE'); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <?php foreach ($this->files as $file) : ?>
                <td><?php echo ++$ii; ?></td>
                <td><?php echo $file['download_link']; ?></td>
                <td><?php echo $file['modified']; ?></td>
                <td><?php echo $file['delete_link']; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>