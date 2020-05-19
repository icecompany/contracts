<?php
defined('_JEXEC') or die;
$listOrder    = $this->escape($this->state->get('list.ordering'));
$listDirn    = $this->escape($this->state->get('list.direction'));
?>
<tr>
    <th style="width: 1%;">
        <?php echo JHtml::_('grid.checkall'); ?>
    </th>
    <th style="width: 1%;">
        №
    </th>
    <?php if ($this->contractID === 0): ?>
        <th>
            <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_ITEMS_COMPANY', 'e.title', $listDirn, $listOrder); ?>
        </th>
    <?php endif; ?>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_ITEMS_ITEM', 'pi.weight', $listDirn, $listOrder); ?>
    </th>
    <th style="width: 10%;">
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_ITEMS_COST', 'i.cost', $listDirn, $listOrder); ?>
    </th>
    <th style="width: 2%;">
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_ITEMS_FACTOR', 'i.factor', $listDirn, $listOrder); ?>
    </th>
    <th style="width: 2%;">
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_ITEMS_MARKUP', 'i.markup', $listDirn, $listOrder); ?>
    </th>
    <th style="width: 1%;">
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_ITEMS_COLUMN', 'i.columnID', $listDirn, $listOrder); ?>
    </th>
    <th style="width: 5%;">
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_ITEMS_STAND', 's.number', $listDirn, $listOrder); ?>
    </th>
    <th style="width: 5%;">
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_ITEMS_VALUE', 'i.value', $listDirn, $listOrder); ?>
    </th>
    <th style="width: 10%;">
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_ITEMS_AMOUNT', 'i.amount', $listDirn, $listOrder); ?>
    </th>
    <th style="width: 1%;">
        <?php echo JHtml::_('searchtools.sort', 'ID', 's.id', $listDirn, $listOrder); ?>
    </th>
</tr>
