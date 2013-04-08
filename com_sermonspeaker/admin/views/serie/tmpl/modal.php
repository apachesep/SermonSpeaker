<?php
// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'serie.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			<?php echo $this->form->getField('series_description')->save(); ?>
			Joomla.submitform(task, document.getElementById('adminForm'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php JRoute::_('index.php?option=com_sermonspeaker'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="width-70 fltlft">
		<fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('COM_SERMONSPEAKER_NEW_SERIE') : JText::sprintf('COM_SERMONSPEAKER_EDIT_SERIE', $this->item->id); ?></legend>
			<ul class="adminformlist">
			<li><?php echo $this->form->getLabel('series_title'); ?>
			<?php echo $this->form->getInput('series_title'); ?></li>

			<li><?php echo $this->form->getLabel('alias'); ?>
			<?php echo $this->form->getInput('alias'); ?></li>

				<li><?php echo $this->form->getLabel('catid'); ?>
				<?php echo $this->form->getInput('catid'); ?></li>

			<li><?php echo $this->form->getLabel('state'); ?>
			<?php echo $this->form->getInput('state'); ?></li>

			<li><?php echo $this->form->getLabel('ordering'); ?>
			<?php echo $this->form->getInput('ordering'); ?></li>
			</ul>
		</fieldset>

		<fieldset class="adminform">
			<legend><?php echo JText::_('JGLOBAL_DESCRIPTION'); ?></legend>
			<div class="clr"></div>
			<?php echo $this->form->getInput('series_description'); ?>
		</fieldset>
	</div>
	<div class="width-30 fltrt">
		<fieldset class="adminform" style="border: 1px dashed silver; padding: 5px; margin: 18px 0px 10px;">
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?></li>

				<li><?php echo $this->form->getLabel('created_by'); ?>
				<?php echo $this->form->getInput('created_by'); ?></li>

				<li><?php echo $this->form->getLabel('created'); ?>
				<?php echo $this->form->getInput('created'); ?></li>

				<?php if ($this->item->modified_by) : ?>
					<li><?php echo $this->form->getLabel('modified_by'); ?>
					<?php echo $this->form->getInput('modified_by'); ?></li>

					<li><?php echo $this->form->getLabel('modified'); ?>
					<?php echo $this->form->getInput('modified'); ?></li>
				<?php endif; ?>

				<li><?php echo $this->form->getLabel('hits'); ?>
				<?php echo $this->form->getInput('hits'); ?></li>
			</ul>
		</fieldset>
		<?php echo JHtml::_('sliders.start','sermon-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
		<?php echo JHtml::_('sliders.panel',JText::_('COM_SERMONSPEAKER_DETAIL'), 'detail-panel'); ?>
		<fieldset class="panelform">
			<ul class="adminformlist">
			<?php foreach($this->form->getFieldset('detail') as $field): ?>
				<li>
					<?php if (!$field->hidden): ?>
						<?php echo $field->label; ?>
					<?php endif; ?>
					<?php echo $field->input; ?>
				</li>
			<?php endforeach; ?>
			</ul>
		</fieldset>

		<?php echo JHtml::_('sliders.panel',JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'meta-panel'); ?>
		<fieldset class="panelform">
			<ul class="adminformlist">
			<?php foreach($this->form->getFieldset('metadata') as $field): ?>
				<li>
					<?php if (!$field->hidden): ?>
						<?php echo $field->label; ?>
					<?php endif; ?>
					<?php echo $field->input; ?>
				</li>
			<?php endforeach; ?>
			</ul>
		</fieldset>
		<?php echo JHtml::_('sliders.end'); ?>
		<div class="formelm-buttons">
			<button type="button" onclick="Joomla.submitbutton('serie.save');">
				<?php echo JText::_('JSAVE') ?>
			</button>
			<button type="button" onclick="window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL') ?>
			</button>
		</div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	<div class="clr"></div>
</form>