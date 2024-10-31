<?php
/*
Plugin Name: Picasna
Description: Picasna for Wordpress is a free plugin that enables you to display your photos in a stylish and easy-to-browse way on your Wordpress Blog. It's a fullscreen gallery that uses the best tool for editing and managing your photos - Picasa 3 (by Google). You can upload photos directly to Picasa Web Albums and they will appear on your web page in seconds.
Author: Igor Barbashin
Version: 1.5.2
Plugin URI: http://picasna.com/
Author URI: http://picasna.com/
*/

/* CHANGELOG
	1.5	Keyboard control (Right/Left/Space/Backspace)
		Caching for faster loading
		Fetching all public albums
		BugFixes

	1.4.1 New settings

	1.4 Album cover redesign
		Smart layout (better support of screens less than 1280px wide)
		Bug fixes

	1.3	Highly improved performance
		Better preloading
		Bug fixes

	1.2.1 Improved performance
		Bug Fixes

	1.2 UTF-8 Unicode for all text! (All languages support)
		Light and dark color schemes
		Preloading of next photos
		Reduced gallery filesize (26kb instead of 135kb)
		Hide photo-caption field if empty
		Bug fixes

	1.1 Bug fixes
		Caption notifications
		
	1.0 Beta released
*/



// looks for a link to a picasaweb album on a line by itself.
// link must be of the form http://picasaweb.google.com/username/albumname (or else it won't get processed)--no whitespace in username or albumname
// filtering function
function picasna($content){
	if (false===strpos($content, 'http://picasaweb.google.'))
		return $content;
	require_once('picasna-functions.php');
	// build our regex:
	$preg = '~(?:\n+|<p>)\s*';	// look for a new line, possibly with leading whitespace
	$preg .= '(?:\s*|<!--\s*more\s*-->|<span id="more-[0-9]+"></span>)'; // in case there's a <!--more--> at the beginning of the line (or, after processing, <span id="more-7"></span>)
	$preg .= '<a\s*([^>]*)\s+'; // look for opening <a, possibly with some attributes before href=
	$preg .= 'href=["\']http://picasaweb\.google\.(co\.uk|[a-z]{2,4})/([^/\s]+)/([^/"\'\s]+)/?["\']'; // the URL; grab the username and album name.
	$preg .= '\s*([^>]*)\s*>'; // grab any remaining attributes, then the closing >
	$preg .= '([^>]+)';	// the link text. There must not be any tags within the link text.
	$preg .= '</a\s*>';	// the closing </a> tag, possibly with whitespace
	$preg .= '\s*(?:\n+|</p>)~i';	// the end of the line, possibly with whitespace
	// do it:
	return preg_replace_callback($preg, 'picasna_display', $content);	// note that we force the trigger to be on its own line.
}
add_filter('the_content', 'picasna');




// admin page
function picasna_admin(){
	require_once('picasna-admin.php');
	$admin = new picasna_options();
}
function picasna_adminHook(){
	add_submenu_page('options-general.php', 'Picasna', 'Picasna', 7, 'picasna.php', 'picasna_admin');
}
add_action('admin_menu', 'picasna_adminHook');



// prefix to use on all our options
define('PICASNA_OPTION', 'picasna_');



// some styles. This is optional.
function picasna_head(){
	echo '
	<style type="text/css"><!--
	.picasna{text-align:center;}
	.picasna-wrap{margin:1em 0;}
	.picasna-image{}
	.picasna-caption{}	// -->
	</style>
	';
}
add_action('wp_head', 'picasna_head');
?>