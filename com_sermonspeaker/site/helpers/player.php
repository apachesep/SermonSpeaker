<?php
defined('_JEXEC') or die;

/**
 * Sermonspeaker Component Player Helper
 */
class SermonspeakerHelperPlayer
{
	public $mspace;
	public $script;
	public $playlist;
	public $error;
	public $popup;		// 
	public $toggle;		// is able to toggle between audio and video
	public $player;		// name of player

	protected $params;
	protected $config;

	public function __construct()
	{
		// Get params
		$this->params	= JComponentHelper::getParams('com_sermonspeaker');
	}

	// Sets the dimensions of the player for audio and video. $height and $width are default values.
	protected function setDimensions($height, $width)
	{
		$this->config['aheight']	= (isset($this->config['aheight'])) ? $this->config['aheight'] : $this->params->get('aheight', $height);
		$this->config['awidth']		= (isset($this->config['awidth'])) ? $this->config['awidth'] : $this->params->get('awidth', $width);
		$this->config['vheight']	= (isset($this->config['vheight'])) ? $this->config['vheight'] : $this->params->get('vheight', '300px');
		$this->config['vwidth']		= (isset($this->config['vwidth'])) ? $this->config['vwidth'] : $this->params->get('vwidth', '100%');
		return;
	}

	// Sets the dimensions of the Popup window. $type can be 'a' (audio) or 'v' (video)
	protected function setPopup($type = 'a')
	{
		$this->popup['width']	= (strpos($this->config[$type.'width'], '%')) ? 500 : $this->config[$type.'width'] + 130;
		$this->popup['height']	= $this->config[$type.'height'] + $this->params->get('popup_height');
	}
}