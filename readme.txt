=== Plugin Name ===
Contributors: techguytom
Tags: twitter, display tweets, mentions,
Requires at least: 2.9
Tested up to: 3.0
Stable tag: 1.2

Display your most recent tweets and mentions in a sidebar or widget enabled area.

== Description ==

This plugin adds a widget to Wordpress that will display a users tweets as well as recent mentions. Choose from several options including displaying your avatar and the number of tweets you'd like displayed.

== Installation ==

1. Upload the 'tweet_stream' directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

= Widget Use =
1. Go to 'Appearance' -> 'Widgets' to add and configure Tweet Stream to your Widget enabled areas.

= Template Tag Use =
You can display your tweets and/or mentions anywhere within your theme now through the use of the new template tag.
Place the following code anywhere you would like your tweets displayed:
<?php $args = array(
    'username'          => 'techguytom',
    'tweets_number'     => 4,
    'links'             => true,
    'images'            => true,
    'mentions'          => false,
    'mentions_number'   => 0,
    'cache'             => false
    );?>
<?php tgt_get_tweets($args);?>

= Parameters =
username
The username of the person to display the tweets for

tweets_number
The number of tweets to display

links
Switch to determine whether links in tweets should be clickable

images
Switch to determine if the twitter avatar should be displayed with each tweet

mentions
Switch to determine if mentions of the user should be displayed

mentions_number
The number of mentions to display

cache
Determines whether you want to cache the result for 5 minutes to prevent twitter rate limiting. This is a recommended setting if you have a high traffic site.

== Frequently Asked Questions ==

= Can I alter the way the widget looks in the sidebar? =

Absolutely.  CSS classes have been added to each element so you may style the plugin as you see fit.

== Screenshots ==

1. The configuration screen within the widget area.
2. The user facing display with all options chosen and default css.

== Changelog ==

= 1.2 =
Add template tag tgt_get_tweets() so the plugin is no longer dependent on a widget area
fixed typo in translation text domain - props: mike

= 1.1 =
Set internal cache to be dynamic so multiple widgets can be displayed

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
