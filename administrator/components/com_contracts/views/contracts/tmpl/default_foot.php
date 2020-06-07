<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$colspan = '16';?>
<tr>
    <td colspan="<?php echo $colspan;?>" class="center"><?php echo $this->pagination->getListFooter(); ?></td>
</tr>