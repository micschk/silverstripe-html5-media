<?php

/**
 * Media object: class to put common logic between Video & Audio
 */
		
require_once BASE_PATH.'/vendor/heywatch/heywatch/src/heywatch.php';

class TranscodableObject extends DataObject {
	
	/**
     * @config upload method, see http://www.heywatchencoding.com/docs/output-urls
     */
    private static $transcode_upload_method = "";
	
	/**
     * @config The path, relative to the upload_method, e.g. '/assets/videos/' or '/../subdomains/www2/'
     */
	private static $transcode_relative_path = "";
	
	/**
     * @config The path, relative to the upload_method, e.g. '/assets/videos/' or '/../subdomains/www2/'
     */
	private static $transcode_api_key = "";
	
	private static $db = array (
		"Name" => "Varchar(1024)",
		//"DefaultView" => "Enum('source, transcoded','source'",
		//"Description" => "Text"
		//"Status" => "Enum('created,transcodingstarted,transcodingfailed,done','created')",
	);
	
	private static $default_sort = "Name ASC";

	private static $summary_fields = array(
		"Name" => "Name",
      	//"Description" => "Description"
   	);
	
	private static $casting = array(
		'Tag' => 'HTMLText',
	);
	
	/* let any member crud */
	function canCreate($Member = null) { return true; }
	function canEdit($Member = null) { return true; }
	function canView($Member = null) { return true; }
	function canDelete($Member = null) { return true; }
	
	public function getCMSFields() {
		
//		$fields = parent::getCMSFields();
//		$fields = FieldList::create();
		$fields = new FieldList(new TabSet("Root", new Tab("Main")));
		
		$fields->addFieldToTab("Root.Main", TextField::create("Name"));
		
		return $fields;
		
	}
	
	//
	// Better button actions (generate HTML5 videoformats)
	//
	private static $better_buttons_actions = array (
        'transcode',
    );
	
	/** 
	 * Transcode missing formats from source
	 * @param type $missingOnly
	 */
	public function transcode($missingOnly = true) {
		user_error('Subclass of MediaObject should implement transcode()');
    }
	
	//
	// Duplicated from Images, mainly template helpers
	//

	/**
	 * An image exists if it has a filename.
	 * Does not do any filesystem checks.
	 * 
	 * @return boolean
	 */
//	public function exists() {
//		if(isset($this->record["Filename"])) {
//			return true;
//		}		
//	}
	
	/**
	 * Return an XHTML video tag for this Video,
	 * or NULL if the video files don't exist on the filesystem.
	 * 
	 * @return string
	 */
	public function getTag() {
		if(file_exists(Director::baseFolder() . '/' . $this->Filename)) {
			$url = $this->getURL();
			$title = ($this->Title) ? $this->Title : $this->Filename;
			if($this->Title) {
				$title = Convert::raw2att($this->Title);
			} else {
				if(preg_match("/([^\/]*)\.[a-zA-Z0-9]{1,6}$/", $title, $matches)) {
					$title = Convert::raw2att($matches[1]);
				}
			}
			return "<img src=\"$url\" alt=\"$title\" />";
		}
	}
	
	/**
	 * Return an XHTML img tag for this Image.
	 * 
	 * @return string
	 */
	public function forTemplate() {
		return $this->getTag();
	}
	
//	public function onAfterUpload() {
//		$this->deleteFormattedImages();
//		parent::onAfterUpload();
//	}
	
//	protected function onBeforeDelete() {
//		parent::onBeforeDelete(); 
//
//		$this->deleteFormattedImages();
//	}
	
}
