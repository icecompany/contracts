<?php
defined('_JEXEC') or die;
?>
<div class="center"><h3><?php echo JText::sprintf('COM_CONTRACTS_TITLE_TAB_NUM_AND_DATE');?></h3></div>
<fieldset class="adminform">
    <div class="control-group form-inline">
        <?php foreach ($this->form->getFieldset('contract') as $field) : ?>
            <div class="control-label">
                <?php echo $field->label;?>
            </div>
            <div class="controls">
                <?php echo $field->input;?>
            </div>
            <br>
        <?php endforeach; ?>
    </div>
</fieldset>
