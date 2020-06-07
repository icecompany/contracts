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
    <th style="width: 5%;">
        <?php echo JHtml::_('searchtools.sort', 'COM_MKV_HEAD_NUMBER', 'num', $listDirn, $listOrder); ?>
    </th>
    <th style="width: 5%;">
        <?php echo JHtml::_('searchtools.sort', 'COM_MKV_HEAD_DATE', 'c.dat', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JText::sprintf('COM_MKV_HEAD_STANDS'); ?>
    </th>
    <th>
        <?php echo JText::sprintf('COM_CONTRACTS_HEAD_CONTRACTS_OPEN'); ?>
    </th>
    <th>
        <?php echo JText::sprintf('COM_CONTRACTS_HEAD_CONTRACTS_ITEMS'); ?>
    </th>
    <?php if (!is_numeric($this->activeProject)): ?>
        <th>
            <?php echo JHtml::_('searchtools.sort', 'COM_MKV_HEAD_PROJECT', 'p.title', $listDirn, $listOrder); ?>
        </th>
    <?php endif;?>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_MKV_HEAD_COMPANY', 'company', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_MKV_HEAD_MANAGER', 'manager', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_MKV_HEAD_STATUS', 's.ordering', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_CONTRACTS_TASKS_COUNT', 'c.tasks_count', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_CONTRACTS_TASKS_DATE', 'c.tasks_date', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_CONTRACTS_HEAD_CONTRACTS_ORIGINAL', 'i.doc_status', $listDirn, $listOrder); ?>
    </th>
    <th style="width: 9%;">
        <?php echo JHtml::_('searchtools.sort', 'COM_MKV_HEAD_AMOUNT', 'c.amount', $listDirn, $listOrder); ?>
    </th>
    <th style="width: 9%;">
        <?php echo JHtml::_('searchtools.sort', 'COM_MKV_HEAD_PAYED', 'c.payments', $listDirn, $listOrder); ?>
    </th>
    <th style="width: 9%;">
        <?php echo JHtml::_('searchtools.sort', 'COM_MKV_HEAD_DEBT', 'c.debt', $listDirn, $listOrder); ?>
    </th>
    <th style="width: 1%;">
        <?php echo JHtml::_('searchtools.sort', 'ID', 'c.id', $listDirn, $listOrder); ?>
    </th>
</tr>
