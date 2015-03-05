<?php

/**
 * Controller to receive pingbacks from video transcoding jobs
 */
class Transcode_Controller extends Controller {
    
    /**
     * URL That you can access this from
     *
     * @config
     */
    private static $url_segment = "transcode-log";
	
    private static $allowed_actions = array(
		"index",
    );
	
    public function init() {
        parent::init();
    }
	
    public static function staticAbsoluteLink($action = null) {
        return Controller::join_links(
            Director::absoluteURL(BASE_URL),
            self::$url_segment,
            $action
        );
    }
	
	/* Example pings/updates (to be processed by index)
	{
	  "id": 898,
	  "errors": {
		"output": {
		  "jpg": "unknown_error"
		}
	  },
	  "output_urls": {
		"ogv": "",
		"webm": "",
		"mp4": ""
	  }
	}
	Or
	{
	  "id": 898,
	  "errors": {
	  },
	  "output_urls": {
		"jpg": [
		  "ftp://user:pass@restruct.nl:21/test/bird-01.jpg"
		],
		"webm": "ftp://user:pass@restruct.nl:21/test/bird.webm",
		"mp4": "ftp://user:pass@restruct.nl:21/test/bird.mp4",
		"ogv": "ftp://user:pass@restruct.nl:21/test/bird.ogv"
	  }
	}
	*/
	public function index(SS_HTTPRequest $request) {
		
		if($request->isPOST()){
			$update = json_decode($request->getBody());
			$joblog = TranscodeJob::get()->filter('JobID', (int) $update->id)->first();
			// return if status already is done (some protection)
			if($joblog->JobStatus!=="started"){ return "Error: job status not started"; }
			
			// save full update into log object -- no, may contain passwords etc. -- well, fixed but still...
			
			// load files into appropriate relations
			$transcodable = $joblog->Transcodable();
			$transcodable->loadTranscodedFiles();
			
			if(!count(get_object_vars($update->errors))){
				$joblog->JobErrorMessage = "";
				$joblog->JobStatus = "done";
			} else {
				$joblog->JobErrorMessage = json_encode($update->errors);
				$joblog->JobStatus = "error";
			}
			// write logfile
			$joblog->write();
		} else {
			// this shouldn't happen
			return "Well hello there...";
		}
		return "Updated";
	}

}