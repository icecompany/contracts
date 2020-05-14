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
            <?php echo $item['edit_link']; ?>
        </td>
        <td>
            <?php echo $item['cost']; ?>
        </td>
        <td>
            <?php echo $item['factor']; ?>
        </td>
        <td>
            <?php echo $item['markup']; ?>
        </td>
        <td>
            <?php echo $item['columnID']; ?>
        </td>
        <td>
            <?php echo $item['stand_link']; ?>
        </td>
        <td>
            <?php echo $item['value']; ?>
        </td>
        <td>
            <?php echo $item['amount']; ?>
        </td>
        <td>
            <?php echo $item['id']; ?>
        </td>
    </tr>
<?php endforeach; ?>