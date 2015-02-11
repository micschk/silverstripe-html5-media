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
	
	public function getCMSFields() {
		
		$fields = parent::getCMSFields();

		
		
		return $fields;
		
	}
	
	//
	// Better button actions (generate HTML5 videoformats)
	//
	private static $better_buttons_actions = array (
        'transcode',
    );
	
	public function getBetterButtonsActions() {
        $fields = parent::getBetterButtonsActions();
        if($this->SourceID || $this->MP4ID || $this->WEBMID || $this->OGVID) {
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
//		$source = false;
//		if(!$source && $this->SourceID){ $source = $this->Source(); }
//		if(!$source && $this->MP4ID){ $source = $this->MP4(); }
//		if(!$source && $this->WEBMID){ $source = $this->WEBM(); }
//		if(!$source && $this->OGVID){ $source = $this->OGV(); }
//		if(!$source || !$source->exists()){ 
//			Session::set('VideoNotification', array(
//				'error' => _t('Transcodable.MissingSource', 
//							'Could not find any video to use as source for transcoding')) );
//			return false;
//		}
//		
//		// Build heywatch configuration
//		$hw_config = "# Upload config
//			
//set source  = vid_source
//set webhook = vid_webhook
//";
//		// Add missing files;
//		if(!$this->PosterID){ $hw_config .= "
//-> jpg = vid_uploadvid_pathvid_name-#num#.jpg, number=1"; }
//		if(!$this->MP4ID){ $hw_config .= "
//-> mp4 = vid_uploadvid_pathvid_name.mp4"; }
//		if(!$this->WEBMID){ $hw_config .= "
//-> webm = vid_uploadvid_pathvid_name.webm"; }
//		if(!$this->OGVID){ $hw_config .= "
//-> ogv = vid_uploadvid_pathvid_name.ogv"; }
//
//		$ext = pathinfo($source->getFilename(), PATHINFO_EXTENSION);
//		
//		$replacements = array(
//			'vid_webhook' => 'https://app.heywatch.com/pings/54ca174d/micschk',
//			'vid_upload' => Config::inst()->get('Transcoding', 'transcode_upload_method'),
//			'vid_path' => Config::inst()->get('Transcoding', 'transcode_relative_path'),
//			'vid_source' => 'http://www.engr.colostate.edu/me/facil/dynamics/files/flame.avi',//$source->getAbsoluteURL(),
//			'vid_name' => basename($source->getFilename(), ".".$ext) // with extension stripped
//		);
//		$hw_config = strtr($hw_config, $replacements);
////		$conf = file_get_contents("heywatch.conf");
////		Debug::dump($hw_config);
//		
//		$joblog = TranscodeJob::getOrCreateForTranscodable($this->ID);
//		$joblog->TranscodableClass = $this->ClassName;
//		$job = HeyWatch::submit($hw_config, Config::inst()->get('Transcoding', 'transcode_api_key'));
//		
//		if($job->{"status"} == "ok") {
//			// job created
//			$joblog->JobStatus = "started";
//			$joblog->JobID = $job->{"id"};
//		} else {
//			// job not created...
//			$joblog->JobStatus = "error";
//			//$joblog->JobErrorCode = $job->{"error_code"};
//			$joblog->JobErrorMessage = $job->{"error_message"};
//		}
//		$joblog->write();
		
    }
	
	public function loadTranscodedFiles(){
		
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
	

}