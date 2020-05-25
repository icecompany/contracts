<?php
defined('_JEXEC') or die;
$return = ContractsHelper::getReturnUrl();
$url = JRoute::_("index.php?option=com_contracts&amp;task=stand.add&amp;contractID={$this->item->id}&amp;return={$return}");
$add = JHtml::link($url, JText::sprintf('COM_CONTRACTS_ACTION_ADD_STAND'));
?>
<div><?php echo $add;?></div>
<?php if (!empty($this->stands)): ?>
    <div>
        <table class="table table-stripped">
            <thead>
                <tr>
                    <th><?php echo JText::sprintf('COM_MKV_HEAD_NUMBER');?></th>
                    <th><?php echo JText::sprintf('COM_MKV_HEAD_TYPE');?></th>
                    <th><?php echo JText::sprintf('COM_MKV_HEAD_STATUS');?></th>
                    <th><?php echo JText::sprintf('COM_CONTRACTS_HEAD_STAND_FREEZE');?></th>
                    <th><?php echo JText::sprintf('COM_MKV_HEAD_COMMENT');?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->stands as $i => $stand) :?>
                    <td><?php echo $stand['edit_link'];?></td>
                    <td><?php echo $stand['type'];?></td>
                    <td><?php echo $stand['status'];?></td>
                    <td><?php echo $stand['freeze'];?></td>
                    <td><?php echo $stand['comment'];?></td>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif;?>