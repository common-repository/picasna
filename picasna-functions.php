<?php

// set a few definitions that we'll use on every link that this plugin filters:

// set max image size
if (!defined(PICASNA_MAXSIZE)){	// these brackets are unnecessary, since we use require_once on this file. But regardless...
	$picasna_maxsize = (int) get_option( PICASNA_OPTION . 'maxsize' );
	$picasna_valid = array(320, 400, 512, 576, 640, 720, 800, 912, 1024, 1152, 1280, 1440, 1600 );
	if (!in_array($picasna_maxsize, $picasna_valid))
		$picasna_maxsize = 800; // default
	define('PICASNA_MAXSIZE', $picasna_maxsize);
}

if (!defined(PICASNA_THUMBSIZE)){	// these brackets are unnecessary, since we use require_once on this file. But regardless...
	$picasna_thumbsize = (int) get_option( PICASNA_OPTION . 'thumbsize' );
	$picasna_valid = array(64, 72, 144, 160);
	if (!in_array($picasna_thumbsize, $picasna_valid))
		$picasna_thumbsize = 144; // default
	define('PICASNA_THUMBSIZE', $picasna_thumbsize);
}

if (!defined(PICASNA_COVERSIZE)){	// these brackets are unnecessary, since we use require_once on this file. But regardless...
	$picasna_coversize = (int) get_option( PICASNA_OPTION . 'coversize' );
	$picasna_valid = array(72, 144, 160);
	if (!in_array($picasna_coversize, $picasna_valid))
		$picasna_coversize = 144; // default
	define('PICASNA_COVERSIZE', $picasna_coversize);
}


if (!defined(PICASNA_COLOR)){
	$picasna_color = (int) get_option( PICASNA_OPTION . 'color' );
	if (!$picasna_color)
		$picasna_color = 0; //Dark
	define('PICASNA_COLOR', $picasna_color);
}

if (!defined(PICASNA_COVERTITLE)){
	$picasna_covertitle = (int) get_option( PICASNA_OPTION . 'covertitle' );
	if (!$picasna_covertitle)
		$picasna_covertitle = 0; //Hide
	define('PICASNA_COVERTITLE', $picasna_covertitle);
}

if (!defined(PICASNA_BADGETYPE)){
	$picasna_badgetype = (int) get_option( PICASNA_OPTION . 'badgetype' );
	if (!$picasna_badgetype)
		$picasna_badgetype = 0; //Green
	define('PICASNA_BADGETYPE', $picasna_badgetype);
}

// set time of last options update
if (!defined(PICASNA_UPDATE)){
	$picasna_update = (int) get_option( PICASNA_OPTION . 'update' ); // holds timestamp of last change to plugin's options.
	define('PICASNA_UPDATE', $picasna_update);
}

function _picasna_getalbumsxml($url) {
	if (function_exists('curl_init')) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$xml_raw = curl_exec($ch);
		$xml = simplexml_load_string($xml_raw);
	} else {
		$xml = simplexml_load_file($url);
	}
	  
		return $xml;
}



