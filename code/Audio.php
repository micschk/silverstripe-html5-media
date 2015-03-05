<?php

/**
 * Video object: this represents a HTML video tag with all necessary encoded file formats
 */

class Audio extends TranscodableObject {
	
	private static $has_one = array (
		"Source" => "File",
		"MP3" => "File",
		"OGG" => "File",
	);

	private static $summary_fields = array(
		"Name" => "Name",
		"ShowShortCode" => "ShortCode"
      	//"Description" => "Description"
   	);
	
	public function getCMSFields() {
		
		$fields = parent::getCMSFields();

		if($this->JobID){
			$fields->addFieldToTab("Root.Main", new LiteralField("JobID", 
		"<p class=\"message good\">Transcoding job ID: {$this->JobID}, Status: {$this->JobStatus}</p>"));
		}
		
		// if transcoding started or error
		if(Session::get('AudioNotification')){
			$num = 0;
			foreach(Session::get('AudioNotification') as $type => $message){
				$num += 1;
				$fields->addFieldToTab("Root.Main", new LiteralField("AudioNotification".$num, 
						"<p class=\"message $type\">$message</p>") );
			}
			Session::clear('AudioNotification');
		}
		
		$UploadSizeMB = 350 * 1024 * 1024; // 350 MB in bytes
		
		// Switch between two views; 
		// - Uploadfield for source file (any type) to be transcoded OR
		// - Uploadfields for manually transcoded files
		$mode = new DropdownField(
		  'DefaultView',
		  _t('Transcodable.Mode', 'Mode'),
		  array(
			  'source' => _t('Transcodable.SourceForTranscoding', 'Upload source file for transcoding'),
			  'transcoded' => _t('Transcodable.EncodedFormats', 'Upload HTML5 encoded formats')
			  )
		);
		
		$sourcehead = LiteralField::create('SourceHead', 
				'<h2>'._t('Transcodable.AudioFiles', 'Audio files').'</h2>'.
				'<p style="font-size: 120%; max-width: 640px; line-height: 130%;">'.
				_t('Transcodable.AudioFilesExplain', 
				'Upload a source file to be transcoded to all (missing) HTML5 formats. If your source file is already in one of the HTML5 formats, choose "HTML encoded formats" and upload it into the correct upload field (MP3 or OGG) to have that used as original for transcoding. Only missing formats/files will be transcoded.')
				.'</p>');
		
		$appextensions = Config::inst()->get('File', 'app_categories');
		$audioextensions = $appextensions['audio'];
		$sourcefield = ChunkedUploadField::create("Source")
				->setTitle(_t('Transcodable.SourceVideo', "Source video"))
				->setFolderName('audio')
        		->setAllowedExtensions($audioextensions);
		$sourcefield->getValidator()->setAllowedMaxFileSize($UploadSizeMB);
		$sourcefieldHolder = DisplayLogicWrapper::create($sourcefield)
				->displayIf('DefaultView')->isEqualTo("source")->end();
		
		$mp3field = ChunkedUploadField::create("MP3")
				->setTitle(_t('Transcodable.MP3audio', "MP3 audio"))
				->setFolderName('audio')
        		->setAllowedExtensions(array("mp3"));
		$mp3field->getValidator()->setAllowedMaxFileSize($UploadSizeMB);
		$mp3fieldHolder = DisplayLogicWrapper::create($mp3field)
				->displayIf('DefaultView')->isEqualTo("transcoded")->end();
		
		$oggfield = ChunkedUploadField::create("OGG")
				->setTitle(_t('Transcodable.OGGaudio', "OGG audio"))
				->setFolderName('audio')
        		->setAllowedExtensions(array("webm"));
		$oggfield->getValidator()->setAllowedMaxFileSize($UploadSizeMB);
		$oggfieldHolder = DisplayLogicWrapper::create($oggfield)
				->displayIf('DefaultView')->isEqualTo("transcoded")->end();
		
		$fields->addFieldsToTab('Root.Main', array(
						$mode,
						$sourcehead, 
						$sourcefieldHolder,
						$mp3fieldHolder, 
						$oggfieldHolder
					) 
				);
		
		return $fields;
		
	}
	
	public function ShowShortCode(){
		return '[audio id='.$this->ID.']';
	}
	
	//
	// Better button actions (generate HTML5 videoformats)
	//
	private static $better_buttons_actions = array (
        'transcode',
    );
	
