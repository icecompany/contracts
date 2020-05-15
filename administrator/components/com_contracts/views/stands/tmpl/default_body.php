<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$ii = $this->state->get('list.start', 0);
foreach ($this->items['stands'] as $i => $item) :
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
            <?php echo $item['square']; ?>
        </td>
        <td>
            <?php echo $item['company']; ?>
        </td>
        <td>
            <?php echo $item['contract_status']; ?>
        </td>
        <td>
            <?php echo $item['contract_number']; ?>
        </td>
        <td>
            <?php echo $item['contract_dat']; ?>
        </td>
        <td>
            <?php echo $item['manager']; ?>
        </td>
        <td>
            <?php echo $item['status']; ?>
        </td>
        <?php foreach ($this->items['titles'] as $id => $title): ?>
            <td>
                <?php echo $this->items['items'][$item['id']][$id]; ?>
            </td>
        <?php endforeach;?>
        <td>
            <?php echo $item['id']; ?>
        </td>
    </tr>
<?php endforeach; ?>