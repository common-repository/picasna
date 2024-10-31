<?php

// functions that create our options page

class picasna_options{
	
	// option names
	
	var $opt_thumbsize;
	var $opt_coversize;
	var $opt_maxsize;
	var $opt_update;
	var $opt_color;
	var $opt_covertitle;
	var $opt_badgetype;
	
	var $opt_other;
	
	// option values
	var $thumbsize = 144;
	var $coversize = 144;
	var $maxsize = 800;	// default image size
	var $color = 0; // default color (Dark)
	var $covertitle = 0;
	var $badgetype = 0;
	var $update;
	var $other; // opt_other
	
	// used internally
	var $image_sizes;
	var $thumb_sizes;
	var $cover_sizes;

	// sets all defaults
	function picasna_options(){
		// set option names
		$this->opt_thumbsize = PICASNA_OPTION . 'thumbsize';
		$this->opt_coversize = PICASNA_OPTION . 'coversize';
		$this->opt_maxsize = PICASNA_OPTION . 'maxsize';
		$this->opt_update = PICASNA_OPTION . 'update'; // timestamp of last change to plugin's options
		$this->opt_color = PICASNA_OPTION . 'color';
		$this->opt_covertitle = PICASNA_OPTION . 'covertitle';
		$this->opt_badgetype = PICASNA_OPTION . 'badgetype';
		$this->opt_other = PICASNA_OPTION . 'other'; // array of assorted options
		
		// is thumbsize set, or use default?
		$coversize = get_option( $this->opt_coversize );
		if ($coversize)
			$this->coversize = $coversize;
		
		// is thumbsize set, or use default?
		$thumbsize = get_option( $this->opt_thumbsize );
		if ($thumbsize)
			$this->thumbsize = $thumbsize;
		
		// is maxsize set, or use default?
		$maxsize = get_option( $this->opt_maxsize );
		if ($maxsize)
			$this->maxsize = $maxsize;

		// is $color set, or use default?
		$color = get_option( $this->opt_color );
		if ($color)
			$this->color = $color;
			
		// is $color set, or use default?
		$covertitle = get_option( $this->opt_covertitle );
		if ($covertitle)
			$this->covertitle = $covertitle;
			
		// is $color set, or use default?
		$badgetype = get_option( $this->opt_badgetype );
		if ($badgetype)
			$this->badgetype = $badgetype;


		
		$this->cover_sizes = array(72, 144, 160);
		
		$this->thumb_sizes = array(64, 72, 144, 160);

		// set valid sizes array
		$this->image_sizes = array(320, 400, 512, 576, 640, 720, 800, 912, 1024, 1152, 1280, 1440, 1600);

		// set valid color schemes
		$this->valid_colors = array(
			'Dark'=>0, 'Light'=>1);
		
		$this->valid_covertitles = array(
			'Hide'=>0, 'Show'=>1);
		
		$this->valid_badgetypes = array(
			'Green ribbon (old)'=>0, 'White square (new)'=>1);

		if (1==$_POST['picasna_update'])
			echo '<div id="message" class="updated fade"><p><strong>'. $this->onSave() .'</strong> <a href="'.get_bloginfo('url').'">View site &raquo;</a></p></div>';
		
		echo $this->makeForm();
	}
	
	// for dummy options that get saved as 1 or 0
	function checkbox($o){
		if ($o)
			return 'checked="checked"';
	}
	
	function onSave(){
		// validate
		if (!in_array($_POST['cover_sizes'], $this->cover_sizes))
			return 'Invalid input. Are you trying to hack something?';
		if (!in_array($_POST['thumb_sizes'], $this->thumb_sizes))
			return 'Invalid input. Are you trying to hack something?';
		if (!in_array($_POST['picasna_sizes'], $this->image_sizes))
			return 'Invalid input. Are you trying to hack something?';
		if (!in_array($_POST['picasna_color'], $this->valid_colors))
			return 'Invalid input. Are you trying to hack something?';
		if (!in_array($_POST['picasna_covertitle'], $this->valid_covertitles))
			return 'Invalid input. Are you trying to hack something?';
		if (!in_array($_POST['picasna_badgetype'], $this->valid_badgetypes))
			return 'Invalid input. Are you trying to hack something?';


		// update vars
		if ($_POST['cover_sizes'] != $this->coversize){
			$didone = true;
			$this->coversize = $_POST['cover_sizes'];
			$update_time = true;	// if we change this setting, we need to be sure to dump all album caches
			update_option($this->opt_coversize, $_POST['cover_sizes']);
		}
		
		if ($_POST['thumb_sizes'] != $this->thumbsize){
			$didone = true;
			$this->thumbsize = $_POST['thumb_sizes'];
			$update_time = true;	// if we change this setting, we need to be sure to dump all album caches
			update_option($this->opt_thumbsize, $_POST['thumb_sizes']);
		}
		if ($_POST['picasna_sizes'] != $this->maxsize){
			$didone = true;
			$this->maxsize = $_POST['picasna_sizes'];
			$update_time = true;	// if we change this setting, we need to be sure to dump all album caches
			update_option($this->opt_maxsize, $_POST['picasna_sizes']);
		}
		if ($_POST['picasna_color'] != $this->color){
			$didone = true;
			$this->color = $_POST['picasna_color'];
			update_option($this->opt_color, $_POST['picasna_color']);
		}
		if ($_POST['picasna_covertitle'] != $this->covertitle){
			$didone = true;
			$this->covertitle = $_POST['picasna_covertitle'];
			update_option($this->opt_covertitle, $_POST['picasna_covertitle']);
		}
		if ($_POST['picasna_badgetype'] != $this->badgetype){
			$didone = true;
			$this->badgetype = $_POST['picasna_badgetype'];
			update_option($this->opt_badgetype, $_POST['picasna_badgetype']);
		}

		
		// do we need to update option timestamp to clear cache?
		if ($update_time)
			update_option($this->opt_update, time());		

		// done
		if ($didone)
			return 'Options updated.';
		return 'Nothing to save! (You didn\'t change anything.)';
	}
	
