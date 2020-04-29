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
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_CONTRACTS_NUMBER', 'number', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_CONTRACTS_DATE', 'c.dat', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JText::sprintf('COM_CONTRACTS_HEAD_CONTRACTS_OPEN'); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_CONTRACTS_PROJECT', 'p.title', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_CONTRACTS_COMPANY', 'company', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_CONTRACTS_MANAGER', 'manager', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_CONTRACTS_STATUS', 's.ordering', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_CONTRACTS_ORIGINAL', 'i.doc_status', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_CONTRACTS_AMOUNT', 'c.amount', $listDirn, $listOrder); ?>
    </th>
    <th style="width: 1%;">
        <?php echo JHtml::_('searchtools.sort', 'ID', 'c.id', $listDirn, $listOrder); ?>
    </th>
</tr>
