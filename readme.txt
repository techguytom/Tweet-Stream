=== Plugin Name ===
Contributors: techguytom
Tags: twitter, display tweets, mentions, 
Requires at least: 2.9
Tested up to: 3.0
Stable tag: 1.0

Display your most recent tweets and mentions in a sidebar or widget enabled area.

== Description ==

This plugin adds a widget to Wordpress that will display a users tweets as well as recent mentions. Choose from several options including displaying your avatar and the number of tweets you'd like displayed.

== Installation ==

1. Upload the 'tweet_stream' directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Appearance' -> 'Widgets' to add and configure Tweet Stream to your Widget enabled areas.

== Frequently Asked Questions ==

= Can I alter the way the widget looks in the sidebar? =

Absolutely.  CSS classes have been added to each element so you may style the plugin as you see fit.

== Screenshots ==

1. The configuration screen within the widget area.
2. The user facing display with all options chosen and default css.

== Changelog ==

= 1.0 =
Clear internal cache whenever the widgets configuration is updated.
Better error handling if twitter is not responding or errors occur in transmission
Removed places data from tweet display
Added option to decide whether the user wants links within tweet to be clickable or not. This affects usernames and avatars as well.
Added title options to include image and custom text

= 0.4 =
Commented out places display, which broke the plugin as twitter changed return data

= 0.3 =
Corrected directory information that was keeping the style sheet from loading

= 0.2 =
Initial Release.

== Upgrade Notice ==

= 0.3 =
An error in the directory name prevented the stylesheet from loading. Upgrade if you are using the default styling.
