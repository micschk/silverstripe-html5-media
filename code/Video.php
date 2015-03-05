<?php

/**
 * Video object: this represents a HTML video tag with all necessary encoded file formats
 */

class Video extends TranscodableObject {
	
	private static $has_one = array (
		"Poster" => "Image",
		"Source" => "File",
		"MP4" => "File",
		"WEBM" => "File",
		"OGV" => "File",
	);

	private static $summary_fields = array( 
		"Thumbnail" => "Poster",
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
		if(Session::get('VideoNotification')){
			$num = 0;
			foreach(Session::get('VideoNotification') as $type => $message){
				$num += 1;
				$fields->addFieldToTab("Root.Main", new LiteralField("VideoNotification_".$num, 
						"<p class=\"message $type\">$message</p>") );
			}
			Session::clear('VideoNotification');
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
		
//		$mode->setRightTitle(_t('Transcodable.ModeExplain', 
//				'Upload a source file to be transcoded to all (missing) HTML5 formats. If your source file is already in one of the HTML5 formats, choose "HTML encoded formats" and upload it into the correct upload field (MP4, WEBM or OGG) to have that used as original for transcoding. Only missing formats will be transcoded.'));
		
		$sourcehead = LiteralField::create('SourceHead', 
				'<h2>'._t('Transcodable.VideoFiles', 'Video files').'</h2>'.
				'<p style="font-size: 120%; max-width: 640px; line-height: 130%;">'._t('Transcodable.VideoFilesExplain', 
				'Upload a source file to be transcoded to all (missing) HTML5 formats and a poster image. If your source file is already in one of the HTML5 formats, choose "HTML encoded formats" and upload it into the correct upload field (MP4, WEBM or OGG) to have that used as original for transcoding. Only missing formats/files will be transcoded.')
				.'</p>');
		
		$appextensions = Config::inst()->get('File', 'app_categories');
		$movextensions = $appextensions['mov'];
		array_push($movextensions, 'mp4'); // add mp4 to video (seen as audio)
		$sourcefield = ChunkedUploadField::create("Source")
				->setTitle(_t('Transcodable.SourceVideo', "Source video"))
				->setFolderName('videos')
				//->setAllowedFileCategories("mov")
        		->setAllowedExtensions($movextensions);
		$sourcefield->getValidator()->setAllowedMaxFileSize($UploadSizeMB);
		$sourcefieldHolder = DisplayLogicWrapper::create($sourcefield)
				->displayIf('DefaultView')->isEqualTo("source")->end();
		
		$posterfield = UploadField::create("Poster")
				->setTitle("'Poster' image")
				->setFolderName('videos')
				->setAllowedExtensions(array("jpg","jpeg","gif","png"));
//		$posterfieldHolder = DisplayLogicWrapper::create($posterfield)
//				->displayIf('DefaultView')->isEqualTo("source")->end();
		
		$mp4field = ChunkedUploadField::create("MP4")
				->setTitle(_t('Transcodable.MP4video', "MP4 video"))
				->setFolderName('videos')
        		->setAllowedExtensions(array("mp4"));
		$mp4field->getValidator()->setAllowedMaxFileSize($UploadSizeMB);
		$mp4fieldHolder = DisplayLogicWrapper::create($mp4field)
				->displayIf('DefaultView')->isEqualTo("transcoded")->end();
//		$mp4field->displayIf("DefaultView")->isEqualTo("transcoded")->end();
		
		$webmfield = ChunkedUploadField::create("WEBM")
				->setTitle(_t('Transcodable.WEBMvideo', "WEBM video"))
				->setFolderName('videos')
        		->setAllowedExtensions(array("webm"));
		$webmfield->getValidator()->setAllowedMaxFileSize($UploadSizeMB);
		$webmfieldHolder = DisplayLogicWrapper::create($webmfield)
				->displayIf('DefaultView')->isEqualTo("transcoded")->end();
//		$webmfield->displayIf("DefaultView")->isEqualTo("transcoded")->end();
		
		$ogvfield = ChunkedUploadField::create("OGV")
				->setTitle(_t('Transcodable.OGVvideo', "OGG theora video"))
				->setFolderName('videos')
        		->setAllowedExtensions(array("ogg","ogv"));
		$ogvfield->getValidator()->setAllowedMaxFileSize($UploadSizeMB);
		$ogvfieldHolder = DisplayLogicWrapper::create($ogvfield)
				->displayIf('DefaultView')->isEqualTo("transcoded")->end();
//		$ogvfield->displayIf("DefaultView")->isEqualTo("transcoded")->end();
		
		$fields->addFieldsToTab('Root.Main', array(
						$mode,
						$sourcehead, 
						$sourcefieldHolder,
						$posterfield, 
						$mp4fieldHolder,
						$webmfieldHolder,
						$ogvfieldHolder
					) 
				);
		
		return $fields;
		
	}
	
	public function ShowShortCode(){
		return '[video id='.$this->ID.']';
	}
	
	public function PosterCropped($x=160,$y=90) { /* 16:9 Ratio */
		 return $this->Poster()->CroppedImage($x,$y);
	}
	
	public function Thumbnail() {
		$Image = $this->Poster();
		if ( $Image ) 
			return $Image->CMSThumbnail();
		else 
			return null;
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
		$source = false;
		if(!$source && $this->SourceID){ $source = $this->Source(); }
		if(!$source && $this->MP4ID){ $source = $this->MP4(); }
		if(!$source && $this->WEBMID){ $source = $this->WEBM(); }
		if(!$source && $this->OGVID){ $source = $this->OGV(); }
		if(!$source || !$source->exists()){ 
			Session::set('VideoNotification', array(
				'error' => _t('Transcodable.MissingVideoSource', 
							'Could not find any video to use as source for transcoding')) );
			return false;
		}
		
		// Build heywatch configuration
		$hw_config = "# Video config for Heywatch
			
set source  = vid_source
set webhook = vid_webhook
";
		// Add missing files;
		if(!$this->PosterID){ $hw_config .= "
-> jpg = vid_upload/vid_pathvid_name-#num#.jpg, number=1"; }
		if(!$this->MP4ID){ $hw_config .= "
-> mp4 = vid_upload/vid_pathvid_name.mp4"; }
		if(!$this->WEBMID){ $hw_config .= "
-> webm = vid_upload/vid_pathvid_name.webm"; }
		if(!$this->OGVID){ $hw_config .= "
-> ogv = vid_upload/vid_pathvid_name.ogv"; }

		$ext = pathinfo($source->getFilename(), PATHINFO_EXTENSION);
		
		// if we're on localhost, use development webhook, else use real one
		if(Config::inst()->get('Transcoding', 'transcode_development_webhook')){
			$whook = Config::inst()->get('Transcoding', 'transcode_development_webhook');
		} else {
			$whook = Transcode_Controller::staticAbsoluteLink();
		}
		$replacements = array(
			'vid_webhook' => $whook,
			'vid_upload' => Config::inst()->get('Transcoding', 'transcode_upload_method'),
			'vid_path' => Config::inst()->get('Transcoding', 'transcode_relative_video_path_ftp'),
			'vid_source' => $source->getAbsoluteURL(),
			'vid_name' => basename($source->getFilename(), ".".$ext) // with extension stripped
		);
		$hw_config = strtr($hw_config, $replacements);
		//Debug::dump($hw_config);
		
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
		if(!$source && $this->MP4ID){ $source = $this->MP4(); }
		if(!$source && $this->WEBMID){ $source = $this->WEBM(); }
		if(!$source && $this->OGVID){ $source = $this->OGV(); }
		
		$ext = pathinfo($source->getFilename(), PATHINFO_EXTENSION);
		$vid_name = basename($source->getFilename(), ".".$ext); // with extension stripped
		$vid_path = Config::inst()->get('Transcoding', 'transcode_relative_video_path_base');
		
		$poster_path = "$vid_path$vid_name-01.jpg";
		$mp4_path = "$vid_path$vid_name.mp4";
		$webm_path = "$vid_path$vid_name.webm";
		$ogv_path = "$vid_path$vid_name.ogv";
		
		if(!$this->PosterID && file_exists(BASE_PATH."/".$poster_path)){ 
			$file = new Image();
			$file->setFilename($poster_path);
			$file->write();
			$this->PosterID = $file->ID;
		}
		if(!$this->MP4ID && file_exists(BASE_PATH."/".$mp4_path)){ 
			$file = new File();
			$file->setFilename($mp4_path);
			$file->write();
			$this->MP4ID = $file->ID;
		}
		if(!$this->WEBMID && file_exists(BASE_PATH."/".$webm_path)){ 
			$file = new File();
			$file->setFilename($webm_path);
			$file->write();
			$this->WEBMID = $file->ID;
		}
		if(!$this->OGVID && file_exists(BASE_PATH."/".$ogv_path)){ 
			$file = new File();
			$file->setFilename($ogv_path);
			$file->write();
			$this->OGVID = $file->ID;
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

class Video_Controller extends Controller {
	
	private static $allowed_actions = array(
		"oembed",
    );
	
	public static function VideoShortCodeHandler($arguments, $content = null, $parser = null, $tagName) {
        if(!empty($arguments['id']) 
				&& $vid = Video::get()->byID((int) $arguments['id'])){
			return $vid->customise(array('Content'=>$content))->renderWith('VideoTag');
		} else {
			return;
		}
    }
	
	public function oembed(SS_HTTPRequest $request){
		/*{
		 * "thumbnail_height": 360, 
		 * "author_name": "ZackScott", 
		 * "title": "Amazing Nintendo Facts", 
		 * "height": 270, 
		 * "provider_name": "YouTube", 
		 * "width": 480, 
		 * "html": "\u003ciframe width=\"480\" height=\"270\" src=\"http:\/\/www.youtube.com\/embed\/M3r2XDceM6A?feature=oembed\" frameborder=\"0\" allowfullscreen\u003e\u003c\/iframe\u003e", 
		 * "provider_url": "http:\/\/www.youtube.com\/", 
		 * "thumbnail_url": "http:\/\/i.ytimg.com\/vi\/M3r2XDceM6A\/hqdefault.jpg", 
		 * "type": "video", 
		 * "thumbnail_width": 480, 
		 * "author_url": "http:\/\/www.youtube.com\/user\/ZackScott", 
		 * "version": "1.0"
		 * }
		 */
		/* {
		"version": "1.0",
		"type": "video",
		"provider_name": "YouTube",
		"provider_url": "http://youtube.com/",
		"width": 425,
		"height": 344,
		"title": "Amazing Nintendo Facts",
		"author_name": "ZackScott",
		"author_url": "http://www.youtube.com/user/ZackScott",
		"html":
			"<object width=\"425\" height=\"344\">
				<param name=\"movie\" value=\"http://www.youtube.com/v/M3r2XDceM6A&fs=1\"></param>
				<param name=\"allowFullScreen\" value=\"true\"></param>
				<param name=\"allowscriptaccess\" value=\"always\"></param>
				<embed src=\"http://www.youtube.com/v/M3r2XDceM6A&fs=1\"
					type=\"application/x-shockwave-flash\" width=\"425\" height=\"344\"
					allowscriptaccess=\"always\" allowfullscreen=\"true\"></embed>
			</object>",
		}*/
		
		// get video object by ID
		if($this->request->param('ID') 
				&& $vid = Video::get()->byID((int) $this->request->param('ID'))){
			// get some references
			$thumbnail = $vid->Poster();
			$html = $vid->renderWith('VideoTag');
			
			// build embed
			$embed = new stdClass();
			$embed->version = "1.0";
			$embed->type = "video";
			$embed->title = $vid->Name;
			$embed->width = $thumbnail->getWidth();
			$embed->height = $thumbnail->getHeight();
			$embed->html = "$html";
			$embed->thumbnail_height = $thumbnail->getWidth();
			$embed->thumbnail_width = $thumbnail->getHeight();
			$embed->thumbnail_url = $thumbnail->getAbsoluteURL();
					
			// Format response with json
			$response = new SS_HTTPResponse(Convert::raw2json($embed));
			$response->addHeader('Content-Type', 'application/json');
			return $response;
		} else {
			return $this->httpError(404);
		}
		
		
		
		
	}
	
}