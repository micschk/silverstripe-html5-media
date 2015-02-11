<?php

//ftp://login:password@host:21/path/to/video.mp4
//ftp://user:pass@dev.restruct.nl:21/httpdocs/test.mp4 etc
//ftp ftp://user:secret@ftp.example.com/my-local-file.txt my-local-file.txt
//ftp ftp://user:pass@dev.restruct.nl:21/httpdocs/test.txt test.txt
//../subdomains/somesub.restruct.nl
//http://www.engr.colostate.edu/me/facil/dynamics/files/bird.avi
//http://www.engr.colostate.edu/me/facil/dynamics/files/drop.avi
//http://www.engr.colostate.edu/me/facil/dynamics/files/flame.avi
//http://www.engr.colostate.edu/me/facil/dynamics/files/cbw3.avi

/**
# Test config Heywatch (ftp folders will be created if they dont exist yet)

set source = http://www.engr.colostate.edu/me/facil/dynamics/files/drop.avi
set webhook = https://app.heywatch.com/pings/54ca174d/micschk

-> mp4 = ftp://user:pass@restruct.nl:21/test.mp4
-> webm = ftp://user:pass@restruct.nl:21/test.webm
-> ogv = ftp://user:pass@restruct.nl:21/test.ogv
-> jpg = ftp://user:pass@restruct.nl:21/poster-#num#.jpg, number=1
 */

class TranscodeJob extends DataObject {
	
	public static function getOrCreateForTranscodable($TranscodableID){
		$job = TranscodeJob::get()->filter("TranscodableID",$TranscodableID)->first();
		if($job){ 
			return $job;
		} else {
			$job = new TranscodeJob();
			$job->TranscodableID = $TranscodableID;
			$job->write();
			return $job;
		}
	}
	
	private static $db = array (
		"JobID" => "Varchar",
		"JobStatus" => "Enum('started,done,error','started')",
		"JobErrorMessage" => "Text",
		//"JobUpdateContent" => "Text", // json/serialized callback post (don't put in db, contains passwords)
		//"TranscodableID" => "Int",
		"TranscodableClass" => "Varchar",
	);
	
	private static $has_one = array(
		"Transcodable" => "TranscodableObject",
	);
	
	private static $summary_fields = array( 
		"JobID",
		"JobStatus",
		"JobErrorCode",
		"JobErrorMessage",
		"Transcodable.Name",
		"TranscodableClass",
		"Created"
   	);
	
//	public function getTranscodable(){
//		Debug::dump(DataObject::get_by_id($this->TranscodableClass, $this->TranscodableID));
//		return DataObject::get_by_id($this->TranscodableClass, $this->TranscodableID);
//	}
	
	/* no manual editing */
	function canCreate($Member = null) { return false; }
	function canEdit($Member = null) { return true; }
	function canView($Member = null) { return true; }
	function canDelete($Member = null) { return false; }
	
}
