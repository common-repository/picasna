=== Picasna ===
Contributors: Igor Barbashin
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4880760
Tags: picasa, picasaweb, picassa, picassaweb, photos, images, photo, image, google, jpg, png, gif, photograph, photographs, photography, gallery, inline, fullscreen, presentation, unlisted, wordpress, pictures, plugin
Requires at least: 2.0
Tested up to: 2.7.1
Stable tag: 1.5.2

Picasa users: Just paste your Picasaweb album link in a post or page and Picasna will create a stylish fullscreen gallery. Supports unlisted albums. The best choice for your portfolio, showcase, presentations or photo trip reports.

== Description ==

You can check other Picasaweb plugins, but they all look pretty bad. Most of them just lead your visitors away from your blog to picasaweb service. They show random stuff or require programming skills to set up. Picasna will do everything for you without this headache. It parses link to your picasa album and creates professional fullscreen flash gallery ([demo](http://picasna.com/)). Don't waste time choosing a plugin. Go shoot! **The best choice for your portfolio, showcase, presentations or photo trip reports.**

= What's new in 1.5 =

* Keyboard control (Right/Left/Space/Backspace).
* Caching for faster loading.
* Fetching all public albums.
* Bug fixes.

= Features =

* Autolayout for all screen sizes and aspect ratios.
* Fullscreen browsing.
* Show albums from your Picasaweb Account.
* Unlisted albums support.
* UTF-8 Unicode for all text! (All languages support)
* Next image preloading.
* Lightweight (27kb).
* Light and dark color schemes.
* Basic HTML formatting for description and photo-captions (supports `<b>bold</b> <i>italic</i> <u>underline</u> <br /> for line breaks and <a href="http://picasna.com" target="_self">links</a>`).
* Adjust thumbnail and album cover sizes.
* Browser and platform independent. All you need is a browser with Flash 7 (or higher) installed.

= Support =

If you have any problems, please check the [F.A.Q.](http://picasna.com/faq/). 
Also, you are always welcome to [ask questions](http://picasna.com/picasna-for-wordpress/picasna-1_5-release-notes/) on my blog.

== Installation ==

1. IF USING WP 2.7 OR HIGHER, use the automatic installer. OTHERWISE, follow the usual download/unzip/upload/activate routine (after unzipping, upload the "picasna" folder, to your `/wp-content/plugins/` directory).
1. Activate the plugin through the 'Plugins' menu in WordPress.
Optional: Go to the `Options->Picasna` page to adjust the plugin's settings.

1. Create a new page or post on your site (or edit an existing one). 
1. Create a link to a PicasaWeb album on a line by itself.
1. The line should look like this `<a href="http://picasaweb.google.com/your.username/AlbumName">Your Album Title</a>`
1. View the page (or post).

You have several settings available at the options page, including

* Album thumbnail size
* Pictures thumbnail size
* Full image size

= License =

This plugin is provided "as is" and without warranty or expectation of function. I'll try to help you if you ask nicely, but I can't promise anything. You are welcome to use this plugin and modify it however you want, as long as you give credit to http://picasna.com.

= Support =

If you have any problems, please check the [F.A.Q.](http://picasna.com/faq/). 
Also, you are always welcome to [ask questions](http://picasna.com/picasna-for-wordpress/picasna-1_5-release-notes/) on my blog.

== Screenshots ==

1. Opened screenshot (fullscreen).
2. Clickable album cover.
3. Options page.

== Frequently Asked Questions ==

= I'm getting this error: Parse error: syntax error, unexpected T_OBJECT_OPERATOR in /wp-content/plugins/picasna/picasna-functions.php on line 78 =

Upgrade your PHP version to PHP5.

= I'm getting this warning: Warning: simplexml_load_file() [function.simplexml-load-file] =

Option `allow_url_fopen` should be `ON` in PHP configuration (php.ini). If you don't have access to edit this file and it could also be that your hosting company has restricted changing the `allow_url_fopen` value. In this case, you should refer to them and ask them to set the value of `allow_url_fopen` to `ON`.

= I'm still getting PHP errors or warnings. What to do? =

You can use [Picasna Widget](http://picasna.com/widget/). It works and looks *exactly the same as the plugin*.

= Why doesn't it work? =

Check if the link in its own line. Try adding linebreaks before and after the link.

= Why is my album title showing up weird? =

The title is the body of your link. It can differ from your album title in Picasaweb.
`<a href="http://picasaweb.google.com/youralbumlink">Your Album Title</a>`

= Why did my album disappear? =

First, check if you changed the visibility status of your album since the last time it was working. If you did, then just copy the new link and replace the old one. The same can happen if you "Reset unlisted link".