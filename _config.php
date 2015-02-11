<?php

define('HTML5_MEDIA_DIR', basename(dirname(__FILE__)));

// Provide shortcodes [video id=2] or [video id=2]description[/video]
ShortcodeParser::get('default')->register('video', array('Video_Controller', 'VideoShortCodeHandler'));

// Add oembed to config
//Config::inst()->update('Oembed', 'providers', 
//		array(
//			Director::absoluteBaseURL().'video-media/id/*' => array(
//				'http' => Director::absoluteBaseURL().'video-media/oembed/'
//			),
////			'http://*.yourtube.com/watch*' => array(
////				'http' => 'http://www.yourtube.com/oembed/'
////			)
//		)
//);
//Debug::dump( Config::inst()->get('Oembed', 'providers') );
//Debug::dump( Oembed::get_oembed_from_url('http://localhost/joshan-muziek.nl/site/video-media/id/2') );

//Oembed:
//  providers:
//    'http://*.youtube.com/watch*':
//      http: 'http://www.youtube.com/oembed/',
//      https: 'https://www.youtube.com/oembed/?scheme=https'