<?php
defined('_JEXEC') or die;
$ii = 0;
$arr = [-2, 1, 2]; ?>
<div>
    <table class="table table-stripped">
        <thead>
        <tr>
            <th>№</th>
            <th><?php echo JText::sprintf('COM_MKV_HEAD_OPEN'); ?></th>
            <th><?php echo JText::sprintf('COM_MKV_HEAD_DATE'); ?></th>
            <th><?php echo JText::sprintf('COM_MKV_HEAD_STATUS'); ?></th>
            <th><?php echo JText::sprintf('COM_MKV_HEAD_MANAGER'); ?></th>
            <th><?php echo JText::sprintf('COM_MKV_HEAD_TASK'); ?></th>
            <th><?php echo JText::sprintf('COM_MKV_HEAD_RESULT'); ?></th>
            <th>ID</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($arr as $status) : ?>
            <tr>
            <?php foreach ($this->tasks['items'][$status] as $task) : ?>
                <td><?php echo ++$ii; ?></td>
                <td><?php echo $task['edit_link']; ?></td>
                <td><?php echo $task['date_task']; ?></td>
                <td><?php echo $task['status']; ?></td>
                <td><?php echo $task['manager']; ?></td>
                <td><?php echo $task['task']; ?></td>
                <td><?php echo $task['result']; ?></td>
                <td><?php echo $task['id']; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <?php foreach ($this->tasks['items'][3] as $task) : ?>
            <td><?php echo ++$ii; ?></td>
            <td><?php echo $task['edit_link']; ?></td>
            <td><?php echo $task['date_task']; ?></td>
            <td><?php echo $task['status']; ?></td>
            <td><?php echo $task['manager']; ?></td>
            <td><?php echo $task['task']; ?></td>
            <td><?php echo $task['result']; ?></td>
            <td><?php echo $task['id']; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