	public function getBetterButtonsActions() {
        $fields = parent::getBetterButtonsActions();
        if($this->SourceID || $this->MP3ID || $this->OGGID) {
            $fields->push($bbact = BetterButtonCustomAction::create('transcode', 
					_t('Transcodable.TranscodeSource', 'Transcode (missing) formats')));
//			$bbact->setRedirectType(BetterButtonCustomAction::REFRESH)
//			->setSuccessMessage('Denied for publication');
        }
        return $fields;
    }
	
	/** 
	 * Transcode missing formats from source
	 * @param type $missingOnly
	 */
	public function transcode($missingOnly = true) {
		// get source in preferred order
		$source = false;
		if(!$source && $this->SourceID){ $source = $this->Source(); }
		if(!$source && $this->MP3ID){ $source = $this->MP3(); }
		if(!$source && $this->OGGID){ $source = $this->OGG(); }
		if(!$source || !$source->exists()){ 
			Session::set('AudioNotification', array(
				'error' => _t('Transcodable.MissingAudioSource', 
							'Could not find any audio to use as source for transcoding')) );
			return false;
		}
		
		// Build heywatch configuration
		$hw_config = "# Audio config for Heywatch
			
set source  = aud_source
set webhook = aud_webhook
";
		// Add missing files;
		if(!$this->MP3ID){ $hw_config .= "
-> mp3 = aud_uploadaud_pathaud_name.mp4"; }
		if(!$this->OGGID){ $hw_config .= "
-> ogg = aud_uploadaud_pathaud_name.ogg"; }

		$ext = pathinfo($source->getFilename(), PATHINFO_EXTENSION);
		
		// if we're on localhost, use development webhook, else use real one
		if(Config::inst()->get('Transcoding', 'transcode_development_webhook')){
			$whook = Config::inst()->get('Transcoding', 'transcode_development_webhook');
		} else {
			$whook = Transcode_Controller::staticAbsoluteLink();
		}
		$replacements = array(
			'aud_webhook' => $whook,
			'aud_upload' => Config::inst()->get('Transcoding', 'transcode_upload_method'),
			'aud_path' => Config::inst()->get('Transcoding', 'transcode_relative_audio_path'),
			'aud_source' => $source->getAbsoluteURL(),
			'aud_name' => basename($source->getFilename(), ".".$ext) // with extension stripped
		);
		$hw_config = strtr($hw_config, $replacements);
//		$conf = file_get_contents("heywatch.conf");
//		Debug::dump($hw_config);
		
		$joblog = TranscodeJob::getOrCreateForTranscodable($this->ID);
		$joblog->TranscodableClass = $this->ClassName;
		$job = HeyWatch::submit($hw_config, Config::inst()->get('Transcoding', 'transcode_api_key'));
		
		if($job->{"status"} == "ok") {
			// job created
			$joblog->JobStatus = "started";
			$joblog->JobID = $job->{"id"};
		} else {
			// job not created...
			$joblog->JobStatus = "error";
			//$joblog->JobErrorCode = $job->{"error_code"};
			$joblog->JobErrorMessage = $job->{"error_message"};
		}
		$joblog->write();
		
    }
	
	public function loadTranscodedFiles(){
		
		// get (a) source file to deduct name etc from...
		$source = false;
		if(!$source && $this->SourceID){ $source = $this->Source(); }
		if(!$source && $this->MP3ID){ $source = $this->MP3(); }
		if(!$source && $this->OGGID){ $source = $this->OGG(); }
		
		$ext = pathinfo($source->getFilename(), PATHINFO_EXTENSION);
		$aud_name = basename($source->getFilename(), ".".$ext); // with extension stripped
		$aud_path = Config::inst()->get('Transcoding', 'transcode_relative_audio_path_base');
		
		$mp3_path = "$vid_path$vid_name.mp3";
		$ogg_path = "$vid_path$vid_name.ogg";
		
		if(!$this->MP3ID && file_exists(BASE_PATH."/".$mp3_path)){ 
			$file = new File();
			$file->setFilename($mp3_path);
			$file->write();
			$this->MP3ID = $file->ID;
		}
		if(!$this->OGGID && file_exists(BASE_PATH."/".$ogg_path)){ 
			$file = new File();
			$file->setFilename($ogg_path);
			$file->write();
			$this->OGGID = $file->ID;
		}
		$this->write();
		
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

class Audio_Controller extends Controller {

	public static function AudioShortCodeHandler($arguments, $content = null, $parser = null, $tagName) {
        if(!empty($arguments['id']) 
				&& $vid = Audio::get()->byID((int) $arguments['id'])){
			return $vid->customise(array('Content'=>$content))->renderWith('AudioTag');
		} else {
			return;
		}
    }
	
}