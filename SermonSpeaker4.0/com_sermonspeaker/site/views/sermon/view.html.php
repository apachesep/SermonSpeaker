<?php
/**
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
/**
 * HTML Sermon View class for the Sermonspeaker component
 *
 * @package		Sermonspeaker
 */
class SermonspeakerViewSermon extends JViewLegacy
{
	protected $state;
	protected $item;

	function display($tpl = null)
	{
		$app	= JFactory::getApplication();
		$jinput	= $app->input;
		if (!$jinput->get('id', 0, 'int'))
		{
			$id = $this->get('Latest');
			if(!$id){
				JError::raiseWarning(404, JText::_('JGLOBAL_RESOURCE_NOT_FOUND'));
				return;
			}
			$jinput->set('id', $id);
		}
		// Applying CSS file
		JHTML::stylesheet('media/com_sermonspeaker/css/sermonspeaker.css');
		require_once(JPATH_COMPONENT.'/helpers/player.php');
		// Initialise variables.
		$params		= $app->getParams();
		$this->user	= JFactory::getUser();
		$groups		= $this->user->getAuthorisedViewLevels();
		// check if access is not public
		if (!in_array($params->get('access'), $groups))
		{
			$app->redirect(JRoute::_('index.php?view=sermons'), JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		}
		$this->columns = $params->get('col');
		if (!$this->columns)
		{
			$this->columns = array();
		}
		// Get model data (/models/sermon.php) 
		$this->state	= $this->get('State');
		$item	= $this->get('Item');
		if(!$item)
		{
			$app->redirect(JRoute::_('index.php?view=sermons'), JText::_('JGLOBAL_RESOURCE_NOT_FOUND'), 'error');
		}
		// Get Tags
		$item->tags	= $this->get('tags');

		// Check for category ACL
		if ($item->category_access)
		{
			if (!in_array($item->category_access, $groups))
			{
				$app->redirect(JRoute::_('index.php?view=sermons'), JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			}
		}
		if ($item->speaker_category_access)
		{
			if (!in_array($item->speaker_category_access, $groups))
			{
				$app->redirect(JRoute::_('index.php?view=sermons'), JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			}
		}
		if ($item->series_category_access)
		{
			if (!in_array($item->series_category_access, $groups))
			{
				$app->redirect(JRoute::_('index.php?view=sermons'), JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			}
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		// Set layout from parameters if not already set elsewhere
		if ($this->getLayout() == 'default')
		{
			$this->setLayout($params->get('sermonlayout', 'icon'));
		}

		// Update Statistic
		if ($params->get('track_sermon') && !$this->user->authorise('com_sermonspeaker.hit', 'com_sermonspeaker'))
		{
			$model = $this->getModel();
			$model->hit();
		}

		$this->params	= $params;
		$this->item		= $item;

		$this->_prepareDocument();
		parent::display($tpl);
	}
	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app	= JFactory::getApplication();
		$menus	= $app->getMenu();
		$pathway = $app->getPathway();

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_SERMONSPEAKER_SERMON_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		// if the menu item does not concern this article
		if ($menu && ($menu->query['option'] != 'com_sermonspeaker' || $menu->query['view'] != 'sermon' || $menu->query['id'] != $this->item->id))
		{
			if($this->item->sermon_title)
			{
				$title = $this->item->sermon_title;
			}
		}

		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		if (empty($title))
		{
			$title = $this->item->sermon_title;
		}
		$this->document->setTitle($title);

		// add Breadcrumbs
		$pathway = $app->getPathway();
		if ($menu && ($menu->query['view'] == 'series'))
		{
			$pathway->addItem($this->item->series_title, JRoute::_(SermonspeakerHelperRoute::getSerieRoute($this->item->series_slug)));
		}
		elseif ($menu && ($menu->query['view'] == 'speakers'))
		{
			$pathway->addItem($this->item->name, JRoute::_(SermonspeakerHelperRoute::getSpeakerRoute($this->item->speaker_slug)));
		}
		$pathway->addItem($this->item->sermon_title, '');

		// Set MetaData
		if ($this->item->metadesc)
		{
			$this->document->setDescription($this->item->metadesc);
		}
		elseif (!$this->item->metadesc && $this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->item->metakey)
		{
			$keywords = $this->item->metakey;
			if ($this->item->tags && $this->params->get('tags_to_metakey', 0))
			{
				$keywords .= ', '.implode(', ', $this->item->tags);
			}
			$this->document->setMetadata('keywords', $keywords);
		}
		elseif (!$this->item->metakey && $this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		if ($app->getCfg('MetaAuthor'))
		{
			$this->document->setMetaData('author', $this->item->name);
		}
		
		// Add Metadata for Facebook Open Graph API
		$fbadmins	= $this->params->get('fbadmins', '');
		$fbapp_id	= $this->params->get('fbapp_id', '');
		$fbmode		= $this->params->get('fbmode', 'article');
		$prio		= $this->params->get('fileprio', 0);
		if ($fbadmins || $fbapp_id)
		{
			$this->document->addCustomTag('<meta property="og:title" content="'.$this->item->sermon_title.'"/>');
			$this->document->addCustomTag('<meta property="og:url" content="'.JURI::getInstance()->toString().'"/>');
			$this->document->addCustomTag('<meta property="og:description" content="'.$this->document->getDescription().'"/>');
			$this->document->addCustomTag('<meta property="og:site_name" content="'.$app->getCfg('sitename').'"/>');
			if ($picture = SermonspeakerHelperSermonspeaker::insertPicture($this->item))
			{
				$this->document->addCustomTag('<meta property="og:image" content="'.SermonSpeakerHelperSermonSpeaker::makelink($picture, true).'"/>');
			}
			if ($fbmode)
			{
				$this->document->addCustomTag('<meta property="og:type" content="article"/>');
				if ($this->item->name)
				{
					$this->document->addCustomTag('<meta property="article:author" content="'.JURI::base().trim(JRoute::_(SermonspeakerHelperRoute::getSpeakerRoute($this->item->speaker_slug)), '/').'"/>');
				}
				if ($this->item->series_title)
				{
					$this->document->addCustomTag('<meta property="article:section" content="'.$this->item->series_title.'"/>');
				}
				
			}
			else
			{
				if ($this->item->videofile && ($prio || !$this->item->audiofile))
				{
					$this->document->addCustomTag('<meta property="og:type" content="movie"/>');
					if ((strpos($this->item->videofile, 'http://vimeo.com') === 0) || (strpos($this->item->videofile, 'http://player.vimeo.com') === 0))
					{
						$id		= trim(strrchr($this->item->videofile, '/'), '/ ');
						$file	= 'http://vimeo.com/moogaloop.swf?clip_id='.$id.'&amp;server=vimeo.com&amp;show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;color=00adef&amp;fullscreen=1&amp;autoplay=0&amp;loop=0';
					}
					else
					{
						$file	= SermonSpeakerHelperSermonSpeaker::makelink($this->item->videofile, true);
					}
					$this->document->addCustomTag('<meta property="og:video" content="'.$file.'"/>');
				}
				else
				{
					$this->document->addCustomTag('<meta property="og:type" content="song"/>');
					$this->document->addCustomTag('<meta property="og:audio" content="'.SermonSpeakerHelperSermonSpeaker::makelink($this->item->audiofile, true).'"/>');
					$this->document->addCustomTag('<meta property="og:audio:title" content="'.$this->item->sermon_title.'"/>');
					if ($this->item->name)
					{
						$this->document->addCustomTag('<meta property="og:audio:artist" content="'.$this->item->name.'"/>');
					}
					if ($this->item->series_title)
					{
						$this->document->addCustomTag('<meta property="og:audio:album" content="'.$this->item->series_title.'"/>');
					}
				}
			}
			if ($fbadmins)
			{
				$this->document->addCustomTag('<meta property="fb:admins" content="'.$fbadmins.'"/>');
			}
			if ($fbapp_id)
			{
				$this->document->addCustomTag('<meta property="fb:app_id" content="'.$fbapp_id.'"/>');
			}
		}
	}
}