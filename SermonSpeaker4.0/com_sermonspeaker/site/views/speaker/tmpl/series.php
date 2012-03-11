<?php
defined('_JEXEC') or die('Restricted access');
JHTML::addIncludePath(JPATH_COMPONENT.'/helpers');
JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');
$user		= JFactory::getUser();
$canEdit	= $user->authorise('core.edit', 'com_sermonspeaker');
$canEditOwn	= $user->authorise('core.edit.own', 'com_sermonspeaker');
$listOrder	= $this->state_series->get('list.ordering');
$listDirn	= $this->state_series->get('list.direction');
?>
<div class="ss-speaker-container<?php echo htmlspecialchars($this->params->get('pageclass_sfx')); ?>" >
<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php endif; ?>
<h2><a href="<?php echo JRoute::_(SermonspeakerHelperRoute::getSpeakerRoute($this->item->slug)); ?>"><?php echo $this->item->name; ?></a></h2>
<?php if ($canEdit || ($canEditOwn && ($user->id == $this->item->created_by))) : ?>
	<ul class="actions">
		<li class="edit-icon">
			<?php echo JHtml::_('icon.edit', $this->item, $this->params, array('type' => 'speaker')); ?>
		</li>
	</ul>
<?php endif;
if (in_array('speaker:hits', $this->columns)): ?>
	<dl class="article-info speaker-info">
	<dt class="article-info-term"><?php  echo JText::_('JDETAILS'); ?></dt>
	<?php if (in_array('serie:hits', $this->columns)): ?>
		<dd class="hits">
			<?php echo JText::_('JGLOBAL_HITS').': '.$this->item->hits; ?>
		</dd>
	<?php endif; ?>
	</dl>
<?php endif; ?>
<div class="category-desc">
	<div class="ss-pic">
		<?php if ($this->item->pic) : ?>
			<img src="<?php echo SermonspeakerHelperSermonspeaker::makelink($this->item->pic); ?>" title="<?php echo $this->item->name; ?>" alt="<?php echo $this->item->name; ?>" />
		<?php endif; ?>
	</div>
	<?php if (($this->item->bio && in_array('speaker:bio', $this->columns)) || ($this->item->intro && in_array('speaker:intro', $this->columns))) : ?>
		<h3><?php echo JText::_('COM_SERMONSPEAKER_SPEAKER_BIO'); ?></h3>
		<?php 
		if (in_array('speaker:intro', $this->columns)):
			echo JHTML::_('content.prepare', $this->item->intro);
		endif;
		if (in_array('speaker:bio', $this->columns)):
			echo JHTML::_('content.prepare', $this->item->bio);
		endif;
	endif; ?>
	<div class="clear-left"></div>
	<?php if ($this->item->website && $this->item->website != 'http://') : ?>
		<a href="<?php echo $this->item->website; ?>" target="_blank" title="<?php echo JText::_('COM_SERMONSPEAKER_SPEAKER_WEBLINK_HOOVER'); ?>"><?php echo JText::sprintf('COM_SERMONSPEAKER_SPEAKER_WEBLINK', $this->item->name); ?></a>
	<?php endif; ?>
</div>
<!-- Begin Data - Series -->
<?php if (empty($this->series)) : ?>
	<div class="no_entries"><?php echo JText::sprintf('COM_SERMONSPEAKER_NO_ENTRIES', JText::_('COM_SERMONSPEAKER_SERIES')); ?></div>
<?php else : ?>
<form action="<?php echo JFilterOutput::ampReplace(JFactory::getURI()->toString()); ?>" method="post" id="adminForm" name="adminForm">
	<?php if ($this->params->get('show_pagination_limit')) : ?>
	<div class="display-limit">
		<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
		<?php echo $this->pag_series->getLimitBox(); ?>
	</div>
	<?php endif; ?>
	<table class="category">
	<!-- Tabellenkopf mit Sortierlinks erstellen -->
		<thead><tr>
			<?php if ($this->av > 0) : ?>
				<th width='10'> </th>
			<?php endif; ?>
			<th class="ss-title">
				<?php echo JHTML::_('grid.sort', 'JGLOBAL_TITLE', 'series_title', $listDirn, $listOrder); ?>
			</th>
			<?php if (in_array('speaker:description', $this->col_serie)): ?>
				<th class="ss-col ss-series_desc">
					<?php echo JHTML::_('grid.sort', 'JGLOBAL_DESCRIPTION', 'series_description', $listDirn, $listOrder); ?>
				</th>
			<?php endif;
			if (in_array('speaker:hits', $this->col_serie)) : ?>
				<th class="ss-col ss-hits">
					<?php echo JHTML::_('grid.sort', 'JGLOBAL_HITS', 'hits', $listDirn, $listOrder); ?>
				</th>
			<?php endif;
			if (in_array('speaker:download', $this->col_serie)) : ?>
				<th></th>
			<?php endif; ?>
		</tr></thead>
	<!-- Begin Data -->
		<tbody>
			<?php foreach($this->series as $i => $item) : ?>
				<tr class="<?php echo ($i % 2) ? "odd" : "even"; ?>">
					<?php if ($this->av) :
						if ($item->avatar) : ?>
							<td class="ss-col ss-avatar"><img src="<?php echo SermonspeakerHelperSermonspeaker::makelink($item->avatar); ?>"></td>
						<?php else : ?>
							<td class="ss-col ss-avatar"></td>
						<?php endif;
					endif; ?>
					<td class="ss-title">
						<a title='<?php echo JText::_('COM_SERMONSPEAKER_SERIESLINK_HOOVER'); ?>' href="<?php echo JRoute::_(SermonspeakerHelperRoute::getSerieRoute($item->slug)); ?>"><?php echo $item->series_title; ?></a>
						<?php if ($canEdit || ($canEditOwn && ($user->id == $item->created_by))) : ?>
							<ul class="actions">
								<li class="edit-icon">
									<?php echo JHtml::_('icon.edit', $item, $this->params, array('type' => 'serie')); ?>
								</li>
							</ul>
						<?php endif; ?>
					</td>
					<?php if (in_array('speaker:description', $this->col_serie)): ?>
						<td class="ss-col ss-series_desc"><?php echo JHTML::_('content.prepare', $item->series_description); ?></td>
					<?php endif;
					if (in_array('speaker:hits', $this->col_serie)) : ?>
						<td class="ss-col ss-hits"><?php echo $item->hits; ?></td>
					<?php endif;
					if (in_array('speaker:download', $this->col_serie)) : ?>
						<td class="ss-col ss-dl"><a href="<?php echo JRoute::_('index.php?task=serie.download&id='.$item->slug); ?>">
							<img src="media/com_sermonspeaker/images/download.png" alt="<?php echo JText::_('COM_SERMONSPEAKER_DIRECTLINK_HOOVER'); ?>" />
						</a></td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php if ($this->params->get('show_pagination') && ($this->pag_series->get('pages.total') > 1)) : ?>
		<div class="pagination">
			<?php if ($this->params->get('show_pagination_results', 1)) : ?>
				<p class="counter">
					<?php echo $this->pag_series->getPagesCounter(); ?>
				</p>
			<?php endif;
			echo $this->pag_series->getPagesLinks(); ?>
		</div>
	<?php endif; ?>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
</form>
<?php endif; ?>
</div>