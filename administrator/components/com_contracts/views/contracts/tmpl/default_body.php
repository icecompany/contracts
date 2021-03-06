<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$ii = $this->state->get('list.start', 0);
foreach ($this->items['items'] as $i => $item) :
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="center">
            <?php echo JHtml::_('grid.id', $i, $item['id']); ?>
        </td>
        <td>
            <?php echo ++$ii; ?>
        </td>
        <td>
            <?php echo $item['number']; ?>
        </td>
        <td>
            <?php echo $item['dat']; ?>
        </td>
        <td>
            <?php echo $item['stands']; ?>
        </td>
        <td>
            <?php echo $item['edit_link']; ?>
        </td>
        <td>
            <?php echo $item['items_link']; ?>
        </td>
        <?php if (!is_numeric($this->activeProject)): ?>
            <td>
                <?php echo $item['project']; ?>
            </td>
        <?php endif; ?>
        <td>
            <?php echo $item['company_link']; ?>
        </td>
        <td>
            <?php echo $item['manager']; ?>
        </td>
        <td>
            <?php echo $item['status']; ?>
        </td>
        <td>
            <?php echo $item['tasks_link'] ?? $item['tasks_count']; ?>
        </td>
        <td>
            <?php echo $item['tasks_date']; ?>
        </td>
        <td>
            <?php echo $item['doc_status']; ?>
        </td>
        <td>
            <?php echo $item['amount_full']; ?>
        </td>
        <td>
            <?php echo $item['payments_full']; ?>
        </td>
        <td>
            <?php echo $item['debt_full']; ?>
        </td>
        <td>
            <?php echo $item['id']; ?>
        </td>
    </tr>
<?php endforeach; ?>