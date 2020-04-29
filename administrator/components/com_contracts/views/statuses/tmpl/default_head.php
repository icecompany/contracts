<?php
defined('_JEXEC') or die;
$listOrder    = $this->escape($this->state->get('list.ordering'));
$listDirn    = $this->escape($this->state->get('list.direction'));
?>
<tr>
    <th style="width: 1%;" class="nowrap center hidden-phone">
        <?php echo JHtml::_('searchtools.sort', '', 's.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
    </th>
    <th style="width: 1%;">
        <?php echo JHtml::_('grid.checkall'); ?>
    </th>
    <th style="width: 1%;">
        №
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_TITLE', 's.title', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_CODE', 's.code', $listDirn, $listOrder); ?>
    </th>
    <th style="width: 1%;">
        <?php echo JHtml::_('searchtools.sort', 'ID', 's.id', $listDirn, $listOrder); ?>
    </th>
</tr>
