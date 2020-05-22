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
        <?php echo JHtml::_('searchtools.sort', 'COM_MKV_HEAD_CONTRACT_NUMBER', 'length(number), number', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_MKV_HEAD_CONTRACT_STATUS', 's.code', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_MKV_HEAD_MANAGER', 'u.name', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_MKV_HEAD_COMPANY', 'e.title', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JText::sprintf('COM_CONTRACTS_HEAD_RESPONSIBLES_FOR_ACCREDITATION'); ?>
    </th>
    <th>
        <?php echo JText::sprintf('COM_CONTRACTS_HEAD_RESPONSIBLES_FOR_BUILDING'); ?>
    </th>
    <th style="width: 1%;">
        <?php echo JHtml::_('searchtools.sort', 'ID', 'con.id', $listDirn, $listOrder); ?>
    </th>
</tr>