// our primary callback function
function picasna_display($match){
	$pout = "";
	$cs = PICASNA_COVERSIZE."c";
	
	$user = $match[3];
	$album = $match[4];
	
	if (substr($album, -1) == "#") $album = substr ($album, 0, -1);
	$albvars = explode("?", $album);
	$album = $albvars[0];
	
	$qsign = "";
	$asign = "";
	
	if (isset($albvars[1])) {
		$auth = $albvars[1];
		$qsign = "?";
		$asign = "&";
	} else {
		$auth = "";
	}
	
	$feedURL = "http://picasaweb.google.com/data/feed/api/user/".$match[3]."?kind=album&thumbsize=$cs".$asign.$auth;
	//$albumsxml = simplexml_load_file($feedURL);
	$albumsxml = _picasna_getalbumsxml($feedURL);
	
	if ($album!="allpublic") {
	
		foreach ($albumsxml->entry as $entry) {
			$gphoto = $entry->children('http://schemas.google.com/photos/2007');
			$albumname = $gphoto->name;  
			
			if ($albumname==$album) {
				$albummedia = $entry->children('http://search.yahoo.com/mrss/');
				$albumcover = $albummedia->group->thumbnail->attributes()->{'url'};
				$coverwidth = $albummedia->group->thumbnail->attributes()->{'width'}+10;
				$coverheight = $albummedia->group->thumbnail->attributes()->{'height'}+10;
			}
		}
		// $match[0] holds the entire matched phrase. We'll return it if we cannot parse as needed.
		// $match[2] holds the TLD e.g. google.COM, google.DK, google.UK
		// $match[3] holds the user name, $match[4] holds the requested album name
		// $match[1] and $match[5] hold any additional attributes other than href (rel, title, style, onclick, etc) that may have been specified.
		// $match[6] holds the link text--we'll use this as an album title
		
		// first, check that we've got a username, album, and link text
		if (!$match[2] || !$match[3] || !$album || !$match[6])
			return $match[0];
		// now, check that rel="noDisplay" wasn't specified:
		if (preg_match('~rel=["\'][^"\']*noDisplay[^"\']*["\']~', $match[1].$match[5]))
			return $match[0];
	
		
		$url = 'http://picasaweb.google.'.$match[2].'/'.$user.'/'.$album;	// for saving our data
	
		// prepare options names
		$md5 = md5($url);
		$content_option = PICASNA_OPTION . $md5;	// holds data about album's contents
		$time_option = $content_option . '_ts';	// holds time stamp from last update
	
		$last_update = (int) get_option( $time_option );	// to 0 if false
		$last_plugin_update = PICASNA_UPDATE;
	
		if ($last_plugin_update > $last_update)	// if plugin's options have been updated, we want to force a flush of the cache.
			$last_update = false;
		
		$now = time();
		
		if ( !$last_update   ||   ($now - $last_update) > PICASNA_CACHETIME )	// content is outdated or has yet to be fetched.
			$content = picasna_fetch( $user, $album );
		elseif ( !($content = get_option( $content_option )) )	// get content from cache--should never fail, but just in case, let's check
			$content = picasna_fetch( $user, $album );
		else	// if we're here, then we successfully set $content using get_option( $content_option ) -- everything is good
			$noUpdate = true;
	
		if (!is_array($content) || empty($content))	// oops! $content doesn't hold what it ought to
			return $match[0];	// don't do nuthin'
	
		// do we need to update our options?
		if (!$noUpdate){	// update our options
			update_option( $time_option, $now, 'Timestamp for album "'.$album.'"', 'no' );	// update cache's timestamp
			update_option( $content_option, $content, 'Cache of album "'.$album.'"', 'no' );	// update cache
		}
		
		
		//$pluginPath = WP_CONTENT_URL .'/plugins/'.plugin_basename(dirname(__FILE__));
		$pluginPath = "http://picasna.com/widget";
		//
		//$xmlscript = substr($pluginPath, 7).'/albumxml.php';
		$xmlscript = "picasna.com/widget/xml";
		$flashvars = '?cover='.substr($albumcover, 7).'&xmlPath='.$xmlscript.'&an='.$album.'&ps='.PICASNA_MAXSIZE.'&un='.$match[3].'&at='.$match[6].'&ts='.PICASNA_THUMBSIZE.'&cpad=5&tpad=7&cscheme='.PICASNA_COLOR.'&ct='.PICASNA_COVERTITLE.'&bt='.PICASNA_BADGETYPE.$asign.$auth;
		
		$pout = '<object style="float:left; margin: 0 10px 10px 0" width="'.$coverwidth.'" height="'.$coverheight.'"><param name="movie" value="'.$pluginPath.'/gallery.swf'.$flashvars.'"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed style="float:left; margin: 0 10px 10px 0" width="'.$coverwidth.'" height="'.$coverheight.'" src="'.$pluginPath.'/gallery.swf'.$flashvars.'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="'.$coverwidth.'" height="'.$coverheight.'"></embed></object>';
	} else {
		
		foreach ($albumsxml->entry as $entry) {
			$gphoto = $entry->children('http://schemas.google.com/photos/2007');
			$albumname = $gphoto->name;  
			
			
			$albummedia = $entry->children('http://search.yahoo.com/mrss/');
			$albumcover = $albummedia->group->thumbnail->attributes()->{'url'};
			$coverwidth = $albummedia->group->thumbnail->attributes()->{'width'}+10;
			$coverheight = $albummedia->group->thumbnail->attributes()->{'height'}+10;
			
		
			if (!$match[2] || !$match[3] || !$albumname || !$match[6])
				return $match[0];
			// now, check that rel="noDisplay" wasn't specified:
			if (preg_match('~rel=["\'][^"\']*noDisplay[^"\']*["\']~', $match[1].$match[5]))
				return $match[0];
		
			
			$url = 'http://picasaweb.google.'.$match[2].'/'.$user.'/'.$albumname;	// for saving our data
		
			// prepare options names
			$md5 = md5($url);
			$content_option = PICASNA_OPTION . $md5;	// holds data about album's contents
			$time_option = $content_option . '_ts';	// holds time stamp from last update
		
			$last_update = (int) get_option( $time_option );	// to 0 if false
			$last_plugin_update = PICASNA_UPDATE;
		
			if ($last_plugin_update > $last_update)	// if plugin's options have been updated, we want to force a flush of the cache.
				$last_update = false;
			
			$now = time();
			
			if ( !$last_update   ||   ($now - $last_update) > PICASNA_CACHETIME )	// content is outdated or has yet to be fetched.
				$content = picasna_fetch( $user, $albumname );
			elseif ( !($content = get_option( $content_option )) )	// get content from cache--should never fail, but just in case, let's check
				$content = picasna_fetch( $user, $albumname );
			else	// if we're here, then we successfully set $content using get_option( $content_option ) -- everything is good
				$noUpdate = true;
		
			if (!is_array($content) || empty($content))	// oops! $content doesn't hold what it ought to
				return $match[0];	// don't do nuthin'
		
			// do we need to update our options?
			if (!$noUpdate){	// update our options
				update_option( $time_option, $now, 'Timestamp for album "'.$albumname.'"', 'no' );	// update cache's timestamp
				update_option( $content_option, $content, 'Cache of album "'.$albumname.'"', 'no' );	// update cache
			}
			
			
		//$pluginPath = WP_CONTENT_URL .'/plugins/'.plugin_basename(dirname(__FILE__));
		$pluginPath = "http://picasna.com/widget";
		//
		//$xmlscript = substr($pluginPath, 7).'/albumxml.php';
		$xmlscript = "picasna.com/widget/xml";
			$flashvars = '?xmlPath='.$xmlscript.'&an='.$albumname.'&ps='.PICASNA_MAXSIZE.'&un='.$match[3].'&ts='.PICASNA_THUMBSIZE.'&cpad=5&tpad=7&cscheme='.PICASNA_COLOR.'&ct='.PICASNA_COVERTITLE.'&bt='.PICASNA_BADGETYPE.$asign.$auth;
			
			$pout .= '<object style="float:left; margin: 0 10px 10px 0" width="'.$coverwidth.'" height="'.$coverheight.'"><param name="movie" value="'.$pluginPath.'/gallery.swf'.$flashvars.'"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed style="float:left; margin: 0 10px 10px 0" width="'.$coverwidth.'" height="'.$coverheight.'" src="'.$pluginPath.'/gallery.swf'.$flashvars.'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="'.$coverwidth.'" height="'.$coverheight.'"></embed></object> ';
		}
		
	}
	return $pout;
}


