<?php

class MediaManager extends ModelAdmin {

    private static $managed_models = array(
		'Audio',
        'Video',
        'TranscodeJob'
    );

    private static $url_segment = 'html5-media';

    private static $menu_title = 'HTML5 Media';
	
}