	function makeForm(){
		$out = '
			<div class="wrap">
				<h2>Picasna &raquo; Instructions</h2>
				<p>To automatically insert photos from PicasaWeb into one of your posts, just make a link to one of your albums on a line by itself. Don\'t just paste the URL into a blog post; you need to actually link the URL (so it is clickable). The plugin will find your link and convert it as long as it meets the following conditions:</p>
				<ul style="list-style-type:disc;">
					<li>The link is on a line by itself.</li>
					<li>The URL looks like this (it will by default): <em>http://picasaweb.google.com/<strong>username</strong>/<strong>albumname</strong></em>
						<ul style="list-style-type:disc;padding-left:1em;">
							<li><strong>username</strong> is your Google (PicasaWeb) username</li>
							<li><strong>albumname</strong> is the name of an album</li>
						</ul>
					</li>
					<li>The link is clickable.</li>
					<li>The link does not contain <code>rel="noDisplay"</code> in it.</li>
				</ul>

				<h2>Picasna &raquo; Options</h2>
				<form action="" method="post">
				<table>
					<tr>
						<td style="text-align:right;vertical-align:top;"><select name="cover_sizes" id="cover_sizes">';
		foreach($this->cover_sizes as $size){
			$selected = ($size == $this->coversize) ? ' selected="selected"' : '';
			$out .= '<option value="'.$size.'"'.$selected.'>'.$size.' pixels</option>';
		}
		$out .= '
						</select>&nbsp;</td>
						<td><strong><label for="thumb_sizes">Thumbnail size for your <b>album cover</b> (in pixels)</label></strong><br /><em>144 pixels recommended</em><br />&nbsp;</td>
					</tr>
					<tr>
						<td style="text-align:right;vertical-align:top;"><select name="thumb_sizes" id="thumb_sizes">';
		foreach($this->thumb_sizes as $size){
			$selected = ($size == $this->thumbsize) ? ' selected="selected"' : '';
			$out .= '<option value="'.$size.'"'.$selected.'>'.$size.' pixels</option>';
		}
		$out .= '
						</select>&nbsp;</td>
						<td><strong><label for="thumb_sizes">Thumbnail size for your images (in pixels)</label></strong><br /><em>144 pixels is pretty good for all screen sizes, but if you need more thumbnails per page, choose smaller size.</em><br />&nbsp;</td>
					</tr>
					<tr>
						<td style="text-align:right;vertical-align:top;"><select name="picasna_sizes" id="picasna_sizes">';
		foreach($this->image_sizes as $size){
			$selected = ($size == $this->maxsize) ? ' selected="selected"' : '';
			$out .= '<option value="'.$size.'"'.$selected.'>'.$size.' pixels</option>';
		}
		$out .= '
						</select>&nbsp;</td>
						<td><strong><label for="picasna_sizes">Size for full images (in pixels)?</label></strong><br /><em>800 is enough for most screen sizes and works everywhere.</em><br />&nbsp;</td>
					</tr>
					<tr>
						<td style="text-align:right;vertical-align:top;"><select name="picasna_color" id="picasna_color">';
		foreach($this->valid_colors as $words=>$seconds){
			$selected = ($seconds == $this->color) ? ' selected="selected"' : '';
			$out .= '<option value="'.$seconds.'"'.$selected.'>'.$words.'</option>';
		}
		$out .= '
						</select>&nbsp;</td>
						<td><strong><label for="picasna_color">Color Scheme for your gallery</label></strong><br /><br />&nbsp;</td>
					</tr>
					
					<tr>
						<td style="text-align:right;vertical-align:top;"><select name="picasna_covertitle" id="picasna_covertitle">';
		foreach($this->valid_covertitles as $words=>$seconds){
			$selected = ($seconds == $this->covertitle) ? ' selected="selected"' : '';
			$out .= '<option value="'.$seconds.'"'.$selected.'>'.$words.'</option>';
		}
		$out .= '
						</select>&nbsp;</td>
						<td><strong><label for="picasna_covertitle">Show or hide the title on the initial cover thumbnail</label></strong><br /><br />&nbsp;</td>
					</tr>
					
					<tr>
						<td style="text-align:right;vertical-align:top;"><select name="picasna_badgetype" id="picasna_badgetype">';
		foreach($this->valid_badgetypes as $words=>$seconds){
			$selected = ($seconds == $this->badgetype) ? ' selected="selected"' : '';
			$out .= '<option value="'.$seconds.'"'.$selected.'>'.$words.'</option>';
		}
		$out .= '
						</select>&nbsp;</td>
						<td><strong><label for="picasna_badgetype">Badge type</label></strong><br /><em>The "Green" is the classic but may not fit your design. Try the new one!</em><br />&nbsp;</td>
					</tr>
					
				</table>
				<p class="submit" style="width:430px;"><input type="submit" value="Update Options &raquo;" />
							<input type="hidden" name="picasna_update" value="1" />
				</p>
				</form>
			</div>
		';
		return $out;
	}
}
?>