// HTTP FUNCTION -- this function does the actual picasaweb queries. Don't call it directly--use the  display function instead, since it has caching built in.
// given a username and an album name/id, produces an array of the album's contents.
// note that $imagesize only takes a certain set of values. If you try an unallowed value, you'll get weird results
// this function was derived partly from http://code.google.com/p/picasawebphplibrary/
function picasna_fetch($userName, $albumNameOrId, $imagesize = 800) {

	// prerequisites
	//if (!class_exists('domdocument') || !class_exists('domxpath'))
	//	return false;

	// construct url to album
	if (is_numeric($albumNameOrId))
		$url = 'http://picasaweb.google.com/data/feed/api/user/' .urlencode($userName) . '/albumid/' . urlencode($albumNameOrId);
	else
		$url = 'http://picasaweb.google.com/data/feed/api/user/' .urlencode($userName) . '/album/' . urlencode($albumNameOrId);

	// append image size option
	$url .= '?thumbsize='.PICASNA_MAXSIZE;

	$album = array();
	
	// request album data	
	// first, let's try wp_remote_fopen:
	$xml = wp_remote_fopen($url);	// will try file_get_contents or, if allow_url_fopen is off, looks for curl extension
	
	// check. wp_remote_fopen returns false if (1) $url is bad or (2) allow_url_fopen is off and curl is not installed. In case it's (2), let's try snoopy:
	if (false===$xml){
		if (!class_exists('Snoopy') && file_exists(ABSPATH . 'wp-includes/class-snoopy.php'))
			require_once(ABSPATH . 'wp-includes/class-snoopy.php');
		if (!class_exists('Snoopy'))	// in case !file_exists()
			return false;
		$snoopy = new Snoopy;
		$snoopy->fetch( $url );
		$xml = $snoopy->results;
	}
	if (!$xml)	// either snoopy failed too, or the $url is bad.
		return false;
	
	// get album title
	preg_match('~<title[^>]*>(.*)</title>~Usi', $xml, $m);
	$album['meta']['title'] = $m[1];
	
	
	// get photos
	preg_match_all('~<entry[^>]*>(.*)</entry>~Usi', $xml, $m);
	$m = $m[0];
	foreach($m as $p){	// loop through all the photos, grabbing the URL, width, height, and caption
		$photo = array();
		// image url, width, height:
		preg_match('~<media:thumbnail\s*url=(["\'])(.*)\1\s*height=(["\'])([0-9]*)\3\s*width=(["\'])([0-9]*)\5~Usi', $p, $pm);
		$photo['image'] = $pm[2];
		$photo['height'] = $pm[4];
		$photo['width'] = $pm[6];
		// image caption:
		preg_match('~<summary[^>]*>(.*)</summary>~Usi', $p, $pm);
		$photo['caption'] = $pm[1];
		// image id (for linking to image)
		preg_match('~<gphoto:id>(.*)</gphoto:id>~U', $p, $pm);
		$photo['id'] = $pm[1];
		$album[] = $photo;
	}
	return $album;
}

?>