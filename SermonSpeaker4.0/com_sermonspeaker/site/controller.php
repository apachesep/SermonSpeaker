<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * SermonSpeaker Component Controller
 */
class SermonspeakerController extends JController
{
	public function display($cachable = false, $urlparams = false)
	{
		$cachable = true;

		// Get the document object.
		$document = JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'sermons');
		JRequest::setVar('view', $vName);

		if (JRequest::getCmd('view') == 'feed' && (JRequest::getCmd('format') != 'raw')){
			$this->setRedirect('index.php?option=com_sermonspeaker&view=feed&format=raw');
			return;
		}

		$safeurlparams = array('id'=>'INT','limit'=>'INT','limitstart'=>'INT','filter_order'=>'CMD','filter_order_Dir'=>'CMD','lang'=>'CMD','year'=>'INT','month'=>'INT');

		return parent::display($cachable,$safeurlparams);
	}

	function updateStat ($type, $id) {
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sermonspeaker'.DS.'tables');
		$table =& JTable::getInstance($type, 'Table');
		$table->hit($id);
	}
	
	function download_serie(){
		$id = JRequest::getInt('id');
		if ($id == ''){
			die("<html><body OnLoad=\"javascript: alert('I have no clue what you want to download...');history.back();\" bgcolor=\"#F0F0F0\"></body></html>");
		}
		$db =& JFactory::getDBO();
		$query = "SELECT audiofile, videofile FROM #__sermon_sermons WHERE series_id = ".$id;
		$db->setQuery($query);
		$rows = $db->loadAssocList();
		jimport('joomla.filesystem.file');
		$files = array();
		foreach ($rows as $row){
			if ($row['audiofile'] && (substr($row['audiofile'], 0, 7) != 'http://') && JFile::exists(JPATH_BASE.DS.$row['audiofile'])){
				$file['path'] = JPATH_BASE.DS.$row['audiofile'];
				$slash = strrpos($row['audiofile'], '/');
				if ($slash !== false) {
					$file['name'] = substr($row['audiofile'], $slash + 1);
				} else {
					$file['name'] = $row['audiofile'];
				}
				$files[] = $file;
			}
			if ($row['videofile'] && (substr($row['videofile'], 0, 7) != 'http://') && JFile::exists(JPATH_BASE.DS.$row['videofile'])){
				$file['path'] = JPATH_BASE.DS.$row['videofile'];
				$slash = strrpos($row['videofile'], '/');
				if ($slash !== false) {
					$file['name'] = substr($row['videofile'], $slash + 1);
				} else {
					$file['name'] = $row['videofile'];
				}
				$files[] = $file;
			}
		}
		if (count($files)){
			$query = "SELECT series_title FROM #__sermon_series WHERE id = ".$id;
			$db->setQuery($query);
			$name = JFile::makeSafe($db->loadResult().'.zip');
			$filename = JPATH_BASE.DS.'images'.DS.$name;
			$zip = new ZipArchive();
			if ($zip->open($filename, ZIPARCHIVE::OVERWRITE)!==TRUE) {
				exit("cannot open <$filename>\n");
			}
			foreach ($files as $file){
				$zip->addFile($file['path'], $file['name']);
			}
			$zip->close();
			$app = JFactory::getApplication();
			$app->redirect(JURI::root().'/images/'.$name);
		}
	}

	function download () {
		$id = JRequest::getInt('id');
		if ($id == ''){
			die("<html><body OnLoad=\"javascript: alert('I have no clue what you want to download...');history.back();\" bgcolor=\"#F0F0F0\"></body></html>");
		}
		$db =& JFactory::getDBO();
		$query = "SELECT audiofile FROM #__sermon_sermons WHERE id = ".$id;
		$db->setQuery($query);
		$result = $db->loadResult() or die ("<html><body OnLoad=\"javascript: alert('Encountered an error while accessing the database');history.back();\" bgcolor=\"#F0F0F0\"></body></html>");
		$result = rtrim($result);

		if (substr($result, 0, 7) == 'http://'){ // cancel if link goes to an external source
			die('<html><body OnLoad="javascript: alert(\'This file points to an external source. I can\'t access it.\');history.back();" bgcolor="#F0F0F0"></body></html>');
		}
		$result = str_replace('\\', '/', $result); // replace \ with /
		if (substr($result, 0, 1) != '/') { // add a leading slash to the sermonpath if not present.
			$result = '/'.$result;
		}
		$file = JPATH_ROOT.$result;
		$filename = explode("/", $file);
		$filename = array_reverse($filename);

		if(ini_get('zlib.output_compression')) {
			ini_set('zlib.output_compression', 'Off');
		}
		if (file_exists($file)) {
			if(ini_get('memory_limit')){
				ini_set('memory_limit','-1'); // if present overriding the memory_limit for php so big mp3 files can be downloaded.
			}
			header("Pragma: public");
			header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false);
			header("Content-Type: application/mp3");
			header('Content-Disposition: attachment; filename="'.$filename[0].'"');
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".@filesize($file));
			set_time_limit(0);
			@readfile($file) OR die('Unable to read file!');
			exit;
		} else {
			die("<html><body OnLoad=\"javascript: alert('File not found!');history.back();\" bgcolor=\"#F0F0F0\"></body></html>");
		}
	} // end of download
}